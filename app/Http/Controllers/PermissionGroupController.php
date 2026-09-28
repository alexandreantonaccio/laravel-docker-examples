<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionGroupController extends Controller
{
    public function index(): View
    {
        return view('groups.index', ['groups' => PermissionGroup::query()->withCount(['users', 'permissions'])->orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('groups.form', ['group' => new PermissionGroup(), 'permissions' => Permission::query()->orderBy('key')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGroup($request);
        $group = DB::transaction(function () use ($validated): PermissionGroup {
            $group = PermissionGroup::query()->create($validated);
            $group->permissions()->sync($validated['permission_ids'] ?? []);

            return $group;
        });

        return redirect()->route('groups.show', $group)->with('status', 'Grupo criado.');
    }

    public function show(PermissionGroup $group): View
    {
        $group->loadCount('users')->load(['permissions', 'users' => fn ($query) => $query->orderBy('name')]);

        return view('groups.show', ['group' => $group, 'users' => User::query()->orderBy('name')->get()]);
    }

    public function edit(PermissionGroup $group): View
    {
        return view('groups.form', ['group' => $group->load('permissions'), 'permissions' => Permission::query()->orderBy('key')->get()]);
    }

    public function update(Request $request, PermissionGroup $group): RedirectResponse
    {
        $validated = $this->validateGroup($request, $group);
        DB::transaction(function () use ($validated, $group): void {
            $group->update($validated);
            $group->permissions()->sync($validated['permission_ids'] ?? []);
        });

        return redirect()->route('groups.show', $group)->with('status', 'Grupo atualizado.');
    }

    public function destroy(PermissionGroup $group): RedirectResponse
    {
        $affected = $group->users()->count();
        $group->delete();

        return redirect()->route('groups.index')->with('status', "Grupo removido. {$affected} usuário(s) perderam as permissões herdadas deste grupo.");
    }

    public function syncUsers(Request $request, PermissionGroup $group): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
        $group->users()->sync($validated['user_ids'] ?? []);

        return back()->with('status', 'Membros do grupo atualizados.');
    }

    private function validateGroup(Request $request, ?PermissionGroup $group = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permission_groups', 'name')->ignore($group?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ]);
    }
}