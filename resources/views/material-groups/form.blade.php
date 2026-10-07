<x-layouts.app title="Grupo de materiais | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Grupos de materiais</div><h1>{{ $group->exists ? 'Editar grupo' : 'Novo grupo' }}</h1></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ $group->exists ? route('material-groups.update', $group) : route('material-groups.store') }}">
        @csrf @if($group->exists) @method('PUT') @endif
        <div class="field"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name', $group->name) }}" maxlength="255" required></div>
        <div class="field"><label for="notification_emails_text">E-mails para notificação (um por linha, opcional)</label><textarea id="notification_emails_text" name="notification_emails_text">{{ old('notification_emails_text', implode("\n", $group->notification_emails ?? [])) }}</textarea></div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('material-groups.index') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar</button></div>
    </form></section>
</x-layouts.app>
