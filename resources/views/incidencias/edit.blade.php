@extends('layouts.app')

@section('title', 'Editar incidencia')

@section('content')
<div class="mb-4">
    <h1 class="h2 mb-1">Editar incidencia #{{ $incidencia->id }}</h1>
    <p class="text-secondary mb-0">Actualiza los datos de la incidencia.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('incidencias.update', $incidencia) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="curso_id" class="form-label">Curso</label>
                    <select name="curso_id" id="curso_id" class="form-select @error('curso_id') is-invalid @enderror" required>
                        <option value="">Seleccione un curso</option>
                        @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}" @selected(old('curso_id', $incidencia->curso_id) == $curso->id)>{{ $curso->nombre }}</option>
                        @endforeach
                    </select>
                    @error('curso_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="seccion_id" class="form-label">Sección</label>
                    <select name="seccion_id" id="seccion_id" class="form-select @error('seccion_id') is-invalid @enderror" required>
                        <option value="">Seleccione una sección</option>
                        @foreach ($secciones as $seccion)
                        <option value="{{ $seccion->id }}" @selected(old('seccion_id', $incidencia->estudiante?->seccion_id) == $seccion->id)>
                            {{ $seccion->grado }} - {{ $seccion->seccion }}
                        </option>
                        @endforeach
                    </select>
                    @error('seccion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="estudiante_id" class="form-label">Estudiante</label>
                    <select name="estudiante_id" id="estudiante_id" class="form-select @error('estudiante_id') is-invalid @enderror" required disabled>
                        <option value="">Seleccione primero una sección</option>
                        @foreach ($estudiantes as $estudiante)
                        <option value="{{ $estudiante->id }}" data-seccion="{{ $estudiante->seccion_id }}" @selected(old('estudiante_id', $incidencia->estudiante_id) == $estudiante->id)>
                            {{ $estudiante->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('estudiante_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" name="fecha" id="fecha" value="{{ old('fecha', \Carbon\Carbon::parse($incidencia->fecha)->format('Y-m-d')) }}" class="form-control @error('fecha') is-invalid @enderror" required>
                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="hora" class="form-label">Hora</label>
                    <input type="time" name="hora" id="hora" value="{{ old('hora', substr((string) $incidencia->hora, 0, 5)) }}" class="form-control @error('hora') is-invalid @enderror" required>
                    @error('hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">Gravedad</label>

                    <div class="d-flex gap-2 flex-wrap">

                        @foreach ($gravedades as $gravedad)

                        <input
                            type="radio"
                            class="btn-check"
                            name="gravedad_id"
                            id="gravedad_{{ $gravedad->id }}"
                            value="{{ $gravedad->id }}"
                            @checked(old('gravedad_id', $incidencia->gravedad_id) == $gravedad->id)
                        required
                        >

                        <label
                            class="btn gravedad-btn
                    @if($gravedad->nombre == 'Leve')
                        btn-outline-primary
                    @elseif($gravedad->nombre == 'Intermedia')
                        btn-outline-secondary
                    @elseif($gravedad->nombre == 'Grave')
                        btn-outline-danger
                    @endif
                "
                            for="gravedad_{{ $gravedad->id }}">
                            {{ $gravedad->nombre }}
                        </label>

                        @endforeach

                    </div>

                    @error('gravedad_id')
                    <div class="text-danger small mt-1">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion', $incidencia->descripcion) }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="estado_id" class="form-label">Estado</label>

                    <select name="estado_id"
                        id="estado_id"
                        class="form-select @error('estado_id') is-invalid @enderror"
                        required>

                        @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}"
                            @selected(old('estado_id', $incidencia->estado_id) == $estado->id)>
                            {{ $estado->nombre }}
                        </option>
                        @endforeach

                    </select>

                    @error('estado_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
    const sectionSelect = document.getElementById('seccion_id');
    const studentSelect = document.getElementById('estudiante_id');
    const studentOptions = Array.from(studentSelect.querySelectorAll('option[data-seccion]'));

    function filterStudents() {
        const sectionId = sectionSelect.value;
        const selectedStudent = studentSelect.value;

        studentSelect.disabled = sectionId === '';
        studentOptions.forEach((option) => {
            const belongsToSection = option.dataset.seccion === sectionId;
            option.hidden = sectionId !== '' && !belongsToSection;
            option.disabled = sectionId === '' || !belongsToSection;
        });

        if (!studentOptions.some((option) => option.value === selectedStudent && !option.disabled)) {
            studentSelect.value = '';
        }
    }

    sectionSelect.addEventListener('change', filterStudents);
    filterStudents();
</script>
@endsection