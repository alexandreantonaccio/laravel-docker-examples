<x-layouts.app title="Agenda | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agendamentos</div><h1>Agenda</h1><p class="muted">Visualização dos agendamentos e disponibilidade por ambiente.</p></div>
        @if (auth()->user()->hasPermissionTo('agendamentos.solicitar.proprio') || auth()->user()->hasPermissionTo('agendamentos.solicitar.qualquer'))<a class="button button-primary" href="{{ route('bookings.create') }}">Solicitar agendamento</a>@endif
    </div>
    <section class="table-card">
        <form method="GET" class="filter-row">
            <div class="field"><label for="start">De</label><input id="start" name="start" type="date" value="{{ request('start',$start) }}"></div>
            <div class="field"><label for="end">Até</label><input id="end" name="end" type="date" value="{{ request('end',$end) }}"></div>
            <div class="field"><label for="environment_id">Ambiente</label><select id="environment_id" name="environment_id"><option value="">Todos</option>@foreach($environments as $environment)<option value="{{ $environment->id }}" @selected((string)request('environment_id')===(string)$environment->id)>{{ $environment->name }}</option>@endforeach</select></div>
            <button class="button button-quiet" type="submit">Filtrar</button>
        </form>
        <div class="table-wrap"><table><thead><tr><th>Data e horário</th><th>Ambiente</th><th>Tipo</th><th>Solicitante</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($bookings as $booking)<tr>
            <td>{{ $booking->booking_date->format('d/m/Y') }} · {{ substr($booking->starts_at,0,5) }}–{{ substr($booking->ends_at,0,5) }}</td>
            <td>{{ $booking->environment->name }}</td><td><span class="booking-color" style="--booking-color:{{ $booking->type->color }}"></span>{{ $booking->type->name }}</td>
            <td>{{ $booking->requester?->name ?? $booking->teacher?->name ?? '—' }}</td><td><span class="status-label">{{ ucfirst($booking->status) }}</span></td>
            <td><a class="text-link" href="{{ route('bookings.show',$booking) }}">Detalhes</a></td>
        </tr>@empty<tr><td colspan="6" class="empty-state">Não há agendamentos neste período.</td></tr>@endforelse
        </tbody></table></div>
    </section>
    <div class="action-row">
        @if(auth()->user()->hasPermissionTo('agendamentos.listar.qualquer'))<a class="button button-quiet" href="{{ route('bookings.admin') }}">Solicitações / lista administrativa</a>@endif
        @if(auth()->user()->hasPermissionTo('agendamentos.fixo.criar'))<a class="button button-quiet" href="{{ route('bookings.fixed.create') }}">Criar série fixa</a>@endif
        @if(auth()->user()->hasPermissionTo('agendamentos.configurar'))<a class="button button-quiet" href="{{ route('bookings.rules') }}">Configurações de agendamento</a>@endif
    </div>
</x-layouts.app>
