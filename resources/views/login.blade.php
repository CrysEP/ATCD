@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0">Sistema de Gestión de Solicitudes</h4>
            </div>
            <div class="card-body card-gradient-body p-4">

                <div class="text-center mb-3">
                    <img src="{{ asset('images/logo_corpointa.png') }}" alt="Logo Corpointa" style="width: 100px;">
                </div>
                
                <h5 class="card-title text-center mb-4">Acceso de Funcionarios</h5>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf <div class="mb-3">
                        <label for="NombreUsuario" class="form-label">Nombre de Usuario:</label>
                        <input type="text" 
                               class="form-control" 
                               id="NombreUsuario" 
                               name="NombreUsuario" 
                               value="{{ old('NombreUsuario') }}" 
                               required 
                               autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="ContraseniaUsuario" class="form-label">Contraseña:</label>
                        <input type="password" 
                               class="form-control" 
                               id="ContraseniaUsuario"
                               name="ContraseniaUsuario" 
                               required>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Ingresar
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection