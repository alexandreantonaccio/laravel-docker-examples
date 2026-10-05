<x-layouts.app title="Prévia da série | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agendamento recorrente</div><h1>Prévia das ocorrências</h1><p class="muted">Somente ocorrências sem bloqueio ou conflito serão criadas.</p></div></div>
    <section class="table-card table-wrap"><table><thead><tr><th>Data</th><th>Resultado</th></tr></thead><tbody>
    @foreach($occurrences as $occurrence)<tr><td>{{ \Illuminate\Support\Carbon::parse($occurrence['date'])->format('d/m/Y') }}</td><td>{{ $occurrence['problem'] ?? 'Será aprovada' }}</td></tr>@endforeach
    @if($occurrences->isEmpty())<tr><td colspan="2" class="empty-state">Não há ocorrências nos dias selecionados.</td></tr>@endif
    </tbody></table></section>
    @if($occurrences->contains(fn($occurrence)=>!$occurrence['problem']))
        <form class="action-row" method="POST" action="{{ route('bookings.fixed.store') }}">@csrf
            @foreach($data as $key=>$value)
                @if(is_array($value))@foreach($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
                @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach
            <a class="button button-quiet" href="{{ route('bookings.fixed.create') }}">Voltar e editar</a><button class="button button-primary" type="submit">Confirmar série</button>
        </form>
    @endif
</x-layouts.app>
