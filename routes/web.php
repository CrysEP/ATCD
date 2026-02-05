<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\AdminUsuarioController;

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
    
    // 1. PRIMERO: Dashboard
    Route::get('/', [SolicitudController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [SolicitudController::class, 'index']);

    // 2. SEGUNDO: Rutas de Creación y Guardado
    Route::get('/solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');

    // 3. TERCERO: Rutas ESPECÍFICAS (Historial, Exportar, Anuladas)
    Route::get('/solicitudes/historial', [SolicitudController::class, 'history'])->name('solicitudes.history');
    Route::get('/solicitudes/historial/exportar', [SolicitudController::class, 'exportarHistorialExcel'])->name('solicitudes.exportarHistorial');
    Route::get('/solicitudes/historial/pdf', [SolicitudController::class, 'exportarHistorialPdf'])->name('solicitudes.exportarHistorialPdf'); 
    Route::get('/solicitudes/anuladas', [SolicitudController::class, 'anuladas'])->name('solicitudes.anuladas');
    Route::post('/solicitudes/exportar-zip', [SolicitudController::class, 'exportarZip'])->name('solicitudes.exportarZip');

    // 4. CUARTO: Rutas con {id} (Variables)
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::get('/solicitudes/{id}/editar', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
    Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');
    Route::get('/solicitudes/{id}/pdf', [SolicitudController::class, 'generarPDF'])->name('solicitudes.pdf'); 
    
    // Resto de tus rutas
    Route::put('/solicitudes/{id}/anular', [SolicitudController::class, 'anular'])->name('solicitudes.anular');
    Route::put('/solicitudes/{id}/restaurar', [SolicitudController::class, 'restaurar'])->name('solicitudes.restaurar');
    Route::get('/solicitudes/{id}/ticket', [App\Http\Controllers\SolicitudController::class, 'generarTicket'])->name('solicitudes.ticket');
    Route::post('/solicitudes/{id}/subir-archivo', [App\Http\Controllers\SolicitudController::class, 'subirArchivo'])->name('solicitudes.subirArchivo');
    Route::get('/solicitudes/archivo/{id}/descargar', [SolicitudController::class, 'downloadFile'])->name('solicitudes.downloadFile');
    Route::get('/solicitudes/archivo/{id}/ver', [App\Http\Controllers\SolicitudController::class, 'verArchivo'])->name('solicitudes.verArchivo');
    Route::delete('/archivos/{id}/eliminar', [App\Http\Controllers\SolicitudController::class, 'eliminarArchivo'])->name('archivos.eliminar');
    
    // Rutas de estadísticas 
    Route::get('/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');
    Route::get('/estadisticas/excel', [EstadisticaController::class, 'exportarExcel'])->name('estadisticas.excel');
    Route::get('/estadisticas/data-calendario', [EstadisticaController::class, 'dataCalendario'])->name('estadisticas.dataCalendario');
});


// --- RUTAS SOLO PARA ADMINS ---
Route::middleware(['auth', 'can:es-admin'])->group(function () {

    // 1. Gestión de Usuarios (CRUD Completo)
    Route::get('/admin/usuarios', [AdminUsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::get('/admin/usuarios/crear', [AdminUsuarioController::class, 'create'])->name('admin.usuarios.create');
    Route::post('/admin/usuarios', [AdminUsuarioController::class, 'store'])->name('admin.usuarios.store');
    
    Route::get('/admin/usuarios/{id}/editar', [AdminUsuarioController::class, 'edit'])->name('admin.usuarios.edit');
    Route::put('/admin/usuarios/{id}', [AdminUsuarioController::class, 'update'])->name('admin.usuarios.update');

    // 2. Gestión de Solicitudes (Acciones Admin)
    Route::post('/solicitudes/{id}/actualizar-estado', [SolicitudController::class, 'updateStatus'])->name('solicitudes.updateStatus');
    Route::post('/solicitudes/{id}/actualizar-flujo', [SolicitudController::class, 'updateFlujo'])->name('solicitudes.updateFlujo');

});