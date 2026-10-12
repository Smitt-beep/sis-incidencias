<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Incidencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4 text-primary">LOGIN</h3>

                    {{-- Mensaje de error general del login --}}
                    @if ($errors->has('login_error'))
                        <div class="alert alert-danger">
                            {{ $errors->first('login_error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        <!-- Campo DNI -->
                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text"
                                   name="dni"
                                   id="dni"
                                   class="form-control @error('dni') is-invalid @enderror"
                                   value="{{ old('dni') }}"
                                   placeholder="Ingrese su DNI"
                                   maxlength="8"
                                   pattern="[0-9]{8}"
                                   inputmode="numeric"
                                   required>
                            @error('dni')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campo Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password"
                                name="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="******"
                                autocomplete="current-password"
                                required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botón Submit -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('register') }}" class="text-decoration-none">Crear cuenta</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>