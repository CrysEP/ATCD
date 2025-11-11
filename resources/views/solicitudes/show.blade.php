@extends('layouts.app')

@section('title', 'Detalle de Solicitud #' . ($solicitud->Nro_UAC ?? $solicitud->CodSolucitud))

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">
            Solicitud 
            <span class="text-primary fw-bold">
                #{{ $solicitud->Nro_UAC ?? $solicitud->CodSolucitud }}
            </span>
        </h2>
        <span class="fs-5 text-muted">Nivel de Urgencia:
            <span class="badge fs-6
                @if($solicitud->NivelUrgencia == 'Alto') bg-danger
                @elseif($solicitud->NivelUrgencia == 'Medio') bg-warning text-dark
                @else bg-success
                @endif">
                {{ $solicitud->NivelUrgencia }}
            </span>
        </span>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        &larr; Volver a la Agenda Digital
    </a>
</div>

<div class="row g-4">

    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">1. Información del Solicitante</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6"><strong>Nombre:</strong> {{ $solicitud->persona->NombreCompleto }}</div>
                    <div class="col-md-6"><strong>Cédula:</strong> {{ $solicitud->persona->CedulaPersona }}</div>
                    <div class="col-md-6">
                        <strong>Teléfono:</strong> {{ $solicitud->persona->TelefonoPersona }}
                        <a href="tel:{{ $solicitud->persona->TelefonoPersona }}" class="ms-2">(Llamar)</a>
                    </div>
                    <div class="col-md-6"><strong>Correo:</strong> {{ $solicitud->persona->CorreoElectronicoPersona ?? 'N/A' }}</div>
                    <div class="col-12"><strong>Localidad:</strong>
                        {{ $solicitud->persona->parroquia->NombreParroquia ?? 'N/A' }},
                        Mcp. {{ $solicitud->persona->parroquia->municipio->NombreMunicipio ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">2. Detalles de la Solicitud</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6"><strong>Tipo (Planilla):</strong> {{ $solicitud->TipoSolicitudPlanilla }}</div>
                    <div class="col-md-6"><strong>Tipo Solicitante:</strong> {{ $solicitud->TipoSolicitante }}</div>
                    <div class="col-md-6"><strong>Fecha Solicitud:</strong> {{ $solicitud->FechaSolicitud->format('d/m/Y h:i A') }}</div>
                    <div class="col-md-6"><strong>Código Interno:</strong> {{ $solicitud->CodigoInterno_FK }}</div>
                </div>
                <div class="mt-4">
                    <strong>Descripción de la Solicitud:</strong>
                    <p class="bg-light p-3 rounded-2 mt-2" style="white-space: pre-wrap;">{{ $solicitud->DescripcionSolicitud }}</p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">3. Archivos Adjuntos</h5>
            </div>
            <div class="card-body p-4">
                @if($solicitud->archivos->count() > 0)
                    <ul class="list-group">
                        @foreach($solicitud->archivos as $archivo)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('solicitudes.downloadFile', $archivo->id) }}" class="text-decoration-none" title="Descargar">
                                {{ $archivo->nombre_original }}
                            </a>
                            <span class="text-muted small">{{ number_format($archivo->tamano_archivo / 1024, 2) }} KB</span>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No hay archivos adjuntos para esta solicitud.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 position-sticky" style="top: 1.5rem;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Estado y Flujo</h5>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <span class="fs-4 fw-bold p-3 rounded-3 bg-primary bg-opacity-10 text-primary border border-primary">
                        {{ $solicitud->status->NombreStatusSolicitud ?? 'Sin Estado' }}
                    </span>
                </div>

                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0"><strong>Nro. Oficio:</strong> {{ $solicitud->correspondencia->{'Nro.Oficio'} ?? 'N/A' }}</li>
                    <li class="list-group-item px-0"><strong>Recibido:</strong> {{ $solicitud->correspondencia->FechaRecibido->format('d/m/Y') }}</li>
                    <li class="list-group-item px-0"><strong>Registrado por:</strong> {{ $solicitud->funcionario->NombreUsuario ?? 'N/A' }}</li>
                </ul>

                <div>
                    <strong>Instrucciones:</strong>
                    <p class="bg-light p-3 rounded-2 mt-1 small" style="white-space: pre-wrap; height: 150px; overflow-y: auto;">{{ $solicitud->correspondencia->InstruccionPresidencia ?? 'Sin instrucciones.' }}</p>
                </div>
            </div>

            <!--  @if(auth()->user()->RolUsuario == 'Administrador') -->
            @can('es-admin')
            <div class="card-footer p-4">
                <li class="nav-item">
        <a class="nav-link" href="{{ route('reportes.index') }}">Reportes</a>
    </li>
                <h5 class="mb-3">Acciones</h5>
                <form action="{{ route('solicitudes.updateStatus', $solicitud->CodSolucitud) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Cambiar Estado:</label>
                        <select name="status_id" id="status_id" required class="form-select">
                            @foreach($statuses as $status)
                            <option value="{{ $status->CodStatusSolicitud }}" @selected($solicitud->status->CodStatusSolicitud == $status->CodStatusSolicitud)>
                                {{ $status->NombreStatusSolicitud }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    @can('es-personal')
    <div class="alert alert-info">
        <p>Hola, {{ auth()->user()->NombreUsuario }}. Esta es tu vista de usuario personal.</p>
    </div>
@endcan

                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación (se añadirá al historial):</label>
                        <textarea name="observacion" id="observacion" rows="3" class="form-control" placeholder="Ej: Aprobado por presidencia, remitir a..."></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            Actualizar Estado
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection