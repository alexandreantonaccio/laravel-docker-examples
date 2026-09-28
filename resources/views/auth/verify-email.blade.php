<x-layouts.app title="Confirmar e-mail | Userboard">
    <section class="auth-card">
        <div class="eyebrow">Confirmação de cadastro</div>
        <h1>Confirme seu e-mail</h1>
        <p class="muted">Informe o e-mail usado no cadastro para receber um novo link. A mensagem não revela se há cadastro associado.</p>
        <form method="POST" action="{{ route('verification.send') }}" class="stack-form">
            @csrf
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <button class="button button-primary button-wide" type="submit">Reenviar confirmação</button>
        </form>
        <p class="auth-footer"><a href="{{ route('register') }}">Realizar novo cadastro</a></p>
    </section>
</x-layouts.app>