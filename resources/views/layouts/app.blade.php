<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-m">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SGS Corpointa')</title>

    <link rel="icon" href="{{ asset('images/logo_corpointa.png') }}" type="image/x-icon">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">

    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('images/logo_corpointa.png') }}" alt="Logo Corpointa" style="height: 30px; margin-right: 10px;">
                    ◊</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">Solicitudes activas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('solicitudes.create') }}">Nueva Solicitud</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('solicitudes.history') }}">Solicitudes procesadas</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('solicitudes.anuladas') }}">
                                    <i class="bi bi-trash3"></i> Anuladas
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Iniciar Sesión</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->NombreUsuario }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        Cerrar Sesión
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>