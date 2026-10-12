@extends('layouts.app')

@section('title', 'Incidencias')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Incidencias</h1>
            <p class="text-secondary mb-0">Registro y seguimiento de incidencias.</p>
        </div>
        <a href="{{ route('incidencias.create') }}" class="btn btn-primary">+ Registrar incidencia</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Estudiante</th>
                        <th scope="col">Curso</th>
                        <th scope="col">Sección</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Hora</th>
                        <th scope="col">Gravedad</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidencias as $incidencia)
                        <tr>
                            <td>{{ $incidencia->id }}</td>
                            <td>{{ $incidencia->estudiante?->nombre ?? '—' }}</td>
                            <td>{{ $incidencia->curso?->nombre ?? '—' }}</td>
                            <td>
                                @if ($incidencia->estudiante?->seccion)
                                    {{ $incidencia->estudiante->seccion->grado }} - {{ $incidencia->estudiante->seccion->seccion }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</td>
                            <td>{{ substr((string) $incidencia->hora, 0, 5) }}</td>
                            <td>{{ $incidencia->gravedad?->nombre ?? '—' }}</td>
                            @php
                                $statusName = $incidencia->estado?->nombre ?? '';
                                $normalizedStatus = mb_strtolower(trim($statusName));
                                $statusClass = match ($normalizedStatus) {
                                    'pendiente' => 'status-badge--pending',
                                    'en proceso' => 'status-badge--warning',
                                    'finalizado' => 'status-badge--success',
                                    default => 'status-badge--info',
                                };
                            @endphp
                            <td><span class="status-badge {{ $statusClass }}">{{ $statusName !== '' ? $statusName : '—' }}</span></td>
                            <td>
                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="{{ route('incidencias.show', $incidencia) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                    <a href="{{ route('incidencias.edit', $incidencia) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('incidencias.destroy', $incidencia) }}" method="POST" onsubmit="return confirm('¿Eliminar esta incidencia?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-4">No hay incidencias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidencias->hasPages())
            <div class="p-3 border-top">
                {{ $incidencias->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection