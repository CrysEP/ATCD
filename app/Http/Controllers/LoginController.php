<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Carbon\Carbon;
use App\Models\Bitacora;



class LoginController extends Controller
{
    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm()
    {
        // Si ya está logueado, redirige al dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    /**
     * Procesa el intento de login.
     */
    public function login(Request $request)
    {
        // 1. Validar los datos del formulario
        $credentials = $request->validate([
            'NombreUsuario' => ['required', 'string'],
            'ContraseniaUsuario' => ['required', 'string'],
        ]);

        // 2. Intentar autenticar manualmente
        // Auth::attempt espera 'password' como clave para la contraseña.
        // Mapeamos tu campo 'ContraseniaUsuario' a 'password'.
        $attemptCredentials = [
            'NombreUsuario' => $credentials['NombreUsuario'],
            'password' => $credentials['ContraseniaUsuario'],
            'EstadoUsuario' => 'Activo' 
        ];

if (Auth::attempt($attemptCredentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Obtenemos al usuario que acaba de entrar
            $user = Auth::user(); 

            // A. Actualizar última conexión (Esto ya te funciona)
            $user->update([
                'UltimoAccesoUsuario' => Carbon::now()
            ]);

            // B. Registrar en Bitácora (MÉTODO DIRECTO)
            // Usamos create directo para asegurar que tome el ID correcto ($user->CodUsuario)
            Bitacora::create([
                'Usuario_FK'  => $user->CodUsuario,
                'Accion'      => 'Iniciar Sesión',
                'Tabla'       => 'Usuarios',
                'Registro_ID' => $user->CodUsuario,
                'Nro_UAC'     => null,
                'Descripcion' => "El usuario {$user->NombreUsuario} ha ingresado al sistema.",
                'FechaHora'   => Carbon::now(),
            ]);

            // Redirigir al dashboard
            return redirect()->intended(route('dashboard'));
        }

        // 3. Si falla el login
        return back()->withErrors([
            'NombreUsuario' => 'Las credenciales no coinciden o el usuario está inactivo.',
        ])->onlyInput('NombreUsuario');
    }

    /** Cierra la sesión.*/
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

}