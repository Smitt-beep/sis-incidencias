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

        @if ($graficos && ($graficos['tipo'] ?? null) !== 'admin')
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

    @if ($graficos && ($graficos['tipo'] ?? null) !== 'admin')
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
    @endif

    @if (isset($graficos['tipo']) && $graficos['tipo'] === 'admin')
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
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 text-secondary mb-3">Incidencias por grado</h3>
                            <canvas id="grafico-admin-grado" height="220" role="img" aria-label="Cantidad de incidencias por grado"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 text-secondary mb-3">Cursos de teoría</h3>
                            <canvas id="grafico-admin-curso-teoria" height="220" role="img" aria-label="Cantidad de incidencias por curso de teoría"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 text-secondary mb-3">Cursos de taller</h3>
                            <canvas id="grafico-admin-curso-taller" height="220" role="img" aria-label="Cantidad de incidencias por curso de taller"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mx-auto">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="h6 text-secondary mb-3">Incidencias por gravedad</h3>
                            <canvas id="grafico-admin-gravedad" height="220" role="img" aria-label="Incidencias por nivel de gravedad"></canvas>
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

            const pluginTotalGravedadAdmin = {
                id: 'totalGravedadAdmin',
                afterDraw(chart) {
                    const area = chart.chartArea;
                    if (!area) {
                        return;
                    }

                    const contexto = chart.ctx;
                    const centroX = (area.left + area.right) / 2;
                    const centroY = (area.top + area.bottom) / 2;
                    const radioInterno = chart.getDatasetMeta(0).data[0]?.innerRadius
                        ?? Math.min(area.width, area.height) * 0.25;
                    const tamanoNumero = Math.max(14, Math.min(radioInterno * 0.55, area.height * 0.16));
                    const tamanoEtiqueta = Math.max(10, Math.min(tamanoNumero * 0.35, 14));
                    const total = datosDashboardAdmin[periodoDashboardAdmin].gravedad.total;

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

            function opcionesBarrasAdmin(etiquetaEjeX) {
                return {
                    responsive: true,
                    plugins: { legend: { display: true, position: 'bottom' } },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false },
                            title: { display: true, text: etiquetaEjeX },
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            title: { display: true, text: 'Incidencias' },
                        },
                    },
                };
            }

            const graficoAdminGrado = new Chart(document.getElementById('grafico-admin-grado'), {
                type: 'bar',
                data: { labels: [], datasets: [] },
                options: opcionesBarrasAdmin('Grados'),
            });

            const graficoAdminCursoTeoria = new Chart(document.getElementById('grafico-admin-curso-teoria'), {
                type: 'bar',
                data: { labels: [], datasets: [] },
                options: opcionesBarrasAdmin('Cursos'),
            });

            const graficoAdminCursoTaller = new Chart(document.getElementById('grafico-admin-curso-taller'), {
                type: 'bar',
                data: { labels: [], datasets: [] },
                options: opcionesBarrasAdmin('Cursos'),
            });

            const graficoAdminGravedad = new Chart(document.getElementById('grafico-admin-gravedad'), {
                type: 'doughnut',
                data: {
                    labels: ['Leve', 'Intermedia', 'Grave'],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#0756A3', '#F5C542', '#dc3545'],
                    }],
                },
                options: {
                    responsive: true,
                    cutout: '68%',
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
                plugins: [pluginTotalGravedadAdmin],
            });

            function actualizarGraficosAdmin(periodo) {
                periodoDashboardAdmin = periodo;
                const datosPeriodo = datosDashboardAdmin[periodo];

                function actualizarBarras(chart, resumen) {
                    chart.data.labels = resumen.labels;
                    chart.data.datasets = Object.entries(resumen.series).map(([gravedad, valores]) => ({
                        label: gravedad,
                        data: valores,
                        backgroundColor: coloresGravedadAdmin[gravedad],
                    }));
                    chart.update();
                }

                actualizarBarras(graficoAdminGrado, datosPeriodo.porGradoApilado);
                actualizarBarras(graficoAdminCursoTeoria, datosPeriodo.porCursoTeoriaApilado);
                actualizarBarras(graficoAdminCursoTaller, datosPeriodo.porCursoTallerApilado);

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

            document.querySelectorAll('input[name="periodo-dashboard-admin"]').forEach((input) => {
                input.addEventListener('change', (evento) => actualizarGraficosAdmin(evento.target.value));
            });

            actualizarGraficosAdmin('semana');
        </script>
    @endif
@endsection