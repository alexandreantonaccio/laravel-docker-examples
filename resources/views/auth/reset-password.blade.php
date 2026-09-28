<x-layouts.app title="Definir senha | Userboard">
    <section class="auth-card">
        <div class="eyebrow">Acesso à conta</div>
        <h1>Definir nova senha</h1>
        <form method="POST" action="{{ route('password.update') }}" class="stack-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
            </div>
            <div class="field">
                <label for="password">Nova senha</label>
                <input id="password" name="password" type="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirme a senha</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>
            <button class="button button-primary button-wide" type="submit">Redefinir senha</button>
        </form>
    </section>
</x-layouts.app>