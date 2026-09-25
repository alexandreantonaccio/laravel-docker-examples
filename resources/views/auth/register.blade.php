<x-layouts.app title="Criar cadastro | Userboard">
    <section class="auth-card">
        <div class="eyebrow">Primeiro acesso</div>
        <h1>Crie seu cadastro</h1>
        <p class="muted">Cadastre um usuário para começar a usar o painel.</p>

        <form method="POST" action="{{ route('register.store') }}" class="stack-form">
            @csrf
            <div class="field">
                <label for="name">Nome</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="field">
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirme a senha</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>
            <button class="button button-primary button-wide" type="submit">Criar cadastro</button>
        </form>

        <p class="auth-footer">Já possui uma conta? <a href="{{ route('login') }}">Entrar</a></p>
    </section>
</x-layouts.app>
