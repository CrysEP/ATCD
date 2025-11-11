@extends('layouts.app')

@section('title', 'Agenda Digital - Solicitudes Activas')

@section('content')
<div class="row">
    <div class="col-lg-9">
        <h2 class="mb-4">Agenda Digital (Solicitudes Activas)</h2>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="list-group">
            @forelse ($solicitudes as $solicitud)
                <a href="#" class="list-group-item list-group-item-action mb-3 p-3 shadow-sm border-0">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary">
                            @if ($solicitud->Nro_UAC)
                                <span class="fw-bold">Nro. UAC:</span> {{ $solicitud->Nro_UAC }}
                            @else
                                <span class="fw-bold">{{ $solicitud->persona->NombreCompleto }}</span>
                            @endif
                        </h5>
                        <small class="text-muted">{{ $solicitud->FechaSolicitud->diffForHumans() }}</small>
                    </div>

                    <p class="mb-1">
                        {{ \Illuminate\Support\Str::limit($solicitud->DescripcionSolicitud, 150) }}
                    </p>

                    <div class="mt-2">
                        <span class="badge 
                            @if($solicitud->correspondencia->status->CodStatusSolicitud == 1) bg-warning text-dark
                            @elseif($solicitud->correspondencia->status->CodStatusSolicitud == 2) bg-info text-dark
                            @else bg-secondary
                            @endif">
                            <i class="bi bi-tag me-1"></i>
                            {{ $solicitud->correspondencia->status->NombreStatusSolicitud }}
                        </span>

                        <span class="badge 
                            @if($solicitud->NivelUrgencia == 'Alto') bg-danger
                            @elseif($solicitud->NivelUrgencia == 'Medio') bg-warning text-dark
                            @else bg-success
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