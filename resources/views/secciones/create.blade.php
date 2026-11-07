@extends('layouts.app')

@section('title', 'Nueva sección')

@section('content')
    <div class="mb-4">
        <h1 class="h2 mb-1">Registrar sección</h1>
        <p class="text-secondary mb-0">Ingresa el grado y el nombre de la sección.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            @include('secciones._form', [
                'action' => route('secciones.store'),
                'submitLabel' => 'Guardar sección',
            ])
        </div>
    </div>
@endsection
