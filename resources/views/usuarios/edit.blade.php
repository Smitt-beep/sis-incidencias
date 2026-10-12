@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <div class="mb-4">
        <h1 class="h2 mb-1">Editar usuario</h1>
        <p class="text-secondary mb-0">Actualiza los datos de {{ $usuario->name }}.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" name="dni" id="dni" value="{{ old('dni', $usuario->dni) }}" class="form-control @error('dni') is-invalid @enderror" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required>
                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">Correo</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="255" autocomplete="email" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Nueva contraseña (opcional)</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection