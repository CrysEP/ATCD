@extends('layouts.app')

@section('title', 'Registrar Nuevo Usuario')

@section('content')
<div class="container py-4">
    
    {{-- BLOQUE DE ERRORES: Esto te dirá por qué falla --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-person-plus-fill me-2"></i> Registrar Nuevo Usuario del Sistema
                </div>
                <div class="card-body p-4">
                    
                    <form action="{{ route('admin.usuarios.store') }}" method="POST">
                        @csrf

                        <h5 class="text-secondary mb-3 border-bottom pb-2">1. Datos Personales</h5>
                   <div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">Cédula</label>
        <div class="input-group">
            <select name="tipo_cedula" id="tipo_cedula" class="form-select" style="max-width: 70px;">
                <option value="V-">V-</option>
                <option value="E-">E-</option>
            </select>
            <input type="text" name="cedula" id="cedula_input" class="form-control" placeholder="12345678" required>
            <button class="btn btn-outline-primary" type="button" id="btn_buscar_cedula">
                <i class="bi bi-search"></i>
            </button>
        </div>
        <small id="mensaje_cedula" class="form-text text-muted"></small>
    </div>
    
    <div class="col-md-4">
        <label class="form-label">Nombres</label>
        <input type="text" name="nombres" id="nombres" class="form-control" required oninput="this.value = this.value.toUpperCase()">
    </div>
    
    <div class="col-md-4">
        <label class="form-label">Apellidos</label>
        <input type="text" name="apellidos" id="apellidos" class="form-control" required oninput="this.value = this.value.toUpperCase()">
    </div>
</div>

<script>
document.getElementById('btn_buscar_cedula').addEventListener('click', function() {
    let tipo = document.getElementById('tipo_cedula').value;
    let numero = document.getElementById('cedula_input').value;
    let cedulaCompleta = tipo + numero;
    let mensaje = document.getElementById('mensaje_cedula');
    let campoNombres = document.getElementById('nombres');
    let campoApellidos = document.getElementById('apellidos');

    if(numero.length < 5) {
        mensaje.className = 'text-danger';
        mensaje.innerText = 'Escriba una cédula válida.';
        return;
    }

    mensaje.className = 'text-info';
    mensaje.innerText = 'Buscando...';

    // Llamada AJAX a la ruta que creamos
    fetch(`/admin/personas/buscar/${cedulaCompleta}`)
        .then(response => response.json())
        .then(data => {
            if (data.encontrado) {
                if (data.tiene_usuario) {
                    mensaje.className = 'text-danger fw-bold';
                    mensaje.innerText = '¡Esta persona ya tiene un usuario!';
                    campoNombres.value = data.nombres;
                    campoApellidos.value = data.apellidos;
                } else {
                    mensaje.className = 'text-success fw-bold';
                    mensaje.innerText = 'Persona encontrada en base de datos.';
                    campoNombres.value = data.nombres;
                    campoApellidos.value = data.apellidos;
                    // Opcional: Bloquear campos para no editar datos viejos
                    // campoNombres.readOnly = true;
                    // campoApellidos.readOnly = true;
                }
            } else {
                mensaje.className = 'text-muted';
                mensaje.innerText = 'Persona nueva (no registrada previamente).';
                campoNombres.value = '';
                campoApellidos.value = '';
                campoNombres.readOnly = false;
                campoApellidos.readOnly = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mensaje.innerText = 'Error al buscar.';
        });
});
</script>

                        <h5 class="text-secondary mb-3 border-bottom pb-2">2. Datos de Cuenta</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Nombre de Usuario (Login)</label>
                                <input type="text" name="nombre_usuario" class="form-control bg-light" required placeholder="Ej: j.peralta" value="{{ old('nombre_usuario') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            
                            <div class="col-md-12 mt-3">
                                <label class="form-label fw-bold">Tipo de Perfil / Rol</label>
                                <select name="rol" id="rol_selector" class="form-select form-select-lg border-primary" required>
                                    <option value="" selected disabled>Seleccione el rol...</option>
                                    {{-- VALORES CORREGIDOS PARA COINCIDIR CON LA BD --}}
                                    <option value="UsuarioPersonal">Funcionario (Personal de CORPOINTA)</option>
                                    <option value="Externo">Externo (Ciudadano / Visitante)</option>
                                    <option value="Administrador">Administrador del Sistema</option>
                                </select>
                                <div class="form-text">
                                    Si selecciona "Funcionario", deberá indicar su cargo y departamento.
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN OCULTA: DATOS DEL FUNCIONARIO --}}
                        <div class="card mb-4 border-primary bg-light" id="panel_funcionario" style="display: none;">
                            <div class="card-body">
                                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-building me-2"></i>Datos Institucionales</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Departamento / Ubicación</label>
                                        <select name="departamento_id" id="departamento_id" class="form-select">
                                            <option value="">Seleccione...</option>
                                            @foreach($departamentos as $dep)
                                                <option value="{{ $dep->CodDepartamento }}">{{ $dep->NombreDepartamento }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cargo</label>
                                        <input type="text" name="cargo" id="cargo" class="form-control" placeholder="Ej: Analista, Gerente..." value="{{ old('cargo') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-save me-1"></i> Guardar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rolSelector = document.getElementById('rol_selector');
        const panelFuncionario = document.getElementById('panel_funcionario');
        const inputDepto = document.getElementById('departamento_id');
        const inputCargo = document.getElementById('cargo');

        rolSelector.addEventListener('change', function() {
            // CORREGIDO: Ahora comparamos con 'UsuarioPersonal'
            if (this.value === 'UsuarioPersonal') {
                panelFuncionario.style.display = 'block';
                inputDepto.required = true;
                inputCargo.required = true;
                panelFuncionario.classList.add('fade-in');
            } else {
                panelFuncionario.style.display = 'none';
                inputDepto.required = false;
                inputCargo.required = false;
                inputDepto.value = "";
                inputCargo.value = "";
            }
        });
    });
</script>

<style>
    .fade-in { animation: fadeIn 0.5s; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
@endsection