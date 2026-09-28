<?php

namespace App\Http\Controllers;

use App\Models\EmailDomain;
use App\Models\User;
use App\Models\UserAuditLog;
use App\Models\UserProfileOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending_email', 'email_confirmed', 'approved', 'rejected'])],
            'profile' => ['nullable', Rule::in(config('users.profiles'))],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('registration_status', $status))
            ->when($filters['profile'] ?? null, fn ($query, $profile) => $query->where('profile', $profile))
            ->when($filters['search'] ?? null, function ($query, $term): void {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('functional_id', 'like', '%'.$term.'%'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'filters'));
    }

    public function profile(Request $request): View
    {
        abort_unless($request->user()->hasPermissionTo('usuarios.visualizar.proprio'), 403);

        return view('users.show', ['user' => $request->user(), 'isOwnProfile' => true]);
    }

    public function show(User $user): View
    {
        abort_unless(auth()->user()->hasPermissionTo('usuarios.visualizar.qualquer'), 403);

        return view('users.show', ['user' => $user, 'isOwnProfile' => false]);
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'options' => UserProfileOption::query()->where('active', true)->get()->groupBy('type'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'functional_id' => ['required', 'string', 'max:100', Rule::unique('users', 'functional_id')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30'],
            'course' => [Rule::requiredIf($user->profile === 'aluno'), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'course')->where('active', true))],
            'job_title' => [Rule::requiredIf(in_array($user->profile, ['professor', 'tecnico'], true)), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'job_title')->where('active', true))],
            'employment_link' => [Rule::requiredIf(in_array($user->profile, ['professor', 'tecnico'], true)), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'employment_link')->where('active', true))],
        ]);

        if ($validated['email'] !== $user->email) {
            $domain = strtolower(substr(strrchr($validated['email'], '@'), 1));
            $allowed = EmailDomain::query()->exists()
                ? EmailDomain::query()->where('domain', $domain)->where('active', true)->exists()
                : in_array($domain, config('users.allowed_email_domains', []), true);
            abort_unless($allowed, 422, 'Domínio de e-mail não autorizado.');
        }

        $before = $user->only(['name', 'email', 'functional_id', 'phone', 'course', 'job_title', 'employment_link']);
        $user->update($validated);
        UserAuditLog::query()->create([
            'actor_id' => $request->user()->id,
            'user_id' => $user->id,
            'action' => 'user_edited',
            'details' => ['before' => $before, 'after' => $validated],
        ]);

        if ($before['email'] !== $user->email && config('users.administrator_email')) {
            Mail::raw('O e-mail do seu cadastro foi alterado por um administrador.', function ($message) use ($user): void {
                $message->to($user->email)->subject('E-mail do cadastro alterado');
            });
        }

        return redirect()->route('users.show', $user)->with('status', 'Cadastro atualizado.');
    }
}
