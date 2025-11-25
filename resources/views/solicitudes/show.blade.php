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
            @if($solicitud->correspondencia->StatusSolicitud_FK != 7)
            <a href="{{ route('solicitudes.edit', $solicitud->CodSolicitud) }}" class="btn btn-warning text-dark">
                <i class="bi bi-pencil-square"></i> Editar Datos
            </a>


            @else
            <button class="btn btn-secondary" disabled title="No se puede editar">
                <i class="bi bi-lock-fill"></i> Edición Bloqueada
            </button>
        @endif

            {{-- BOTÓN ANULAR SOLICITUD --}}        
            @if($solicitud->correspondencia->StatusSolicitud_FK != 7)
                <button type="button" class="btn btn-danger" onclick="confirmarAnulacion()">
                    <i class="bi bi-trash"></i> Anular
                </button>

                <form id="form-anular" action="{{ route('solicitudes.anular', $solicitud->CodSolicitud) }}" method="POST" style="display: none;">
                    @csrf
                    @method('PUT')
                </form>
            @else
                <button class="btn btn-secondary" disabled>Solicitud Anulada</button>
            @endif

            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                &larr; Volver a la Agenda
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
                        <div class="col-md-6"><strong>Teléfono:</strong> {{ $solicitud->persona->TelefonoPersona }}</div>
                        <div class="col-md-6"><strong>Correo:</strong> {{ $solicitud->persona->CorreoElectronicoPersona ?? 'N/A' }}</div>
                        <div class="col-12"><strong>Localidad:</strong>
                            {{ $solicitud->persona->parroquia->NombreParroquia ?? 'N/A' }},
                            Mcp. {{ $solicitud->persona->parroquia->municipio->NombreMunicipio ?? 'N/A' }}
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
                        <div class="col-md-6"><strong>Fecha Solicitud:</strong> {{ $solicitud->FechaSolicitud->format('d/m/Y h:i A') }}</div>
                        <div class="col-md-6"><strong>Código Interno:</strong> {{ $solicitud->correspondencia->CodigoInterno ?? 'N/A' }}</div>
                    </div>
                    <div class="mt-4">
                        <strong>Descripción de la Solicitud:</strong>
                        <p class="bg-light p-3 rounded-2 mt-2" style="white-space: pre-wrap;">{{ $solicitud->DescripcionSolicitud }}</p>
                    </div>
                </div>
            </div>

            {{-- 3. Archivos --}}
            <div class="card card-gradient-body shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">3. Archivos Adjuntos</h5>
                </div>
                <div class="card-body p-4">
                    @if($solicitud->archivos->count() > 0)
                        <ul class="list-group">
                            @foreach($solicitud->archivos as $archivo)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('solicitudes.downloadFile', $archivo->id) }}" class="text-decoration-none" title="Descargar">
                                    {{ $archivo->nombre_original }}
                                </a>
                                <span class="text-muted small">{{ number_format($archivo->tamano_archivo / 1024, 2) }} KB</span>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No hay archivos adjuntos para esta solicitud.</p>
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
                        <li class="list-group-item px-0"><strong>Tipo de Solicitud:</strong> <span class="badge bg-info text-dark">{{ $solicitud->TipoSolicitudPlanilla }}</span></li>

                        {{-- Visualización de la Clasificación --}}
                        <li class="list-group-item px-0">
                            <strong>Clasificación:</strong><br>
                            <span class="text-muted">
                                {{ $catActual && isset($categorias[$catActual]) ? $categorias[$catActual]['label'] : 'No definida' }}
                            </span>
                        </li>
                        <li class="list-group-item px-0">
                            <strong>Detalle:</strong><br>
                            <span class="text-muted">{{ $detActual ?? 'N/A' }}</span>
                        </li>

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
                            <label for="observacion" class="form-label">Observación (se añadirá al historial):</label>
                            <textarea name="observacion" id="observacion" rows="3" class="form-control" placeholder="Ej: Aprobado por presidencia, remitir a..."></textarea>
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
                            Utilice este formulario para corregir errores en los datos de la correspondencia y clasificación.
                        </p>

                        <div class="mb-3">
                            <label for="Nro_Oficio" class="form-label">Nro. Oficio:</label>
                            <input type="text" class="form-control" id="Nro_Oficio" name="Nro_Oficio" 
                                   value="{{ $solicitud->correspondencia->Nro_Oficio }}">
                        </div>

                        <div class="mb-3">
                            <label for="InstruccionPresidencia" class="form-label">Instrucciones de Presidencia:</label>
                            <textarea class="form-control" id="InstruccionPresidencia" name="InstruccionPresidencia" rows="4"
                                      placeholder="Escriba aquí el texto corregido. Esto REEMPLAZARÁ el contenido actual.">{{ $solicitud->correspondencia->InstruccionPresidencia }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="TipoSolicitudPlanilla" class="form-label">Tipo de Solicitud (Planilla):</label>
                            <select class="form-select" id="TipoSolicitudPlanilla" name="TipoSolicitudPlanilla">
                                <option value="Solicitud o Petición" @selected($solicitud->TipoSolicitudPlanilla == 'Solicitud o Petición')>Solicitud o Petición</option>
                                <option value="Quejas, reclamos o sugerencias" @selected($solicitud->TipoSolicitudPlanilla == 'Quejas, reclamos o sugerencias')>Quejas, reclamos o sugerencias</option>
                                <option value="Denuncia" @selected($solicitud->TipoSolicitudPlanilla == 'Denuncia')>Denuncia</option>
                            </select>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Clasificación de la Solicitud</h6>

                        <div class="mb-3">
                            <label for="categoria_solicitud_edit" class="form-label">Clasificación (Área):</label>
                            <select class="form-select" id="categoria_solicitud_edit" name="categoria_solicitud" required>
                                <option value="" disabled>-- Seleccione --</option>
                                @foreach ($categorias as $key => $cat)
                                    <option value="{{ $key }}" @selected($key == $catActual)>
                                        {{ $cat['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="detalle_solicitud_edit" class="form-label">Detalle Específico:</label>
                            <select class="form-select" id="detalle_solicitud_edit" name="detalle_solicitud" required>
                                {{-- Si ya hay un detalle, lo mostramos como seleccionado --}}
                                @if($detActual)
                                    <option value="{{ $detActual }}" selected>{{ $detActual }}</option>
                                @else
                                    <option value="" disabled selected>-- Seleccione Área primero --</option>
                                @endif
                            </select>
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

    {{-- Scripts --}}
    <script>
        function confirmarAnulacion() {
            if (confirm('¿Está seguro de que desea ANULAR esta solicitud?\n\nEsta acción no borrará el registro, pero lo sacará de la lista de pendientes.')) {
                document.getElementById('form-anular').submit();
            }
        }
    </script>

    <script>
        // Lógica para el selector dinámico del MODAL
        const categoriasDataEdit = @json($categorias);
        
        const catSelectEdit = document.getElementById('categoria_solicitud_edit');
        const detSelectEdit = document.getElementById('detalle_solicitud_edit');

        if(catSelectEdit) {
            catSelectEdit.addEventListener('change', function() {
                const catKey = this.value;
                
                // Limpiar opciones anteriores
                detSelectEdit.innerHTML = '<option value="" disabled selected>-- Seleccione Detalle --</option>';

                if (categoriasDataEdit[catKey]) {
                    categoriasDataEdit[catKey].opciones.forEach(opcion => {
                        const opt = document.createElement('option');
                        opt.value = opcion;
                        opt.textContent = opcion;
                        detSelectEdit.appendChild(opt);
                    });
                }
            });
        }
    </script>
@endsection