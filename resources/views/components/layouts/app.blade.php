<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Userboard' }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ auth()->check() ? route('home') : route('login') }}">Userboard</a>
        @auth
            <nav class="topbar-nav">
                @if (auth()->user()->hasPermissionTo('usuarios.visualizar.proprio'))
                    <a class="text-link" href="{{ route('profile.show') }}">Meus dados</a>
                @endif
                @if (auth()->user()->hasPermissionTo('usuarios.listar'))
                    <a class="text-link" href="{{ route('users.index') }}">Usuários</a>
                @endif
                @if (auth()->user()->hasPermissionTo('usuarios.aprovar'))
                    <a class="text-link" href="{{ route('users.pending') }}">Pendentes</a>
                @endif
                @if (auth()->user()->hasPermissionTo('grupos_permissoes.listar'))
                    <a class="text-link" href="{{ route('groups.index') }}">Grupos</a>
                @endif
                <a class="text-link" href="{{ route('bookings.calendar') }}">Agenda</a>
                @if (auth()->user()->hasPermissionTo('ambientes.listar'))
                    <a class="text-link" href="{{ route('environments.index') }}">Ambientes</a>
                @endif
                @if (auth()->user()->hasPermissionTo('grupos_ambientes.listar'))
                    <a class="text-link" href="{{ route('environment-groups.index') }}">Grupos de ambientes</a>
                @endif
                <a class="text-link" href="{{ route('materials.index') }}">Materiais</a>
                <span class="user-label">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button button-quiet" type="submit">Sair</button>
                </form>
            </nav>
        @endauth
    </header>

    <main class="page-shell">
        @if (session('status'))
            <div class="flash flash-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash flash-error">
                <strong>Revise os dados:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
