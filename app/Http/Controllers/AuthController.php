<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\EmailDomain;
use App\Models\UserAuditLog;
use App\Models\UserProfileOption;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $key = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, config('users.login_attempts'))) {
            return back()->withErrors([
                'email' => 'Muitas tentativas. Tente novamente em '.RateLimiter::availableIn($key).' segundos.',
            ])->onlyInput('email');
        }

        $user = User::query()->where('email', $credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (! $user->is_active) {
                return back()->withErrors(['email' => 'Conta desativada. Procure a sala de apoio para reativação.'])->onlyInput('email');
            }
            if ($user->registration_status === 'pending_email') {
                return back()->withErrors(['email' => 'Confirme seu e-mail antes de entrar.'])->onlyInput('email');
            }
            if (! $user->canLogin()) {
                return back()->withErrors(['email' => 'E-mail ou senha inválidos.'])->onlyInput('email');
            }

            RateLimiter::clear($key);
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        RateLimiter::hit($key, config('users.login_decay_minutes') * 60);

        return back()->withErrors(['email' => 'E-mail ou senha inválidos.'])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'options' => UserProfileOption::query()->where('active', true)->get()->groupBy('type'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        User::query()
            ->where('registration_status', 'pending_email')
            ->where('created_at', '<', now()->subHours(config('users.pending_registration_expiration_hours')))
            ->where(fn ($query) => $query
                ->where('email', $request->input('email'))
                ->orWhere('functional_id', $request->input('functional_id')))
            ->delete();

        $profile = $request->input('profile');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'functional_id' => ['required', 'string', 'max:100', 'unique:users,functional_id'],
            'phone' => ['required', 'string', 'max:30'],
            'profile' => ['required', Rule::in(config('users.profiles'))],
            'course' => [Rule::requiredIf($profile === 'aluno'), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'course')->where('active', true))],
            'job_title' => [Rule::requiredIf($profile === 'tecnico'), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'job_title')->where('active', true))],
            'employment_link' => [Rule::requiredIf(in_array($profile, ['professor', 'tecnico'], true)), 'nullable', Rule::exists('user_profile_options', 'value')->where(fn ($query) => $query->where('type', 'employment_link')->where('active', true))],
            'enrollment_proof' => [Rule::requiredIf($profile === 'aluno'), 'nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:1024'],
            'password' => ['required', 'confirmed', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ]);

        $domain = Str::lower(Str::afterLast($validated['email'], '@'));
        $allowed = EmailDomain::query()->exists()
            ? EmailDomain::query()->where('domain', $domain)->where('active', true)->exists()
            : in_array($domain, config('users.allowed_email_domains', []), true);
        if (! $allowed) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'O e-mail informado deve pertencer a um domínio autorizado.',
            ]);
        }

        if ($profile === 'professor') {
            $validated['job_title'] = config('users.professor_job_title');
        }

        if ($profile !== 'aluno') {
            unset($validated['course'], $validated['enrollment_proof']);
        }
        unset($validated['enrollment_proof']);

        $user = User::query()->create($validated + [
            'registration_status' => 'pending_email',
            'verification_sent_at' => now(),
        ]);

        if ($request->hasFile('enrollment_proof')) {
            $user->update(['enrollment_proof_path' => $request->file('enrollment_proof')->store('enrollment-proofs')]);
        }

        $user->sendEmailVerificationNotification();

        return redirect()->route('login')->with('status', 'Cadastro iniciado. Confira seu e-mail para confirmar o endereço.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()->find($id);
        if (! $user) {
            return redirect()->route('register')->withErrors(['email' => 'Este cadastro não está mais disponível. Realize um novo cadastro.']);
        }

        if ($user->registration_status === 'pending_email' && $user->created_at->addHours(config('users.pending_registration_expiration_hours'))->isPast()) {
            if ($user->enrollment_proof_path) {
                Storage::delete($user->enrollment_proof_path);
            }
            $user->delete();

            return redirect()->route('register')->withErrors(['email' => 'Este cadastro expirou. Realize um novo cadastro.']);
        }

        $validLink = $request->hasValidSignature()
            && hash_equals(sha1($user->getEmailForVerification()), $hash)
            && (int) $request->query('sent_at') === $user->verification_sent_at?->timestamp;
        if (! $validLink) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => 'O link é inválido ou expirou. Solicite um novo link de confirmação.'])
                ->withInput(['email' => $user->email]);
        }

        if ($user->markEmailAsVerified()) {
            $user->forceFill(['registration_status' => 'email_confirmed'])->save();
            event(new Verified($user));
            $administrator = config('users.administrator_email');
            if ($administrator) {
                Mail::raw('Um novo cadastro confirmou o e-mail e aguarda análise administrativa.', function ($message) use ($administrator): void {
                    $message->to($administrator)->subject('Cadastro aguardando aprovação');
                });
            }
        }

        return redirect()->route('login')->with('status', 'E-mail confirmado. Você já pode entrar; o acesso funcional aguarda análise administrativa.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $key = 'verification:'.Str::lower($validated['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['email' => 'Limite de reenvios atingido. Tente novamente mais tarde.']);
        }
        RateLimiter::hit($key, 3600);

        $user = User::query()->where('email', $validated['email'])->first();
        if ($user?->registration_status === 'pending_email') {
            if ($user->created_at->addHours(config('users.pending_registration_expiration_hours'))->isPast()) {
                $user->delete();
            } else {
                $user->forceFill(['verification_sent_at' => now()])->save();
                $user->sendEmailVerificationNotification();
            }
        }

        return back()->with('status', 'Se houver um cadastro pendente válido, um novo link será enviado.');
    }

    public function verificationNotice(): View
    {
        return view('auth.verify-email');
    }
}
