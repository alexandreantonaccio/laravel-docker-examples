<x-layouts.app title="Grupo de ambientes | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Grupos de ambientes</div><h1>{{ $group->exists ? 'Editar grupo' : 'Novo grupo' }}</h1></div></div>
    <section class="form-card"><form class="stack-form" method="POST" action="{{ $group->exists ? route('environment-groups.update',$group) : route('environment-groups.store') }}">
        @csrf @if ($group->exists) @method('PUT') @endif
        <div class="field"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name',$group->name) }}" required></div>
        <div class="field"><label for="notification_emails_text">E-mails de notificação (um por linha)</label><textarea id="notification_emails_text" name="notification_emails_text" required>{{ old('notification_emails_text',implode("\n",$group->notification_emails ?? [])) }}</textarea></div>
        <div class="field"><label>Ambientes associados</label>
            @foreach ($environments as $environment)
                <label class="checkbox-label"><input type="checkbox" name="environment_ids[]" value="{{ $environment->id }}" @checked(collect(old('environment_ids',$group->environments->pluck('id')->all()))->map(fn($id)=>(int)$id)->contains((int)$environment->id)) @disabled($environment->environment_group_id && (int)$environment->environment_group_id !== (int)$group->id)>{{ $environment->name }}{{ $environment->environment_group_id && (int)$environment->environment_group_id !== (int)$group->id ? ' (associado a outro grupo)' : '' }}</label>
            @endforeach
        </div>
        <div class="form-actions"><a class="button button-quiet" href="{{ route('environment-groups.index') }}">Cancelar</a><button class="button button-primary" type="submit">Salvar</button></div>
    </form></section>
</x-layouts.app>
