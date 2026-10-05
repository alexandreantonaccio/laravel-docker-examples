<x-layouts.app :title="($item->exists ? 'Editar ' : 'Novo ').$config['label']">
    <div class="page-heading"><div><div class="eyebrow">{{ $config['label'] }}</div><h1>{{ $item->exists ? 'Editar registro' : 'Novo registro' }}</h1></div></div>
    <section class="form-card">
        <form class="stack-form" method="POST" action="{{ $item->exists ? route('catalogs.update', [$catalog, $item->id]) : route('catalogs.store', $catalog) }}">
            @csrf
            @if ($item->exists) @method('PUT') @endif
            <div class="field"><label for="value">Nome</label><input id="value" name="value" value="{{ old('value', $item->value) }}" required maxlength="255"></div>
            <div class="form-actions"><a class="button button-quiet" href="{{ route('catalogs.index', $catalog) }}">Cancelar</a><button class="button button-primary" type="submit">Salvar</button></div>
        </form>
    </section>
</x-layouts.app>
