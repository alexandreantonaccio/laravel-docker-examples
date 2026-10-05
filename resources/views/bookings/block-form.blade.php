<x-layouts.app title="Editar bloqueio | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Regras de agendamento</div><h1>Editar bloqueio</h1></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ route('bookings.blocks.update',$block->id) }}">@csrf @method('PUT')
        <div class="field"><label for="block_type">Tipo</label><select id="block_type" name="block_type" required>@foreach(['date'=>'Data inteira','date_time'=>'Data e horário','weekday'=>'Dia semanal recorrente','weekday_time'=>'Dia semanal e horário'] as $value=>$label)<option value="{{ $value }}" @selected(old('block_type',$block->block_type)===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="environment_id">Ambiente (opcional)</label><select id="environment_id" name="environment_id"><option value="">Global</option>@foreach($environments as $environment)<option value="{{ $environment->id }}" @selected((string)old('environment_id',$block->environment_id)===(string)$environment->id)>{{ $environment->name }}</option>@endforeach</select></div>
        <div class="field"><label for="specific_date">Data específica</label><input id="specific_date" type="date" name="specific_date" value="{{ old('specific_date',$block->specific_date) }}"></div>
        <div class="field"><label for="weekday">Dia da semana</label><select id="weekday" name="weekday"><option value="">Selecione</option>@foreach(['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'] as $i=>$day)<option value="{{ $i }}" @selected((string)old('weekday',$block->weekday)===(string)$i)>{{ $day }}</option>@endforeach</select></div>
        <div class="field"><label for="starts_at">Início</label><input id="starts_at" type="time" name="starts_at" value="{{ old('starts_at',$block->starts_at ? substr($block->starts_at,0,5) : '') }}"></div>
        <div class="field"><label for="ends_at">Fim</label><input id="ends_at" type="time" name="ends_at" value="{{ old('ends_at',$block->ends_at ? substr($block->ends_at,0,5) : '') }}"></div>
        <div class="field"><label for="reason">Motivo</label><input id="reason" name="reason" value="{{ old('reason',$block->reason) }}"></div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('bookings.rules') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar bloqueio</button></div>
    </form></section>
</x-layouts.app>
