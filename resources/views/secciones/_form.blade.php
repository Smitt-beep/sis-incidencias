<form action="{{ $action }}" method="POST">
    @csrf
    @if (isset($method))
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label for="grado" class="form-label">Grado</label>
            <input
                type="text"
                name="grado"
                id="grado"
                value="{{ old('grado', $seccion->grado ?? '') }}"
                class="form-control @error('grado') is-invalid @enderror"
                maxlength="255"
                required
            >
            @error('grado') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="seccion" class="form-label">Sección</label>
            <input
                type="text"
                name="seccion"
                id="seccion"
                value="{{ old('seccion', $seccion->seccion ?? '') }}"
                class="form-control @error('seccion') is-invalid @enderror"
                maxlength="255"
                required
            >
            @error('seccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        <a href="{{ route('secciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
