<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - Sistema de Incidencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Paleta institucional I.E. Tungasuca (misma que el login) */
        :root {
            --azul-principal: #0756A3;
            --azul-secundario: #1689D4;
            --celeste: #48B9E8;
            --azul-oscuro: #172B4D;
        }

        body.auth-page {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: #fff;
            background:
                radial-gradient(circle at 12% 8%, rgba(22, 137, 212, .45), transparent 38%),
                radial-gradient(circle at 92% 92%, rgba(72, 185, 232, .28), transparent 40%),
                linear-gradient(160deg, #0b1d3a 0%, #0a1830 55%, #07122a 100%);
        }

        .auth-card {
            width: 100%;
            max-width: 660px;
            padding: 2rem 1.75rem 1.5rem;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .14);
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .45);
        }

        .auth-crest {
            display: block;
            height: 64px;
            width: auto;
            margin: 0 auto .75rem;
            filter: drop-shadow(0 4px 14px rgba(72, 185, 232, .35));
        }

        .auth-title {
            text-align: center;
            font-size: 1.45rem;
            font-weight: 700;
            margin: 0;
        }

        .auth-subtitle {
            text-align: center;
            font-size: .8rem;
            color: #b6c6e3;
            margin: .25rem 0 1.5rem;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem 1.1rem;
        }

        @media (max-width: 575.98px) {
            .auth-grid { grid-template-columns: 1fr; }
        }

        .auth-label {
            display: block;
            font-size: .75rem;
            font-weight: 600;
            color: #dbe7fb;
            margin-bottom: .35rem;
        }

        .auth-input { position: relative; }

        .auth-card .form-control,
        .auth-card .form-select {
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: 2.8rem;
            padding: 0 .9rem;
            border-radius: .7rem;
            border: 1px solid transparent;
            background-color: #fff;
            color: var(--azul-oscuro);
            font-size: .92rem;
        }

        .auth-card .form-select { padding-right: 2.2rem; }
        .auth-card .auth-input .form-control { padding-right: 2.7rem; }

        .auth-card .form-control:focus,
        .auth-card .form-select:focus {
            border-color: var(--celeste);
            box-shadow: 0 0 0 .2rem rgba(72, 185, 232, .3);
        }

        .auth-card .is-invalid {
            border-color: #f87171;
            background-image: none;
        }

        .auth-card .form-select.is-invalid {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }

        .auth-role {
            display: inline-flex;
            align-items: center;
            height: 2.8rem;
            padding: 0 1rem;
            border-radius: .7rem;
            font-size: .9rem;
            font-weight: 600;
            color: #bfe9fb;
            background: rgba(72, 185, 232, .14);
            border: 1px solid rgba(72, 185, 232, .4);
        }

        .toggle-password {
            position: absolute;
            right: .55rem;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            padding: .35rem;
            line-height: 0;
            color: #6b7a99;
            cursor: pointer;
        }

        .toggle-password:hover { color: var(--azul-principal); }
        .toggle-password svg { width: 1.1rem; height: 1.1rem; }

        .auth-error {
            display: block;
            margin-top: .3rem;
            font-size: .78rem;
            color: #fca5a5;
        }

        .auth-alert {
            font-size: .85rem;
            padding: .7rem .9rem;
            border-radius: .7rem;
            margin-bottom: 1.1rem;
            background: rgba(248, 113, 113, .15);
            border: 1px solid rgba(248, 113, 113, .5);
            color: #fecaca;
        }

        .auth-alert ul { margin: 0; padding-left: 1.1rem; }

        .auth-btn {
            width: 100%;
            height: 2.9rem;
            margin-top: 1.4rem;
            border: 0;
            border-radius: .7rem;
            font-weight: 700;
            font-size: .95rem;
            color: #fff;
            background: linear-gradient(90deg, var(--azul-principal), var(--azul-secundario) 55%, var(--celeste));
            box-shadow: 0 8px 22px rgba(22, 137, 212, .4);
            transition: transform .15s ease, filter .15s ease;
        }

        .auth-btn:hover { transform: translateY(-1px); filter: brightness(1.08); }
        .auth-btn:focus-visible { outline: 3px solid var(--celeste); outline-offset: 2px; }

        .auth-link {
            text-align: center;
            margin-top: 1.1rem;
            font-size: .85rem;
        }

        .auth-link a { color: #7dd3fc; text-decoration: none; font-weight: 600; }
        .auth-link a:hover { text-decoration: underline; }

        .auth-footer {
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, .1);
            text-align: center;
            font-size: .7rem;
            color: #8ea2c7;
        }
    </style>
</head>
<body class="auth-page">

    <main class="auth-card">
        <img src="{{ asset('images/escudo-ie-tungasuca.png') }}" alt="Escudo de la I.E. Tungasuca" class="auth-crest">
        <h1 class="auth-title">Crear cuenta</h1>
        <p class="auth-subtitle">Tu cuenta quedará pendiente hasta que el administrador la apruebe</p>

        @if ($errors->any())
            <div class="auth-alert" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" novalidate>
            @csrf

            <div class="auth-grid">
                <div>
                    <label for="name" class="auth-label">Nombre completo</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           maxlength="255"
                           placeholder="Nombres y apellidos"
                           required>
                    @error('name')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="dni" class="auth-label">DNI</label>
                    <input type="text"
                           name="dni"
                           id="dni"
                           class="form-control @error('dni') is-invalid @enderror"
                           value="{{ old('dni') }}"
                           maxlength="8"
                           pattern="[0-9]{8}"
                           inputmode="numeric"
                           placeholder="8 dígitos"
                           required>
                    @error('dni')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="email" class="auth-label">Correo electrónico</label>
                    <input type="email"
                           name="email"
                           id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           maxlength="255"
                           autocomplete="email"
                           placeholder="correo@ejemplo.com"
                           required>
                    @error('email')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <span class="auth-label">Cargo / Rol</span>
                    <div class="auth-role">Profesor</div>
                </div>

                <div>
                    <label for="password" class="auth-label">Contraseña</label>
                    <div class="auth-input">
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password"
                               placeholder="Cree una contraseña"
                               required>
                        <button type="button" class="toggle-password" data-target="password" aria-label="Mostrar contraseña">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="auth-label">Confirmar contraseña</label>
                    <div class="auth-input">
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="form-control"
                               autocomplete="new-password"
                               placeholder="Repita la contraseña"
                               required>
                        <button type="button" class="toggle-password" data-target="password_confirmation" aria-label="Mostrar contraseña">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="auth-btn">Registrar cuenta</button>

            <div class="auth-link">
                <a href="{{ route('login') }}">Volver al login</a>
            </div>
        </form>

        <div class="auth-footer">Acceso exclusivo para personal autorizado</div>
    </main>

    <script>
        // Mostrar / ocultar contraseña (ambos campos)
        document.querySelectorAll('.toggle-password').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var input = document.getElementById(boton.dataset.target);
                var visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            });
        });
    </script>
</body>
</html>