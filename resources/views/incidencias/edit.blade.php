@extends('layouts.app')

@section('title', 'Editar incidencia')

@section('content')
<div class="mb-4">
    <h1 class="h2 mb-1">Editar incidencia #{{ $incidencia->id }}</h1>
    <p class="text-secondary mb-0">Actualiza los datos de la incidencia.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('incidencias.update', $incidencia) }}" method="POST" enctype="multipart/form-data">
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
                        <option
                            value="{{ $seccion->id }}"
                            data-cursos="{{ implode(',', $sectionCourseIds[$seccion->id] ?? []) }}"
                            @selected(old('seccion_id', $incidencia->estudiante?->seccion_id) == $seccion->id)
                        >
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
                    <p class="mb-0">
                        <span class="form-label d-block">Fecha y hora de registro</span>
                        {{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}
                        {{ substr((string) $incidencia->hora, 0, 5) }}
                    </p>
                </div>

                <div class="col-md-6">
                    <label for="tipo_aula" class="form-label">Tipo de aula</label>
                    <select name="tipo_aula" id="tipo_aula" class="form-select @error('tipo_aula') is-invalid @enderror" required>
                        <option value="">Seleccione el tipo de aula</option>
                        <option value="teoria" @selected(old('tipo_aula', $incidencia->tipo_aula) === 'teoria')>Teoría</option>
                        <option value="taller" @selected(old('tipo_aula', $incidencia->tipo_aula) === 'taller')>Taller</option>
                    </select>
                    @error('tipo_aula') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label d-block mb-1">Tipos de incidencia</label>
                    <p class="text-secondary small mb-3">Selecciona uno o varios tipos relacionados con el incidente</p>
                    <div class="row g-2">
                        @foreach ($tiposIncidencia as $tipo)
                        <div class="col-12 col-md-6 col-lg-4">
                            <input
                                type="checkbox"
                                class="btn-check"
                                name="tipos[]"
                                id="tipo_incidencia_{{ $tipo->id }}"
                                value="{{ $tipo->id }}"
                                @checked(in_array($tipo->id, (array) old('tipos', $tiposIncidenciaSeleccionados)))
                            >
                            <label class="btn btn-outline-primary w-100 h-100 p-3 text-start text-wrap" for="tipo_incidencia_{{ $tipo->id }}">
                                {{ $tipo->nombre }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @foreach ($errors->getMessages() as $key => $messages)
                        @if ($key === 'tipos' || str_starts_with($key, 'tipos.'))
                            <div class="text-danger small mt-1">{{ $messages[0] }}</div>
                        @endif
                    @endforeach
                </div>

                <div class="col-12">
                    <div data-photograph-picker data-saved-count="{{ $fotografiasActuales }}" data-max-count="5" data-max-file-size="5242880">
                        <h2 class="h6 mb-2">Fotografías guardadas ({{ $fotografiasActuales }})</h2>
                        @if ($incidencia->fotografias->isNotEmpty())
                            <div class="row g-3 mb-3">
                                @foreach ($incidencia->fotografias as $fotografia)
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <a
                                            href="{{ route('incidencias.fotografias.show', [$incidencia, $fotografia]) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="d-block rounded border border-primary-subtle bg-light p-2 text-decoration-none"
                                        >
                                            <img
                                                src="{{ route('incidencias.fotografias.show', [$incidencia, $fotografia]) }}"
                                                alt="Fotografía guardada de la incidencia #{{ $incidencia->id }}"
                                                class="img-fluid rounded w-100"
                                                style="height: 150px; object-fit: cover;"
                                                loading="lazy"
                                            >
                                            <span class="small d-block text-center text-primary mt-2">Fotografía guardada</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-secondary small">Esta incidencia aún no tiene fotografías guardadas.</p>
                        @endif

                        <label for="fotografias" class="form-label">Agregar fotografías</label>
                        <p class="text-secondary small mb-2">
                            Puede agregar {{ $fotografiasDisponibles }} más; máximo 5 MB por archivo.
                            Formatos permitidos: JPG, JPEG, PNG y WebP.
                        </p>
                        <input
                            type="file"
                            name="fotografias[]"
                            id="fotografias"
                            data-photograph-input
                            class="form-control @error('fotografias') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            multiple
                            @disabled($fotografiasDisponibles === 0)
                        >
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary" data-photograph-take-photo @disabled($fotografiasDisponibles === 0)>Tomar foto</button>
                            <input
                                type="file"
                                data-photograph-camera-input
                                class="visually-hidden"
                                accept="image/*"
                                capture="environment"
                                tabindex="-1"
                                aria-hidden="true"
                                @disabled($fotografiasDisponibles === 0)
                            >
                        </div>
                        <p class="small fw-semibold text-primary mt-2 mb-2" data-photograph-count aria-live="polite"></p>
                        <div class="small text-danger mb-2" data-photograph-feedback role="alert" aria-live="assertive" hidden></div>
                        <h3 class="h6 mt-3 mb-2">Fotografías nuevas seleccionadas</h3>
                        <div class="row g-3" data-photograph-preview></div>
                        <div class="modal" data-photograph-camera-modal tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="camera-title-edit" aria-hidden="true" style="display: none;">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="modal-title fs-5" id="camera-title-edit">Tomar fotografía</h2>
                                        <button type="button" class="btn-close" data-photograph-camera-close aria-label="Cerrar cámara"></button>
                                    </div>
                                    <div class="modal-body">
                                        <video data-photograph-camera-video class="w-100 rounded bg-dark" style="max-height: 60vh; object-fit: contain;" autoplay muted playsinline aria-label="Vista en vivo de la cámara"></video>
                                        <p class="small mt-2 mb-0" data-photograph-camera-status role="status" aria-live="polite"></p>
                                        <p class="small text-danger mt-2 mb-0" data-photograph-camera-error role="alert" hidden></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-photograph-native-camera>Abrir cámara del dispositivo</button>
                                        <button type="button" class="btn btn-secondary" data-photograph-camera-close>Cancelar</button>
                                        <button type="button" class="btn btn-primary" data-photograph-capture disabled>Capturar foto</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @foreach ($errors->getMessages() as $key => $messages)
                        @if ($key === 'fotografias' || str_starts_with($key, 'fotografias.'))
                            <div class="text-danger small mt-1">{{ $messages[0] }}</div>
                        @endif
                    @endforeach
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
    const courseSelect = document.getElementById('curso_id');
    const sectionSelect = document.getElementById('seccion_id');
    const studentSelect = document.getElementById('estudiante_id');
    const studentOptions = Array.from(studentSelect.querySelectorAll('option[data-seccion]'));
    const sectionOptions = Array.from(sectionSelect.querySelectorAll('option[data-cursos]'));
    const isTeacher = @json($esProfesor);

    function filterSections() {
        const courseId = courseSelect.value;
        const selectedSection = sectionSelect.value;

        sectionSelect.disabled = isTeacher && courseId === '';
        sectionOptions.forEach((option) => {
            const assignedCourses = option.dataset.cursos.split(',');
            const isAvailable = !isTeacher || assignedCourses.includes(courseId);
            option.hidden = !isAvailable;
            option.disabled = !isAvailable;
        });

        if (!sectionOptions.some((option) => option.value === selectedSection && !option.disabled)) {
            sectionSelect.value = '';
        }
    }

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

    courseSelect.addEventListener('change', () => {
        filterSections();
        filterStudents();
    });
    sectionSelect.addEventListener('change', filterStudents);
    filterSections();
    filterStudents();
</script>
@endsection