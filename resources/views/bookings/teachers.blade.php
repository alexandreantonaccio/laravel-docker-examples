<x-layouts.app title="Docentes | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agenda</div><h1>Docentes de referência</h1><p class="muted">Nomes sem vínculo com as contas de usuário.</p></div><a class="button button-quiet" href="{{ route('bookings.calendar') }}">Voltar à agenda</a></div>
    @if(auth()->user()->hasPermissionTo('agendamentos.docente.criar'))
    <section class="form-card"><h2>Novo docente</h2><form class="stack-form" method="POST" action="{{ route('teachers.store') }}">@csrf<div class="field"><label for="name">Nome</label><input id="name" name="name" required></div><button class="button button-primary" type="submit">Cadastrar docente</button></form></section>
    @endif
    <section class="table-card table-wrap"><table><thead><tr><th>Nome</th><th>Status</th><th class="table-actions">Ações</th></tr></thead><tbody>
    @forelse($teachers as $teacher)<tr><td>{{ $teacher->name }}</td><td>{{ $teacher->active?'Ativo':'Desativado' }}</td><td class="table-actions">
        @if(auth()->user()->hasPermissionTo('agendamentos.docente.editar'))<form class="inline-form" method="POST" action="{{ route('teachers.update',$teacher) }}">@csrf @method('PUT')<input class="compact-input" name="name" value="{{ $teacher->name }}" required><button class="text-button" type="submit">Salvar</button></form>@endif
        @if(auth()->user()->hasPermissionTo('agendamentos.docente.desativar'))<form class="inline-form" method="POST" action="{{ route('teachers.toggle',$teacher) }}">@csrf @method('PATCH')<button class="text-button" type="submit">{{ $teacher->active?'Desativar':'Reativar' }}</button></form>@endif
    </td></tr>@empty<tr><td colspan="3" class="empty-state">Nenhum docente cadastrado.</td></tr>@endforelse
    </tbody></table></section>
</x-layouts.app>
