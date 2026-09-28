<x-layouts.app title="Usuários | Userboard">
    <div class="page-heading">
        <div>
            <div class="eyebrow">Administração</div>
            <h1>Usuários</h1>
            <p class="muted">Pesquise por nome, e-mail ou identificação funcional.</p>
        </div>
    </div>

    <section class="table-card">
        <form method="GET" action="{{ route('users.index') }}" class="filter-row">
            <div class="field">
                <label for="search">Pesquisa</label>
                <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nome, e-mail ou matrícula/SIAPE">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    @foreach (['pending_email' => 'Pendente de confirmação', 'email_confirmed' => 'E-mail confirmado', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="profile">Perfil</label>
                <select id="profile" name="profile">
                    <option value="">Todos</option>
                    @foreach (config('users.profiles') as $profile)
                        <option value="{{ $profile }}" @selected(($filters['profile'] ?? '') === $profile)>{{ ucfirst($profile) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="button button-primary" type="submit">Filtrar</button>
        </form>

        @if ($users->isEmpty())
            <div class="empty-state"><h2>Nenhum usuário encontrado</h2><p class="muted">Ajuste a pesquisa ou os filtros.</p></div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Matrícula/SIAPE</th><th>Status</th><th>Ativo</th><th class="table-actions">Ações</th></tr></thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->profile ? ucfirst($user->profile) : 'Não definido' }}</td>
                                <td>{{ $user->functional_id ?? '—' }}</td>
                                <td><span class="status-label">{{ ['pending_email' => 'Pendente', 'email_confirmed' => 'Confirmado', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado'][$user->registration_status] ?? $user->registration_status }}</span></td>
                                <td>{{ $user->is_active ? 'Sim' : 'Não' }}</td>
                                <td class="table-actions"><a class="text-link" href="{{ route('users.show', $user) }}">Detalhes</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $users->links() }}</div>
        @endif
    </section>
</x-layouts.app>