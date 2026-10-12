@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Gestión de Usuarios</h1>
            <p class="text-secondary mb-0">Administra las cuentas registradas.</p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">Nuevo usuario</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">DNI</th>
                        <th scope="col">Correo</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Estado de aprobación</th>
                        <th scope="col">Estado de cuenta</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $usuario)
                        <tr>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->dni }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->rol?->nombre_rol ?? '—' }}</td>
                            <td>
                                @php
                                    $estadoAprobacion = $usuario->estado_aprobacion ?? 'Pendiente';
                                @endphp
                                @if ($estadoAprobacion === 'Aprobado')
                                    <span class="badge bg-success">Aprobado</span>
                                @elseif ($estadoAprobacion === 'Rechazado')
                                    <span class="badge bg-danger">Rechazado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if ((bool) $usuario->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Desactivado</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end flex-wrap gap-2">
                                    @if ($usuario->estado_aprobacion === 'Pendiente' && session('usuario_id') != $usuario->id)
                                        <form action="{{ route('usuarios.aprobar', $usuario) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Aprobar</button>
                                        </form>
                                        <form action="{{ route('usuarios.rechazar', $usuario) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Rechazar</button>
                                        </form>
                                    @elseif ($usuario->estado_aprobacion === 'Aprobado' && session('usuario_id') != $usuario->id)
                                        @if ((bool) $usuario->activo)
                                            <form action="{{ route('usuarios.desactivar', $usuario) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Desactivar</button>
                                            </form>
                                        @else
                                            <form action="{{ route('usuarios.activar', $usuario) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">Activar</button>
                                            </form>
                                        @endif
                                    @elseif ($usuario->estado_aprobacion === 'Rechazado')
                                        <span class="text-danger small fw-semibold">Rechazado</span>
                                    @endif

                                    @if (session('usuario_id') != $usuario->id)
                                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-3 border-top">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection