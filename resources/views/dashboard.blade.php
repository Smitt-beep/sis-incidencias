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
        @if ($rol === 'Profesor')
            <section class="card shadow-sm border-0 mb-4" aria-labelledby="contexto-profesor-title">
                <div class="card-body">
                    <h2 class="h5 mb-1" id="contexto-profesor-title">Selecciona tu contexto</h2>
                    <p class="text-secondary small mb-3">Elige grado, sección y curso para visualizar solamente tus incidencias.</p>
                    <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="dashboard-grado" class="form-label">Grado</label>
                            <select name="grado" id="dashboard-grado" class="form-select" required>
                                <option value="">Seleccione un grado</option>
                                @foreach ($grados as $grado)
                                    <option value="{{ $grado }}" @selected(request('grado') === $grado)>{{ $grado }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="dashboard-seccion" class="form-label">Sección</label>
                            <select name="seccion_id" id="dashboard-seccion" class="form-select" required>
                                <option value="">Seleccione una sección</option>
                                @foreach ($secciones as $seccion)
                                    <option value="{{ $seccion->id }}" data-grado="{{ $seccion->grado }}" @selected((string) request('seccion_id') === (string) $seccion->id)>
                                        {{ $seccion->grado }} - {{ $seccion->seccion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="dashboard-curso" class="form-label">Curso</label>
                            <select name="curso_id" id="dashboard-curso" class="form-select" required>
                                <option value="">Seleccione un curso</option>
                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso->id }}" @selected((string) request('curso_id') === (string) $curso->id)>
                                        {{ $curso->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Aplicar</button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Limpiar selección</a>
                        </div>
                    </form>
                </div>
            </section>
            @if (!($graficos['seleccionado'] ?? false))
                <div class="alert alert-info" role="status">
                    Selecciona grado, sección y curso para visualizar tus estadísticas
                </div>
            @endif
        @endif
        @if ($rol === 'Profesor')
            <script>
                (() => {
                    const gradoSelect = document.getElementById('dashboard-grado');
                    const seccionSelect = document.getElementById('dashboard-seccion');
                    if (!gradoSelect || !seccionSelect) {
                        return;
                    }

                    const aplicarFiltroSecciones = () => {
                        const grado = gradoSelect.value;
                        let seccionVisible = false;

                        Array.from(seccionSelect.options).forEach((option) => {
                            if (!option.value) {
                                option.hidden = false;
                                return;
                            }

                            const coincide = !grado || option.dataset.grado === grado;
                            option.hidden = !coincide;
                            option.disabled = !coincide;
                            if (coincide && option.selected) {
                                seccionVisible = true;
                            }
                        });

                        if (!seccionVisible && seccionSelect.selectedOptions.length) {
                            seccionSelect.value = '';
                        }
                    };

                    gradoSelect.addEventListener('change', aplicarFiltroSecciones);
                    aplicarFiltroSecciones();
                })();
            </script>
        @endif
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
        @include('partials.tarjetas-resumen')  
        @if ($graficos && ($graficos['tipo'] ?? null) === 'profesor' && ($graficos['seleccionado'] ?? false))
            <div class="row mt-4">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h3 class="h5 mb-0">Mis incidencias registradas</h3>

                    <div class="btn-group" role="group" aria-label="Periodo del dashboard">
                        <input type="radio" class="btn-check" name="periodo-dashboard" id="periodo-semana" value="semana" checked>
                        <label class="btn btn-outline-primary btn-sm" for="periodo-semana">Semana</label>

                        <input type="radio" class="btn-check" name="periodo-dashboard" id="periodo-mes" value="mes">
                        <label class="btn btn-outline-primary btn-sm" for="periodo-mes">Mes</label>
                    </div>
                </div>

                <div class="col-lg-7 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h4 class="h6 text-secondary mb-3">Incidencias registradas</h4>
                            <canvas id="grafico-incidencias" height="220" role="img" aria-label="Incidencias registradas por periodo"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h4 class="h6 text-secondary mb-3">Incidencias por gravedad</h4>
                            <canvas id="grafico-gravedad" height="220" role="img" aria-label="Incidencias por nivel de gravedad"></canvas>
                            <p class="text-muted small mb-0 mt-2" id="grafico-gravedad-total"></p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($graficos && ($graficos['tipo'] ?? null) === 'profesor' && ($graficos['seleccionado'] ?? false))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            const datosDashboard = @json($graficos);

            // Misma paleta que ya se usa en el formulario de incidencias:
            // Leve = azul (primary), Intermedia = amarillo (warning), Grave = rojo (danger).
            const coloresGravedad = {
                Leve: '#0756A3',
                Intermedia: '#F5C542',
                Grave: '#dc3545',
            };

            const ctxIncidencias = document.getElementById('grafico-incidencias');
            const ctxGravedad = document.getElementById('grafico-gravedad');
            const totalGravedadTexto = document.getElementById('grafico-gravedad-total');

            const graficoIncidencias = new Chart(ctxIncidencias, {
                type: 'bar',
                data: {
                    labels: datosDashboard.semana.labels,
                    datasets: [{
                        label: 'Incidencias',
                        data: datosDashboard.semana.valores,
                        backgroundColor: '#1689D4',
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });

            const graficoGravedad = new Chart(ctxGravedad, {
                type: 'pie',
                data: {
                    labels: ['Leve', 'Intermedia', 'Grave'],
                    datasets: [{
                        data: [
                            datosDashboard.semana.gravedad.cantidades.Leve,
                            datosDashboard.semana.gravedad.cantidades.Intermedia,
                            datosDashboard.semana.gravedad.cantidades.Grave,
                        ],
                        backgroundColor: [coloresGravedad.Leve, coloresGravedad.Intermedia, coloresGravedad.Grave],
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (contexto) => {
                                    const periodo = document.querySelector('input[name="periodo-dashboard"]:checked').value;
                                    const porcentajes = datosDashboard[periodo].gravedad.porcentajes;
                                    const porcentaje = porcentajes[contexto.label] ?? 0;
                                    return `${contexto.label}: ${contexto.parsed} (${porcentaje}%)`;
                                },
                            },
                        },
                    },
                },
            });

            function actualizarTotalGravedad(periodo) {
                const total = datosDashboard[periodo].gravedad.total;
                totalGravedadTexto.textContent = total > 0
                    ? `Total del periodo: ${total} incidencia(s).`
                    : 'No hay incidencias registradas en este periodo.';
            }

            function actualizarGraficos(periodo) {
                const datosPeriodo = datosDashboard[periodo];

                graficoIncidencias.data.labels = datosPeriodo.labels;
                graficoIncidencias.data.datasets[0].data = datosPeriodo.valores;
                graficoIncidencias.update();

                graficoGravedad.data.datasets[0].data = [
                    datosPeriodo.gravedad.cantidades.Leve,
                    datosPeriodo.gravedad.cantidades.Intermedia,
                    datosPeriodo.gravedad.cantidades.Grave,
                ];
                graficoGravedad.update();

                actualizarTotalGravedad(periodo);
            }

            document.querySelectorAll('input[name="periodo-dashboard"]').forEach((input) => {
                input.addEventListener('change', (evento) => actualizarGraficos(evento.target.value));
            });

            actualizarTotalGravedad('semana');
        </script>
    @elseif (isset($graficos['tipo']) && $graficos['tipo'] === 'admin')
        <section class="container mb-4" aria-labelledby="dashboard-admin-title">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h2 class="h4 mb-1" id="dashboard-admin-title">Resumen de incidencias</h2>
                    <p class="text-secondary mb-0">Distribución por grado, curso y gravedad.</p>
                </div>
                <div class="btn-group" role="group" aria-label="Periodo de los gráficos administrativos">
                    <input type="radio" class="btn-check" name="periodo-dashboard-admin" id="admin-periodo-semana" value="semana" checked>
                    <label class="btn btn-outline-primary btn-sm" for="admin-periodo-semana">Semana</label>
                    <input type="radio" class="btn-check" name="periodo-dashboard-admin" id="admin-periodo-mes" value="mes">
                    <label class="btn btn-outline-primary btn-sm" for="admin-periodo-mes">Mes</label>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 d-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 text-secondary mb-3">Incidencias Generales</h3>
                            <canvas id="grafico-admin-general" height="120" role="img" aria-label="Total de incidencias por semana o mes"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 text-secondary mb-3">Incidencias por grado</h3>
                            <div class="row g-2">
                                @foreach (['Primer grado', 'Segundo grado', 'Tercer grado', 'Cuarto grado', 'Quinto grado'] as $indice => $grado)
                                    <div class="{{ $indice < 3 ? 'col-4' : 'col-6' }}">
                                        <h4 class="small text-center mb-1">{{ $grado }}</h4>
                                        <canvas
                                            id="grafico-gravedad-grado-{{ $indice + 1 }}"
                                            height="150"
                                            role="img"
                                            aria-label="Incidencias por gravedad de {{ $grado }}"
                                        ></canvas>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 text-secondary mb-3">Incidencias por gravedad</h3>
                            <canvas id="grafico-admin-gravedad" height="220" role="img" aria-label="Incidencias por nivel de gravedad"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h3 class="h6 text-secondary mb-0">Incidencias por curso</h3>
                                <div class="d-flex align-items-center gap-2">
                                    <label for="grado-admin-curso" class="small text-secondary mb-0">Grado</label>
                                    <select id="grado-admin-curso" class="form-select form-select-sm" aria-label="Seleccionar grado">
                                        <option value="0">Primer grado</option>
                                        <option value="1">Segundo grado</option>
                                        <option value="2">Tercer grado</option>
                                        <option value="3">Cuarto grado</option>
                                        <option value="4">Quinto grado</option>
                                    </select>
                                </div>
                            </div>
                            <div id="grafico-admin-curso-contenedor" style="height: 280px;">
                                <canvas id="grafico-admin-curso" role="img" aria-label="Cantidad de incidencias por curso y grado seleccionado"></canvas>
                            </div>
                            <p id="grafico-admin-curso-vacio" class="text-muted small mb-0 d-none" role="status"></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            const datosDashboardAdmin = @json($graficos);
            const coloresGravedadAdmin = {
                Leve: '#0756A3',
                Intermedia: '#F5C542',
                Grave: '#dc3545',
            };
            let periodoDashboardAdmin = 'semana';
            let gradoDashboardAdmin = 0;

            const pluginValorCurso = {
                id: 'valorCurso',
                afterDatasetsDraw(chart) {
                    if (chart.canvas.id !== 'grafico-admin-curso') {
                        return;
                    }
                    const dataset = chart.data.datasets[0];
                    const meta = chart.getDatasetMeta(0);
                    const contexto = chart.ctx;
                    contexto.save();
                    contexto.fillStyle = '#172B4D';
                    contexto.font = '700 12px sans-serif';
                    contexto.textAlign = 'left';
                    contexto.textBaseline = 'middle';
                    meta.data.forEach((barra, indice) => {
                        const valor = dataset.data[indice] ?? 0;
                        contexto.fillText(String(valor), barra.x + 8, barra.y);
                    });
                    contexto.restore();
                },
            };

            const graficoAdminCurso = new Chart(document.getElementById('grafico-admin-curso'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Incidencias',
                        data: [],
                        backgroundColor: '#1689D4',
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (contexto) => `Incidencias: ${contexto.parsed.x}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            title: { display: true, text: 'Incidencias' },
                        },
                        y: {
                            grid: { display: false },
                            title: { display: true, text: 'Cursos' },
                        },
                    },
                },
                plugins: [pluginValorCurso],
            });
            const contenedorGraficoAdminCurso = document.getElementById('grafico-admin-curso-contenedor');
            const mensajeGraficoAdminCursoVacio = document.getElementById('grafico-admin-curso-vacio');
            const nombresGradosDashboardAdmin = [
                'Primer grado', 'Segundo grado', 'Tercer grado', 'Cuarto grado', 'Quinto grado',
            ];

            const graficoAdminGeneral = new Chart(document.getElementById('grafico-admin-general'), {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Incidencias',
                        data: [],
                        borderColor: '#7C3AED',
                        backgroundColor: 'rgba(124, 58, 237, 0.18)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { display: false },
                            title: { display: true, text: 'Periodo' },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            title: { display: true, text: 'Cantidad de incidencias' },
                        },
                    },
                },
            });

            const graficoAdminGravedad = new Chart(document.getElementById('grafico-admin-gravedad'), {
                type: 'pie',
                data: {
                    labels: ['Leve', 'Intermedia', 'Grave'],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#0756A3', '#F5C542', '#dc3545'],
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                title: (elementos) => elementos[0]?.label ?? '',
                                label: (elemento) => {
                                    const gravedad = datosDashboardAdmin[periodoDashboardAdmin].gravedad;
                                    return [
                                        `Cantidad: ${elemento.parsed}`,
                                        `Porcentaje: ${gravedad.porcentajes[elemento.label]}%`,
                                    ];
                                },
                            },
                        },
                    },
                },
            });

            function actualizarGraficoAdminCurso(datosPeriodo) {
                const cursos = datosPeriodo.porCursoPorGrado[gradoDashboardAdmin]
                    .map((curso) => ({
                        nombre: curso.curso,
                        cantidad: curso.valores.reduce((total, valor) => total + valor, 0),
                    }))
                    .sort((a, b) => b.cantidad - a.cantidad || a.nombre.localeCompare(b.nombre, 'es'));

                if (cursos.length === 0) {
                    contenedorGraficoAdminCurso.classList.add('d-none');
                    mensajeGraficoAdminCursoVacio.textContent =
                        `No hay incidencias para ${nombresGradosDashboardAdmin[gradoDashboardAdmin]} en este periodo.`;
                    mensajeGraficoAdminCursoVacio.classList.remove('d-none');
                    graficoAdminCurso.data.labels = [];
                    graficoAdminCurso.data.datasets[0].data = [];
                    graficoAdminCurso.update();
                    return;
                }

                mensajeGraficoAdminCursoVacio.classList.add('d-none');
                contenedorGraficoAdminCurso.classList.remove('d-none');
                contenedorGraficoAdminCurso.style.height = `${Math.max(240, cursos.length * 38 + 40)}px`;
                graficoAdminCurso.data.labels = cursos.map((curso) => curso.nombre);
                graficoAdminCurso.data.datasets[0].data = cursos.map((curso) => curso.cantidad);
                graficoAdminCurso.update();
            }

            function actualizarGraficosAdmin(periodo) {
                periodoDashboardAdmin = periodo;
                const datosPeriodo = datosDashboardAdmin[periodo];

                graficoAdminGeneral.data.labels = datosPeriodo.general.labels;
                graficoAdminGeneral.data.datasets[0].data = datosPeriodo.general.valores;
                graficoAdminGeneral.update();

                actualizarGraficoAdminCurso(datosPeriodo);

                graficoAdminGravedad.data.datasets[0].data = [
                    datosPeriodo.gravedad.cantidades.Leve,
                    datosPeriodo.gravedad.cantidades.Intermedia,
                    datosPeriodo.gravedad.cantidades.Grave,
                ];
                graficoAdminGravedad.data.datasets[0].backgroundColor = [
                    coloresGravedadAdmin.Leve,
                    coloresGravedadAdmin.Intermedia,
                    coloresGravedadAdmin.Grave,
                ];
                graficoAdminGravedad.update();
            }

            document.getElementById('grado-admin-curso').addEventListener('change', (evento) => {
                gradoDashboardAdmin = Number(evento.target.value);
                actualizarGraficoAdminCurso(datosDashboardAdmin[periodoDashboardAdmin]);
            });

            document.querySelectorAll('input[name="periodo-dashboard-admin"]').forEach((input) => {
                input.addEventListener('change', (evento) => actualizarGraficosAdmin(evento.target.value));
            });

            actualizarGraficosAdmin('semana');
        </script>
    @endif

    @if (isset($graficos['tipo']) && $graficos['tipo'] === 'admin')
        <script>
            const datosDashboardGrados = @json($graficos);
            const etiquetasGravedadDashboard = ['Leve', 'Intermedia', 'Grave'];
            const coloresGravedadDashboard = ['#0756A3', '#F5C542', '#dc3545'];
            let periodoDashboardGrados = 'semana';

            function pluginTotalGrado(indiceGrado) {
                return {
                    id: `totalGrado${indiceGrado}`,
                    afterDraw(chart) {
                        const area = chart.chartArea;
                        if (!area) {
                            return;
                        }

                        const total = datosDashboardGrados[periodoDashboardGrados]
                            .gravedadPorGrado[indiceGrado].total;
                        const contexto = chart.ctx;
                        const centroX = (area.left + area.right) / 2;
                        const centroY = (area.top + area.bottom) / 2;
                        const radioInterno = chart.getDatasetMeta(0).data[0]?.innerRadius
                            ?? Math.min(area.width, area.height) * 0.25;
                        const tamanoNumero = Math.max(12, Math.min(radioInterno * 0.42, 18));
                        const tamanoEtiqueta = Math.max(9, Math.min(tamanoNumero * 0.55, 11));

                        contexto.save();
                        contexto.textAlign = 'center';
                        contexto.textBaseline = 'middle';
                        contexto.fillStyle = '#172B4D';
                        contexto.font = `700 ${tamanoNumero}px sans-serif`;
                        contexto.fillText(
                            String(total),
                            centroX,
                            centroY - (tamanoNumero + tamanoEtiqueta) / 2
                        );
                        contexto.font = `500 ${tamanoEtiqueta}px sans-serif`;
                        contexto.fillText(
                            'Total',
                            centroX,
                            centroY + (tamanoNumero + tamanoEtiqueta) / 2
                        );
                        contexto.restore();
                    },
                };
            }

            const graficosGravedadPorGrado = [0, 1, 2, 3, 4].map((indiceGrado) => {
                const resumen = datosDashboardGrados.semana.gravedadPorGrado[indiceGrado];

                return new Chart(
                    document.getElementById(`grafico-gravedad-grado-${indiceGrado + 1}`),
                    {
                        type: 'doughnut',
                        data: {
                            labels: etiquetasGravedadDashboard,
                            datasets: [{
                                data: etiquetasGravedadDashboard.map((gravedad) =>
                                    resumen.cantidades[gravedad]
                                ),
                                backgroundColor: coloresGravedadDashboard,
                            }],
                        },
                        options: {
                            responsive: true,
                            cutout: '62%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8 } },
                                tooltip: {
                                    callbacks: {
                                        label: (contexto) => {
                                            const resumenActual = datosDashboardGrados[periodoDashboardGrados]
                                                .gravedadPorGrado[indiceGrado];
                                            const gravedad = contexto.label;
                                            const cantidad = contexto.parsed;
                                            const porcentaje = resumenActual.porcentajes[gravedad];

                                            return `${gravedad}: ${cantidad} (${porcentaje}%)`;
                                        },
                                    },
                                },
                            },
                        },
                        plugins: [pluginTotalGrado(indiceGrado)],
                    }
                );
            });

            function actualizarGraficosGravedadPorGrado(periodo) {
                periodoDashboardGrados = periodo;

                graficosGravedadPorGrado.forEach((grafico, indiceGrado) => {
                    const resumen = datosDashboardGrados[periodo].gravedadPorGrado[indiceGrado];
                    grafico.data.datasets[0].data = etiquetasGravedadDashboard.map((gravedad) =>
                        resumen.cantidades[gravedad]
                    );
                    grafico.update();
                });
            }

            const nombreSelectorDashboard = document.querySelector(
                'input[name="periodo-dashboard-admin"]'
            ) ? 'periodo-dashboard-admin' : 'periodo-dashboard';

            document.querySelectorAll(`input[name="${nombreSelectorDashboard}"]`).forEach((input) => {
                input.addEventListener('change', (evento) =>
                    actualizarGraficosGravedadPorGrado(evento.target.value)
                );
            });

            actualizarGraficosGravedadPorGrado(
                document.querySelector(`input[name="${nombreSelectorDashboard}"]:checked`).value
            );
        </script>
    @endif
@endsection