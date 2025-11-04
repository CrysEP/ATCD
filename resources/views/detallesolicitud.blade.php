@extends('layouts.app')

@section('title', 'Detalle de Solicitud #' . $solicitud->Nro_UAC)

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Solicitud #{{ $solicitud->Nro_UAC }}</h1>
            <span class="text-xl text-gray-600">Nivel de Urgencia: 
                <span class="font-bold p-1 rounded
                    @if($solicitud->NivelUrgencia == 'Alto') bg-red-100 text-red-700 @endif
                    @if($solicitud->NivelUrgencia == 'Medio') bg-yellow-100 text-yellow-700 @endif
                    @if($solicitud->NivelUrgencia == 'Bajo') bg-green-100 text-green-700 @endif
                ">
                    {{ $solicitud->NivelUrgencia }}
                </span>
            </span>
        </div>
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">&larr; Volver al Dashboard</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna Principal (Información) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md space-y-6">
            
            <!-- Detalles del Ciudadano -->
            <section>
                <h2 class="text-2xl font-semibold border-b pb-2 mb-4 text-primario">Información del Ciudadano</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Nombre:</strong> {{ $solicitud->persona->NombreCompleto }}</div>
                    <div><strong>Cédula:</strong> {{ $solicitud->persona->CedulaPersona }}</div>
                    <div><strong>Teléfono:</strong> {{ $solicitud->persona->TelefonoPersona }} 
                        <a href="tel:{{ $solicitud->persona->TelefonoPersona }}" class="text-blue-600 hover:underline">(Llamar)</a>
                    </div>
                    <div><strong>Correo:</strong> {{ $solicitud->persona->CorreoElectronicoPersona ?? 'N/A' }}</div>
                    <div class="col-span-2"><strong>Localidad:</strong> 
                        {{ $solicitud->persona->parroquia->NombreParroquia ?? 'N/A' }}, 
                        Mcp. {{ $solicitud->persona->parroquia->municipio->NombreMunicipio ?? 'N/A' }}
                    </div>
                </div>
            </section>

            <!-- Detalles de la Solicitud -->
            <section>
                <h2 class="text-2xl font-semibold border-b pb-2 mb-4 text-primario">Detalles de la Solicitud</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Tipo (Planilla):</strong> {{ $solicitud->TipoSolicitudPlanilla }}</div>
                    <div><strong>Tipo Solicitante:</strong> {{ $solicitud->TipoSolicitante }}</div>
                    <div><strong>Fecha Solicitud:</strong> {{ $solicitud->FechaSolicitud->format('d/m/Y h:i A') }}</div>
                    <div><strong>Código Interno:</strong> {{ $solicitud->CodigoInterno_FK }}</div>
                </div>
                <div class="mt-4">
                    <strong>Descripción de la Solicitud:</strong>
                    <p class="bg-gray-50 p-4 rounded-md mt-2 whitespace-pre-wrap">{{ $solicitud->DescripcionSolicitud }}</p>
                </div>
            </section>

            <!-- Archivos Adjuntos -->
            <section>
                <h2 class="text-2xl font-semibold border-b pb-2 mb-4 text-primario">Archivos Adjuntos</h2>
                @if($solicitud->archivos->count() > 0)
                    <ul class="list-disc list-inside space-y-2">
                        @foreach($solicitud->archivos as $archivo)
                        <li>
                            <a href="{{ route('solicitudes.downloadFile', $archivo->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline" title="Descargar">
                                {{ $archivo->nombre_original }}
                            </a>
                            <span class="text-gray-500 text-sm">({{ number_format($archivo->tamano_archivo / 1024, 2) }} KB)</span>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">No hay archivos adjuntos para esta solicitud.</p>
                @endif
            </section>

        </div>

        <!-- Columna Lateral (Estado y Acciones) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Estado Actual -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4 text-primario">Estado y Flujo</h2>
                
                <div class="text-center mb-4">
                    <span class="text-3xl font-bold p-3 rounded-lg bg-blue-100 text-blue-800 border border-blue-300">
                        {{ $solicitud->status->NombreStatusSolicitud ?? 'Sin Estado' }}
                    </span>
                </div>
                
                <div class="space-y-2 text-gray-700">
                    <div><strong>Nro. Oficio:</strong> {{ $solicitud->correspondencia->{'Nro.Oficio'} ?? 'N/A' }}</div>
                    <div><strong>Recibido:</strong> {{ $solicitud->correspondencia->FechaRecibido->format('d/m/Y') }}</div>
                    <div><strong>Registrado por:</strong> {{ $solicitud->funcionario->NombreUsuario ?? 'N/A' }}</div>
                </div>

                <div class="mt-4">
                    <strong>Instrucciones:</strong>
                    <p class="bg-gray-50 p-3 rounded-md mt-1 text-sm whitespace-pre-wrap h-40 overflow-y-auto">{{ $solicitud->correspondencia->InstruccionPresidencia ?? 'Sin instrucciones.' }}</p>
                </div>
            </div>

            <!-- Cambiar Estado (Admin) -->
            @if(auth()->user()->RolUsuario == 'Administrador')
            <div id="cambiar-estado" class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4 text-primario">Acciones de Administrador</h2>
                
                <form action="{{ route('solicitudes.updateStatus', $solicitud->CodSolucitud) }}" method="POST">
                    @csrf
                    <label for="status_id" class="block text-sm font-medium text-gray-700">Cambiar Estado</label>
                    <select name="status_id" id="status_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach($statuses as $status)
                        <option value="{{ $status->CodStatusSolicitud }}" @selected($solicitud->status->CodStatusSolicitud == $status->CodStatusSolicitud)>
                            {{ $status->NombreStatusSolicitud }}
                        </option>
                        @endforeach
                    </select>

                    <label for="observacion" class="block text-sm font-medium text-gray-700 mt-4">Observación o Instrucción (se añadirá al historial)</label>
                    <textarea name="observacion" id="observacion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Ej: Aprobado por presidencia, remitir a..."></textarea>

                    <button type="submit" class="btn-primario w-full mt-4 justify-center py-3">
                        Actualizar Estado
                    </button>
                </form>
            </div>
            @endif

        </div>

    </div>
@endsection
