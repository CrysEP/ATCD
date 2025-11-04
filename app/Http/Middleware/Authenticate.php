<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Obtiene la ruta a la que el usuario debe ser redirigido
     * cuando no está autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Si la solicitud no espera una respuesta JSON (es decir, es una
        // solicitud web normal desde el navegador)...
        if (! $request->expectsJson()) {
            
            // ...redirige al usuario a la RUTA que se LLAMA 'login'.
            // Esto conectará con la ruta que definimos en routes/web.php
            return route('login');
        }
        
        // Si es una solicitud de API (JSON), simplemente devolverá
        // un error 401, pero para esta app, la línea de arriba es la clave.
    }
}
