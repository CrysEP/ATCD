@extends('layouts.app')

@section('title', 'Dashboard - Solicitudes Activas')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard de Solicitudes Activas</h1>
        <a href="{{ route('solicitudes.create') }}" class="btn-primario">
            + Registrar Nueva Solicitud
        </a>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <form action="{{ route('dashboard') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Buscar (Cód, C.I., Nombre, Nro.UAC)</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo de Solicitud</label>
                    <select name="tipo" id="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Todos</option>
                        <option value="Solicitud o Petición" @selected(request('tipo') == 'Solicitud o Petición')>Solicitud o Petición</option>
                        <option value="Quejas, reclamos o sugerencias" @selected(request('tipo') == 'Quejas, reclamos o sugerencias')>Quejas, reclamos o sugerencias</option>
                        <option value="Denuncia" @selected(request('tipo') == 'Denuncia')>Denuncia</option>
                    </select>
                </div>
                <div>
                    <label for="urgencia" class="block text-sm font-medium text-gray-700">Urgencia</label>
                    <select name="urgencia" id="urgencia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Todas</option>
                        <option value="Alto" @selected(request('urgencia') == 'Alto')>Alto</option>
                        <option value="Medio" @selected(request('urgencia') == 'Medio')>Medio</option>
                        <option value="Bajo" @selected(request('urgencia') == 'Bajo')>Bajo</option>
                    </select>
                </div>
                <!-- Añadir filtro de municipio (requiere cargar Municipios) -->
                <!--
                <div>
                    <label for="municipio" class="block text-sm font-medium text-gray-700">Municipio</label>
                    <select name="municipio" id="municipio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        
                    </select>
                </div>
                -->
                <div class="self-end">
                    <button type="submit" class="w-full btn-primario justify-center">Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Barra visual (Ejemplo) -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <h3 class="font-bold text-lg mb-2">Resumen de Estados</h3>
        <!-- Aquí iría una barra de progreso o similar -->
        <div class="flex space-x-2">
            <div class="flex-1 bg-yellow-400 text-white text-center p-2 rounded-l-md">Pendientes ({{-- contador --}})</div>
            <div class="flex-1 bg-blue-500 text-white text-center p-2">En Revisión ({{-- contador --}})</div>
            <div class="flex-1 bg-green-500 text-white text-center p-2">Aceptadas ({{-- contador --}})</div>
            <div class="flex-1 bg-red-500 text-white text-center p-2 rounded-r-md">Rechazadas ({{-- contador --}})</div>
        </div>
    </div>

    <!-- Contenedor de Solicitudes Activas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        
        @forelse ($solicitudes as $solicitud)
            
            <!--
            ================================================================
            INICIO: Componente de Tarjeta de Solicitud (Basado en tu HTML)
            ================================================================
            -->
            <div class="solicitud-card urgencia-{{ $solicitud->NivelUrgencia }}">
                <div class="card-header">
                    <span class="codigo">#{{ $solicitud->Nro_UAC }}</span>
                    <span class="tipo">{{ $solicitud->TipoSolicitudPlanilla }}</span>
                    <span class="badge urgencia-{{ $solicitud->NivelUrgencia }}">
                        {{ $solicitud->NivelUrgencia }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Usamos el accesor NombreCompleto del modelo Persona -->
                    <h6>{{ $solicitud->persona->NombreCompleto ?? 'N/A' }}</h6>
                    <p class="descripcion-corta">{{ Str::limit($solicitud->DescripcionSolicitud, 100) }}</p>
                    <div class="info-adicional">
                        <small><strong>Tel:</strong> {{ $solicitud->persona->TelefonoPersona ?? 'N/A' }}</small>
                        <small><strong>Fecha:</strong> {{ $solicitud->FechaSolicitud->format('d/m/Y') }}</small>
                        <small><strong>Municipio:</strong> {{ $solicitud->persona->parroquia->municipio->NombreMunicipio ?? 'N/A' }}</small>
                        <small><strong>Estado:</strong> <span class="font-bold">{{ $solicitud->status->NombreStatusSolicitud ?? 'N/A' }}</span></small>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="{{ route('solicitudes.show', $solicitud->CodSolucitud) }}" class="btn btn-sm btn-primary ver-detalles" data-id="{{ $solicitud->CodSolucitud }}">
                        👁️ Ver
                    </a>
                    
                    <a href="tel:{{ $solicitud->persona->TelefonoPersona }}" class="btn btn-sm btn-success llamar-ciudadano"
                       data-telefono="{{ $solicitud->persona->TelefonoPersona }}"
                       data-nombre="{{ $solicitud->persona->NombresPersona }}">
                        📞 Llamar
                    </a>
                    
                    @if(auth()->user()->RolUsuario == 'Administrador')
                    <!-- El botón de cambiar estado estará en la vista de detalle -->
                    <a href="{{ route('solicitudes.show', $solicitud->CodSolucitud) }}#cambiar-estado" class="btn btn-sm btn-warning cambiar-estado" data-id="{{ $solicitud->CodSolucitud }}">
                        ✏️ Estado
                    </a>
                    @endif
                </div>
            </div>
            <!--
            ================================================================
            FIN: Componente de Tarjeta de Solicitud
            ================================================================
            -->

        @empty
            <div class="lg:col-span-2 xl:col-span-3 text-center bg-white p-12 rounded-lg shadow-sm">
                <h3 class="text-xl font-medium text-gray-700">No se encontraron solicitudes activas.</h3>
                <p class="text-gray-500 mt-2">Intenta ajustar los filtros o registra una nueva solicitud.</p>
            </div>
        @endforelse

    </div>

    <!-- Paginación -->
    <div class="mt-8">
        {{ $solicitudes->links() }}
    </div>

@endsection
