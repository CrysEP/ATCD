<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Control</title>
    <style>
        @page { margin: 0px; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 10px; /* Margen interno del ticket o tarjeta */
        }
        
        .card-container {
            border: 2px solid #1F497D;
            border-radius: 10px;
            padding: 10px;
            height: 245px; /* Altura ajustada al papel */
            position: relative;
            z-index: 1;
        }


        .watermark {
        position: absolute;
        top: 44%;        
        left: 50%;          
        transform: translate(-62%, -15%);
        width: 70%;     
        opacity: 0.15;      
        z-index: -1;     
        pointer-events: none;
    }


        /* El espacio del QR y el bloque del UAC (DomPDF no soporta bien Flexbox) */
        .layout-table {
            width: 95%;
            border-collapse: collapse;
        }

        .header-logo {
            font-size: 18px;
            font-weight: bold;
            color: #0B59F4;
            text-transform: uppercase;
        }

        .header-sub {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }

        .uac-box {
            background-color: #0f79ea;
            color: white;
            padding: 5px 10px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            text-align: center;
        }

        .info-label {
            font-size: 8px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .info-value {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-bottom: 6px;
            display: block;
        }

        .divider {
            border-bottom: 1px solid #ddd;
            margin: 5px 0 8px 0;
        }

        .qr-placeholder {
            width: 70px;
            height: 70px;
            border: 1px solid #ddd;
        }
        
        .footer-note {
            position: absolute;
            bottom: 5px;
            left: 10px;
            right: 10px;
            font-size: 8px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="card-container">
        
<img src="{{ public_path('images/logo_corpointa.png') }}" class="watermark" alt="Marca de Agua">


        {{-- ENCABEZADO --}}
        <table class="layout-table">
            <tr>
                <td style="width: 65%;">
                    <div class="header-logo">CORPOINTA</div>
                    <div class="header-sub">Unidad de Atención al Ciudadano</div>
                    <div class="header-sub">Fecha: {{ now()->format('d/m/Y h:i A') }}</div>
                </td>
                <td style="width: 35%; text-align: right;">
                    <div class="uac-box">
                        {{ $solicitud->Nro_UAC ?? 'S/N' }}
                    </div>
                    <div style="font-size: 8px; text-align: center; margin-top: 2px;">Nro. Control UAC</div>
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- CUERPO DE LA TARJETA --}}
        <table class="layout-table">
            <tr>
                {{-- COLUMNA IZQUIERDA: DATOS --}}
                <td style="vertical-align: top;">
                    
                    <div class="info-label">Solicitante:</div>
                    <div class="info-value" style="font-size: 12px;">
                        {{ strtoupper($solicitud->persona->NombresPersona) }} {{ strtoupper($solicitud->persona->ApellidosPersona) }}
                    </div>

                    <table style="width: 100%">
                        <tr>
                            <td>
                                <div class="info-label">Cédula:</div>
                                <div class="info-value">{{ $solicitud->persona->CedulaPersona }}</div>
                            </td>
                            <td>
                                <div class="info-label">Código Interno:</div>
                                <div class="info-value">{{ $solicitud->correspondencia->CodigoInterno ?? '---' }}</div>
                            </td>
                        </tr>
                    </table>

                    <div class="info-label">Asunto / Clasificación:</div>
                    <div class="info-value">
                        {{ $clasificacion }}
                    </div>
                    
                    <div class="info-label">Detalle Breve:</div>
                    <div class="info-value" style="font-size: 10px; line-height: 1.1; text-align: justify; padding-right: 10px;">
                        {{ \Illuminate\Support\Str::limit($solicitud->DescripcionSolicitud, 290, '...') }}
                    </div>

                </td>

                {{-- COLUMNA DERECHA: QR --}}
                <td style="width: 80px; vertical-align: middle; text-align: center; border-left: 1px dashed #ccc;">
                    
                    {{-- QR GENERADO --}}
                    {{-- API pública para generar el QR sin instalar librerías extras, por ahora --}}
                    {{-- El contenido del QR es el ID de la solicitud para futuro rastreo --}}
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ route('solicitudes.show', $solicitud->CodSolicitud) }}" 
                         style="width: 70px; height: 70px;" alt="QR">
                    
                    <div style="font-size: 7px; margin-top: 4px; color: #555;">
                        ESCANEE PARA<br>VER ESTATUS
                    </div>

                </td>
            </tr>
        </table>

        {{-- PIE DE PÁGINA --}}
        <div class="footer-note">
            Conserve esta tarjeta. Es su único comprobante para solicitar una respuesta o consultar el estado de su trámite.
            <br>
            <strong>Unidad de Atención al Ciudadano </strong> - CORPOINTA
        </div>

    </div>

</body>
</html>