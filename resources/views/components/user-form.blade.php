@csrf

<div class="field">
    <label for="name">Nome</label>
    <input id="name" name="name" type="text" value="{{ old('name', $user->name ?? '') }}" required autofocus>
</div>

<div class="field">
    <label for="email">E-mail</label>
    <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required>
</div>

<div class="field">
    <label for="password">Senha {{ isset($user) ? '(opcional)' : '' }}</label>
    <input id="password" name="password" type="password" {{ isset($user) ? '' : 'required' }} minlength="8">
    <small>{{ isset($user) ? 'Preencha apenas se quiser trocar a senha.' : 'Use pelo menos 8 caracteres.' }}</small>
</div>

<div class="field">
    <label for="password_confirmation">Confirme a senha</label>
    <input id="password_confirmation" name="password_confirmation" type="password" {{ isset($user) ? '' : 'required' }} minlength="8">
</div>

<div class="form-actions">
    <a class="button button-quiet" href="{{ route('users.index') }}">Cancelar</a>
    <button class="button button-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
</div>
