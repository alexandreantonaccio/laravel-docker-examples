<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Models\UserAuditLog;
use App\Models\UserProfileOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAdministrationController extends Controller
{
    public function pending(): View
    {
        return view('users.pending', [
            'users' => User::query()->where('registration_status', 'email_confirmed')->latest()->paginate(15),
            'groups' => PermissionGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->registration_status === 'email_confirmed', 409);
        $validated = $request->validate([
            'group_ids' => ['required', 'array', 'min:1'],
            'group_ids.*' => ['integer', 'distinct', 'exists:permission_groups,id'],
            'documentation_validated_at' => ['nullable', 'date'],
            'documentation_notes' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $user, $validated): void {
            $user->groups()->sync($validated['group_ids']);
            $user->forceFill([
                'registration_status' => 'approved',
                'documentation_validated_at' => $validated['documentation_validated_at'] ?? now(),
                'documentation_validated_by' => $request->user()->id,
                'documentation_notes' => $validated['documentation_notes'],
            ])->save();
            UserAuditLog::query()->create([
                'actor_id' => $request->user()->id,
                'user_id' => $user->id,
                'action' => 'registration_approved',
                'details' => ['groups' => $validated['group_ids'], 'notes' => $validated['documentation_notes']],
            ]);
        });

        Mail::raw('Seu cadastro foi aprovado e seu acesso está liberado.', function ($message) use ($user): void {
            $message->to($user->email)->subject('Cadastro aprovado');
        });

        return back()->with('status', 'Cadastro aprovado e associado aos grupos selecionados.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->registration_status === 'email_confirmed', 409);
        $validated = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);
        $user->groups()->detach();
            $user->permissionAdjustments()->delete();
        $user->forceFill([
            'registration_status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'documentation_validated_at' => now(),
            'documentation_validated_by' => $request->user()->id,
            'documentation_notes' => $validated['rejection_reason'],
        ])->save();
        UserAuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'user_id' => $user->id,
            'action' => 'registration_rejected',
            'details' => ['reason' => $validated['rejection_reason']],
        ]);
        Mail::raw('Seu cadastro foi rejeitado. Motivo: '.$validated['rejection_reason'], function ($message) use ($user): void {
            $message->to($user->email)->subject('Resultado da análise cadastral');
        });

        return back()->with('status', 'Cadastro rejeitado e decisão registrada.');
    }

    public function permissions(User $user): View
    {
        $permissions = Permission::query()->with(['groups' => fn ($query) => $query->whereHas('users', fn ($users) => $users->whereKey($user->id))])
            ->orderBy('key')->get();
        $adjustments = $user->permissionAdjustments()->pluck('effect', 'permission_id');

        return view('users.permissions', [
            'user' => $user,
            'permissions' => $permissions,
            'adjustments' => $adjustments,
            'effective' => $user->effectivePermissions(),
        ]);
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'adjustments' => ['present', 'array'],
            'adjustments.*' => ['required', Rule::in(['inherit', 'grant', 'deny'])],
        ]);
        $adjustments = collect($validated['adjustments'])->mapWithKeys(function (string $effect, string $permissionId): array {
            abort_unless(Permission::query()->whereKey($permissionId)->exists(), 422);

            return [(int) $permissionId => $effect];
        });

        DB::transaction(function () use ($request, $user, $adjustments): void {
            $groupPermissionIds = $user->groups()->with('permissions:id')->get()
                ->flatMap(fn (PermissionGroup $group) => $group->permissions->pluck('id'))->unique();
            $user->permissionAdjustments()->delete();

            foreach ($adjustments as $permissionId => $effect) {
                if ($effect === 'inherit' || ($effect === 'grant' && $groupPermissionIds->contains($permissionId))) {
                    continue;
                }
                $user->permissionAdjustments()->create([
                    'permission_id' => $permissionId,
                    'effect' => $effect,
                    'updated_by' => $request->user()->id,
                ]);
            }
            UserAuditLog::query()->create([
                'actor_id' => $request->user()->id,
                'user_id' => $user->id,
                'action' => 'permissions_updated',
                'details' => ['adjustments' => $adjustments->all()],
            ]);
        });

        return back()->with('status', 'Permissões individuais atualizadas.');
    }

    public function editProfile(User $user): View
    {
        return view('users.profile-edit', [
            'user' => $user,
            'options' => UserProfileOption::query()->where('active', true)->get()->groupBy('type'),
        ]);
    }

    public function updateProfile(Request $request, User $user): RedirectResponse
    {
        $profile = $request->input('profile');
        $oldProfile = $user->profile;
        $validated = $request->validate([
            'profile' => ['required', Rule::in(config('users.profiles'))],
            'course' => [Rule::requiredIf($profile === 'aluno'), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'course')->where('active', true))],
            'job_title' => [Rule::requiredIf($profile === 'tecnico'), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'job_title')->where('active', true))],
            'employment_link' => [Rule::requiredIf(in_array($profile, ['professor', 'tecnico'], true)), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'employment_link')->where('active', true))],
            'documentation_notes' => [Rule::requiredIf($profile === 'aluno'), 'nullable', 'string', 'max:2000'],
            'documentation_notes' => [Rule::requiredIf($profile === 'aluno' && $oldProfile !== 'aluno'), 'nullable', 'string', 'max:2000'],
        ]);
        $newSpecificFields = match ($profile) {
            'aluno' => ['course' => $validated['course']],
            'professor' => [
                'job_title' => config('users.professor_job_title'),
                'employment_link' => $validated['employment_link'],
            ],
            default => [
                'job_title' => $validated['job_title'],
                'employment_link' => $validated['employment_link'],
            ],
        };
        $oldSpecificFields = match ($oldProfile) {
            'aluno' => $user->only(['course', 'enrollment_proof_path']),
            'professor', 'tecnico' => $user->only(['job_title', 'employment_link']),
            default => [],
        };
        $archived = array_filter($oldSpecificFields, function ($value, $field) use ($newSpecificFields): bool {
            return ! array_key_exists($field, $newSpecificFields) || $newSpecificFields[$field] !== $value;
        }, ARRAY_FILTER_USE_BOTH);
        DB::transaction(function () use ($request, $user, $validated, $oldProfile, $archived, $profile): void {
            $user->profileHistory()->create([
                'changed_by' => $request->user()->id,
                'old_profile' => $oldProfile,
                'new_profile' => $profile,
                'archived_fields' => $archived,
                'validation_notes' => $validated['documentation_notes'] ?? null,
            ]);
            $user->forceFill([
                'profile' => $profile,
                'course' => $profile === 'aluno' ? ($validated['course'] ?? null) : null,
                'job_title' => $profile === 'professor' ? config('users.professor_job_title') : ($profile === 'tecnico' ? ($validated['job_title'] ?? null) : null),
                'employment_link' => in_array($profile, ['professor', 'tecnico'], true) ? ($validated['employment_link'] ?? null) : null,
                'enrollment_proof_path' => $profile === 'aluno' ? $user->enrollment_proof_path : null,
                'documentation_validated_at' => $profile === 'aluno' && $oldProfile !== 'aluno' ? now() : $user->documentation_validated_at,
                'documentation_validated_by' => $profile === 'aluno' && $oldProfile !== 'aluno' ? $request->user()->id : $user->documentation_validated_by,
                'documentation_notes' => $validated['documentation_notes'] ?? $user->documentation_notes,
            ])->save();
            UserAuditLog::query()->create([
                'actor_id' => $request->user()->id,
                'user_id' => $user->id,
                'action' => 'profile_changed',
                'details' => ['from' => $oldProfile, 'to' => $profile, 'archived' => $archived],
            ]);
        });

        Mail::raw('O perfil do seu cadastro foi alterado para '.ucfirst($profile).'.', function ($message) use ($user): void {
            $message->to($user->email)->subject('Perfil cadastral alterado');
        });

        return redirect()->route('users.show', $user)->with('status', 'Perfil alterado e histórico registrado.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'Não é permitido desativar a própria conta.');
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:2000']]);
        $user->forceFill([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => $request->user()->id,
            'deactivation_reason' => $validated['reason'] ?? null,
        ])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();
        UserAuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'user_id' => $user->id,
            'action' => 'user_deactivated',
            'details' => ['reason' => $validated['reason'] ?? null],
        ]);
        Mail::raw('Sua conta foi desativada. Procure a sala de apoio para solicitar a reativação.', function ($message) use ($user): void {
            $message->to($user->email)->subject('Conta desativada');
        });

        return back()->with('status', 'Usuário desativado; as sessões foram encerradas.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $user->forceFill([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
            'deactivation_reason' => null,
        ])->save();
        UserAuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'user_id' => $user->id,
            'action' => 'user_activated',
        ]);
        Mail::raw('Sua conta foi reativada.', function ($message) use ($user): void {
            $message->to($user->email)->subject('Conta reativada');
        });

        return back()->with('status', 'Usuário reativado.');
    }
}