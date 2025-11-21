@extends('layouts.app')

@section('title', 'Solicitudes Anuladas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-danger">
            <i class="bi bi-trash3 me-2"></i> Solicitudes Anuladas
        </h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">&larr; Volver a la Agenda</a>
    </div>

    <div class="card shadow-sm border-0 border-start border-danger border-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Código / UAC</th>
                            <th scope="col">Ciudadano</th>
                            <th scope="col">Motivo / Descripción</th>
                            <th scope="col">Fecha Anulación</th>
                            <th scope="col" class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($solicitudes as $solicitud)
                        <tr>
                            <td class="fw-bold">
                                {{ $solicitud->correspondencia->CodigoInterno ?? $solicitud->Nro_UAC ?? 'S/N' }}
                            </td>
                            <td>{{ $solicitud->persona->NombreCompleto }}</td>
                            <td>
                                <small class="text-muted d-block">Solicitud: {{ \Illuminate\Support\Str::limit($solicitud->DescripcionSolicitud, 50) }}</small>
                                {{-- Mostramos parte de la observación de anulación si existe --}}
                                <span class="text-danger small fst-italic">
                                    {{ \Illuminate\Support\Str::limit($solicitud->correspondencia->Observacion, 60) }}
                                </span>
                            </td>
                           <td>{{ $solicitud->FechaSolicitud->format('d/m/Y h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('solicitudes.show', $solicitud->CodSolicitud) }}" class="btn btn-sm btn-outline-danger">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="bi bi-check-circle fs-1 d-block mb-3 text-success"></i>
                                No hay solicitudes anuladas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if ($solicitudes->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $solicitudes->links() }}
            </div>
        @endif
    </div>
@endsection