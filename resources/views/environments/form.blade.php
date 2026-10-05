<x-layouts.app title="Ambiente | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Ambientes</div><h1>{{ $environment->exists ? 'Editar ambiente' : 'Novo ambiente' }}</h1></div></div>
    <section class="form-card"><form class="stack-form" method="POST" enctype="multipart/form-data" action="{{ $environment->exists ? route('environments.update',$environment) : route('environments.store') }}">
        @csrf @if ($environment->exists) @method('PUT') @endif
        <div class="field"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name',$environment->name) }}" required></div>
        <div class="field"><label for="code">Sigla</label><input id="code" name="code" value="{{ old('code',$environment->code) }}" required></div>
        <div class="field"><label for="chairs_count">Quantidade de cadeiras</label><input id="chairs_count" type="number" min="0" name="chairs_count" value="{{ old('chairs_count',$environment->chairs_count) }}" required></div>
        <div class="field"><label for="benches_count">Quantidade de bancadas</label><input id="benches_count" type="number" min="0" name="benches_count" value="{{ old('benches_count',$environment->benches_count) }}" required></div>
        <div class="field"><label for="description">Descrição</label><textarea id="description" name="description" required>{{ old('description',$environment->description) }}</textarea></div>
        <div class="field"><label for="environment_group_id">Grupo de ambientes</label><select id="environment_group_id" name="environment_group_id"><option value="">Sem grupo</option>@foreach ($groups as $group)<option value="{{ $group->id }}" @selected((string)old('environment_group_id',$environment->environment_group_id)===(string)$group->id)>{{ $group->name }}</option>@endforeach</select></div>
        <div class="field"><label for="photo">Foto (JPG/PNG, até 1 MB)</label><input id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png"></div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('environments.index') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar</button></div>
    </form></section>
</x-layouts.app>
