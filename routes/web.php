<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotMensajeController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\IncidenciaFotografiaController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    
    // Ruta POST original para procesar los mensajes del chatbot
    Route::post('/chatbot/mensajes', [ChatbotMensajeController::class, 'store'])->name('chatbot.mensajes.store');
    
    // Ruta GET de respaldo para redirigir si entran directamente por la URL
    Route::get('/chatbot/mensajes', function () {
        return redirect()->route('dashboard');
    });
});

Route::middleware(['auth.custom', 'role:Profesor'])->group(function () {
    Route::get('estudiantes', [EstudianteController::class, 'index'])->name('estudiantes.index');
    Route::post('estudiantes', [EstudianteController::class, 'store'])->name('estudiantes.store');
    Route::get('estudiantes/{estudiante}/reporte', [EstudianteController::class, 'reporteSeleccionar'])
        ->name('estudiantes.reporte.seleccionar');
    Route::post('estudiantes/{estudiante}/reporte', [EstudianteController::class, 'reportePdf'])
        ->name('estudiantes.reporte.pdf.post');
    Route::get('estudiantes/{estudiante}/reporte/todas', [EstudianteController::class, 'reportePdfTodas'])
        ->name('estudiantes.reporte.pdf');
});

Route::middleware(['auth.custom'])->group(function () {
    Route::get('incidencias/urgencias', [IncidenciaController::class, 'urgencias'])
        ->middleware('role:Administrador,Directora,Subdirector')
        ->name('incidencias.urgencias');
    Route::get('incidencias', [IncidenciaController::class, 'index'])
        ->middleware('role:Administrador,Profesor,Directora,Subdirector')
        ->name('incidencias.index');
    Route::get('incidencias/create', [IncidenciaController::class, 'create'])
        ->middleware('role:Administrador,Profesor')
        ->name('incidencias.create');
    Route::post('incidencias', [IncidenciaController::class, 'store'])
        ->middleware('role:Administrador,Profesor')
        ->name('incidencias.store');
    Route::get('incidencias/{incidencia}/edit', [IncidenciaController::class, 'edit'])
        ->middleware('role:Administrador,Profesor')
        ->name('incidencias.edit');
    Route::get('incidencias/{incidencia}/fotografias/{fotografia}', [IncidenciaFotografiaController::class, 'show'])
        ->middleware('role:Administrador,Profesor,Directora,Subdirector')
        ->name('incidencias.fotografias.show');
    Route::delete('incidencias/{incidencia}/fotografias/{fotografia}', [IncidenciaFotografiaController::class, 'destroy'])
        ->middleware('role:Administrador,Profesor')
        ->name('incidencias.fotografias.destroy');
    Route::match(['put', 'patch'], 'incidencias/{incidencia}', [IncidenciaController::class, 'update'])
        ->middleware('role:Administrador,Profesor')
        ->name('incidencias.update');
    Route::delete('incidencias/{incidencia}', [IncidenciaController::class, 'destroy'])
        ->middleware('role:Administrador,Directora')
        ->name('incidencias.destroy');
    Route::get('incidencias/{incidencia}', [IncidenciaController::class, 'show'])
        ->middleware('role:Administrador,Profesor,Directora,Subdirector')
        ->name('incidencias.show');
});

Route::middleware(['auth.custom', 'role:Administrador'])->group(function () {
    Route::resource('usuarios', UserController::class)->except(['show']);
    Route::post('usuarios/{usuario}/aprobar', [UserController::class, 'aprobar'])->name('usuarios.aprobar');
    Route::post('usuarios/{usuario}/rechazar', [UserController::class, 'rechazar'])->name('usuarios.rechazar');
    Route::post('usuarios/{usuario}/activar', [UserController::class, 'activar'])->name('usuarios.activar');
    Route::post('usuarios/{usuario}/desactivar', [UserController::class, 'desactivar'])->name('usuarios.desactivar');
});

Route::middleware(['auth.custom', 'role:Administrador,Directora,Subdirector'])->group(function () {
    Route::resource('secciones', SeccionController::class)
        ->except(['show', 'destroy'])
        ->parameters(['secciones' => 'seccion']);
});