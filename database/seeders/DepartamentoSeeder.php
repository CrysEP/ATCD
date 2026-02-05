<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Departamento;

class DepartamentoSeeder extends Seeder
{
    public function run()
    {
        // Desactivamos la verificación de llaves foráneas para poder limpiar la tabla
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Departamento::truncate(); // Limpia la tabla antes de cargar
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Estructura Jerárquica Completa
        $organigrama = [
            'Junta Directiva' => [],
            'Presidencia' => [],
            'Servicio de Seguridad y Salud en el trabajo' => [],
            'Unidad de Auditoría Interna' => [],
            
            'Consultoría Jurídica' => [
                'Departamento de revisión y control de documentos legales' => []
            ],
            
            'Comisión de contrataciones' => [
                'Unidad de Ingeniería y apoyo técnico' => []
            ],
            
            'Gerencia General' => [
                'Unidad de Atención al Ciudadano' => [],
                'Departamento de Sistemas e Informática' => [],
                'Coordinación de Organización y Métodos' => [],
                'Coordinación de Información y Comunicación' => [],
                'Coordinación de Gestión y Control' => [],
            ],
            
            'Gerencia de Recursos Humanos' => [
                'Departamento de Personal' => [
                    'Unidad de Servicios Generales' => [],
                    'Unidad de Seguridad Interna' => [],
                ]
            ],
            
            'Gerencia de Finanzas y Presupuesto' => [
                'Departamento de Planificación y Presupuesto' => [],
                'Departamento de Compras' => [
                    'Unidad de Almacén' => []
                ],
                'Departamento de Contabilidad y Tesorería' => [
                    'Unidad de Tesorería' => [],
                    'Unidad Archivo General' => []
                ],
                'Unidad de Bienes y Materia' => [],
                'U.T.A de Responsabilidad Social' => [],
            ],
            
            'Gerencia de Construcción y Mantenimiento' => [
                'Departamento de Ejecución y Supervisión de Obras' => [],
                'Departamento de Administración de Obras' => [],
                'Departamento de Mantenimiento, Transporte y Maquinaria pesada' => [
                    'Unidad de Transporte' => [],
                    'Unidad de Maquinaria Pesada' => [
                        'Supervisión de Operadores de Maquinaria Pesada' => []
                    ],
                ],
                'Departamento de Mantenimiento y Administración de Obras' => [
                    'Unidad de Administración Directa' => [],
                    'Supervisión de Electricidad' => [],
                ]
            ],
            
            'Gerencia de Planificación y Proyectos' => [
                'Departamento de Planificación y Formulación de Proyectos' => [],
                'Departamento de Ingeniería de Proyectos' => [
                    'Unidad de Laboratorio de Suelos' => []
                ]
            ]
        ];

        // Función recursiva para guardar niveles infinitos
        $this->guardarNivel($organigrama, null);
    }

    private function guardarNivel($nodos, $padreId)
    {
        foreach ($nodos as $nombre => $hijos) {
            // Creamos el departamento
            $nuevoDepto = Departamento::create([
                'NombreDepartamento' => $nombre,
                'DepartamentoPadre_FK' => $padreId
            ]);

            // Si tiene hijos, llamamos a la función de nuevo (Recursividad)
            if (!empty($hijos)) {
                $this->guardarNivel($hijos, $nuevoDepto->CodDepartamento);
            }
        }
    }
}