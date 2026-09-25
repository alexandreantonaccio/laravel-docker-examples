<x-layouts.app title="Usuários | Userboard">
    <div class="page-heading">
        <div>
            <div class="eyebrow">Painel de administração</div>
            <h1>Usuários</h1>
            <p class="muted">Cadastre, consulte e mantenha os acessos da aplicação.</p>
        </div>
        <a class="button button-primary" href="{{ route('users.create') }}">Novo usuário</a>
    </div>

    <section class="table-card">
        @if ($users->isEmpty())
            <div class="empty-state">
                <h2>Nenhum usuário cadastrado</h2>
                <p class="muted">Comece criando o primeiro cadastro.</p>
                <a class="button button-primary" href="{{ route('users.create') }}">Criar usuário</a>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Cadastrado em</th>
                            <th class="table-actions">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="table-actions">
                                    <a class="text-link" href="{{ route('users.show', $user) }}">Ver</a>
                                    <a class="text-link" href="{{ route('users.edit', $user) }}">Editar</a>
                                    @if (! $user->is(auth()->user()))
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline-form" onsubmit="return confirm('Remover este usuário?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-button danger-text" type="submit">Excluir</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $users->links() }}</div>
        @endif
    </section>
</x-layouts.app>
