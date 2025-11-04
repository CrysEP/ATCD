@extends('layouts.app')

@section('title', 'Registrar Nueva Solicitud')

@section('content')
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Registrar Nueva Solicitud</h1>

    <form action="{{ route('solicitudes.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-md space-y-6">
        @csrf

        <!-- Sección 1: Datos del Ciudadano -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">1. Datos del Ciudadano</legend>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                <div>
                    <label for="cedula" class="block text-sm font-medium text-gray-700">Cédula de Identidad</label>
                    <input type="text" name="cedula" id="cedula" value="{{ old('cedula') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                    <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="apellidos" class="block text-sm font-medium text-gray-700">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" value="{{ old('telefono') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico (Opcional)</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="parroquia_id" class="block text-sm font-medium text-gray-700">Parroquia</label>
                    <!-- 
                        NOTA: Este select debe ser poblado dinámicamente desde la BD
                        Ej: Cargar Parroquias con Municipios
                    -->
                    <select name="parroquia_id" id="parroquia_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Seleccione...</option>
                        <!-- Ejemplo (Reemplazar con datos reales) -->
                        <option value="1">1 - San Cristóbal (SC)</option> 
                    </select>
                </div>
            </div>
        </fieldset>

        <!-- Sección 2: Datos de la Solicitud -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">2. Datos de la Solicitud</legend>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                <div>
                    <label for="nro_uac" class="block text-sm font-medium text-gray-700">Nro. UAC (Atención al Ciudadano)</label>
                    <input type="text" name="nro_uac" id="nro_uac" value="{{ old('nro_uac', 'UAC-' . date('Ymd-His')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                </div>
                <div>
                    <label for="tipo_solicitante" class="block text-sm font-medium text-gray-700">Tipo de Solicitante</label>
                    <select name="tipo_solicitante" id="tipo_solicitante" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="Institucional" @selected(old('tipo_solicitante') == 'Institucional')>Institucional</option>
                        <option value="Consejo Comunal" @selected(old('tipo_solicitante') == 'Consejo Comunal')>Consejo Comunal</option>
                        <option value="Personal" @selected(old('tipo_solicitante') == 'Personal')>Personal</option>
                    </select>
                </div>
                <div>
                    <label for="tipo_solicitud_planilla" class="block text-sm font-medium text-gray-700">Tipo (Planilla)</label>
                    <select name="tipo_solicitud_planilla" id="tipo_solicitud_planilla" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="Solicitud o Petición" @selected(old('tipo_solicitud_planilla') == 'Solicitud o Petición')>Solicitud o Petición</option>
                        <option value="Quejas, reclamos o sugerencias" @selected(old('tipo_solicitud_planilla') == 'Quejas, reclamos o sugerencias')>Quejas, reclamos o sugerencias</option>
                        <option value="Denuncia" @selected(old('tipo_solicitud_planilla') == 'Denuncia')>Denuncia</option>
                    </select>
                </div>
                
                <div class="md:col-span-3">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción Detallada de la Solicitud</label>
                    <textarea name="descripcion" id="descripcion" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('descripcion') }}</textarea>
                </div>

                <!-- 
                    NOTA: Aquí irían los selects dinámicos para 'TipoSolicitud_FK'
                    (ServicioPublico_FK, InfraestructuraVial_FK, etc.)
                    Esto es más complejo y requiere JS o Livewire.
                -->

                <div>
                    <label for="nivel_urgencia" class="block text-sm font-medium text-gray-700">Nivel de Urgencia</D>
                    <select name="nivel_urgencia" id="nivel_urgencia" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="Bajo" @selected(old('nivel_urgencia') == 'Bajo')>Bajo</option>
                        <option value="Medio" @selected(old('nivel_urgencia') == 'Medio')>Medio</option>
                        <option value="Alto" @selected(old('nivel_urgencia') == 'Alto')>Alto</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="instruccion_presidencia" class="block text-sm font-medium text-gray-700">Instrucción Inicial (Opcional)</label>
                    <input type="text" name="instruccion_presidencia" id="instruccion_presidencia" value="{{ old('instruccion_presidencia') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

            </div>
        </fieldset>

        <!-- Sección 3: Archivos Adjuntos -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">3. Archivos Adjuntos</legend>
            <div class="mt-4">
                <label for="archivos" class="block text-sm font-medium text-gray-700">Subir documentos (PDF, Imágenes, Excel)</label>
                <input type="file" name="archivos[]" id="archivos" multiple class="mt-1 block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                "/>
            </div>
        </fieldset>
        
        <!-- Botón de Envío -->
        <div class="text-right">
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-800 mr-4">Cancelar</a>
            <button type="submit" class="btn-primario text-lg px-8 py-3">
                Guardar Solicitud
            </button>
        </div>
    </form>
@endsection
