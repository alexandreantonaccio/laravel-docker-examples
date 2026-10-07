<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        return view('materials.index', [
            'materials' => Material::query()->with('group')->withSum([
                'rentals as reserved_quantity' => fn ($query) => $query
                    ->whereIn('status', ['pending', 'approved'])
                    ->whereDate('starts_on', '<=', today())
                    ->whereDate('ends_on', '>=', today()),
            ], 'quantity')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->materialForm(new Material);
    }

    public function store(Request $request): RedirectResponse
    {
        Material::query()->create($this->validateMaterial($request) + ['active' => true]);

        return redirect()->route('materials.index')->with('status', 'Material cadastrado.');
    }

    public function edit(Material $material): View
    {
        return $this->materialForm($material);
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $data = $this->validateMaterial($request, $material);
        DB::transaction(function () use ($material, $data): void {
            $locked = Material::query()->lockForUpdate()->findOrFail($material->id);
            abort_if(
                $data['quantity'] < $this->maximumReservedQuantity($locked),
                422,
                'A quantidade não pode ser menor que o total já reservado em períodos futuros.',
            );
            $locked->update($data);
        });

        return redirect()->route('materials.index')->with('status', 'Material atualizado.');
    }

    public function toggle(Material $material): RedirectResponse
    {
        DB::transaction(function () use ($material): void {
            $locked = Material::query()->lockForUpdate()->findOrFail($material->id);
            $locked->update(['active' => ! $locked->active]);
        });
        $material->refresh();

        return back()->with('status', $material->active ? 'Material reativado.' : 'Material desativado.');
    }

    public function groups(): View
    {
        return view('material-groups.index', [
            'groups' => MaterialGroup::query()->withCount('materials')->orderBy('name')->paginate(15),
        ]);
    }

    public function createGroup(): View
    {
        return $this->groupForm(new MaterialGroup);
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        MaterialGroup::query()->create($this->validateGroup($request) + ['active' => true]);

        return redirect()->route('material-groups.index')->with('status', 'Grupo de materiais cadastrado.');
    }

    public function editGroup(MaterialGroup $materialGroup): View
    {
        return $this->groupForm($materialGroup);
    }

    public function updateGroup(Request $request, MaterialGroup $materialGroup): RedirectResponse
    {
        $materialGroup->update($this->validateGroup($request, $materialGroup));

        return redirect()->route('material-groups.index')->with('status', 'Grupo de materiais atualizado.');
    }

    public function toggleGroup(MaterialGroup $materialGroup): RedirectResponse
    {
        DB::transaction(function () use ($materialGroup): void {
            $locked = MaterialGroup::query()->lockForUpdate()->findOrFail($materialGroup->id);
            if ($locked->active) {
                $locked->materials()->update(['material_group_id' => null]);
            }
            $locked->update(['active' => ! $locked->active]);
        });
        $materialGroup->refresh();

        return back()->with('status', $materialGroup->active ? 'Grupo reativado.' : 'Grupo desativado.');
    }

    private function materialForm(Material $material): View
    {
        return view('materials.form', [
            'material' => $material,
            'groups' => MaterialGroup::query()
                ->where('active', true)
                ->when($material->material_group_id, fn ($query, $id) => $query->orWhereKey($id))
                ->orderBy('name')->get(),
        ]);
    }

    private function groupForm(MaterialGroup $group): View
    {
        return view('material-groups.form', ['group' => $group]);
    }

    private function validateMaterial(Request $request, ?Material $material = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', Rule::unique('materials', 'code')->ignore($material?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'material_group_id' => ['nullable', 'integer', Rule::exists('material_groups', 'id')->where('active', true)],
        ]);
    }

    private function validateGroup(Request $request, ?MaterialGroup $group = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('material_groups', 'name')->ignore($group?->id)],
            'notification_emails_text' => ['nullable', 'string', 'max:5000'],
        ]);
        $emails = preg_split('/[\r\n,;]+/', $data['notification_emails_text'] ?? '', -1, PREG_SPLIT_NO_EMPTY);
        $request->merge(['notification_emails' => array_values(array_map('trim', $emails ?: []))]);
        $emailsData = $request->validate([
            'notification_emails' => ['array'],
            'notification_emails.*' => ['required', 'email', 'max:255', 'distinct'],
        ]);

        return [
            'name' => $data['name'],
            'notification_emails' => $emailsData['notification_emails'] ?? [],
        ];
    }

    private function maximumReservedQuantity(Material $material): int
    {
        $events = [];
        foreach ($material->rentals()
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('ends_on', '>=', today())
            ->get(['quantity', 'starts_on', 'ends_on']) as $rental) {
            $events[$rental->starts_on->toDateString()] = ($events[$rental->starts_on->toDateString()] ?? 0) + $rental->quantity;
            $releaseDate = $rental->ends_on->addDay()->toDateString();
            $events[$releaseDate] = ($events[$releaseDate] ?? 0) - $rental->quantity;
        }
        ksort($events);

        $reserved = 0;
        $maximum = 0;
        foreach ($events as $change) {
            $reserved += $change;
            $maximum = max($maximum, $reserved);
        }

        return $maximum;
    }
}
