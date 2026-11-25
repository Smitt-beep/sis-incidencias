@extends('layouts.app')

@section('title', 'Editar alumno')

@section('content')
<div class="mb-4">
    <h1 class="h2 mb-1">Editar alumno</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('estudiantes.update', $estudiante) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="dni" class="form-label">Documento de identidad</label>
                    <input type="text" name="dni" id="dni" value="{{ old('dni', $estudiante->dni) }}"
                           class="form-control @error('dni') is-invalid @enderror"
                           minlength="6" maxlength="20" required>
                    @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label for="nombre" class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $estudiante->nombre) }}"
                           class="form-control @error('nombre') is-invalid @enderror" maxlength="255" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="seccion_id" class="form-label">Sección</label>
                    <select name="seccion_id" id="seccion_id" class="form-select @error('seccion_id') is-invalid @enderror" required>
                        <option value="">Seleccione una sección</option>
                        @foreach ($secciones as $seccion)
                            <option value="{{ $seccion->id }}" @selected(old('seccion_id', $estudiante->seccion_id) == $seccion->id)>
                                {{ $seccion->grado }} - {{ $seccion->seccion }}
                            </option>
                        @endforeach
                    </select>
                    @error('seccion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
