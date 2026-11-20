<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm 12mm; }
        body { color: #202b3c; font-family: DejaVu Sans, sans-serif; font-size: 8px; }
        h1 { color: #183b78; font-size: 17px; margin: 0; text-align: center; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: middle; }
        .heading { margin-bottom: 5px; }
        .serial { border: 1px solid #ef4444; color: #dc2626; font-size: 10px; font-weight: bold; padding: 4px 8px; text-align: center; }
        .line { border-bottom: 1px dotted #687386; min-height: 15px; padding: 3px 4px 1px; }
        .label { font-weight: bold; white-space: nowrap; }
        .header-row td { padding: 2px 3px; }
        .note { color: #566174; font-size: 7px; }
        .section { background: #eef0f3; border-left: 3px solid #244c9a; font-size: 9px; font-weight: bold; margin: 5px 0 3px; padding: 4px 6px; }
        .section-one { background: #eef0f3; border-left: 3px solid #244c9a; font-size: 8px; margin: 5px 0 3px; }
        .section-one td { padding: 3px 5px; }
        .content-row td { padding: 2px 3px; }
        .choices { line-height: 17px; }
        .choice { white-space: nowrap; margin-right: 7px; }
        .small { font-size: 7px; }
        .aggressor-title { color: #244c9a; font-weight: bold; padding: 3px 2px 1px; }
        .aggressor { border-bottom: 1px dotted #a5aab3; padding-bottom: 2px; }
        .metadata { color: #566174; font-size: 7px; margin-top: 6px; }
    </style>
</head>
<body>
    @php
        $acta = $incidencia->acta;
        $fechaActa = $incidencia->fecha ? \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') : '—';
        $fechaSisve = $acta?->sisve_fecha ? \Carbon\Carbon::parse($acta->sisve_fecha)->format('d/m/Y') : '';
        $seccion = $incidencia->estudiante?->seccion;
        $estudianteLabel = $seccion ? $seccion->grado.' - '.$seccion->seccion : '—';
        $violencias = $acta?->tipos_violencia ?? [];
        $esViolenciaSexual = in_array('sexual', $violencias, true);
        $nombreAgredido = $acta?->agredido_nombres_iniciales;
        if (!$nombreAgredido && !$esViolenciaSexual) {
            $nombreAgredido = $incidencia->estudiante?->nombre;
        }
    @endphp

    <table class="heading">
        <tr>
            <td style="width: 18%"></td>
            <td><h1>ACTA DE INCIDENCIA</h1></td>
            <td class="serial" style="width: 18%">{{ str_pad((string) $incidencia->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
    </table>

    <table class="header-row">
        <tr>
            <td class="label">IE:</td><td class="line" style="width: 43%">{{ $acta?->ie }}</td>
            <td class="label">DRE:</td><td class="line" style="width: 23%">{{ $acta?->dre }}</td>
            <td class="label">UGEL:</td><td class="line">{{ $acta?->ugel }}</td>
        </tr>
        <tr>
            <td colspan="6">Si el caso está reportado en el Portal del SíseVe, indicar fecha
                <span class="line">{{ $fechaSisve }}</span>
                y N° Reporte <span class="line">{{ $acta?->sisve_nro_reporte }}</span>
            </td>
        </tr>
    </table>

    <table class="section-one">
        <tr>
            <td rowspan="2" style="width: 43%">
                <strong>1. Fecha actual</strong> <span class="note">(dd/mm/aaaa)</span><br>
                <span class="line">{{ $fechaActa }}</span>
            </td>
            <td><strong>Tipo de Reporte:</strong>
                <span class="choice">{{ $acta?->tipo_reporte === 'A' ? '[X]' : '[ ]' }} A. Adulto a escolar</span>
                <span class="choice">{{ $acta?->tipo_reporte === 'B' ? '[X]' : '[ ]' }} B. Entre escolares</span>
            </td>
        </tr>
        <tr><td>
            <span class="choice">{{ $acta?->tipo_reporte === 'C' ? '[X]' : '[ ]' }} C. Escolar a adulto</span>
            <span class="choice">{{ $acta?->tipo_reporte === 'D' ? '[X]' : '[ ]' }} D. Entre adultos</span>
        </td></tr>
    </table>

    <div class="section">2. Datos de la persona informante del caso:</div>
    <table class="content-row">
        <tr><td class="label">Nombres y apellidos:</td><td class="line" colspan="5">{{ $acta?->informante_nombres }}</td></tr>
        <tr>
            <td colspan="6" class="choices">
                @foreach(['Padre', 'Madre', 'Hermana/o', 'Estudiante', 'Docente', 'Otros'] as $parentesco)
                    <span class="choice">{{ $acta?->informante_parentesco === $parentesco ? '[X]' : '[ ]' }} {{ $parentesco }}</span>
                @endforeach
                Especifique: <span class="line">{{ $acta?->informante_parentesco_otro }}</span>
            </td>
        </tr>
        <tr>
            <td class="label">DNI:</td><td class="line">{{ $acta?->informante_dni }}</td>
            <td class="label">Domicilio:</td><td class="line" colspan="3">{{ $acta?->informante_domicilio }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono:</td><td class="line" colspan="2">{{ $acta?->informante_telefono }}</td>
            <td class="label">Correo electrónico:</td><td class="line" colspan="2">{{ $acta?->informante_correo }}</td>
        </tr>
    </table>

    <div class="section">3. Datos de la supuesta persona agredida:</div>
    <table class="content-row">
        <tr><td class="label">Nombres y apellidos:</td><td class="line" colspan="5">{{ $nombreAgredido ?: ($esViolenciaSexual ? 'Iniciales no registradas' : '') }}</td></tr>
        <tr>
            <td colspan="6" class="note">En caso de denuncia por violencia sexual anotar iniciales.</td>
        </tr>
        <tr>
            <td class="label">Edad:</td><td class="line">{{ $acta?->agredido_edad }}</td>
            <td class="label">Sexo:</td><td>{{ $acta?->agredido_sexo === 'M' ? '[X] M [ ] F' : ($acta?->agredido_sexo === 'F' ? '[ ] M [X] F' : '[ ] M [ ] F') }}</td>
            <td class="label">Grado/Sección:</td><td class="line">{{ $estudianteLabel }}</td>
        </tr>
        <tr>
            <td class="label">Turno:</td><td class="choices" colspan="2">
                @foreach(['M', 'T', 'N'] as $turno)<span class="choice">{{ $acta?->agredido_turno === $turno ? '[X]' : '[ ]' }} {{ $turno }}</span>@endforeach
            </td>
            <td class="label">Teléfono:</td><td class="line" colspan="2">{{ $acta?->agredido_telefono }}</td>
        </tr>
        <tr>
            <td class="label">Discapacidad:</td>
            <td colspan="5">
                {{ $acta?->agredido_discapacidad === true ? '[X] Sí [ ] No' : ($acta?->agredido_discapacidad === false ? '[ ] Sí [X] No' : '[ ] Sí [ ] No') }}
                Especifique: <span class="line">{{ $acta?->agredido_discapacidad_detalle }}</span>
            </td>
        </tr>
    </table>

    <div class="section">4. Datos del presunto agresor (Incluir más si es necesario):</div>
    @foreach(['estudiante' => ['Estudiante 1', 'Estudiante 2'], 'adulto' => ['Adulto 1', 'Adulto 2']] as $tipo => $titulos)
        @foreach($titulos as $indice => $titulo)
            @php
                $agresor = $acta?->agresores?->where('tipo', $tipo)->values()->get($indice);
            @endphp
            <div class="aggressor-title">{{ $titulo }}</div>
            <table class="content-row aggressor">
                <tr>
                    <td class="label">Nombres y apellidos:</td><td class="line" style="width:44%">{{ $agresor?->nombres }}</td>
                    <td class="label">Edad:</td><td class="line">{{ $agresor?->edad }}</td>
                    <td class="label">Sexo:</td><td>{{ $agresor?->sexo === 'M' ? '[X] M [ ] F' : ($agresor?->sexo === 'F' ? '[ ] M [X] F' : '[ ] M [ ] F') }}</td>
                </tr>
                @if($tipo === 'estudiante')
                    <tr>
                        <td colspan="6" class="choices">
                            @foreach(['mismo_grado' => 'Estudiante del mismo grado', 'mayor' => 'Estudiante mayor', 'menor' => 'Estudiante menor'] as $relacion => $label)
                                <span class="choice">{{ $agresor?->relacion_grado === $relacion ? '[X]' : '[ ]' }} {{ $label }}</span>
                            @endforeach
                            Grado/Sección: <span class="line">{{ $agresor?->grado_seccion }}</span>
                            @foreach(['M', 'T', 'N'] as $turno)<span class="choice">{{ $agresor?->turno === $turno ? '[X]' : '[ ]' }} {{ $turno }}</span>@endforeach
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="6" class="choices">
                            @foreach(['director' => 'Director', 'docente' => 'Docente', 'administrativo' => 'Administrativo', 'personal_apoyo' => 'Personal de Apoyo'] as $cargo => $label)
                                <span class="choice">{{ $agresor?->cargo === $cargo ? '[X]' : '[ ]' }} {{ $label }}</span>
                            @endforeach
                            <span class="choice">{{ $agresor?->cargo === 'otro' ? '[X]' : '[ ]' }} Otro:</span>
                            <span class="line">{{ $agresor?->cargo_otro }}</span>
                        </td>
                    </tr>
                    <tr><td class="label">DNI:</td><td class="line" colspan="5">{{ $agresor?->dni }}</td></tr>
                @endif
            </table>
        @endforeach
    @endforeach

    <div class="section">5. Especifique el tipo de violencia que sufrió el escolar:</div>
    <div class="choices">
        @foreach(['fisico' => 'Físico', 'sexual' => 'Sexual', 'verbal' => 'Verbal', 'bullying' => 'Bullying', 'ciberbullying' => 'Ciberbullying', 'otro' => 'Otro'] as $value => $label)
            <span class="choice">{{ in_array($value, $violencias, true) ? '[X]' : '[ ]' }} {{ $label }}</span>
        @endforeach
        <span class="line">{{ $acta?->violencia_otro }}</span>
    </div>
    <p class="metadata">Registrado por: {{ $incidencia->usuario?->name ?: '—' }}</p>
</body>
</html>
