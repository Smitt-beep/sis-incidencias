@extends('layouts.app')

@section('title', 'Alumnos')

@section('content')
    <div class="mb-4">
        <h1 class="h2 mb-1">Alumnos</h1>
        <p class="text-secondary mb-0">
            {{ $rol === 'Profesor' ? 'Alumnos de tus secciones asignadas.' : 'Consulta alumnos y genera sus reportes de incidencias.' }}
        </p>
    </div>

    @if ($rol === 'Profesor')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Añadir alumno</h2>

            @if ($secciones->isEmpty())
                <p class="text-secondary mb-0">No tienes secciones asignadas. Contacta al administrador para actualizar tus asignaciones.</p>
            @else
                <form action="{{ route('estudiantes.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                value="{{ old('nombre') }}"
                                class="form-control @error('nombre') is-invalid @enderror"
                                maxlength="255"
                                autocomplete="name"
                                required
                            >
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input
                                type="text"
                                name="dni"
                                id="dni"
                                value="{{ old('dni') }}"
                                class="form-control @error('dni') is-invalid @enderror"
                                inputmode="numeric"
                                pattern="[0-9]{8}"
                                maxlength="8"
                                required
                            >
                            @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="seccion_id" class="form-label">Sección asignada</label>
                            <select
                                name="seccion_id"
                                id="seccion_id"
                                class="form-select @error('seccion_id') is-invalid @enderror"
                                required
                            >
                                <option value="">Seleccione una sección</option>
                                @foreach ($secciones as $seccion)
                                    <option value="{{ $seccion->id }}" @selected(old('seccion_id') == $seccion->id)>
                                        {{ $seccion->grado }} - {{ $seccion->seccion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seccion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Añadir alumno</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">{{ $rol === 'Profesor' ? 'Alumnos de mis secciones' : 'Alumnos' }}</h2>
        <span class="badge text-bg-secondary">{{ $estudiantes->total() }}</span>
    </div>

    <form action="{{ route('estudiantes.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-sm-8 col-md-6">
            <label for="buscar-estudiante" class="form-label">Buscar alumno</label>
            <input
                type="search"
                name="buscar"
                id="buscar-estudiante"
                value="{{ $buscar }}"
                class="form-control"
                placeholder="Nombre o DNI"
            >
        </div>
        <div class="col-sm-6 col-md-4">
            <label for="filtro-seccion" class="form-label">Sección</label>
            <select name="seccion_id" id="filtro-seccion" class="form-select">
                <option value="">Todas las secciones</option>
                @foreach ($secciones as $seccion)
                    <option value="{{ $seccion->id }}" @selected((string) $seccionId === (string) $seccion->id)>
                        {{ $seccion->grado }} - {{ $seccion->seccion }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Buscar</button>
            @if ($buscar !== '' || $seccionId)
                <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">DNI</th>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Grado</th>
                        <th scope="col">Sección</th>
                        <th scope="col">Reportes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estudiantes as $estudiante)
                        <tr>
                            <td>{{ $estudiante->dni }}</td>
                            <td>{{ $estudiante->nombre }}</td>
                            <td>{{ $estudiante->seccion?->grado }}</td>
                            <td>{{ $estudiante->seccion?->seccion }}</td>
                            <td>
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-primary dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        Reportes
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('estudiantes.reporte.seleccionar', $estudiante) }}">
                                                Generar uno o varios (seleccionar)
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('estudiantes.reporte.pdf', $estudiante) }}">
                                                Todas (PDF directo)
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                {{ $rol === 'Profesor' ? 'No hay alumnos registrados en tus secciones.' : 'No hay alumnos que coincidan con la búsqueda.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($estudiantes->hasPages())
            <div class="p-3 border-top">
                {{ $estudiantes->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
