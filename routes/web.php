<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IncidenciaController;

// Ruta principal: redirige al login por defecto
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas públicas para el inicio de sesión
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Registro público de usuarios
Route::get('/register', [UserController::class, 'showRegister'])->name('register');
Route::post('/register', [UserController::class, 'register'])->name('register.store');

// Rutas protegidas (requieren estar autenticado)
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth.custom', 'role:Administrador,Profesor'])->group(function () {
    Route::resource('incidencias', IncidenciaController::class);
});

Route::middleware(['auth.custom', 'role:Administrador'])->group(function () {
    Route::resource('usuarios', UserController::class)->except(['show']);
    Route::post('usuarios/{usuario}/aprobar', [UserController::class, 'aprobar'])->name('usuarios.aprobar');
    Route::post('usuarios/{usuario}/rechazar', [UserController::class, 'rechazar'])->name('usuarios.rechazar');
    Route::post('usuarios/{usuario}/activar', [UserController::class, 'activar'])->name('usuarios.activar');
    Route::post('usuarios/{usuario}/desactivar', [UserController::class, 'desactivar'])->name('usuarios.desactivar');
});
