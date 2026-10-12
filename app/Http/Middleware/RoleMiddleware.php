<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, mixed ...$roles): Response
    {
        if (!session()->has('usuario_rol')) {
            return redirect()->route('login')->withErrors([
                'login_error' => 'Debe iniciar sesión para acceder al sistema.',
            ]);
        }

        $sessionRole = session('usuario_rol');
        $allowedRoles = [];

        foreach ($roles as $role) {
            foreach (explode(',', (string) $role) as $value) {
                $allowedRoles[] = trim($value);
            }
        }

        if ($allowedRoles !== [] && !in_array($sessionRole, $allowedRoles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
