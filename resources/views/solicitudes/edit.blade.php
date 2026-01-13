@extends('layouts.app')

@section('title', 'Editar Solicitud #' . ($solicitud->Nro_UAC ?? $solicitud->CodSolicitud))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        
        {{-- FORMULARIO PRINCIPAL DE EDICIÓN DE DATOS --}}
        <form method="POST" action="{{ route('solicitudes.update', $solicitud->CodSolicitud) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h2 class="mb-4 text-center">Editar Solicitud</h2>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Error:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- 1. DATOS DEL SOLICITANTE --}}
            <div class="card card-gradient-body shadow-sm mb-4 border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">1. Datos del Solicitante</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                    
                        {{-- CÉDULA --}}
                        <div class="col-md-4">
                            <label for="tipo_cedula" class="form-label">Cédula *</label>
                            <div class="input-group">
                                <select class="form-select" id="tipo_cedula" name="tipo_cedula" style="max-width: 80px;">
                                    <option value="V-" @selected(old('tipo_cedula', $tipo_cedula_actual) == 'V-')>V-</option>
                                    <option value="E-" @selected(old('tipo_cedula', $tipo_cedula_actual) == 'E-')>E-</option>
                                    <option value="J-" @selected(old('tipo_cedula', $tipo_cedula_actual) == 'J-')>J-</option>
                                    <option value="P-" @selected(old('tipo_cedula', $tipo_cedula_actual) == 'P-')>P-</option>
                                    <option value="G-" @selected(old('tipo_cedula', $tipo_cedula_actual) == 'G-')>G-</option>
                                </select>
                                <input type="text" class="form-control" id="cedula" name="cedula" 
                                       value="{{ old('cedula', $cedula_numero_actual) }}"  
                                       title="Solo números" maxlength="20" 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nombres *</label>
                            <input type="text" class="form-control" name="nombres" 
                                   value="{{ old('nombres', $solicitud->persona->NombresPersona) }}" 
                                   maxlength="100" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" name="apellidos" 
                                   value="{{ old('apellidos', $solicitud->persona->ApellidosPersona) }}" 
                                   maxlength="100" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                        </div>

                        {{-- GÉNERO --}}
                        <div class="col-md-3">
                            <label for="sexo" class="form-label">Género *</label>
                            <select class="form-select" id="sexo" name="sexo" required>
                                <option value="M" @selected(old('sexo', $solicitud->persona->SexoPersona) == 'M')>Masculino</option>
                                <option value="F" @selected(old('sexo', $solicitud->persona->SexoPersona) == 'F')>Femenino</option>
                            </select>
                        </div>

                        {{-- FECHA NACIMIENTO --}}
                        <div class="col-md-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha Nacimiento *</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" 
                                   value="{{ old('fecha_nacimiento', $solicitud->persona->FechaNacPersona ? \Carbon\Carbon::parse($solicitud->persona->FechaNacPersona)->format('Y-m-d') : '') }}" 
                                   required max="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" name="telefono" 
                                   value="{{ old('telefono', $solicitud->persona->TelefonoPersona) }}" 
                                   maxlength="11" oninput="this.value = this.value.replace(/[^0-9\-\+\s\(\)]/g, '')">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" 
                                   value="{{ old('email', $solicitud->persona->CorreoElectronicoPersona) }}" maxlength="200">
                        </div>

                        {{-- DIRECCIÓN --}}
                        <div class="col-12">
                            <label for="direccion_habitacion" class="form-label">Dirección de Habitación *</label>
                            <textarea class="form-control" id="direccion_habitacion" name="direccion_habitacion" rows="2" >{{ old('direccion_habitacion', $solicitud->DirecciónHabitación) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label for="punto_referencia" class="form-label">Punto de Referencia (Opcional)</label>
                            <input type="text" class="form-control" id="punto_referencia" name="punto_referencia" 
                                   value="{{ old('punto_referencia', $solicitud->PuntoReferencia) }}">
                        </div>

                        {{-- MUNICIPIO Y PARROQUIA --}}
                        <div class="col-md-6">
                            <label class="form-label">Municipio:</label>
                            <select class="form-select" id="municipio_id" name="municipio_id" required>
                                @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->CodMunicipio }}" 
                                        @selected($municipio->CodMunicipio == $solicitud->persona->parroquia->Municipio_FK)>
                                        {{ $municipio->NombreMunicipio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parroquia:</label>
                           <select class="form-select" id="parroquia_id" name="parroquia_id" required>
                                {{-- Recorremos para mostrar las parroquias del municipio actual --}}
                                @foreach ($municipios as $m)
                                    @if($m->CodMunicipio == $solicitud->persona->parroquia->Municipio_FK)
                                        @foreach($m->parroquias as $p)
                                            <option value="{{ $p->CodParroquia }}" 
                                                @selected($p->CodParroquia == $solicitud->persona->ParroquiaPersona_FK)>
                                                {{ $p->NombreParroquia }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. DETALLES DE LA SOLICITUD --}}
            <div class="card card-gradient-body shadow-sm mb-4 border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">2. Detalles de la Solicitud</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Fecha y Hora (Creación):</label>
                            <input type="datetime-local" class="form-control" name="fecha_solicitud" 
                                   value="{{ old('fecha_solicitud', $solicitud->FechaSolicitud->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha y Hora (Recepción):</label>
                            <input type="datetime-local" class="form-control" name="fecha_atencion" 
                                   value="{{ old('fecha_atencion', \Carbon\Carbon::parse($solicitud->FechaAtención)->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Código Interno:</label>
                            <input type="text" class="form-control" name="codigo_interno" 
                                   value="{{ old('codigo_interno', $solicitud->correspondencia->CodigoInterno) }}" 
                                   required maxlength="100">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Nro. UAC:</label>
                            <input type="text" class="form-control" name="nro_uac" value="{{ old('nro_uac', $solicitud->Nro_UAC) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo Solicitante:</label>
                            <select class="form-select" name="tipo_solicitante">
                                <option value="Personal" @selected($solicitud->TipoSolicitante == 'Personal')>Personal</option>
                                <option value="Institucional" @selected($solicitud->TipoSolicitante == 'Institucional')>Institucional</option>
                                <option value="Consejo Comunal" @selected($solicitud->TipoSolicitante == 'Consejo Comunal')>Consejo Comunal</option>
                            </select>
                        </div>

                        {{-- ENTE (CATEGORÍA) --}}
                        <div class="col-md-6">
                            <label class="form-label">Ente (Categoría) *</label>
                            <select class="form-select" name="tipo_ente" required>
                                @foreach ($tiposEnte as $ente)
                                    <option value="{{ $ente->CodTipoEnte }}" 
                                        @selected($solicitud->correspondencia->TipoEnte_FK == $ente->CodTipoEnte)>
                                        {{ $ente->NombreEnte }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tipo de Planilla:</label>
                            <select class="form-select" name="tipo_solicitud_planilla">
                                <option value="Solicitud o Petición" @selected($solicitud->TipoSolicitudPlanilla == 'Solicitud o Petición')>Solicitud o Petición</option>
                                <option value="Quejas, reclamos o sugerencias" @selected($solicitud->TipoSolicitudPlanilla == 'Quejas, reclamos o sugerencias')>Quejas...</option>
                                <option value="Denuncia" @selected($solicitud->TipoSolicitudPlanilla == 'Denuncia')>Denuncia</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Urgencia:</label>
                            <select class="form-select" name="nivel_urgencia">
                                <option value="Bajo" @selected($solicitud->NivelUrgencia == 'Bajo')>Bajo</option>
                                <option value="Medio" @selected($solicitud->NivelUrgencia == 'Medio')>Medio</option>
                                <option value="Alto" @selected($solicitud->NivelUrgencia == 'Alto')>Alto</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción:</label>
                            <textarea class="form-control" name="descripcion" rows="4" required>{{ old('descripcion', $solicitud->DescripcionSolicitud) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTONES DE GUARDADO --}}
            <div class="d-flex justify-content-center gap-3 mb-4">
                <a href="{{ route('solicitudes.show', $solicitud->CodSolicitud) }}" class="btn btn-secondary btn-lg">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
            </div>
        </form>


        <hr class="my-5">

        {{-- SECCIÓN GESTIÓN DE ARCHIVOS --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-paperclip"></i> Gestión de Archivos Adjuntos</h5>
            </div>
            <div class="card-body">
                
                {{-- 1. LISTA DE ARCHIVOS EXISTENTES --}}
                @if($solicitud->archivos->count() > 0)
                    <h6 class="fw-bold">Archivos Actuales:</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre del Archivo</th>
                                    <th>Tipo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($solicitud->archivos as $archivo)
                                    <tr>
                                        <td>
                                            <a href="{{ route('solicitudes.downloadFile', $archivo->id) }}" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-file-earmark-text"></i> {{ $archivo->nombre_original }}
                                            </a>
                                        </td>
                                        <td>{{ $archivo->tipo_archivo }}</td>
                                        <td>
                                            <div class="d-flex gap-4">
                                                
                                                {{-- BOTÓN PREVISUALIZAR --}}
                                                @if(in_array($archivo->tipo_archivo, ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']))
                                                    <button type="button" class="btn btn-sm btn-info text-white" 
                                                            onclick="abrirModalPreview(this)"
                                                            data-url="{{ route('solicitudes.verArchivo', $archivo->id) }}"
                                                            data-tipo="{{ $archivo->tipo_archivo }}"
                                                            data-nombre="{{ $archivo->nombre_original }}">
                                                        <i class="bi bi-eye-fill"></i> Ver
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">Vista no disponible</span>
                                                @endif

                                                {{-- BOTÓN ELIMINAR --}}
                                                <form action="{{ route('archivos.eliminar', $archivo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este archivo? Esta acción es irreversible.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        No hay archivos adjuntos en esta solicitud.
                    </div>
                @endif

                {{-- 2. FORMULARIO PARA SUBIR NUEVOS --}}
                <div class="p-3 border rounded bg-light">
                    <h6 class="fw-bold mb-3"><i class="bi bi-cloud-upload"></i> Subir Nuevos Archivos</h6>
                    
                    <form action="{{ route('solicitudes.subirArchivo', $solicitud->CodSolicitud) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-9">
                                <label for="nuevos_archivos" class="form-label">Seleccionar documentos (PDF, Imágenes, Excel)</label>
                                <input type="file" class="form-control" name="nuevos_archivos[]" id="nuevos_archivos" multiple accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx">
                            </div>
                            <div class="col-md-3 mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-upload"></i> Cargar Archivos
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- SCRIPT PARA MUNICIPIOS/PARROQUIAS --}}
<script>
    const municipiosData = @json($municipios);
    const municipioSelect = document.getElementById('municipio_id');
    const parroquiaSelect = document.getElementById('parroquia_id');

    municipioSelect.addEventListener('change', function() {
        parroquiaSelect.innerHTML = '<option value="">-- Cargando... --</option>';
        const municipioId = this.value;
        const municipioSeleccionado = municipiosData.find(m => m.CodMunicipio == municipioId);
        
        if (municipioSeleccionado && municipioSeleccionado.parroquias) {
            parroquiaSelect.innerHTML = '<option value="" disabled selected>-- Seleccione Parroquia --</option>';
            municipioSeleccionado.parroquias.forEach(parroquia => {
                const option = document.createElement('option');
                option.value = parroquia.CodParroquia;
                option.textContent = parroquia.NombreParroquia;
                parroquiaSelect.appendChild(option);
            });
        }
    });
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
                {{-- Visor PDF --}}
                <iframe id="visorPDF" src="" style="width: 100%; height: 100%; border: none; display: none;"></iframe>
                {{-- Visor Imagen --}}
                <img id="visorImagen" src="" class="img-fluid" style="max-height: 100%; max-width: 100%; display: none;" alt="Vista previa">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- SCRIPT PARA LA PREVISUALIZACIÓN --}}
<script>
    // Variable global para la instancia del modal
    let modalPreviewInstance = null;

    function abrirModalPreview(boton) {
        // 1. Obtener datos del botón (seguro contra caracteres extraños)
        const url = boton.getAttribute('data-url');
        const tipo = boton.getAttribute('data-tipo');
        const nombre = boton.getAttribute('data-nombre');

        // 2. Asignar título
        document.getElementById('tituloArchivo').textContent = nombre;

        // 3. Referencias a elementos
        const iframe = document.getElementById('visorPDF');
        const img = document.getElementById('visorImagen');
        
        // 4. Resetear visualización
        iframe.style.display = 'none';
        iframe.src = ''; 
        img.style.display = 'none';
        img.src = '';

        // 5. Cargar contenido según tipo
        if (tipo === 'application/pdf') {
            iframe.src = url;
            iframe.style.display = 'block';
        } else {
            // Imágenes
            img.src = url;
            img.style.display = 'block';
        }

        // 6. Abrir Modal (Manejo correcto de instancia Bootstrap 5)
        const modalEl = document.getElementById('modalPrevisualizacion');
        
        if (!modalPreviewInstance) {
            modalPreviewInstance = new bootstrap.Modal(modalEl);
        }
        modalPreviewInstance.show();
    }
    
    // Limpiar src al cerrar
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('modalPrevisualizacion');
        if(modalEl){
            modalEl.addEventListener('hidden.bs.modal', function () {
                document.getElementById('visorPDF').src = '';
                document.getElementById('visorImagen').src = '';
            });
        }
    });
</script>

@endsection