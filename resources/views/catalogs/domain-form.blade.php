<x-layouts.app title="Domínio de e-mail | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Domínios permitidos</div><h1>{{ $domain->exists ? 'Editar domínio' : 'Novo domínio' }}</h1></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ $domain->exists ? route('domains.update', $domain) : route('domains.store') }}">
        @csrf @if ($domain->exists) @method('PUT') @endif
        <div class="field"><label for="domain">Sufixo (sem @)</label><input id="domain" name="domain" value="{{ old('domain', $domain->domain) }}" placeholder="ufam.edu.br" required></div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('domains.index') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar</button></div>
    </form></section>
</x-layouts.app>
