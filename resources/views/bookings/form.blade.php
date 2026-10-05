<x-layouts.app title="Agendamento | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agenda</div><h1>{{ $booking->exists ? 'Editar agendamento' : 'Solicitar agendamento' }}</h1></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ $booking->exists ? route('bookings.update',$booking) : route('bookings.store') }}">
        @csrf @if($booking->exists) @method('PUT') @endif
        @if(!$booking->exists && auth()->user()->hasPermissionTo('agendamentos.solicitar.qualquer'))
        <div class="field"><label for="requester_user_id">Solicitante</label><select id="requester_user_id" name="requester_user_id" required><option value="">Selecione</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('requester_user_id')==$user->id)>{{ $user->name }} · {{ $user->email }}</option>@endforeach</select></div>
        @endif
        <div class="field"><label for="environment_id">Ambiente</label><select id="environment_id" name="environment_id" required><option value="">Selecione</option>@foreach($environments as $environment)<option value="{{ $environment->id }}" @selected(old('environment_id',$booking->environment_id)==$environment->id)>{{ $environment->name }} ({{ $environment->code }})</option>@endforeach</select></div>
        <div class="field"><label for="booking_type_id">Tipo</label><select id="booking_type_id" name="booking_type_id" required><option value="">Selecione</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected(old('booking_type_id',$booking->booking_type_id)==$type->id)>{{ $type->name }}</option>@endforeach</select></div>
        <div class="field"><label for="reason">Motivo</label><textarea id="reason" name="reason" required>{{ old('reason',$booking->reason) }}</textarea></div>
        <div class="field"><label for="booking_date">Data</label><input id="booking_date" type="date" name="booking_date" min="{{ today()->toDateString() }}" value="{{ old('booking_date',$booking->booking_date?->toDateString()) }}" required></div>
        <div class="field"><label for="starts_at">Horário de início</label><input id="starts_at" type="time" name="starts_at" value="{{ old('starts_at',$booking->exists ? substr($booking->starts_at,0,5) : '') }}" required></div>
        <div class="field"><label for="ends_at">Horário de fim</label><input id="ends_at" type="time" name="ends_at" value="{{ old('ends_at',$booking->exists ? substr($booking->ends_at,0,5) : '') }}" required></div>
        @if($booking->exists)<div class="field"><label for="change_reason">Motivo da edição</label><textarea id="change_reason" name="change_reason" required></textarea></div>@endif
        @if($booking->booking_series_id)
            <div class="field"><label for="scope">Aplicação da edição</label><select id="scope" name="scope"><option value="one">Apenas esta ocorrência</option><option value="following">Esta e as seguintes</option></select></div>
        @endif
        <div class="form-actions"><a class="button button-quiet" href="{{ route('bookings.calendar') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar</button></div>
    </form></section>
</x-layouts.app>
