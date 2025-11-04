<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\Auth\LoginController; // Asumiendo que crearás un
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



// Rutas de Autenticación (Ejemplo básico)
// NOTA: Deberás crear el LoginController y las vistas de login.
 Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
// Route::post('login', [LoginController::class, 'login']);
// Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Rutas principales de la aplicación (protegidas por autenticación)
Route::middleware(['auth'])->group(function () {

    // Dashboard (Punto de entrada principal)
    Route::get('/', [SolicitudController::class, 'index'])->name('dashboard');

    // CRUD de Solicitudes
    Route::get('/solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    Route::get('/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitudes.show');
    
    // Ruta para el historial (solicitudes resueltas)
    Route::get('/historial', [SolicitudController::class, 'history'])->name('solicitudes.history');

    // Acciones específicas
    Route::post('/solicitudes/{id}/actualizar-estado', [SolicitudController::class, 'updateStatus'])->name('solicitudes.updateStatus');
    Route::get('/solicitudes/archivos/{id}', [SolicitudController::class, 'downloadFile'])->name('solicitudes.downloadFile');

});

// Ruta de fallback por si el usuario no está autenticado
Route::get('/login-placeholder', function () {
    return '<h2>Por favor, inicia sesión.</h2><p>Aquí iría tu formulario de login. (Ruta: /login)</p>';
})->name('login'); // Nombrar la ruta de login es importante para el middleware 'auth'
