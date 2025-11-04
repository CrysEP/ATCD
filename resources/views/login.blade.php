@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-xl">
        <h2 class="text-3xl font-bold text-center text-primario mb-6">SGS Corpointa</h2>
        <p class="text-center text-gray-600 mb-8">Iniciar Sesión</p>

        <!-- 
            Este es un placeholder. Deberás crear un Auth\LoginController
            que maneje la lógica de autenticación usando:
            - Campo de usuario: 'NombreUsuario'
            - Campo de contraseña: 'ContraseniaUsuario'
        -->
        <form action="{{-- route('login') --}}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="NombreUsuario" class="block text-sm font-medium text-gray-700">Nombre de Usuario</label>
                <input type="text" name="NombreUsuario" id="NombreUsuario" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primario focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>

            <div>
                <label for="ContraseniaUsuario" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="ContraseniaUsuario" id="ContraseniaUsuario" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primario focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            
            <!--
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 text-primario border-gray-300 rounded focus:ring-blue-500">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">Recordarme</label>
                </div>
            </div>
            -->

            <div>
                <button type="submit" class="w-full btn-primario justify-center py-3 text-lg">
                    Ingresar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
