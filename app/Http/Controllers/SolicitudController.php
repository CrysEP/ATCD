<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitud;
use App\Models\Persona;
use App\Models\RelacionCorrespondencia;
use App\Models\StatusSolicitud;
use App\Models\TipoEnte; 
use App\Models\Municipio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;



class SolicitudController extends Controller
{
    /**
     * Muestra el Dashboard con las solicitudes activas.
     */
    public function index(Request $request)
    {
        $query = Solicitud::query();

        // Obtener solo solicitudes NO resueltas (Status != 6)
        $query->whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', '!=', 6); // 6 = Resuelta
        });

        // --- Aplicar Filtros (Ejemplos) ---
        if ($request->filled('tipo')) {
            $query->where('TipoSolicitudPlanilla', $request->tipo);
        }

        if ($request->filled('urgencia')) {
            $query->where('NivelUrgencia', $request->urgencia);
        }

        if ($request->filled('municipio')) {
            $query->whereHas('persona.parroquia.municipio', function ($q) use ($request) {
                $q->where('CodMunicipio', $request->municipio);
            });
        }
        
        // --- Búsqueda ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('Nro.UAC', 'like', "%{$search}%")
                  ->orWhere('DescripcionSolicitud', 'like', "%{$search}%")
                  ->orWhereHas('persona', function ($q_persona) use ($search) {
                      $q_persona->where('NombresPersona', 'like', "%{$search}%")
                                ->orWhere('ApellidosPersona', 'like', "%{$search}%")
                                ->orWhere('CedulaPersona', 'like', "%{$search}%");
                  });
            });
        }

        // Carga eficiente de relaciones (Eager Loading)
        // Esto es CRUCIAL para que el componente de la tarjeta funcione sin N+1 queries
        $solicitudes = $query->with([
            'persona.parroquia.municipio', 
            'correspondencia.status',
            // 'funcionario.persona' // Cuando el modelo Funcionario esté listo
        ])
        ->orderBy('FechaSolicitud', 'desc')
        ->paginate(20);

        // dd($solicitudes->first()->status); // Para depurar

        return view('dashboard', compact('solicitudes'));
    }

    /**
     * Muestra el formulario para crear una nueva solicitud.
     */
  public function create()
    {
        // 1. Cargar los datos de la BD
        $tiposEnte = TipoEnte::all(['CodTipoEnte', 'NombreEnte']);
        $municipios = Municipio::with('parroquias:CodParroquia,NombreParroquia,Municipio_FK')
                        ->get(['CodMunicipio', 'NombreMunicipio']);

        
       // 2. Cargar datos del funcionario logueado 
        $funcionario = Usuario::with('persona', 'funcionarioData')->find(Auth::id());

        // 3. Retornar la vista NUEVA pasando los datos
        return view('solicitudes.create', compact('tiposEnte', 'municipios', 'funcionario'));
    }

    /**
     * Almacena una nueva solicitud en la base de datos.
     * Este es el flujo de trabajo principal.
     */
    public function store(Request $request)
    {
        // --- 1. Validación (Añadir reglas según sea necesario) ---
        $validatedData = $request->validate([
            'cedula' => 'required|string|max:30',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'required|string|max:15',
            'parroquia_id' => 'required|integer', 
            'email' => 'nullable|email',
            
            'nro_uac' => 'nullable|string|max:50|unique:solicitud,Nro.UAC',
            'tipo_solicitud_planilla' => 'required|string', // Enum
            'descripcion' => 'required|string',
            'tipo_solicitante' => 'required|string', // Enum
            'nivel_urgencia' => 'required|string', // Enum
            
            'instruccion_presidencia' => 'nullable|string',

            'archivos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,xls,xlsx|max:10240', // max 10MB, por ahora o.o
            'fecha_atencion' => 'required|date',   
        ]);

        // Iniciar transacción de base de datos
        DB::beginTransaction();

        try {
            // --- 2. Gestionar Persona (Buscar o Crear) ---
            $persona = Persona::updateOrCreate(
                ['CedulaPersona' => $validatedData['cedula']],
                [
                    'NombresPersona' => $validatedData['nombres'],
                    'ApellidosPersona' => $validatedData['apellidos'],
                    'TelefonoPersona' => $validatedData['telefono'],
                    'ParroquiaPersona_FK' => $validatedData['parroquia_id'],
                    'CorreoElectronicoPersona' => $validatedData['email'] ?? '',
                    'FechaNacPersona' => $request->fecha_nacimiento ?? now(), // Asumir default
                    'SexoPersona' => $request->sexo ?? 'N', // Asumir default
                    
                ]
            );

            // --- 3. Crear la Solicitud ---
            $solicitud = Solicitud::create([
                'TipoSolicitudPlanilla' => $validatedData['tipo_solicitud_planilla'],
                'DescripcionSolicitud' => $validatedData['descripcion'],
                'FechaSolicitud' => now(),
                'FechaAtención' => $validatedData['fecha_atencion'],
                'TipoSolicitante' => $validatedData['tipo_solicitante'],
                'NivelUrgencia' => $validatedData['nivel_urgencia'],
      
                'AnexaDocumentos' => $request->hasFile('archivos'),
                'CantidadDocumentosOriginal' => 0, // Ajustar si es necesario, si no pues miau
                'CantidadDocumentoCopia' => 0, // Ajustar si es necesario
                'CantidadPaginasAnexo' => 0, // Ajustar si es necesario
                'CedulaPersona_FK' => $persona->CedulaPersona,
                'Nro.UAC' => $validatedData['nro_uac'],
                // 'CodigoInterno_FK' => $correspondencia->CodigoInterno,
                'Funcionario_FK' => auth()->user()->CodUsuario, // Asumiendo que el funcionario es el usuario logueado
                'TipoSolicitud_FK' => $request->tipo_solicitud_fk ?? 'TIPO-01', // Ajustar
            ]);

            // --- 4. Crear Relación de Correspondencia (Vínculo al Status) ---
            // El estado inicial siempre es 'Pendiente' (ID 1, según tu seeder)
            $codigoInterno = 'CI-' . date('Ymd-His') . '-' . Str::random(4); // Generar un código único

            $correspondencia = RelacionCorrespondencia::create([
                'CodigoInterno' => $codigoInterno,
                'Solicitud_FK' => $solicitud->CodSolucitud,
                'Nro.Oficio' => $request->nro_oficio ?? 'N/A',
                'FechaOficioEntrega' => now(),
                'FechaRecibido' => now(),
                'Municipio_FK' => $persona->parroquia->Municipio_FK,
                'Ente' => 0, // Ajustar según lógica
                'Sector' => $request->sector ?? 'N/A',
                'Descripcion' => $validatedData['descripcion'], // Replicar descripción
                'InstruccionPresidencia' => $validatedData['instruccion_presidencia'] ?? '',
                'Observacion' => '',
                'Gerencia_Jefatura' => '',
                'StatusSolicitud_FK' => 1, // 1 = Pendiente (del seeder o.o)
            ]);

            // --- 5. Manejar Archivos Adjuntos ---
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    // Almacenar en 'storage/app/public/solicitudes'
                    $path = $file->store('solicitudes', 'public');

                    // Crear registro en la nueva tabla 'archivos_solicitud'
                    $solicitud->archivos()->create([
                        'nombre_original' => $file->getClientOriginalName(),
                        'ruta_archivo' => $path,
                        'tipo_archivo' => $file->getClientMimeType(),
                        'tamano_archivo' => $file->getSize(),
                    ]);
                }
            }
            
            // Si todo fue exitoso, confirmar la transacción
            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Solicitud registrada exitosamente.');

        } catch (\Exception $e) {
            // Si algo falla, revertir la transacción
            DB::rollBack();
            // Log::error($e->getMessage()); // Buena práctica
            return back()->withInput()->with('error', 'Error al registrar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la vista de detalle de una solicitud.
     */
    public function show($id)
    {
        $solicitud = Solicitud::with([
            'persona.parroquia.municipio', 
            'correspondencia.status',
            'archivos'
        ])->findOrFail($id);

        $statuses = StatusSolicitud::all(); // Para el modal de cambio de estado

        return view('solicitudes.show', compact('solicitud', 'statuses'));
    }

    /**
     * Actualiza el estado de una solicitud.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status_id' => 'required|integer|exists:status_solicitud,CodStatusSolicitud']);
        
        $solicitud = Solicitud::findOrFail($id);
        
        // El estado está en la tabla 'relacion_correspondencia'
        $solicitud->correspondencia->update([
            'StatusSolicitud_FK' => $request->status_id,
            // Opcional: Añadir instrucción/observación al cambiar estado
            'InstruccionPresidencia' => $solicitud->correspondencia->InstruccionPresidencia . "\n[Actualizado por " . auth()->user()->NombreUsuario . " el " . now() . "]: " . $request->observacion,
        ]);

        return back()->with('success', 'Estado de la solicitud actualizado.');
    }

    /**
     * Muestra el historial de solicitudes resueltas.
     */
    public function history(Request $request)
    {
        // Lógica similar a index() pero filtrando por Status = 6
        $solicitudes = Solicitud::whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', 6); // 6 = Resuelta
        })
        ->with(['persona.parroquia.municipio', 'correspondencia.status'])
        ->orderBy('FechaAtención', 'desc')
        ->paginate(20);

        return view('solicitudes.history', compact('solicitudes'));
    }
    
    /**
     * Descarga un archivo adjunto.
     */
     public function downloadFile($id)
     {
         $archivo = \App\Models\ArchivoSolicitud::findOrFail($id);
         
         // Seguridad: Verificar que el usuario tenga permiso (opcional pero recomendado)
         // ...
         
         // Devuelve la descarga desde el storage 'public'
         return Storage::disk('public')->download($archivo->ruta_archivo, $archivo->nombre_original);
     }
}
