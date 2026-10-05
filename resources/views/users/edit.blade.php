<x-layouts.app title="Editar usuário | Userboard">
    <div class="page-heading compact-heading"><div><div class="eyebrow">Usuários / {{ $user->name }}</div><h1>Editar dados</h1></div></div>
    <section class="form-card">
        <form method="POST" action="{{ route('users.update', $user) }}" class="stack-form">
            @csrf
            @method('PUT')
            <div class="field"><label for="name">Nome completo</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
            <div class="field"><label for="email_local">E-mail institucional</label><div class="email-composer">
                <input id="email_local" name="email_local" type="text" value="{{ old('email_local', $emailLocal) }}" placeholder="parte.local" required @disabled($domains->isEmpty())>
                <span>@</span>
                <select id="email_domain" name="email_domain" required @disabled($domains->isEmpty())><option value="">Selecione o domínio</option>@foreach($domains as $domain)<option value="{{ $domain->domain }}" @selected(old('email_domain',$emailDomain)===$domain->domain)>{{ $domain->domain }}{{ $domain->active ? '' : ' (inativo; endereço atual)' }}</option>@endforeach</select>
            </div>@if($domains->isEmpty())<small>Não há domínios ativos; ative um domínio antes de alterar o e-mail.</small>@endif</div>
            <div class="field"><label for="functional_id">Matrícula/SIAPE</label><input id="functional_id" name="functional_id" value="{{ old('functional_id', $user->functional_id) }}" required></div>
            <div class="field"><label for="phone">Telefone</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required></div>
            @if ($user->profile === 'aluno')
                <div class="field"><label for="course">Curso</label><select id="course" name="course" required>@foreach (($options['course'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('course', $user->course) === $option->value)>{{ $option->value }}{{ $option->active ? '' : ' (inativo; valor atual)' }}</option>@endforeach</select></div>
            @elseif (in_array($user->profile, ['professor', 'tecnico'], true))
                @if ($user->profile === 'tecnico')<div class="field"><label for="job_title">Cargo</label><select id="job_title" name="job_title" required>@foreach (($options['job_title'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('job_title', $user->job_title) === $option->value)>{{ $option->value }}{{ $option->active ? '' : ' (inativo; valor atual)' }}</option>@endforeach</select></div>@endif
                <div class="field"><label for="employment_link">Vínculo</label><select id="employment_link" name="employment_link" required>@foreach (($options['employment_link'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('employment_link', $user->employment_link) === $option->value)>{{ $option->value }}{{ $option->active ? '' : ' (inativo; valor atual)' }}</option>@endforeach</select></div>
            @endif
            <div class="action-row"><a class="button button-quiet" href="{{ route('users.show', $user) }}">Cancelar</a><button class="button button-primary" type="submit">Salvar dados</button></div>
        </form>
    </section>
</x-layouts.app>