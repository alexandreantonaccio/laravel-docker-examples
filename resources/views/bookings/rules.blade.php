<x-layouts.app title="Configurações de agendamento | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agenda</div><h1>Regras e bloqueios</h1><p class="muted">Uma regra por ambiente substitui a global; bloqueios sempre prevalecem.</p></div><a class="button button-quiet" href="{{ route('bookings.calendar') }}">Voltar à agenda</a></div>
    <section class="form-card"><h2>Regra global ou por ambiente</h2><form class="stack-form" method="POST" action="{{ route('bookings.rules.save') }}">@csrf
        <div class="field"><label for="environment_id">Escopo</label><select id="environment_id" name="environment_id"><option value="">Regra global</option>@foreach($environments as $environment)<option value="{{ $environment->id }}">Ambiente: {{ $environment->name }}</option>@endforeach</select></div>
        <fieldset class="weekdays"><legend>Dias permitidos</legend>@foreach(['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'] as $i=>$day)<label class="checkbox-label"><input type="checkbox" name="weekdays[]" value="{{ $i }}">{{ $day }}</label>@endforeach</fieldset>
        <div class="field"><label>Faixas de horário</label><div data-ranges><div class="range-row"><input name="time_ranges[0][starts_at]" type="time" required><span>até</span><input name="time_ranges[0][ends_at]" type="time" required></div></div><button class="button button-quiet" data-add-range type="button">Adicionar faixa</button></div>
        <button class="button button-primary" type="submit">Salvar regra</button>
    </form></section>
    <section class="table-card table-wrap"><h2 class="table-section-heading">Regras existentes</h2><table><thead><tr><th>Escopo</th><th>Dias</th><th>Faixas</th><th></th></tr></thead><tbody>
    @forelse($rules as $rule)<tr><td>{{ $rule->environment_id ? $environments->firstWhere('id',$rule->environment_id)?->name : 'Global' }}</td><td>{{ implode(', ',json_decode($rule->weekdays,true) ?: []) }}</td><td>{{ collect(json_decode($rule->time_ranges,true) ?: [])->map(fn($range)=>$range['starts_at'].'–'.$range['ends_at'])->join(', ') }}</td><td>
        @if($rule->environment_id)<form method="POST" action="{{ route('bookings.rules.remove',$rule->id) }}">@csrf @method('DELETE')<button class="text-button" type="submit">Remover regra específica</button></form>@endif
    </td></tr>@empty<tr><td colspan="4">Nenhuma regra configurada.</td></tr>@endforelse
    </tbody></table></section>
    <section class="form-card"><h2>Novo bloqueio</h2><form class="stack-form" method="POST" action="{{ route('bookings.blocks.store') }}">@csrf
        <div class="field"><label for="block_type">Tipo</label><select id="block_type" name="block_type" required><option value="date">Data inteira</option><option value="date_time">Data e horário</option><option value="weekday">Dia da semana recorrente</option><option value="weekday_time">Dia semanal e horário</option></select></div>
        <div class="field"><label for="block_environment_id">Ambiente (opcional)</label><select id="block_environment_id" name="environment_id"><option value="">Global</option>@foreach($environments as $environment)<option value="{{ $environment->id }}">{{ $environment->name }}</option>@endforeach</select></div>
        <div class="field"><label for="specific_date">Data específica</label><input id="specific_date" type="date" name="specific_date"></div>
        <div class="field"><label for="weekday">Dia da semana</label><select id="weekday" name="weekday"><option value="">Selecione</option>@foreach(['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'] as $i=>$day)<option value="{{ $i }}">{{ $day }}</option>@endforeach</select></div>
        <div class="field"><label for="block_starts_at">Início (para bloqueio parcial)</label><input id="block_starts_at" type="time" name="starts_at"></div><div class="field"><label for="block_ends_at">Fim</label><input id="block_ends_at" type="time" name="ends_at"></div>
        <div class="field"><label for="reason">Motivo</label><input id="reason" name="reason"></div><button class="button button-primary" type="submit">Criar bloqueio</button>
    </form></section>
    <section class="table-card table-wrap"><h2 class="table-section-heading">Bloqueios cadastrados</h2><table><thead><tr><th>Escopo</th><th>Tipo</th><th>Data / dia</th><th>Motivo</th><th></th></tr></thead><tbody>
    @forelse($blocks as $block)<tr><td>{{ $block->environment_id ? $environments->firstWhere('id',$block->environment_id)?->name : 'Global' }}</td><td>{{ $block->block_type }}</td><td>{{ $block->specific_date ?? $block->weekday ?? '—' }}</td><td>{{ $block->reason }}</td><td>
        <a class="text-link" href="{{ route('bookings.blocks.edit',$block->id) }}">Editar</a>
        <form class="inline-form" method="POST" action="{{ route('bookings.blocks.delete',$block->id) }}">@csrf @method('DELETE')<button class="text-button" type="submit">Remover</button></form>
    </td></tr>@empty<tr><td colspan="5">Nenhum bloqueio cadastrado.</td></tr>@endforelse
    </tbody></table></section>
    <script>
        document.querySelector('[data-add-range]')?.addEventListener('click', () => {
            const container = document.querySelector('[data-ranges]');
            const index = container.querySelectorAll('.range-row').length;
            container.insertAdjacentHTML('beforeend', `<div class="range-row"><input name="time_ranges[${index}][starts_at]" type="time" required><span>até</span><input name="time_ranges[${index}][ends_at]" type="time" required></div>`);
        });
    </script>
</x-layouts.app>
