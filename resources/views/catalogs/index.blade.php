<x-layouts.app :title="$config['label'].' | Userboard'">
    <div class="page-heading">
        <div><div class="eyebrow">Cadastros de apoio</div><h1>{{ $config['label'] }}</h1><p class="muted">Registros armazenados como cópia de texto nos usuários existentes.</p></div>
        @if (auth()->user()->hasPermissionTo($config['permission'].'.criar'))
            <a class="button button-primary" href="{{ route('catalogs.create', $catalog) }}">Novo registro</a>
        @endif
    </div>
    <div class="action-row">
        <a class="button button-quiet" href="{{ route('catalogs.index', [$catalog, 'status' => 'all']) }}">Todos</a>
        <a class="button button-quiet" href="{{ route('catalogs.index', [$catalog, 'status' => 'active']) }}">Ativos</a>
        <a class="button button-quiet" href="{{ route('catalogs.index', [$catalog, 'status' => 'inactive']) }}">Desativados</a>
    </div>
    <section class="table-card table-wrap">
        <table><thead><tr><th>Nome</th><th>Status</th><th>Usuários</th><th class="table-actions">Ações</th></tr></thead><tbody>
        @forelse ($items as $item)
            <tr>
                <td>{{ $item->value }}</td><td><span class="status-label">{{ $item->active ? 'Ativo' : 'Desativado' }}</span></td><td>{{ $item->usage_count }}</td>
                <td class="table-actions">
                    @if (auth()->user()->hasPermissionTo($config['permission'].'.editar'))
                        <a class="text-link" href="{{ route('catalogs.edit', [$catalog, $item->id]) }}">Editar</a>
                    @endif
                    @if (auth()->user()->hasPermissionTo($config['permission'].'.desativar'))
                        <form class="inline-form" method="POST" action="{{ route('catalogs.toggle', [$catalog, $item->id]) }}">@csrf @method('PATCH')
                            <button class="text-button" type="submit">{{ $item->active ? 'Desativar' : 'Reativar' }}</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="empty-state">Nenhum registro cadastrado.</td></tr>
        @endforelse
        </tbody></table>
    </section>
</x-layouts.app>
