<x-layouts.app title="Criar cadastro | Userboard">
    <section class="auth-card">
        <div class="eyebrow">Primeiro acesso</div>
        <h1>Crie seu cadastro</h1>
        <p class="muted">Seu acesso será liberado após confirmar o e-mail e aguardar a análise.</p>

        <form method="POST" action="{{ route('register.store') }}" class="stack-form" enctype="multipart/form-data" data-registration-form>
            @csrf
            <div class="field">
                <label for="profile">Perfil</label>
                <select id="profile" name="profile" required data-profile-select>
                    <option value="">Selecione</option>
                    @foreach (config('users.profiles') as $profile)
                        <option value="{{ $profile }}" @selected(old('profile') === $profile)>{{ ucfirst($profile) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="functional_id"><span data-functional-label>Matrícula/SIAPE</span></label>
                <input id="functional_id" name="functional_id" type="text" value="{{ old('functional_id') }}" required>
            </div>
            <div class="field">
                <label for="name">Nome completo</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name">
            </div>
            <div class="field">
                <label for="email_local">E-mail institucional</label>
                <div class="email-composer">
                    <input id="email_local" name="email_local" type="text" value="{{ old('email_local') }}" placeholder="parte.local" required autocomplete="username" @disabled($domains->isEmpty())>
                    <span>@</span>
                    <select id="email_domain" name="email_domain" required @disabled($domains->isEmpty())>
                        <option value="">Selecione o domínio</option>
                        @foreach ($domains as $domain)
                            <option value="{{ $domain->domain }}" @selected(old('email_domain', $domain->is_default ? $domain->domain : '') === $domain->domain)>{{ $domain->domain }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($domains->isEmpty())<small>Não há domínios de e-mail ativos. Solicite a ativação de um domínio ao administrador.</small>@endif
            </div>
            <div class="field">
                <label for="phone">Telefone</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required>
            </div>
            <div class="field profile-field" data-profile="aluno" hidden>
                <label for="course">Curso</label>
                <select id="course" name="course" data-required-profile>
                    <option value="">Selecione</option>
                    @foreach (($options['course'] ?? collect()) as $option)
                        <option value="{{ $option->value }}" @selected(old('course') === $option->value)>{{ $option->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field profile-field" data-profile="aluno" hidden>
                <label for="enrollment_proof">Comprovante de matrícula</label>
                <input id="enrollment_proof" name="enrollment_proof" type="file" accept=".pdf,.png,.jpg,.jpeg" data-required-profile>
                <small>PDF ou imagem, até 1 MB.</small>
            </div>
            <div class="field profile-field" data-profile="tecnico" hidden>
                <label for="job_title">Cargo</label>
                <select id="job_title" name="job_title" data-required-profile>
                    <option value="">Selecione</option>
                    @foreach (($options['job_title'] ?? collect()) as $option)
                        <option value="{{ $option->value }}" @selected(old('job_title') === $option->value)>{{ $option->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field profile-field" data-profile="professor tecnico" hidden>
                <label for="employment_link">Vínculo</label>
                <select id="employment_link" name="employment_link" data-required-profile>
                    <option value="">Selecione</option>
                    @foreach (($options['employment_link'] ?? collect()) as $option)
                        <option value="{{ $option->value }}" @selected(old('employment_link') === $option->value)>{{ $option->value }}</option>
                    @endforeach
                </select>
            </div>
            <p class="muted profile-field" data-profile="professor tecnico" hidden>Validação presencial: compareça à sala de apoio.</p>
                        <p class="muted profile-field" data-profile="professor" hidden>Cargo fixo: {{ config('users.professor_job_title') }}.</p>
            <div class="field">
                <label for="password">Senha</label>
                <input id="password" name="password" type="password" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d).{8,}" autocomplete="new-password">
                <small>No mínimo 8 caracteres, com letras e números.</small>
            </div>
            <div class="field">
                <label for="password_confirmation">Confirme a senha</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
            </div>
            <div class="form-progress" data-form-progress aria-live="polite"></div>
            <button class="button button-primary button-wide" type="submit">Cadastrar</button>
        </form>

        <p class="auth-footer">Já possui uma conta? <a href="{{ route('login') }}">Entrar</a></p>
    </section>
</x-layouts.app>