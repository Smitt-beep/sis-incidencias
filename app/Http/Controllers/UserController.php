<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ADMIN_CREATED_ROLE_NAMES = ['Profesor', 'Directora', 'Subdirector'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('rol')->orderBy('id')->paginate(10);

        return view('usuarios.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Rol::whereIn('nombre_rol', self::ADMIN_CREATED_ROLE_NAMES)
            ->orderBy('nombre_rol')
            ->get();
        $rolesConfigured = $roles->pluck('nombre_rol')->unique()->count() === count(self::ADMIN_CREATED_ROLE_NAMES);

        return view('usuarios.create', compact('roles', 'rolesConfigured'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $profesorRole = Rol::where('nombre_rol', 'Profesor')->first();

        if (!$profesorRole) {
            return redirect()->route('register')->withErrors([
                'register_error' => 'No es posible completar el registro porque el rol Profesor no está configurado.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'dni' => ['required', 'digits:8', Rule::unique('users', 'dni')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo debe tener un formato válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'dni' => $validated['dni'],
            'password' => $validated['password'],
            'rol_id' => $profesorRole->id,
            'estado_aprobacion' => 'Pendiente',
            'activo' => true,
        ]);

        return redirect()->route('login')->with('success', 'Cuenta registrada correctamente. Está pendiente de aprobación por el administrador.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $roles = Rol::whereIn('nombre_rol', self::ADMIN_CREATED_ROLE_NAMES)->get();
        $availableRoleNames = $roles->pluck('nombre_rol')->unique()->all();

        if (count(array_diff(self::ADMIN_CREATED_ROLE_NAMES, $availableRoleNames)) > 0) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'rol_id' => 'No se puede crear el usuario porque faltan roles permitidos en la configuración.',
                ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'dni' => ['required', 'digits:8', Rule::unique('users', 'dni')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol_id' => ['required', 'integer', Rule::in($roles->pluck('id')->all())],
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'rol_id.required' => 'Seleccione un cargo.',
            'rol_id.in' => 'El cargo seleccionado no está permitido.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'dni' => $validated['dni'],
            'password' => $validated['password'],
            'rol_id' => $validated['rol_id'],
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'dni' => ['required', 'digits:8', Rule::unique('users', 'dni')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'confirmed'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario)
    {
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }

    public function aprobar(User $usuario)
    {
        if (session('usuario_id') == $usuario->id) {
            return back()->withErrors([
                'usuario_error' => 'No puedes aprobarte a ti mismo.',
            ]);
        }

        $usuario->update([
            'estado_aprobacion' => 'Aprobado',
        ]);

        return back()->with('success', 'Usuario aprobado correctamente.');
    }

    public function rechazar(User $usuario)
    {
        if (session('usuario_id') == $usuario->id) {
            return back()->withErrors([
                'usuario_error' => 'No puedes rechazarte a ti mismo.',
            ]);
        }

        $usuario->update([
            'estado_aprobacion' => 'Rechazado',
        ]);

        return back()->with('success', 'Usuario rechazado correctamente.');
    }

    public function activar(User $usuario)
    {
        if (session('usuario_id') == $usuario->id) {
            return back()->withErrors([
                'usuario_error' => 'No puedes desactivar tu propia cuenta desde esta interfaz.',
            ]);
        }

        $usuario->update([
            'activo' => true,
        ]);

        return back()->with('success', 'Cuenta activada correctamente.');
    }

    public function desactivar(User $usuario)
    {
        if (session('usuario_id') == $usuario->id) {
            return back()->withErrors([
                'usuario_error' => 'No puedes desactivarte a ti mismo.',
            ]);
        }

        $usuario->update([
            'activo' => false,
        ]);

        return back()->with('success', 'Cuenta desactivada correctamente.');
    }
}
