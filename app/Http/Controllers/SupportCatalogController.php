<?php

namespace App\Http\Controllers;

use App\Models\EmailDomain;
use App\Models\User;
use App\Models\UserProfileOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportCatalogController extends Controller
{
    private const CATALOGS = [
        'cursos' => ['type' => 'course', 'label' => 'Cursos', 'permission' => 'cursos'],
        'cargos' => ['type' => 'job_title', 'label' => 'Cargos', 'permission' => 'cargos'],
        'vinculos' => ['type' => 'employment_link', 'label' => 'Vínculos', 'permission' => 'vinculos'],
    ];

    public function index(Request $request, string $catalog): View
    {
        $config = $this->catalog($catalog);
        abort_unless($request->user()->hasPermissionTo($config['permission'].'.listar'), 403);
        $query = UserProfileOption::query()->where('type', $config['type']);
        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }
        $items = $query->orderBy('value')->get()->map(function (UserProfileOption $item) use ($config): UserProfileOption {
            $column = $config['type'];
            $item->setAttribute('usage_count', User::query()->where($column, $item->value)->count());

            return $item;
        });

        return view('catalogs.index', ['catalog' => $catalog, 'config' => $config, 'items' => $items]);
    }

    public function create(string $catalog): View
    {
        abort_unless(request()->user()->hasPermissionTo($this->catalog($catalog)['permission'].'.criar'), 403);

        return view('catalogs.form', [
            'catalog' => $catalog,
            'config' => $this->catalog($catalog),
            'item' => new UserProfileOption(),
        ]);
    }

    public function store(Request $request, string $catalog): RedirectResponse
    {
        $config = $this->catalog($catalog);
        abort_unless($request->user()->hasPermissionTo($config['permission'].'.criar'), 403);
        $value = $this->validatedName($request, $config['type']);
        UserProfileOption::query()->create(['type' => $config['type'], 'value' => $value, 'active' => true]);

        return redirect()->route('catalogs.index', $catalog)->with('status', "{$config['label']} cadastrado(a).");
    }

    public function edit(string $catalog, int $item): View
    {
        $config = $this->catalog($catalog);
        abort_unless(request()->user()->hasPermissionTo($config['permission'].'.editar'), 403);

        return view('catalogs.form', [
            'catalog' => $catalog,
            'config' => $config,
            'item' => UserProfileOption::query()->where('type', $config['type'])->findOrFail($item),
        ]);
    }

    public function update(Request $request, string $catalog, int $item): RedirectResponse
    {
        $config = $this->catalog($catalog);
        abort_unless($request->user()->hasPermissionTo($config['permission'].'.editar'), 403);
        $record = UserProfileOption::query()->where('type', $config['type'])->findOrFail($item);
        $record->update(['value' => $this->validatedName($request, $config['type'], $record)]);

        return redirect()->route('catalogs.index', $catalog)->with('status', "{$config['label']} atualizado(a).");
    }

    public function toggle(Request $request, string $catalog, int $item): RedirectResponse
    {
        $config = $this->catalog($catalog);
        abort_unless($request->user()->hasPermissionTo($config['permission'].'.desativar'), 403);
        $record = UserProfileOption::query()->where('type', $config['type'])->findOrFail($item);
        $record->update(['active' => ! $record->active]);

        return back()->with('status', $record->active ? 'Registro reativado.' : 'Registro desativado.');
    }

    public function domains(Request $request): View
    {
        $query = EmailDomain::query();
        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        $domains = $query->orderBy('domain')->get()->map(function (EmailDomain $domain): EmailDomain {
            $domain->setAttribute('usage_count', User::query()->where('email', 'like', '%@'.$domain->domain)->count());

            return $domain;
        });

        return view('catalogs.domains', compact('domains'));
    }

    public function createDomain(): View
    {
        return view('catalogs.domain-form', ['domain' => new EmailDomain()]);
    }

    public function storeDomain(Request $request): RedirectResponse
    {
        $validated = $this->validateDomain($request);
        EmailDomain::query()->create(['domain' => strtolower($validated['domain']), 'active' => true]);

        return redirect()->route('domains.index')->with('status', 'Domínio cadastrado.');
    }

    public function editDomain(EmailDomain $domain): View
    {
        return view('catalogs.domain-form', compact('domain'));
    }

    public function updateDomain(Request $request, EmailDomain $domain): RedirectResponse
    {
        $validated = $this->validateDomain($request, $domain);
        $domain->update(['domain' => strtolower($validated['domain'])]);

        return redirect()->route('domains.index')->with('status', 'Domínio atualizado.');
    }

    public function toggleDomain(EmailDomain $domain): RedirectResponse
    {
        $domain->update(['active' => ! $domain->active, 'is_default' => $domain->active ? false : $domain->is_default]);
        $usage = User::query()->where('email', 'like', '%@'.$domain->domain)->count();

        return back()->with('status', ($domain->active ? 'Domínio reativado.' : 'Domínio desativado.')." {$usage} usuário(s) usam este domínio.");
    }

    public function setDefaultDomain(EmailDomain $domain): RedirectResponse
    {
        abort_unless($domain->active, 422, 'Somente domínios ativos podem ser padrão.');
        DB::transaction(function () use ($domain): void {
            EmailDomain::query()->update(['is_default' => false]);
            $domain->update(['is_default' => true]);
        });

        return back()->with('status', 'Domínio padrão atualizado.');
    }

    private function catalog(string $catalog): array
    {
        abort_unless(isset(self::CATALOGS[$catalog]), 404);

        return self::CATALOGS[$catalog];
    }

    private function validatedName(Request $request, string $type, ?UserProfileOption $record = null): string
    {
        $validated = $request->validate(['value' => ['required', 'string', 'max:255']]);
        $value = trim($validated['value']);
        if ($type === 'course') {
            $value = mb_strtoupper($value);
        }
        $request->merge(['value' => $value]);
        $request->validate([
            'value' => [
                Rule::unique('user_profile_options', 'value')
                    ->where(fn ($query) => $query->where('type', $type))
                    ->ignore($record?->id),
            ],
        ]);

        return $value;
    }

    private function validateDomain(Request $request, ?EmailDomain $domain = null): array
    {
        $validated = $request->validate(['domain' => ['required', 'string', 'max:253']]);
        $request->merge(['domain' => strtolower(trim($validated['domain']))]);

        return $request->validate([
            'domain' => [
                'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\\.)+[a-z]{2,63}$/i',
                Rule::unique('email_domains', 'domain')->ignore($domain?->id),
            ],
        ]);
    }
}
