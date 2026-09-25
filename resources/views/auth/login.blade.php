<x-layouts.app title="Entrar | Userboard">
    <section class="auth-card">
        <div class="eyebrow">Área restrita</div>
        <h1>Entre na sua conta</h1>
        <p class="muted">Acesse o painel para gerenciar os usuários cadastrados.</p>

        <form method="POST" action="{{ route('login.store') }}" class="stack-form">
            @csrf
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div class="field">
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <label class="checkbox-label">
                <input name="remember" type="checkbox" value="1"> Lembrar de mim
            </label>
            <button class="button button-primary button-wide" type="submit">Entrar</button>
        </form>

        <p class="auth-footer">Ainda não tem conta? <a href="{{ route('register') }}">Criar cadastro</a></p>
    </section>
</x-layouts.app>
