<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeccionController extends Controller
{
    public function index(): View
    {
        $secciones = Seccion::query()
            ->orderBy('grado')
            ->orderBy('seccion')
            ->paginate(15);

        return view('secciones.index', compact('secciones'));
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