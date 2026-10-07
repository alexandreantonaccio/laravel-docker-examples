<x-layouts.app title="Cadastro de material | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Materiais</div><h1>{{ $material->exists ? 'Editar material' : 'Cadastrar material' }}</h1></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ $material->exists ? route('materials.update', $material) : route('materials.store') }}">
        @csrf @if($material->exists) @method('PUT') @endif
        <div class="field"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name', $material->name) }}" required maxlength="255"></div>
        <div class="field"><label for="code">Código</label><input id="code" name="code" value="{{ old('code', $material->code) }}" required maxlength="40"></div>
        <div class="field"><label for="description">Descrição</label><textarea id="description" name="description" maxlength="5000">{{ old('description', $material->description) }}</textarea></div>
        <div class="field"><label for="quantity">Quantidade em estoque</label><input id="quantity" name="quantity" type="number" min="0" max="1000000" value="{{ old('quantity', $material->quantity ?? 1) }}" required></div>
        <div class="field"><label for="material_group_id">Grupo</label><select id="material_group_id" name="material_group_id"><option value="">Sem grupo</option>@foreach($groups as $group)<option value="{{ $group->id }}" @selected(old('material_group_id', $material->material_group_id) == $group->id)>{{ $group->name }}</option>@endforeach</select></div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('materials.index') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar</button></div>
    </form></section>
</x-layouts.app>
