<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Solicitud</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            /* font-family: Arial, sans-serif; */
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        .logo {
            width: 180px; /* Ajusta según tus imágenes */
            height: auto;
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        .subtitulo {
            text-align: center;
            font-size: 15px;
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
            background-color: #d0d0d0; /* Gris claro como en la planilla */
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .label {
            font-weight: bold;
            font-size: 10px;
        }

        .label-detalle {
            font-weight: bold;
            font-size: 10px;
        }
        .content {
            font-size: 12px;
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

    {{-- ENCABEZADO CON LOGOS --}}
    <table class="header-table">
        <tr>
            <td style="width: 20%; border: none; text-align: left;">
                <img src="{{ public_path('images/logo_corpointa.png') }}" class="logo" alt="Corpointa">
            </td>
            <td style="width: 60%; border: none;">
                <div class="titulo">República Bolivariana de Venezuela</div>
                <div class="titulo">Corpointa</div>
                <div class="subtitulo">Formulario de Solicitud o Petición de Atención Ciudadana</div>
            </td>
            <td style="width: 20%; border: none; text-align: right;">
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
            {{-- Modificado para incluir SEXO --}}
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
            {{-- Modificado para incluir FECHA DE NACIMIENTO --}}
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
                <span class="label">MUNICIPIO / PARROQUIA:</span><br>
                <span class="content">
                    Mcp. {{ $solicitud->persona->parroquia->municipio->NombreMunicipio }}, 
                    Pq. {{ $solicitud->persona->parroquia->NombreParroquia }}.
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="height: 40px; vertical-align: bottom;">
                <span class="label">FIRMA DEL CIUDADANO ATENDIDO:</span>
            </td>
        </tr>

        {{-- SECCIÓN II: DATOS DE LA SOLICITUD --}}
        <tr>
            <td colspan="4" class="section-header">II. DATOS DE LA SOLICITUD O PETICIÓN</td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">TIPO DE PLANILLA:</span> {{ $solicitud->TipoSolicitudPlanilla }} <br>
                <span class="label">ENTE / CATEGORÍA:</span> {{ $solicitud->correspondencia->tipoEnte->NombreEnte ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td colspan="4" style="height: 200px;">
                <span class="label-detalle">DETALLE DEL PLANTEAMIENTO REALIZADO:</span><br><br>
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
                <span class="label">OBSERVACIONES:</span> {{ $solicitud->correspondencia->Observacion }}
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
            <td colspan="4">
                <span class="label">ADSCRIPCIÓN:</span><br>
                <span class="content">Oficina de Atención al Ciudadano - CORPOINTA</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="height: 60px; vertical-align: bottom;">
                <span class="label">FIRMA DEL RESPONSABLE:</span>
            </td>
            <td colspan="2" style="height: 60px; vertical-align: bottom;">
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