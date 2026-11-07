@extends('layouts.app')

@section('title', 'Gestión de Secciones')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Gestión de Secciones</h1>
            <p class="text-secondary mb-0">Consulta y administra las secciones registradas.</p>
        </div>
        <a href="{{ route('secciones.create') }}" class="btn btn-primary">Nueva sección</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Grado</th>
                        <th scope="col">Sección</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($secciones as $seccion)
                        <tr>
                            <td>{{ $seccion->grado }}</td>
                            <td>{{ $seccion->seccion }}</td>
                            <td class="text-end">
                                <a href="{{ route('secciones.edit', $seccion) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-secondary py-4">No hay secciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($secciones->hasPages())
            <div class="p-3 border-top">
                {{ $secciones->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
