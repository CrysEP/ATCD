<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\EstadisticaExport;     

class EstadisticaController extends Controller
{
    public function index(Request $request)
    {
        // Filtro de fecha (Por defecto: Hoy)
        $anio = $request->get('anio', Carbon::now()->year);
        $mes = $request->get('mes', Carbon::now()->month);
        
        // Convertir número de mes a nombre para la vista
        $nombreMes = Carbon::create()->month($mes)->locale('es')->monthName;
        $solicitudesHoy = Solicitud::whereDate('FechaSolicitud', Carbon::now())->count();

      
        $totalSolicitudes = Solicitud::whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', '!=', 7); // No anuladas
        })->whereMonth('FechaSolicitud', $mes)->whereYear('FechaSolicitud', $anio)->count();

        // 2. Solicitudes por Estatus (DEL MES SELECCIONADO)
        $porEstatus = DB::table('solicitudes')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
            ->join('status_solicitudes', 'relacion_correspondencia.StatusSolicitud_FK', '=', 'status_solicitudes.CodStatusSolicitud')
            ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7) // No anuladas
            ->whereMonth('FechaSolicitud', $mes)
            ->whereYear('FechaSolicitud', $anio)
            ->select('status_solicitudes.NombreStatusSolicitud', DB::raw('count(*) as total'))
            ->groupBy('status_solicitudes.NombreStatusSolicitud')
            ->get();

        // 3. Solicitudes por Tipo (Global o Mes? Dejaré Mes para consistencia)
        $porTipo = Solicitud::whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', '!=', 7);
        })
        ->whereMonth('FechaSolicitud', $mes)->whereYear('FechaSolicitud', $anio)
        ->select('TipoSolicitudPlanilla', DB::raw('count(*) as total'))
        ->groupBy('TipoSolicitudPlanilla')
        ->get();

        // 4. Histórico por Mes (Este SÍ debe ser de todo el año para ver la curva)
        $porMes = Solicitud::select(
                DB::raw('MONTH(FechaSolicitud) as mes'), 
                DB::raw('count(*) as total')
            )
            ->whereHas('correspondencia', function ($q) {
                $q->where('StatusSolicitud_FK', '!=', 7);
            })
            ->whereYear('FechaSolicitud', $anio)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // 5. Municipios (DEL MES SELECCIONADO)
        $municipiosMes = DB::table('solicitudes')
            ->join('personas', 'solicitudes.CedulaPersona_FK', '=', 'personas.CedulaPersona')
            ->join('parroquias', 'personas.ParroquiaPersona_FK', '=', 'parroquias.CodParroquia')
            ->join('municipios', 'parroquias.Municipio_FK', '=', 'municipios.CodMunicipio')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK') // Join extra para filtrar anuladas
            ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7) // No anuladas
            ->whereMonth('FechaSolicitud', $mes)
            ->whereYear('FechaSolicitud', $anio)
            ->select('municipios.NombreMunicipio', DB::raw('count(*) as total'))
            ->groupBy('municipios.NombreMunicipio')
            ->orderByDesc('total')
            ->limit(10) 
            ->get();

        // 6. Entes (DEL MES SELECCIONADO)
        $entesMes = DB::table('solicitudes')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
            ->join('tipos_entes', 'relacion_correspondencia.TipoEnte_FK', '=', 'tipos_entes.CodTipoEnte')
            ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7) // No anuladas
            ->whereMonth('FechaSolicitud', $mes)
            ->whereYear('FechaSolicitud', $anio)
            ->select('tipos_entes.NombreEnte', DB::raw('count(*) as total'))
            ->groupBy('tipos_entes.NombreEnte')
            ->orderByDesc('total')
            ->get();

            

        // 7. Calcular el "Ente Top"
        $topEnteNombre = 'N/A';
        $topEnteTotal = 0;
        if ($entesMes->isNotEmpty()) {
            $topEnteNombre = $entesMes->first()->NombreEnte;
            $topEnteTotal = $entesMes->first()->total;
        }

        // --- PREPARACIÓN DE DATOS PARA JS ---
        $labelsEstatus = $porEstatus->pluck('NombreStatusSolicitud');
        $dataEstatus = $porEstatus->pluck('total');

        $labelsTipo = $porTipo->pluck('TipoSolicitudPlanilla');
        $dataTipo = $porTipo->pluck('total');

        $labelsMunMes = $municipiosMes->pluck('NombreMunicipio');
        $dataMunMes = $municipiosMes->pluck('total');

        $labelsEnteMes = $entesMes->pluck('NombreEnte');
        $dataEnteMes = $entesMes->pluck('total');

        $dataMeses = array_fill(0, 12, 0);
        foreach ($porMes as $registro) {
            $dataMeses[$registro->mes - 1] = $registro->total;
        }



// 8. Solicitudes por Nivel de Urgencia
        $urgenciaData = Solicitud::whereHas('correspondencia', function ($q) {
                $q->where('StatusSolicitud_FK', '!=', 7);
            })
            ->whereMonth('FechaSolicitud', $mes)
            ->whereYear('FechaSolicitud', $anio)
            ->select('NivelUrgencia', DB::raw('count(*) as total'))
            ->groupBy('NivelUrgencia')
            ->get();

        // 9. Solicitudes por Tipo de Solicitante (Personal, Institucional, etc.)
        $tipoSolicitanteData = Solicitud::whereHas('correspondencia', function ($q) {
                $q->where('StatusSolicitud_FK', '!=', 7);
            })
            ->whereMonth('FechaSolicitud', $mes)
            ->whereYear('FechaSolicitud', $anio)
            ->select('TipoSolicitante', DB::raw('count(*) as total'))
            ->groupBy('TipoSolicitante')
            ->get();

        // --- PREPARAR DATOS PARA LA VISTA ---
        
        // Urgencia
        $labelsUrgencia = $urgenciaData->pluck('NivelUrgencia');
        $dataUrgencia = $urgenciaData->pluck('total');

        // Tipo Solicitante
        $labelsTipoSolicitante = $tipoSolicitanteData->pluck('TipoSolicitante');
        $dataTipoSolicitante = $tipoSolicitanteData->pluck('total');



$municipiosAnio = DB::table('solicitudes')
            ->join('personas', 'solicitudes.CedulaPersona_FK', '=', 'personas.CedulaPersona')
            ->join('parroquias', 'personas.ParroquiaPersona_FK', '=', 'parroquias.CodParroquia')
            ->join('municipios', 'parroquias.Municipio_FK', '=', 'municipios.CodMunicipio')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
            ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7) // No anuladas
            ->whereYear('FechaSolicitud', $anio) // <--- SOLO FILTRO DE AÑO
            ->select('municipios.NombreMunicipio', DB::raw('count(*) as total'))
            ->groupBy('municipios.NombreMunicipio')
            ->orderByDesc('total')
            ->limit(10) // Top 10 para no saturar el gráfico
            ->get();

        // Preparar datos para JS
        $labelsMunAnio = $municipiosAnio->pluck('NombreMunicipio');
        $dataMunAnio = $municipiosAnio->pluck('total');



// 1. Total Denuncias
        $denunciasMes = Solicitud::whereHas('correspondencia', function ($q) {
                $q->where('StatusSolicitud_FK', '!=', 7); // No anuladas
            })
            ->where('TipoSolicitudPlanilla', 'Denuncia')
            ->whereMonth('FechaSolicitud', $mes)
            ->whereYear('FechaSolicitud', $anio)
            ->count();

        // 2. Total Quejas y Sugerencias
        $quejasMes = Solicitud::whereHas('correspondencia', function ($q) {
                $q->where('StatusSolicitud_FK', '!=', 7);
            })
            ->where('TipoSolicitudPlanilla', 'Quejas, reclamos o sugerencias')
            ->whereMonth('FechaSolicitud', $mes)
            ->whereYear('FechaSolicitud', $anio)
            ->count();




        return view('estadisticas.index', compact(
            'solicitudesHoy',
            'totalSolicitudes', 'nombreMes', 'mes', 'anio', 'topEnteNombre', 'topEnteTotal',
            'labelsEstatus', 'dataEstatus',
            'labelsTipo', 'dataTipo',
            'labelsMunMes', 'dataMunMes',
            'labelsEnteMes', 'dataEnteMes',
            'dataMeses',
            'labelsMunAnio', 'dataMunAnio',
            'denunciasMes', 'quejasMes',
            'labelsUrgencia', 'dataUrgencia',
            'labelsTipoSolicitante', 'dataTipoSolicitante'
        ));
    }

    /**
     * Descargar Excel del mes seleccionado*/
    public function exportarExcel(Request $request)
    {
        $mes = $request->mes;
        $anio = $request->anio;
        
        return Excel::download(new EstadisticaExport($mes, $anio), "Reporte_Estadistico_{$mes}_{$anio}.xlsx");
    }


public function dataCalendario()
{
    // 1. Agrupar solicitudes por día
 $eventos = [];

    // --- GRUPO 1: SOLICITUDES PROCESADAS (Van al Historial) ---
    $procesadas = Solicitud::join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
        ->select(
            DB::raw('DATE(solicitudes.FechaSolicitud) as date'),
            DB::raw('count(*) as count')
        )
        ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 1) // No Pendientes
        ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7) // No Anuladas
        ->groupBy('date')
        ->get();

    foreach ($procesadas as $row) {
        // Semáforo de carga para lo procesado
        $color = '#198754'; // Verde (Bajo: 1-5)
        if ($row->count > 5) $color = '#fd7e14'; // Naranja (Medio: 6-15)
        if ($row->count > 15) $color = '#dc3545'; // Rojo (Alto: 16+)

        $eventos[] = [
            'title' => $row->count . ' Procesadas',
            'start' => $row->date,
            'color' => $color,
            'textColor' => 'white',
            // URL: Lleva al Historial
            'url' => route('solicitudes.history', [
                'fecha_desde' => $row->date, 
                'fecha_hasta' => $row->date
            ])
        ];
    }

    // --- GRUPO 2: SOLICITUDES PENDIENTES (Van a la Agenda/Dashboard) ---
    $pendientes = Solicitud::join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
        ->select(
            DB::raw('DATE(solicitudes.FechaSolicitud) as date'),
            DB::raw('count(*) as count')
        )
        ->where('relacion_correspondencia.StatusSolicitud_FK', '=', 1) 
        ->groupBy('date')
        ->get();

    foreach ($pendientes as $row) {
        $eventos[] = [
            'title' => '⚠️ ' . $row->count . ' Pendientes', 
            'start' => $row->date,
            'color' => '#0d6efd', 
            'textColor' => 'white',
            // URL: Lleva al Dashboard (Agenda)
            'url' => route('dashboard', [
                'fecha_desde' => $row->date, 
                'fecha_hasta' => $row->date
            ])
        ];
    }
    

    return response()->json($eventos);
}





}