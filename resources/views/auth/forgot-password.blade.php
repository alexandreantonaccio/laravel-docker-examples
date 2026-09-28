<x-layouts.app title="Recuperar senha | Userboard">
    <section class="auth-card">
        <div class="eyebrow">Acesso à conta</div>
        <h1>Recuperar senha</h1>
        <p class="muted">Informe o e-mail institucional associado ao cadastro.</p>
        <form method="POST" action="{{ route('password.email') }}" class="stack-form">
            @csrf
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <button class="button button-primary button-wide" type="submit">Enviar link</button>
        </form>
        <p class="auth-footer"><a href="{{ route('login') }}">Voltar ao login</a></p>
    </section>
</x-layouts.app>