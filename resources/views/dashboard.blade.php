@extends('layouts.app')

@section('title', 'Panel de Control')

@section('content')
    @php
        $rol = session('usuario_rol');
        $nombre = session('usuario_nombre');

        $mensaje = match ($rol) {
            'Administrador' => 'Bienvenido Administrador ' . $nombre,
            'Profesor' => 'Bienvenido Profesor ' . $nombre,
            'Directora' => 'Bienvenida Directora ' . $nombre,
            'Subdirector' => 'Bienvenido Subdirector ' . $nombre,
            default => 'Bienvenido ' . $nombre,
        };
    @endphp

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2>{{ $mensaje }}</h2>
                        <p class="text-muted">Has iniciado sesión correctamente con el correo: <strong>{{ session('usuario_correo') }}</strong></p>
                        <hr>
                        <p>Este es el panel principal protegido por sesión.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection