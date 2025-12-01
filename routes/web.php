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
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- RUTAS PROTEGIDAS (PARA TODOS LOS USUARIOS LOGUEADOS) ---
Route::middleware(['auth'])->group(function () {
    // Agenda Digital (Dashboard)
    Route::get('/', [SolicitudController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [SolicitudController::class, 'index']);

    // Gestión de Solicitudes
    Route::get('/solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');

  // Ruta para ver el historial (DEBE IR PRIMERO)
    Route::get('/solicitudes/historial', [SolicitudController::class, 'history'])->name('solicitudes.history');
   
   // --- NUEVA RUTA ---
    Route::get('/solicitudes/anuladas', [SolicitudController::class, 'anuladas'])->name('solicitudes.anuladas');
     
    // Ver Detalles (DEBE IR DESPUÉS DE LAS RUTAS ESTÁTICAS)
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');

    // Descargar Archivos
    Route::get('/solicitudes/archivo/{id}/descargar', [SolicitudController::class, 'downloadFile'])->name('solicitudes.downloadFile');

    Route::get('/solicitudes/{id}/editar', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
    Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');


    // Ruta para anular (eliminación lógica)
    Route::put('/solicitudes/{id}/anular', [SolicitudController::class, 'anular'])->name('solicitudes.anular');

    Route::put('/solicitudes/{id}/restaurar', [SolicitudController::class, 'restaurar'])->name('solicitudes.restaurar');

    Route::get('/solicitudes/{id}/pdf', [SolicitudController::class, 'generarPDF'])->name('solicitudes.pdf');

    Route::post('/solicitudes/exportar-zip', [SolicitudController::class, 'exportarZip'])->name('solicitudes.exportarZip');
});


// --- RUTAS SOLO PARA ADMINS ---
Route::middleware(['auth', 'can:es-admin'])->group(function () {
    
    // Ruta para que el formulario de 'show' actualice el estado
    Route::post('/solicitudes/{id}/actualizar-estado', [SolicitudController::class, 'updateStatus'])->name('solicitudes.updateStatus');

    // Ruta para editar los datos del flujo (Nro Oficio, etc.)
    Route::post('/solicitudes/{id}/actualizar-flujo', [SolicitudController::class, 'updateFlujo'])->name('solicitudes.updateFlujo');

    
    // (Aquí irán futuras rutas de reportes)

});