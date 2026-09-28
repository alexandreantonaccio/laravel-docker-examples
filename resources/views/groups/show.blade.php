<x-layouts.app title="{{ $group->name }} | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Grupo de permissões</div><h1>{{ $group->name }}</h1><p class="muted">{{ $group->description }}</p></div>
        @if (auth()->user()->hasPermissionTo('grupos_permissoes.editar'))<a class="button button-primary" href="{{ route('groups.edit', $group) }}">Editar grupo</a>@endif
    </div>
    <div class="split-layout">
        <section class="form-card"><h2>Permissões</h2><div class="option-list">@forelse ($group->permissions as $permission)<div class="option-row"><strong>{{ $permission->key }}</strong></div>@empty<p>Nenhuma permissão concedida.</p>@endforelse</div></section>
        <section class="form-card"><h2>Membros ({{ $group->users->count() }})</h2>
            @if (auth()->user()->hasPermissionTo('grupos_permissoes.atribuir_usuarios'))
                <form method="POST" action="{{ route('groups.members.sync', $group) }}" class="stack-form">
                    @csrf @method('PUT')
                    <div class="option-list">@foreach ($users as $user)<label class="checkbox-label"><input type="checkbox" name="user_ids[]" value="{{ $user->id }}" @checked($group->users->contains($user->id))> {{ $user->name }} <small>({{ $user->email }})</small></label>@endforeach</div>
                    <button class="button button-primary" type="submit">Salvar membros</button>
                </form>
            @else
                <ul>@foreach ($group->users as $user)<li>{{ $user->name }}</li>@endforeach</ul>
            @endif
        </section>
    </div>
    @if (auth()->user()->hasPermissionTo('grupos_permissoes.excluir'))
        <form method="POST" action="{{ route('groups.destroy', $group) }}" class="action-row" onsubmit="return confirm('Este grupo afeta {{ $group->users_count ?? $group->users->count() }} usuário(s). Excluir e remover as permissões herdadas?')">
            @csrf @method('DELETE')<button class="button button-quiet danger-text" type="submit">Excluir grupo</button>
        </form>
    @endif
</x-layouts.app>