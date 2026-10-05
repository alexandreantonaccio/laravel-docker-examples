<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingSeries;
use App\Models\BookingType;
use App\Models\Environment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\BookingAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function calendar(Request $request): View
    {
        $start = $request->date('start')?->startOfDay() ?? today()->startOfDay();
        $end = $request->date('end')?->endOfDay() ?? $start->copy()->endOfDay();
        $bookings = Booking::query()->with(['requester', 'teacher', 'environment', 'type', 'series'])
            ->whereBetween('booking_date', [$start->toDateString(), $end->toDateString()])
            ->when($request->integer('environment_id'), fn ($query, $id) => $query->where('environment_id', $id))
            ->orderBy('booking_date')->orderBy('starts_at')->get();

        return view('bookings.calendar', [
            'bookings' => $bookings,
            'environments' => Environment::query()->where('active', true)->orderBy('name')->get(),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ]);
    }

    public function adminIndex(Request $request): View
    {
        $query = Booking::query()->with(['requester', 'teacher', 'environment', 'type', 'series'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->input('status')))
            ->when($request->integer('environment_id'), fn ($builder, $id) => $builder->where('environment_id', $id))
            ->when($request->filled('start'), fn ($builder) => $builder->whereDate('booking_date', '>=', $request->date('start')))
            ->when($request->filled('end'), fn ($builder) => $builder->whereDate('booking_date', '<=', $request->date('end')));

        return view('bookings.admin-index', [
            'bookings' => $query->orderBy('booking_date')->orderBy('starts_at')->paginate(25)->withQueryString(),
            'environments' => Environment::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Booking $booking): View
    {
        return view('bookings.show', ['booking' => $booking->load(['requester', 'teacher', 'environment', 'type', 'series'])]);
    }

    public function create(): View
    {
        return view('bookings.form', $this->bookingOptions());
    }

    public function store(Request $request, BookingAvailability $availability): RedirectResponse
    {
        $canRequestForOthers = $request->user()->hasPermissionTo('agendamentos.solicitar.qualquer');
        $data = $request->validate([
            'requester_user_id' => [
                $canRequestForOthers ? 'required' : 'nullable',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
            ],
            'environment_id' => ['required', 'integer', Rule::exists('environments', 'id')->where('active', true)],
            'booking_type_id' => ['required', 'integer', Rule::exists('booking_types', 'id')->where('active', true)],
            'reason' => ['required', 'string', 'max:5000'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
        ]);
        $data['requester_user_id'] = $canRequestForOthers
            ? $data['requester_user_id']
            : $request->user()->id;
        $problem = $availability->reason($data['environment_id'], $data['booking_date'], $data['starts_at'], $data['ends_at']);
        if ($problem) {
            return back()->withErrors(['booking_date' => $problem])->withInput();
        }
        $booking = Booking::query()->create($data + ['status' => 'pending']);
        $this->notifyBooking($booking, 'Nova solicitação de agendamento pendente.');

        return redirect()->route('bookings.calendar')->with('status', 'Solicitação enviada para aprovação.');
    }

    public function approve(Booking $booking, BookingAvailability $availability): RedirectResponse
    {
        DB::transaction(function () use ($booking, $availability): void {
            Environment::query()->lockForUpdate()->findOrFail($booking->environment_id);
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            abort_unless($locked->status === 'pending', 409, 'A solicitação já foi decidida.');
            $problem = $availability->reason(
                $locked->environment_id,
                $locked->booking_date->toDateString(),
                $locked->starts_at,
                $locked->ends_at,
                $locked->id,
            );
            if ($problem) {
                abort(422, $problem);
            }
            $locked->update(['status' => 'approved']);
        });
        $this->notifyBooking($booking, 'Seu agendamento foi aprovado.');

        return back()->with('status', 'Agendamento aprovado.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['decision_reason' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($booking, $data): void {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            abort_unless($locked->status === 'pending', 409, 'Somente solicitações pendentes podem ser rejeitadas.');
            $locked->update(['status' => 'rejected', 'decision_reason' => $data['decision_reason']]);
        });
        $this->notifyBooking($booking, 'Seu agendamento foi rejeitado: '.$data['decision_reason']);

        return back()->with('status', 'Solicitação rejeitada.');
    }

    public function edit(Booking $booking): View
    {
        abort_unless($this->canEdit($booking, request()->user()), 403);

        return view('bookings.form', $this->bookingOptions($booking));
    }

    public function update(Request $request, Booking $booking, BookingAvailability $availability): RedirectResponse
    {
        abort_unless($this->canEdit($booking, $request->user()), 403);
        $data = $request->validate([
            'environment_id' => ['required', 'integer', Rule::exists('environments', 'id')->where('active', true)],
            'booking_type_id' => ['required', 'integer', Rule::exists('booking_types', 'id')->where('active', true)],
            'reason' => ['required', 'string', 'max:5000'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'change_reason' => ['required', 'string', 'max:2000'],
            'scope' => ['nullable', Rule::in(['one', 'following'])],
        ]);
        $scope = $data['scope'] ?? 'one';
        unset($data['change_reason']);
        unset($data['scope']);
        $targets = Booking::query()->whereKey($booking->id);
        if ($scope === 'following' && $booking->booking_series_id) {
            $targets = Booking::query()->where('booking_series_id', $booking->booking_series_id)
                ->whereDate('booking_date', '>=', $booking->booking_date);
        }
        $problem = null;
        DB::transaction(function () use ($targets, $scope, $data, $availability, &$problem): void {
            Environment::query()->whereKey($data['environment_id'])->lockForUpdate()->firstOrFail();
            $targetBookings = $targets->lockForUpdate()->get();
            $ignoredIds = $targetBookings->pluck('id')->all();
            foreach ($targetBookings as $index => $target) {
                $date = $scope === 'one' ? $data['booking_date'] : ($index === 0 ? $data['booking_date'] : $target->booking_date->toDateString());
                $problem = $availability->reason($data['environment_id'], $date, $data['starts_at'], $data['ends_at'], $ignoredIds);
                if ($problem) {
                    return;
                }
            }
            foreach ($targetBookings as $index => $target) {
                $changes = $data;
                if ($scope === 'following' && $index > 0) {
                    unset($changes['booking_date']);
                }
                $target->update($changes);
            }
        });
        if ($problem) {
            return back()->withErrors(['booking_date' => $problem])->withInput();
        }

        return redirect()->route('bookings.show', $booking)->with('status', 'Agendamento atualizado.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $isOwner = (int) $booking->requester_user_id === (int) $request->user()->id;
        $admin = $request->user()->hasPermissionTo('agendamentos.cancelar.qualquer');
        abort_unless($admin || ($isOwner && $booking->status === 'pending'
            && $request->user()->hasPermissionTo('agendamentos.cancelar.proprio')), 403);
        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:2000'],
            'scope' => ['nullable', Rule::in(['one', 'following'])],
        ]);
        abort_if($booking->status === 'cancelled', 409, 'O agendamento já está cancelado.');
        DB::transaction(function () use ($booking, $data, $admin): void {
            $targets = Booking::query()->whereKey($booking->id);
            if ($admin && $data['scope'] === 'following' && $booking->booking_series_id) {
                $targets = Booking::query()->where('booking_series_id', $booking->booking_series_id)
                    ->whereDate('booking_date', '>=', $booking->booking_date);
            }
            $targets->update(['status' => 'cancelled', 'cancellation_reason' => $data['cancellation_reason']]);
        });
        $this->notifyBooking($booking, 'Um agendamento foi cancelado: '.$data['cancellation_reason']);

        return back()->with('status', 'Agendamento cancelado.');
    }

    public function types(): View
    {
        return view('bookings.types', ['types' => BookingType::query()->orderBy('name')->get()]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:booking_types,name'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
        BookingType::query()->create($data + ['active' => true]);

        return back()->with('status', 'Tipo de agendamento cadastrado.');
    }

    public function updateType(Request $request, BookingType $type): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('booking_types', 'name')->ignore($type->id)],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
        $type->update($data);

        return back()->with('status', 'Tipo de agendamento atualizado.');
    }

    public function toggleType(BookingType $type): RedirectResponse
    {
        $type->update(['active' => ! $type->active]);

        return back()->with('status', $type->active ? 'Tipo reativado.' : 'Tipo desativado.');
    }

    public function teachers(): View
    {
        return view('bookings.teachers', ['teachers' => Teacher::query()->orderBy('name')->get()]);
    }

    public function storeTeacher(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:teachers,name']]);
        Teacher::query()->create($data + ['active' => true]);

        return back()->with('status', 'Docente cadastrado.');
    }

    public function updateTeacher(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('teachers', 'name')->ignore($teacher->id)]]);
        $teacher->update($data);

        return back()->with('status', 'Docente atualizado.');
    }

    public function toggleTeacher(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['active' => ! $teacher->active]);

        return back()->with('status', $teacher->active ? 'Docente reativado.' : 'Docente desativado.');
    }

    public function rules(): View
    {
        return view('bookings.rules', [
            'environments' => Environment::query()->orderBy('name')->get(),
            'rules' => DB::table('booking_rules')->orderBy('environment_id')->get(),
            'blocks' => DB::table('booking_blocks')->orderByDesc('created_at')->get(),
        ]);
    }

    public function saveRules(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'environment_id' => ['nullable', 'integer', 'exists:environments,id'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:0,6', 'distinct'],
            'time_ranges' => ['required', 'array', 'min:1'],
            'time_ranges.*.starts_at' => ['required', 'date_format:H:i'],
            'time_ranges.*.ends_at' => ['required', 'date_format:H:i', 'after:time_ranges.*.starts_at'],
        ]);
        $data['weekdays'] = array_map('intval', $data['weekdays']);
        foreach ($data['time_ranges'] as $range) {
            if ($range['starts_at'] >= $range['ends_at']) {
                return back()->withErrors(['time_ranges' => 'Cada faixa deve terminar após seu início.'])->withInput();
            }
        }
        $ruleQuery = DB::table('booking_rules')->when(
            $data['environment_id'] ?? null,
            fn ($query, $id) => $query->where('environment_id', $id),
            fn ($query) => $query->whereNull('environment_id'),
        );
        $rule = $ruleQuery->first();
        $values = ['weekdays' => json_encode($data['weekdays']), 'time_ranges' => json_encode($data['time_ranges']), 'updated_at' => now()];
        if ($rule) {
            DB::table('booking_rules')->where('id', $rule->id)->update($values);
        } else {
            DB::table('booking_rules')->insert($values + [
                'environment_id' => $data['environment_id'] ?? null,
                'created_at' => now(),
            ]);
        }

        return back()->with('status', 'Regras de agendamento salvas.');
    }

    public function removeEnvironmentRule(int $rule): RedirectResponse
    {
        DB::table('booking_rules')->where('id', $rule)->whereNotNull('environment_id')->delete();

        return back()->with('status', 'Regra específica removida.');
    }

    public function storeBlock(Request $request): RedirectResponse
    {
        $data = $this->validateBlock($request);
        $approvedQuery = Booking::query()->where('status', 'approved')
            ->when($data['environment_id'] ?? null, fn ($query, $id) => $query->where('environment_id', $id))
            ->when($data['specific_date'] ?? null, fn ($query, $date) => $query->whereDate('booking_date', $date));
        $approved = $approvedQuery->get()->filter(function (Booking $booking) use ($data): bool {
            return ! isset($data['weekday']) || $booking->booking_date->dayOfWeek === (int) $data['weekday'];
        })->count();
        DB::table('booking_blocks')->insert($data + ['active' => true, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('status', "Bloqueio criado. Há {$approved} agendamento(s) aprovado(s) potencialmente afetado(s); eles não foram cancelados.");
    }

    public function deleteBlock(int $block): RedirectResponse
    {
        DB::table('booking_blocks')->where('id', $block)->delete();

        return back()->with('status', 'Bloqueio removido.');
    }

    public function editBlock(int $block): View
    {
        $item = DB::table('booking_blocks')->where('id', $block)->first();
        abort_unless($item, 404);

        return view('bookings.block-form', [
            'block' => $item,
            'environments' => Environment::query()->orderBy('name')->get(),
        ]);
    }

    public function updateBlock(Request $request, int $block): RedirectResponse
    {
        abort_unless(DB::table('booking_blocks')->where('id', $block)->exists(), 404);
        $data = $this->validateBlock($request);
        DB::table('booking_blocks')->where('id', $block)->update($data + ['updated_at' => now()]);

        return redirect()->route('bookings.rules')->with('status', 'Bloqueio atualizado.');
    }

    public function fixedForm(): View
    {
        return view('bookings.fixed-form', $this->bookingOptions());
    }

    public function previewSeries(Request $request, BookingAvailability $availability): View
    {
        $data = $this->validateSeries($request);
        $dates = $this->seriesDates($data);
        $occurrences = collect($dates)->map(fn (string $date): array => [
            'date' => $date,
            'problem' => $availability->reason($data['environment_id'], $date, $data['starts_at'], $data['ends_at']),
        ]);

        return view('bookings.fixed-preview', ['data' => $data, 'occurrences' => $occurrences]);
    }

    public function storeSeries(Request $request, BookingAvailability $availability): RedirectResponse
    {
        $data = $this->validateSeries($request);
        [$seriesId, $createdCount] = DB::transaction(function () use ($data, $availability): array {
            Environment::query()->whereKey($data['environment_id'])->lockForUpdate()->firstOrFail();
            $occurrences = collect($this->seriesDates($data))->filter(fn (string $date): bool =>
                $availability->reason($data['environment_id'], $date, $data['starts_at'], $data['ends_at']) === null
            );
            abort_if($occurrences->isEmpty(), 422, 'Não há ocorrências válidas para criar.');
            $series = BookingSeries::query()->create(collect($data)->except('confirm')->all());
            foreach ($occurrences as $date) {
                $series->bookings()->create([
                    'requester_user_id' => $data['requester_user_id'] ?: null,
                    'teacher_id' => $data['teacher_id'] ?: null,
                    'environment_id' => $data['environment_id'],
                    'booking_type_id' => $data['booking_type_id'],
                    'reason' => $data['reason'],
                    'booking_date' => $date,
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],
                    'status' => 'approved',
                ]);
            }
            return [$series->id, $occurrences->count()];
        });
        $this->notifyBooking(Booking::query()->where('booking_series_id', $seriesId)->first(), 'Uma série fixa de agendamentos foi criada.');

        return redirect()->route('bookings.calendar')->with('status', $createdCount.' ocorrência(s) fixa(s) criada(s).');
    }

    private function bookingOptions(?Booking $booking = null): array
    {
        return [
            'booking' => $booking ?? new Booking(),
            'environments' => Environment::query()->where('active', true)->orderBy('name')->get(),
            'types' => BookingType::query()->where('active', true)->orderBy('name')->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'teachers' => Teacher::query()->where('active', true)->orderBy('name')->get(),
        ];
    }

    private function canEdit(Booking $booking, User $user): bool
    {
        return $user->hasPermissionTo('agendamentos.editar.qualquer')
            || ((int) $booking->requester_user_id === (int) $user->id && $user->hasPermissionTo('agendamentos.editar.proprio'));
    }

    private function validateSeries(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'requester_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true), 'required_without:teacher_id'],
            'teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')->where('active', true), 'required_without:requester_user_id'],
            'environment_id' => ['required', 'integer', Rule::exists('environments', 'id')->where('active', true)],
            'booking_type_id' => ['required', 'integer', Rule::exists('booking_types', 'id')->where('active', true)],
            'reason' => ['required', 'string', 'max:5000'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:0,6', 'distinct'],
            'starts_on' => ['required', 'date', 'after_or_equal:today'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
        ]);
        if ((bool) ($data['requester_user_id'] ?? null) === (bool) ($data['teacher_id'] ?? null)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'requester_user_id' => 'Informe exatamente um solicitante: usuário ou docente.',
            ]);
        }
        $data['weekdays'] = array_map('intval', $data['weekdays']);

        return $data;
    }

    private function validateBlock(Request $request): array
    {
        $data = $request->validate([
            'environment_id' => ['nullable', 'integer', 'exists:environments,id'],
            'block_type' => ['required', Rule::in(['date', 'date_time', 'weekday', 'weekday_time'])],
            'specific_date' => ['required_if:block_type,date,date_time', 'nullable', 'date'],
            'weekday' => ['required_if:block_type,weekday,weekday_time', 'nullable', 'integer', 'between:0,6'],
            'starts_at' => ['required_if:block_type,date_time,weekday_time', 'nullable', 'date_format:H:i'],
            'ends_at' => ['required_if:block_type,date_time,weekday_time', 'nullable', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        if (isset($data['starts_at'], $data['ends_at']) && $data['ends_at'] <= $data['starts_at']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'ends_at' => 'O horário de fim deve ser posterior ao horário de início.',
            ]);
        }
        if (in_array($data['block_type'], ['date', 'date_time'], true)) {
            $data['weekday'] = null;
        } else {
            $data['specific_date'] = null;
        }
        if (in_array($data['block_type'], ['date', 'weekday'], true)) {
            $data['starts_at'] = null;
            $data['ends_at'] = null;
        }

        return $data;
    }

    private function seriesDates(array $data): array
    {
        $dates = [];
        $cursor = Carbon::parse($data['starts_on']);
        $end = Carbon::parse($data['ends_on']);
        while ($cursor->lte($end)) {
            if (in_array($cursor->dayOfWeek, $data['weekdays'], true)) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return $dates;
    }

    private function notifyBooking(?Booking $booking, string $message): void
    {
        if (! $booking) {
            return;
        }
        $booking->loadMissing(['requester', 'environment.group']);
        if ($booking->requester?->email) {
            Mail::raw($message, fn ($mail) => $mail->to($booking->requester->email)->subject('Atualização de agendamento'));
        }
        foreach ($booking->environment?->group?->notification_emails ?? [] as $email) {
            Mail::raw($message, fn ($mail) => $mail->to($email)->subject('Atualização de agendamento'));
        }
    }
}
