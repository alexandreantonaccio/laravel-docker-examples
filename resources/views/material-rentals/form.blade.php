<x-layouts.app title="Alugar material | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Materiais</div><h1>Solicitar aluguel</h1><p class="muted">A disponibilidade será confirmada no envio e na aprovação do pedido.</p></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ route('material-rentals.store') }}">
        @csrf
        <div class="field"><label for="material_id">Material</label><select id="material_id" name="material_id" required><option value="">Selecione</option>@foreach($materials as $material)<option value="{{ $material->id }}" @selected(old('material_id') == $material->id)>{{ $material->name }} · {{ $material->code }} · estoque {{ $material->quantity }}</option>@endforeach</select></div>
        <div class="field"><label for="quantity">Quantidade</label><input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required></div>
        <div class="field"><label for="starts_on">Retirada / início</label><input id="starts_on" name="starts_on" type="date" min="{{ today()->toDateString() }}" value="{{ old('starts_on') }}" required></div>
        <div class="field"><label for="ends_on">Devolução / fim</label><input id="ends_on" name="ends_on" type="date" min="{{ today()->toDateString() }}" value="{{ old('ends_on') }}" required></div>
        <div class="field"><label for="reason">Finalidade</label><textarea id="reason" name="reason" maxlength="5000" required>{{ old('reason') }}</textarea></div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('materials.index') }}">Cancelar</a><button class="button button-primary" type="submit">Enviar pedido</button></div>
    </form></section>
</x-layouts.app>
