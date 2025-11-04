<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers; // Importamos el trait
use App\Models\Usuario;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | Este controlador maneja la autenticación de usuarios para la aplicación
    | y los redirige a su pantalla de inicio. Estamos usando el trait
    | 'AuthenticatesUsers' pero sobrescribiendo los métodos necesarios
    | para que funcione con la base de datos de Corpointa.
    |
    */

    use AuthenticatesUsers;

    /**
     * A dónde redirigir a los usuarios después de iniciar sesión.
     *
     * @var string
     */
    protected $redirectTo = '/'; // Redirige al Dashboard (ruta definida en web.php)

    /**
     * Crea una nueva instancia del controlador.
     *
     * @return void
     */
    public function __construct()
    {
        // Aplica el middleware 'guest' (invitado) a todos los métodos
        // excepto a 'logout'. Un usuario autenticado no puede ver el login.
        $this->middleware('guest')->except('logout');
    }

    /**
     * Muestra el formulario de inicio de sesión de la aplicación.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Apunta a la vista que ya creamos
        return view('auth.login');
    }

    /**
     * Sobrescribe el método 'username' del trait.
     * Le dice a Laravel que nuestro campo de "usuario" es 'NombreUsuario'.
     *
     * @return string
     */
    public function username()
    {
        return 'NombreUsuario';
    }

    /**
     * Sobrescribe el método 'credentials' del trait.
     * Aquí es donde personalizamos los campos exactos que se usarán
     * para el intento de autenticación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $credentials = [
            'NombreUsuario'     => $request->input('NombreUsuario'),
            'ContraseniaUsuario' => $request->input('ContraseniaUsuario'),
            'EstadoUsuario'     => 'Activo' // ¡IMPORTANTE! Solo permite usuarios activos
        ];
        
        return $credentials;
    }

    /**
     * Sobrescribe el método 'attemptLogin' para usar el 'guard' web
     * y el método 'validateCredentials' personalizado.
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);

        // Usamos el 'guard' web (el predeterminado)
        // El método attempt() de Laravel se encargará de hashear y comparar
        // la 'ContraseniaUsuario' si está hasheada.
        // Si NO está hasheada, Auth::attempt fallará.
        return $this->guard()->attempt(
            $credentials, $request->filled('remember')
        );
    }
    
    /**
     * Maneja la respuesta de un intento de autenticación fallido.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Mensaje de error personalizado
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => 'Las credenciales no coinciden o el usuario está inactivo.',
            ]);
    }

    /**
     * Cierra la sesión del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirige al formulario de login después de salir
        return redirect()->route('login');
    }
}
