<x-layouts.app title="Grupos de permissões | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Acesso</div><h1>Grupos de permissões</h1></div>
        @if (auth()->user()->hasPermissionTo('grupos_permissoes.criar'))<a class="button button-primary" href="{{ route('groups.create') }}">Novo grupo</a>@endif
    </div>
    <section class="table-card">
        @if ($groups->isEmpty())<div class="empty-state"><h2>Nenhum grupo cadastrado</h2></div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Grupo</th><th>Permissões</th><th>Usuários</th><th>Ações</th></tr></thead><tbody>
                @foreach ($groups as $group)<tr><td><strong>{{ $group->name }}</strong><br><small>{{ $group->description }}</small></td><td>{{ $group->permissions_count }}</td><td>{{ $group->users_count }}</td><td><a class="text-link" href="{{ route('groups.show', $group) }}">Detalhes</a></td></tr>@endforeach
            </tbody></table></div><div class="pagination">{{ $groups->links() }}</div>
        @endif
    </section>
</x-layouts.app>