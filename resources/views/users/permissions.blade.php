<x-layouts.app title="Permissões de {{ $user->name }} | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Usuários / {{ $user->name }}</div><h1>Permissões efetivas</h1><p class="muted">Negar individualmente prevalece sobre permissões herdadas de grupos.</p></div></div>
    <section class="form-card" style="max-width:none">
        <form method="POST" action="{{ route('users.permissions.update', $user) }}" class="stack-form">
            @csrf
            @method('PUT')
            <div class="option-list">
                @foreach ($permissions as $permission)
                    @php
                        $groupsForPermission = $permission->groups->pluck('name');
                        $effect = $adjustments->get($permission->id);
                        $isEffective = in_array($permission->key, $effective, true);
                    @endphp
                    <div class="option-row">
                        <div style="flex:1">
                            <strong>{{ $permission->key }}</strong>
                            <small>{{ $permission->label }} · {{ $isEffective ? 'Efetiva' : 'Não efetiva' }}</small>
                            <small>{{ $groupsForPermission->isNotEmpty() ? 'Grupos: '.$groupsForPermission->join(', ') : 'Sem concessão por grupo' }}</small>
                        </div>
                        @if ($groupsForPermission->isNotEmpty())
                        <div class="field">
                            <label for="adjustment-{{ $permission->id }}">Ajuste individual</label>
                            <select id="adjustment-{{ $permission->id }}" name="adjustments[{{ $permission->id }}]">
                                <option value="inherit" @selected($effect === null)>Sem ajuste</option>
                                <option value="grant" @selected($effect === 'grant')>Conceder individualmente</option>
                                <option value="deny" @selected($effect === 'deny')>Negar individualmente</option>
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="action-row"><button class="button button-primary" type="submit">Salvar permissões</button><a class="button button-quiet" href="{{ route('users.show', $user) }}">Voltar ao cadastro</a></div>
        </form>
    </section>
</x-layouts.app>