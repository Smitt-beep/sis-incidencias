@extends('layouts.app')

@section('title', 'Detalle de incidencia')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Incidencia #{{ $incidencia->id }}</h1>
            <p class="text-secondary mb-0">Detalle completo del registro.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('incidencias.edit', $incidencia) }}" class="btn btn-primary">Editar</a>
            <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <dl class="row mb-0">
                <dt class="col-sm-4 col-lg-3">Estudiante</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->estudiante?->nombre ?? '—' }}</dd>

                <dt class="col-sm-4 col-lg-3">Sección</dt>
                <dd class="col-sm-8 col-lg-9">
                    @if ($incidencia->estudiante?->seccion)
                        {{ $incidencia->estudiante->seccion->grado }} - {{ $incidencia->estudiante->seccion->seccion }}
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-4 col-lg-3">Curso</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->curso?->nombre ?? '—' }}</dd>

                <dt class="col-sm-4 col-lg-3">Fecha</dt>
                <dd class="col-sm-8 col-lg-9">{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</dd>

                <dt class="col-sm-4 col-lg-3">Hora</dt>
                <dd class="col-sm-8 col-lg-9">{{ substr((string) $incidencia->hora, 0, 5) }}</dd>

                <dt class="col-sm-4 col-lg-3">Gravedad</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->gravedad?->nombre ?? '—' }}</dd>

                <dt class="col-sm-4 col-lg-3">Descripción</dt>
                <dd class="col-sm-8 col-lg-9 text-break">{{ $incidencia->descripcion }}</dd>

                <dt class="col-sm-4 col-lg-3">Estado</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->estado?->nombre ?? '—' }}</dd>

                <dt class="col-sm-4 col-lg-3">Registrado por</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->usuario?->name ?? '—' }}</dd>
            </dl>
        </div>
    </div>
@endsection