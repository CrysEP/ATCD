@extends('layouts.app')

@section('title', 'Historial de Solicitudes')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Historial de Solicitudes Resueltas</h1>
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; Volver al Dashboard</a>
    </div>

    <!-- Aquí podrías añadir filtros de fecha -->

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nro. UAC</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciudadano</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Solicitud</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($solicitudes as $solicitud)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primario">{{ $solicitud->Nro_UAC }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $solicitud->persona->NombreCompleto }}</td>
                    <td class="px-6 py-4 max-w-sm text-sm text-gray-500 truncate">{{ $solicitud->DescripcionSolicitud }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $solicitud->FechaSolicitud->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            {{ $solicitud->status->NombreStatusSolicitud }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('solicitudes.show', $solicitud->CodSolucitud) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">No se encontraron solicitudes resueltas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <div class="mt-8">
        {{ $solicitudes->links() }}
    </div>
@endsection
