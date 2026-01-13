<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\EstadisticaController;

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
    // ¡IMPORTANTE! Estas deben ir ANTES de cualquier ruta con {id}
    Route::get('/solicitudes/historial', [SolicitudController::class, 'history'])->name('solicitudes.history');
    Route::get('/solicitudes/historial/exportar', [SolicitudController::class, 'exportarHistorialExcel'])->name('solicitudes.exportarHistorial');
    Route::get('/solicitudes/historial/pdf', [SolicitudController::class, 'exportarHistorialPdf'])->name('solicitudes.exportarHistorialPdf'); // <--- AQUI ESTABA EL CONFLICTO
    Route::get('/solicitudes/anuladas', [SolicitudController::class, 'anuladas'])->name('solicitudes.anuladas');
    Route::post('/solicitudes/exportar-zip', [SolicitudController::class, 'exportarZip'])->name('solicitudes.exportarZip');

    // 4. CUARTO: Rutas con {id} (Variables)
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::get('/solicitudes/{id}/editar', [SolicitudController::class, 'edit'])->name('solicitudes.edit');
    Route::put('/solicitudes/{id}', [SolicitudController::class, 'update'])->name('solicitudes.update');
    Route::get('/solicitudes/{id}/pdf', [SolicitudController::class, 'generarPDF'])->name('solicitudes.pdf'); // <--- ESTA ERA LA CULPABLE
    
    //Resto de tus rutas (ticket, archivos, anular, restaurar, estadísticas)
    Route::put('/solicitudes/{id}/anular', [SolicitudController::class, 'anular'])->name('solicitudes.anular');
    Route::put('/solicitudes/{id}/restaurar', [SolicitudController::class, 'restaurar'])->name('solicitudes.restaurar');
    Route::get('/solicitudes/{id}/ticket', [App\Http\Controllers\SolicitudController::class, 'generarTicket'])->name('solicitudes.ticket');
    Route::post('/solicitudes/{id}/subir-archivo', [App\Http\Controllers\SolicitudController::class, 'subirArchivo'])->name('solicitudes.subirArchivo');
    Route::get('/solicitudes/archivo/{id}/descargar', [SolicitudController::class, 'downloadFile'])->name('solicitudes.downloadFile');
    Route::get('/solicitudes/archivo/{id}/ver', [App\Http\Controllers\SolicitudController::class, 'verArchivo'])->name('solicitudes.verArchivo');
    Route::delete('/archivos/{id}/eliminar', [App\Http\Controllers\SolicitudController::class, 'eliminarArchivo'])->name('archivos.eliminar');
    
    //Rutas de estadísticas 
    Route::get('/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');
    Route::get('/estadisticas/excel', [EstadisticaController::class, 'exportarExcel'])->name('estadisticas.excel');
    Route::get('/estadisticas/data-calendario', [EstadisticaController::class, 'dataCalendario'])->name('estadisticas.dataCalendario');
});


// --- RUTAS SOLO PARA ADMINS ---
Route::middleware(['auth', 'can:es-admin'])->group(function () {
    
    // Ruta para que el formulario de 'show' actualice el estado
    Route::post('/solicitudes/{id}/actualizar-estado', [SolicitudController::class, 'updateStatus'])->name('solicitudes.updateStatus');

    // Ruta para editar los datos del flujo (Nro Oficio, etc.)
    Route::post('/solicitudes/{id}/actualizar-flujo', [SolicitudController::class, 'updateFlujo'])->name('solicitudes.updateFlujo');

    
    // (Aquí irán futuras rutas de reportes)

});