@extends('layouts.app')

@section('content')
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Agenda Digital de Solicitudes
            </h2>
            <a href="{{ route('solicitudes.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                + Nueva Solicitud
            </a>
        </div>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">¡Excelente!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-6">
                        <form method="GET" action="{{ route('dashboard') }}" class="flex gap-4">
                            <div class="flex-1">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por código, cédula o nombre..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition">
                                Buscar
                            </button>
                            @if(request('search'))
                                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition flex items-center">
                                    Limpiar
                                </a>
                            @endif
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Código
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Solicitante
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo / Urgencia
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($solicitudes as $solicitud)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $solicitud->correspondencia->CodigoInterno ?? 'S/C' }}
                                            </div>
                                            @if($solicitud->Nro_UAC)
                                                <div class="text-xs text-gray-500">
                                                    UAC: {{ $solicitud->Nro_UAC }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $solicitud->FechaSolicitud ? $solicitud->FechaSolicitud->format('d/m/Y') : 'N/A' }}
                                            <div class="text-xs">
                                                {{ $solicitud->FechaSolicitud ? $solicitud->FechaSolicitud->format('h:i A') : '' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $solicitud->persona->NombresPersona ?? '' }} {{ $solicitud->persona->ApellidosPersona ?? '' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                C.I: {{ $solicitud->persona->CedulaPersona ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                 {{ $solicitud->persona->parroquia->municipio->NombreMunicipio ?? '' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $solicitud->TipoSolicitudPlanilla }}
                                            </div>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $solicitud->NivelUrgencia === 'Alto' ? 'bg-red-100 text-red-800' : 
                                                  ($solicitud->NivelUrgencia === 'Medio' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                {{ $solicitud->NivelUrgencia }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $statusNombre = $solicitud->status->NombreStatusSolicitud ?? 'Desconocido';
                                                $statusClass = match($statusNombre) {
                                                    'Recibida' => 'bg-blue-100 text-blue-800',
                                                    'En espera' => 'bg-yellow-100 text-yellow-800',
                                                    'Aceptada' => 'bg-green-100 text-green-800',
                                                    'Rechazada' => 'bg-red-100 text-red-800',
                                                    'Respuesta parcial' => 'bg-purple-100 text-purple-800',
                                                    'Resuelta' => 'bg-gray-100 text-gray-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ $statusNombre }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- Enlace temporal hasta que tengas la ruta 'show' lista --}}
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                                Ver Detalles →
                                            </a>
                                            {{-- Cuando tengas la ruta lista, usa esto: --}}
                                            {{-- <a href="{{ route('solicitudes.show', $solicitud->CodSolucitud) }}" class="text-indigo-600 hover:text-indigo-900">Ver Detalles</a> --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-lg font-medium text-gray-900">No hay solicitudes pendientes</p>
                                                <p class="text-sm text-gray-500">¡Buen trabajo! La agenda está despejada por ahora.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $solicitudes->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection