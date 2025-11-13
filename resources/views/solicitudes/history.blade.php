@extends('layouts.app')

@section('title', 'Historial de Solicitudes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Historial de Solicitudes Resueltas</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">&larr; Volver a la Agenda Digital</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Nro. Solicitud</th>
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
                            <td class="fw-bold text-primary">{{ $solicitud->Nro_UAC ?? $solicitud->CodSolicitud }}</td>
                            <td>{{ $solicitud->persona->NombreCompleto }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($solicitud->DescripcionSolicitud, 70) }}</td>
                            <td>{{ $solicitud->FechaSolicitud->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-success">
                                    {{ $solicitud->status->NombreStatusSolicitud }}
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
                                No se encontraron solicitudes resueltas en el historial.
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