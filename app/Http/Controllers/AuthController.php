<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Muestra el formulario de login
    public function showLogin()
    {
        return view('login');
    }

    // Procesa el intento de inicio de sesión
    public function login(Request $request)
    {
        // 1. Validar datos recibidos del formulario
        $request->validate([
            'dni' => ['required', 'digits:8'],
            'password' => ['required', 'string'],
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 números.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // 2. Buscar al usuario únicamente por DNI en la tabla users
        $user = User::where('dni', $request->dni)->first();

        // 3. Verificar si el usuario existe y si la contraseña coincide
        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->estado_aprobacion === 'Pendiente') {
                return back()->withErrors([
                    'login_error' => 'Tu cuenta está pendiente de aprobación por el administrador.',
                ])->withInput($request->only('dni'));
            }

            if ($user->estado_aprobacion === 'Rechazado') {
                return back()->withErrors([
                    'login_error' => 'Tu solicitud de cuenta ha sido rechazada.',
                ])->withInput($request->only('dni'));
            }

            if ($user->activo === false) {
                return back()->withErrors([
                    'login_error' => 'Tu cuenta se encuentra desactivada.',
                ])->withInput($request->only('dni'));
            }

            if ($user->estado_aprobacion !== 'Aprobado') {
                return back()->withErrors([
                    'login_error' => 'Las credenciales ingresadas son incorrectas.',
                ])->withInput($request->only('dni'));
            }

            $user->load('rol');

            // 4. Guardar datos clave en la sesión
            $request->session()->regenerate();

            $request->session()->put([
                'usuario_id' => $user->id,
                'usuario_nombre' => $user->name,
                'usuario_correo' => $user->email,
                'usuario_dni' => $user->dni,
                'usuario_rol_id' => $user->rol?->id,
                'usuario_rol' => $user->rol?->nombre_rol,
            ]);

            // Redirigir al dashboard
            return redirect()->route('dashboard');
        }

        // 5. Si falla la autenticación, volver atrás con un mensaje de error
        return back()->withErrors([
            'login_error' => 'Las credenciales ingresadas son incorrectas.',
        ])->withInput($request->only('dni'));
    }

    // Procesa el cierre de sesión
    public function logout(Request $request)
    {
        // Invalidar la sesión actual.
        $request->session()->invalidate();

        // Crear un token CSRF nuevo para la siguiente sesión.
        $request->session()->regenerateToken();

        // Redirigir al login
        return redirect()->route('login');
    }

    // Muestra la vista del Dashboard
    public function dashboard()
    {
        return view('dashboard');
    }
}
