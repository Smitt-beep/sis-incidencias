<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de reporte de incidencias - {{ $estudiante->nombre }}</title>
    <style>
        @page {
            margin: 24px 30px;
        }

        body {
            color: #17243b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .institution {
            border-bottom: 1px solid #dce3ed;
            margin-bottom: 8px;
            padding: 0 0 9px;
            width: 100%;
        }

        .institution td {
            vertical-align: middle;
        }

        .crest-cell {
            width: 58px;
        }

        .crest {
            height: 52px;
            width: 52px;
        }

        .institution-copy {
            padding-left: 8px;
        }

        .ministry {
            color: #50627d;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .school {
            color: #172e58;
            font-size: 15px;
            font-weight: bold;
            margin: 3px 0;
            text-transform: uppercase;
        }

        .committee {
            color: #50627d;
            font-size: 7px;
        }

        .metadata {
            margin: 5px 0;
            width: 100%;
        }

        .metadata td {
            vertical-align: middle;
        }

        .record-number {
            background: #edf2f8;
            border-radius: 3px;
            color: #334a6a;
            font-size: 8px;
            font-weight: bold;
            padding: 5px 8px;
        }

        .state-label {
            color: #50627d;
            font-size: 8px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .badge {
            border-radius: 9px;
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
            padding: 4px 8px;
        }

        .state-pendiente, .priority-high {
            background: #fff1e8;
            color: #b94b18;
        }

        .state-proceso, .priority-medium {
            background: #fff6db;
            color: #966300;
        }

        .state-finalizado, .priority-low {
            background: #e7f6ed;
            color: #257447;
        }

        .state-default, .priority-default {
            background: #edf2f8;
            color: #40536e;
        }

        .title {
            background: #f4f7fb;
            border: 1px solid #e3eaf3;
            border-radius: 4px;
            color: #183b72;
            font-size: 11px;
            letter-spacing: .3px;
            margin: 6px 0 10px;
            padding: 7px;
            text-align: center;
        }

        .cards {
            border-collapse: separate;
            border-spacing: 7px 0;
            margin: 0 -7px 7px;
            table-layout: fixed;
            width: 100%;
        }

        .card {
            border: 1px solid #dce4ef;
            border-radius: 5px;
            vertical-align: top;
        }

        .card-title {
            background: #f1f4f8;
            border-bottom: 1px solid #e1e7ef;
            color: #294e80;
            font-size: 8px;
            font-weight: bold;
            padding: 7px 8px;
            text-transform: uppercase;
        }

        .card-content {
            padding: 8px;
        }

        .student-card {
            width: 50%;
        }

        .event-card {
            width: 50%;
        }

        .student-name {
            background: #f6f8fb;
            border-radius: 4px;
            margin-bottom: 6px;
            padding: 7px;
        }

        .field-label {
            color: #65748b;
            display: block;
            font-size: 7px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .field-value {
            color: #1c2d49;
            font-size: 9px;
            font-weight: bold;
        }

        .muted {
            color: #65748b;
            font-size: 8px;
        }

        .detail-grid {
            border-collapse: collapse;
            width: 100%;
        }

        .detail-grid td {
            padding: 4px 3px;
            vertical-align: top;
            width: 50%;
        }

        .incident-types {
            margin-top: 6px;
        }

        .type-chip {
            background: #2464ce;
            border-radius: 8px;
            color: #fff;
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
            margin: 2px 2px 0 0;
            padding: 4px 7px;
        }

        .description {
            line-height: 1.5;
            min-height: 26px;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }

        .photo-card {
            page-break-inside: avoid;
        }

        .photo-item {
            border: 1px solid #e1e7ef;
            border-radius: 4px;
            margin: 0 0 7px;
            padding: 6px;
            page-break-inside: avoid;
            text-align: center;
        }

        .photo {
            max-height: 235px;
            max-width: 100%;
        }

        .photo-caption {
            color: #65748b;
            font-size: 7px;
            margin-top: 4px;
            overflow-wrap: break-word;
        }

        .empty-message {
            border: 1px solid #dce4ef;
            border-radius: 5px;
            color: #50627d;
            margin-top: 10px;
            padding: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $crestPath = public_path('images/escudo-ie-tungasuca.jpg');
        $crestData = is_file($crestPath) ? base64_encode(file_get_contents($crestPath)) : null;
    @endphp

    @forelse ($incidencias as $incidencia)
        @php
            $estado = strtolower($incidencia->estado?->nombre ?? '');
            $estadoClass = match (true) {
                str_contains($estado, 'proceso') => 'state-proceso',
                str_contains($estado, 'finalizado') => 'state-finalizado',
                str_contains($estado, 'pendiente') => 'state-pendiente',
                default => 'state-default',
            };
            $gravedad = strtolower($incidencia->gravedad?->nombre ?? '');
            $prioridadClass = match (true) {
                str_contains($gravedad, 'grave') => 'priority-high',
                str_contains($gravedad, 'intermedia') => 'priority-medium',
                str_contains($gravedad, 'leve') => 'priority-low',
                default => 'priority-default',
            };
            $prioridad = match ($prioridadClass) {
                'priority-high' => 'Prioridad alta',
                'priority-medium' => 'Prioridad media',
                'priority-low' => 'Prioridad baja',
                default => 'Sin clasificar',
            };
        @endphp

        <div style="{{ $loop->last ? '' : 'page-break-after: always;' }}">
            <table class="institution">
                <tr>
                    <td class="crest-cell">
                        @if ($crestData)
                            <img class="crest" src="data:image/jpeg;base64,{{ $crestData }}" alt="Escudo de la I.E. Tungasuca">
                        @endif
                    </td>
                    <td class="institution-copy">
                        <p class="ministry">República del Perú · Ministerio de Educación</p>
                        <p class="ministry">UGEL 04 · Comas / Carabayllo</p>
                        <p class="school">I.E. Tungasuca</p>
                        <p class="committee">Comité de Tutoría, Orientación Educativa y Convivencia Escolar</p>
                    </td>
                </tr>
            </table>

            <table class="metadata">
                <tr>
                    <td><span class="record-number">EXPEDIENTE N.° INC-{{ str_pad((string) $incidencia->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                    <td class="state-label">
                        Estado:
                        <span class="badge {{ $estadoClass }}">{{ $incidencia->estado?->nombre ?? 'Sin estado' }}</span>
                    </td>
                </tr>
            </table>

            <h1 class="title">FICHA DE REPORTE INDIVIDUAL DE INCIDENCIA</h1>

            <table class="cards">
                <tr>
                    <td class="card student-card">
                        <div class="card-title">Datos del estudiante y ubicación</div>
                        <div class="card-content">
                            <div class="student-name">
                                <span class="field-label">Estudiante implicado</span>
                                <span class="field-value">{{ $estudiante->nombre }}</span>
                                <div class="muted">DNI: {{ $estudiante->dni }}</div>
                            </div>
                            <table class="detail-grid">
                                <tr>
                                    <td>
                                        <span class="field-label">Grado y sección</span>
                                        <span class="field-value">
                                            {{ $estudiante->seccion?->grado ?? '—' }} - {{ $estudiante->seccion?->seccion ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="field-label">Tipo de aula</span>
                                        <span class="badge state-default">
                                            {{ $incidencia->tipo_aula === 'teoria' ? 'Teoría' : ($incidencia->tipo_aula === 'taller' ? 'Taller' : '—') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="field-label">Docente a cargo</span>
                                        <span class="field-value">{{ $incidencia->usuario?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="field-label">Curso / área</span>
                                        <span class="field-value">{{ $incidencia->curso?->nombre ?? '—' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>

                    <td class="card event-card">
                        <div class="card-title">Información del suceso y gravedad</div>
                        <div class="card-content">
                            <table class="detail-grid">
                                <tr>
                                    <td>
                                        <span class="field-label">Fecha</span>
                                        <span class="field-value">{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="field-label">Hora</span>
                                        <span class="field-value">
                                            {{ $incidencia->hora ? \Carbon\Carbon::parse($incidencia->hora)->format('H:i') : '—' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <span class="field-label">Gravedad clasificada</span>
                                        <span class="badge {{ $prioridadClass }}">{{ $incidencia->gravedad?->nombre ?? 'Sin clasificar' }}</span>
                                        <span class="muted">{{ $prioridad }}</span>
                                    </td>
                                </tr>
                            </table>
                            <div class="incident-types">
                                <span class="field-label">Tipos de incidencia detectada</span>
                                @forelse ($incidencia->tiposIncidencia as $tipo)
                                    <span class="type-chip">{{ $tipo->nombre }}</span>
                                @empty
                                    <span class="muted">No se especificó un tipo de incidencia.</span>
                                @endforelse
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <section class="card" style="margin-bottom: 7px;">
                <div class="card-title">Descripción del hecho reportado</div>
                <div class="card-content description">{{ $incidencia->descripcion }}</div>
            </section>

            @php
                $disk = \Illuminate\Support\Facades\Storage::disk('evidencias');
                $fotografias = $incidencia->fotografias;
            @endphp
            <section class="card photo-card">
                <div class="card-title">
                    Evidencias fotográficas
                    <span style="float: right; font-weight: normal;">
                        {{ $fotografias->count() }} {{ $fotografias->count() === 1 ? 'archivo adjunto' : 'archivos adjuntos' }}
                    </span>
                </div>
                <div class="card-content">
                    @forelse ($fotografias as $fotografia)
                        @php
                            $existeArchivo = $disk->exists($fotografia->ruta_archivo);
                            $contenido = $existeArchivo ? $disk->get($fotografia->ruta_archivo) : null;
                            $tipoMime = $contenido !== null
                                ? (new \finfo(FILEINFO_MIME_TYPE))->buffer($contenido)
                                : null;
                            $tipoPermitido = in_array($tipoMime, ['image/jpeg', 'image/png', 'image/webp'], true);
                        @endphp
                        <div class="photo-item">
                            @if ($tipoPermitido)
                                <img
                                    class="photo"
                                    src="data:{{ $tipoMime }};base64,{{ base64_encode($contenido) }}"
                                    alt="Evidencia fotográfica de la incidencia"
                                >
                                <p class="photo-caption">{{ $fotografia->nombre_original }}</p>
                            @elseif (!$existeArchivo)
                                <p class="muted">Archivo de evidencia no disponible: {{ $fotografia->nombre_original }}</p>
                            @else
                                <p class="muted">El formato de esta evidencia no se puede incluir en el PDF: {{ $fotografia->nombre_original }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="muted">No se adjuntaron evidencias fotográficas a esta incidencia.</p>
                    @endforelse
                </div>
            </section>
        </div>
    @empty
        <table class="institution">
            <tr>
                <td class="crest-cell">
                    @if ($crestData)
                        <img class="crest" src="data:image/jpeg;base64,{{ $crestData }}" alt="Escudo de la I.E. Tungasuca">
                    @endif
                </td>
                <td class="institution-copy">
                    <p class="ministry">República del Perú · Ministerio de Educación</p>
                    <p class="ministry">UGEL 04 · Comas / Carabayllo</p>
                    <p class="school">I.E. Tungasuca</p>
                    <p class="committee">Comité de Tutoría, Orientación Educativa y Convivencia Escolar</p>
                </td>
            </tr>
        </table>
        <h1 class="title">FICHA DE REPORTE INDIVIDUAL DE INCIDENCIA</h1>
        <p class="empty-message">Este alumno no tiene incidencias registradas.</p>
    @endforelse
</body>
</html>
