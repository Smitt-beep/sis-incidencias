@extends('layouts.app')

@section('title', 'Registrar incidencia')

@section('content')
<div class="mb-4">
    <h1 class="h2 mb-1">Registrar incidencia</h1>
    <p class="text-secondary mb-0">Completa los datos para registrar una incidencia.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('incidencias.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                @if ($contextoProfesor)
              {{-- El profesor ya eligió su aula en Inicio: se muestra fija y el alumno se busca con la lupa --}}
              <input type="hidden" name="curso_id" value="{{ $contextoProfesor['curso_id'] }}">
              <input type="hidden" name="seccion_id" value="{{ $contextoProfesor['seccion_id'] }}">

              <div class="col-12">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 rounded border bg-light px-3 py-2">
                      <div>
                          <span class="text-secondary small d-block">Aula elegida en Inicio</span>
                          <strong>Grado {{ $contextoProfesor['grado'] }} · Sección {{ $contextoProfesor['seccion'] }} · Curso {{ $contextoProfesor['curso'] }}</strong>
                      </div>
                      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Cambiar aula</a>
                  </div>
              </div>

              <div class="col-12">
                  <label for="buscar_estudiante" class="form-label">Estudiante</label>
                  <input type="hidden" name="estudiante_id" id="estudiante_id" value="{{ old('estudiante_id') }}">
                  <div class="position-relative">
                      <div class="input-group">
                          <span class="input-group-text" aria-hidden="true">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                          </span>
                          <input type="text" id="buscar_estudiante" class="form-control @error('estudiante_id') is-invalid @enderror"
                                 placeholder="Busca por nombre o documento ({{ count($contextoProfesor['estudiantes']) }} alumnos en esta aula)"
                                 autocomplete="off" aria-controls="lista_estudiantes">
                          <button type="button" class="btn btn-outline-secondary" id="cambiar_estudiante" hidden>Cambiar</button>
                          @error('estudiante_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </div>
                      <div class="list-group position-absolute w-100 shadow-sm" id="lista_estudiantes" style="z-index: 1050; max-height: 260px; overflow-y: auto;" hidden></div>
                  </div>
                  <div class="form-text text-danger" id="estudiante_error" hidden>Busca y selecciona un estudiante de la lista.</div>
              </div>
              @else
                <div class="col-md-4">
                    <label for="curso_id" class="form-label">Curso</label>
                    <select name="curso_id" id="curso_id" class="form-select @error('curso_id') is-invalid @enderror" required>
                        <option value="">Seleccione un curso</option>
                        @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}" @selected(old('curso_id')==$curso->id)>{{ $curso->nombre }}</option>
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
                            @selected(old('seccion_id') == $seccion->id)
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
                        <option value="{{ $estudiante->id }}" data-seccion="{{ $estudiante->seccion_id }}" @selected(old('estudiante_id')==$estudiante->id)>
                            {{ $estudiante->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('estudiante_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @endif


                <div class="col-12">
                    <p class="text-secondary mb-0">La fecha y hora se registrarán automáticamente al guardar la incidencia</p>
                </div>

                <div class="col-md-6">
                    <label for="tipo_aula" class="form-label">Tipo de aula</label>
                    <select name="tipo_aula" id="tipo_aula" class="form-select @error('tipo_aula') is-invalid @enderror" required>
                        <option value="">Seleccione el tipo de aula</option>
                        <option value="teoria" @selected(old('tipo_aula') === 'teoria')>Teoría</option>
                        <option value="taller" @selected(old('tipo_aula') === 'taller')>Taller</option>
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
                                @checked(in_array($tipo->id, (array) old('tipos', [])))
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
                    <div data-photograph-picker data-saved-count="0" data-max-count="5" data-max-file-size="5242880">
                        <label for="fotografias" class="form-label">Fotografías de evidencia</label>
                        <input
                            type="file"
                            name="fotografias[]"
                            id="fotografias"
                            data-photograph-input
                            class="form-control @error('fotografias') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            multiple
                        >
                        <div class="form-text">Puede adjuntar hasta 5 fotografías, con un máximo de 5 MB por archivo. Formatos permitidos: JPG, JPEG, PNG y WebP.</div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary" data-photograph-take-photo>Tomar foto</button>
                            <input
                                type="file"
                                data-photograph-camera-input
                                class="visually-hidden"
                                accept="image/*"
                                capture="environment"
                                tabindex="-1"
                                aria-hidden="true"
                            >
                        </div>
                        <p class="small fw-semibold text-primary mt-2 mb-2" data-photograph-count aria-live="polite">0 de 5 fotografías seleccionadas</p>
                        <div class="small text-danger mb-2" data-photograph-feedback role="alert" aria-live="assertive" hidden></div>
                        <div class="row g-3" data-photograph-preview></div>
                        <div class="modal" data-photograph-camera-modal tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="camera-title-create" aria-hidden="true" style="display: none;">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="modal-title fs-5" id="camera-title-create">Tomar fotografía</h2>
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
                            @checked(old('gravedad_id')==$gravedad->id)
                        required
                        >

                        <label
                            class="btn gravedad-btn
                             @if($gravedad->nombre == 'Leve')
                              btn-outline-primary
                             @elseif($gravedad->nombre == 'Intermedia')
                              btn-outline-warning
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
                    <textarea name="descripcion" id="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror" required>{{ old('descripcion') }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="estado_id" class="form-label">Estado</label>

                    <select name="estado_id"
                        id="estado_id"
                        class="form-select @error('estado_id') is-invalid @enderror"
                        required>

                        <option value="" disabled selected hidden>Seleccione un estado</option>

                        @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}"
                            @selected(old('estado_id')==$estado->id)>

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
                <button type="submit" class="btn btn-primary">Guardar incidencia</button>
                <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@if ($contextoProfesor)
<script type="application/json" id="estudiantes-aula">@json($contextoProfesor['estudiantes'])</script>
<script>
    (function () {
        const alumnos = JSON.parse(document.getElementById('estudiantes-aula').textContent);
        const campoId = document.getElementById('estudiante_id');
        const buscador = document.getElementById('buscar_estudiante');
        const lista = document.getElementById('lista_estudiantes');
        const botonCambiar = document.getElementById('cambiar_estudiante');
        const mensajeError = document.getElementById('estudiante_error');
        const formulario = buscador.closest('form');

        // Quita tildes y mayúsculas para que "pena" encuentre "PEÑA"
        const limpiar = (texto) => String(texto || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

        function elegir(alumno) {
            campoId.value = alumno.id;
            buscador.value = alumno.nombre;
            buscador.readOnly = true;
            botonCambiar.hidden = false;
            lista.hidden = true;
            mensajeError.hidden = true;
        }

        function mostrar(texto) {
            const palabras = limpiar(texto).split(/\s+/).filter(Boolean);
            lista.innerHTML = '';
            if (palabras.length === 0) { lista.hidden = true; return; }

            const encontrados = alumnos.filter((a) => {
                const base = limpiar(a.nombre) + ' ' + limpiar(a.dni);
                return palabras.every((p) => base.includes(p));
            }).slice(0, 8);

            if (encontrados.length === 0) {
                const vacio = document.createElement('div');
                vacio.className = 'list-group-item text-secondary small';
                vacio.textContent = 'Ningún alumno de esta aula coincide.';
                lista.appendChild(vacio);
            }
            encontrados.forEach((alumno) => {
                const opcion = document.createElement('button');
                opcion.type = 'button';
                opcion.className = 'list-group-item list-group-item-action';
                opcion.textContent = alumno.nombre + (alumno.dni ? ' — ' + alumno.dni : '');
                opcion.addEventListener('click', () => elegir(alumno));
                lista.appendChild(opcion);
            });
            lista.hidden = false;
        }

        buscador.addEventListener('input', () => {
            campoId.value = ''; // si cambia el texto, ya no hay alumno elegido
            mostrar(buscador.value);
        });
        buscador.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); // Enter no envía el formulario: elige el primer resultado
                const primero = lista.querySelector('button');
                if (primero) primero.click();
            } else if (e.key === 'Escape') {
                lista.hidden = true;
            }
        });
        botonCambiar.addEventListener('click', () => {
            campoId.value = '';
            buscador.value = '';
            buscador.readOnly = false;
            botonCambiar.hidden = true;
            buscador.focus();
        });
        document.addEventListener('click', (e) => {
            if (!lista.contains(e.target) && e.target !== buscador) lista.hidden = true;
        });
        formulario.addEventListener('submit', (e) => {
            if (campoId.value === '') {
                e.preventDefault();
                mensajeError.hidden = false;
                buscador.focus();
            }
        });

        // Si el formulario volvió con errores, se recupera el alumno que ya se había elegido
        const previo = alumnos.find((a) => String(a.id) === String(campoId.value));
        if (previo) elegir(previo);
    })();
</script>
@else

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
@endif
@endsection