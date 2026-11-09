<div class="border rounded p-3" id="teacher-assignment-fields">
    <h2 class="h5 mb-2">Asignación docente</h2>
    <p class="text-secondary small mb-3">
        Selecciona un curso y las secciones que le corresponden. Los grados se deducen de las secciones.
        Al editar, solo se reemplaza la asignación del curso seleccionado.
    </p>

    @if ($cursos->isEmpty())
        <div class="alert alert-info py-2" role="status">
            No hay cursos registrados. El usuario puede crearse sin asignación hasta que existan cursos disponibles.
        </div>
    @endif
    @if ($secciones->isEmpty())
        <div class="alert alert-info py-2" role="status">
            No hay secciones registradas para asignar.
        </div>
    @endif

    <div class="mb-3">
        <label for="curso_asignacion_id" class="form-label">Curso</label>
        <select name="curso_id" id="curso_asignacion_id" class="form-select @error('curso_id') is-invalid @enderror">
            <option value="">Sin asignación de curso</option>
            @foreach ($cursos as $curso)
                <option value="{{ $curso->id }}" @selected($cursoSeleccionado == $curso->id)>{{ $curso->nombre }}</option>
            @endforeach
        </select>
        @error('curso_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <fieldset>
        <legend class="form-label">Grados y secciones</legend>
        @error('grados') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
        @error('secciones') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

        @foreach ($secciones->groupBy('grado') as $grado => $seccionesDelGrado)
            @php
                $gradoSeleccionado = in_array($grado, $gradosSeleccionados, true);
            @endphp
            <div class="border rounded p-3 mb-2">
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="grados[]"
                        id="grado-{{ $loop->index }}"
                        value="{{ $grado }}"
                        class="form-check-input"
                        data-grade-toggle
                        @checked($gradoSeleccionado)
                    >
                    <label class="form-check-label fw-semibold" for="grado-{{ $loop->index }}">{{ $grado }}</label>
                </div>
                <div class="row g-2 mt-1" data-grade-sections="{{ $grado }}" @unless($gradoSeleccionado) hidden @endunless>
                    @foreach ($seccionesDelGrado as $seccion)
                        <div class="col-auto">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    name="secciones[]"
                                    id="seccion-asignacion-{{ $seccion->id }}"
                                    value="{{ $seccion->id }}"
                                    class="form-check-input"
                                    data-section-choice
                                    @checked(in_array($seccion->id, $seccionesSeleccionadas))
                                >
                                <label class="form-check-label" for="seccion-asignacion-{{ $seccion->id }}">
                                    {{ $seccion->seccion }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </fieldset>
</div>

<script>
    const gradeToggles = Array.from(document.querySelectorAll('[data-grade-toggle]'));

    function updateGradeSections(gradeToggle) {
        const sectionGroup = document.querySelector(`[data-grade-sections="${CSS.escape(gradeToggle.value)}"]`);
        if (!sectionGroup) {
            return;
        }

        sectionGroup.hidden = !gradeToggle.checked;
        sectionGroup.querySelectorAll('[data-section-choice]').forEach((sectionChoice) => {
            sectionChoice.disabled = !gradeToggle.checked;
            if (!gradeToggle.checked) {
                sectionChoice.checked = false;
            }
        });
    }

    gradeToggles.forEach((gradeToggle) => {
        gradeToggle.addEventListener('change', () => updateGradeSections(gradeToggle));
        updateGradeSections(gradeToggle);
    });
</script>
