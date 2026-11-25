@extends('layouts.app')

@section('title', 'Detalle de incidencia')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Incidencia #{{ $incidencia->id }}</h1>
            <p class="text-secondary mb-0">Detalle completo del registro.</p>
        </div>
        <div class="d-flex gap-2">
            @if ($puedeGestionarActa)
                <a href="{{ route('incidencias.acta.edit', $incidencia) }}" class="btn btn-outline-primary">Editar</a>
                @if ($tieneActa)
                    <a href="{{ route('incidencias.acta.pdf', $incidencia) }}" class="btn btn-primary">Descargar acta PDF</a>
                @endif
            @endif
            @if (session('usuario_rol') === 'Administrador'
                || (session('usuario_rol') === 'Profesor' && (int) $incidencia->usuario_id === (int) session('usuario_id')))
                <a href="{{ route('incidencias.edit', $incidencia) }}" class="btn btn-primary">Editar</a>
            @endif
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

                <dt class="col-sm-4 col-lg-3">Tipo de aula</dt>
                <dd class="col-sm-8 col-lg-9">
                    {{ match ($incidencia->tipo_aula) {
                        'teoria' => 'Teoría',
                        'taller' => 'Taller',
                        default => 'No especificado',
                    } }}
                </dd>

                <dt class="col-sm-4 col-lg-3">Fecha</dt>
                <dd class="col-sm-8 col-lg-9">{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</dd>

                <dt class="col-sm-4 col-lg-3">Hora</dt>
                <dd class="col-sm-8 col-lg-9">{{ substr((string) $incidencia->hora, 0, 5) }}</dd>

                <dt class="col-sm-4 col-lg-3">Gravedad</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->gravedad?->nombre ?? '—' }}</dd>

                <dt class="col-sm-4 col-lg-3">Tipos de incidencia</dt>
                <dd class="col-sm-8 col-lg-9">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($incidencia->tiposIncidencia as $tipo)
                            <span class="badge bg-primary rounded-pill text-wrap">{{ $tipo->nombre }}</span>
                        @empty
                            <span class="text-secondary">Sin tipos registrados</span>
                        @endforelse
                    </div>
                </dd>

                <dt class="col-sm-4 col-lg-3">Descripción</dt>
                <dd class="col-sm-8 col-lg-9 text-break">{{ $incidencia->descripcion }}</dd>

                <dt class="col-sm-4 col-lg-3">Estado</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->estado?->nombre ?? '—' }}</dd>

                <dt class="col-sm-4 col-lg-3">Registrado por</dt>
                <dd class="col-sm-8 col-lg-9">{{ $incidencia->usuario?->name ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <section class="card border-0 shadow-sm mt-4" aria-labelledby="evidencias-heading">
        <div class="card-body p-4">
            <h2 id="evidencias-heading" class="h5 mb-3">Evidencias fotográficas</h2>
            @if ($incidencia->fotografias->isEmpty())
                <p class="text-secondary mb-0">Sin evidencias fotográficas</p>
            @else
                <div class="row g-3">
                    @foreach ($incidencia->fotografias as $fotografia)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="rounded border border-primary-subtle bg-light p-2">
                                <a
                                    href="{{ route('incidencias.fotografias.show', [$incidencia, $fotografia]) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="d-block"
                                    aria-label="Ampliar evidencia fotográfica"
                                >
                                <img
                                    src="{{ route('incidencias.fotografias.show', [$incidencia, $fotografia]) }}"
                                    alt="Evidencia fotográfica de la incidencia #{{ $incidencia->id }}"
                                    class="img-fluid rounded w-100"
                                    style="height: 180px; object-fit: cover;"
                                    loading="lazy"
                                >
                                </a>
                                @if (session('usuario_rol') === 'Administrador'
                                    || (session('usuario_rol') === 'Profesor'
                                        && (int) $incidencia->usuario_id === (int) session('usuario_id')))
                                    <form
                                        action="{{ route('incidencias.fotografias.destroy', [$incidencia, $fotografia]) }}"
                                        method="POST"
                                        class="mt-2"
                                        onsubmit="return confirm('¿Está seguro de que desea retirar esta fotografía?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            Retirar fotografía
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection