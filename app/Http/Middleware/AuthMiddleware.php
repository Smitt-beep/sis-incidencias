<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si NO existe la variable usuario_id en la sesión
        if (! session()->has('usuario_id')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Inicia sesión para continuar.',
                ], 401);
            }

            return redirect()->route('login')->withErrors([
                'login_error' => 'Debe iniciar sesión para acceder al sistema.',
            ]);
        }

        $user = User::find($request->session()->get('usuario_id'));

        if (! $user || $user->estado_aprobacion !== 'Aprobado' || $user->activo !== true) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu sesión venció o ya no está autorizada. Inicia sesión nuevamente.',
                ], 401);
            }

            return redirect()->route('login')->withErrors([
                'login_error' => 'Tu sesión ya no está autorizada. Inicia sesión nuevamente.',
            ]);
        }

        return $next($request);
    }
}
