<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;  
use App\Models\Bitacora;       
use Carbon\Carbon;             

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    */

    use AuthenticatesUsers;

    /**
     
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }


    /**
     * Se ejecuta automáticamente cuando el usuario inicia sesión.
     */
    protected function authenticated(Request $request, $user)
    {
        // 1. Actualizar la fecha de último acceso
        // (Asegúrate de que 'UltimoAccesoUsuario' esté en el $fillable de tu modelo Usuario)
        $user->update([
            'UltimoAccesoUsuario' => Carbon::now()
        ]);

        // 2. Guardar en la Bitácora
        Bitacora::registrar(
            'Acceso al Sistema',   // Acción
            'Usuarios',            // Tabla
            $user->CodUsuario,     // ID del registro
            null,                  // Nro UAC (No aplica)
            "El usuario {$user->NombreUsuario} inició sesión." // Descripción
        );
    }
}