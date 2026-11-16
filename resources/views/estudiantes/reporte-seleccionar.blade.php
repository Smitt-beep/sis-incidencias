@extends('layouts.app')

@section('title', 'Seleccionar incidencias')

@section('content')
    <div class="mb-4">
        <h1 class="h2 mb-1">Seleccionar incidencias para reporte de {{ $estudiante->nombre }}</h1>
        <p class="text-secondary mb-0">Elige las incidencias que deseas incluir en el reporte.</p>
    </div>

    @if ($incidencias->isEmpty())
        <div class="alert alert-info" role="status">Este alumno no tiene incidencias registradas.</div>
        <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    @else
        <form action="{{ route('estudiantes.reporte.pdf.post', $estudiante) }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><span class="visually-hidden">Seleccionar</span></th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Curso</th>
                                <th scope="col">Gravedad</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($incidencias as $incidencia)
                                <tr>
                                    <td>
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="incidencia_ids[]"
                                            id="incidencia-{{ $incidencia->id }}"
                                            value="{{ $incidencia->id }}"
                                        >
                                        <label class="visually-hidden" for="incidencia-{{ $incidencia->id }}">
                                            Seleccionar incidencia del {{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}
                                        </label>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</td>
                                    <td>{{ $incidencia->curso?->nombre ?? '—' }}</td>
                                    <td>{{ $incidencia->gravedad?->nombre ?? '—' }}</td>
                                    <td>{{ $incidencia->estado?->nombre ?? '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($incidencia->descripcion, 60) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Generar PDF</button>
                <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    @endif
@endsection
