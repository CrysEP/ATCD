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

    public function view(): View
    {
        // 1. Municipios
        $municipios = Municipio::orderBy('NombreMunicipio')->get();
        
        $conteosMun = DB::table('solicitudes')
            ->join('personas', 'solicitudes.CedulaPersona_FK', '=', 'personas.CedulaPersona')
            ->join('parroquias', 'personas.ParroquiaPersona_FK', '=', 'parroquias.CodParroquia')
            ->join('municipios', 'parroquias.Municipio_FK', '=', 'municipios.CodMunicipio')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
            ->whereMonth('FechaSolicitud', $this->mes)
            ->whereYear('FechaSolicitud', $this->anio)
            ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7) 
            ->select('municipios.CodMunicipio', DB::raw('count(*) as total'))
            ->groupBy('municipios.CodMunicipio')
            ->pluck('total', 'CodMunicipio');

        $municipios->map(function ($mun) use ($conteosMun) {
            $mun->total_mes = $conteosMun[$mun->CodMunicipio] ?? 0;
            return $mun;
        });

        // 2. Entes
        $entes = TipoEnte::all();
        
        $conteosEnte = DB::table('solicitudes')
            ->join('relacion_correspondencia', 'solicitudes.CodSolicitud', '=', 'relacion_correspondencia.Solicitud_FK')
            ->join('tipos_entes', 'relacion_correspondencia.TipoEnte_FK', '=', 'tipos_entes.CodTipoEnte')
            ->whereMonth('FechaSolicitud', $this->mes)
            ->whereYear('FechaSolicitud', $this->anio)
            ->where('relacion_correspondencia.StatusSolicitud_FK', '!=', 7)
            ->select('tipos_entes.CodTipoEnte', DB::raw('count(*) as total'))
            ->groupBy('tipos_entes.CodTipoEnte')
            ->pluck('total', 'CodTipoEnte');

        $entes->map(function ($ente) use ($conteosEnte) {
            $ente->total_mes = $conteosEnte[$ente->CodTipoEnte] ?? 0;
            return $ente;
        });

        $nombreMes = Carbon::create()->month($this->mes)->locale('es')->monthName;
        $totalGeneral = $municipios->sum('total_mes');

        // --- CORRECCIÓN FINAL AQUÍ ---
        // Usamos un array limpio para evitar errores de sintaxis
        return view('estadisticas.export_excel', [
            'municipios'   => $municipios,
            'entes'        => $entes,
            'nombreMes'    => $nombreMes,
            'totalGeneral' => $totalGeneral,
            'anio'         => $this->anio 
        ]);
    }

    public function title(): string
    {
        return 'Reporte ' . $this->mes . '-' . $this->anio;
    }
}