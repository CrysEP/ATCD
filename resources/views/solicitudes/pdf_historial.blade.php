<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Solicitudes Procesadas</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; }
        
        /* Encabezado con Logos */
        .header-table { width: 100%; border-bottom: 2px solid #1F497D; margin-bottom: 10px; padding-bottom: 5px; }
        .logo { height: 50px; object-fit: contain; }
        .titulo-box { text-align: center; color: #1F497D; }
        .titulo { font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .subtitulo { font-size: 10px; margin-top: 2px; }

        /* Tabla de Datos */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { 
            background-color: #1F497D; 
            color: white; 
            padding: 6px; 
            font-size: 9px; 
            text-transform: uppercase;
            border: 1px solid #000;
        }
        .data-table td { 
            border: 1px solid #ccc; 
            padding: 5px; 
            vertical-align: top; /* Importante para que el texto empiece arriba */
            color: #333;
        }
        
        /* Ajuste de columnas para Hoja Vertical (Letter/A4) */
        .col-codigo { width: 10%; text-align: center; font-weight: bold; }
        .col-cedula { width: 10%; text-align: center; }
        .col-nombre { width: 20%; }
        .col-desc { width: 35%; text-align: justify; } /* La más ancha */
        .col-fecha { width: 10%; text-align: center; }
        .col-status { width: 15%; text-align: center; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <table class="header-table">
        <tr>
            <td style="width: 20%; text-align: left;">
                <img src="{{ public_path('images/Gobernación_logo.jpg') }}" class="logo">
                
            </td>
            <td style="width: 60%;" class="titulo-box">
                <h1 class="titulo">Listado de Solicitudes Procesadas</h1>
                <div class="subtitulo">Generado el: {{ date('d/m/Y h:i A') }}</div>
            </td>
            <td style="width: 20%; text-align: right;">
                <img src="{{ public_path('images/logo_corpointa.png') }}" class="logo">
            </td>
        </tr>
    </table>

    {{-- TABLA DE DATOS --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cédula</th>
                <th>Ciudadano</th>
                <th>Descripción (Resumen)</th>
                <th>Fecha</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($solicitudes as $solicitud)
            <tr>
                <td class="col-codigo">
                    {{ $solicitud->correspondencia->CodigoInterno ?? $solicitud->Nro_UAC ?? 'S/N' }}
                </td>
                <td class="col-cedula">{{ $solicitud->persona->CedulaPersona }}</td>
                <td class="col-nombre">
                    {{ $solicitud->persona->NombresPersona }} {{ $solicitud->persona->ApellidosPersona }}
                </td>
                <td class="col-desc">
                
                    {{ \Illuminate\Support\Str::words($solicitud->DescripcionSolicitud, 25, '...') }}
                </td>
                <td class="col-fecha">{{ $solicitud->FechaSolicitud->format('d/m/Y') }}</td>
                <td class="col-status">
                    <strong>{{ $solicitud->correspondencia->status->NombreStatusSolicitud ?? 'N/A' }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">No se encontraron registros con los filtros actuales.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Unidad de Atención al Ciudadano - Reporte de Control Interno
    </div>

</body>
</html>