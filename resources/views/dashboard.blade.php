<x-layouts.app title="Início | Userboard">
    <div class="page-heading">
        <div><div class="eyebrow">Área autenticada</div><h1>Olá, {{ auth()->user()->name }}</h1><p class="muted">Acesso disponível conforme suas permissões atuais.</p></div>
    </div>
    <section class="detail-card">
        <div class="detail-row"><span>Status do cadastro</span><strong>{{ auth()->user()->registration_status }}</strong></div>
        <div class="detail-row"><span>Permissões efetivas</span><strong>{{ count(auth()->user()->effectivePermissions()) }}</strong></div>
    </section>
    <div class="action-row">
        @if (auth()->user()->hasPermissionTo('usuarios.visualizar.proprio'))<a class="button button-primary" href="{{ route('profile.show') }}">Meus dados</a>@endif
        @if (auth()->user()->hasPermissionTo('usuarios.listar'))<a class="button button-quiet" href="{{ route('users.index') }}">Gerenciar usuários</a>@endif
        @if (auth()->user()->hasPermissionTo('usuarios.aprovar'))<a class="button button-quiet" href="{{ route('users.pending') }}">Analisar cadastros</a>@endif
        @if (auth()->user()->hasPermissionTo('grupos_permissoes.criar') || auth()->user()->hasPermissionTo('grupos_permissoes.editar') || auth()->user()->hasPermissionTo('grupos_permissoes.excluir') || auth()->user()->hasPermissionTo('grupos_permissoes.atribuir_usuarios'))<a class="button button-quiet" href="{{ route('groups.index') }}">Grupos de permissões</a>@endif
    </div>
</x-layouts.app>