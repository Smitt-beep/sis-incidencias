@extends('layouts.app')

@section('title', 'Incidencias')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h1 class="h2 mb-1">{{ ($esUrgencias ?? false) ? 'Urgencias' : 'Incidencias' }}</h1>
                @if ($esUrgencias ?? false)
                    <span class="urgencias-active-count" aria-label="{{ $urgenciasActivas }} {{ $urgenciasActivas === 1 ? 'urgencia activa' : 'urgencias activas' }}">
                        {{ number_format($urgenciasActivas) }} {{ $urgenciasActivas === 1 ? 'urgencia activa' : 'urgencias activas' }}
                    </span>
                @endif
            </div>
            <p class="text-secondary mb-0">
                {{ ($esUrgencias ?? false) ? 'Incidencias graves pendientes o en proceso.' : 'Registro y seguimiento de incidencias.' }}
            </p>
        </div>
        @if (in_array(session('usuario_rol'), ['Administrador', 'Profesor'], true))
            <a href="{{ route('incidencias.create') }}" class="btn btn-primary">+ Registrar incidencia</a>
        @endif
        @if (in_array(session('usuario_rol'), ['Directora', 'Subdirector'], true))
            <a href="{{ route('incidencias.acta.create') }}" class="btn btn-primary">+ Registrar acta</a>
        @endif
    </div>

    @unless ($esUrgencias ?? false)
        <form action="{{ route('incidencias.index') }}" method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-sm-8 col-md-6">
                <label for="buscar-incidencia" class="form-label">Buscar por alumno</label>
                <input
                    type="search"
                    name="buscar"
                    id="buscar-incidencia"
                    value="{{ request('buscar') }}"
                    class="form-control"
                    placeholder="Nombre del alumno"
                >
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Buscar</button>
                @if (request()->filled('buscar'))
                    <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                @endif
            </div>
        </form>
    @endunless

    @php
        $esUrgenciasDirectivas = ($esUrgencias ?? false)
            && in_array(session('usuario_rol'), ['Directora', 'Subdirector'], true);
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="{{ ($esUrgencias ?? false) ? 'urgencias-table-wrap' : 'table-responsive' }}">
            <table class="table table-hover align-middle mb-0 {{ ($esUrgencias ?? false) ? 'urgencias-table' : '' }}{{ $esUrgenciasDirectivas ? ' urgencias-table--directivo' : '' }}">
                @if ($esUrgencias ?? false)
                    <colgroup>
                        <col class="urgencias-col-id">
                        <col class="urgencias-col-student">
                        <col class="urgencias-col-section">
                        <col class="urgencias-col-course">
                        <col class="urgencias-col-datetime">
                        <col class="urgencias-col-actions">
                    </colgroup>
                @endif
                <thead class="table-light">
                    <tr>
                        @if ($esUrgencias ?? false)
                            <th scope="col">ID</th>
                            <th scope="col">Estudiante</th>
                            <th scope="col">Grado / Sección</th>
                            <th scope="col">Curso</th>
                            <th scope="col">Fecha y hora</th>
                            <th scope="col" class="text-end">Acciones</th>
                        @else
                            <th scope="col">ID</th>
                            <th scope="col">Estudiante</th>
                            <th scope="col">Curso</th>
                            <th scope="col">Sección</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Hora</th>
                            <th scope="col">Gravedad</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incidencias as $incidencia)
                        @php
                            $puedeVerActa = in_array(session('usuario_rol'), ['Directora', 'Subdirector'], true);
                        @endphp
                        @if ($esUrgencias ?? false)
                            @php
                                $contenidoReservado = $incidencia->es_reservada && ! $puedeVerActa;
                                $seccionEstudiante = $incidencia->estudiante?->seccion;
                                $nombreEstudiante = $incidencia->estudiante?->nombre ?? '—';
                            @endphp
                            <tr>
                                <td>{{ $incidencia->id }}</td>
                                <td class="urgencias-truncate" title="{{ $nombreEstudiante }}">{{ $nombreEstudiante }}</td>
                                <td class="urgencias-truncate" title="{{ $seccionEstudiante ? $seccionEstudiante->grado.' - '.$seccionEstudiante->seccion : '—' }}">
                                    @if ($seccionEstudiante)
                                        {{ $seccionEstudiante->grado }} - {{ $seccionEstudiante->seccion }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="urgencias-truncate" title="{{ $contenidoReservado ? '—' : ($incidencia->curso?->nombre ?? '—') }}">
                                    {{ $contenidoReservado ? '—' : ($incidencia->curso?->nombre ?? '—') }}
                                </td>
                                <td class="urgencias-datetime">
                                    <span class="urgencias-date">{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</span>
                                    <span class="urgencias-time">{{ $contenidoReservado || ! $incidencia->hora ? '—' : substr((string) $incidencia->hora, 0, 5) }}</span>
                                </td>
                                <td class="urgencias-actions-cell">
                                    @unless ($contenidoReservado)
                                        <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 urgencias-actions">
                                            <a href="{{ route('incidencias.show', $incidencia) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                            @if ($incidencia->es_reservada && $puedeVerActa)
                                                <a href="{{ route('incidencias.acta.edit', $incidencia) }}" class="btn btn-sm btn-outline-primary">Editar acta</a>
                                            @endif
                                            @if (session('usuario_rol') === 'Administrador'
                                                || (session('usuario_rol') === 'Profesor' && (int) $incidencia->usuario_id === (int) session('usuario_id')))
                                                <a href="{{ route('incidencias.edit', $incidencia) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                            @endif
                                            @if (in_array(session('usuario_rol'), ['Administrador', 'Directora'], true))
                                                <form action="{{ route('incidencias.destroy', $incidencia) }}" method="POST" onsubmit="return confirm('¿Eliminar esta incidencia?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endunless
                                </td>
                            </tr>
                        @elseif ($incidencia->es_reservada && ! $puedeVerActa)
                            {{-- Acta reservada: solo se muestra alumno, fecha y la etiqueta "Reservado" --}}
                            <tr>
                                <td>{{ $incidencia->id }}</td>
                                <td>{{ $incidencia->estudiante?->nombre ?? '—' }}</td>
                                <td>—</td>
                                <td>
                                    @if ($incidencia->estudiante?->seccion)
                                        {{ $incidencia->estudiante->seccion->grado }} - {{ $incidencia->estudiante->seccion->seccion }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</td>
                                <td>—</td>
                                <td><span class="badge text-bg-secondary">Reservado</span></td>
                                <td><span class="badge text-bg-secondary">Reservado</span></td>
                                <td></td>
                            </tr>
                            @continue
                        @endif
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
                                    @if ($incidencia->es_reservada && in_array(session('usuario_rol'), ['Directora', 'Subdirector'], true))
                                        <a href="{{ route('incidencias.acta.edit', $incidencia) }}" class="btn btn-sm btn-outline-primary">Editar acta</a>
                                    @endif
                                    @if (session('usuario_rol') === 'Administrador'
                                        || (session('usuario_rol') === 'Profesor' && (int) $incidencia->usuario_id === (int) session('usuario_id')))
                                        <a href="{{ route('incidencias.edit', $incidencia) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @endif
                                    @if (in_array(session('usuario_rol'), ['Administrador', 'Directora'], true))
                                        <form action="{{ route('incidencias.destroy', $incidencia) }}" method="POST" onsubmit="return confirm('¿Eliminar esta incidencia?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($esUrgencias ?? false) ? 6 : 9 }}" class="text-center text-secondary py-4">
                                {{ ($esUrgencias ?? false) ? 'No hay urgencias activas.' : 'No hay incidencias registradas.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($esUrgencias ?? false)
            <div class="p-3 border-top">
                {{ $incidencias->onEachSide(1)->links('pagination.urgencias') }}
            </div>
        @elseif ($incidencias->hasPages())
            <div class="p-3 border-top">
                {{ $incidencias->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection