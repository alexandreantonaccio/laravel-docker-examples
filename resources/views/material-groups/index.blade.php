<x-layouts.app title="Grupos de materiais | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Materiais</div><h1>Grupos de materiais</h1><p class="muted">Organize o estoque e os contatos de notificação por grupo.</p></div>
        @if(auth()->user()->hasPermissionTo('grupos_materiais.criar'))<a class="button button-primary" href="{{ route('material-groups.create') }}">Novo grupo</a>@endif
    </div>
    <section class="table-card table-wrap"><table><thead><tr><th>Nome</th><th>Materiais</th><th>E-mails de notificação</th><th>Status</th><th>Ações</th></tr></thead><tbody>
    @forelse($groups as $group)<tr><td>{{ $group->name }}</td><td>{{ $group->materials_count }}</td><td>{{ implode(', ', $group->notification_emails ?? []) ?: '—' }}</td><td>{{ $group->active ? 'Ativo' : 'Desativado' }}</td><td class="table-actions">
        @if(auth()->user()->hasPermissionTo('grupos_materiais.editar'))<a class="text-link" href="{{ route('material-groups.edit', $group) }}">Editar</a>@endif
        @if(auth()->user()->hasPermissionTo('grupos_materiais.desativar'))<form class="inline-form" method="POST" action="{{ route('material-groups.toggle', $group) }}">@csrf @method('PATCH')<button class="text-button" type="submit">{{ $group->active ? 'Desativar' : 'Reativar' }}</button></form>@endif
    </td></tr>@empty<tr><td colspan="5" class="empty-state">Nenhum grupo cadastrado.</td></tr>@endforelse
    </tbody></table><div class="pagination">{{ $groups->links() }}</div></section>
</x-layouts.app>
