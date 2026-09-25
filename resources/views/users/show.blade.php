<x-layouts.app title="{{ $user->name }} | Userboard">
    <div class="page-heading compact-heading">
        <div>
            <div class="eyebrow">Usuários / Detalhes</div>
            <h1>{{ $user->name }}</h1>
            <p class="muted">Informações do cadastro.</p>
        </div>
        <a class="button button-primary" href="{{ route('users.edit', $user) }}">Editar usuário</a>
    </div>

    <section class="detail-card">
        <div class="detail-row"><span>Nome</span><strong>{{ $user->name }}</strong></div>
        <div class="detail-row"><span>E-mail</span><strong>{{ $user->email }}</strong></div>
        <div class="detail-row"><span>Cadastrado em</span><strong>{{ $user->created_at->format('d/m/Y H:i') }}</strong></div>
        <div class="detail-row"><span>Atualizado em</span><strong>{{ $user->updated_at->format('d/m/Y H:i') }}</strong></div>
    </section>

    <a class="back-link" href="{{ route('users.index') }}">Voltar para usuários</a>
</x-layouts.app>
