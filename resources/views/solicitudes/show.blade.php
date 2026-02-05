@extends('layouts.app')

@section('title', 'Detalle de Solicitud #' . ($solicitud->Nro_UAC ?? $solicitud->CodSolicitud))

@section('content')

    {{-- Alerta de éxito --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                <div>
                    <strong>¡Éxito!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Encabezado y Botones de Acción --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                Solicitud 
                <span class="text-primary fw-bold">
                    #{{ $solicitud->Nro_UAC ?? $solicitud->CodSolicitud }}
                </span>
            </h2>
            <span class="fs-5 text-muted">Nivel de Urgencia:
                <span class="badge fs-6
                    @if($solicitud->NivelUrgencia == 'Alto') bg-danger
                    @elseif($solicitud->NivelUrgencia == 'Medio') bg-warning text-dark
                    @else bg-success
                    @endif">
                    {{ $solicitud->NivelUrgencia }}
                </span>
            </span>
        </div>

        <div class="d-flex gap-2">
            {{-- BOTÓN EDITAR --}}
            @if($solicitud->correspondencia->StatusSolicitud_FK != 7)
                <a href="{{ route('solicitudes.edit', $solicitud->CodSolicitud) }}" class="btn btn-warning text-dark">
                    <i class="bi bi-pencil-square"></i> Editar Datos
                </a>
            @else
                <button class="btn btn-secondary" disabled title="No se puede editar">
                    <i class="bi bi-lock-fill"></i> Edición Bloqueada
                </button>
            @endif

            {{-- BOTÓN ANULAR / RESTAURAR --}}        
            @if($solicitud->correspondencia->StatusSolicitud_FK != 7)
                <button type="button" class="btn btn-danger" onclick="confirmarAnulacion()">
                    <i class="bi bi-trash"></i> Anular
                </button>
                <form id="form-anular" action="{{ route('solicitudes.anular', $solicitud->CodSolicitud) }}" method="POST" style="display: none;">
                    @csrf @method('PUT')
                </form>
            @else
                <button class="btn btn-secondary" disabled>Solicitud Anulada</button>
                <button type="button" class="btn btn-success" onclick="confirmarRestauracion()">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                </button>
                <form id="form-restaurar" action="{{ route('solicitudes.restaurar', $solicitud->CodSolicitud) }}" method="POST" style="display: none;">
                    @csrf @method('PUT')
                </form>
            @endif

            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                &larr; Volver
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna Izquierda: Datos Principales --}}
        <div class="col-lg-8">
            
            {{-- 1. Info Solicitante --}}
            <div class="card card-gradient-body shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">1. Información del Solicitante</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Nombre:</strong> {{ $solicitud->persona->NombreCompleto }}</div>
                        <div class="col-md-6"><strong>Cédula:</strong> {{ $solicitud->persona->CedulaPersona }}</div>
                        
                        <div class="col-md-6"><strong>Género:</strong> {{ $solicitud->persona->SexoPersona == 'M' ? 'Masculino' : 'Femenino' }}</div>
                        <div class="col-md-6"><strong>Fecha Nacimiento:</strong> 
                            {{ \Carbon\Carbon::parse($solicitud->persona->FechaNacPersona)->format('d/m/Y') }}
                            <small class="text-muted">({{ \Carbon\Carbon::parse($solicitud->persona->FechaNacPersona)->age }} años)</small>
                        </div>

                        <div class="col-md-6"><strong>Teléfono:</strong> {{ $solicitud->persona->TelefonoPersona }}</div>
                        <div class="col-md-6"><strong>Correo:</strong> {{ $solicitud->persona->CorreoElectronicoPersona ?? 'N/A' }}</div>
                        <div class="col-12 border-top pt-2 mt-2">
                            <strong>Ubicación Geográfica:</strong><br>
                            {{ $solicitud->persona->parroquia->NombreParroquia ?? 'N/A' }},
                            Municipio {{ $solicitud->persona->parroquia->municipio->NombreMunicipio ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Detalles Solicitud --}}
            <div class="card card-gradient-body shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">2. Detalles de la Solicitud</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Tipo (Planilla):</strong> {{ $solicitud->TipoSolicitudPlanilla }}</div>
                        <div class="col-md-6"><strong>Tipo Solicitante:</strong> {{ $solicitud->TipoSolicitante }}</div>
                        
                        <div class="col-md-6"><strong>Fecha Solicitud (Planilla):</strong> {{ $solicitud->FechaSolicitud->format('d/m/Y h:i A') }}</div>
                        <div class="col-md-6"><strong>Fecha Atención (Recepción):</strong> {{ $solicitud->FechaAtención->format('d/m/Y h:i A') }}</div>

                        <div class="col-md-6"><strong>Código Interno:</strong> {{ $solicitud->correspondencia->CodigoInterno ?? 'N/A' }}</div>
                        
                        <div class="col-12 mt-3 border-top pt-2">
                            <strong>Dirección de Habitación:</strong><br>
                            {{ $solicitud->DirecciónHabitación }}
                        </div>
                        @if($solicitud->PuntoReferencia)
                        <div class="col-12">
                            <strong>Punto de Referencia:</strong><br>
                            {{ $solicitud->PuntoReferencia }}
                        </div>
                        @endif
                    </div>

                    <div class="mt-4 border-top pt-3">
                        <strong>Descripción de la Solicitud:</strong>
                        <p class="bg-light p-3 rounded-2 mt-2" style="white-space: pre-wrap;">{{ $solicitud->DescripcionSolicitud }}</p>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('solicitudes.pdf', $solicitud->CodSolicitud) }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                </a> 
                <a href="{{ route('solicitudes.ticket', $solicitud->CodSolicitud) }}" target="_blank" class="btn btn-outline-dark">
                    <i class="bi bi-receipt"></i> Imprimir Ticket
                </a>
            </div>
            <br>
            
            {{-- 3. Archivos --}}
            <div class="card card-gradient-body shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">3. Archivos Adjuntos</h5>
                </div>
                <div class="card-body p-4">
                    @if($solicitud->archivos->count() > 0)
                        <ul class="list-group">
                            @foreach($solicitud->archivos as $archivo)
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-truncate me-3" style="max-width: 60%;">
                                    <i class="bi bi-file-earmark-text-fill text-primary me-2"></i> 
                                    <span class="fw-bold text-dark">{{ $archivo->nombre_original }}</span>
                                    <small class="text-muted ms-2">({{ number_format($archivo->tamano_archivo / 1024, 2) }} KB)</small>
                                </div>
                                <div class="d-flex gap-2 mt-2 mt-md-0">
                                    @if(in_array($archivo->tipo_archivo, ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']))
                                        <button type="button" class="btn btn-sm btn-info text-white" 
                                                onclick="abrirModalPreview(this)"
                                                data-url="{{ route('solicitudes.verArchivo', $archivo->id) }}"
                                                data-tipo="{{ $archivo->tipo_archivo }}"
                                                data-nombre="{{ $archivo->nombre_original }}"
                                                title="Vista Previa">
                                            <i class="bi bi-eye-fill"></i> Ver
                                        </button>
                                    @endif
                                    <a href="{{ route('solicitudes.downloadFile', $archivo->id) }}" class="btn btn-sm btn-outline-primary" title="Descargar">
                                        <i class="bi bi-download"></i> Descargar
                                    </a>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-folder-x fs-3 d-block mb-2"></i>
                            No hay archivos adjuntos en esta solicitud.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna Derecha: Correspondencia y Acciones --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 position-sticky" style="top: 1.5rem;">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Relación Correspondencia</h5>
                    @can ('es-admin')
                        @if($solicitud->correspondencia->StatusSolicitud_FK != 7)
                            <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalEditarFlujo">
                                Editar
                            </button>
                        @endif
                    @endcan
                </div>
                
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <span class="fs-4 fw-bold p-3 rounded-3 bg-primary bg-opacity-10 text-primary border border-primary">
                            {{ $solicitud->status->NombreStatusSolicitud ?? 'Sin Estado' }}
                        </span>
                    </div>

                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item px-0"><strong>Nro. Oficio:</strong> {{ $solicitud->correspondencia->Nro_Oficio ?? 'N/A' }}</li>
                        <li class="list-group-item px-0">
                            <strong>Ente (Categoría):</strong><br>
                            <span class="text-dark fw-bold">
                                {{ $tiposEnte->firstWhere('CodTipoEnte', $solicitud->correspondencia->TipoEnte_FK)?->NombreEnte ?? 'N/A' }}
                            </span>
                        </li>
                        <li class="list-group-item px-0"><strong>Sector:</strong> {{ $solicitud->correspondencia->Sector ?? 'N/A' }}</li>
                        <li class="list-group-item px-0"><strong>Gerencia/Jefatura:</strong> {{ $solicitud->correspondencia->Gerencia_Jefatura ?? 'N/A' }}</li>
                        <li class="list-group-item px-0">
                            <strong>Observación:</strong><br>
                            <span class="text-muted small" style="white-space: pre-wrap;">{{ $solicitud->correspondencia->Observacion ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item px-0"><strong>Tipo de Solicitud:</strong> <span class="badge bg-info text-dark">{{ $solicitud->TipoSolicitudPlanilla }}</span></li>
                        <li class="list-group-item px-0"><strong>Recibido:</strong> {{ $solicitud->correspondencia->FechaRecibido->format('d/m/Y') }}</li>
                        <li class="list-group-item px-0"><strong>Registrado por:</strong> {{ $solicitud->funcionario->persona->NombreCompleto ?? 'N/A' }}</li>
                    </ul>

                    <div>
                        <strong>Instrucciones:</strong>
                        <p class="bg-light p-3 rounded-2 mt-1 small" style="white-space: pre-wrap; height: 150px; overflow-y: auto;">{{ $solicitud->correspondencia->InstruccionPresidencia ?? 'Sin instrucciones.' }}</p>
                    </div>
                </div>

                {{-- Acciones de Administrador --}}
                @can('es-admin')
                    @if($solicitud->correspondencia->StatusSolicitud_FK != 7)
                        <div class="card-footer p-4">
                            <h5 class="mb-3">Acciones</h5>
                            
                            <form action="{{ route('solicitudes.updateStatus', ['id' => $solicitud->CodSolicitud]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="status_id" class="form-label">Cambiar Estado:</label>
                                    <select name="status_id" id="status_id" required class="form-select">
                                        @foreach($statuses as $status)
                                        <option value="{{ $status->CodStatusSolicitud }}" @selected($solicitud->status->CodStatusSolicitud == $status->CodStatusSolicitud)>
                                            {{ $status->NombreStatusSolicitud }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="observacion_status" class="form-label">Instrucción Presidencia:</label>
                                    <textarea name="observacion" id="observacion_status" rows="3" class="form-control" placeholder="Ej: Aprobado por presidencia..."></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        Actualizar Estado
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card-footer p-4 bg-light text-center text-muted">
                            <small><i class="bi bi-slash-circle me-1"></i> Sin acciones disponibles (Anulada)</small>
                        </div>
                    @endif
                @endcan 
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR RELACIÓN DE CORRESPONDENCIA --}}
    <div class="modal fade" id="modalEditarFlujo" tabindex="-1" aria-labelledby="modalEditarFlujoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('solicitudes.updateFlujo', ['id' => $solicitud->CodSolicitud]) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarFlujoLabel">Editar Relación de Correspondencia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">
                            Utilice este formulario para corregir datos administrativos internos.
                        </p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="Nro_Oficio" class="form-label">Nro. Oficio:</label>
                                <input type="text" class="form-control" id="Nro_Oficio" name="Nro_Oficio" value="{{ $solicitud->correspondencia->Nro_Oficio }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Gerencia_Jefatura" class="form-label">Gerencia / Jefatura:</label>
                                <input type="text" class="form-control" id="Gerencia_Jefatura" name="Gerencia_Jefatura" value="{{ $solicitud->correspondencia->Gerencia_Jefatura }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="Sector" class="form-label">Sector:</label>
                            <input type="text" class="form-control" id="Sector" name="Sector" value="{{ $solicitud->correspondencia->Sector }}" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="tipo_ente" class="form-label">Ente (Categoría):</label>
                            <select class="form-select" id="tipo_ente" name="tipo_ente" required>
                                @foreach($tiposEnte as $ente)
                                    <option value="{{ $ente->CodTipoEnte }}" @selected($solicitud->correspondencia->TipoEnte_FK == $ente->CodTipoEnte)>
                                        {{ $ente->NombreEnte }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="TipoSolicitudPlanilla" class="form-label">Tipo de Solicitud (Planilla):</label>
                            <select class="form-select" id="TipoSolicitudPlanilla" name="TipoSolicitudPlanilla">
                                <option value="Solicitud o Petición" @selected($solicitud->TipoSolicitudPlanilla == 'Solicitud o Petición')>Solicitud o Petición</option>
                                <option value="Quejas, reclamos o sugerencias" @selected($solicitud->TipoSolicitudPlanilla == 'Quejas, reclamos o sugerencias')>Quejas, reclamos o sugerencias</option>
                                <option value="Denuncia" @selected($solicitud->TipoSolicitudPlanilla == 'Denuncia')>Denuncia</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="InstruccionPresidencia" class="form-label">Instrucciones de Presidencia:</label>
                            <textarea class="form-control" id="InstruccionPresidencia" name="InstruccionPresidencia" rows="4">{{ $solicitud->correspondencia->InstruccionPresidencia }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="Observacion" class="form-label">Observación (Opcional):</label>
                            <textarea class="form-control" id="Observacion" name="Observacion" rows="2">{{ $solicitud->correspondencia->Observacion }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmarRestauracion() {
            if (confirm('¿Estás seguro de que deseas RESTAURAR esta solicitud?\n\nVolverá a aparecer en la Agenda Digital como "Pendiente".')) {
                document.getElementById('form-restaurar').submit();
            }
        }

        function confirmarAnulacion() {
            if (confirm('¿Está seguro de que desea ANULAR esta solicitud?\n\nEsta acción no borrará el registro, pero lo sacará de la lista de pendientes.')) {
                document.getElementById('form-anular').submit();
            }
        }
    </script>

    {{-- === MODAL DE PREVISUALIZACIÓN === --}}
    <div class="modal fade" id="modalPrevisualizacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered"> 
            <div class="modal-content" style="height: 90vh;"> 
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title fw-bold" id="tituloArchivo">Vista Previa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-discord d-flex justify-content-center align-items-center" style="overflow: hidden;">
                    <iframe id="visorPDF" src="" style="width: 100%; height: 100%; border: none; display: none;"></iframe>
                    <img id="visorImagen" src="" class="img-fluid" style="max-height: 100%; max-width: 100%; display: none;" alt="Vista previa">
                </div>
            </div>
        </div>
    </div>

    {{-- NOTA IMPORTANTE: Se eliminó la importación manual de Bootstrap Bundle aquí porque causaba conflicto con la del Layout --}}
    
    <script>
        let modalPreviewInstance = null;

        function abrirModalPreview(boton) {
            const url = boton.getAttribute('data-url');
            const tipo = boton.getAttribute('data-tipo');
            const nombre = boton.getAttribute('data-nombre');

            document.getElementById('tituloArchivo').textContent = nombre;
            const iframe = document.getElementById('visorPDF');
            const img = document.getElementById('visorImagen');
            
            iframe.style.display = 'none';
            iframe.src = ''; 
            img.style.display = 'none';
            img.src = '';

            if (tipo === 'application/pdf') {
                iframe.src = url;
                iframe.style.display = 'block';
            } else {
                img.src = url;
                img.style.display = 'block';
            }

            const modalEl = document.getElementById('modalPrevisualizacion');
            if (!modalPreviewInstance) {
                // Asumimos que Bootstrap ya está cargado globalmente desde app.js
                modalPreviewInstance = new bootstrap.Modal(modalEl);
            }
            modalPreviewInstance.show();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalPrevisualizacion');
            if(modalEl){
                modalEl.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('visorPDF').src = '';
                    document.getElementById('visorImagen').src = '';
                });
            }
        });
    </script>

@endsection