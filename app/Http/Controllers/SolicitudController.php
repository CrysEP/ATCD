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
use Illuminate\Validation\Rule;



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
            $q->where('StatusSolicitud_FK', '!=', 6) // No resueltas
              ->where('StatusSolicitud_FK', '!=', 7); // No anuladas
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
                $q->where('Nro_UAC', 'like', "%{$search}%")
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

$categorias = [
        'servicio_publico' => [
            'label' => 'Servicios Públicos',
            'opciones' => [
                'Acueductos', 'Aguas Servidas (Cloacas)', 'Canalizaciones', 'Caños', 
                'Causes', 'Cuencas', 'Drenajes', 'Embaulamientos', 'Pozos Profundos', 
                'Aguas Pluviales', 'Sistema de Riego', 'Tanques de Almacenamiento', 
                'Torrentera', 'Alumbrado' // Alumbrado viene de TendidosElectricos
            ]
        ],
        'infraestructura_vial' => [
            'label' => 'Infraestructura Vial',
            'opciones' => [
                'Aceras', 'Bateas', 'Brocales', 'Vías, calles, carreteras, Vereda', 
                'Cajones', 'Alcantarillas', 'Cuñetas', 'Demarcación Vial', 
                'Estabilización de Talud', 'Falla de Borde', 'Pasarelas', 
                'Pavimentos Flexibles (Asfaltados)', 'Pavimento Rígido', 'Puentes'
            ]
        ],
        'fortalecimiento_instituciones' => [
            'label' => 'Fortalecimiento Inst.',
            'opciones' => [
                'Infraestructuras Asistenciales', 'Infraestructuras Educativas', 
                'Infraestructuras Gubernamentales', 'Instituciones Religiosas', 
                'Muros Diques', 'Pared Perimetrales'
            ]
        ],
        'apoyo_instituciones' => [
            'label' => 'Apoyo a Instituciones',
            'opciones' => [
                'Maquinarias', 'Camiones', 'Materiales', 
                'Cuadrillas, Obreros, Albañiles...', 'Levantamientos Topográficos', 
                'Estudios de Suelos', 'Informes de Inspección', 'Proyectos'
            ]
        ],
        'apoyo_ciudadania' => [
            'label' => 'Apoyo a la Ciudadanía',
            'opciones' => [
                'Pasantías', 'Comisiones de Servicio', 'Sínstesis Curriculares', 
                'Gubernamental a la Corporación', 'Empresas', 'Otros'
            ]
        ],
    ];

    return view('solicitudes.create', compact('tiposEnte', 'municipios', 'funcionario', 'categorias'));
}
    

    /**
     * Almacena una nueva solicitud en la base de datos.
     * Este es el flujo de trabajo principal.
     */
    public function store(Request $request)
    {
        // --- 1. Validación (Añadir reglas según sea necesario) ---
        $validatedData = $request->validate([
            'tipo_cedula' => ['required', 'string', 'max:2', Rule::in(['V-', 'E-', 'J-', 'P-', 'G-'])],
            'cedula' => 'required|string|max:20|regex:/^[0-9]+$/',
            'nombres' => 'required|string|max:100|regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/',
            'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/',
            'telefono' => 'required|string|max:15|regex:/^[0-9\-\+\s\(\)]+$/',
            'parroquia_id' => 'required|integer', 
            'email' => 'nullable|email|max:300|email:filter', 
            
            'nro_uac' => 'nullable|string|max:50|unique:solicitud,Nro_UAC',
            'tipo_solicitud_planilla' => 'required|string', // Enum
            'descripcion' => 'required|string',
            'tipo_solicitante' => 'required|string', // Enum
            'nivel_urgencia' => 'required|string', // Enum
            
            'instruccion_presidencia' => 'nullable|string',

            'archivos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,xls,xlsx|max:10240', // max 10MB, por ahora o.o
            'fecha_atencion' => 'required|date',  //Esta quedó como la fecha de creación de la planilla en físico
            'fecha_solicitud' => 'required|date', //Esta quedó como la fecha de atención del formato en físico, creo

            'tipo_ente' => 'required|integer|exists:tipo_ente,CodTipoEnte',

            'categoria_solicitud' => 'required|string',
            'detalle_solicitud' => 'required|string'
    
    
    ]);

        // Iniciar transacción de base de datos
        DB::beginTransaction();

        try {

            $cedulaCompleta = $validatedData['tipo_cedula'] . $validatedData['cedula'];
            // --- 2. Gestionar Persona (Buscar o Crear) ---
            $persona = Persona::updateOrCreate(
                ['CedulaPersona' => $cedulaCompleta],
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
                'FechaSolicitud' => $validatedData['fecha_solicitud'],
                'FechaAtención' => $validatedData['fecha_atencion'],
                'TipoSolicitante' => $validatedData['tipo_solicitante'],
                'NivelUrgencia' => $validatedData['nivel_urgencia'],
      
                'AnexaDocumentos' => $request->hasFile('archivos'),
                'CantidadDocumentosOriginal' => 0, // Ajustar si es necesario, si no pues miau
                'CantidadDocumentoCopia' => 0, // Ajustar si es necesario
                'CantidadPaginasAnexo' => 0, // Ajustar si es necesario
                'CedulaPersona_FK' => $persona->CedulaPersona,
                'Nro_UAC' => $validatedData['nro_uac'],
                // 'CodigoInterno_FK' => $correspondencia->CodigoInterno,
                'Funcionario_FK' => auth()->user()->funcionarioData->CodFuncionario,
                'TipoSolicitud_FK' => $request->tipo_solicitud_fk ?? null, // Ajustar
            ]);

            
            // --- 4. Crear Relación de Correspondencia (Vínculo al Status) ---
            // El estado inicial siempre es 'Pendiente' (ID 1, según tu seeder)
           // $codigoInterno = 'CI-' . date('Ymd-His') . '-' . Str::random(4); //El que sirve


            // 1. Buscar el Tipo de Ente seleccionado
    $ente = TipoEnte::findOrFail($validatedData['tipo_ente']);

    // 2. Incrementar el contador de ese ente (ej: de 176 pasa a 177)
    $ente->increment('ContadorActual');

    // 3. Generar el código (Ej: CO + - + 00177)
    // str_pad rellena con ceros a la izquierda hasta llegar a 5 dígitos
    $codigoInterno = $ente->PrefijoCodigo . '-' . str_pad($ente->ContadorActual, 5, '0', STR_PAD_LEFT);

            $correspondencia = RelacionCorrespondencia::create([
                'CodigoInterno' => $codigoInterno,
                'Solicitud_FK' => $solicitud->CodSolicitud,
                'TipoEnte_FK' => $ente->CodTipoEnte,
                'Nro_Oficio' => $request->nro_oficio ?? 'N/A',
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
            

$cat = $request->categoria_solicitud;
            $det = $request->detalle_solicitud;
            $tipoId = null; // Guardará el ID de la tabla específica

            // Variables para las FKs
            $fk_servicio = null;
            $fk_infra = null;
            $fk_fortalecimiento = null;
            $fk_apoyo_inst = null;
            $fk_apoyo_ciud = null;

            // Insertar en la tabla específica según la categoría
            if ($cat == 'servicio_publico') {
                // Detectar si es alumbrado (el único de TendidosElectricos) o Hidraulico
                $columna = ($det == 'Alumbrado') ? 'TendidosElectricos' : 'Hidraulicos';
                $id = DB::table('servicio_publico')->insertGetId([$columna => $det]);
                $fk_servicio = $id;
            } 
            elseif ($cat == 'infraestructura_vial') {
                $id = DB::table('infraestructura_vial')->insertGetId(['Vialidad' => $det]);
                $fk_infra = $id;
            }
            elseif ($cat == 'fortalecimiento_instituciones') {
                $id = DB::table('fortalecimiento_instituciones')->insertGetId(['Edificaciones' => $det]);
                $fk_fortalecimiento = $id;
            }
            elseif ($cat == 'apoyo_instituciones') {
                $id = DB::table('apoyo_instituciones')->insertGetId(['EquipoMateriales' => $det]);
                $fk_apoyo_inst = $id;
            }
            elseif ($cat == 'apoyo_ciudadania') {
                $id = DB::table('apoyo_ciudadania')->insertGetId(['ApoyoCiudadania' => $det]);
                $fk_apoyo_ciud = $id;
            }

            // Crear registro en la tabla intermedia 'tipo_solicitud'
            $codTipoSolicitud = 'tsl_' . uniqid();// Generamos un ID único temporal
            
            DB::table('tipo_solicitud')->insert([
                'CodTipoSolicitud' => $codTipoSolicitud,
                'ServicioPublico_FK' => $fk_servicio,
                'InfraestructuraVial_FK' => $fk_infra,
                'FortalecimientoInstituciones_FK' => $fk_fortalecimiento,
                'ApoyoInstituciones_FK' => $fk_apoyo_inst,
                'ApoyoCiudadania_FK' => $fk_apoyo_ciud,
            ]);

            // Actualizar la Solicitud principal con este vínculo
            $solicitud->update(['TipoSolicitud_FK' => $codTipoSolicitud]);



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
            'archivos',
            'funcionario.persona'
        ])->findOrFail($id);

        $statuses = StatusSolicitud::all(); 

        // 1. DEFINIR LAS OPCIONES (Necesario para el modal)
        $categorias = [
            'servicio_publico' => [
                'label' => 'Servicios Públicos',
                'opciones' => ['Acueductos', 'Aguas Servidas (Cloacas)', 'Canalizaciones', 'Caños', 'Causes', 'Cuencas', 'Drenajes', 'Embaulamientos', 'Pozos Profundos', 'Aguas Pluviales', 'Sistema de Riego', 'Tanques de Almacenamiento', 'Torrentera', 'Alumbrado']
            ],
            'infraestructura_vial' => [
                'label' => 'Infraestructura Vial',
                'opciones' => ['Aceras', 'Bateas', 'Brocales', 'Vías, calles, carreteras, Vereda', 'Cajones', 'Alcantarillas', 'Cuñetas', 'Demarcación Vial', 'Estabilización de Talud', 'Falla de Borde', 'Pasarelas', 'Pavimentos Flexibles (Asfaltados)', 'Pavimento Rígido', 'Puentes']
            ],
            'fortalecimiento_instituciones' => [
                'label' => 'Fortalecimiento Inst.',
                'opciones' => ['Infraestructuras Asistenciales', 'Infraestructuras Educativas', 'Infraestructuras Gubernamentales', 'Instituciones Religiosas', 'Muros Diques', 'Pared Perimetrales']
            ],
            'apoyo_instituciones' => [
                'label' => 'Apoyo a Instituciones',
                'opciones' => ['Maquinarias', 'Camiones', 'Materiales', 'Cuadrillas, Obreros, Albañiles...', 'Levantamientos Topográficos', 'Estudios de Suelos', 'Informes de Inspección', 'Proyectos']
            ],
            'apoyo_ciudadania' => [
                'label' => 'Apoyo a la Ciudadanía',
                'opciones' => ['Pasantías', 'Comisiones de Servicio', 'Sínstesis Curriculares', 'Gubernamental a la Corporación', 'Empresas', 'Otros']
            ],
        ];

        // 2. DETECTAR CATEGORÍA Y DETALLE ACTUAL
        $tipoActual = DB::table('tipo_solicitud')->where('CodTipoSolicitud', $solicitud->TipoSolicitud_FK)->first();
        
        $catActual = null;
        $detActual = null;

        if ($tipoActual) {
            if ($tipoActual->ServicioPublico_FK) {
                $catActual = 'servicio_publico';
                // CORRECCIÓN: Usamos el nombre real de la columna ID
                $reg = DB::table('servicio_publico')->where('CodServicioPublico', $tipoActual->ServicioPublico_FK)->first();
                $detActual = $reg ? ($reg->TendidosElectricos ?? $reg->Hidraulicos) : null;
            
            } elseif ($tipoActual->InfraestructuraVial_FK) {
                $catActual = 'infraestructura_vial';
                $detActual = DB::table('infraestructura_vial')
                                ->where('CodInfraestructuraVial', $tipoActual->InfraestructuraVial_FK)
                                ->value('Vialidad');
            
            } elseif ($tipoActual->FortalecimientoInstituciones_FK) {
                $catActual = 'fortalecimiento_instituciones';
                $detActual = DB::table('fortalecimiento_instituciones')
                                ->where('CodFortalecimientoInstituciones', $tipoActual->FortalecimientoInstituciones_FK)
                                ->value('Edificaciones');
            
            } elseif ($tipoActual->ApoyoInstituciones_FK) {
                $catActual = 'apoyo_instituciones';
                $detActual = DB::table('apoyo_instituciones')
                                ->where('CodApoyoInstituciones', $tipoActual->ApoyoInstituciones_FK)
                                ->value('EquipoMateriales');
            
            } elseif ($tipoActual->ApoyoCiudadania_FK) {
                $catActual = 'apoyo_ciudadania';
                $detActual = DB::table('apoyo_ciudadania')
                                ->where('CodApoyoCiudadania', $tipoActual->ApoyoCiudadania_FK)
                                ->value('ApoyoCiudadania');
            }
        }

        // 3. ENVIAR VARIABLES A LA VISTA (Aquí es donde fallaba antes)
        return view('solicitudes.show', compact('solicitud', 'statuses', 'categorias', 'catActual', 'detActual'));
    }

    /**
     * Actualiza el estado de una solicitud.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status_id' => 'required|integer|exists:status_solicitud,CodStatusSolicitud']);
        
        $solicitud = Solicitud::findOrFail($id);

        // BLOQUEO DE SEGURIDAD (Opcional: si quieres impedir reactivarlas)
        if ($solicitud->correspondencia->StatusSolicitud_FK == 7) {
            return back()->with('error', 'Esta solicitud está ANULADA y no puede cambiar de estado.');
        }
        
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

/**
     * Actualiza los datos del flujo de correspondencia (Nro Oficio, Instrucciones).
     */
public function updateFlujo(Request $request, $id)
    {
        // 1. Validar
        $validatedData = $request->validate([
            'Nro_Oficio' => 'required|string|max:100',
            'InstruccionPresidencia' => 'nullable|string',
            'Observacion' => 'nullable|string',
            // Validamos el nuevo campo
            'TipoSolicitudPlanilla' => 'required|string',
            'categoria_solicitud' => 'required|string',
            'detalle_solicitud' => 'required|string'
        ]);

        try {
            $solicitud = Solicitud::findOrFail($id);
            
            // 2. Actualizar la correspondencia (Datos del flujo)
            $solicitud->correspondencia->update([
                'Nro_Oficio' => $validatedData['Nro_Oficio'],
                'InstruccionPresidencia' => $validatedData['InstruccionPresidencia'] ?? '',
                'Observacion' => $validatedData['Observacion'] ?? '',
            ]);

            // 3. Actualizar el Tipo de Solicitud en la tabla PADRE (solicitud)
            $solicitud->update([
                'TipoSolicitudPlanilla' => $validatedData['TipoSolicitudPlanilla']
            ]);



            // 3. ACTUALIZAR CLASIFICACIÓN (TIPO SOLICITUD)
            $cat = $request->categoria_solicitud;
            $det = $request->detalle_solicitud;

            // Insertar el nuevo detalle en la tabla específica
            $nuevoId = null;
            if ($cat == 'servicio_publico') {
                $columna = ($det == 'Alumbrado') ? 'TendidosElectricos' : 'Hidraulicos';
                $nuevoId = DB::table('servicio_publico')->insertGetId([$columna => $det]);
            } elseif ($cat == 'infraestructura_vial') {
                $nuevoId = DB::table('infraestructura_vial')->insertGetId(['Vialidad' => $det]);
            } elseif ($cat == 'fortalecimiento_instituciones') {
                $nuevoId = DB::table('fortalecimiento_instituciones')->insertGetId(['Edificaciones' => $det]);
            } elseif ($cat == 'apoyo_instituciones') {
                $nuevoId = DB::table('apoyo_instituciones')->insertGetId(['EquipoMateriales' => $det]);
            } elseif ($cat == 'apoyo_ciudadania') {
                $nuevoId = DB::table('apoyo_ciudadania')->insertGetId(['ApoyoCiudadania' => $det]);
            }

            // Preparar datos para actualizar la tabla intermedia
            $updateData = [
                'ServicioPublico_FK' => ($cat == 'servicio_publico') ? $nuevoId : null,
                'InfraestructuraVial_FK' => ($cat == 'infraestructura_vial') ? $nuevoId : null,
                'FortalecimientoInstituciones_FK' => ($cat == 'fortalecimiento_instituciones') ? $nuevoId : null,
                'ApoyoInstituciones_FK' => ($cat == 'apoyo_instituciones') ? $nuevoId : null,
                'ApoyoCiudadania_FK' => ($cat == 'apoyo_ciudadania') ? $nuevoId : null,
            ];

            if ($solicitud->TipoSolicitud_FK) {
                DB::table('tipo_solicitud')
                    ->where('CodTipoSolicitud', $solicitud->TipoSolicitud_FK)
                    ->update($updateData);
            } else {
                // Caso raro: si no tenía registro previo
                $newCod = 'tsl_' . uniqid();
                $updateData['CodTipoSolicitud'] = $newCod;
                DB::table('tipo_solicitud')->insert($updateData);
                $solicitud->update(['TipoSolicitud_FK' => $newCod]);
            }

            return back()->with('success', 'Relación correspondencia y Tipo de Solicitud actualizados correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }





    public function edit($id)
{
    $solicitud = Solicitud::with(['persona', 'correspondencia'])->findOrFail($id);
    
// BLOQUEO DE SEGURIDAD
        if ($solicitud->correspondencia && $solicitud->correspondencia->StatusSolicitud_FK == 7) {
            return redirect()->route('solicitudes.show', $id)
                ->with('error', 'No se puede editar una solicitud que está ANULADA.');
        }


    // Listas para los selects
    $tiposEnte = TipoEnte::all(['CodTipoEnte', 'NombreEnte']);
    $municipios = Municipio::with('parroquias:CodParroquia,NombreParroquia,Municipio_FK')
                    ->get(['CodMunicipio', 'NombreMunicipio']);
    
    // Separar la cédula (Tipo y Número) para el formulario
    // Asumiendo que los primeros 2 caracteres son el tipo (ej. "V-")
    $tipo_cedula_actual = substr($solicitud->persona->CedulaPersona, 0, 2); 
    $cedula_numero_actual = substr($solicitud->persona->CedulaPersona, 2);

    return view('solicitudes.edit', compact(
        'solicitud', 'tiposEnte', 'municipios', 'tipo_cedula_actual', 'cedula_numero_actual'
    ));
}



public function update(Request $request, $id)
{
    $solicitud = Solicitud::findOrFail($id);

    // BLOQUEO DE SEGURIDAD
        if ($solicitud->correspondencia && $solicitud->correspondencia->StatusSolicitud_FK == 7) {
            return back()->with('error', 'Acción denegada: La solicitud está ANULADA.');
        }

    // 1. Validación (Igual al store, pero ajustando el unique de Nro_UAC para ignorar el actual)
    $validatedData = $request->validate([
        'tipo_cedula' => ['required', 'string', 'max:2', Rule::in(['V-', 'E-', 'J-', 'P-', 'G-'])],
        'cedula' => ['required', 'string', 'max:20'],
        'nombres' => 'required|string|max:100',
        'apellidos' => 'required|string|max:100',
        'telefono' => 'required|string|max:15',
        'parroquia_id' => 'required|integer', 
        'email' => 'nullable|email',
        
        // Ignoramos el ID actual para que no de error si no cambiamos el Nro UAC
        'nro_uac' => ['nullable', 'string', 'max:50', Rule::unique('solicitud', 'Nro_UAC')->ignore($solicitud->CodSolicitud, 'CodSolicitud')],
        
        'tipo_solicitud_planilla' => 'required|string',
        'descripcion' => 'required|string',
        'tipo_solicitante' => 'required|string',
        'nivel_urgencia' => 'required|string',
        
        'fecha_solicitud' => 'required|date', // Transcripción Paso 1
        'fecha_atencion' => 'required|date',  // Transcripción Paso 2
    ]);

    DB::beginTransaction();

    try {
        // 2. Actualizar Persona
        $cedulaCompleta = $validatedData['tipo_cedula'] . $validatedData['cedula'];
        
        // Nota: Si cambian la cédula, hay que tener cuidado. 
        // Lo ideal es actualizar la persona existente vinculada.
        $persona = $solicitud->persona;
        $persona->update([
            'CedulaPersona' => $cedulaCompleta, // Cuidado: esto cambia la PK, Laravel puede quejarse si no está configurado cascade
            'NombresPersona' => $validatedData['nombres'],
            'ApellidosPersona' => $validatedData['apellidos'],
            'TelefonoPersona' => $validatedData['telefono'],
            'ParroquiaPersona_FK' => $validatedData['parroquia_id'],
            'CorreoElectronicoPersona' => $validatedData['email'] ?? '',
        ]);

        // 3. Actualizar Solicitud
        $solicitud->update([
            'TipoSolicitudPlanilla' => $validatedData['tipo_solicitud_planilla'],
            'DescripcionSolicitud' => $validatedData['descripcion'],
            'FechaSolicitud' => $validatedData['fecha_solicitud'], // Actualiza fecha del papel
            'FechaAtención' => $validatedData['fecha_atencion'], // Actualiza fecha de recepción
            'TipoSolicitante' => $validatedData['tipo_solicitante'],
            'NivelUrgencia' => $validatedData['nivel_urgencia'],
            'Nro_UAC' => $validatedData['nro_uac'],
            // Actualizamos la FK de persona por si cambió la cédula
            'CedulaPersona_FK' => $cedulaCompleta, 
        ]);

        // 4. Actualizar Correspondencia (opcional, datos básicos)
        if ($solicitud->correspondencia) {
            $solicitud->correspondencia->update([
                'Descripcion' => $validatedData['descripcion'], // Mantener sincronizada la descripción
                'Municipio_FK' => $persona->parroquia->Municipio_FK,
            ]);
        }

        DB::commit();
        return redirect()->route('solicitudes.show', $solicitud->CodSolicitud)->with('success', 'Solicitud actualizada correctamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
    }
}



/** Anula una solicitud (No la borra, cambia su estado a 7). **/
    public function anular($id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
            
            // Buscamos la correspondencia y cambiamos el status a 7 (Anulada)
            // Asumiendo que la relación ya existe
            if($solicitud->correspondencia) {
                $solicitud->correspondencia->update([
                    'StatusSolicitud_FK' => 7, 
                    'Observacion' => $solicitud->correspondencia->Observacion . "\n[ANULADA por " . auth()->user()->NombreUsuario . " el " . now() . "]",
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'La solicitud ha sido anulada correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }
    


    /** Muestra el historial de solicitudes ANULADAS (Papelera).**/
public function anuladas(Request $request)
    {
        $solicitudes = Solicitud::whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', 7); // 7 = Anulada
        })
        ->with(['persona.parroquia.municipio', 'correspondencia.status'])
        ->orderBy('CodSolicitud', 'desc') 
        ->paginate(20);

        return view('solicitudes.anuladas', compact('solicitudes'));
    }

}
