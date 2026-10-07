<x-layouts.app title="Solicitações de agendamento | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Administração</div><h1>Solicitações</h1><p class="muted">Agendamentos comuns e ocorrências fixas aparecem em grupos distintos.</p></div><a class="button button-quiet" href="{{ route('bookings.calendar') }}">Voltar à agenda</a></div>
    <section class="table-card">
        <form method="GET" class="filter-row">
            <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">Todos</option>@foreach(['pending'=>'Pendente','approved'=>'Aprovado','rejected'=>'Rejeitado','cancelled'=>'Cancelado'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="field"><label for="environment_id">Ambiente</label><select id="environment_id" name="environment_id"><option value="">Todos</option>@foreach($environments as $environment)<option value="{{ $environment->id }}" @selected((string)request('environment_id')===(string)$environment->id)>{{ $environment->name }}</option>@endforeach</select></div>
            <div class="field"><label for="start">A partir de</label><input id="start" name="start" type="date" value="{{ request('start') }}"></div>
            <div class="field"><label for="end">Até</label><input id="end" name="end" type="date" value="{{ request('end') }}"></div>
            <button class="button button-quiet" type="submit">Filtrar</button>
        </form>
        @foreach([false => 'Agendamentos normais', true => 'Agendamentos fixos / recorrentes'] as $series => $heading)
            <h2 class="table-section-heading">{{ $heading }}</h2>
            <div class="table-wrap"><table><thead><tr><th>Solicitante</th><th>Ambiente</th><th>Tipo</th><th>Data</th><th>Horário</th><th>Status</th><th></th></tr></thead><tbody>
            @php($rows = $bookings->getCollection()->filter(fn($booking)=>(bool)$booking->booking_series_id === (bool)$series))
            @forelse($rows as $booking)<tr>
                <td>{{ $booking->requester?->name ?? $booking->teacher?->name ?? $booking->teacher_other ?? '—' }}</td><td>{{ $booking->environment?->name ?? $booking->environment_other }}</td><td>{{ $booking->type?->name ?? $booking->booking_type_other }}</td>
                <td>{{ $booking->booking_date->format('d/m/Y') }}</td><td>{{ substr($booking->starts_at,0,5) }}–{{ substr($booking->ends_at,0,5) }}</td><td>{{ ucfirst($booking->status) }}</td><td><a class="text-link" href="{{ route('bookings.show',$booking) }}">Detalhes</a></td>
            </tr>@empty<tr><td colspan="7" class="empty-state">Nenhum registro nesta seção.</td></tr>@endforelse
            </tbody></table></div>
        @endforeach
        <div class="pagination">{{ $bookings->links() }}</div>
    </section>
</x-layouts.app>
