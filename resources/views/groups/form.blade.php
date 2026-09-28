<x-layouts.app title="{{ $group->exists ? 'Editar grupo' : 'Novo grupo' }} | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Grupos de permissões</div><h1>{{ $group->exists ? 'Editar grupo' : 'Criar grupo' }}</h1></div></div>
    <section class="form-card">
        <form method="POST" action="{{ $group->exists ? route('groups.update', $group) : route('groups.store') }}" class="stack-form">
            @csrf
            @if ($group->exists) @method('PUT') @endif
            <div class="field"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name', $group->name) }}" required maxlength="255"></div>
            <div class="field"><label for="description">Descrição</label><textarea id="description" name="description" maxlength="2000">{{ old('description', $group->description) }}</textarea></div>
            <fieldset class="option-list"><legend>Permissões concedidas</legend>
                @foreach ($permissions as $permission)
                    <label class="option-row"><input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked(collect(old('permission_ids', $group->permissions->pluck('id')->all()))->contains($permission->id))><span><strong>{{ $permission->key }}</strong><small>{{ $permission->label }}</small></span></label>
                @endforeach
            </fieldset>
            <div class="action-row"><a class="button button-quiet" href="{{ route('groups.index') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar grupo</button></div>
        </form>
    </section>
</x-layouts.app>