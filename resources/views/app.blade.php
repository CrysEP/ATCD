<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-g">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SGS Corpointa')</title>
    <!-- Carga de Tailwind CSS (CDN para demo) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos personalizados para el color corporativo (ejemplo) */
        :root {
            --color-primario: #0D47A1; /* Azul Corpointa (ejemplo) */
            --color-secundario: #1976D2;
        }
        .bg-primario { background-color: var(--color-primario); }
        .text-primario { color: var(--color-primario); }
        .border-primario { border-color: var(--color-primario); }
        .btn-primario {
            background-color: var(--color-primario);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-primario:hover {
            background-color: var(--color-secundario);
        }
        /* Estilos para la tarjeta de solicitud (basado en tu CSS) */
        .solicitud-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background-color: #fff;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .solicitud-card.urgente-Alto {
            border-left: 5px solid #EF4444; /* Rojo para Urgencia Alta */
        }
        .solicitud-card.urgente-Medio {
            border-left: 5px solid #F59E0B; /* Ámbar para Urgencia Media */
        }
        .solicitud-card.urgente-Bajo {
            border-left: 5px solid #10B981; /* Verde para Urgencia Baja */
        }
        .solicitud-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .solicitud-card .card-header .codigo { font-weight: 700; color: var(--color-primario); }
        .solicitud-card .card-header .tipo { font-size: 0.875rem; font-weight: 500; }
        .solicitud-card .card-header .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            color: white;
        }
        .solicitud-card .badge.urgencia-Alto { background-color: #EF4444; }
        .solicitud-card .badge.urgencia-Medio { background-color: #F59E0B; }
        .solicitud-card .badge.urgencia-Bajo { background-color: #10B981; }
        
        .solicitud-card .card-body { padding: 1rem; }
        .solicitud-card .card-body h6 { font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; }
        .solicitud-card .card-body .descripcion-corta { font-size: 0.875rem; color: #4b5563; margin-bottom: 1rem; }
        .solicitud-card .info-adicional {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .solicitud-card .info-adicional small { display: block; }

        .solicitud-card .card-actions {
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 0.5rem;
        }
        .solicitud-card .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }
        .solicitud-card .btn-primary { background-color: #3B82F6; color: white; }
        .solicitud-card .btn-primary:hover { background-color: #2563EB; }
        .solicitud-card .btn-success { background-color: #10B981; color: white; }
        .solicitud-card .btn-success:hover { background-color: #059669; }
        .solicitud-card .btn-warning { background-color: #F59E0B; color: white; }
        .solicitud-card .btn-warning:hover { background-color: #D97706; }

    </style>
</head>
<body class="bg-gray-100 font-sans">
    
    <nav class="bg-primario text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('dashboard') }}" class="font-bold text-xl">
                    SGS Corpointa
                </a>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="hover:text-gray-200">Dashboard</a>
                    <a href="{{ route('solicitudes.create') }}" class="hover:text-gray-200">Nueva Solicitud</a>
                    <a href="{{ route('solicitudes.history') }}" class="hover:text-gray-200">Historial</a>
                    
                    @auth
                    <span class="text-gray-300">|</span>
                    <span class="font-medium">{{ auth()->user()->NombreUsuario }}</span>
                    <!-- Aquí iría el form de logout -->
                    <!--
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-gray-200">Salir</button>
                    </form>
                    -->
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        
        <!-- Alertas de Éxito o Error -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white text-center text-sm text-gray-500 py-4 mt-8 border-t">
        Sistema de Gestión de Solicitudes - Corpointa &copy; {{ date('Y') }}
    </footer>

    <!-- Scripts (si son necesarios) -->
    @stack('scripts')
</body>
</html>