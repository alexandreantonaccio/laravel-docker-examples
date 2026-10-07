<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MaterialRentalController extends Controller
{
    public function index(Request $request): View
    {
        $canListAll = $request->user()->hasPermissionTo('materiais_alugueis.listar.qualquer');
        $rentals = MaterialRental::query()->with(['material', 'requester'])
            ->when(! $canListAll, fn ($query) => $query->where('requester_user_id', $request->user()->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('material-rentals.index', ['rentals' => $rentals, 'canListAll' => $canListAll]);
    }

    public function create(): View
    {
        return view('material-rentals.form', [
            'materials' => Material::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'material_id' => ['required', 'integer', 'exists:materials,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'starts_on' => ['required', 'date', 'after_or_equal:today'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'reason' => ['required', 'string', 'max:5000'],
        ]);

        $rental = DB::transaction(function () use ($request, $data): MaterialRental {
            $material = Material::query()->whereKey($data['material_id'])->lockForUpdate()->firstOrFail();
            abort_unless($material->active, 422, 'Este material não está disponível para aluguel.');
            $this->ensureAvailability($material, $data['quantity'], $data['starts_on'], $data['ends_on']);

            return MaterialRental::query()->create($data + [
                'requester_user_id' => $request->user()->id,
                'status' => 'pending',
            ]);
        });

        $this->notify($rental, 'Novo pedido de aluguel de material pendente.');

        return redirect()->route('material-rentals.index')->with('status', 'Pedido de aluguel enviado para aprovação.');
    }

    public function show(MaterialRental $materialRental): View
    {
        $materialRental->load(['material.group', 'requester']);
        abort_unless(
            (int) $materialRental->requester_user_id === (int) request()->user()->id
                || request()->user()->hasPermissionTo('materiais_alugueis.listar.qualquer'),
            403,
        );

        return view('material-rentals.show', ['rental' => $materialRental]);
    }

    public function approve(MaterialRental $materialRental): RedirectResponse
    {
        DB::transaction(function () use ($materialRental): void {
            $material = Material::query()->whereKey($materialRental->material_id)->lockForUpdate()->firstOrFail();
            $locked = MaterialRental::query()->lockForUpdate()->findOrFail($materialRental->id);
            abort_unless($locked->status === 'pending', 409, 'Somente pedidos pendentes podem ser aprovados.');
            $this->ensureAvailability($material, $locked->quantity, $locked->starts_on->toDateString(), $locked->ends_on->toDateString(), $locked->id);
            $locked->update(['status' => 'approved']);
        });

        $this->notify($materialRental, 'Seu pedido de aluguel de material foi aprovado.');

        return back()->with('status', 'Pedido aprovado.');
    }

    public function reject(Request $request, MaterialRental $materialRental): RedirectResponse
    {
        $data = $request->validate(['decision_reason' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($materialRental, $data): void {
            $locked = MaterialRental::query()->lockForUpdate()->findOrFail($materialRental->id);
            abort_unless($locked->status === 'pending', 409, 'Somente pedidos pendentes podem ser rejeitados.');
            $locked->update(['status' => 'rejected', 'decision_reason' => $data['decision_reason']]);
        });

        $this->notify($materialRental, 'Seu pedido de aluguel de material foi rejeitado: '.$data['decision_reason']);

        return back()->with('status', 'Pedido rejeitado.');
    }

    public function cancel(Request $request, MaterialRental $materialRental): RedirectResponse
    {
        $owner = (int) $materialRental->requester_user_id === (int) $request->user()->id;
        $canCancelAny = $request->user()->hasPermissionTo('materiais_alugueis.cancelar.qualquer');
        abort_unless($owner || $canCancelAny, 403);
        $data = $request->validate(['cancellation_reason' => ['required', 'string', 'max:2000']]);
        abort_unless(in_array($materialRental->status, ['pending', 'approved'], true), 409, 'Este pedido não pode mais ser cancelado.');
        $materialRental->update(['status' => 'cancelled', 'cancellation_reason' => $data['cancellation_reason']]);

        $this->notify($materialRental, 'Um pedido de aluguel foi cancelado: '.$data['cancellation_reason']);

        return back()->with('status', 'Pedido cancelado.');
    }

    private function ensureAvailability(
        Material $material,
        int $requestedQuantity,
        string $startsOn,
        string $endsOn,
        ?int $ignoreRentalId = null,
    ): void {
        $events = [];
        foreach ($material->rentals()
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('starts_on', '<=', $endsOn)
            ->whereDate('ends_on', '>=', $startsOn)
            ->when($ignoreRentalId, fn ($query) => $query->where('id', '!=', $ignoreRentalId))
            ->get(['quantity', 'starts_on', 'ends_on']) as $rental) {
            $overlapStarts = max($startsOn, $rental->starts_on->toDateString());
            $overlapEnds = min($endsOn, $rental->ends_on->toDateString());
            $events[$overlapStarts] = ($events[$overlapStarts] ?? 0) + $rental->quantity;
            $releaseDate = Carbon::parse($overlapEnds)->addDay()->toDateString();
            $events[$releaseDate] = ($events[$releaseDate] ?? 0) - $rental->quantity;
        }
        ksort($events);

        $reservedQuantity = 0;
        $maximumReservedQuantity = 0;
        foreach ($events as $change) {
            $reservedQuantity += $change;
            $maximumReservedQuantity = max($maximumReservedQuantity, $reservedQuantity);
        }
        if ($requestedQuantity + $maximumReservedQuantity > $material->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'A quantidade solicitada excede o estoque disponível neste período.',
            ]);
        }
    }

    private function notify(MaterialRental $rental, string $message): void
    {
        $rental->loadMissing(['requester', 'material.group']);
        if ($rental->requester->email) {
            Mail::raw($message, fn ($mail) => $mail->to($rental->requester->email)->subject('Atualização de aluguel de material'));
        }
        foreach ($rental->material->group?->notification_emails ?? [] as $email) {
            Mail::raw($message, fn ($mail) => $mail->to($email)->subject('Atualização de aluguel de material'));
        }
    }
}
