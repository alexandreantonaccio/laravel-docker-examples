<x-layouts.app title="Detalhes do agendamento | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agenda</div><h1>Detalhes do agendamento</h1></div><a class="button button-quiet" href="{{ route('bookings.calendar') }}">Voltar à agenda</a></div>
    <section class="detail-card">
        <div class="detail-row"><span>Solicitante</span><strong>{{ $booking->requester?->name ?? $booking->teacher?->name ?? '—' }}</strong></div>
        <div class="detail-row"><span>Ambiente</span><strong>{{ $booking->environment->name }}</strong></div>
        <div class="detail-row"><span>Tipo</span><strong>{{ $booking->type->name }}</strong></div>
        <div class="detail-row"><span>Motivo</span><strong>{{ $booking->reason }}</strong></div>
        <div class="detail-row"><span>Data e horário</span><strong>{{ $booking->booking_date->format('d/m/Y') }} · {{ substr($booking->starts_at,0,5) }}–{{ substr($booking->ends_at,0,5) }}</strong></div>
        <div class="detail-row"><span>Status</span><strong>{{ ucfirst($booking->status) }}</strong></div>
        @if($booking->decision_reason)<div class="detail-row"><span>Motivo da rejeição</span><strong>{{ $booking->decision_reason }}</strong></div>@endif
        @if($booking->cancellation_reason)<div class="detail-row"><span>Motivo do cancelamento</span><strong>{{ $booking->cancellation_reason }}</strong></div>@endif
        @if($booking->series)<div class="detail-row"><span>Série</span><strong>{{ $booking->series->name }}</strong></div>@endif
    </section>
    <div class="action-row">
        @if(auth()->user()->hasPermissionTo('agendamentos.aprovar.qualquer') && $booking->status==='pending')
            <form method="POST" action="{{ route('bookings.approve',$booking) }}">@csrf<button class="button button-primary" type="submit">Aprovar</button></form>
            <form class="inline-reject" method="POST" action="{{ route('bookings.reject',$booking) }}">@csrf<div class="field"><label for="decision_reason">Motivo da rejeição</label><input id="decision_reason" name="decision_reason" required></div><button class="button button-quiet" type="submit">Rejeitar</button></form>
        @endif
        @if(auth()->user()->hasPermissionTo('agendamentos.editar.qualquer') || ((int)$booking->requester_user_id===(int)auth()->id() && auth()->user()->hasPermissionTo('agendamentos.editar.proprio')))
            <a class="button button-quiet" href="{{ route('bookings.edit',$booking) }}">Editar</a>
        @endif
        @if(auth()->user()->hasPermissionTo('agendamentos.cancelar.qualquer') || ((int)$booking->requester_user_id===(int)auth()->id() && $booking->status==='pending' && auth()->user()->hasPermissionTo('agendamentos.cancelar.proprio')))
            <form class="cancel-form" method="POST" action="{{ route('bookings.cancel',$booking) }}">@csrf
                @if($booking->booking_series_id && auth()->user()->hasPermissionTo('agendamentos.cancelar.qualquer'))<select name="scope"><option value="one">Apenas esta ocorrência</option><option value="following">Esta e as seguintes</option></select>@endif
                <input name="cancellation_reason" placeholder="Motivo do cancelamento" required><button class="button button-quiet" type="submit">Cancelar agendamento</button>
            </form>
        @endif
    </div>
</x-layouts.app>
