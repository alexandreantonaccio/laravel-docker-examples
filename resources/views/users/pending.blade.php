<x-layouts.app title="Cadastros pendentes | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Análise administrativa</div><h1>Cadastros pendentes</h1><p class="muted">Somente e-mails confirmados aguardam esta decisão.</p></div></div>
    @forelse ($users as $user)
        <section class="form-card" style="max-width:none;margin-bottom:24px">
            <div class="split-layout">
                <div>
                    <h2>{{ $user->name }}</h2>
                    <p class="muted">{{ ucfirst($user->profile) }} · {{ $user->email }} · {{ $user->functional_id }}</p>
                    @if ($user->profile === 'aluno')
                        <p>Curso: {{ $user->course }} · Comprovante: @if ($user->enrollment_proof_path)<a href="{{ route('users.enrollment-proof', $user) }}">Abrir arquivo</a>@else não anexado @endif</p>
                    @else
                        <p>Validação presencial necessária · {{ $user->job_title }} · {{ $user->employment_link }}</p>
                    @endif
                    @if (auth()->user()->hasPermissionTo('usuarios.visualizar.qualquer'))
                        <a class="text-link" href="{{ route('users.show', $user) }}">Ver cadastro</a>
                    @endif
                    @if (auth()->user()->hasPermissionTo('usuarios.alterar_perfil'))
                        <a class="text-link" href="{{ route('users.profile.edit', $user) }}">Corrigir perfil</a>
                    @endif
                </div>
                <div>
                    <form method="POST" action="{{ route('users.approve', $user) }}" class="stack-form">
                        @csrf
                        <strong>Validar documentação e aprovar</strong>
                        <div class="field"><label>Grupo(s) de permissões, obrigatório</label>
                            @forelse ($groups as $group)
                                <label class="checkbox-label"><input type="checkbox" name="group_ids[]" value="{{ $group->id }}"> {{ $group->name }}</label>
                            @empty
                                <small>Crie um grupo antes de aprovar cadastros.</small>
                            @endforelse
                        </div>
                        <div class="field"><label for="notes-{{ $user->id }}">Registro de validação</label><textarea id="notes-{{ $user->id }}" name="documentation_notes" required maxlength="2000"></textarea></div>
                        <button class="button button-primary" type="submit">Aprovar cadastro</button>
                    </form>
                    <form method="POST" action="{{ route('users.reject', $user) }}" class="stack-form" style="margin-top:24px">
                        @csrf
                        <div class="field"><label for="reason-{{ $user->id }}">Motivo da rejeição</label><textarea id="reason-{{ $user->id }}" name="rejection_reason" required maxlength="2000"></textarea></div>
                        <button class="button button-quiet" type="submit">Rejeitar cadastro</button>
                    </form>
                </div>
            </div>
        </section>
    @empty
        <div class="empty-state"><h2>Nenhum cadastro aguardando análise</h2></div>
    @endforelse
    <div class="pagination">{{ $users->links() }}</div>
</x-layouts.app>