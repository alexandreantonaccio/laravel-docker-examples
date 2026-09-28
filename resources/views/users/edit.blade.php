<x-layouts.app title="Editar usuário | Userboard">
    <div class="page-heading compact-heading"><div><div class="eyebrow">Usuários / {{ $user->name }}</div><h1>Editar dados</h1></div></div>
    <section class="form-card">
        <form method="POST" action="{{ route('users.update', $user) }}" class="stack-form">
            @csrf
            @method('PUT')
            <div class="field"><label for="name">Nome completo</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
            <div class="field"><label for="email">E-mail institucional</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
            <div class="field"><label for="functional_id">Matrícula/SIAPE</label><input id="functional_id" name="functional_id" value="{{ old('functional_id', $user->functional_id) }}" required></div>
            <div class="field"><label for="phone">Telefone</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required></div>
            @if ($user->profile === 'aluno')
                <div class="field"><label for="course">Curso</label><select id="course" name="course" required>@foreach (($options['course'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('course', $user->course) === $option->value)>{{ $option->value }}</option>@endforeach</select></div>
            @elseif (in_array($user->profile, ['professor', 'tecnico'], true))
                @if ($user->profile === 'tecnico')<div class="field"><label for="job_title">Cargo</label><select id="job_title" name="job_title" required>@foreach (($options['job_title'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('job_title', $user->job_title) === $option->value)>{{ $option->value }}</option>@endforeach</select></div>@endif
                <div class="field"><label for="employment_link">Vínculo</label><select id="employment_link" name="employment_link" required>@foreach (($options['employment_link'] ?? collect()) as $option)<option value="{{ $option->value }}" @selected(old('employment_link', $user->employment_link) === $option->value)>{{ $option->value }}</option>@endforeach</select></div>
            @endif
            <div class="action-row"><a class="button button-quiet" href="{{ route('users.show', $user) }}">Cancelar</a><button class="button button-primary" type="submit">Salvar dados</button></div>
        </form>
    </section>
</x-layouts.app>