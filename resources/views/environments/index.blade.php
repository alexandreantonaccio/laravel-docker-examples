<x-layouts.app title="Ambientes | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Recursos</div><h1>Ambientes</h1><p class="muted">Ambientes referenciados pelos agendamentos; registros não são excluídos.</p></div>
        @if (auth()->user()->hasPermissionTo('ambientes.criar'))<a class="button button-primary" href="{{ route('environments.create') }}">Novo ambiente</a>@endif
    </div>
    <div class="action-row"><a class="button button-quiet" href="{{ route('environments.index') }}">Todos</a><a class="button button-quiet" href="{{ route('environments.index',['status'=>'active']) }}">Ativos</a><a class="button button-quiet" href="{{ route('environments.index',['status'=>'inactive']) }}">Desativados</a></div>
    <section class="table-card table-wrap"><table><thead><tr><th>Nome</th><th>Sigla</th><th>Grupo</th><th>Status</th><th>Agendamentos</th><th class="table-actions">Ações</th></tr></thead><tbody>
    @forelse ($environments as $environment)<tr><td>{{ $environment->name }}</td><td>{{ $environment->code }}</td><td>{{ $environment->group?->name ?? '—' }}</td><td>{{ $environment->active ? 'Ativo' : 'Desativado' }}</td><td>{{ $environment->bookings_count }}</td><td class="table-actions">
        @if (auth()->user()->hasPermissionTo('ambientes.editar'))<a class="text-link" href="{{ route('environments.edit',$environment) }}">Editar</a>@endif
        @if (auth()->user()->hasPermissionTo('ambientes.desativar'))<form class="inline-form" method="POST" action="{{ route('environments.toggle',$environment) }}">@csrf @method('PATCH')<button class="text-button" type="submit">{{ $environment->active ? 'Desativar' : 'Reativar' }}</button></form>@endif
    </td></tr>@empty<tr><td colspan="6" class="empty-state">Nenhum ambiente cadastrado.</td></tr>@endforelse
    </tbody></table><div class="pagination">{{ $environments->links() }}</div></section>
</x-layouts.app>
