@extends('layouts.app') @section('content')
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrar Nueva Solicitud
            </h2>
        </div>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">¡Éxito!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">¡Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Por favor, corrige los siguientes errores:</strong>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data">
                        @csrf <h3 class="text-lg font-semibold border-b pb-2 mb-4">Datos de la Solicitud</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <div>
                                <label for="tipo_ente_id" class="block font-medium text-sm text-gray-700">Categoría (Tipo de Ente) *</label>
                                <select name="tipo_ente_id" id="tipo_ente_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled {{ old('tipo_ente_id') ? '' : 'selected' }}>-- Seleccione --</option>
                                    @foreach($tiposEnte as $ente)
                                        <option value="{{ $ente->CodTipoEnte }}" {{ old('tipo_ente_id') == $ente->CodTipoEnte ? 'selected' : '' }}>
                                            {{ $ente->NombreEnte }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tipo_ente_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="nro_uac" class="block font-medium text-sm text-gray-700">Nro. UAC (Opcional)</label>
                                <input type="text" name="nro_uac" id="nro_uac" value="{{ old('nro_uac') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej: UAC-2025-050">
                                @error('nro_uac')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tipo_solicitud_planilla" class="block font-medium text-sm text-gray-700">Tipo de Planilla *</label>
                                <select name="tipo_solicitud_planilla" id="tipo_solicitud_planilla" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="Solicitud o Petición" {{ old('tipo_solicitud_planilla') == 'Solicitud o Petición' ? 'selected' : '' }}>Solicitud o Petición</option>
                                    <option value="Quejas, reclamos o sugerencias" {{ old('tipo_solicitud_planilla') == 'Quejas, reclamos o sugerencias' ? 'selected' : '' }}>Quejas, reclamos o sugerencias</option>
                                    <option value="Denuncia" {{ old('tipo_solicitud_planilla') == 'Denuncia' ? 'selected' : '' }}>Denuncia</option>
                                </select>
                                @error('tipo_solicitud_planilla')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tipo_solicitante" class="block font-medium text-sm text-gray-700">Tipo de Solicitante *</label>
                                <select name="tipo_solicitante" id="tipo_solicitante" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="Personal" {{ old('tipo_solicitante') == 'Personal' ? 'selected' : '' }}>Personal</option>
                                    <option value="Institucional" {{ old('tipo_solicitante') == 'Institucional' ? 'selected' : '' }}>Institucional</option>
                                    <option value="Consejo Comunal" {{ old('tipo_solicitante') == 'Consejo Comunal' ? 'selected' : '' }}>Consejo Comunal</option>
                                </select>
                                @error('tipo_solicitante')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="nivel_urgencia" class="block font-medium text-sm text-gray-700">Nivel de Urgencia *</label>
                                <select name="nivel_urgencia" id="nivel_urgencia" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="Bajo" {{ old('nivel_urgencia') == 'Bajo' ? 'selected' : '' }}>Bajo</option>
                                    <option value="Medio" {{ old('nivel_urgencia') == 'Medio' ? 'selected' : '' }}>Medio</option>
                                    <option value="Alto" {{ old('nivel_urgencia') == 'Alto' ? 'selected' : '' }}>Alto</option>
                                </select>
                                @error('nivel_urgencia')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div> <h3 class="text-lg font-semibold border-b pb-2 mb-4 mt-8">Datos del Solicitante</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <div>
                                <label for="cedula" class="block font-medium text-sm text-gray-700">Cédula *</label>
                                <input type="text" name="cedula" id="cedula" value="{{ old('cedula') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                @error('cedula')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="nombres" class="block font-medium text-sm text-gray-700">Nombres *</label>
                                <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                @error('nombres')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="apellidos" class="block font-medium text-sm text-gray-700">Apellidos *</label>
                                <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                @error('apellidos')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="telefono" class="block font-medium text-sm text-gray-700">Teléfono *</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                @error('telefono')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700">Correo Electrónico (Opcional)</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            </div>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                             <div>
                                <label for="municipio_select" class="block font-medium text-sm text-gray-700">Municipio *</label>
                                <select id="municipio_select" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled {{ old('parroquia_id') ? '' : 'selected' }}>-- Seleccione un Municipio --</option>
                                    @foreach($municipios as $municipio)
                                        <option value="{{ $municipio->CodMunicipio }}" data-parroquias="{{ $municipio->parroquias->toJson() }}">
                                            {{ $municipio->NombreMunicipio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="parroquia_id" class="block font-medium text-sm text-gray-700">Parroquia *</label>
                                <select name="parroquia_id" id="parroquia_select" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>-- Seleccione una Parroquia --</option>
                                    </select>
                                @error('parroquia_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div> <h3 class="text-lg font-semibold border-b pb-2 mb-4 mt-8">Detalles de la Solicitud</h3>
                        <div class="grid grid-cols-1 gap-6">

                            <div>
                                <label for="descripcion" class="block font-medium text-sm text-gray-700">Descripción de la Solicitud *</label>
                                <textarea name="descripcion" id="descripcion" rows="5" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div> 

                            <h3 class="text-lg font-semibold border-b pb-2 mb-4 mt-8">Funcionario Responsable (Receptor)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg border">
                            
                            <div>
                                <label class="block font-medium text-sm text-gray-500">Registrado por:</label>
                                <input type="text" 
                                       value="{{ Auth::user()->persona->NombresPersona ?? Auth::user()->NombreUsuario }} {{ Auth::user()->persona->ApellidosPersona ?? '' }}" 
                                       class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed" 
                                       disabled>
                            </div>

                            <div>
                                    <label for="fecha_atencion" class="block font-medium text-sm text-gray-700">Fecha y Hora de Recepción *</label>
                                    <input type="datetime-local" 
                                        name="fecha_atencion"
                                        id="fecha_atencion"
                                        value="{{ old('fecha_atencion', now()->format('Y-m-d\TH:i')) }}" 
                                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" 
                                        required>
                                   
                                    @error('fecha_atencion')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                            <div>

                        

                 

                            <div>
                                <label for="archivos" class="block font-medium text-sm text-gray-700">Anexar Documentos (Opcional)</label>
                                <input type="file" name="archivos[]" id="archivos" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('archivos.0') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        
                        </div> <div class="flex items-center justify-end mt-8">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Registrar Solicitud
                            </button>
                        </div>
                

                    </form>
                    </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const municipioSelect = document.getElementById('municipio_select');
        const parroquiaSelect = document.getElementById('parroquia_select');
        
        // --- Datos (para manejar recarga con error de validación) ---
        // Almacenamos el ID de la parroquia si falló la validación
        const oldParroquiaId = @json(old('parroquia_id'));
        let selectedMunicipio = null;

        // Función para poblar las parroquias
        function poblarParroquias(municipioOption) {
            // Limpiamos el select de parroquias
            parroquiaSelect.innerHTML = '<option value="" disabled selected>-- Seleccione una Parroquia --</option>';

            if (!municipioOption || !municipioOption.dataset.parroquias) {
                parroquiaSelect.disabled = true;
                return;
            }

            const parroquias = JSON.parse(municipioOption.dataset.parroquias);

            if (parroquias.length > 0) {
                parroquiaSelect.disabled = false;
                parroquias.forEach(parroquia => {
                    const option = document.createElement('option');
                    option.value = parroquia.CodParroquia;
                    option.textContent = parroquia.NombreParroquia;
                    
                    // Si este es el ID que falló en la validación, lo pre-seleccionamos
                    if (oldParroquiaId && parroquia.CodParroquia == oldParroquiaId) {
                        option.selected = true;
                    }
                    
                    parroquiaSelect.appendChild(option);
                });
            } else {
                parroquiaSelect.disabled = true;
            }
        }

        // --- Event Listener ---
        municipioSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            poblarParroquias(selectedOption);
        });

        // --- Lógica de Carga Inicial (para errores de validación) ---
        // 1. Buscamos si hay un 'old' parroquia_id
        if (oldParroquiaId) {
            // 2. Si hay, buscamos a qué municipio pertenece esa parroquia
            @foreach($municipios as $municipio)
                @foreach($municipio->parroquias as $parroquia)
                    @if(old('parroquia_id') == $parroquia->CodParroquia)
                        selectedMunicipio = {{ $municipio->CodMunicipio }};
                    @endif
                @endforeach
            @endforeach
            
            // 3. Si encontramos el municipio, lo preseleccionamos
            if (selectedMunicipio) {
                municipioSelect.value = selectedMunicipio;
                // 4. Poblamos las parroquias de ese municipio
                const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
                poblarParroquias(selectedOption);
            }
        }
    });
</script>
@endpush