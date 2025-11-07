<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

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
            'EstadoUsuario' => 'Activo' // Extra: solo permite usuarios activos
        ];

        // El tercer parámetro 'true' activa la funcionalidad "Recuérdame" si se envía
        if (Auth::attempt($attemptCredentials, $request->filled('remember'))) {
            // Login exitoso: Regenerar sesión por seguridad
            $request->session()->regenerate();

            // Redirigir al dashboard
            return redirect()->intended(route('dashboard'));
        }

        // 3. Si falla el login, volver atrás con un error
        return back()->withErrors([
            'NombreUsuario' => 'Las credenciales no coinciden o el usuario está inactivo.',
        ])->onlyInput('NombreUsuario');
    }

    /**
     * Cierra la sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}