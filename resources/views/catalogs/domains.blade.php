<x-layouts.app title="Domínios de e-mail | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Cadastro de apoio</div><h1>Domínios de e-mail</h1><p class="muted">O domínio é usado para compor os endereços nos novos cadastros.</p></div>
        @if (auth()->user()->hasPermissionTo('dominios_email.criar'))<a class="button button-primary" href="{{ route('domains.create') }}">Novo domínio</a>@endif
    </div>
    <div class="action-row">
        <a class="button button-quiet" href="{{ route('domains.index') }}">Todos</a><a class="button button-quiet" href="{{ route('domains.index', ['status'=>'active']) }}">Ativos</a><a class="button button-quiet" href="{{ route('domains.index', ['status'=>'inactive']) }}">Desativados</a>
    </div>
    <section class="table-card table-wrap"><table><thead><tr><th>Domínio</th><th>Status</th><th>Padrão</th><th>Usuários</th><th class="table-actions">Ações</th></tr></thead><tbody>
    @forelse ($domains as $domain)<tr><td>@ {{ $domain->domain }}</td><td>{{ $domain->active ? 'Ativo' : 'Desativado' }}</td><td>{{ $domain->is_default ? 'Sim' : 'Não' }}</td><td>{{ $domain->usage_count }}</td><td class="table-actions">
        @if (auth()->user()->hasPermissionTo('dominios_email.editar'))<a class="text-link" href="{{ route('domains.edit', $domain) }}">Editar</a>
            @if ($domain->active && ! $domain->is_default)<form class="inline-form" method="POST" action="{{ route('domains.default', $domain) }}">@csrf @method('PATCH')<button class="text-button" type="submit">Definir padrão</button></form>@endif
        @endif
        @if (auth()->user()->hasPermissionTo('dominios_email.desativar'))<form class="inline-form" method="POST" action="{{ route('domains.toggle', $domain) }}">@csrf @method('PATCH')<button class="text-button" type="submit">{{ $domain->active ? 'Desativar' : 'Reativar' }}</button></form>@endif
    </td></tr>@empty<tr><td colspan="5" class="empty-state">Nenhum domínio cadastrado.</td></tr>@endforelse
    </tbody></table></section>
</x-layouts.app>
