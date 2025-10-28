<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StatusSolicitud;

class StatusSolicitudSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Poblar la tabla status_solicitud con los estados del flujo de trabajo
        $estados = [
            ['CodStatusSolicitud' => 1, 'NombreStatusSolicitud' => 'Pendiente'],
            ['CodStatusSolicitud' => 2, 'NombreStatusSolicitud' => 'En Revisión'],
            ['CodStatusSolicitud' => 3, 'NombreStatusSolicitud' => 'Aceptada'],
            ['CodStatusSolicitud' => 4, 'NombreStatusSolicitud' => 'Rechazada'],
            ['CodStatusSolicitud' => 5, 'NombreStatusSolicitud' => 'Respuesta Parcial'],
            ['CodStatusSolicitud' => 6, 'NombreStatusSolicitud' => 'Resuelta'],
        ];

        // Usar updateOrInsert para evitar duplicados si se ejecuta el seeder varias veces
        foreach ($estados as $estado) {
            StatusSolicitud::updateOrInsert(
                ['CodStatusSolicitud' => $estado['CodStatusSolicitud']],
                ['NombreStatusSolicitud' => $estado['NombreStatusSolicitud']]
            );
        }
    }
}
