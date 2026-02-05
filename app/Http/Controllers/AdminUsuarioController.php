<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Funcionario;
use App\Models\Departamento;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // ¡Importante para validar al editar!

class AdminUsuarioController extends Controller
{
    // 1. LISTADO DE USUARIOS (NUEVO)
    public function index()
    {
        $usuarios = Usuario::with(['persona', 'funcionarioData.departamento'])->paginate(10);
        return view('admin.usuarios.index', compact('usuarios'));
    }

    // 2. FORMULARIO DE CREACIÓN (YA LO TENÍAS)
    public function create()
    {
        $departamentos = Departamento::all();
        return view('admin.usuarios.create', compact('departamentos'));
    }

    // 3. GUARDAR NUEVO USUARIO (YA LO TENÍAS)
    public function store(Request $request)
    {
        $cedulaCompleta = $request->tipo_cedula . $request->cedula;
        $request->merge(['cedula_completa' => $cedulaCompleta]);

        $request->validate([
            'cedula_completa' => 'required|unique:personas,CedulaPersona',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'nombre_usuario' => 'required|string|max:100|unique:usuarios,NombreUsuario',
            'password' => 'required|string|min:6|confirmed',
            'rol' => 'required|in:Administrador,UsuarioPersonal,Externo',
            'departamento_id' => 'required_if:rol,UsuarioPersonal',
            'cargo' => 'required_if:rol,UsuarioPersonal',
        ]);

        DB::beginTransaction();

        try {
            $persona = Persona::firstOrCreate(
                ['CedulaPersona' => $request->cedula_completa],
                [
                    'NombresPersona' => strtoupper($request->nombres),
                    'ApellidosPersona' => strtoupper($request->apellidos),
                    'FechaNacPersona' => now(),
                    'SexoPersona' => 'M',
                    'TelefonoPersona' => '0000-0000000',
                    'ParroquiaPersona_FK' => 1,
                    'CorreoElectronicoPersona' => ''
                ]
            );

            $usuario = Usuario::create([
                'NombreUsuario' => $request->nombre_usuario,
                'ContraseniaUsuario' => Hash::make($request->password),
                'RolUsuario' => $request->rol,
                'EstadoUsuario' => 'Activo',
                'CedulaPersonaUsuario_FK' => $persona->CedulaPersona,
            ]);

            if ($request->rol === 'UsuarioPersonal' || $request->rol === 'Administrador') {
                $nombreDepto = 'Sin Asignar';
                if ($request->departamento_id) {
                    $depto = Departamento::find($request->departamento_id);
                    $nombreDepto = $depto ? $depto->NombreDepartamento : 'Sin Asignar';
                }

                Funcionario::create([
                    'CodFuncionario' => 'FUN-' . strtoupper(uniqid()),
                    'CargoFuncionario' => strtoupper($request->cargo),
                    'AdscripciónFuncionario' => $nombreDepto,
                    'Departamento_FK' => $request->departamento_id,
                    'CedulaPersona_FK' => $persona->CedulaPersona
                ]);
            }

            DB::commit();
            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error crítico: ' . $e->getMessage());
        }
    }

    // 4. FORMULARIO DE EDICIÓN (NUEVO)
    public function edit($id)
    {
        $usuario = Usuario::with(['persona', 'funcionarioData'])->findOrFail($id);
        $departamentos = Departamento::all();
        
        return view('admin.usuarios.edit', compact('usuario', 'departamentos'));
    }

    // 5. ACTUALIZAR USUARIO (NUEVO - AQUÍ ESTÁ LA LÓGICA BLINDADA)
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $persona = $usuario->persona;

        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            
            'nombre_usuario' => ['required', Rule::unique('usuarios', 'NombreUsuario')->ignore($usuario->CodUsuario, 'CodUsuario')],
            'password' => 'nullable|string|min:6|confirmed',
            'rol' => 'required|in:Administrador,UsuarioPersonal,Externo',
            'estado' => 'required|in:Activo,Inactivo,Cerrado',

            'departamento_id' => 'nullable|required_if:rol,UsuarioPersonal,Administrador',
            'cargo' => 'nullable|required_if:rol,UsuarioPersonal,Administrador',
        ]);

        DB::beginTransaction();

        try {
            // A. Actualizar Persona
            $persona->update([
                'NombresPersona' => strtoupper($request->nombres),
                'ApellidosPersona' => strtoupper($request->apellidos),
                'TelefonoPersona' => $request->telefono ?? $persona->TelefonoPersona,
                'CorreoElectronicoPersona' => $request->email ?? $persona->CorreoElectronicoPersona,
            ]);

            // B. Actualizar Usuario
            $datosUsuario = [
                'NombreUsuario' => $request->nombre_usuario,
                'RolUsuario' => $request->rol,
                'EstadoUsuario' => $request->estado,
            ];

            if ($request->filled('password')) {
                $datosUsuario['ContraseniaUsuario'] = Hash::make($request->password);
            }

            $usuario->update($datosUsuario);

            // C. Lógica de Funcionario (Protegida)
            if (in_array($request->rol, ['UsuarioPersonal', 'Administrador'])) {
                
                $nombreDepto = 'Sin Asignar';
                if ($request->departamento_id) {
                    $depto = Departamento::find($request->departamento_id);
                    $nombreDepto = $depto ? $depto->NombreDepartamento : 'Sin Asignar';
                }

                if ($usuario->funcionarioData) {
                    // Si ya existe, actualizamos cargo y depto
                    $usuario->funcionarioData->update([
                        'CargoFuncionario' => strtoupper($request->cargo),
                        'Departamento_FK' => $request->departamento_id,
                        'AdscripciónFuncionario' => $nombreDepto
                    ]);
                } else {
                    // Si era externo y ahora es funcionario, creamos la ficha
                    Funcionario::create([
                        'CodFuncionario' => 'FUN-' . strtoupper(uniqid()),
                        'CargoFuncionario' => strtoupper($request->cargo),
                        'AdscripciónFuncionario' => $nombreDepto,
                        'Departamento_FK' => $request->departamento_id,
                        'CedulaPersona_FK' => $persona->CedulaPersona
                    ]);
                }
            }
            // NOTA: Eliminamos el 'else { delete() }'. 
            // Si pasa a Externo, la ficha de funcionario SE QUEDA en la BD para no romper el historial.

            DB::commit();
            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
}