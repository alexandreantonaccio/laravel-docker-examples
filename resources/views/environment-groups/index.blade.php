<x-layouts.app title="Grupos de ambientes | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Notificações</div><h1>Grupos de ambientes</h1><p class="muted">Cada ambiente pode pertencer a apenas um grupo ativo.</p></div>
        @if (auth()->user()->hasPermissionTo('grupos_ambientes.criar'))<a class="button button-primary" href="{{ route('environment-groups.create') }}">Novo grupo</a>@endif
    </div>
    <section class="table-card table-wrap"><table><thead><tr><th>Nome</th><th>Ambientes</th><th>E-mails</th><th>Status</th><th class="table-actions">Ações</th></tr></thead><tbody>
    @forelse ($groups as $group)<tr><td>{{ $group->name }}</td><td>{{ $group->environments_count }}</td><td>{{ implode(', ', $group->notification_emails) }}</td><td>{{ $group->active ? 'Ativo' : 'Desativado' }}</td><td class="table-actions">
        @if (auth()->user()->hasPermissionTo('grupos_ambientes.editar'))<a class="text-link" href="{{ route('environment-groups.edit',$group) }}">Editar</a>@endif
        @if (auth()->user()->hasPermissionTo('grupos_ambientes.desativar'))<form class="inline-form" method="POST" action="{{ route('environment-groups.toggle',$group) }}">@csrf @method('PATCH')<button class="text-button" type="submit">{{ $group->active ? 'Desativar' : 'Reativar' }}</button></form>@endif
    </td></tr>@empty<tr><td colspan="5" class="empty-state">Nenhum grupo cadastrado.</td></tr>@endforelse
    </tbody></table><div class="pagination">{{ $groups->links() }}</div></section>
</x-layouts.app>
