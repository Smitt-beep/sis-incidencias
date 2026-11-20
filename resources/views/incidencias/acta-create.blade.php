@extends('layouts.app')

@section('title', isset($incidencia) ? 'Editar Acta de Incidencia' : 'Registrar Acta de Incidencia')

@section('content')
@php
    $incidenciaActa = $incidencia ?? null;
    $acta = $incidenciaActa?->acta;
    $esEdicionActa = $incidenciaActa !== null;
    $agresoresFormulario = old('agresores');
    if ($agresoresFormulario === null) {
        $agresoresFormulario = [];
        $agresoresGuardados = $acta?->agresores ?? collect();
        foreach (['estudiante', 'estudiante', 'adulto', 'adulto'] as $indice => $tipoAgresor) {
            $posicion = $indice < 2 ? $indice : $indice - 2;
            $agresorGuardado = $agresoresGuardados
                ->where('tipo', $tipoAgresor)
                ->values()
                ->get($posicion);
            $agresoresFormulario[$indice] = $agresorGuardado?->getAttributes() ?? ['tipo' => $tipoAgresor];
        }
    }
@endphp
<div class="mb-4">
    <h1 class="h2 mb-1">{{ $esEdicionActa ? 'Editar Acta de Incidencia #'.$incidenciaActa->id : 'Registrar Acta de Incidencia' }}</h1>
    <p class="text-secondary mb-0">Los campos opcionales pueden dejarse en blanco.</p>
</div>

<form action="{{ $esEdicionActa ? route('incidencias.acta.update', $incidenciaActa) : route('incidencias.acta.store') }}" method="POST" class="card border-0 shadow-sm">
    @csrf
    @if ($esEdicionActa)
        @method('PUT')
    @endif
    <div class="card-body p-4">
        @if ($errors->has('base'))
            <div class="alert alert-danger">{{ $errors->first('base') }}</div>
        @endif

        <h2 class="h5 mb-3">Estudiante agredido</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="seccion_id" class="form-label">Sección</label>
                <select id="seccion_id" class="form-select">
                    <option value="">Todas las secciones</option>
                    @foreach ($secciones as $seccion)
                        <option value="{{ $seccion->id }}" @selected(old('seccion_id', $incidenciaActa?->estudiante?->seccion_id) == $seccion->id)>{{ $seccion->grado }} - {{ $seccion->seccion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label for="buscar_estudiante" class="form-label">Buscar estudiante</label>
                <input id="buscar_estudiante" type="search" class="form-control" placeholder="Nombre o DNI">
            </div>
            <div class="col-12">
                <label for="estudiante_id" class="form-label">Estudiante</label>
                <select name="estudiante_id" id="estudiante_id" class="form-select @error('estudiante_id') is-invalid @enderror" required>
                    <option value="">Seleccione un estudiante</option>
                    @foreach ($estudiantes as $estudiante)
                        <option value="{{ $estudiante->id }}" data-seccion="{{ $estudiante->seccion_id }}" @selected(old('estudiante_id', $incidenciaActa?->estudiante_id) == $estudiante->id)>
                            {{ $estudiante->nombre }} — {{ $estudiante->dni }} ({{ $estudiante->seccion?->grado }} - {{ $estudiante->seccion?->seccion }})
                        </option>
                    @endforeach
                </select>
                @error('estudiante_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <h2 class="h5 mb-3">Encabezado</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><label class="form-label">IE</label><input name="ie" value="{{ old('ie', $acta?->ie) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">DRE</label><input name="dre" value="{{ old('dre', $acta?->dre) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">UGEL</label><input name="ugel" value="{{ old('ugel', $acta?->ugel) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Fecha actual (automática)</label><input type="date" value="{{ old('fecha', $incidenciaActa?->fecha ?? now()->toDateString()) }}" class="form-control" disabled></div>
            <div class="col-md-4"><label class="form-label">Fecha SíseVe</label><input type="date" name="sisve_fecha" value="{{ old('sisve_fecha', $acta?->sisve_fecha?->format('Y-m-d')) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">N° reporte SíseVe</label><input name="sisve_nro_reporte" value="{{ old('sisve_nro_reporte', $acta?->sisve_nro_reporte) }}" class="form-control"></div>
            <div class="col-12"><label class="form-label">Tipo de reporte</label>
                @foreach(['A' => 'Adulto a escolar', 'B' => 'Entre escolares', 'C' => 'Escolar a adulto', 'D' => 'Entre adultos'] as $value => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_reporte" id="tipo_{{ $value }}" value="{{ $value }}" @checked(old('tipo_reporte', $acta?->tipo_reporte) === $value)>
                        <label class="form-check-label" for="tipo_{{ $value }}">{{ $value }}. {{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <h2 class="h5 mb-3">Persona informante</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label">Nombres y apellidos</label><input name="informante_nombres" value="{{ old('informante_nombres', $acta?->informante_nombres) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Parentesco</label><select name="informante_parentesco" class="form-select"><option value="">Seleccione</option>@foreach(['Padre','Madre','Hermana/o','Estudiante','Docente','Otros'] as $tipo)<option value="{{ $tipo }}" @selected(old('informante_parentesco', $acta?->informante_parentesco) === $tipo)>{{ $tipo }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Especifique</label><input name="informante_parentesco_otro" value="{{ old('informante_parentesco_otro', $acta?->informante_parentesco_otro) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">DNI</label><input name="informante_dni" value="{{ old('informante_dni', $acta?->informante_dni) }}" class="form-control"></div>
            <div class="col-md-5"><label class="form-label">Domicilio</label><input name="informante_domicilio" value="{{ old('informante_domicilio', $acta?->informante_domicilio) }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Teléfono</label><input name="informante_telefono" value="{{ old('informante_telefono', $acta?->informante_telefono) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Correo electrónico</label><input type="email" name="informante_correo" value="{{ old('informante_correo', $acta?->informante_correo) }}" class="form-control"></div>
        </div>

        <h2 class="h5 mb-3">Supuesta persona agredida</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label for="agredido_nombres_iniciales" class="form-label">Nombre (usar iniciales si aplica)</label><input id="agredido_nombres_iniciales" name="agredido_nombres_iniciales" value="{{ old('agredido_nombres_iniciales', $acta?->agredido_nombres_iniciales) }}" class="form-control @error('agredido_nombres_iniciales') is-invalid @enderror"><div class="form-text">Si selecciona violencia sexual, registre únicamente iniciales.</div>@error('agredido_nombres_iniciales')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-2"><label class="form-label">Edad</label><input type="number" min="1" max="99" name="agredido_edad" value="{{ old('agredido_edad', $acta?->agredido_edad) }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Sexo</label><select name="agredido_sexo" class="form-select"><option value="">Seleccione</option><option value="M" @selected(old('agredido_sexo', $acta?->agredido_sexo) === 'M')>M</option><option value="F" @selected(old('agredido_sexo', $acta?->agredido_sexo) === 'F')>F</option></select></div>
            <div class="col-md-2"><label class="form-label">Turno</label><select name="agredido_turno" class="form-select"><option value="">Seleccione</option>@foreach(['M','T','N'] as $turno)<option value="{{ $turno }}" @selected(old('agredido_turno', $acta?->agredido_turno) === $turno)>{{ $turno }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Teléfono</label><input name="agredido_telefono" value="{{ old('agredido_telefono', $acta?->agredido_telefono) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Discapacidad</label><select name="agredido_discapacidad" class="form-select"><option value="">No especificado</option><option value="1" @selected(old('agredido_discapacidad', $acta?->agredido_discapacidad) === '1' || old('agredido_discapacidad', $acta?->agredido_discapacidad) === true)>Sí</option><option value="0" @selected(old('agredido_discapacidad', $acta?->agredido_discapacidad) === '0' || old('agredido_discapacidad', $acta?->agredido_discapacidad) === false)>No</option></select></div>
            <div class="col-md-6"><label class="form-label">Especifique discapacidad</label><input name="agredido_discapacidad_detalle" value="{{ old('agredido_discapacidad_detalle', $acta?->agredido_discapacidad_detalle) }}" class="form-control"></div>
        </div>

        <h2 class="h5 mb-3">Presuntos agresores</h2>
        @for ($i = 0; $i < 4; $i++)
            @php
                $agresorFormulario = $agresoresFormulario[$i] ?? [];
            @endphp
            <div class="border rounded p-3 mb-3">
                <h3 class="h6">{{ $i < 2 ? 'Estudiante '.($i + 1) : 'Adulto '.($i - 1) }}</h3>
                <div class="row g-2">
                    <input type="hidden" name="agresores[{{ $i }}][tipo]" value="{{ $agresorFormulario['tipo'] ?? ($i < 2 ? 'estudiante' : 'adulto') }}">
                    <div class="col-md-5"><label class="form-label">Nombres</label><input name="agresores[{{ $i }}][nombres]" value="{{ $agresorFormulario['nombres'] ?? '' }}" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Edad</label><input type="number" min="1" max="99" name="agresores[{{ $i }}][edad]" value="{{ $agresorFormulario['edad'] ?? '' }}" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Sexo</label><select name="agresores[{{ $i }}][sexo]" class="form-select"><option value="">—</option><option value="M" @selected(($agresorFormulario['sexo'] ?? null) === 'M')>M</option><option value="F" @selected(($agresorFormulario['sexo'] ?? null) === 'F')>F</option></select></div>
                    @if ($i < 2)
                        <div class="col-md-3"><label class="form-label">Relación</label><select name="agresores[{{ $i }}][relacion_grado]" class="form-select"><option value="">—</option><option value="mismo_grado" @selected(($agresorFormulario['relacion_grado'] ?? null) === 'mismo_grado')>Mismo grado</option><option value="mayor" @selected(($agresorFormulario['relacion_grado'] ?? null) === 'mayor')>Mayor</option><option value="menor" @selected(($agresorFormulario['relacion_grado'] ?? null) === 'menor')>Menor</option></select></div>
                        <div class="col-md-4"><label class="form-label">Grado/Sección</label><input name="agresores[{{ $i }}][grado_seccion]" value="{{ $agresorFormulario['grado_seccion'] ?? '' }}" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Turno</label><select name="agresores[{{ $i }}][turno]" class="form-select"><option value="">—</option>@foreach(['M','T','N'] as $turno)<option value="{{ $turno }}" @selected(($agresorFormulario['turno'] ?? null) === $turno)>{{ $turno }}</option>@endforeach</select></div>
                    @else
                        <div class="col-md-3"><label class="form-label">DNI</label><input name="agresores[{{ $i }}][dni]" value="{{ $agresorFormulario['dni'] ?? '' }}" class="form-control"></div>
                        <div class="col-md-5"><label class="form-label">Cargo</label><select name="agresores[{{ $i }}][cargo]" class="form-select"><option value="">—</option><option value="director" @selected(($agresorFormulario['cargo'] ?? null) === 'director')>Director</option><option value="docente" @selected(($agresorFormulario['cargo'] ?? null) === 'docente')>Docente</option><option value="administrativo" @selected(($agresorFormulario['cargo'] ?? null) === 'administrativo')>Administrativo</option><option value="personal_apoyo" @selected(($agresorFormulario['cargo'] ?? null) === 'personal_apoyo')>Personal de apoyo</option><option value="otro" @selected(($agresorFormulario['cargo'] ?? null) === 'otro')>Otro</option></select></div>
                        <div class="col-md-4"><label class="form-label">Especifique cargo</label><input name="agresores[{{ $i }}][cargo_otro]" value="{{ $agresorFormulario['cargo_otro'] ?? '' }}" class="form-control"></div>
                    @endif
                </div>
            </div>
        @endfor

        <h2 class="h5 mb-3">Tipo de violencia</h2>
        <div class="d-flex flex-wrap gap-3 mb-2">
            @foreach(['fisico' => 'Físico', 'sexual' => 'Sexual', 'verbal' => 'Verbal', 'bullying' => 'Bullying', 'ciberbullying' => 'Ciberbullying', 'otro' => 'Otro'] as $value => $label)
                <div class="form-check"><input class="form-check-input" type="checkbox" name="tipos_violencia[]" id="violencia_{{ $value }}" value="{{ $value }}" @checked(in_array($value, old('tipos_violencia', $acta?->tipos_violencia ?? [])))><label class="form-check-label" for="violencia_{{ $value }}">{{ $label }}</label></div>
            @endforeach
        </div>
        <label class="form-label">Especifique otro tipo</label>
        <input name="violencia_otro" value="{{ old('violencia_otro', $acta?->violencia_otro) }}" class="form-control">
    </div>
    <div class="card-footer d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $esEdicionActa ? 'Actualizar acta' : 'Guardar acta' }}</button>
        <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const seccion = document.getElementById('seccion_id');
    const buscar = document.getElementById('buscar_estudiante');
    const select = document.getElementById('estudiante_id');
    const iniciales = document.getElementById('agredido_nombres_iniciales');
    const violenciaSexual = document.getElementById('violencia_sexual');

    let timeout;
    async function cargar() {
        const params = new URLSearchParams({ seccion_id: seccion.value, buscar: buscar.value });
        const respuesta = await fetch(`{{ route('incidencias.acta.estudiantes.buscar') }}?${params}`);
        if (!respuesta.ok) return;
        const datos = await respuesta.json();
        const actual = select.value;
        select.innerHTML = '<option value="">Seleccione un estudiante</option>';
        datos.estudiantes.forEach(estudiante => {
            const option = document.createElement('option');
            option.value = estudiante.id;
            option.textContent = `${estudiante.nombre} — ${estudiante.dni} (${estudiante.grado || ''} - ${estudiante.seccion || ''})`;
            select.appendChild(option);
        });
        if ([...select.options].some(option => option.value === actual)) select.value = actual;
    }
    seccion.addEventListener('change', cargar);
    violenciaSexual.addEventListener('change', function () {
        iniciales.required = violenciaSexual.checked;
    });
    iniciales.required = violenciaSexual.checked;
    buscar.addEventListener('input', function () {
        clearTimeout(timeout);
        timeout = setTimeout(cargar, 250);
    });
});
</script>
@endsection
