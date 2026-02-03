@extends('layouts.app')

@section('title', 'Solicitudes pendientes')

@section('content')
<div class="row">
    <div class="col-lg-9">
        <h2 class="mb-4">Solicitudes pendientes (nuevas)</h2>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="list-group">
            @forelse ($solicitudes as $solicitud)
                <a href="{{ route('solicitudes.show', $solicitud->CodSolicitud) }}" class="list-group-item list-group-item-action mb-3 p-3 shadow-sm border-0">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-dark">

                      {{-- 1. Prioridad: Código Interno Nuevo (Prefijo-Numero) --}}
        @if ($solicitud->correspondencia && $solicitud->correspondencia->CodigoInterno)
            <span class="fw-bold">{{ $solicitud->correspondencia->CodigoInterno }}</span>
        
        {{-- 2. Fallback: Nro UAC (si existe) --}}
        @elseif ($solicitud->Nro_UAC)
            <span class="fw-bold">UAC: {{ $solicitud->Nro_UAC }}</span>
        
        {{-- 3. Último recurso: Nombre (por si acaso hay datos viejos sin código) --}}
        @else
            <span class="fw-bold">{{ $solicitud->persona->NombreCompleto }}</span>
        @endif
    </h5>
    <small class="text-muted">{{ $solicitud->FechaSolicitud->diffForHumans() }}</small>
                    
                    </div>

                    <p class="mb-1">
                       <strong>Solicitante:</strong> {{ $solicitud->persona->NombreCompleto }} <br>
                         {{ \Illuminate\Support\Str::limit($solicitud->DescripcionSolicitud, 150) }}
                    </p>

                    <div class="mt-2">
                        <span class="badge 
                            @if($solicitud->correspondencia->status->CodStatusSolicitud == 1) bg-warning text-dark
                            @elseif($solicitud->correspondencia->status->CodStatusSolicitud == 2) bg-info text-dark
                            @elseif($solicitud->correspondencia->status->CodStatusSolicitud == 4) bg-danger-emphasis
                            @else bg-secondary
                            @endif">
                            <i class="bi bi-tag me-1"></i>
                            {{ $solicitud->correspondencia->status->NombreStatusSolicitud }}
                        </span>

                        <span class="badge 
                            @if($solicitud->NivelUrgencia == 'Alto') bg-danger
                            @elseif($solicitud->NivelUrgencia == 'Medio') bg-warning text-dark
                            @else bg-secondary-bg-subtle
                            @endif">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Urgencia: {{ $solicitud->NivelUrgencia }}
                        </span>
                        
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $solicitud->persona->parroquia->municipio->NombreMunicipio }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="card card-gradient-body text-center shadow-sm border-0">
                    <div class="card-body p-5">
                        <h4 class="card-title">No hay solicitudes activas</h4>
                        <p class="card-text">Todas las solicitudes han sido resueltas o no hay registros.</p>
                        <a href="{{ route('solicitudes.create') }}" class="btn btn-primary">Registrar Nueva Solicitud</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $solicitudes->links() }}
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card shadow-sm border-0 position-sticky" style="top: 1.5rem;">
            <div class="card-header">
                <h5 class="mb-0">Filtros y Búsqueda</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard') }}">
                    <div class="mb-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Cédula, Nro. UAC, palabra clave...">
                    </div>
                    

                    <div class="mb-3">
    <label class="form-label fw-bold text-primary small">Rango de Fechas</label>
    
    <div class="mb-2">
        <label for="fecha_desde" class="form-label small text-muted">Desde:</label>
        <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" 
               value="{{ request('fecha_desde') }}">
    </div>
    
    <div class="mb-2">
        <label for="fecha_hasta" class="form-label small text-muted">Hasta:</label>
        <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" 
               value="{{ request('fecha_hasta') }}">
    </div>
</div>


        {{-- Filtro Código Interno --}}
        <div class="col-md-6">
            <label class="form-label fw-bold small text-muted">Código Interno</label>
            <input type="text" class="form-control" name="codigo_interno" 
                   value="{{ request('codigo_interno') }}" placeholder="Ej: CO-0005">
        </div>

        {{-- Filtro Nro UAC --}}
        <div class="col-md-6">
            <label class="form-label fw-bold small text-muted">Nro. UAC</label>
            <input type="text" class="form-control" name="nro_uac" 
                   value="{{ request('nro_uac') }}" placeholder="Ej: UAC-123">
        </div>

        {{-- Filtro Municipio --}}
        <div class="col-md-6">
            <label class="form-label fw-bold small text-muted">Municipio</label>
            <select class="form-select" name="municipio_id">
                <option value="">-- Todos --</option>
                @foreach($municipios as $m)
                    <option value="{{ $m->CodMunicipio }}" {{ request('municipio_id') == $m->CodMunicipio ? 'selected' : '' }}>
                        {{ $m->NombreMunicipio }}
                    </option>
                @endforeach
            </select>
        </div>



                    <div class="mb-3">
                        <label for="urgencia" class="form-label">Urgencia</label>
                        <select name="urgencia" id="urgencia" class="form-select">
                            <option value="">Todas</option>
                            <option value="Alto">Alto</option>
                            <option value="Medio">Medio</option>
                            <option value="Bajo">Bajo</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection