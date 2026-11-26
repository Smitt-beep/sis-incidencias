<?php

namespace App\Console\Commands;

use App\Models\Estudiante;
use App\Models\Seccion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importa alumnos desde un Excel (.xlsx) o CSV con las columnas:
 * Estudiante, Documento, Grado, Sección.
 *
 * Por defecto SOLO SIMULA (no escribe nada). Para guardar hay que usar --confirmar.
 * No usa librerías externas: el .xlsx se lee con ZipArchive + SimpleXML (el documento
 * se lee como texto, así no se pierden los ceros a la izquierda).
 *
 * Ejemplos:
 *   php artisan estudiantes:importar "D:\datos\estudiantes.xlsx"
 *   php artisan estudiantes:importar "D:\datos\estudiantes.xlsx" --crear-secciones --confirmar
 */
class ImportarEstudiantes extends Command
{
    protected $signature = 'estudiantes:importar
        {archivo : Ruta del archivo .xlsx o .csv}
        {--confirmar : Guarda los cambios (sin esta opción solo se simula)}
        {--crear-secciones : Crea las secciones (grado + letra) que no existan}
        {--actualizar : Si el documento ya existe, actualiza su nombre y sección (por defecto se omite)}
        {--ignorar-errores : Con --confirmar, importa las filas válidas aunque otras tengan errores}
        {--errores= : Ruta del CSV de errores (por defecto storage/app/importacion_estudiantes_errores.csv)}';

    protected $description = 'Importa alumnos desde un Excel/CSV (modo simulación por defecto)';

    public function handle(): int
    {
        $ruta = (string) $this->argument('archivo');

        if (! is_file($ruta)) {
            $this->error("No se encontró el archivo: {$ruta}");

            return self::FAILURE;
        }

        try {
            $filas = $this->leerArchivo($ruta);
        } catch (\Throwable $e) {
            $this->error('No se pudo leer el archivo: ' . $e->getMessage());

            return self::FAILURE;
        }

        $columnas = $this->detectarColumnas(array_shift($filas) ?? []);
        if ($columnas === null) {
            $this->error('No se encontraron las columnas Estudiante, Documento, Grado y Sección en la primera fila.');

            return self::FAILURE;
        }

        // --- 1. Validar y normalizar cada fila ---
        $validas = [];
        $errores = [];
        $advertencias = 0;
        $documentosVistos = [];

        foreach ($filas as $indice => $fila) {
            $numeroFila = $indice + 2; // la fila 1 es el encabezado
            $celda = fn (string $campo) => $fila[$columnas[$campo]] ?? ['v' => '', 'n' => false];

            $nombre = $this->limpiar($celda('nombre')['v']);
            $documento = preg_replace('/\s+/u', '', $this->limpiar($celda('documento')['v']));
            $gradoCrudo = $this->limpiar($celda('grado')['v']);
            $seccionCruda = $this->limpiar($celda('seccion')['v']);

            // Fila totalmente vacía: se ignora
            if ($nombre === '' && $documento === '' && $gradoCrudo === '' && $seccionCruda === '') {
                continue;
            }

            $motivo = null;
            $grado = $this->normalizarGrado($gradoCrudo);
            $seccion = strtoupper($seccionCruda);

            if ($nombre === '' || mb_strlen($nombre) > 255) {
                $motivo = 'Nombre vacío o demasiado largo';
            } elseif (! preg_match('/^[A-Za-z0-9-]{6,20}$/', $documento)) {
                $motivo = 'Documento inválido (solo letras, números y guiones, de 6 a 20 caracteres)';
            } elseif ($grado === null) {
                $motivo = 'Grado no reconocido (se esperaba de 1 sec a 5 sec)';
            } elseif (! preg_match('/^[A-Z]$/', $seccion)) {
                $motivo = 'Sección no reconocida (se esperaba una letra, por ejemplo A)';
            } elseif (isset($documentosVistos[strtoupper($documento)])) {
                $motivo = 'Documento repetido en el archivo (primera vez en la fila ' . $documentosVistos[strtoupper($documento)] . ')';
            }

            if ($motivo !== null) {
                $errores[] = [
                    'fila' => $numeroFila, 'motivo' => $motivo, 'estudiante' => $nombre,
                    'documento' => $documento, 'grado' => $gradoCrudo, 'seccion' => $seccionCruda,
                ];

                continue;
            }

            // Un documento guardado como número en Excel pudo perder ceros iniciales
            if ($celda('documento')['n'] && ctype_digit($documento) && strlen($documento) === 7) {
                $advertencias++;
            }

            $documentosVistos[strtoupper($documento)] = $numeroFila;
            $validas[] = [
                'fila' => $numeroFila, 'nombre' => $nombre, 'dni' => $documento,
                'grado' => $grado, 'seccion' => $seccion,
            ];
        }

        // --- 2. Comparar con la base de datos ---
        $existentes = [];
        foreach (Estudiante::query()->get(['id', 'dni', 'seccion_id']) as $e) {
            $existentes[strtoupper((string) $e->dni)] = $e;
        }

        $seccionesBd = [];
        foreach (Seccion::query()->get(['id', 'grado', 'seccion']) as $s) {
            $seccionesBd[$s->grado . '|' . strtoupper((string) $s->seccion)] = $s->id;
        }

        $seccionesNuevas = [];
        $aCrear = [];
        $aActualizar = [];
        $omitidos = 0;
        $sinSeccion = 0;

        foreach ($validas as $fila) {
            $claveSeccion = $fila['grado'] . '|' . $fila['seccion'];
            $existeSeccion = isset($seccionesBd[$claveSeccion]);

            if (! $existeSeccion) {
                if ($this->option('crear-secciones')) {
                    $seccionesNuevas[$claveSeccion] = ['grado' => $fila['grado'], 'seccion' => $fila['seccion']];
                } else {
                    $sinSeccion++;
                    $errores[] = [
                        'fila' => $fila['fila'], 'motivo' => "La sección {$fila['grado']} {$fila['seccion']} no existe (usa --crear-secciones)",
                        'estudiante' => $fila['nombre'], 'documento' => $fila['dni'], 'grado' => $fila['grado'], 'seccion' => $fila['seccion'],
                    ];

                    continue;
                }
            }

            if (isset($existentes[strtoupper($fila['dni'])])) {
                if ($this->option('actualizar')) {
                    $aActualizar[] = $fila;
                } else {
                    $omitidos++;
                }

                continue;
            }

            $aCrear[] = $fila;
        }

        // --- 3. Resumen ---
        $this->line('');
        $this->info($this->option('confirmar') ? 'IMPORTACIÓN' : 'SIMULACIÓN (no se escribe nada en la base de datos)');
        $this->table(['Concepto', 'Cantidad'], [
            ['Filas con datos leídas', count($validas) + count(array_filter($errores, fn ($e) => ! str_contains($e['motivo'], 'no existe')))],
            ['Alumnos nuevos a crear', count($aCrear)],
            ['Ya existentes ' . ($this->option('actualizar') ? '(se actualizan)' : '(se omiten)'), $this->option('actualizar') ? count($aActualizar) : $omitidos],
            ['Secciones nuevas a crear', count($seccionesNuevas)],
            ['Filas con error', count($errores)],
        ]);

        if ($seccionesNuevas !== []) {
            ksort($seccionesNuevas);
            $this->line('Secciones que se crearían: ' . implode(', ', array_map(fn ($s) => "{$s['grado']} {$s['seccion']}", $seccionesNuevas)));
        }
        if ($advertencias > 0) {
            $this->warn("Aviso: {$advertencias} documento(s) de 7 dígitos estaban guardados como número en Excel; podrían haber perdido un cero inicial. Revísalos.");
        }

        if ($errores !== []) {
            $rutaErrores = $this->guardarErrores($errores);
            $this->warn(count($errores) . " fila(s) con error. Detalle en: {$rutaErrores}");
            foreach (array_slice($errores, 0, 10) as $e) {
                $this->line("  Fila {$e['fila']}: {$e['motivo']}");
            }
            if (count($errores) > 10) {
                $this->line('  ... y ' . (count($errores) - 10) . ' más en el archivo.');
            }
        }

        if (! $this->option('confirmar')) {
            $this->line('');
            $this->comment('Simulación terminada. Para guardar, repite el comando agregando --confirmar.');

            return self::SUCCESS;
        }

        if ($errores !== [] && ! $this->option('ignorar-errores')) {
            $this->error('Hay filas con error, así que NO se guardó nada. Corrige el archivo o usa --ignorar-errores.');

            return self::FAILURE;
        }

        // --- 4. Guardar todo o nada ---
        DB::transaction(function () use ($seccionesNuevas, &$seccionesBd, $aCrear, $aActualizar, $existentes): void {
            foreach ($seccionesNuevas as $clave => $datos) {
                $seccionesBd[$clave] = Seccion::create($datos)->id;
            }

            foreach (array_chunk($aCrear, 200) as $bloque) {
                DB::table('estudiantes')->insert(array_map(fn ($f) => [
                    'dni' => $f['dni'],
                    'nombre' => $f['nombre'],
                    'seccion_id' => $seccionesBd[$f['grado'] . '|' . $f['seccion']],
                ], $bloque));
            }

            foreach ($aActualizar as $f) {
                Estudiante::query()->whereKey($existentes[strtoupper($f['dni'])]->id)->update([
                    'nombre' => $f['nombre'],
                    'seccion_id' => $seccionesBd[$f['grado'] . '|' . $f['seccion']],
                ]);
            }
        });

        $this->info('Listo: ' . count($aCrear) . ' alumno(s) creados'
            . ($aActualizar !== [] ? ', ' . count($aActualizar) . ' actualizados' : '')
            . (count($seccionesNuevas) > 0 ? ', ' . count($seccionesNuevas) . ' sección(es) nuevas' : '') . '.');

        return self::SUCCESS;
    }

    /** "1 sec", "1", "1°", "Primero"... -> "1 sec" (formato del colegio). Devuelve null si no se reconoce. */
    private function normalizarGrado(string $grado): ?string
    {
        if (! preg_match('/^\s*([1-5])\s*(?:°|º|\.º|ro|do|er|to|vo)?\s*(?:sec\w*)?\s*$/iu', $grado, $m)) {
            return null;
        }

        return ((int) $m[1]) . ' sec';
    }

    private function limpiar(string $valor): string
    {
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $valor));
    }

    /** Busca en la primera fila qué columna es cada dato. */
    private function detectarColumnas(array $encabezado): ?array
    {
        $alias = [
            'nombre' => ['estudiante', 'nombre', 'alumno', 'apellidos y nombres'],
            'documento' => ['documento', 'dni', 'documento de identidad'],
            'grado' => ['grado'],
            'seccion' => ['seccion'],
        ];

        $columnas = [];
        foreach ($encabezado as $indice => $celda) {
            $texto = Str::lower(Str::ascii($this->limpiar($celda['v'])));
            foreach ($alias as $campo => $nombres) {
                if (! isset($columnas[$campo]) && in_array($texto, $nombres, true)) {
                    $columnas[$campo] = $indice;
                }
            }
        }

        return count($columnas) === 4 ? $columnas : null;
    }

    /** @return array<int, array<int, array{v: string, n: bool}>> */
    private function leerArchivo(string $ruta): array
    {
        return strtolower(pathinfo($ruta, PATHINFO_EXTENSION)) === 'csv'
            ? $this->leerCsv($ruta)
            : $this->leerXlsx($ruta);
    }

    private function leerCsv(string $ruta): array
    {
        $contenido = file_get_contents($ruta);
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido);
        if (! mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1'); // Excel en Windows
        }

        $primera = strtok($contenido, "\n") ?: '';
        $separador = substr_count($primera, ';') > substr_count($primera, ',') ? ';' : ',';

        $flujo = fopen('php://temp', 'r+');
        fwrite($flujo, $contenido);
        rewind($flujo);

        $filas = [];
        while (($linea = fgetcsv($flujo, 0, $separador)) !== false) {
            $filas[] = array_map(fn ($v) => ['v' => (string) $v, 'n' => false], $linea);
        }
        fclose($flujo);

        return $filas;
    }

    private function leerXlsx(string $ruta): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new \RuntimeException('el archivo no es un .xlsx válido.');
        }

        // Textos compartidos (así Excel guarda los textos, incluidos los documentos con ceros)
        $compartidas = [];
        $xmlTextos = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlTextos !== false) {
            foreach (simplexml_load_string($xmlTextos)->si as $si) {
                $texto = '';
                if (isset($si->t)) {
                    $texto = (string) $si->t;
                } else {
                    foreach ($si->r as $parte) {
                        $texto .= (string) $parte->t;
                    }
                }
                $compartidas[] = $texto;
            }
        }

        // Primera hoja del libro
        $rutaHoja = 'xl/worksheets/sheet1.xml';
        $libro = $zip->getFromName('xl/workbook.xml');
        $relaciones = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($libro !== false && $relaciones !== false) {
            $primeraHoja = simplexml_load_string($libro)->sheets->sheet[0] ?? null;
            $idRelacion = $primeraHoja ? (string) $primeraHoja->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'] : '';
            foreach (simplexml_load_string($relaciones)->Relationship as $rel) {
                if ((string) $rel['Id'] === $idRelacion) {
                    $destino = ltrim((string) $rel['Target'], '/');
                    $rutaHoja = str_starts_with($destino, 'xl/') ? $destino : 'xl/' . $destino;
                }
            }
        }

        $xmlHoja = $zip->getFromName($rutaHoja);
        $zip->close();
        if ($xmlHoja === false) {
            throw new \RuntimeException('no se encontró la hoja de datos.');
        }

        $filas = [];
        foreach (simplexml_load_string($xmlHoja)->sheetData->row as $fila) {
            $celdas = [];
            foreach ($fila->c as $c) {
                $tipo = (string) $c['t'];
                if ($tipo === 's') {
                    $valor = $compartidas[(int) $c->v] ?? '';
                } elseif ($tipo === 'inlineStr') {
                    $valor = (string) ($c->is->t ?? '');
                } else {
                    $valor = (string) $c->v;
                }
                $celdas[$this->indiceColumna((string) $c['r'])] = [
                    'v' => $valor,
                    'n' => ! in_array($tipo, ['s', 'inlineStr', 'str'], true) && $valor !== '',
                ];
            }

            // Rellena huecos para que cada fila tenga todas sus columnas
            $maximo = $celdas === [] ? -1 : max(array_keys($celdas));
            $completa = [];
            for ($i = 0; $i <= $maximo; $i++) {
                $completa[$i] = $celdas[$i] ?? ['v' => '', 'n' => false];
            }
            $filas[] = $completa;
        }

        return $filas;
    }

    /** "C12" -> 2 (columna C, empezando en 0) */
    private function indiceColumna(string $referencia): int
    {
        preg_match('/^[A-Z]+/', $referencia, $m);
        $indice = 0;
        foreach (str_split($m[0] ?? 'A') as $letra) {
            $indice = $indice * 26 + (ord($letra) - 64);
        }

        return $indice - 1;
    }

    private function guardarErrores(array $errores): string
    {
        $ruta = $this->option('errores') ?: storage_path('app/importacion_estudiantes_errores.csv');
        if (! is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0775, true);
        }

        $f = fopen($ruta, 'w');
        fwrite($f, "\xEF\xBB\xBF"); // para que Excel abra bien las tildes
        fputcsv($f, ['Fila', 'Motivo', 'Estudiante', 'Documento', 'Grado', 'Sección']);
        foreach ($errores as $e) {
            fputcsv($f, [$e['fila'], $e['motivo'], $e['estudiante'], $e['documento'], $e['grado'], $e['seccion']]);
        }
        fclose($f);

        return $ruta;
    }
}