<x-layouts.app title="Materiais | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Recursos</div><h1>Materiais</h1><p class="muted">Consulte o estoque e solicite materiais por período.</p></div>
        <a class="button button-primary" href="{{ route('material-rentals.create') }}">Alugar material</a>
    </div>
    <div class="action-row">
        <a class="button button-quiet" href="{{ route('material-rentals.index') }}">{{ auth()->user()->hasPermissionTo('materiais_alugueis.listar.qualquer') ? 'Pedidos de aluguel' : 'Meus pedidos' }}</a>
        @if(auth()->user()->hasPermissionTo('materiais.criar'))<a class="button button-quiet" href="{{ route('materials.create') }}">Cadastrar material</a>@endif
        @if(auth()->user()->hasPermissionTo('grupos_materiais.listar'))<a class="button button-quiet" href="{{ route('material-groups.index') }}">Grupos de materiais</a>@endif
    </div>
    <section class="table-card table-wrap"><table><thead><tr><th>Material</th><th>Código</th><th>Grupo</th><th>Estoque total</th><th>Disponível hoje</th><th>Status</th><th>Ações</th></tr></thead><tbody>
    @forelse($materials as $material)
        @php($available = max(0, $material->quantity - (int)($material->reserved_quantity ?? 0)))
        <tr><td>{{ $material->name }}</td><td>{{ $material->code }}</td><td>{{ $material->group?->name ?? '—' }}</td><td>{{ $material->quantity }}</td><td>{{ $material->active ? $available : 0 }}</td><td>{{ $material->active ? 'Ativo' : 'Desativado' }}</td><td class="table-actions">
            @if(auth()->user()->hasPermissionTo('materiais.editar'))<a class="text-link" href="{{ route('materials.edit', $material) }}">Editar</a>@endif
            @if(auth()->user()->hasPermissionTo('materiais.desativar'))<form class="inline-form" method="POST" action="{{ route('materials.toggle', $material) }}">@csrf @method('PATCH')<button class="text-button" type="submit">{{ $material->active ? 'Desativar' : 'Reativar' }}</button></form>@endif
        </td></tr>
    @empty<tr><td colspan="7" class="empty-state">Nenhum material cadastrado.</td></tr>@endforelse
    </tbody></table><div class="pagination">{{ $materials->links() }}</div></section>
</x-layouts.app>
