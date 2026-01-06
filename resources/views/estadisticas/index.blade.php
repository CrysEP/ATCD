@extends('layouts.app')

@section('title', 'Estadísticas del Sistema')

@section('content')

<style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
            min-height: 600px; /* Altura cómoda */
        }
        .fc-event {
            cursor: pointer; /* Manito al pasar el mouse */
        }

        /* Para la mayúscula que quería el inge 
        .fc-toolbar-title {
        text-transform: capitalize;
    } */

    </style>
    
<div class="container pb-5">
    
    {{-- ENCABEZADO CON FILTROS --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-line-fill"></i> Panel de Estadísticas</h2>
            <p class="text-muted mb-0">Mostrando datos de: <strong class="text-uppercase">{{ $nombreMes }} {{ $anio }}</strong></p>
        </div>

        <div class="d-flex gap-2 align-items-end mt-3 mt-md-0">
            <form action="{{ route('estadisticas.index') }}" method="GET" class="d-flex gap-2">
                <div>
                    <select name="mes" class="form-select form-select-sm">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" @selected($m == $mes)>
                                {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <select name="anio" class="form-select form-select-sm">
                        @for($a=date('Y'); $a>=2024; $a--)
                            <option value="{{ $a }}" @selected($a == $anio)>{{ $a }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary text-white" title="Actualizar Gráficos">Actualizar gráficos<i class="bi bi-search"></i></button>
            </form>

            {{-- BOTÓN EXPORTAR EXCEL --}}
            <form action="{{ route('estadisticas.excel') }}" method="GET">
                <input type="hidden" name="mes" value="{{ $mes }}">
                <input type="hidden" name="anio" value="{{ $anio }}">
                <button type="submit" class="btn btn-sm btn-success text-white">
                    <i class="bi bi-file-earmark-excel-fill"></i> Exportar Data
                </button>
            </form>
        </div>
    </div>


    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-line-fill"></i> Panel de Estadísticas</h2>
            <p class="text-muted mb-0">Reporte del mes de <strong class="text-uppercase">{{ $nombreMes }}</strong></p>
        </div>
    </div>

    {{-- TARJETAS DE RESUMEN --}}
    <div class="row g-4 mb-4">


<div class="col-md-4">
            <div class="card text-white shadow-sm border-0 h-100" style="background-color: #6f42c1;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Recibidas Hoy</h6>
                        <h2 class="mb-0 fw-bold">{{ $solicitudesHoy }}</h2>
                        <small>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</small>
                    </div>
                    <i class="bi bi-calendar-check-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Total Histórico</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalSolicitudes }}</h2>
                        <small>Solicitudes registradas al mes</small>
                    </div>
                    <i class="bi bi-folder2-open fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Más Solicitado en {{ ucfirst($nombreMes) }}</h6>
                        <h2 class="mb-0 fw-bold">{{ $topEnteNombre }}</h2>
                        <small>{{ $topEnteTotal }} solicitudes recibidas</small>
                    </div>
                    <i class="bi bi-trophy-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- FILA 1: ESTADÍSTICAS DEL MES ACTUAL --}}
    <h5 class="mb-3 border-bottom pb-2">Estadísticas del Mes ({{ ucfirst($nombreMes) }})</h5>
    <div class="row g-4 mb-5">
        {{-- GRAFICO: MUNICIPIOS DEL MES --}}
        <div class="col-lg-6">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Solicitudes por Municipio</div>
                <div class="card-body">
                    <canvas id="chartMunMes"></canvas>
                </div>
            </div>
        </div>

        {{-- GRAFICO: ENTES DEL MES --}}
        <div class="col-lg-6">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Solicitudes por Tipo de Ente</div>
                <div class="card-body">
                    <canvas id="chartEnteMes"></canvas>
                </div>
            </div>
        </div>
    </div>

      <div class="col-md-4">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Estatus Global por mes</div>
                <div class="card-body">
                    <canvas id="chartEstatus"></canvas>
                </div>
            </div>
        </div>

        <br>

    {{-- FILA 2: ESTADÍSTICAS GLOBALES E HISTÓRICAS --}}
    <h5 class="mb-3 border-bottom pb-2">Métricas Globales e Históricas</h5>
    <div class="row g-4">
        
      

        <div class="col-md-8">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Comportamiento Anual ({{ $anio }})</div>
                <div class="card-body">
                    <canvas id="chartMeses" height="100"></canvas>
                </div>
            </div>
        </div>

    </div>

<br>

{{-- FILA 3: ESTADÍSTICAS GEOGRÁFICAS ANUALES --}}
    <h5 class="mb-3 border-bottom pb-2">Distribución Geográfica Anual ({{ $anio }})</h5>
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-map-fill text-primary me-2"></i>Municipios con más solicitudes en {{ $anio }}
                </div>
                <div class="card-body">

                    <div style="height: 300px;">
                        <canvas id="chartMunAnio"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <h5 class="mb-3 border-bottom pb-2 mt-5">Días con Solicitudes Procesadas</h5>
    <div class="card shadow-sm border-0 mb-5 card-gradient-body">
        <div class="card-body">
        
            <div id='calendar'></div>
        </div>
        <div class="card-footer bg-light">
            <small class="text-muted d-flex gap-3 justify-content-center">
                <span><i class="bi bi-circle-fill text-success"></i> Baja (1-5)</span>
                <span><i class="bi bi-circle-fill text-warning"></i> Media (6-15)</span>
                <span><i class="bi bi-circle-fill text-danger"></i> Alta (16+)</span>
            </small>
        </div>
    </div>



</div>

{{-- IMPORTAR CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  

<script>
    // Colores base
    const colores = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6610f2', '#fd7e14', '#20c997', '#0dcaf0', '#6c757d'];

    // 1. MUNICIPIOS (Barra Horizontal)
    new Chart(document.getElementById('chartMunMes'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelsMunMes) !!},
            datasets: [{
                label: 'Solicitudes',
                data: {!! json_encode($dataMunMes) !!},
                backgroundColor: '#0d6efd',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y', 
            plugins: { legend: { display: false } },
            scales: {
                x: {  
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });

    // 2. ENTES (Barra Vertical)
    new Chart(document.getElementById('chartEnteMes'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelsEnteMes) !!},
            datasets: [{
                label: 'Cantidad',
                data: {!! json_encode($dataEnteMes) !!},
                backgroundColor: colores,
                borderRadius: 4
            }]
        },
        options: {
        
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });

    // 3. ESTATUS GLOBAL (Dona)
    const rawLabelsEstatus = {!! json_encode($labelsEstatus) !!};
    const rawDataEstatus = {!! json_encode($dataEstatus) !!};

    const labelsConConteo = rawLabelsEstatus.map((label, index) => {
        return `${label}: ${rawDataEstatus[index]}`;
    });

    new Chart(document.getElementById('chartEstatus'), {
        type: 'doughnut',
        data: {
            labels: labelsConConteo, 
            datasets: [{
                data: rawDataEstatus,
                backgroundColor: ['#0dcaf0', '#ffc107', '#198754', '#dc3545', '#0d6efd', '#6c757d'],
                hoverOffset: 4
            }]
        },
        options: { 
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,         
                        font: {
                            weight: 'bold'   
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label; 
                        }
                    }
                }
            } 
        }
    });


// 4. HISTÓRICO ANUAL (Línea)
    new Chart(document.getElementById('chartMeses'), {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Solicitudes Recibidas',
                data: {!! json_encode($dataMeses) !!},
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.3 // Suaviza la curva de la línea
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' Solicitudes';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Cantidad' },
                    ticks: {
                        stepSize: 1,   
                        precision: 0
                    }
                }
            }
        }
    });    


// 5. NUEVO: MUNICIPIOS DEL AÑO (Barra Vertical)
    const ctxMunAnio = document.getElementById('chartMunAnio').getContext('2d');
    new Chart(ctxMunAnio, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelsMunAnio) !!},
            datasets: [{
                label: 'Total Solicitudes {{ $anio }}',
                data: {!! json_encode($dataMunAnio) !!},
                backgroundColor: 'rgba(253, 126, 20, 0.7)', 
                borderColor: '#fd7e14',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Importante para que respete el height del div
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' Solicitudes';
                        }
                    }
                }
            },
          scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad de Solicitudes'
                    },
                  
                    ticks: {
                        stepSize: 1,   // Salto de 1 en 1
                        precision: 0   // Sin decimales
                    }
                  
                }
            }
        }
    });


</script>



<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es', // Idioma español
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth' // Vista mes y lista
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    list: 'Lista'
                },
                events: "{{ route('estadisticas.dataCalendario') }}", // Carga los datos JSON
                eventClick: function(info) {
                    // Para que abra en una pestaña nueva, descomentar:
                    // info.jsEvent.preventDefault();
                    // window.open(info.event.url, '_blank');
                }
            });
            calendar.render();
        });
    </script>



@endsection