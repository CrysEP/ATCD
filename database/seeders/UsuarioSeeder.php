<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;


class UsuarioSeeder extends Seeder // ¡Faltaba envolverlo en la clase!
{
    public function run(): void
    {
        // Usamos 'Usuario' directamente gracias al 'use' de arriba
        Usuario::create([
            'NombreUsuario' => 'admin',
            'ContraseniaUsuario' => Hash::make('tu-contraseña-segura'),
            'RolUsuario' => 'Administrador',
            'EstadoUsuario' => 'Activo',
            'CedulaPersonaUsuario_FK' => 'V-12345678'
        ]);
    }
}



//No se si está bien, miau