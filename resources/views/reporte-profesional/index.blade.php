@extends('layouts.app')

@section('title', 'Reporte Profesional')

@section('content')
<style>
    .professional-grade-menu { min-width: 13rem; width: min(18rem, calc(100vw - 3rem)); }
    .professional-course-item { position: relative; }
    .professional-course-toggle {
        align-items: center;
        display: flex;
        justify-content: space-between;
        flex: 1 1 auto;
        min-width: 0;
    }
    .professional-course-row { display: flex; align-items: center; gap: .35rem; width: 100%; }
    .professional-row-submit { white-space: nowrap; }
    .professional-course-submenu {
        display: none;
        left: calc(100% - 1px);
        min-width: 17rem;
        position: absolute;
        top: -.25rem;
    }
    .professional-course-item:hover > .professional-course-submenu,
    .professional-course-item:focus-within > .professional-course-submenu,
    .professional-course-item.is-open > .professional-course-submenu { display: block; }
    .professional-report-document {
        --report-blue: #234f91;
        --report-ink: #172033;
        max-width: 1120px;
        margin: 0 auto;
        background: #fff;
    }
    .professional-report-header {
        display: grid;
        grid-template-columns: 92px 1fr 210px;
        align-items: center;
        gap: 18px;
        border-top: 4px solid var(--report-blue);
        border-bottom: 1px solid #dfe5ef;
        padding: 12px 16px;
    }
    .professional-report-logo { width: 72px; height: 72px; object-fit: contain; }
    .professional-report-institution { text-align: center; }
    .professional-report-institution .sector { color: var(--report-blue); font-size: 10px; font-weight: 800; letter-spacing: .04em; }
    .professional-report-institution h2 { color: #244f96; font-size: 18px; font-weight: 800; line-height: 1.05; margin: 3px 0 4px; }
    .professional-report-institution .report-title { color: #42536e; font-size: 10px; font-weight: 700; }
    .professional-report-code { border-left: 1px solid #dfe5ef; padding-left: 14px; font-size: 10px; color: #526178; line-height: 1.7; }
    .professional-report-code strong { color: #1c2a44; }
    .professional-context-bar {
        display: grid;
        grid-template-columns: 1.1fr 1fr 1.4fr;
        gap: 0;
        background: #f1f5fb;
        border-bottom: 1px solid #dfe5ef;
        padding: 8px 16px;
        font-size: 11px;
    }
    .professional-context-bar span { color: #6a7890; display: block; font-size: 9px; text-transform: uppercase; }
    .professional-context-bar strong { color: #24344e; }
    .professional-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 12px 0; }
    .professional-kpi {
        border: 1px solid #dfe5ef;
        border-radius: 4px;
        min-height: 82px;
        padding: 10px 12px;
        background: #fff;
    }
    .professional-kpi.total { background: #1f4c85; color: #fff; }
    .professional-kpi.leve { border-top: 4px solid #2368a8; }
    .professional-kpi.intermedia { border-top: 4px solid #f0b429; }
    .professional-kpi.grave { border-top: 4px solid #dc3545; }
    .professional-kpi-label { color: #64748b; font-size: 9px; font-weight: 800; text-transform: uppercase; }
    .professional-kpi.total .professional-kpi-label { color: #dbe7f7; }
    .professional-kpi-value { display: flex; align-items: baseline; gap: 8px; }
    .professional-kpi-value strong { font-size: 30px; line-height: 1.1; }
    .professional-kpi-value small { font-size: 10px; font-weight: 700; }
    .professional-report-grid { display: grid; grid-template-columns: 1.15fr .85fr; gap: 12px; }
    .professional-report-panel { border: 1px solid #dfe5ef; border-radius: 4px; background: #fff; }
    .professional-report-panel h3 { border-bottom: 1px solid #dfe5ef; margin: 0; padding: 8px 10px; font-size: 11px; font-weight: 800; color: #263d61; text-transform: uppercase; }
    .professional-report-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .professional-report-table th { background: #204f88; color: #fff; padding: 7px 8px; text-align: right; }
    .professional-report-table th:first-child, .professional-report-table td:first-child { text-align: left; }
    .professional-report-table td { border-bottom: 1px solid #edf0f5; padding: 7px 8px; text-align: right; }
    .professional-state-row { padding: 7px 10px 2px; }
    .professional-state-label { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 4px; }
    .professional-progress { height: 7px; background: #edf1f7; border-radius: 99px; overflow: hidden; }
    .professional-progress div { height: 100%; border-radius: 99px; }
    .professional-monthly-chart { border: 1px solid #dfe5ef; border-radius: 4px; margin-top: 12px; padding: 10px 12px 4px; }
    .professional-monthly-chart .monthly-chart-wrapper { height: 190px; width: 100%; position: relative; }
    .professional-monthly-head { display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #263d61; font-weight: 800; }
    .professional-monthly-head span { border-radius: 99px; background: #eef4ff; color: #315e9b; font-size: 9px; padding: 4px 8px; }
    @media (max-width: 767.98px) {
        .professional-report-header { grid-template-columns: 64px 1fr; }
        .professional-report-logo { width: 58px; height: 58px; }
        .professional-report-code { grid-column: 1 / -1; border-left: 0; border-top: 1px solid #dfe5ef; padding: 8px 0 0; }
        .professional-context-bar { grid-template-columns: 1fr 1fr; gap: 8px; }
        .professional-kpi-grid { grid-template-columns: 1fr 1fr; }
        .professional-report-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 767.98px) {
        .professional-course-submenu {
            border: 0;
            box-shadow: none;
            left: auto;
            min-width: 0;
            position: static;
            width: 100%;
        }
        .professional-course-item.is-open > .professional-course-submenu { display: block; }
    }
    @media print {
        .panel-sidebar, .chatbot-panel, .chatbot-toggle, .no-print, .navbar { display: none !important; }
        .panel-main { width: 100% !important; margin: 0 !important; }
        .card { break-inside: avoid; }
        @page { size: A4 portrait; margin: 8mm; }
        body, .panel-body { background: #fff !important; }
        .professional-report-document {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            box-shadow: none !important;
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        .professional-report-document section,
        .professional-report-document .professional-report-grid,
        .professional-report-document .professional-monthly-chart {
            break-inside: avoid;
        }
        .professional-monthly-chart .monthly-chart-wrapper { height: 190px !important; }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 no-print">
    <div>
        <h1 class="h2 mb-1">Reporte Profesional</h1>
        <p class="text-secondary mb-0">Selecciona grado, curso y una o varias secciones.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 no-print">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">Grado</th>
                <th scope="col">Cursos</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($grados as $gradoIndex => $grado)
                <tr>
                    <th scope="row" class="fw-semibold">{{ $grado['nombre'] }}</th>
                    <td>
                        <div class="dropdown">
                            <button
                                class="btn btn-sm btn-outline-primary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                            >
                                Ver cursos
                                <span class="badge text-bg-primary ms-1">{{ $grado['cursos']->count() }}</span>
                            </button>
                            <div class="dropdown-menu professional-grade-menu p-2">
                                @forelse ($grado['cursos'] as $cursoIndex => $curso)
                                    @php
                                        $reporteFormId = 'reporte-form-'.$gradoIndex.'-'.$cursoIndex;
                                    @endphp
                                    <div class="professional-course-item">
                                        <div class="professional-course-row">
                                            <button
                                                class="dropdown-item professional-course-toggle"
                                                type="button"
                                                aria-expanded="false"
                                            >
                                                <span>{{ $curso['nombre'] }}</span>
                                                <span aria-hidden="true">&rsaquo;</span>
                                            </button>
                                            <button
                                                type="submit"
                                                form="{{ $reporteFormId }}"
                                                class="btn btn-primary btn-sm professional-row-submit d-none"
                                                data-row-submit
                                            >
                                                Generar reporte
                                            </button>
                                        </div>
                                        <div class="dropdown-menu professional-course-submenu p-3">
                                            <form
                                                id="{{ $reporteFormId }}"
                                                method="POST"
                                                action="{{ route('reporte-profesional.generar') }}"
                                                data-professional-report-form
                                            >
                                                @csrf
                                                <input type="hidden" name="grado" value="{{ $grado['nombre'] }}">
                                                <input type="hidden" name="curso_id" value="{{ $curso['id'] }}">
                                                <fieldset>
                                                    <legend class="h6">Secciones</legend>
                                                    @forelse ($curso['secciones'] as $seccion)
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                name="secciones[]"
                                                                id="reporte-seccion-{{ $seccion->id }}"
                                                                value="{{ $seccion->id }}"
                                                                @checked(in_array($seccion->id, (array) old('secciones', [])))
                                                            >
                                                            <label class="form-check-label" for="reporte-seccion-{{ $seccion->id }}">
                                                                Sección {{ $seccion->seccion }}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <p class="text-secondary small mb-2">Sin secciones asignadas</p>
                                                    @endforelse
                                                </fieldset>
                                                @error('secciones') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                                @error('curso_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                                <button
                                                    type="submit"
                                                    class="btn btn-primary btn-sm mt-3"
                                                    data-panel-submit
                                                    @disabled($curso['secciones']->isEmpty())
                                                >
                                                    Generar reporte
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-secondary small p-2">Sin cursos disponibles</div>
                                @endforelse
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-secondary py-4">
                        No hay grados, cursos o secciones disponibles.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($reporte)
    @php
        $anioLectivo = now()->year;
        $fechaReporte = now()->format('d/m/Y');
        $codigoReporte = 'REP-' . $anioLectivo . '-' . substr(strtoupper(md5(
            $reporte['grado'].'|'.$reporte['curso'].'|'.implode(',', $reporte['secciones']).'|'.$fechaReporte
        )), 0, 8);
        $docente = session('usuario_nombre') ?: 'No registrado';
        $maxMes = max($reporte['mensual']['valores']);
        $indiceMesMayor = array_search($maxMes, $reporte['mensual']['valores'], true);
        $nombreMesMayor = $reporte['total'] > 0 && $indiceMesMayor !== false
            ? $reporte['mensual']['labels'][$indiceMesMayor]
            : 'Sin datos';
    @endphp
    <div class="d-flex justify-content-end mb-2 no-print">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            Imprimir / Guardar PDF
        </button>
    </div>
    <section id="reporte-profesional-resultados" class="professional-report-document shadow-sm" aria-labelledby="reporte-profesional-title">
        <header class="professional-report-header">
            <img src="{{ asset('images/escudo-ie-tungasuca.png') }}" alt="Escudo de la I.E. Tungasuca" class="professional-report-logo">
            <div class="professional-report-institution">
                <div class="sector">SECTOR EDUCACIÓN · UGEL 04 COMAS / TUNGASUCA</div>
                <h2 id="reporte-profesional-title">INSTITUCIÓN EDUCATIVA TUNGASUCA</h2>
                <div class="report-title">REPORTE PROFESIONAL DE GESTIÓN Y SEGUIMIENTO DE INCIDENCIAS</div>
            </div>
            <div class="professional-report-code">
                <div>CÓDIGO REGISTRO</div>
                <strong>{{ $codigoReporte }}</strong>
                <div>Fecha: {{ $fechaReporte }}</div>
                <div>Año lectivo: {{ $anioLectivo }}</div>
            </div>
        </header>

        <div class="professional-context-bar">
            <div><span>Grado / Curso</span><strong>{{ $reporte['grado'] }} · {{ $reporte['curso'] }}</strong></div>
            <div><span>Secciones</span><strong>{{ implode(', ', array_map(fn ($nombre) => 'Sección '.$nombre, $reporte['secciones'])) ?: 'N/A' }}</strong></div>
            <div><span>Docente / Tutor</span><strong>{{ $docente }}</strong></div>
        </div>

        <div class="px-3 pt-2 pb-3">
            <div class="professional-kpi-grid">
                <div class="professional-kpi total">
                    <div class="professional-kpi-label">Total</div>
                    <div class="professional-kpi-value"><strong>{{ $reporte['total'] }}</strong></div>
                </div>
                @foreach ([
                    'Leve' => 'leve',
                    'Intermedia' => 'intermedia',
                    'Grave' => 'grave',
                ] as $nombre => $clase)
                    <div class="professional-kpi {{ $clase }}">
                        <div class="professional-kpi-label">{{ $nombre }}</div>
                        <div class="professional-kpi-value">
                            <strong>{{ $reporte['gravedades']['cantidades'][$nombre] }}</strong>
                            <small>{{ $reporte['gravedades']['porcentajes'][$nombre] }}%</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="professional-report-grid">
                <div class="professional-report-panel">
                    <h3>Comparativa por sección</h3>
                    <table class="professional-report-table">
                        <thead>
                            <tr>
                                <th>Sección</th>
                                <th>Total</th>
                                <th>Leve</th>
                                <th>Intermedia</th>
                                <th>Grave</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reporte['comparativa'] as $fila)
                                <tr>
                                    <td>Sección {{ $fila['seccion'] }}</td>
                                    <td>{{ $fila['total'] ?? 0 }}</td>
                                    <td>{{ $fila['gravedades']['Leve'] ?? 0 }}</td>
                                    <td>{{ $fila['gravedades']['Intermedia'] ?? 0 }}</td>
                                    <td>{{ $fila['gravedades']['Grave'] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Sin secciones seleccionadas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="professional-report-panel">
                    <h3>Estado de incidencias</h3>
                    @foreach ([
                        'Pendiente' => '#2f75c9',
                        'En Proceso' => '#f0b429',
                        'Finalizado' => '#3ca86b',
                    ] as $estado => $color)
                        @php
                            $cantidadEstado = $reporte['estados']['cantidades'][$estado] ?? 0;
                            $porcentajeEstado = $reporte['estados']['porcentajes'][$estado] ?? 0;
                        @endphp
                        <div class="professional-state-row">
                            <div class="professional-state-label">
                                <span>{{ $estado }}</span>
                                <strong>{{ $cantidadEstado }} ({{ $porcentajeEstado }}%)</strong>
                            </div>
                            <div class="professional-progress">
                                <div style="width: {{ $porcentajeEstado }}%; background: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="professional-monthly-chart">
                <div class="professional-monthly-head">
                    <div>Evolución mensual ({{ $nombreMesMayor === 'Sin datos' ? 'Ene–Dic '.$anioLectivo : $anioLectivo }})</div>
                    <span>{{ $nombreMesMayor === 'Sin datos' ? 'Sin incidencias' : 'Mes mayor: '.$nombreMesMayor.' ('.$maxMes.')' }}</span>
                </div>
                <div class="monthly-chart-wrapper">
                    <canvas id="reporte-profesional-mensual" aria-label="Incidencias por mes"></canvas>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const reporteMensual = @json($reporte['mensual']);
        new Chart(document.getElementById('reporte-profesional-mensual'), {
            type: 'bar',
            data: {
                labels: reporteMensual.labels.map((mes) => mes.substring(0, 3)),
                datasets: [{
                    label: 'Incidencias',
                    data: reporteMensual.valores,
                    backgroundColor: '#3a78bd',
                    borderRadius: 2,
                    maxBarThickness: 36,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf1f6' } },
                    x: { grid: { display: false } },
                },
                plugins: { legend: { display: false } },
            },
        });
    </script>
@endif

<script>
    document.querySelectorAll('.professional-course-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const item = button.closest('.professional-course-item');
            const shouldOpen = !item.classList.contains('is-open');
            item.parentElement.querySelectorAll('.professional-course-item.is-open').forEach((openItem) => {
                openItem.classList.remove('is-open');
                openItem.querySelector('.professional-course-toggle').setAttribute('aria-expanded', 'false');
            });
            item.classList.toggle('is-open', shouldOpen);
            button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        });
    });

    document.querySelectorAll('[data-professional-report-form]').forEach((form) => {
        const item = form.closest('.professional-course-item');
        const panelSubmit = form.querySelector('[data-panel-submit]');
        const rowSubmit = item?.querySelector('[data-row-submit]');
        const sectionChecks = Array.from(form.querySelectorAll('input[name="secciones[]"]'));

        const actualizarBotones = () => {
            const tieneSecciones = sectionChecks.some((checkbox) => checkbox.checked);

            if (panelSubmit) {
                panelSubmit.disabled = !tieneSecciones;
            }
            if (rowSubmit) {
                rowSubmit.classList.toggle('d-none', !tieneSecciones);
                rowSubmit.disabled = !tieneSecciones;
            }
        };

        sectionChecks.forEach((checkbox) => {
            checkbox.addEventListener('click', (evento) => evento.stopPropagation());
            checkbox.addEventListener('change', (evento) => {
                evento.stopPropagation();
                actualizarBotones();
            });
        });

        form.addEventListener('click', (evento) => {
            evento.stopPropagation();
        });

        actualizarBotones();
    });

    const resultadosReporte = document.getElementById('reporte-profesional-resultados');
    if (resultadosReporte) {
        requestAnimationFrame(() => {
            resultadosReporte.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
</script>
@endsection
