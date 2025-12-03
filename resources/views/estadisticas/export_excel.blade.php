<table>
    {{-- TÍTULO PRINCIPAL --}}
    <tr>
        <td colspan="2" style="font-weight: bold; text-align: center; font-size: 14px;">
            MES DE {{ strtoupper($nombreMes) }} DEL AÑO {{ $anio }}
        </td>
    </tr>
    <tr></tr> {{-- Espacio vacío --}}

    {{-- SECCIÓN 1: MUNICIPIOS --}}
    <tr>
        <td colspan="2" style="font-weight: bold;">Gráfica N°1</td>
    </tr>
    <tr>
        <td colspan="5" style="font-style: italic;">
            "Relación de solicitudes por municipio; mes de {{ ucfirst($nombreMes) }} de {{ $anio }}, Unidad de Atención al Ciudadano"
        </td>
    </tr>
    <tr></tr>

    {{-- Tabla de Municipios --}}
    @foreach($municipios as $mun)
    <tr>
        <td>{{ $mun->NombreMunicipio }}</td>
        <td>{{ $mun->total_mes }}</td>
    </tr>
    @endforeach

    {{-- Total Municipios --}}
    <tr>
        <td style="font-weight: bold; text-align: right;">TOTAL:</td>
        <td style="font-weight: bold;">{{ $totalGeneral }}</td>
    </tr>

    <tr></tr>
    <tr></tr>

    {{-- SECCIÓN 2: ENTES --}}
    <tr>
        <td colspan="2" style="font-weight: bold;">Gráfica N°2</td>
    </tr>
    <tr>
        <td colspan="10" style="font-style: italic;">
            Relación de solicitudes recibidas discriminadas por municipios y por entes durante el mes de {{ ucfirst($nombreMes) }} del Año {{ $anio }}; Unidad de Atención al Ciudadano
        </td>
    </tr>
    <tr></tr>

    {{-- Tabla de Entes (Horizontal como en tu ejemplo) --}}
    <tr>
        @foreach($entes as $ente)
            <td style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f0f0f0;">
                {{ $ente->NombreEnte }}
            </td>
        @endforeach
    </tr>
    <tr>
        @foreach($entes as $ente)
            <td style="text-align: center; border: 1px solid #000000;">
                {{ $ente->total_mes }}
            </td>
        @endforeach
    </tr>

</table>