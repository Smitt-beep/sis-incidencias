@extends('layouts.app')

@section('title', 'Reporte Profesional Institucional')

@section('content')
    <style>
        .directivo-report { max-width: 1120px; margin: 16px auto 0; background: #fff; border-top: 4px solid #234f91; }
        .directivo-header { display: grid; grid-template-columns: 92px 1fr 210px; align-items: center; gap: 18px; padding: 12px 16px; border-bottom: 1px solid #dfe5ef; }
        .directivo-logo { width: 72px; height: 72px; object-fit: contain; }
        .directivo-title { text-align: center; }
        .directivo-title .sector { color: #234f91; font-size: 9px; font-weight: 800; }
        .directivo-title h2 { color: #234f91; font-size: 18px; margin: 3px 0; }
        .directivo-subtitle { color: #334155 !important; }
        .directivo-code { border-left: 1px solid #dfe5ef; padding-left: 14px; font-size: 10px; color: #334155; line-height: 1.7; }
        .directivo-context { display: grid; grid-template-columns: 1fr 1fr 1fr; background: #f1f5fb; padding: 8px 16px; font-size: 10px; }
        .directivo-context span { display: block; color: #334155; font-size: 9px; text-transform: uppercase; }
        .directivo-context strong { color: #0f172a !important; font-weight: 700 !important; }
        .directivo-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; padding: 12px 16px; }
        .directivo-kpi { border: 1px solid #dfe5ef; border-radius: 4px; min-height: 82px; padding: 10px 12px; background: #fff; }
        .directivo-kpi.total { background: #1f4c85; color: #fff; }
        .directivo-kpi.leve strong { color: #0756A3 !important; }
        .directivo-kpi.intermedia strong { color: #b45309 !important; }
        .directivo-kpi.grave strong { color: #dc3545 !important; }
        .directivo-kpi:not(.total) span { color: #334155 !important; }
        .directivo-kpi.leve { border-top: 4px solid #2368a8; }
        .directivo-kpi.intermedia { border-top: 4px solid #f0b429; }
        .directivo-kpi.grave { border-top: 4px solid #dc3545; }
        .directivo-kpi-label { color: #334155; font-size: 9px; font-weight: 800; }
        .directivo-kpi.total .directivo-kpi-label { color: #fff !important; }
        .directivo-kpi strong { font-size: 30px; }
        .directivo-grid { display: grid; grid-template-columns: 1.15fr .85fr; gap: 12px; padding: 0 16px 12px; }
        .directivo-panel { border: 1px solid #dfe5ef; border-radius: 4px; }
        .directivo-panel h3 { border-bottom: 1px solid #dfe5ef; color: #1e293b; margin: 0; padding: 8px 10px; font-size: 11px; }
        .directivo-table { width: 100%; font-size: 10px; border-collapse: collapse; }
        .directivo-table th { background: #204f88; color: #fff; padding: 7px 8px; text-align: right; }
        .directivo-table th:first-child, .directivo-table td:first-child { text-align: left; }
        .directivo-table td { border-bottom: 1px solid #edf0f5; color: #1e293b; padding: 7px 8px; text-align: right; }
        .directivo-state { color: #1e293b; padding: 8px 10px 2px; font-size: 10px; }
        .directivo-state-label { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .directivo-progress { height: 7px; background: #edf1f7; border-radius: 99px; overflow: hidden; }
        .directivo-progress div { height: 100%; border-radius: 99px; }
        .directivo-total-row { background: #eaf3ff; font-weight: 800; }
        .directivo-active { background: #f1f6fc; border: 1px solid #d5e0ee; border-radius: 4px; margin: 12px 10px 10px; padding: 8px; }
        .directivo-active h4 { color: #1e293b; font-size: 10px; font-weight: 800; margin: 0 0 6px; text-transform: uppercase; }
        .directivo-active-row { color: #1e293b; display: flex; justify-content: space-between; font-size: 10px; padding: 3px 0; }
        .directivo-active-row.grave { color: #1e293b; }
        .directivo-monthly { border: 1px solid #dfe5ef; margin: 0 16px 12px; padding: 8px 12px 4px; }
        .directivo-monthly-head { color: #1e293b; display: flex; justify-content: space-between; font-size: 10px; font-weight: 800; }
        .directivo-monthly-wrapper { height: 190px; width: 100%; position: relative; }
        .directivo-course-breakdown,
        .directivo-course-breakdown * { color: #1e293b !important; }
        .directivo-course-breakdown { border-top: 1px solid #dfe5ef; padding: 8px; }
        .directivo-course-breakdown h4 { color: #1e293b; font-size: 10px; font-weight: 800; margin: 0 0 6px; }
        .directivo-course-table { border-collapse: collapse; color: #1e293b; font-size: 10px; width: 100%; }
        .directivo-course-table th { background: #dbeafe; color: #1e293b; font-weight: 800; text-align: left; }
        .directivo-course-table th,
        .directivo-course-table td { border-bottom: 1px solid #e2e8f0; padding: 5px 8px; }
        .directivo-course-table th:last-child,
        .directivo-course-table td:last-child { text-align: right; width: 64px; }

        @media print {
            .no-print, .panel-sidebar, .panel-navbar, .chatbot-panel, .chatbot-toggle, .navbar { display: none !important; }
            .panel-main { width: 100% !important; margin: 0 !important; }
            @page { size: A4 portrait; margin: 8mm; }
            body, .panel-body { background: #fff !important; }
            .directivo-report { width: 100% !important; max-width: none !important; margin: 0 !important; box-shadow: none !important; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .directivo-report, .directivo-grid, .directivo-monthly { break-inside: avoid; }
            .directivo-header,
            .directivo-context,
            .directivo-kpi,
            .directivo-panel,
            .directivo-panel h3,
            .directivo-table th,
            .directivo-total-row,
            .directivo-course-breakdown,
            .directivo-course-table,
            .directivo-course-table th,
            .directivo-course-table td,
            .directivo-active,
            .directivo-monthly,
            .directivo-progress,
            .directivo-progress div,
            .directivo-monthly-wrapper,
            .directivo-monthly-wrapper canvas {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .directivo-report .directivo-title,
            .directivo-report .directivo-code,
            .directivo-report .directivo-context,
            .directivo-report .directivo-context *,
            .directivo-report .directivo-kpi:not(.total),
            .directivo-report .directivo-kpi:not(.total) *,
            .directivo-report .directivo-panel h3,
            .directivo-report .directivo-table td,
            .directivo-report .directivo-table tfoot td,
            .directivo-report .directivo-state,
            .directivo-report .directivo-state *,
            .directivo-report .directivo-active,
            .directivo-report .directivo-active *,
            .directivo-report .directivo-monthly-head {
                color: #111827 !important;
            }
            .directivo-report .directivo-kpi.total {
                background-color: #1f4c85 !important;
                color: #fff !important;
            }
            .directivo-report .directivo-kpi.total * {
                color: #fff !important;
            }
            .directivo-report .directivo-subtitle {
                color: #334155 !important;
            }
            .directivo-report .directivo-context strong {
                color: #0f172a !important;
                font-weight: 700 !important;
            }
            .directivo-report .directivo-kpi.leve strong {
                color: #0756A3 !important;
            }
            .directivo-report .directivo-kpi.intermedia strong {
                color: #b45309 !important;
            }
            .directivo-report .directivo-kpi.grave strong {
                color: #dc3545 !important;
            }
            .directivo-report .directivo-kpi:not(.total) span {
                color: #334155 !important;
            }
            .directivo-report .directivo-course-breakdown,
            .directivo-report .directivo-course-breakdown * {
                color: #1e293b !important;
            }
            .directivo-report .directivo-course-table th {
                background-color: #dbeafe !important;
                color: #1e293b !important;
            }
            .directivo-report .directivo-course-table td {
                color: #1e293b !important;
            }
            .directivo-report .directivo-table th {
                background-color: #dbeafe !important;
                color: #111827 !important;
            }
            .directivo-monthly-wrapper { height: 190px !important; }
        }

        @media (max-width: 767.98px) {
            .directivo-header { grid-template-columns: 64px 1fr; }
            .directivo-logo { width: 58px; height: 58px; }
            .directivo-code { grid-column: 1 / -1; border-left: 0; border-top: 1px solid #dfe5ef; padding: 8px 0 0; }
            .directivo-context { grid-template-columns: 1fr 1fr; gap: 8px; }
            .directivo-kpis { grid-template-columns: 1fr 1fr; }
            .directivo-grid { grid-template-columns: 1fr; }
        }
    </style>

    @php
        $gradoSeleccionado = old('grado', $reporteDirectivo['grado'] ?? 'todos');
        $cursoSeleccionado = old('curso_id', $reporteDirectivo['curso_id'] ?? '');
        $anioSeleccionado = old('anio', $reporteDirectivo['anio'] ?? now()->year);
        $seccionesSeleccionadas = array_map(
            'strval',
            (array) old('secciones', $reporteDirectivo['secciones'] ?? [])
        );
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
        <div>
            <h1 class="h2 mb-1">Reporte Profesional Institucional</h1>
            <p class="text-secondary mb-0">Selecciona el alcance, curso, periodo y secciones que deseas incluir.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="POST" action="{{ route('reporte-profesional.institucional.generar') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="reporte-grado" class="form-label">Alcance / Grado</label>
                    <select name="grado" id="reporte-grado" class="form-select">
                        <option value="todos" @selected((string) $gradoSeleccionado === 'todos')>Todos los grados</option>
                        @foreach ($grados as $grado)
                            <option value="{{ $grado }}" @selected((string) $gradoSeleccionado === (string) $grado)>{{ $grado }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="reporte-curso" class="form-label">Curso (opcional)</label>
                    <select name="curso_id" id="reporte-curso" class="form-select">
                        <option value="">Todos los cursos</option>
                        @foreach ($cursos as $curso)
                            <option value="{{ $curso->id }}" @selected((string) $cursoSeleccionado === (string) $curso->id)>{{ $curso->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="reporte-anio" class="form-label">Periodo / Año</label>
                    <input type="number" name="anio" id="reporte-anio" class="form-control" min="2000" max="2100" value="{{ $anioSeleccionado }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Secciones</label>
                    <div class="border rounded p-2" style="max-height: 110px; overflow-y: auto;">
                        @foreach ($seccionesDisponibles as $seccion)
                            <div class="form-check" data-reporte-seccion-grado="{{ $seccion->grado }}">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="secciones[]"
                                    id="reporte-directivo-seccion-{{ $seccion->id }}"
                                    value="{{ $seccion->id }}"
                                    @checked(in_array((string) $seccion->id, $seccionesSeleccionadas, true))
                                >
                                <label class="form-check-label" for="reporte-directivo-seccion-{{ $seccion->id }}">{{ $seccion->grado }} - {{ $seccion->seccion }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Generar reporte</button>
                    @error('secciones') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    @error('curso_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </form>
        </div>
    </div>

    @isset($reporteDirectivo)
        @php
            $r = $reporteDirectivo;
            $anio = $r['anio'];
            $fecha = now()->format('d/m/Y');
            $codigo = 'REP-' . $anio . '-' . substr(strtoupper(md5($r['alcance'].'|'.($r['curso_id'] ?? 'todos').'|'.$fecha)), 0, 8);
            $maxMes = max($r['mensual']['valores']);
            $indiceMes = array_search($maxMes, $r['mensual']['valores'], true);
            $mesMayor = $r['total'] > 0 && $indiceMes !== false
                ? $r['mensual']['labels'][$indiceMes]
                : 'Sin datos';
        @endphp
        <div class="d-flex justify-content-end mb-2 no-print">
            <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir / Guardar PDF</button>
        </div>
        <section class="directivo-report shadow-sm" aria-label="Reporte Profesional Institucional">
            <header class="directivo-header">
                <img src="{{ asset('images/escudo-ie-tungasuca.png') }}" alt="Escudo I.E. Tungasuca" class="directivo-logo">
                <div class="directivo-title">
                    <div class="sector">SECTOR EDUCACIÓN · UGEL 04 COMAS / TUNGASUCA</div>
                    <h2>INSTITUCIÓN EDUCATIVA TUNGASUCA</h2>
                    <div class="directivo-subtitle">REPORTE PROFESIONAL INSTITUCIONAL DE INCIDENCIAS</div>
                </div>
                <div class="directivo-code">
                    <div>CÓDIGO REGISTRO</div>
                    <strong>{{ $codigo }}</strong>
                    <div>Fecha: {{ $fecha }}</div>
                    <div>Año lectivo: {{ $anio }}</div>
                </div>
            </header>
            <div class="directivo-context">
                <div><span>Alcance</span><strong>{{ $r['alcance'] }}@if ($r['seccionesNombres']) · Secciones {{ implode(', ', $r['seccionesNombres']) }}@endif</strong></div>
                <div><span>Curso curricular</span><strong>{{ $r['curso_id'] ? ($cursos->firstWhere('id', $r['curso_id'])?->nombre ?? 'Curso') : 'Todos los cursos' }}</strong></div>
                <div><span>Autoridad emisora</span><strong>{{ $r['autoridad'] }} · {{ $r['docente'] }}</strong></div>
            </div>
            <div class="directivo-kpis">
                <div class="directivo-kpi total"><div class="directivo-kpi-label">TOTAL REGISTRADAS</div><strong>{{ $r['total'] }}</strong></div>
                @foreach (['Leve' => 'leve', 'Intermedia' => 'intermedia', 'Grave' => 'grave'] as $nombre => $clase)
                    <div class="directivo-kpi {{ $clase }}">
                        <div class="directivo-kpi-label">INCIDENCIAS {{ strtoupper($nombre) }}S</div>
                        <strong>{{ $r['gravedades']['cantidades'][$nombre] }}</strong>
                        <span>{{ $r['gravedades']['porcentajes'][$nombre] }}% del total</span>
                    </div>
                @endforeach
            </div>
            <div class="directivo-grid">
                <div class="directivo-panel">
                    <h3>{{ $r['grado'] === null ? 'INCIDENCIAS POR GRADO' : 'INCIDENCIAS POR SECCIÓN' }}</h3>
                    <table class="directivo-table">
                        <thead><tr><th>{{ $r['grado'] === null ? 'Grado' : 'Sección' }}</th><th>Total</th><th>Leve</th><th>Intermedia</th><th>Grave</th></tr></thead>
                        <tbody>
                            @forelse ($r['comparativa'] as $fila)
                                <tr>
                                    <td>{{ $fila['nombre'] }}</td>
                                    <td>{{ $fila['total'] }}</td>
                                    <td>{{ $fila['Leve'] }}</td>
                                    <td>{{ $fila['Intermedia'] }}</td>
                                    <td>{{ $fila['Grave'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Sin datos para el alcance seleccionado</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="directivo-total-row">
                                <td>TOTAL GENERAL</td>
                                <td>{{ $r['total'] }}</td>
                                <td>{{ $r['gravedades']['cantidades']['Leve'] }}</td>
                                <td>{{ $r['gravedades']['cantidades']['Intermedia'] }}</td>
                                <td>{{ $r['gravedades']['cantidades']['Grave'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @if ($r['porCurso'])
                        <div class="directivo-course-breakdown">
                            <h4>Desglose por curso</h4>
                            <table class="directivo-course-table">
                                <thead>
                                    <tr><th>Curso</th><th>Total</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($r['porCurso'] as $cursoFila)
                                        <tr>
                                            <td>{{ $cursoFila['curso'] }}</td>
                                            <td>{{ $cursoFila['total'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="directivo-panel">
                    <h3>Estado de incidencias</h3>
                    @foreach (['Pendiente' => '#2f75c9', 'En Proceso' => '#f0b429', 'Finalizado' => '#3ca86b'] as $estado => $color)
                        @php
                            $cantidad = $r['estados']['cantidades'][$estado] ?? 0;
                            $porcentaje = $r['estados']['porcentajes'][$estado] ?? 0;
                        @endphp
                        <div class="directivo-state">
                            <div class="directivo-state-label"><span>{{ $estado }}</span><strong>{{ $cantidad }} ({{ $porcentaje }}%)</strong></div>
                            <div class="directivo-progress"><div style="width: {{ $porcentaje }}%; background: {{ $color }};"></div></div>
                        </div>
                    @endforeach
                    <div class="directivo-active">
                        <h4>Resumen de gestión activa</h4>
                        <div class="directivo-active-row">
                            <span>Pendientes y en proceso</span>
                            <strong>{{ $r['gestionActiva']['pendientesEnProceso'] }} casos</strong>
                        </div>
                        <div class="directivo-active-row grave">
                            <span>Graves activas</span>
                            <strong>{{ $r['gestionActiva']['gravesActivas'] }} casos</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="directivo-monthly">
                <div class="directivo-monthly-head">
                    <span>Evolución mensual (Ene–Dic {{ $anio }})</span>
                    <span>{{ $mesMayor === 'Sin datos' ? 'Sin incidencias' : 'Mes mayor: '.$mesMayor.' ('.$maxMes.')' }}</span>
                </div>
                <div class="directivo-monthly-wrapper">
                    <canvas id="directivo-mensual" aria-label="Incidencias mensuales institucionales"></canvas>
                </div>
            </div>
        </section>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            const directivoMensual = @json($r['mensual']);
            new Chart(document.getElementById('directivo-mensual'), {
                type: 'bar',
                data: {
                    labels: directivoMensual.labels.map((mes) => mes.substring(0, 3)),
                    datasets: [{
                        label: 'Incidencias',
                        data: directivoMensual.valores,
                        backgroundColor: '#3a78bd',
                        borderRadius: 2,
                        maxBarThickness: 36,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    color: '#1e293b',
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } },
                    plugins: { legend: { display: false } },
                },
            });
        </script>
    @endisset

    <script>
        const reporteGrado = document.getElementById('reporte-grado');
        if (reporteGrado) {
            const actualizarSeccionesReporte = () => {
                const gradoSeleccionado = reporteGrado.value;
                document.querySelectorAll('[data-reporte-seccion-grado]').forEach((contenedor) => {
                    const coincide = gradoSeleccionado === 'todos'
                        || contenedor.dataset.reporteSeccionGrado === gradoSeleccionado;
                    contenedor.hidden = !coincide;
                    const checkbox = contenedor.querySelector('input[type="checkbox"]');
                    if (checkbox && !coincide) {
                        checkbox.checked = false;
                    }
                });
            };
            reporteGrado.addEventListener('change', actualizarSeccionesReporte);
            actualizarSeccionesReporte();
        }
    </script>
@endsection
