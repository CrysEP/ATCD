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
        <p>Ocurrió un error inesperado al intentar guardar los datos:</p>
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
                       <div class="form-group col-md-5">


    <label for="tipo_cedula">Cédula de identidad *</label>
    <div class="input-group">
        {{-- Dropdown para el tipo de cédula --}}
        <select class="form-select" id="tipo_cedula" name="tipo_cedula" style="padding: 4px 4px 4px 4px; width:5px;" required>
            <option value="V-" selected>V - Venezolano</option>
            <option value="E-">E - Extranjero</option>
            <option value="J-">J - Jurídico</option>
            <option value="P-">P - Pasaporte</option>
            <option value="G-">G - Gobierno</option>
        </select>
        {{-- Campo de texto para el número de cédula --}}
        <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Número de Cédula" required title="Ingrese su identificación según la letra que corresponda" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
    </div>
    @error('tipo_cedula')
        <div class="text-danger">{{ $message }}</div>
    @enderror
    @error('cedula')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
                        <div class="col-md-4">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" value="{{ old('nombres') }}" required title="Ingrese primer, segundo y/o tercer nombre" maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')"placeholder="Ej: Juan Carlos">
                        </div>
                        <div class="col-md-4">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required title="Ingrese apellidos" maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-ZñÑáéíóúÁÉÍÓÚ\s]/g, '')" placeholder="Ej: Molina Canto">
                        </div>

<div class="col-md-3">
    <label for="sexo" class="form-label">Género *</label>
    <select class="form-select" id="sexo" name="sexo" required>
        <option value="" disabled selected>-- Seleccione --</option>
        <option value="M" @selected(old('sexo') == 'M')>Masculino</option>
        <option value="F" @selected(old('sexo') == 'F')>Femenino</option>
    </select>
</div>


<div class="col-md-3">
    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
           value="{{ old('fecha_nacimiento') }}" required 
           max="{{ date('Y-m-d') }}"> {{-- Evita fechas futuras --}}
</div>


                        <div class="col-md-4">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" required title="Introduce un número válido de 11 dígitos. Ej: 04141234567 o 02121234567" maxlength="11" oninput="this.value = this.value.replace(/[^0-9\-\+\s\(\)]/g, '')" placeholder=" Ej: 04141234567 o 02121234567">
                        </div>
                        <div class="col-md-8">
                            <label for="email" class="form-label">Correo Electrónico *</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required title="Ejemplo correo@gmail.com, correo@outlook.com" maxlength="100" placeholder="Ej: a@gmail.com" pattern=".+@(gmail\.com|outlook\.com|hotmail\.com|yahoo\.com|live\.com)" {{-- Patrón para validar dominios específicos --}}
           pattern=".+@(gmail\.com|outlook\.com|hotmail\.com|yahoo\.com|live\.com)"
           {{-- Título que aparece si el patrón falla xd--}}
           title="Solo se permiten correos @gmail.com, @outlook.com, @hotmail.com, @yahoo.com o @live.com"
           {{-- Validación básica del navegador para el @ --}}
           oninvalid="this.setCustomValidity('Incluye un signo &quot;@&quot; en la dirección de correo. Falta un símbolo &quot;@&quot;.')"
           oninput="this.setCustomValidity('')">
                        </div>

                        <div class="col-12">
    <label for="direccion_habitacion" class="form-label">Dirección de Habitación *</label>
    <textarea class="form-control" id="direccion_habitacion" name="direccion_habitacion" rows="2" 
              placeholder="Av. Principal, Calle 5, Casa Nro 12..." required>{{ old('direccion_habitacion') }}</textarea>
</div>

<div class="col-12">
    <label for="punto_referencia" class="form-label">Punto de Referencia (Opcional)</label>
    <input type="text" class="form-control" id="punto_referencia" name="punto_referencia" 
           value="{{ old('punto_referencia') }}" placeholder="Ej: Frente a la plaza Bolívar">
</div>

                        <div class="col-md-6">
                            <label for="municipio_id" class="form-label">Municipio:</label>
                            <select class="form-select" id="municipio_id" name="municipio_id" required title="Seleccione un Municipio">
                                <option value="" disabled selected>-- Seleccione un Municipio --</option>
                                @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->CodMunicipio }}">{{ $municipio->NombreMunicipio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="parroquia_id" class="form-label">Parroquia:</label>
                            <select class="form-select" id="parroquia_id" name="parroquia_id" required Title="Seleccione un Municipio primero y luego su Parroquia">
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
                       {{-- CAMPO 1: FECHA DE SOLICITUD (LA NUEVA) --}}
    <div class="col-md-3">
        <label for="fecha_solicitud" class="form-label">Fecha de Solicitud (Planilla):</label>
        <input type="datetime-local" class="form-control" id="fecha_solicitud" name="fecha_solicitud" value="{{ old('fecha_solicitud', now()->format('Y-m-d\TH:i')) }}" required title="Fecha en la que se llenó de la planilla">
    </div>

    {{-- CAMPO 2: FECHA DE ATENCIÓN (LA EXISTENTE) --}}
    <div class="col-md-3">
        <label for="fecha_atencion" class="form-label">Fecha de Atención (Recepción):</label>
        <input type="datetime-local" class="form-control" id="fecha_atencion" name="fecha_atencion" value="{{ old('fecha_atencion', now()->format('Y-m-d\TH:i')) }}" required>
    </div>
                        <div class="col-md-3">
                            <label for="nro_uac" class="form-label">Nro. UAC:</label>
                            <input type="text" class="form-control" id="nro_uac" name="nro_uac" value="{{ old('nro_uac') }}" maxlength="10" placeholder="UAC-XXXXX" required title="Ingrese el código asignado la Unidad de Atención al Ciudadano">
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
    <label for="tipo_ente" class="form-label">Categoría (Ente) *:</label>
    {{-- Asegúrate que el name sea "tipo_ente" --}}
    <select class="form-select" id="tipo_ente" name="tipo_ente" required>
        <option value="" disabled selected>-- Seleccione --</option>
        @foreach ($tiposEnte as $ente)
            {{-- Mostramos el Nombre y el Prefijo para guiar al usuario --}}
            <option value="{{ $ente->CodTipoEnte }}" @selected(old('tipo_ente') == $ente->CodTipoEnte)>
                {{ $ente->NombreEnte }} ({{ $ente->PrefijoCodigo }})
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-6">
    <label for="tipo_solicitud_planilla" class="form-label">Tipo de Planilla:</label>
    <select class="form-select" id="tipo_solicitud_planilla" name="tipo_solicitud_planilla" required>
        <option value="Solicitud o Petición" @selected(old('tipo_solicitud_planilla') == 'Solicitud o Petición')>Solicitud o Petición</option>
        <option value="Quejas, reclamos o sugerencias" @selected(old('tipo_solicitud_planilla') == 'Quejas, reclamos o sugerencias')>Quejas, reclamos o sugerencias</option>
        <option value="Denuncia" @selected(old('tipo_solicitud_planilla') == 'Denuncia')>Denuncia</option>
    </select>
</div>

{{-- CONTENEDOR PARA CLASIFICACIÓN (Para ocultarlo/mostrarlo) --}}
<div class="col-12" id="clasificacion_wrapper">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="categoria_solicitud" class="form-label">Clasificación (Área) *:</label>
            <select class="form-select" id="categoria_solicitud" name="categoria_solicitud">
                <option value="" disabled selected>-- Seleccione Área --</option>
                @foreach ($categorias as $key => $cat)
                    <option value="{{ $key }}">{{ $cat['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label for="detalle_solicitud" class="form-label">Detalle Específico *:</label>
            <select class="form-select" id="detalle_solicitud" name="detalle_solicitud">
                <option value="" disabled selected>-- Seleccione primero el Área --</option>
            </select>
        </div>
    </div>
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
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required maxlength="1000">{{ old('descripcion') }}</textarea>
                        </div>
                        
                        <div class="col-12">
                            <label for="instruccion_presidencia" class="form-label">Instrucción de Presidencia (Opcional):</label>
                            <textarea class="form-control" id="instruccion_presidencia" name="instruccion_presidencia" rows="2">{{ old('instruccion_presidencia') }}</textarea>
                        </div>
                    </div>


{{-- SECCIÓN DE CONTROL DE FOLIOS Y ANEXOS FÍSICOS --}}
<div class="col-12 mt-4">
    <div class="p-3 border rounded bg-light">
        <label class="form-label fw-bold mb-2">Control de Anexos Físicos (Expediente):</label>
        
        <div class="row align-items-center g-3">
            {{-- 1. ¿Anexa Documentos? (Radio Buttons estilo Switch) --}}
            <div class="col-md-3">
                <label class="d-block text-muted small mb-1">¿Anexa Documentos?</label>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="AnexaDocumentos" id="anexa_si" value="1" 
                           {{ old('AnexaDocumentos') == '1' ? 'checked' : '' }} onclick="toggleAnexos(true)">
                    <label class="btn btn-outline-success" for="anexa_si">SI</label>

                    <input type="radio" class="btn-check" name="AnexaDocumentos" id="anexa_no" value="0" 
                           {{ old('AnexaDocumentos', '0') == '0' ? 'checked' : '' }} onclick="toggleAnexos(false)">
                    <label class="btn btn-outline-danger" for="anexa_no">NO</label>
                </div>
            </div>

            {{-- 2. Cantidad de Originales --}}
            <div class="col-md-3">
                <label for="CantidadDocumentosOriginal" class="form-label small">Originales</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-file-earmark"></i></span>
                    <input type="number" class="form-control" id="CantidadDocumentosOriginal" 
                           name="CantidadDocumentosOriginal" 
                           value="{{ old('CantidadDocumentosOriginal', 0) }}" min="0" 
                           placeholder="0">
                </div>
            </div>

            {{-- 3. Cantidad de Copias --}}
            <div class="col-md-3">
                <label for="CantidadDocumentoCopia" class="form-label small">Copias</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-files"></i></span>
                    <input type="number" class="form-control" id="CantidadDocumentoCopia" 
                           name="CantidadDocumentoCopia" 
                           value="{{ old('CantidadDocumentoCopia', 0) }}" min="0" 
                           placeholder="0">
                </div>
            </div>

            {{-- 4. Cantidad de Páginas (Folios) --}}
            <div class="col-md-3">
                <label for="CantidadPaginasAnexo" class="form-label small">Total Páginas (Anexos)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-bookmarks"></i></span>
                    <input type="number" class="form-control" id="CantidadPaginasAnexo" 
                           name="CantidadPaginasAnexo" 
                           value="{{ old('CantidadPaginasAnexo', 0) }}" min="0" 
                           placeholder="0">
                </div>
            </div>
        </div>
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

<script>
    // Pasamos el array de PHP a JS
    const categoriasData = @json($categorias);

    document.getElementById('categoria_solicitud').addEventListener('change', function() {
        const categoriaKey = this.value;
        const detalleSelect = document.getElementById('detalle_solicitud');
        
        // Limpiar opciones anteriores
        detalleSelect.innerHTML = '<option value="" disabled selected>-- Seleccione Detalle --</option>';

        if (categoriasData[categoriaKey]) {
            // Llenar con las nuevas opciones
            categoriasData[categoriaKey].opciones.forEach(opcion => {
                const opt = document.createElement('option');
                opt.value = opcion;
                opt.textContent = opcion;
                detalleSelect.appendChild(opt);
            });
        }
    });
</script>

<script>
    // LÓGICA PARA OCULTAR/MOSTRAR CLASIFICACIÓN
    const tipoPlanillaSelect = document.getElementById('tipo_solicitud_planilla');
    const clasificacionWrapper = document.getElementById('clasificacion_wrapper');
    const catSelect = document.getElementById('categoria_solicitud');
    const detSelect = document.getElementById('detalle_solicitud');

    function toggleClasificacion() {
        if (tipoPlanillaSelect.value === 'Solicitud o Petición') {
            // Mostrar
            clasificacionWrapper.style.display = 'block';
            // Hacer requeridos
            catSelect.required = true;
            detSelect.required = true;
        } else {
            // Ocultar
            clasificacionWrapper.style.display = 'none';
            // Quitar requeridos para que no bloquee el envío
            catSelect.required = false;
            detSelect.required = false;
            // Limpiar valores para no enviar basura
            catSelect.value = "";
            detSelect.value = "";
        }
    }

    // Ejecutar al cargar y al cambiar
    tipoPlanillaSelect.addEventListener('change', toggleClasificacion);
    toggleClasificacion(); // Para aplicar el estado inicial correcto
</script>

{{-- SCRIPT PARA BLOQUEAR/DESBLOQUEAR INPUTS --}}
<script>
    function toggleAnexos(active) {
        const inputs = [
            document.getElementById('CantidadDocumentosOriginal'),
            document.getElementById('CantidadDocumentoCopia'),
            document.getElementById('CantidadPaginasAnexo')
        ];

        inputs.forEach(input => {
            input.disabled = !active;
            if (!active) input.value = 0; // Reiniciar a 0 si marca NO
        });
    }

    // Ejecutar al cargar para validar estado inicial (por si viene de un old input)
    document.addEventListener("DOMContentLoaded", function() {
        const isSiChecked = document.getElementById('anexa_si').checked;
        toggleAnexos(isSiChecked);
    });
</script>

@endsection