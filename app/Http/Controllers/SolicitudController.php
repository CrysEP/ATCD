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
use Barryvdh\DomPDF\Facade\Pdf;

class SolicitudController extends Controller
{
    public function index(Request $request)
    {
        $query = Solicitud::query();

        $query->whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', 1);
        });

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
        if ($request->filled('fecha_desde')) {
            $query->whereDate('FechaSolicitud', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('FechaSolicitud', '<=', $request->fecha_hasta);
        }
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

        $solicitudes = $query->with(['persona.parroquia.municipio', 'correspondencia.status'])
                             ->orderBy('FechaSolicitud', 'desc')
                             ->paginate(20);

        return view('dashboard', compact('solicitudes'));
    }

    public function create()
    {
        $tiposEnte = TipoEnte::all(['CodTipoEnte', 'NombreEnte']);
        $municipios = Municipio::with('parroquias:CodParroquia,NombreParroquia,Municipio_FK')
                        ->get(['CodMunicipio', 'NombreMunicipio']);
        $funcionario = Usuario::with('persona', 'funcionarioData')->find(Auth::id());

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

        return view('solicitudes.create', compact('tiposEnte', 'municipios', 'funcionario', 'categorias'));
    }

    public function store(Request $request)
    {
        $reglas = [
            'tipo_cedula' => ['required', 'string', 'max:2', Rule::in(['V-', 'E-', 'J-', 'P-', 'G-'])],
            'cedula' => 'required|string|max:20|regex:/^[0-9]+$/',
            'nombres' => 'required|string|max:100|regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/',
            'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/',
            'telefono' => 'required|string|max:15|regex:/^[0-9\-\+\s\(\)]+$/',
            'parroquia_id' => 'required|integer', 
            'email' => ['nullable', 'email', 'max:200', 'ends_with:@gmail.com,@outlook.com,@hotmail.com,@yahoo.com,@live.com'],
            'sexo' => ['required', 'string', Rule::in(['M', 'F'])],
            'direccion_habitacion' => 'required|string',
            'punto_referencia' => 'nullable|string',
            'fecha_nacimiento' => 'required|date|before:tomorrow',
            
            // --- CAMBIOS A PLURAL ---
            'nro_uac' => 'nullable|string|max:50|unique:solicitudes,Nro_UAC',
            'tipo_ente' => 'required|integer|exists:tipos_entes,CodTipoEnte',
            // ------------------------

            'tipo_solicitud_planilla' => 'required|string',
            'descripcion' => 'required|string',
            'tipo_solicitante' => 'required|string',
            'nivel_urgencia' => 'required|string',
            'instruccion_presidencia' => 'nullable|string',
            'observacion' => 'nullable|string',
            'archivos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,xls,xlsx|max:10240',
            'fecha_atencion' => 'required|date',
            'fecha_solicitud' => 'required|date',
            'categoria_solicitud' => 'nullable|string|required_if:tipo_solicitud_planilla,Solicitud o Petición',
            'detalle_solicitud' => 'nullable|string|required_if:tipo_solicitud_planilla,Solicitud o Petición',

            'AnexaDocumentos' => 'required|boolean',
            'CantidadDocumentosOriginal' => 'required|integer|min:0',
            'CantidadDocumentoCopia' => 'required|integer|min:0',
            'CantidadPaginasAnexo' => 'required|integer|min:0',
        ];

        $mensajes = [
            'email.email' => 'El correo electrónico no es válido.',
            'email.ends_with' => 'Solo se aceptan correos de: Gmail, Outlook, Hotmail, Yahoo o Live.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento no puede ser futura.',
        ];

        $validatedData = $request->validate($reglas, $mensajes);

        DB::beginTransaction();

        try {
            $cedulaCompleta = $validatedData['tipo_cedula'] . $validatedData['cedula'];
            
            $persona = Persona::updateOrCreate(
                ['CedulaPersona' => $cedulaCompleta],
                [
                    'NombresPersona' => $validatedData['nombres'],
                    'ApellidosPersona' => $validatedData['apellidos'],
                    'TelefonoPersona' => $validatedData['telefono'],
                    'ParroquiaPersona_FK' => $validatedData['parroquia_id'],
                    'CorreoElectronicoPersona' => $validatedData['email'] ?? '',
                    'FechaNacPersona' => $validatedData['fecha_nacimiento'],
                    'SexoPersona' => $validatedData['sexo'],
                ]
            );

            $solicitud = Solicitud::create([
                'TipoSolicitudPlanilla' => $validatedData['tipo_solicitud_planilla'],
                'DescripcionSolicitud' => $validatedData['descripcion'],
                'FechaSolicitud' => $validatedData['fecha_solicitud'],
                'FechaAtención' => $validatedData['fecha_atencion'],
                'TipoSolicitante' => $validatedData['tipo_solicitante'],
                'NivelUrgencia' => $validatedData['nivel_urgencia'],
                'AnexaDocumentos' => $request->AnexaDocumentos,
                'CantidadDocumentosOriginal' => $request->CantidadDocumentosOriginal ?? 0,
                'CantidadDocumentoCopia' => $request->CantidadDocumentoCopia ?? 0,
                'CantidadPaginasAnexo' => $request->CantidadPaginasAnexo ?? 0,
                'DirecciónHabitación' => $validatedData['direccion_habitacion'],
                'PuntoReferencia' => $validatedData['punto_referencia'],
                'CedulaPersona_FK' => $persona->CedulaPersona,
                'Nro_UAC' => $validatedData['nro_uac'],
                'Funcionario_FK' => auth()->user()->funcionarioData->CodFuncionario,
                'TipoSolicitud_FK' => null,
            ]);

            $ente = TipoEnte::findOrFail($validatedData['tipo_ente']);
            $ente->increment('ContadorActual');
            $codigoInterno = $ente->PrefijoCodigo . '-' . str_pad($ente->ContadorActual, 5, '0', STR_PAD_LEFT);

            $correspondencia = RelacionCorrespondencia::create([
                'CodigoInterno' => $codigoInterno,
                'Solicitud_FK' => $solicitud->CodSolicitud,
                'TipoEnte_FK' => $ente->CodTipoEnte,
                'Nro_Oficio' => $request->nro_oficio ?? 'N/A',
                'FechaOficioEntrega' => now(),
                'FechaRecibido' => now(),
                'Municipio_FK' => $persona->parroquia->Municipio_FK,
                'Ente' => 0,
                'Sector' => $request->sector ?? 'N/A',
                'Descripcion' => $validatedData['descripcion'],
                'InstruccionPresidencia' => $validatedData['instruccion_presidencia'] ?? '',
                'Observacion' => $validatedData['observacion'] ?? '',
                'Gerencia_Jefatura' => '',
                'StatusSolicitud_FK' => 1,
            ]);

            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    $path = $file->store('solicitudes', 'public');
                    $solicitud->archivos()->create([
                        'nombre_original' => $file->getClientOriginalName(),
                        'ruta_archivo' => $path,
                        'tipo_archivo' => $file->getClientMimeType(),
                        'tamano_archivo' => $file->getSize(),
                    ]);
                }
            }
            
            if ($validatedData['tipo_solicitud_planilla'] === 'Solicitud o Petición') {
            // Clasificación
            $cat = $request->categoria_solicitud;
            $det = $request->detalle_solicitud;
            
            $fk_servicio = null;
            $fk_infra = null;
            $fk_fortalecimiento = null;
            $fk_apoyo_inst = null;
            $fk_apoyo_ciud = null;

            if ($cat == 'servicio_publico') {
                $columna = ($det == 'Alumbrado') ? 'TendidosElectricos' : 'Hidraulicos';
                $id = DB::table('servicio_publico')->insertGetId([$columna => $det]);
                $fk_servicio = $id;
            } elseif ($cat == 'infraestructura_vial') {
                $id = DB::table('infraestructura_vial')->insertGetId(['Vialidad' => $det]);
                $fk_infra = $id;
            } elseif ($cat == 'fortalecimiento_instituciones') {
                $id = DB::table('fortalecimiento_instituciones')->insertGetId(['Edificaciones' => $det]);
                $fk_fortalecimiento = $id;
            } elseif ($cat == 'apoyo_instituciones') {
                $id = DB::table('apoyo_instituciones')->insertGetId(['EquipoMateriales' => $det]);
                $fk_apoyo_inst = $id;
            } elseif ($cat == 'apoyo_ciudadania') {
                $id = DB::table('apoyo_ciudadania')->insertGetId(['ApoyoCiudadania' => $det]);
                $fk_apoyo_ciud = $id;
            }

            $codTipoSolicitud = 'tsl_' . uniqid();
            
            // --- CAMBIO A PLURAL ---
            DB::table('tipos_solicitudes')->insert([
                'CodTipoSolicitud' => $codTipoSolicitud,
                'ServicioPublico_FK' => $fk_servicio,
                'InfraestructuraVial_FK' => $fk_infra,
                'FortalecimientoInstituciones_FK' => $fk_fortalecimiento,
                'ApoyoInstituciones_FK' => $fk_apoyo_inst,
                'ApoyoCiudadania_FK' => $fk_apoyo_ciud,
            ]);

            $solicitud->update(['TipoSolicitud_FK' => $codTipoSolicitud]);
            }

            DB::commit();
            return redirect()->route('dashboard')->with('success', 'Solicitud registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $solicitud = Solicitud::with([
            'persona.parroquia.municipio', 
            'correspondencia.status',
            'archivos',
            'funcionario.persona'
        ])->findOrFail($id);

        $statuses = StatusSolicitud::all(); 
        
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

        // --- CAMBIO A PLURAL ---
        $tipoActual = DB::table('tipos_solicitudes')->where('CodTipoSolicitud', $solicitud->TipoSolicitud_FK)->first();
        $tiposEnte = TipoEnte::all(['CodTipoEnte', 'NombreEnte']);
        
        $catActual = null;
        $detActual = null;

        if ($tipoActual) {
            if ($tipoActual->ServicioPublico_FK) {
                $catActual = 'servicio_publico';
                $reg = DB::table('servicio_publico')->where('CodServicioPublico', $tipoActual->ServicioPublico_FK)->first();
                $detActual = $reg ? ($reg->TendidosElectricos ?? $reg->Hidraulicos) : null;
            } elseif ($tipoActual->InfraestructuraVial_FK) {
                $catActual = 'infraestructura_vial';
                $detActual = DB::table('infraestructura_vial')->where('CodInfraestructuraVial', $tipoActual->InfraestructuraVial_FK)->value('Vialidad');
            } elseif ($tipoActual->FortalecimientoInstituciones_FK) {
                $catActual = 'fortalecimiento_instituciones';
                $detActual = DB::table('fortalecimiento_instituciones')->where('CodFortalecimientoInstituciones', $tipoActual->FortalecimientoInstituciones_FK)->value('Edificaciones');
            } elseif ($tipoActual->ApoyoInstituciones_FK) {
                $catActual = 'apoyo_instituciones';
                $detActual = DB::table('apoyo_instituciones')->where('CodApoyoInstituciones', $tipoActual->ApoyoInstituciones_FK)->value('EquipoMateriales');
            } elseif ($tipoActual->ApoyoCiudadania_FK) {
                $catActual = 'apoyo_ciudadania';
                $detActual = DB::table('apoyo_ciudadania')->where('CodApoyoCiudadania', $tipoActual->ApoyoCiudadania_FK)->value('ApoyoCiudadania');
            }
        }

        return view('solicitudes.show', compact('solicitud', 'statuses', 'categorias', 'catActual', 'detActual', 'tiposEnte'));
    }

    public function updateStatus(Request $request, $id)
    {
        // --- CAMBIO A PLURAL ---
        $request->validate(['status_id' => 'required|integer|exists:status_solicitudes,CodStatusSolicitud']);
        
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->correspondencia->StatusSolicitud_FK == 7) {
            return back()->with('error', 'Esta solicitud está ANULADA y no puede cambiar de estado.');
        }
        
        $solicitud->correspondencia->update([
            'StatusSolicitud_FK' => $request->status_id,
            'InstruccionPresidencia' => $solicitud->correspondencia->InstruccionPresidencia . "\n[Actualizado por " . auth()->user()->NombreUsuario . " el " . now() . "]: " . $request->observacion,
        ]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function updateFlujo(Request $request, $id)
    {
        $validatedData = $request->validate([
            'Nro_Oficio' => 'required|string|max:100',
            'InstruccionPresidencia' => 'nullable|string',
            'Observacion' => 'nullable|string',
            'TipoSolicitudPlanilla' => 'required|string',
            'Gerencia_Jefatura' => 'nullable|string|max:255',
            'Sector' => 'nullable|string|max:300',
            'categoria_solicitud' => 'required|string',
            'detalle_solicitud' => 'required|string',
            // --- CAMBIO A PLURAL ---
            'tipo_ente' => 'required|integer|exists:tipos_entes,CodTipoEnte'
        ]);

        try {
            $solicitud = Solicitud::findOrFail($id);
            
            if ($solicitud->correspondencia->StatusSolicitud_FK == 7) {
                return back()->with('error', 'No se puede editar una solicitud ANULADA.');
            }

            $solicitud->correspondencia->update([
                'Nro_Oficio' => $validatedData['Nro_Oficio'],
                'InstruccionPresidencia' => $validatedData['InstruccionPresidencia'] ?? '',
                'Observacion' => $validatedData['Observacion'] ?? '',
                'Gerencia_Jefatura' => $validatedData['Gerencia_Jefatura'] ?? '',
                'Sector' => $validatedData['Sector'] ?? 'N/A',
                'TipoEnte_FK' => $validatedData['tipo_ente'],
            ]);

            $solicitud->update(['TipoSolicitudPlanilla' => $validatedData['TipoSolicitudPlanilla']]);

            // Actualizar Clasificación
            $cat = $request->categoria_solicitud;
            $det = $request->detalle_solicitud;
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

            $updateData = [
                'ServicioPublico_FK' => ($cat == 'servicio_publico') ? $nuevoId : null,
                'InfraestructuraVial_FK' => ($cat == 'infraestructura_vial') ? $nuevoId : null,
                'FortalecimientoInstituciones_FK' => ($cat == 'fortalecimiento_instituciones') ? $nuevoId : null,
                'ApoyoInstituciones_FK' => ($cat == 'apoyo_instituciones') ? $nuevoId : null,
                'ApoyoCiudadania_FK' => ($cat == 'apoyo_ciudadania') ? $nuevoId : null,
            ];

            if ($solicitud->TipoSolicitud_FK) {
                // --- CAMBIO A PLURAL ---
                DB::table('tipos_solicitudes')
                    ->where('CodTipoSolicitud', $solicitud->TipoSolicitud_FK)
                    ->update($updateData);
            } else {
                $newCod = 'tsl_' . uniqid();
                $updateData['CodTipoSolicitud'] = $newCod;
                // --- CAMBIO A PLURAL ---
                DB::table('tipos_solicitudes')->insert($updateData);
                $solicitud->update(['TipoSolicitud_FK' => $newCod]);
            }

            return back()->with('success', 'Datos actualizados correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $solicitud = Solicitud::with(['persona', 'correspondencia'])->findOrFail($id);
        
        if ($solicitud->correspondencia && $solicitud->correspondencia->StatusSolicitud_FK == 7) {
            return redirect()->route('solicitudes.show', $id)->with('error', 'No se puede editar una solicitud ANULADA.');
        }

        $tiposEnte = TipoEnte::all(['CodTipoEnte', 'NombreEnte']);
        $municipios = Municipio::with('parroquias:CodParroquia,NombreParroquia,Municipio_FK')->get(['CodMunicipio', 'NombreMunicipio']);
        
        $tipo_cedula_actual = substr($solicitud->persona->CedulaPersona, 0, 2); 
        $cedula_numero_actual = substr($solicitud->persona->CedulaPersona, 2);

        return view('solicitudes.edit', compact('solicitud', 'tiposEnte', 'municipios', 'tipo_cedula_actual', 'cedula_numero_actual'));
    }

    public function update(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->correspondencia && $solicitud->correspondencia->StatusSolicitud_FK == 7) {
            return back()->with('error', 'Acción denegada: La solicitud está ANULADA.');
        }

        $validatedData = $request->validate([
            'tipo_cedula' => ['required', 'string', 'max:2', Rule::in(['V-', 'E-', 'J-', 'P-', 'G-'])],
            'cedula' => ['required', 'string', 'max:20'],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'required|string|max:15',
            'parroquia_id' => 'required|integer', 
            'email' => ['nullable', 'email', 'max:200', 'ends_with:@gmail.com,@outlook.com,@hotmail.com,@yahoo.com,@live.com'],
            // --- CAMBIO A PLURAL ---
            'nro_uac' => ['nullable', 'string', 'max:50', Rule::unique('solicitudes', 'Nro_UAC')->ignore($solicitud->CodSolicitud, 'CodSolicitud')],
            
            'tipo_solicitud_planilla' => 'required|string',
            'descripcion' => 'required|string',
            'tipo_solicitante' => 'required|string',
            'nivel_urgencia' => 'required|string',
            'fecha_solicitud' => 'required|date',
            'fecha_atencion' => 'required|date',
            'fecha_nacimiento' => 'required|date|before:tomorrow',
            'direccion_habitacion' => 'required|string',
            'punto_referencia' => 'nullable|string',
            'tipo_ente' => 'required|integer|exists:tipos_entes,CodTipoEnte',
        ]);

        DB::beginTransaction();

        try {
            $cedulaCompleta = $validatedData['tipo_cedula'] . $validatedData['cedula'];
            
            $solicitud->persona->update([
                'CedulaPersona' => $cedulaCompleta,
                'NombresPersona' => $validatedData['nombres'],
                'ApellidosPersona' => $validatedData['apellidos'],
                'TelefonoPersona' => $validatedData['telefono'],
                'ParroquiaPersona_FK' => $validatedData['parroquia_id'],
                'CorreoElectronicoPersona' => $validatedData['email'] ?? '',
                'SexoPersona' => $request->sexo ?? $solicitud->persona->SexoPersona,
                'FechaNacPersona' => $validatedData['fecha_nacimiento'],
            ]);

            $solicitud->update([
                'TipoSolicitudPlanilla' => $validatedData['tipo_solicitud_planilla'],
                'DirecciónHabitación' => $validatedData['direccion_habitacion'],
                'PuntoReferencia' => $validatedData['punto_referencia'],
                'DescripcionSolicitud' => $validatedData['descripcion'],
                'FechaSolicitud' => $validatedData['fecha_solicitud'],
                'FechaAtención' => $validatedData['fecha_atencion'],
                'TipoSolicitante' => $validatedData['tipo_solicitante'],
                'NivelUrgencia' => $validatedData['nivel_urgencia'],
                'Nro_UAC' => $validatedData['nro_uac'],
                'CedulaPersona_FK' => $cedulaCompleta, 

            ]);

            if ($solicitud->correspondencia) {
                $solicitud->correspondencia->update([
                    'Descripcion' => $validatedData['descripcion'],
                    'Municipio_FK' => $solicitud->persona->parroquia->Municipio_FK,
                    'TipoEnte_FK' => $validatedData['tipo_ente'],
                ]);
            }

            DB::commit();
            return redirect()->route('solicitudes.show', $solicitud->CodSolicitud)->with('success', 'Datos actualizados.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $query = Solicitud::whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', '!=', 1)->where('StatusSolicitud_FK', '!=', 7); 
        });

        if ($request->filled('urgencia')) {
            $query->where('NivelUrgencia', $request->urgencia);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('FechaSolicitud', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('FechaSolicitud', '<=', $request->fecha_hasta);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('Nro_UAC', 'like', "%{$search}%")
                  ->orWhere('DescripcionSolicitud', 'like', "%{$search}%")
                  ->orWhereHas('persona', function ($q_persona) use ($search) {
                      $q_persona->where('NombresPersona', 'like', "%{$search}%")
                                ->orWhere('ApellidosPersona', 'like', "%{$search}%")
                                ->orWhere('CedulaPersona', 'like', "%{$search}%");
                  })
                  ->orWhereHas('correspondencia', function ($q_corr) use ($search) {
                      $q_corr->where('CodigoInterno', 'like', "%{$search}%");
                  });
            });
        }

        $solicitudes = $query->with(['persona.parroquia.municipio', 'correspondencia.status'])
                             ->orderBy('FechaSolicitud', 'desc')
                             ->paginate(20);

        return view('solicitudes.history', compact('solicitudes'));
    }

    public function anular($id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
            if($solicitud->correspondencia) {
                $solicitud->correspondencia->update([
                    'StatusSolicitud_FK' => 7, 
                    'Observacion' => $solicitud->correspondencia->Observacion . "\n[ANULADA por " . auth()->user()->NombreUsuario . " el " . now() . "]",
                ]);
            }
            return redirect()->route('dashboard')->with('success', 'Solicitud anulada.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    public function anuladas(Request $request)
    {
        $solicitudes = Solicitud::whereHas('correspondencia', function ($q) {
            $q->where('StatusSolicitud_FK', 7);
        })
        ->with(['persona.parroquia.municipio', 'correspondencia.status'])
        ->orderBy('CodSolicitud', 'desc') 
        ->paginate(20);

        return view('solicitudes.anuladas', compact('solicitudes'));
    }

    public function restaurar($id)
    {
        try {
            $solicitud = Solicitud::findOrFail($id);
            if($solicitud->correspondencia) {
                $solicitud->correspondencia->update([
                    'StatusSolicitud_FK' => 1, 
                    'Observacion' => $solicitud->correspondencia->Observacion . "\n[RESTAURADA por " . auth()->user()->NombreUsuario . " el " . now() . "]",
                ]);
            }
            return back()->with('success', 'Solicitud restaurada.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function generarPDF($id)
    {
        $solicitud = Solicitud::with([
            'persona.parroquia.municipio',
            'correspondencia',
            'funcionario.persona' 
        ])->findOrFail($id);

        $pdf = Pdf::loadView('solicitudes.pdf', compact('solicitud'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('Solicitud-UAC-' . ($solicitud->Nro_UAC ?? $solicitud->CodSolicitud) . '.pdf');
    }

    public function downloadFile($id)
    {
        $archivo = \App\Models\ArchivoSolicitud::findOrFail($id);
        return Storage::disk('public')->download($archivo->ruta_archivo, $archivo->nombre_original);
    }

    
/** Genera un ZIP con los PDFs de las solicitudes procesadas en un rango de fechas. **/
    public function exportarZip(Request $request)
    {
        $request->validate([
            'fecha_desde_export' => 'required|date',
            'fecha_hasta_export' => 'required|date|after_or_equal:fecha_desde_export',
        ]);
        // 1. Aumentar tiempo de ejecución (generar muchos PDFs toma tiempo)
        set_time_limit(300); // 5 minutos

        // 2. Buscar Solicitudes PROCESADAS (Igual que en el historial: != 1 y != 7)
        // Usamos whereBetween para el rango de fechas (incluye las horas del día final)
        $solicitudes = Solicitud::whereHas('correspondencia', function ($q) {
                $q->where('StatusSolicitud_FK', '!=', 1)
                  ->where('StatusSolicitud_FK', '!=', 7);
            })
            ->whereDate('FechaSolicitud', '>=', $request->fecha_desde_export)
            ->whereDate('FechaSolicitud', '<=', $request->fecha_hasta_export)
            ->with(['persona.parroquia.municipio', 'correspondencia.status', 'funcionario.persona'])
            ->get();

        if ($solicitudes->isEmpty()) {
            return back()->with('error', 'No se encontraron solicitudes procesadas en ese rango de fechas.');
        }

        // 3. Crear el archivo ZIP temporal
        $zipName = 'reporte-mensual-solicitudes-corpointa-' . now()->format('Ymd-His') . '.zip';
        $zipPath = storage_path('app/public/' . $zipName);
        
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            
            foreach ($solicitudes as $solicitud) {
                // Generar PDF en memoria (vista pdf.blade.php)
                $pdf = Pdf::loadView('solicitudes.pdf', compact('solicitud'));
                $pdf->setPaper('letter', 'portrait');
                $content = $pdf->output();

                // Nombre del archivo dentro del ZIP
                // $codigoLimpio = preg_replace('/[^A-Za-z0-9\-]/', '', ($solicitud->Nro_UAC ?? 'Sin-UAC'));
                $nombreArchivo = 'Solicitud-' . ($solicitud->Nro_UAC ?? $solicitud->CodSolicitud) . '.pdf';
                
                // Agregar al ZIP
                $zip->addFromString($nombreArchivo, $content);
            }
            
            $zip->close();
        } else {
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }

        // 4. Descargar y luego eliminar el temporal
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

}