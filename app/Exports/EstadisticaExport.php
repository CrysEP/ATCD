<?php

namespace App\Exports;

use App\Models\Municipio;
use App\Models\TipoEnte;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadisticaExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $mes;
    protected $anio;

    public function __construct($mes, $anio)
    {
        $this->mes = $mes;
        $this->anio = $anio;
    }

    /**
     * Matriz General (Todas las solicitudes activas)
     */
    private function generarMatriz($anio, $mes = null, $trimestre = null)
    {
        $query = DB::table('solicitudes')
            ->join('personas', 'solicitudes.CedulaPersona_FK', '=', 'personas.CedulaPersona')
            ->join('parroquias', 'personas.ParroquiaPersona_FK', '=', 'parroquias.CodParroquia')
            ->join('municipios', 'parroquias.Municipio_FK', '=', 'municipios.CodMunicipio')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
            ->join('tipos_entes', 'relacion_correspondencia.TipoEnte_FK', '=', 'tipos_entes.CodTipoEnte')
            ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7) // No anuladas
            ->whereYear('FechaSolicitud', $anio);

        if ($mes) { $query->whereMonth('FechaSolicitud', $mes); }
        if ($trimestre) { $query->whereRaw('QUARTER(FechaSolicitud) = ?', [$trimestre]); }

        $data = $query->select('municipios.CodMunicipio', 'tipos_entes.CodTipoEnte', DB::raw('count(*) as total'))
            ->groupBy('municipios.CodMunicipio', 'tipos_entes.CodTipoEnte')->get();

        $matriz = [];
        foreach ($data as $row) {
            $matriz[$row->CodMunicipio][$row->CodTipoEnte] = $row->total;
        }
        return $matriz;
    }

    /**
     * NUEVO: Matriz filtrada por Estatus (Pendiente vs Procesado)
     */
    private function generarMatrizStatus($anio, $mes, $tipoStatus)
    {
        $query = DB::table('solicitudes')
            ->join('personas', 'solicitudes.CedulaPersona_FK', '=', 'personas.CedulaPersona')
            ->join('parroquias', 'personas.ParroquiaPersona_FK', '=', 'parroquias.CodParroquia')
            ->join('municipios', 'parroquias.Municipio_FK', '=', 'municipios.CodMunicipio')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
            ->join('tipos_entes', 'relacion_correspondencia.TipoEnte_FK', '=', 'tipos_entes.CodTipoEnte')
            ->whereYear('FechaSolicitud', $anio)
            ->whereMonth('FechaSolicitud', $mes);

        if ($tipoStatus === 'pendiente') {
            // Solo Pendientes (Status 1)
            $query->where('relacion_correspondencia.StatusSolicitud_FK', 1);
        } else {
            // Procesadas (Ni pendiente ni anulada)
            $query->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 1)
                  ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7);
        }

        $data = $query->select('municipios.CodMunicipio', 'tipos_entes.CodTipoEnte', DB::raw('count(*) as total'))
            ->groupBy('municipios.CodMunicipio', 'tipos_entes.CodTipoEnte')->get();

        $matriz = [];
        foreach ($data as $row) {
            $matriz[$row->CodMunicipio][$row->CodTipoEnte] = $row->total;
        }
        return $matriz;
    }

    public function view(): View
    {
        $municipios = Municipio::orderBy('NombreMunicipio')->get();
        $entes = TipoEnte::orderBy('NombreEnte')->get();

        // 1. Datos Generales (Ya existentes)
        $matrizMensual = $this->generarMatriz($this->anio, $this->mes);
        
        // 2. NUEVO: Datos Desglosados por Estatus
        $matrizPendientes = $this->generarMatrizStatus($this->anio, $this->mes, 'pendiente');
        $matrizProcesadas = $this->generarMatrizStatus($this->anio, $this->mes, 'procesada');

        // 3. Trimestrales y Anuales (Ya existentes)
        $matricesTrimestrales = [];
        for ($i = 1; $i <= 4; $i++) {
            $matricesTrimestrales[$i] = $this->generarMatriz($this->anio, null, $i);
        }
        $matrizAnual = $this->generarMatriz($this->anio);

        $nombreMes = Carbon::create()->month($this->mes)->locale('es')->monthName;

        return view('estadisticas.export_excel', [
            'municipios'           => $municipios,
            'entes'                => $entes,
            'nombreMes'            => $nombreMes,
            'anio'                 => $this->anio,
            'matrizMensual'        => $matrizMensual,
            'matrizPendientes'     => $matrizPendientes,
            'matrizProcesadas'     => $matrizProcesadas, 
            'matricesTrimestrales' => $matricesTrimestrales,
            'matrizAnual'          => $matrizAnual
        ]);
    }

    public function title(): string
    {
        return 'Reporte ' . $this->mes . '-' . $this->anio;
    }
}