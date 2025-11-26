@extends('layouts.app')

@section('title', 'Historial de Solicitudes')

@section('content')
<div class="row">
    
    {{-- COLUMNA IZQUIERDA: TABLA DE RESULTADOS (9 columnas) --}}
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Solicitudes recibidas</h2>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">&larr; Volver a la Agenda</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Código Interno</th>
                                <th scope="col">Ciudadano</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Fecha Solicitud</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($solicitudes as $solicitud)
                            <tr>
                                <td class="fw-bold text-primary">
                                    @if ($solicitud->correspondencia && $solicitud->correspondencia->CodigoInterno)
                                        {{ $solicitud->correspondencia->CodigoInterno }}
                                    @else
                                        {{ $solicitud->Nro_UAC ?? $solicitud->CodSolicitud }}
                                    @endif
                                </td>
                                <td>{{ $solicitud->persona->NombreCompleto }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($solicitud->DescripcionSolicitud, 60) }}</td>
                                <td>{{ $solicitud->FechaSolicitud->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ $solicitud->status->NombreStatusSolicitud ?? 'Resuelta' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('solicitudes.show', $solicitud->CodSolicitud) }}" class="btn btn-sm btn-outline-primary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center p-5 text-muted">
                                    No se encontraron solicitudes procesadas en el historialcon esos criterios.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if ($solicitudes->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $solicitudes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- COLUMNA DERECHA: FILTROS (3 columnas) --}}
    <div class="col-lg-3">
        <div class="card shadow-sm border-0 position-sticky" style="top: 1.5rem;">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
            </div>
            <div class="card-body">
                {{-- Formulario apunta a la ruta HISTORY --}}
                <form method="GET" action="{{ route('solicitudes.history') }}">
                    <div class="mb-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cédula, Código, Nombre...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="urgencia" class="form-label">Urgencia Original</label>
                        <select name="urgencia" id="urgencia" class="form-select">
                            <option value="">Todas</option>
                            <option value="Alto" @selected(request('urgencia') == 'Alto')>Alto</option>
                            <option value="Medio" @selected(request('urgencia') == 'Medio')>Medio</option>
                            <option value="Bajo" @selected(request('urgencia') == 'Bajo')>Bajo</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                        <a href="{{ route('solicitudes.history') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection