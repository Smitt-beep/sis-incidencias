<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Incidencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Paleta institucional I.E. Tungasuca */
        :root {
            --azul-principal: #0756A3;
            --azul-secundario: #1689D4;
            --celeste: #48B9E8;
            --azul-oscuro: #172B4D;
        }

        body.login-page {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 1.5rem 1rem;
            color: #fff;
            background:
                radial-gradient(circle at 12% 8%, rgba(22, 137, 212, .45), transparent 38%),
                radial-gradient(circle at 92% 92%, rgba(72, 185, 232, .28), transparent 40%),
                linear-gradient(160deg, #0b1d3a 0%, #0a1830 55%, #07122a 100%);
        }

        .login-year {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 600;
            text-transform: uppercase;
            padding: .35rem .9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #dbeafe;
        }

        .login-year::before {
            content: "";
            display: inline-block;
            width: .45rem;
            height: .45rem;
            margin-right: .5rem;
            border-radius: 50%;
            background: #34d399;
            vertical-align: middle;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem 1.75rem 1.5rem;
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .14);
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .45);
        }

        .login-crest {
            display: block;
            height: 76px;
            width: auto;
            margin: 0 auto .9rem;
            filter: drop-shadow(0 4px 14px rgba(72, 185, 232, .35));
        }

        .login-title {
            text-align: center;
            font-size: 1.45rem;
            font-weight: 700;
            margin: 0;
        }

        .login-subtitle {
            text-align: center;
            font-size: .8rem;
            color: #b6c6e3;
            margin: .25rem 0 1.5rem;
        }

        .login-label {
            display: block;
            font-size: .75rem;
            font-weight: 600;
            color: #dbe7fb;
            margin-bottom: .35rem;
        }

        .login-field {
            position: relative;
            margin-bottom: 1.1rem;
        }

        .login-input { position: relative; }

        .login-field .field-icon {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.05rem;
            height: 1.05rem;
            color: #6b7a99;
            pointer-events: none;
        }

        .login-field .form-control {
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: 2.9rem;
            padding-left: 2.6rem;
            border-radius: .7rem;
            border: 1px solid transparent;
            background: #fff;
            color: var(--azul-oscuro);
            font-size: .92rem;
        }

        .login-field .form-control:focus {
            border-color: var(--celeste);
            box-shadow: 0 0 0 .2rem rgba(72, 185, 232, .3);
        }

        .login-field .form-control.is-invalid {
            border-color: #f87171;
            background-image: none;
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

        .login-error {
            display: block;
            margin-top: .3rem;
            font-size: .78rem;
            color: #fca5a5;
        }

        .login-alert {
            font-size: .85rem;
            padding: .65rem .85rem;
            border-radius: .7rem;
            margin-bottom: 1.1rem;
        }

        .login-alert--error { background: rgba(248, 113, 113, .15); border: 1px solid rgba(248, 113, 113, .5); color: #fecaca; }
        .login-alert--ok { background: rgba(52, 211, 153, .15); border: 1px solid rgba(52, 211, 153, .5); color: #a7f3d0; }

        .login-btn {
            width: 100%;
            height: 2.9rem;
            border: 0;
            border-radius: .7rem;
            font-weight: 700;
            font-size: .95rem;
            color: #fff;
            background: linear-gradient(90deg, var(--azul-principal), var(--azul-secundario) 55%, var(--celeste));
            box-shadow: 0 8px 22px rgba(22, 137, 212, .4);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }

        .login-btn:hover { transform: translateY(-1px); filter: brightness(1.08); }
        .login-btn:focus-visible { outline: 3px solid var(--celeste); outline-offset: 2px; }

        .login-register {
            text-align: center;
            margin-top: 1.1rem;
            font-size: .85rem;
        }

        .login-register a { color: #7dd3fc; text-decoration: none; font-weight: 600; }
        .login-register a:hover { text-decoration: underline; }

        .login-footer {
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, .1);
            text-align: center;
            font-size: .7rem;
            color: #8ea2c7;
        }
    </style>
</head>
<body class="login-page">

    <div class="login-year">Año académico {{ date('Y') }}</div>

    <main class="login-card">
        <img src="{{ asset('images/escudo-ie-tungasuca.png') }}" alt="Escudo de la I.E. Tungasuca" class="login-crest">
        <h1 class="login-title">I.E. Tungasuca</h1>
        <p class="login-subtitle">Sistema de incidencias para personal educativo y directivo</p>

        {{-- Mensaje de error general del login --}}
        @if ($errors->has('login_error'))
            <div class="login-alert login-alert--error" role="alert">
                {{ $errors->first('login_error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="login-alert login-alert--ok" role="status">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" novalidate>
            @csrf

            <!-- Campo DNI -->
            <div class="login-field">
                <label for="dni" class="login-label">Documento de Identidad (DNI)</label>
                <div class="login-input">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>
                    <input type="text"
                           name="dni"
                           id="dni"
                           class="form-control @error('dni') is-invalid @enderror"
                           value="{{ old('dni') }}"
                           placeholder="Número de DNI"
                           maxlength="8"
                           pattern="[0-9]{8}"
                           inputmode="numeric"
                           autocomplete="username"
                           required>
                </div>
                @error('dni')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Campo Contraseña -->
            <div class="login-field">
                <label for="password" class="login-label">Clave de acceso</label>
                <div class="login-input">
                    <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Ingrese su contraseña"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Mostrar contraseña">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @error('password')
                    <span class="login-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Botón Submit -->
            <button type="submit" class="login-btn">Acceder al Sistema</button>

            <div class="login-register">
                <a href="{{ route('register') }}">Crear cuenta</a>
            </div>
        </form>

        <div class="login-footer">Acceso exclusivo para personal autorizado</div>
    </main>

    <script>
        // Mostrar / ocultar contraseña
        (function () {
            var input = document.getElementById('password');
            var boton = document.getElementById('togglePassword');
            boton.addEventListener('click', function () {
                var visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            });
        })();
    </script>
</body>
</html>