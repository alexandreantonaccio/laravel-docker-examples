<x-layouts.app title="Nova série de agendamentos | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agendamento recorrente</div><h1>Nova série fixa</h1><p class="muted">As ocorrências elegíveis serão aprovadas após a confirmação.</p></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ route('bookings.fixed.preview') }}">
        @csrf
        <div class="field"><label for="name">Nome da série</label><input id="name" name="name" value="{{ old('name') }}" required></div>
        <div class="field"><label for="requester_user_id">Solicitante (usuário, ou selecione um docente abaixo)</label><select id="requester_user_id" name="requester_user_id"><option value="">Docente de referência</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('requester_user_id')==$user->id)>{{ $user->name }}</option>@endforeach</select></div>
        <div class="field"><label for="teacher_id">Docente de referência</label><select id="teacher_id" name="teacher_id"><option value="">Usuário do sistema</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('teacher_id')==$teacher->id)>{{ $teacher->name }}</option>@endforeach</select></div>
        <div class="field"><label for="environment_id">Ambiente</label><select id="environment_id" name="environment_id" required><option value="">Selecione</option>@foreach($environments as $environment)<option value="{{ $environment->id }}" @selected(old('environment_id')==$environment->id)>{{ $environment->name }}</option>@endforeach</select></div>
        <div class="field"><label for="booking_type_id">Tipo</label><select id="booking_type_id" name="booking_type_id" required><option value="">Selecione</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected(old('booking_type_id')==$type->id)>{{ $type->name }}</option>@endforeach</select></div>
        <div class="field"><label for="reason">Motivo</label><textarea id="reason" name="reason" required>{{ old('reason') }}</textarea></div>
        <fieldset class="weekdays"><legend>Dias da semana</legend>@foreach(['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'] as $i=>$day)<label class="checkbox-label"><input type="checkbox" name="weekdays[]" value="{{ $i }}" @checked(in_array((string)$i,old('weekdays',[]),true))>{{ $day }}</label>@endforeach</fieldset>
        <div class="field"><label for="starts_on">Data de início</label><input id="starts_on" type="date" name="starts_on" min="{{ today()->toDateString() }}" value="{{ old('starts_on') }}" required></div>
        <div class="field"><label for="ends_on">Data de fim</label><input id="ends_on" type="date" name="ends_on" value="{{ old('ends_on') }}" required></div>
        <div class="field"><label for="starts_at">Hora de início</label><input id="starts_at" type="time" name="starts_at" value="{{ old('starts_at') }}" required></div>
        <div class="field"><label for="ends_at">Hora de fim</label><input id="ends_at" type="time" name="ends_at" value="{{ old('ends_at') }}" required></div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('bookings.calendar') }}">Cancelar</a><button class="button button-primary" type="submit">Pré-visualizar ocorrências</button></div>
    </form></section>
</x-layouts.app>
