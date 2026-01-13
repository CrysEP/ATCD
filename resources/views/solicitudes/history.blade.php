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
                                <td class="fw-bold text-dark">
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
    <label class="form-label fw-bold text-primary small">Rango de Fechas</label>
    <div class="mb-2">
        <label for="fecha_desde" class="form-label small text-muted">Desde:</label>
        <input type="date" class="form-control form-control-sm" name="fecha_desde" value="{{ request('fecha_desde') }}">
    </div>
    <div class="mb-2">
        <label for="fecha_hasta" class="form-label small text-muted">Hasta:</label>
        <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
    </div>
</div>


{{-- Filtro Código Interno --}}
        <div class="col-md-3">
            <label class="form-label fw-bold small text-muted">Código Interno</label>
            <input type="text" class="form-control" name="codigo_interno" 
                   value="{{ request('codigo_interno') }}" placeholder="Ej: CO-00005">
        </div>

        {{-- Filtro Nro UAC --}}
        <div class="col-md-3">
            <label class="form-label fw-bold small text-muted">Nro. UAC</label>
            <input type="text" class="form-control" name="nro_uac" 
                   value="{{ request('nro_uac') }}" placeholder="Ej: UAC-12345">
        </div>

        {{-- Filtro Municipio --}}
        <div class="col-md-3">
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
                                    <option value="Alto" @selected(request('urgencia') == 'Alto')>Alto</option>
                                    <option value="Medio" @selected(request('urgencia') == 'Medio')>Medio</option>
                                    <option value="Bajo" @selected(request('urgencia') == 'Bajo')>Bajo</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                                <a href="{{ route('solicitudes.history') }}" class="btn btn-outline-secondary">Limpiar</a>


                                  {{-- BOTÓN NUEVO: EXPORTAR EXCEL --}}
                                {{-- request()->query() mantiene los filtros actuales en el enlace --}}
                                     <a href="{{ route('solicitudes.exportarHistorial', request()->query()) }}" class="btn btn-success text-white">
                                            <i class="bi bi-file-earmark-excel"></i> Exportar Listado de solicitudes (Excel)
                                        </a>

                                           <a href="{{ route('solicitudes.exportarHistorialPdf', request()->query()) }}" class="btn btn-danger text-white" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i> Exportar Listado de solicitudes (PDF)
                                        </a>

                            </div>
                          

                </form>
            </div>
        </div>

        
        <div class="card shadow-sm border-0 mt-3 position-sticky" style="top: 22rem;">
        <div class="card-header bg-dark  text-white">
            <h5 class="mb-0"><i class="bi bi-file-earmark-zip"></i> Descarga de solicitudes procesadas</h5>
        </div>
        <div class="card-body card-gradient-body" >
            <p class="small text-muted mb-2">
                Descargue todas las planillas PDF (Mensual y semanal).
            </p>

            <form method="POST" action="{{ route('solicitudes.exportarZip') }}">
                @csrf
                
                <div class="mb-2">
                    <label for="fecha_desde_export" class="form-label small fw-bold">Desde:</label>
                    <input type="date" class="form-control form-control-sm" 
                           id="fecha_desde_export" name="fecha_desde_export" required
                           value="{{ date('Y-m-d') }}">
                </div>
                
                <div class="mb-3">
                    <label for="fecha_hasta_export" class="form-label small fw-bold">Hasta:</label>
                    <input type="date" class="form-control form-control-sm" 
                           id="fecha_hasta_export" name="fecha_hasta_export" required
                           value="{{ date('Y-m-d') }}"> {{-- Por defecto: Hoy --}}
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-download"></i> Descargar ZIP
                    </button>
                </div>
            </form>
        </div>
    </div>

    </div>
</div>



@endsection