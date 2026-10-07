<x-layouts.app title="Pedido de aluguel | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Materiais</div><h1>Pedido de aluguel</h1></div><a class="button button-quiet" href="{{ route('material-rentals.index') }}">Voltar</a></div>
    <section class="detail-card">
        <div class="detail-row"><span>Solicitante</span><strong>{{ $rental->requester->name }}</strong></div>
        <div class="detail-row"><span>Material</span><strong>{{ $rental->material->name }} ({{ $rental->material->code }})</strong></div>
        <div class="detail-row"><span>Grupo</span><strong>{{ $rental->material->group?->name ?? '—' }}</strong></div>
        <div class="detail-row"><span>Quantidade</span><strong>{{ $rental->quantity }}</strong></div>
        <div class="detail-row"><span>Período</span><strong>{{ $rental->starts_on->format('d/m/Y') }} – {{ $rental->ends_on->format('d/m/Y') }}</strong></div>
        <div class="detail-row"><span>Finalidade</span><strong>{{ $rental->reason }}</strong></div>
        <div class="detail-row"><span>Status</span><strong>{{ ucfirst($rental->status) }}</strong></div>
        @if($rental->decision_reason)<div class="detail-row"><span>Motivo da rejeição</span><strong>{{ $rental->decision_reason }}</strong></div>@endif
        @if($rental->cancellation_reason)<div class="detail-row"><span>Motivo do cancelamento</span><strong>{{ $rental->cancellation_reason }}</strong></div>@endif
    </section>
    <div class="action-row">
        @if(auth()->user()->hasPermissionTo('materiais_alugueis.aprovar') && $rental->status === 'pending')
            <form method="POST" action="{{ route('material-rentals.approve', $rental) }}">@csrf<button class="button button-primary" type="submit">Aprovar</button></form>
            <form class="inline-reject" method="POST" action="{{ route('material-rentals.reject', $rental) }}">@csrf<div class="field"><label for="decision_reason">Motivo da rejeição</label><input id="decision_reason" name="decision_reason" required maxlength="2000"></div><button class="button button-quiet" type="submit">Rejeitar</button></form>
        @endif
        @if(in_array($rental->status, ['pending', 'approved'], true) && ((int)$rental->requester_user_id === (int)auth()->id() || auth()->user()->hasPermissionTo('materiais_alugueis.cancelar.qualquer')))
            <form class="cancel-form" method="POST" action="{{ route('material-rentals.cancel', $rental) }}">@csrf<div class="field"><label for="cancellation_reason">Motivo do cancelamento</label><input id="cancellation_reason" name="cancellation_reason" required maxlength="2000"></div><button class="button button-quiet" type="submit">Cancelar pedido</button></form>
        @endif
    </div>
</x-layouts.app>
