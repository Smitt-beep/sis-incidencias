@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <div class="mb-4">
        <h1 class="h2 mb-1">Nuevo usuario</h1>
        <p class="text-secondary mb-0">Registra una cuenta en el sistema.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            @if (!$rolesConfigured)
                <div class="alert alert-danger" role="alert">
                    No están configurados todos los cargos permitidos. No se pueden crear usuarios hasta corregirlo.
                </div>
            @endif

            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="dni" class="form-label">DNI</label>
                        <input type="text" name="dni" id="dni" value="{{ old('dni') }}" class="form-control @error('dni') is-invalid @enderror" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required>
                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">Correo</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" maxlength="255" autocomplete="email" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="rol_id" class="form-label">Cargo</label>
                        <select name="rol_id" id="rol_id" class="form-select @error('rol_id') is-invalid @enderror" required>
                            <option value="">Seleccione un cargo</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(old('rol_id') == $rol->id)>{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                        @error('rol_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" @disabled(!$rolesConfigured)>Guardar usuario</button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection