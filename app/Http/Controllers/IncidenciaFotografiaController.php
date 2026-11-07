<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class IncidenciaFotografiaController extends Controller
{
    public function show(Request $request, Incidencia $incidencia, int $fotografia): Response
    {
        $user = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        Gate::forUser($user)->authorize('view', $incidencia);

        $evidencia = $incidencia->fotografias()
            ->whereKey($fotografia)
            ->firstOrFail();
        $disk = Storage::disk('evidencias');

        abort_unless($disk->exists($evidencia->ruta_archivo), 404);

        $contenido = $disk->get($evidencia->ruta_archivo);
        $tipoMime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contenido);
        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        abort_unless(is_string($tipoMime) && isset($tiposPermitidos[$tipoMime]), 404);

        return response($contenido, 200, [
            'Content-Type' => $tipoMime,
            'Content-Disposition' => 'inline; filename="evidencia.'.$tiposPermitidos[$tipoMime].'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, Incidencia $incidencia, int $fotografia)
    {
        $user = User::with('rol')->findOrFail($request->session()->get('usuario_id'));
        Gate::forUser($user)->authorize('update', $incidencia);

        DB::transaction(function () use ($incidencia, $fotografia, $user): void {
            $evidencia = $incidencia->fotografias()
                ->whereKey($fotografia)
                ->firstOrFail();

            $evidencia->eliminado_por = $user->getKey();

            if (!$evidencia->delete()) {
                throw new RuntimeException('No se pudo retirar la fotografía de la incidencia.');
            }
        });

        return redirect()->route('incidencias.show', $incidencia)
            ->with('success', 'Fotografía retirada correctamente.');
    }
}
