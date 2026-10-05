<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use App\Models\EnvironmentGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnvironmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Environment::query()->with('group')->withCount(['bookings']);
        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        return view('environments.index', ['environments' => $query->orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return $this->form(new Environment());
    }

    public function store(Request $request): RedirectResponse
    {
        $environment = Environment::query()->create($this->validateEnvironment($request) + ['active' => true]);
        if ($request->hasFile('photo')) {
            $environment->update(['photo_path' => $request->file('photo')->store('environments', 'public')]);
        }

        return redirect()->route('environments.index')->with('status', 'Ambiente cadastrado.');
    }

    public function edit(Environment $environment): View
    {
        return $this->form($environment);
    }

    public function update(Request $request, Environment $environment): RedirectResponse
    {
        $environment->update($this->validateEnvironment($request, $environment));
        if ($request->hasFile('photo')) {
            if ($environment->photo_path) {
                Storage::disk('public')->delete($environment->photo_path);
            }
            $environment->update(['photo_path' => $request->file('photo')->store('environments', 'public')]);
        }

        return redirect()->route('environments.index')->with('status', 'Ambiente atualizado.');
    }

    public function toggle(Environment $environment): RedirectResponse
    {
        $futureBookings = $environment->bookings()->whereIn('status', ['pending', 'approved'])
            ->whereDate('booking_date', '>=', today())->count();
        $environment->update(['active' => ! $environment->active]);

        return back()->with('status', ($environment->active ? 'Ambiente reativado.' : 'Ambiente desativado.')
            ." {$futureBookings} agendamento(s) futuro(s) ou pendente(s) relacionado(s).");
    }

    public function groups(): View
    {
        return view('environment-groups.index', [
            'groups' => EnvironmentGroup::query()->withCount('environments')->orderBy('name')->paginate(15),
        ]);
    }

    public function createGroup(): View
    {
        return $this->groupForm(new EnvironmentGroup());
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $group = DB::transaction(function () use ($request): EnvironmentGroup {
            $group = EnvironmentGroup::query()->create($this->validateGroup($request) + ['active' => true]);
            $this->syncGroupEnvironments($request, $group);

            return $group;
        });

        return redirect()->route('environment-groups.index')->with('status', 'Grupo de ambientes cadastrado.');
    }

    public function editGroup(EnvironmentGroup $environmentGroup): View
    {
        return $this->groupForm($environmentGroup);
    }

    public function updateGroup(Request $request, EnvironmentGroup $environmentGroup): RedirectResponse
    {
        DB::transaction(function () use ($request, $environmentGroup): void {
            $environmentGroup->update($this->validateGroup($request, $environmentGroup));
            $this->syncGroupEnvironments($request, $environmentGroup);
        });

        return redirect()->route('environment-groups.index')->with('status', 'Grupo de ambientes atualizado.');
    }

    public function toggleGroup(EnvironmentGroup $environmentGroup): RedirectResponse
    {
        $affected = $environmentGroup->environments()->count();
        if ($environmentGroup->active) {
            $environmentGroup->environments()->update(['environment_group_id' => null]);
        }
        $environmentGroup->update(['active' => ! $environmentGroup->active]);

        return back()->with('status', ($environmentGroup->active ? 'Grupo reativado.' : 'Grupo desativado.')
            ." {$affected} ambiente(s) afetado(s).");
    }

    private function form(Environment $environment): View
    {
        return view('environments.form', [
            'environment' => $environment,
            'groups' => EnvironmentGroup::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    private function groupForm(EnvironmentGroup $group): View
    {
        return view('environment-groups.form', [
            'group' => $group->load('environments'),
            'environments' => Environment::query()->orderBy('name')->get(),
        ]);
    }

    private function validateEnvironment(Request $request, ?Environment $environment = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', Rule::unique('environments', 'code')->ignore($environment?->id)],
            'chairs_count' => ['required', 'integer', 'min:0'],
            'benches_count' => ['required', 'integer', 'min:0'],
            'description' => ['required', 'string', 'max:5000'],
            'environment_group_id' => ['nullable', 'integer', Rule::exists('environment_groups', 'id')->where('active', true)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);
        unset($data['photo']);

        return $data;
    }

    private function validateGroup(Request $request, ?EnvironmentGroup $group = null): array
    {
        $input = $request->validate(['notification_emails_text' => ['required', 'string', 'max:5000']]);
        $emails = preg_split('/[\r\n,;]+/', $input['notification_emails_text'], -1, PREG_SPLIT_NO_EMPTY);
        $request->merge(['notification_emails' => array_values(array_map('trim', $emails ?: []))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('environment_groups', 'name')->ignore($group?->id)],
            'notification_emails' => ['required', 'array', 'min:1'],
            'notification_emails.*' => ['required', 'email', 'max:255', 'distinct'],
            'environment_ids' => ['sometimes', 'array'],
            'environment_ids.*' => ['integer', 'distinct', 'exists:environments,id'],
        ]);
        unset($data['environment_ids']);

        return $data;
    }

    private function syncGroupEnvironments(Request $request, EnvironmentGroup $group): void
    {
        $ids = $request->validate([
            'environment_ids' => ['sometimes', 'array'],
            'environment_ids.*' => ['integer', 'distinct', 'exists:environments,id'],
        ])['environment_ids'] ?? [];
        abort_if(! $group->active && $ids !== [], 422, 'Reative o grupo antes de associar ambientes.');
        if ($ids !== []) {
            Environment::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        }
        $conflict = Environment::query()->whereIn('id', $ids)->whereNotNull('environment_group_id')
            ->where('environment_group_id', '!=', $group->id)->exists();
        abort_unless(! $conflict, 422, 'Um ambiente selecionado já pertence a outro grupo.');
        Environment::query()->where('environment_group_id', $group->id)->whereNotIn('id', $ids)->update(['environment_group_id' => null]);
        Environment::query()->whereIn('id', $ids)->update(['environment_group_id' => $group->id]);
    }
}
