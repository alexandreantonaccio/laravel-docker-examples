<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAuditLog;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = User::query()->where('email', $request->string('email'))->first();

        if ($user?->hasVerifiedEmail() && $user->registration_status !== 'pending_email') {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('status', 'Se o e-mail estiver cadastrado e confirmado, enviaremos um link de redefinição.');
    }

    public function resetForm(Request $request): View
    {
        $user = User::query()->where('email', $request->query('email'))->first();
        $token = $request->route('token');
        if (! $user || ! Password::broker()->tokenExists($user, $token)) {
            throw ValidationException::withMessages([
                'email' => 'O link é inválido ou expirou. Solicite uma nova redefinição de senha.',
            ]);
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $user->email]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ]);

        $status = Password::reset($validated, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('sessions')->where('user_id', $user->id)->delete();
            UserAuditLog::query()->create([
                'user_id' => $user->id,
                'action' => 'password_reset',
            ]);
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', 'Senha redefinida. Entre com sua nova senha.');
    }

    public function changeForm(): View
    {
        return view('auth.change-password');
    }

    public function change(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ]);
        $user = $request->user();
        $user->forceFill(['password' => $validated['password'], 'remember_token' => Str::random(60)])->save();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
        UserAuditLog::query()->create([
            'actor_id' => $user->id,
            'user_id' => $user->id,
            'action' => 'password_changed',
        ]);

        return redirect()->route('profile.show')->with('status', 'Senha alterada com sucesso.');
    }

    public function sendAdminReset(User $user): RedirectResponse
    {
        abort_unless($user->hasVerifiedEmail(), 404);
        Password::sendResetLink(['email' => $user->email]);
        UserAuditLog::query()->create([
            'actor_id' => auth()->id(),
            'user_id' => $user->id,
            'action' => 'admin_password_reset_requested',
        ]);

        return back()->with('status', 'O link de redefinição foi enviado ao usuário.');
    }
}