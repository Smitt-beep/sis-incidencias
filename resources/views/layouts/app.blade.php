<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel de Control')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="panel-body">
    <nav class="navbar navbar-expand-lg navbar-dark panel-navbar">
        <div class="container-fluid px-3 px-lg-4">
            <button class="navbar-toggler d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#panelSidebar" aria-controls="panelSidebar" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand me-auto" href="{{ route('dashboard') }}">Sistema de Incidencias</a>
            <span class="navbar-text text-white me-3 d-none d-sm-inline">{{ session('usuario_nombre') }} · {{ session('usuario_rol') }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
            </form>
        </div>
    </nav>

    <aside class="chatbot-panel" id="chatbot-panel" role="dialog" aria-labelledby="chatbot-title" aria-hidden="true">
        <header class="chatbot-header">
            <div class="chatbot-brand">
                <img class="chatbot-avatar" src="{{ asset('images/chatbot/tungasuquita.png') }}" alt="">
                <h2 class="h5 mb-0" id="chatbot-title">TungaBot</h2>
            </div>
            <button type="button" class="btn-close btn-close-white" id="chatbot-close" aria-label="Cerrar chat"></button>
        </header>
        <div class="chatbot-messages" id="chatbot-messages" role="log" aria-live="polite" aria-relevant="additions">
            <div class="chatbot-message chatbot-message-assistant">
                <span class="chatbot-message-sender">TungaBot</span>
                <p class="mb-0">¡Hola! Soy TungaBot, tu asistente del sistema de incidencias. ¿En qué puedo ayudarte?</p>
            </div>
        </div>
        <form class="chatbot-form" id="chatbot-form" data-endpoint="{{ route('chatbot.mensajes.store') }}">
            @csrf
            <label class="visually-hidden" for="chatbot-input">Escribe tu mensaje</label>
            <input class="form-control" id="chatbot-input" name="message" type="text" placeholder="Escribe tu mensaje..." autocomplete="off">
            <button class="btn btn-primary" type="submit" aria-label="Enviar mensaje">
                <svg class="chatbot-send-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M3.4 20.2 21 12 3.4 3.8 3 10.2l12.2 1.8L3 13.8l.4 6.4Z"/>
                </svg>
            </button>
        </form>
    </aside>
    <button type="button" class="chatbot-toggle" id="chatbot-toggle" aria-label="Abrir chat con TungaBot" aria-controls="chatbot-panel" aria-expanded="false">
        <img class="chatbot-toggle-image" src="{{ asset('images/chatbot/tungasuquita.png') }}" alt="">
        <span class="chatbot-toggle-label">TungaBot</span>
    </button>

    <div class="d-lg-flex panel-shell">
        <aside class="offcanvas-lg offcanvas-start bg-white border-end panel-sidebar" tabindex="-1" id="panelSidebar" aria-labelledby="panelSidebarLabel">
            <div class="offcanvas-header d-lg-none border-bottom">
                <h2 class="offcanvas-title h5" id="panelSidebarLabel">Menú</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#panelSidebar" aria-label="Cerrar menú"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-3">
                <div class="text-uppercase small fw-semibold text-secondary px-3 mb-2">Panel</div>
                <nav class="nav nav-pills flex-column gap-1">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-dark' }}" href="{{ route('dashboard') }}">Inicio</a>

                    @php
                        $rol = session('usuario_rol');
                    @endphp

                    @if ($rol === 'Administrador')
                        <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : 'text-dark' }}" href="{{ route('usuarios.index') }}">Usuarios</a>
                        <a class="nav-link {{ request()->routeIs('incidencias.*') ? 'active' : 'text-dark' }}" href="{{ route('incidencias.index') }}">Incidencias</a>
                        <a class="nav-link {{ request()->routeIs('secciones.*') ? 'active' : 'text-dark' }}" href="{{ route('secciones.index') }}">Grado</a>
                        <a class="nav-link {{ request()->routeIs('reporte-profesional.*') ? 'active' : 'text-dark' }}" href="{{ route('reporte-profesional.index') }}">Reporte Profesional</a>
                    @elseif ($rol === 'Profesor')
                        <a class="nav-link {{ request()->routeIs('incidencias.*') ? 'active' : 'text-dark' }}" href="{{ route('incidencias.index') }}">Incidencias</a>
                        <a class="nav-link {{ request()->routeIs('estudiantes.*') ? 'active' : 'text-dark' }}" href="{{ route('estudiantes.index') }}">Alumnos</a>
                        <a class="nav-link {{ request()->routeIs('reporte-profesional.*') ? 'active' : 'text-dark' }}" href="{{ route('reporte-profesional.index') }}">Reporte Profesional</a>
                    @elseif (in_array($rol, ['Directora', 'Subdirector'], true))
                        <a class="nav-link {{ request()->routeIs('incidencias.*') && ! request()->routeIs('incidencias.urgencias') ? 'active' : 'text-dark' }}" href="{{ route('incidencias.index') }}">Incidencias</a>
                        <a class="nav-link {{ request()->routeIs('secciones.*') ? 'active' : 'text-dark' }}" href="{{ route('secciones.index') }}">Grado</a>
                        <a class="nav-link {{ request()->routeIs('reporte-profesional.*') ? 'active' : 'text-dark' }}" href="{{ route('reporte-profesional.index') }}">Reporte Profesional</a>
                        <a class="nav-link {{ request()->routeIs('estudiantes.*') ? 'active' : 'text-dark' }}" href="{{ route('estudiantes.index') }}">Alumnos</a>
                        <a class="nav-link {{ request()->routeIs('incidencias.urgencias') ? 'active' : 'text-dark' }}" href="{{ route('incidencias.urgencias') }}">Urgencias</a>
                    @endif
                </nav>
            </div>
        </aside>

        <main class="panel-main container-fluid py-4 px-3 px-lg-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>