<x-layouts.app title="Pedidos de aluguel | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Materiais</div><h1>{{ $canListAll ? 'Pedidos de aluguel' : 'Meus pedidos de aluguel' }}</h1></div><a class="button button-primary" href="{{ route('material-rentals.create') }}">Novo pedido</a></div>
    <section class="table-card">
        <form method="GET" class="filter-row"><div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">Todos</option>@foreach(['pending'=>'Pendente','approved'=>'Aprovado','rejected'=>'Rejeitado','cancelled'=>'Cancelado'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div><button class="button button-quiet" type="submit">Filtrar</button></form>
        <div class="table-wrap"><table><thead><tr>@if($canListAll)<th>Solicitante</th>@endif<th>Material</th><th>Quantidade</th><th>Período</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($rentals as $rental)<tr>@if($canListAll)<td>{{ $rental->requester->name }}</td>@endif<td>{{ $rental->material->name }}</td><td>{{ $rental->quantity }}</td><td>{{ $rental->starts_on->format('d/m/Y') }} – {{ $rental->ends_on->format('d/m/Y') }}</td><td>{{ ucfirst($rental->status) }}</td><td><a class="text-link" href="{{ route('material-rentals.show', $rental) }}">Detalhes</a></td></tr>
        @empty<tr><td colspan="{{ $canListAll ? 6 : 5 }}" class="empty-state">Nenhum pedido encontrado.</td></tr>@endforelse
        </tbody></table></div><div class="pagination">{{ $rentals->links() }}</div>
    </section>
</x-layouts.app>
