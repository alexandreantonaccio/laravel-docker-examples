    <form method="POST" action="{{ route('users.profile.update', $user) }}" class="stack-form">
<x-layouts.app title="Alterar perfil de {{ $user->name }} | Userboard">
    <div class="page-heading"><div><div class="eyebrow">Usuários / {{ $user->name }}</div><h1>Alterar perfil</h1><p class="muted">Os dados específicos anteriores serão arquivados no histórico.</p></div></div>
    <section class="form-card">
        <form method="POST" action="{{ route('users.profile.update', $user) }}" class="stack-form" data-profile-select>
            @csrf
            @method('PUT')
            <div class="field"><label for="profile">Novo perfil</label><select id="profile" name="profile" required><option value="">Selecione</option>@foreach (config('users.profiles') as $profile)<option value="{{ $profile }}" @selected(old('profile', $user->profile) === $profile)>{{ ucfirst($profile) }}</option>@endforeach</select></div>
            <div class="field"><label for="course">Curso (Aluno)</label><select id="course" name="course"><option value="">Selecione</option>@foreach (($options['course'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('course', $user->course) === $option->value)>{{ $option->value }}</option>@endforeach</select></div>
            <div class="field"><label for="job_title">Cargo (Técnico)</label><select id="job_title" name="job_title"><option value="">Selecione</option>@foreach (($options['job_title'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('job_title', $user->job_title) === $option->value)>{{ $option->value }}</option>@endforeach</select></div>
            <div class="field"><label for="employment_link">Vínculo (Técnico/Professor)</label><select id="employment_link" name="employment_link"><option value="">Selecione</option>@foreach (($options['employment_link'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('employment_link', $user->employment_link) === $option->value)>{{ $option->value }}</option>@endforeach</select></div>
            <div class="field"><label for="documentation_notes">Validação externa ao tornar Aluno</label><textarea id="documentation_notes" name="documentation_notes" maxlength="2000"></textarea></div>
            <div class="action-row"><a class="button button-quiet" href="{{ route('users.show', $user) }}">Cancelar</a><button class="button button-primary" type="submit">Salvar novo perfil</button></div>
        </form>
    </section>
</x-layouts.app>