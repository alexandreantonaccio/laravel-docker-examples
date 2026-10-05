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
        <a class="button button-quiet" href="{{ route('bookings.calendar') }}">Agenda</a>
        @if(auth()->user()->hasPermissionTo('ambientes.listar'))<a class="button button-quiet" href="{{ route('environments.index') }}">Ambientes</a>@endif
        @if(auth()->user()->hasPermissionTo('grupos_ambientes.listar'))<a class="button button-quiet" href="{{ route('environment-groups.index') }}">Grupos de ambientes</a>@endif
        @if(auth()->user()->hasPermissionTo('dominios_email.listar'))<a class="button button-quiet" href="{{ route('domains.index') }}">Domínios de e-mail</a>@endif
        @foreach(['cursos'=>'cursos','cargos'=>'cargos','vinculos'=>'vinculos'] as $catalog=>$permission)
            @if(auth()->user()->hasPermissionTo($permission.'.listar'))<a class="button button-quiet" href="{{ route('catalogs.index',$catalog) }}">{{ ucfirst($catalog) }}</a>@endif
        @endforeach
        @if(auth()->user()->hasPermissionTo('agendamentos.tipo.criar') || auth()->user()->hasPermissionTo('agendamentos.tipo.editar') || auth()->user()->hasPermissionTo('agendamentos.tipo.desativar'))<a class="button button-quiet" href="{{ route('booking-types.index') }}">Tipos de agendamento</a>@endif
        @if(auth()->user()->hasPermissionTo('agendamentos.docente.criar') || auth()->user()->hasPermissionTo('agendamentos.docente.editar') || auth()->user()->hasPermissionTo('agendamentos.docente.desativar'))<a class="button button-quiet" href="{{ route('teachers.index') }}">Docentes</a>@endif
    </div>
</x-layouts.app>