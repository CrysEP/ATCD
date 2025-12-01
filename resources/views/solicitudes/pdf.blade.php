<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Solicitud</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .logo {
            width: 195px; 
            height: 68px;
            object-fit: contain;
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        .subtitulo {
            text-align: center;
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* Estilos de la Tabla Principal (Formulario) */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .main-table td, .main-table th {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        .section-header {
            background-color: #fff; /* Gris claro */
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }
        .label {
            font-weight: bold;
            font-size: 10px;
        }
        .content {
            font-size: 11px;
        }
        .check-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
            margin-left: 5px;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
          
            <td style="width: 25%; border: none; text-align: left; vertical-align: middle;">
                <img src="{{ public_path('images/Gobernación_logo.jpg') }}" class="logo" alt="Gobernación">
            </td>
            
            {{-- CENTRO: TÍTULOS --}}
            <td style="width: 50%; border: none; vertical-align: middle;">
                <div class="titulo">República Bolivariana de Venezuela</div>
                <div class="titulo">Corpointa</div>
                <div class="subtitulo">Formulario de Solicitud o Petición de Atención Ciudadana</div>
            </td>
            
            {{-- DERECHA: LOGO CORPOINTA (Movido aquí) --}}
            <td style="width: 25%; border: none; text-align: right; vertical-align: middle;">
                <img src="{{ public_path('images/logo_corpointa.png') }}" class="logo" alt="Corpointa">
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td style="text-align: right; font-weight: bold;">
                Nro. UAC: <span style="color: red;">{{ $solicitud->Nro_UAC ?? str_pad($solicitud->CodSolicitud, 5, '0', STR_PAD_LEFT) }}</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;">
                FECHA: {{ $solicitud->FechaSolicitud->format('d/m/Y') }} &nbsp;&nbsp;
                HORA: {{ $solicitud->FechaSolicitud->format('h:i A') }}
            </td>
        </tr>
    </table>

    {{-- TABLA DEL FORMULARIO --}}
    <table class="main-table">
        
        {{-- SECCIÓN I: DATOS DEL SOLICITANTE --}}
        <tr>
            <td colspan="4" class="section-header">I. DATOS DE LA PERSONA ATENDIDA (SOLICITANTE)</td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">TIPO DE SOLICITANTE:</span> &nbsp;&nbsp;
                Particular <div class="check-box">{{ $solicitud->TipoSolicitante == 'Personal' ? 'X' : '' }}</div> &nbsp;&nbsp;
                Consejo Comunal <div class="check-box">{{ $solicitud->TipoSolicitante == 'Consejo Comunal' ? 'X' : '' }}</div> &nbsp;&nbsp;
                Institucional <div class="check-box">{{ $solicitud->TipoSolicitante == 'Institucional' ? 'X' : '' }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">APELLIDOS Y NOMBRES:</span><br>
                <span class="content">{{ strtoupper($solicitud->persona->NombreCompleto) }}</span>
            </td>
            <td colspan="1">
                <span class="label">CÉDULA DE IDENTIDAD:</span><br>
                <span class="content">{{ $solicitud->persona->CedulaPersona }}</span>
            </td>
            <td colspan="1">
                <span class="label">SEXO:</span><br>
                <span class="content">{{ $solicitud->persona->SexoPersona == 'M' ? 'MASCULINO' : 'FEMENINO' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="1">
                <span class="label">FECHA NACIMIENTO:</span><br>
                <span class="content">
                    {{ $solicitud->persona->FechaNacPersona ? \Carbon\Carbon::parse($solicitud->persona->FechaNacPersona)->format('d/m/Y') : 'N/A' }}
                </span>
            </td>
            <td colspan="1">
                <span class="label">TELÉFONO:</span><br>
                <span class="content">{{ $solicitud->persona->TelefonoPersona }}</span>
            </td>
            <td colspan="2">
                <span class="label">CORREO ELECTRÓNICO:</span><br>
                <span class="content">{{ $solicitud->persona->CorreoElectronicoPersona }}</span>
            </td>
        </tr>

       <tr>
            <td colspan="4">
                <span class="label">DIRECCIÓN DE HABITACIÓN / MUNICIPIO / PARROQUIA:</span><br>
                <span class="content">
                    {{-- Mostramos la dirección detallada --}}
                    {{ $solicitud->DirecciónHabitación }}.
                    
                    {{-- Mostramos el punto de referencia si existe --}}
                    @if($solicitud->PuntoReferencia)
                        <br><strong>Ref:</strong> {{ $solicitud->PuntoReferencia }}.
                    @endif
                    
                    {{-- Mostramos la ubicación geográfica --}}
                    <strong>Ubicación:</strong> 
                    Mcp. {{ $solicitud->persona->parroquia->municipio->NombreMunicipio }}, 
                    Pq. {{ $solicitud->persona->parroquia->NombreParroquia }}.
                </span>
            </td>
        </tr>

        {{-- SECCIÓN II: DATOS DE LA SOLICITUD --}}
        <tr>
            <td colspan="4" class="section-header">II. DATOS DE LA SOLICITUD, QUEJA O DENUNCIA</td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">TIPO DE PLANILLA:</span> {{ $solicitud->TipoSolicitudPlanilla }}
                <!-- <span class="label">ENTE / CATEGORÍA:</span> {{ $solicitud->correspondencia->tipoEnte->NombreEnte ?? 'N/A' }} -->
            </td>
        </tr>
        <tr>
            <td colspan="4" style="height: 240px;">
                <span class="label">DETALLE DEL PLANTEAMIENTO REALIZADO:</span><br><br>
                <span class="content" style="text-align: justify; display: block;">
                    {{ $solicitud->DescripcionSolicitud }}
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">ANEXA DOCUMENTOS:</span> 
                SI <div class="check-box">{{ $solicitud->AnexaDocumentos ? 'X' : '' }}</div>
                NO <div class="check-box">{{ !$solicitud->AnexaDocumentos ? 'X' : '' }}</div>
                &nbsp;&nbsp;&nbsp;
            </td>
        </tr>

        {{-- SECCIÓN III: DATOS DEL FUNCIONARIO --}}
        <tr>
            <td colspan="4" class="section-header">III. DATOS DEL FUNCIONARIO RESPONSABLE (RECEPTOR)</td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">NOMBRES Y APELLIDOS:</span><br>
                <span class="content">
                    {{ $solicitud->funcionario->persona->NombreCompleto ?? 'Admin Sistema' }}
                </span>
            </td>
            <td colspan="2">
                <span class="label">CARGO:</span><br>
                <span class="content">
                    {{ $solicitud->funcionario->funcionarioData->CargoFuncionario ?? 'Administrativo' }}
                </span>
            </td>
        </tr>
       <tr>
            <td colspan="2">
                <span class="label">ADSCRIPCIÓN:</span><br>
                <span class="content">Oficina de Atención al Ciudadano - CORPOINTA</span>
            </td>
            <td colspan="2">
                <span class="label">FECHA Y HORA DE ATENCIÓN (RECEPCIÓN):</span><br>
                <span class="content">
                    {{-- Usa Carbon::parse por seguridad, aunque el modelo ya debería castearlo pero dsofijeowji--}}
                    {{ $solicitud->FechaAtención ? \Carbon\Carbon::parse($solicitud->FechaAtención)->format('d/m/Y h:i A') : 'N/A' }}
                </span>
            </td>
        </tr>

        {{-- NUEVA SECCIÓN IV: FIRMAS --}}
        <tr>
            <td colspan="4" class="section-header">IV. FIRMAS</td>
        </tr>
        <tr>
            {{-- Firma del Ciudadano (Espacio más ancho) --}}
            <td colspan="2" style="height: 80px; vertical-align: bottom; text-align: center;">
                <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto 5px auto;"></div>
                <span class="label">FIRMA DEL CIUDADANO (SOLICITANTE)</span>
            </td>
            
            {{-- Firma del Funcionario --}}
            <td colspan="1" style="height: 80px; vertical-align: bottom; text-align: center;">
                <div style="border-top: 1px solid #000; width: 90%; margin: 0 auto 5px auto;"></div>
                <span class="label">FIRMA DEL FUNCIONARIO</span>
            </td>
            
            {{-- Sello --}}
            <td colspan="1" style="height: 80px; vertical-align: top; text-align: left;">
                <span class="label">SELLO DE RECIBIDO:</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dirección: Prolongación 5ta Av. Final Viaducto Viejo – San Cristóbal Estado Táchira<br>
        Teléfono: 0276-3480835 | Correo: Corpointa.gobtachira@gmail.com
    </div>

</body>
</html>