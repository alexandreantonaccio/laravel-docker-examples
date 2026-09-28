<x-layouts.app title="{{ $user->name }} | Userboard">
    <div class="page-heading compact-heading">
        <div><div class="eyebrow">{{ $isOwnProfile ? 'Meus dados' : 'Usuários / Detalhes' }}</div><h1>{{ $user->name }}</h1></div>
    </div>

    <section class="detail-card">
        <div class="detail-row"><span>Nome</span><strong>{{ $user->name }}</strong></div>
        <div class="detail-row"><span>E-mail</span><strong>{{ $user->email }}</strong></div>
        <div class="detail-row"><span>{{ $user->profile === 'aluno' ? 'Matrícula' : 'SIAPE' }}</span><strong>{{ $user->functional_id ?? '—' }}</strong></div>
        <div class="detail-row"><span>Telefone</span><strong>{{ $user->phone ?? '—' }}</strong></div>
        <div class="detail-row"><span>Perfil</span><strong>{{ $user->profile ? ucfirst($user->profile) : 'Não definido' }}</strong></div>
        @if ($user->profile === 'aluno')
            <div class="detail-row"><span>Curso</span><strong>{{ $user->course ?? '—' }}</strong></div>
            <div class="detail-row"><span>Comprovante</span><strong>{{ $user->enrollment_proof_path ? 'Anexado' : 'Validado externamente ou ausente' }}</strong></div>
        @elseif (in_array($user->profile, ['professor', 'tecnico'], true))
            <div class="detail-row"><span>Cargo</span><strong>{{ $user->job_title ?? '—' }}</strong></div>
            <div class="detail-row"><span>Vínculo</span><strong>{{ $user->employment_link ?? '—' }}</strong></div>
        @endif
        <div class="detail-row"><span>Status do cadastro</span><strong>{{ $user->registration_status }}</strong></div>
        <div class="detail-row"><span>Situação</span><strong>{{ $user->is_active ? 'Ativo' : 'Desativado' }}</strong></div>
        @if ($user->documentation_validated_at)
            <div class="detail-row"><span>Validação registrada</span><strong>{{ $user->documentation_validated_at->format('d/m/Y H:i') }} · {{ $user->documentation_notes }}</strong></div>
        @endif
        @if ($user->rejection_reason)
            <div class="detail-row"><span>Motivo da rejeição</span><strong>{{ $user->rejection_reason }}</strong></div>
        @endif
    </section>

    <div class="action-row">
        @if ($isOwnProfile)
            <a class="button button-primary" href="{{ route('password.change') }}">Alterar senha</a>
            <span class="muted">Para alterar outros dados, procure a sala de apoio.</span>
        @else
            @if (auth()->user()->hasPermissionTo('usuarios.editar.qualquer'))
                <a class="button button-primary" href="{{ route('users.edit', $user) }}">Editar dados</a>
            @endif
            @if (auth()->user()->hasPermissionTo('usuarios.alterar_perfil'))
                <a class="button button-quiet" href="{{ route('users.profile.edit', $user) }}">Alterar perfil</a>
            @endif
            @if (auth()->user()->hasPermissionTo('usuarios.gerenciar_permissoes'))
                <a class="button button-quiet" href="{{ route('users.permissions', $user) }}">Permissões</a>
            @endif
            @if (auth()->user()->hasPermissionTo('usuarios.resetar_senha'))
                <form method="POST" action="{{ route('users.password.reset', $user) }}">@csrf<button class="button button-quiet">Enviar link de senha</button></form>
            @endif
            @if (auth()->user()->hasPermissionTo('usuarios.desativar'))
                @if ($user->is_active)
                    <form method="POST" action="{{ route('users.deactivate', $user) }}" class="stack-form" onsubmit="return confirm('Desativar este usuário?')">
                        @csrf
                        <div class="field"><label for="reason">Motivo (opcional)</label><input id="reason" name="reason" maxlength="2000"></div>
                        <button class="button button-quiet" type="submit">Desativar</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('users.activate', $user) }}">@csrf<button class="button button-primary" type="submit">Reativar</button></form>
                @endif
            @endif
        @endif
    </div>
    @if (! $isOwnProfile && auth()->user()->hasPermissionTo('usuarios.listar'))
        <a class="back-link" href="{{ route('users.index') }}">Voltar à listagem</a>
    @endif
</x-layouts.app>