@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-people-fill me-2"></i>Gestión de Usuarios</h2>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4">Usuario / Rol</th>
                            <th>Datos Personales</th>
                            <th>Ubicación / Cargo</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $user->NombreUsuario }}</div>
                                    <span class="badge {{ $user->RolUsuario == 'Administrador' ? 'bg-danger' : ($user->RolUsuario == 'UsuarioPersonal' ? 'bg-primary' : 'bg-info') }}">
                                        {{ $user->RolUsuario }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-bold">{{ $user->persona->NombreCompleto ?? 'N/A' }}</div>
                                    <div class="text-muted small"><i class="bi bi-card-heading me-1"></i>{{ $user->CedulaPersonaUsuario_FK }}</div>
                                </td>
                                <td>
                                    @if($user->funcionarioData)
                                        <div class="small text-primary fw-bold">{{ $user->funcionarioData->departamento->NombreDepartamento ?? 'Sin Depto.' }}</div>
                                        <div class="small text-muted">{{ $user->funcionarioData->CargoFuncionario }}</div>
                                    @else
                                        <span class="text-muted small fst-italic">No aplica (Externo)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $user->EstadoUsuario == 'Activo' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary' }}">
                                        {{ $user->EstadoUsuario }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.usuarios.edit', $user->CodUsuario) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $usuarios->links() }}
        </div>
    </div>
</div>
@endsection