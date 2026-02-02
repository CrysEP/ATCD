<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000000; padding: 5px; text-align: center; vertical-align: middle; font-family: Arial, sans-serif; font-size: 11px; }
        .header-title { font-weight: bold; font-size: 14px; background-color: #627ff5; }
        .header-sub { font-weight: bold; font-size: 12px; background-color: #9daef1; text-align: left; }
        .col-header { font-weight: bold; background-color: #DCE6F1; width: 100px; }
        .row-header { font-weight: bold; background-color: #8ba8ff; text-align: left; width: 150px; }
        .total-cell { font-weight: bold; background-color: #85abff; }
        
        /* Colores para estatus */
        .header-pend { background-color: #fff3cd; color: #856404; } /* Amarillo */
        .header-proc { background-color: #d1e7dd; color: #0f5132; } /* Verde */
    </style>
</head>
<body>

    {{-- ================= TABLA 1: MENSUAL GENERAL ================= --}}
    <table>
        <tr>
            <td colspan="{{ $entes->count() + 2 }}" class="header-title">
                REPORTE MENSUAL: {{ strtoupper($nombreMes) }} {{ $anio }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $entes->count() + 2 }}" class="header-sub" style="background-color: #9fb2ca;">
                Cuadro N° 1: Total de Solicitudes Recibidas (General)
            </td>
        </tr>
        
        <tr>
            <td class="col-header">MUNICIPIOS</td>
            @foreach($entes as $ente)
                <td class="col-header">{{ $ente->NombreEnte }}</td>
            @endforeach
            <td class="col-header total-cell">TOTAL</td>
        </tr>

        @php $totalesColumnasMensual = array_fill_keys($entes->pluck('CodTipoEnte')->toArray(), 0); $granTotalMensual = 0; @endphp
        
        @foreach($municipios as $mun)
            @php 
                $totalFila = 0; 
                $tieneDatos = false;
                foreach($entes as $e) if(($matrizMensual[$mun->CodMunicipio][$e->CodTipoEnte] ?? 0) > 0) $tieneDatos = true;
            @endphp

            @if($tieneDatos)
            <tr>
                <td class="row-header">{{ $mun->NombreMunicipio }}</td>
                @foreach($entes as $ente)
                    @php
                        $cantidad = $matrizMensual[$mun->CodMunicipio][$ente->CodTipoEnte] ?? 0;
                        $totalFila += $cantidad;
                        $totalesColumnasMensual[$ente->CodTipoEnte] += $cantidad;
                    @endphp
                    <td>{{ $cantidad > 0 ? $cantidad : '0' }}</td>
                @endforeach
                <td class="total-cell">{{ $totalFila }}</td>
                @php $granTotalMensual += $totalFila; @endphp
            </tr>
            @endif
        @endforeach

        <tr>
            <td class="total-cell" style="text-align: right;">TOTAL GENERAL</td>
            @foreach($entes as $ente)
                <td class="total-cell">{{ $totalesColumnasMensual[$ente->CodTipoEnte] }}</td>
            @endforeach
            <td class="total-cell">{{ $granTotalMensual }}</td>
        </tr>
    </table>

    <tr></tr>

    {{-- ================= TABLA 1.A: PENDIENTES ================= --}}
    <table>
        <tr>
            <td colspan="{{ $entes->count() + 2 }}" class="header-sub header-pend" style="background-color: #9fb2ca;">
                Cuadro N° 1.A: Desglose de Solicitudes PENDIENTES (En Revisión)
            </td>
        </tr>
        <tr>
            <td class="col-header">MUNICIPIOS</td>
            @foreach($entes as $ente)
                <td class="col-header">{{ $ente->NombreEnte }}</td>
            @endforeach
            <td class="col-header total-cell">TOTAL</td>
        </tr>

        @php $totPenCol = array_fill_keys($entes->pluck('CodTipoEnte')->toArray(), 0); $totPenGen = 0; @endphp

        @foreach($municipios as $mun)
            @php 
                $totalFila = 0; 
                $tieneDatos = false;
                foreach($entes as $e) if(($matrizPendientes[$mun->CodMunicipio][$e->CodTipoEnte] ?? 0) > 0) $tieneDatos = true;
            @endphp

            @if($tieneDatos)
            <tr>
                <td class="row-header">{{ $mun->NombreMunicipio }}</td>
                @foreach($entes as $ente)
                    @php
                        $cantidad = $matrizPendientes[$mun->CodMunicipio][$ente->CodTipoEnte] ?? 0;
                        $totalFila += $cantidad;
                        $totPenCol[$ente->CodTipoEnte] += $cantidad;
                    @endphp
                    <td>{{ $cantidad > 0 ? $cantidad : '0' }}</td>
                @endforeach
                <td class="total-cell">{{ $totalFila }}</td>
                @php $totPenGen += $totalFila; @endphp
            </tr>
            @endif
        @endforeach

        <tr>
            <td class="total-cell" style="text-align: right;">TOTAL PENDIENTES</td>
            @foreach($entes as $ente)
                <td class="total-cell">{{ $totPenCol[$ente->CodTipoEnte] }}</td>
            @endforeach
            <td class="total-cell">{{ $totPenGen }}</td>
        </tr>
    </table>

    <tr></tr>

    {{-- ================= TABLA 1.B: PROCESADAS ================= --}}
    <table>
        <tr>
            <td colspan="{{ $entes->count() + 2 }}" class="header-sub header-proc" style="background-color: #9fb2ca;">
                Cuadro N° 1.B: Desglose de Solicitudes PROCESADAS (Resueltas/Cerradas)
            </td>
        </tr>
        <tr>
            <td class="col-header">MUNICIPIOS</td>
            @foreach($entes as $ente)
                <td class="col-header">{{ $ente->NombreEnte }}</td>
            @endforeach
            <td class="col-header total-cell">TOTAL</td>
        </tr>

        @php $totProCol = array_fill_keys($entes->pluck('CodTipoEnte')->toArray(), 0); $totProGen = 0; @endphp

        @foreach($municipios as $mun)
            @php 
                $totalFila = 0; 
                $tieneDatos = false;
                foreach($entes as $e) if(($matrizProcesadas[$mun->CodMunicipio][$e->CodTipoEnte] ?? 0) > 0) $tieneDatos = true;
            @endphp

            @if($tieneDatos)
            <tr>
                <td class="row-header">{{ $mun->NombreMunicipio }}</td>
                @foreach($entes as $ente)
                    @php
                        $cantidad = $matrizProcesadas[$mun->CodMunicipio][$ente->CodTipoEnte] ?? 0;
                        $totalFila += $cantidad;
                        $totProCol[$ente->CodTipoEnte] += $cantidad;
                    @endphp
                    <td>{{ $cantidad > 0 ? $cantidad : '0' }}</td>
                @endforeach
                <td class="total-cell">{{ $totalFila }}</td>
                @php $totProGen += $totalFila; @endphp
            </tr>
            @endif
        @endforeach

        <tr>
            <td class="total-cell" style="text-align: right;">TOTAL PROCESADAS</td>
            @foreach($entes as $ente)
                <td class="total-cell">{{ $totProCol[$ente->CodTipoEnte] }}</td>
            @endforeach
            <td class="total-cell">{{ $totProGen }}</td>
        </tr>
    </table>

    <tr></tr> {{-- Separador grande --}}

    {{-- ================= TABLA 2: TRIMESTRALES (Bucle) ================= --}}
    @foreach($matricesTrimestrales as $trimestre => $matrizT)
        <table>
            <tr>
                <td colspan="{{ $entes->count() + 2 }}" class="header-sub" style="background-color: #9fb2ca;">
                    Cuadro Trimestral: Trimestre {{ $trimestre }} - {{ $anio }} 
                </td>
            </tr>
            <tr>
                <td class="col-header">MUNICIPIOS</td>
                @foreach($entes as $ente)
                    <td class="col-header">{{ $ente->NombreEnte }}</td>
                @endforeach
                <td class="col-header total-cell">TOTAL</td>
            </tr>

            @php $totalesColT = array_fill_keys($entes->pluck('CodTipoEnte')->toArray(), 0); $granTotalT = 0; @endphp

            @foreach($municipios as $mun)
                @php 
                    $totalFila = 0; 
                    $tieneDatos = false;
                    foreach($entes as $e) if(($matrizT[$mun->CodMunicipio][$e->CodTipoEnte] ?? 0) > 0) $tieneDatos = true;
                @endphp

                @if($tieneDatos)
                <tr>
                    <td class="row-header">{{ $mun->NombreMunicipio }}</td>
                    @foreach($entes as $ente)
                        @php
                            $cantidad = $matrizT[$mun->CodMunicipio][$ente->CodTipoEnte] ?? 0;
                            $totalFila += $cantidad;
                            $totalesColT[$ente->CodTipoEnte] += $cantidad;
                        @endphp
                        <td>{{ $cantidad > 0 ? $cantidad : '0' }}</td>
                    @endforeach
                    <td class="total-cell">{{ $totalFila }}</td>
                    @php $granTotalT += $totalFila; @endphp
                </tr>
                @endif
            @endforeach

            <tr>
                <td class="total-cell" style="text-align: right;">TOTAL TRIMESTRE {{ $trimestre }}</td>
                @foreach($entes as $ente)
                    <td class="total-cell">{{ $totalesColT[$ente->CodTipoEnte] }}</td>
                @endforeach
                <td class="total-cell">{{ $granTotalT }}</td>
            </tr>
        </table>
        <tr></tr>
    @endforeach

    {{-- ================= TABLA 3: ANUAL ================= --}}
    <table>
        <tr>
            <td colspan="{{ $entes->count() + 2 }}" class="header-title" style="background-color: #143774; color: white;">
                RESUMEN ANUAL {{ $anio }}
            </td>
        </tr>
        <tr>
            <td colspan="{{ $entes->count() + 2 }}" class="header-sub">
                Cuadro N° 3: Relación Anual de Solicitudes por Entes y Municipios
            </td>
        </tr>
        <tr>
            <td class="col-header">MUNICIPIOS</td>
            @foreach($entes as $ente)
                <td class="col-header">{{ $ente->NombreEnte }}</td>
            @endforeach
            <td class="col-header total-cell">TOTAL</td>
        </tr>

        @php $totalesColA = array_fill_keys($entes->pluck('CodTipoEnte')->toArray(), 0); $granTotalA = 0; @endphp

        @foreach($municipios as $mun)
            @php 
                $totalFila = 0; 
                foreach($entes as $e) $totalFila += ($matrizAnual[$mun->CodMunicipio][$e->CodTipoEnte] ?? 0);
            @endphp

            @if($totalFila > 0)
            <tr>
                <td class="row-header">{{ $mun->NombreMunicipio }}</td>
                @foreach($entes as $ente)
                    @php
                        $cantidad = $matrizAnual[$mun->CodMunicipio][$ente->CodTipoEnte] ?? 0;
                        $totalesColA[$ente->CodTipoEnte] += $cantidad;
                    @endphp
                    <td>{{ $cantidad > 0 ? $cantidad : '0' }}</td>
                @endforeach
                <td class="total-cell">{{ $totalFila }}</td>
                @php $granTotalA += $totalFila; @endphp
            </tr>
            @endif
        @endforeach

        <tr>
            <td class="total-cell" style="text-align: right;">TOTAL ANUAL</td>
            @foreach($entes as $ente)
                <td class="total-cell">{{ $totalesColA[$ente->CodTipoEnte] }}</td>
            @endforeach
            <td class="total-cell">{{ $granTotalA }}</td>
        </tr>
    </table>

</body>
</html>