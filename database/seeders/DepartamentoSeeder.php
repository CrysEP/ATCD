<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    public function run()
    {
        $departamentos = [
            'Presidencia',
            'Gerencia General',
            'Consultoría Jurídica',
            'Oficina de Auditoría Interna',
            'Unidad de Atención al Ciudadano',
            'Oficina de Gestión Humana',
            'Oficina de Administración y Finanzas',
            'Oficina de Planificación y Presupuesto',
            'Oficina de Tecnología e Informática',
            'Oficina de Comunicación e Información',
            'Gerencia de Infraestructura',
            'Gerencia de Mantenimiento',
            'Gerencia de Proyectos',
            'Gerencia de Inspección',
            'Servicios Generales',
            'Junta Directiva',
        ];

        foreach ($departamentos as $nombre) {
            DB::table('departamentos')->insertOrIgnore([
                'NombreDepartamento' => $nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}