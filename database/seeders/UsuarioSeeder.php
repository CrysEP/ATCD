<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;


\App\Models\Usuario::create([
    'NombreUsuario' => 'admin',
    'ContraseniaUsuario' => \Illuminate\Support\Facades\Hash::make('tu-contraseña-segura'),
    'RolUsuario' => 'Administrador',
    'EstadoUsuario' => 'Activo',
    'CedulaPersonaUsuario_FK' => 'V-12345678' // Cédula de una persona que exista
]);




//No se si está bien