<x-layouts.app title="Alterar senha | Userboard">
    <div class="page-heading compact-heading">
        <div><div class="eyebrow">Meus dados</div><h1>Alterar senha</h1></div>
    </div>
    <section class="form-card">
        <form method="POST" action="{{ route('password.change.update') }}" class="stack-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="current_password">Senha atual</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
            </div>
            <div class="field">
                <label for="password">Nova senha</label>
                <input id="password" name="password" type="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirme a nova senha</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>
            <button class="button button-primary" type="submit">Salvar senha</button>
        </form>
    </section>
</x-layouts.app>