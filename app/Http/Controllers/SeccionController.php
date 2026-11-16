<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use App\Models\AsignacionDocente;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SeccionController extends Controller
{
    public function index(): View
    {
        $grados = Seccion::query()
            ->select('grado')
            ->distinct()
            ->orderBy('grado')
            ->pluck('grado');
        $cursosPorGrado = [];

        foreach ($grados as $grado) {
            $cursosPorGrado[$grado] = [];
        }

        $cursosRegistrados = DB::table('users')
            ->join('cursos', 'users.curso_registro_id', '=', 'cursos.id')
            ->whereIn('users.grado_registro', $grados)
            ->whereIn('users.estado_aprobacion', ['Pendiente', 'Aprobado'])
            ->select(
                'users.grado_registro as grado',
                'cursos.id as curso_id',
                'cursos.nombre'
            )
            ->distinct()
            ->get();

        foreach ($cursosRegistrados as $curso) {
            $cursosPorGrado[$curso->grado][$curso->curso_id] = [
                'id' => $curso->curso_id,
                'nombre' => $curso->nombre,
                'secciones' => [],
            ];
        }

        $asignaciones = AsignacionDocente::query()
            ->with(['curso:id,nombre', 'seccion:id,grado,seccion'])
            ->whereHas('seccion', fn ($query) => $query->whereIn('grado', $grados))
            ->get(['curso_id', 'seccion_id']);

        foreach ($asignaciones as $asignacion) {
            $grado = $asignacion->seccion->grado;
            $cursoId = $asignacion->curso_id;

            if (!isset($cursosPorGrado[$grado][$cursoId])) {
                $cursosPorGrado[$grado][$cursoId] = [
                    'id' => $cursoId,
                    'nombre' => $asignacion->curso->nombre,
                    'secciones' => [],
                ];
            }

            $cursosPorGrado[$grado][$cursoId]['secciones'][$asignacion->seccion_id] = [
                'id' => $asignacion->seccion_id,
                'nombre' => $asignacion->seccion->seccion,
            ];
        }

        $seccionesPorGrado = Seccion::query()
            ->orderBy('seccion')
            ->get(['id', 'grado', 'seccion'])
            ->groupBy('grado');

        $grados = $grados->map(function (string $grado) use ($cursosPorGrado, $seccionesPorGrado): array {
            $cursos = collect($cursosPorGrado[$grado] ?? [])
                ->map(function (array $curso): array {
                    $curso['secciones'] = collect($curso['secciones'])
                        ->sortBy('nombre')
                        ->values();

                    return $curso;
                })
                ->sortBy('nombre')
                ->values();

            return [
                'nombre' => $grado,
                'cursos' => $cursos,
                'secciones' => $seccionesPorGrado->get($grado, collect()),
            ];
        });

        return view('secciones.index', compact('grados'));
    }

    public function create(): View
    {
        return view('secciones.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grado' => ['required', 'string', 'max:255'],
            'seccion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('secciones', 'seccion')
                    ->where(fn ($query) => $query->where('grado', $request->input('grado'))),
            ],
        ], [
            'grado.required' => 'El grado es obligatorio.',
            'seccion.required' => 'La sección es obligatoria.',
            'seccion.unique' => 'Ya existe una sección con ese grado y nombre.',
        ]);

        Seccion::create($validated);

        return redirect()->route('secciones.index')->with('success', 'Sección registrada correctamente.');
    }

    public function edit(Seccion $seccion): View
    {
        return view('secciones.edit', compact('seccion'));
    }

    public function update(Request $request, Seccion $seccion)
    {
        $validated = $request->validate([
            'grado' => ['required', 'string', 'max:255'],
            'seccion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('secciones', 'seccion')
                    ->where(fn ($query) => $query->where('grado', $request->input('grado')))
                    ->ignore($seccion->id),
            ],
        ], [
            'grado.required' => 'El grado es obligatorio.',
            'seccion.required' => 'La sección es obligatoria.',
            'seccion.unique' => 'Ya existe una sección con ese grado y nombre.',
        ]);

        $seccion->update($validated);

        return redirect()->route('secciones.index')->with('success', 'Sección actualizada correctamente.');
    }
}