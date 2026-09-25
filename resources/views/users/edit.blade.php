<x-layouts.app title="Editar usuário | Userboard">
    <div class="page-heading compact-heading">
        <div>
            <div class="eyebrow">Usuários / {{ $user->name }}</div>
            <h1>Editar usuário</h1>
            <p class="muted">Atualize os dados do cadastro.</p>
        </div>
    </div>

    <section class="form-card">
        <form method="POST" action="{{ route('users.update', $user) }}" class="stack-form">
            @method('PUT')
            <x-user-form submit-label="Salvar alterações" />
        </form>
    </section>
</x-layouts.app>
