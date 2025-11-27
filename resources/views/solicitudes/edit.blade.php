@extends('layouts.app')

@section('title', 'Editar Solicitud #' . ($solicitud->Nro_UAC ?? $solicitud->CodSolicitud))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        
        {{-- IMPORTANTE: Ruta al update y método PUT --}}
        <form method="POST" action="{{ route('solicitudes.update', $solicitud->CodSolicitud) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h2 class="mb-4 text-center">Editar Solicitud</h2>

            {{-- Mostrar errores si existen (igual que en create) --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
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
            {{-- 1. Datos del Solicitante --}}
            <div class="card card-gradient-body shadow-sm mb-4 border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">1. Datos del Solicitante</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                    
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
                                       value="{{ old('cedula', $cedula_numero_actual) }}"  title="Cambie su identificación según la letra que corresponda" maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nombres *</label>
                            <input type="text" class="form-control" name="nombres" value="{{ old('nombres', $solicitud->persona->NombresPersona) }}" maxlength="100" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" name="apellidos" value="{{ old('apellidos', $solicitud->persona->ApellidosPersona) }}" maxlength="100" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                        </div>


                        <div class="col-md-3">
    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
    <input type="date" class="form-control" name="fecha_nacimiento" 
           {{-- Obtenemos la fecha de la BD o la anterior si falló la validación --}}
           value="{{ old('fecha_nacimiento', $solicitud->persona->FechaNacPersona ? \Carbon\Carbon::parse($solicitud->persona->FechaNacPersona)->format('Y-m-d') : '') }}" 
           required max="{{ date('Y-m-d') }}">
</div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" name="telefono" value="{{ old('telefono', $solicitud->persona->TelefonoPersona) }}" maxlength="14" oninput="this.value = this.value.replace(/[^0-9\-\+\s\(\)]/g, '')">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $solicitud->persona->CorreoElectronicoPersona) }}" maxlength="200">
                        </div>

                        {{-- MUNICIPIO Y PARROQUIA (Lógica JS necesaria abajo) --}}
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

                           
                        {{-- Recorremos los municipios para encontrar el actual y mostrar sus parroquias --}}
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

            {{-- 2. Detalles de la Solicitud --}}
            <div class="card card-gradient-body shadow-sm mb-4 border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">2. Detalles de la Solicitud</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        {{-- FECHAS CON HORA --}}
                        <div class="col-md-3">
                            <label class="form-label">Fecha y Hora (Planilla):</label>
                            <input type="datetime-local" class="form-control" name="fecha_solicitud" 
                                   value="{{ old('fecha_solicitud', $solicitud->FechaSolicitud->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha y Hora (Recepción):</label>
                            <input type="datetime-local" class="form-control" name="fecha_atencion" 
                                   value="{{ old('fecha_atencion', \Carbon\Carbon::parse($solicitud->FechaAtención)->format('Y-m-d\TH:i')) }}" required>
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

            <div class="d-flex justify-content-center gap-3 mb-4">
                <a href="{{ route('solicitudes.show', $solicitud->CodSolicitud) }}" class="btn btn-secondary btn-lg">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Reutiliza el mismo script de Municipios/Parroquias de create.blade.php
    // Asegúrate de que cargue las parroquias correctas al cambiar el municipio
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
@endsection