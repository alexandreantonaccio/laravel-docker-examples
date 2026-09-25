<x-layouts.app title="Novo usuário | Userboard">
    <div class="page-heading compact-heading">
        <div>
            <div class="eyebrow">Usuários</div>
            <h1>Novo usuário</h1>
            <p class="muted">Crie um acesso para a aplicação.</p>
        </div>
    </div>

    <section class="form-card">
        <form method="POST" action="{{ route('users.store') }}" class="stack-form">
            <x-user-form submit-label="Criar usuário" />
        </form>
    </section>
</x-layouts.app>
