{{--
    Tarjetas de resumen del dashboard (todos los roles).
    Usa la variable $tarjetas que arma AuthController::tarjetasResumen().
    Los estilos se afinan después; por ahora solo clases de Bootstrap.
--}}
@if (!empty($tarjetas))
    @php
        $propias = $tarjetas['alcance'] === 'propias';
        $variacion = $tarjetas['variacion_mes'];
        $puedeVerUrgencias = in_array(session('usuario_rol'), ['Administrador', 'Directora', 'Subdirector'], true);
    @endphp

    <section class="row g-3 mt-1" aria-label="Resumen de incidencias en números">
        @if ($propias && empty($seleccionProfesor))
            <div class="col-12">
                <p class="text-secondary small mb-0">Las tarjetas estarán en cero hasta completar grado, sección y curso.</p>
            </div>
        @endif
        {{-- 1. Total registradas --}}
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="text-uppercase text-secondary small fw-semibold">{{ $propias ? 'Mis registradas' : 'Total registradas' }}</div>
                    <div class="display-6 fw-bold">{{ $tarjetas['total'] }}</div>
                    <div class="small text-secondary">
                        @if ($variacion === null)
                            {{ $tarjetas['mes_actual'] }} este mes
                        @else
                            <span class="fw-semibold {{ $variacion >= 0 ? 'text-danger' : 'text-success' }}">{{ $variacion > 0 ? '+' : '' }}{{ $variacion }}%</span>
                            este mes ({{ $tarjetas['mes_actual'] }})
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Finalizadas --}}
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="text-uppercase text-secondary small fw-semibold">Finalizadas</div>
                    <div class="display-6 fw-bold">{{ $tarjetas['finalizadas'] }}</div>
                    <div class="small text-secondary">{{ $tarjetas['porcentaje_finalizadas'] }}% del total</div>
                </div>
            </div>
        </div>

        {{-- 3. Pendientes y en proceso --}}
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-uppercase text-secondary small fw-semibold">Pendientes y en proceso</div>
                    <div class="display-6 fw-bold">{{ $tarjetas['abiertas'] }}</div>
                    <div class="small text-secondary">{{ $tarjetas['pendientes'] }} pendientes · {{ $tarjetas['en_proceso'] }} en proceso</div>
                </div>
            </div>
        </div>

        {{-- 4. Graves activas (urgencias) --}}
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="text-uppercase text-secondary small fw-semibold">Graves activas</div>
                    <div class="display-6 fw-bold">{{ $tarjetas['graves_activas'] }}</div>
                    <div class="small text-secondary">
                        @if ($puedeVerUrgencias)
                            <a href="{{ route('incidencias.urgencias') }}" class="text-decoration-none">Ver urgencias</a>
                        @else
                            Pendientes o en proceso
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif