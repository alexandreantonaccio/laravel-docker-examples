<x-layouts.app title="Tipos de agendamento | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Agenda</div><h1>Tipos de agendamento</h1></div><a class="button button-quiet" href="{{ route('bookings.calendar') }}">Voltar à agenda</a></div>
    @if(auth()->user()->hasPermissionTo('agendamentos.tipo.criar'))
    <section class="form-card"><h2>Novo tipo</h2><form class="stack-form" method="POST" action="{{ route('booking-types.store') }}">@csrf
        <div class="field"><label for="name">Nome</label><input id="name" name="name" required></div><div class="field"><label for="color">Cor do calendário</label><input id="color" name="color" type="color" value="#477c68" required></div><button class="button button-primary" type="submit">Cadastrar tipo</button>
    </form></section>
    @endif
    <section class="table-card table-wrap"><table><thead><tr><th>Nome</th><th>Cor</th><th>Status</th><th class="table-actions">Ações</th></tr></thead><tbody>
    @forelse($types as $type)<tr><td>{{ $type->name }}</td><td><span class="booking-color" style="--booking-color:{{ $type->color }}"></span>{{ $type->color }}</td><td>{{ $type->active?'Ativo':'Desativado' }}</td><td class="table-actions">
        @if(auth()->user()->hasPermissionTo('agendamentos.tipo.editar'))<form class="inline-form" method="POST" action="{{ route('booking-types.update',$type) }}">@csrf @method('PUT')<input class="compact-input" name="name" value="{{ $type->name }}" required><input name="color" type="color" value="{{ $type->color }}" required><button class="text-button" type="submit">Salvar</button></form>@endif
        @if(auth()->user()->hasPermissionTo('agendamentos.tipo.desativar'))<form class="inline-form" method="POST" action="{{ route('booking-types.toggle',$type) }}">@csrf @method('PATCH')<button class="text-button" type="submit">{{ $type->active?'Desativar':'Reativar' }}</button></form>@endif
    </td></tr>@empty<tr><td colspan="4" class="empty-state">Nenhum tipo cadastrado.</td></tr>@endforelse
    </tbody></table></section>
</x-layouts.app>
