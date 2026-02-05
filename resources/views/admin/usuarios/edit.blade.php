@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h4>
                        <span class="badge bg-secondary">{{ $usuario->CedulaPersonaUsuario_FK }}</span>
                    </div>
                </div>
                <div class="card-body p-4">

                    {{-- Alerta de Errores --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.usuarios.update', $usuario->CodUsuario) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- SECCIÓN 1: DATOS PERSONALES (Para solicitudes futuras) --}}
                        <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">1. Datos Personales</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="nombres" class="form-control" value="{{ old('nombres', $usuario->persona->NombresPersona) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos', $usuario->persona->ApellidosPersona) }}"  required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono <small class="text-muted">(Opcional)</small></label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $usuario->persona->TelefonoPersona) }}" placeholder="0414-1234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico <small class="text-muted">(Opcional)</small></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $usuario->persona->CorreoElectronicoPersona) }}">
                            </div>
                        </div>

                        {{-- SECCIÓN 2: DATOS DE CUENTA --}}
                        <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">2. Configuración de Cuenta</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Usuario (Login)</label>
                                <input type="text" name="nombre_usuario" class="form-control bg-light" value="{{ old('nombre_usuario', $usuario->NombreUsuario) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rol / Perfil</label>
                                <select name="rol" id="rol_selector" class="form-select border-primary">
                                    <option value="UsuarioPersonal" {{ $usuario->RolUsuario == 'UsuarioPersonal' ? 'selected' : '' }}>Funcionario</option>
                                    <option value="Externo" {{ $usuario->RolUsuario == 'Externo' ? 'selected' : '' }}>Externo</option>
                                    <option value="Administrador" {{ $usuario->RolUsuario == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="Activo" {{ $usuario->EstadoUsuario == 'Activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="Inactivo" {{ $usuario->EstadoUsuario == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="Cerrado" {{ $usuario->EstadoUsuario == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                                </select>
                            </div>
                            
                            {{-- Cambio de Contraseña (Opcional) --}}
                            <div class="col-md-12 mt-2">
                                <div class="p-3 bg-light rounded border">
                                    <label class="form-label fw-bold small text-secondary">Cambiar Contraseña (Dejar en blanco para mantener la actual)</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="password" name="password" class="form-control form-control-sm" placeholder="Nueva contraseña">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirmar nueva contraseña">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN 3: DATOS DEL FUNCIONARIO --}}
                        <div id="panel_funcionario" style="display: {{ in_array($usuario->RolUsuario, ['UsuarioPersonal', 'Administrador']) ? 'block' : 'none' }};">
                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">3. Datos Institucionales</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Departamento</label>
                                    <select name="departamento_id" id="departamento_id" class="form-select">
                                        <option value="">Seleccione...</option>
                                        @foreach($departamentos as $dep)
                                            <option value="{{ $dep->CodDepartamento }}" 
                                                {{ (old('departamento_id') == $dep->CodDepartamento) || ($usuario->funcionarioData && $usuario->funcionarioData->Departamento_FK == $dep->CodDepartamento) ? 'selected' : '' }}>
                                                {{ $dep->NombreDepartamento }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cargo</label>
                                    <input type="text" name="cargo" id="cargo" class="form-control" 
                                           value="{{ old('cargo', $usuario->funcionarioData->CargoFuncionario ?? '') }}" >
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-3">
                            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Volver al Listado</a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-save me-2"></i>Actualizar Usuario
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

        function toggleFuncionario() {
            // Mostrar si es Funcionario O Administrador
            if (['UsuarioPersonal', 'Administrador'].includes(rolSelector.value)) {
                panelFuncionario.style.display = 'block';
                inputDepto.required = true;
                inputCargo.required = true;
            } else {
                panelFuncionario.style.display = 'none';
                inputDepto.required = false;
                inputCargo.required = false;
            }
        }

        rolSelector.addEventListener('change', toggleFuncionario);
        // Ejecutar al cargar por si hay errores de validación y debe mantenerse abierto
        toggleFuncionario();
    });
</script>
@endsection