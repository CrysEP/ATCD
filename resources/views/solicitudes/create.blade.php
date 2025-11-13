@extends('layouts.app')

@section('title', 'Registrar Nueva Solicitud')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data">
            @csrf <h2 class="mb-4 text-center">Formulario de Registro de Solicitud</h2>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">¡Error en el formulario!</h4>
                    <p>Por favor, corrige los siguientes campos:</p>
                    <hr>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">¡Error al Guardar!</h4>
        <p>Ocurrió un error inesperado al intentar guardar en la base de datos:</p>
        <hr>
        <p class="mb-0">{{ session('error') }}</p>
    </div>
@endif


            <div class="card card-gradient-body shadow-sm mb-4 border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">1. Datos del Solicitante</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" value="{{ old('cedula') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" value="{{ old('nombres') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label for="email" class="form-label">Correo Electrónico (Opcional)</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="municipio_id" class="form-label">Municipio:</label>
                            <select class="form-select" id="municipio_id" name="municipio_id" required>
                                <option value="" disabled selected>-- Seleccione un Municipio --</option>
                                @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->CodMunicipio }}">{{ $municipio->NombreMunicipio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="parroquia_id" class="form-label">Parroquia:</label>
                            <select class="form-select" id="parroquia_id" name="parroquia_id" required>
                                <option value="">-- Seleccione un Municipio primero --</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-gradient-body shadow-sm mb-4 border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">2. Detalles de la Solicitud</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="fecha_atencion" class="form-label">Fecha de Recepción:</label>
                            <input type="date" class="form-control" id="fecha_atencion" name="fecha_atencion" value="{{ old('fecha_atencion', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="nro_uac" class="form-label">Nro. UAC (Opcional):</label>
                            <input type="text" class="form-control" id="nro_uac" name="nro_uac" value="{{ old('nro_uac') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="tipo_solicitante" class="form-label">Tipo de Solicitante:</label>
                            <select class="form-select" id="tipo_solicitante" name="tipo_solicitante" required>
                                <option value="Personal" @if(old('tipo_solicitante') == 'Personal') selected @endif>Personal</option>
                                <option value="Institucional" @if(old('tipo_solicitante') == 'Institucional') selected @endif>Institucional</option>
                                <option value="Consejo Comunal" @if(old('tipo_solicitante') == 'Consejo Comunal') selected @endif>Consejo Comunal</option>
                            </select>
                        </div>
                         <div class="col-md-3">
                            <label for="tipo_ente" class="form-label">Categoría (Ente):</label>
                            <select class="form-select" id="tipo_ente" name="tipo_ente">
                                <option value="">-- Seleccione Categoría --</option>
                                @foreach ($tiposEnte as $ente)
                                    <option value="{{ $ente->CodTipoEnte }}">{{ $ente->NombreEnte }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo_solicitud_planilla" class="form-label">Tipo de Planilla:</label>
                            <select class="form-select" id="tipo_solicitud_planilla" name="tipo_solicitud_planilla" required>
                                <option value="Solicitud o Petición" @if(old('tipo_solicitud_planilla') == 'Solicitud o Petición') selected @endif>Solicitud o Petición</option>
                                <option value="Quejas, reclamos o sugerencias" @if(old('tipo_solicitud_planilla') == 'Quejas, reclamos o sugerencias') selected @endif>Quejas, reclamos o sugerencias</option>
                                <option value="Denuncia" @if(old('tipo_solicitud_planilla') == 'Denuncia') selected @endif>Denuncia</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="nivel_urgencia" class="form-label">Nivel de Urgencia:</label>
                            <select class="form-select" id="nivel_urgencia" name="nivel_urgencia" required>
                                <option value="Bajo" @if(old('nivel_urgencia') == 'Bajo') selected @endif>Bajo</option>
                                <option value="Medio" @if(old('nivel_urgencia') == 'Medio') selected @endif>Medio</option>
                                <option value="Alto" @if(old('nivel_urgencia') == 'Alto') selected @endif>Alto</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción de la Solicitud:</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required>{{ old('descripcion') }}</textarea>
                        </div>
                        
                        <div class="col-12">
                            <label for="instruccion_presidencia" class="form-label">Instrucción de Presidencia (Opcional):</label>
                            <textarea class="form-control" id="instruccion_presidencia" name="instruccion_presidencia" rows="2">{{ old('instruccion_presidencia') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-gradient-body shadow-sm mb-4 border-0">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">3. Datos del Funcionario</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                 @if ($funcionario->funcionarioData)
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre:</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $funcionario->persona->NombreCompleto ?? $funcionario->NombreUsuario }}" 
                                           disabled readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cédula:</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $funcionario->persona->CedulaPersona ?? 'N/A' }}" 
                                           disabled readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cargo:</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $funcionario->funcionarioData->CargoFuncionario }}" 
                                           disabled readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adscripción (Unidad):</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $funcionario->funcionarioData->AdscripciónFuncionario }}" 
                                           disabled readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Firma Digital Registrada:</label>
                            @if ($funcionario->funcionarioData->FirmaDigitalBase64)
                                <div class="border rounded p-2 text-center" style="background-color: #fff;">
                                    <img src="{{ $funcionario->funcionarioData->FirmaDigitalBase64 }}" 
                                         alt="Firma Digital" 
                                         style="max-height: 100px; max-width: 100%;">
                                </div>
                            @else
                                <div class="border rounded p-2 text-center text-muted" style="background-color: #fff; height: 100%;">
                                    <small>(Sin firma registrada)</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-danger">
                        No se encontró un registro de 'Funcionario' asociado a su Cédula ({{ $funcionario->persona->CedulaPersona ?? 'N/A' }}). 
                        Contacte al administrador.
                    </p>
                @endif
                    </div>
                </div>
            </div>
        </div>

            <div class="card card-gradient-body shadow-sm mb-4 border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">4. Archivos Adjuntos (Opcional)</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="archivos" class="form-label">Seleccione uno o varios archivos (PDF, JPG, PNG, Excel...):</label>
                        <input class="form-control" type="file" id="archivos" name="archivos[]" multiple>
                    </div>
                </div>
            </div>

            
       <div class="d-flex justify-content-center gap-3 mb-4">
    
                <button onclick="history.back()" type="button" class="btn btn-secondary btn-lg">
                    Volver
                </button>

                <button type="submit" class="btn btn-primary btn-lg">
                    Registrar Solicitud
                </button>

            </div>
            
        </form>

    </div>
</div>

<script>
    // 1. Almacenar los datos de Municipios y Parroquias
    // Convertimos la variable de Blade (PHP) a una variable de JavaScript
    const municipiosData = @json($municipios);

    // 2. Obtener referencias a los elementos del DOM
    const municipioSelect = document.getElementById('municipio_id');
    const parroquiaSelect = document.getElementById('parroquia_id');

    // 3. Escuchar el evento 'change' en el select de municipios
    municipioSelect.addEventListener('change', function() {
        // Limpiar el select de parroquias
        parroquiaSelect.innerHTML = '<option value="">-- Cargando parroquias... --</option>';

        // Obtener el ID del municipio seleccionado
        const municipioId = this.value;

        // Encontrar el municipio en nuestros datos
        const municipioSeleccionado = municipiosData.find(
            municipio => municipio.CodMunicipio == municipioId
        );

        // Si se encontró el municipio y tiene parroquias
        if (municipioSeleccionado && municipioSeleccionado.parroquias) {
            // Volver a limpiar para poner las opciones correctas
            parroquiaSelect.innerHTML = '<option value="" disabled selected>-- Seleccione una Parroquia --</option>';

            // Llenar el select con las parroquias correspondientes
            municipioSeleccionado.parroquias.forEach(parroquia => {
                const option = document.createElement('option');
                option.value = parroquia.CodParroquia;
                option.textContent = parroquia.NombreParroquia;
                parroquiaSelect.appendChild(option);
            });
        } else {
            // Si no se encuentran parroquias
            parroquiaSelect.innerHTML = '<option value="">-- No se encontraron parroquias --</option>';
        }
    });
</script>

@endsection