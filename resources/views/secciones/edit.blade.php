@extends('layouts.app')

@section('title', 'Editar sección')

@section('content')
    <div class="mb-4">
        <h1 class="h2 mb-1">Editar sección</h1>
        <p class="text-secondary mb-0">Actualiza los datos de la sección.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            @include('secciones._form', [
                'action' => route('secciones.update', $seccion),
                'method' => 'PUT',
                'seccion' => $seccion,
                'submitLabel' => 'Guardar cambios',
            ])
        </div>
    </div>
@endsection
