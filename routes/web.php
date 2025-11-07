<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SolicitudController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- RUTAS PÚBLICAS ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- RUTAS PROTEGIDAS ---
Route::middleware(['auth'])->group(function () {

    // Agenda Digital (Dashboard)
    Route::get('/', [SolicitudController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [SolicitudController::class, 'index']);

    // Gestión de Solicitudes
    Route::get('/solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');

    // Rutas futuras (comentadas para evitar errores por ahora)
    // Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
});



