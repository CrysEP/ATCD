@extends('layouts.app')

@section('title', 'Estadísticas del Sistema')

@section('content')

<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
        min-height: 600px;
    }
    .fc-event {
        cursor: pointer;
    }
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
                <button type="submit" class="btn btn-sm btn-primary text-white" title="Actualizar Gráficos">Actualizar<i class="bi bi-search ms-1"></i></button>
            </form>

            <form action="{{ route('estadisticas.excel') }}" method="GET">
                <input type="hidden" name="mes" value="{{ $mes }}">
                <input type="hidden" name="anio" value="{{ $anio }}">
                <button type="submit" class="btn btn-sm btn-success text-white">
                    <i class="bi bi-file-earmark-excel-fill"></i> Exportar
                </button>
            </form>
        </div>
    </div>

    {{-- TARJETAS DE RESUMEN --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card text-muted shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 opacity-75">Recibidas Hoy</h6>
                        <h2 class="mb-0 fw-bold">{{ $solicitudesHoy }}</h2>
                        <small>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</small>
                    </div>
                    <i class="bi bi-calendar-check-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-muted shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 opacity-75">Total Histórico</h6>
                        <h2 class="mb-0 fw-bold">{{ $totalSolicitudes }}</h2>
                        <small>Solicitudes registradas al mes</small>
                    </div>
                    <i class="bi bi-folder2-open fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-muted shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1 opacity-75">Más Solicitado en {{ ucfirst($nombreMes) }}</h6>
                        <h2 class="mb-0 fw-bold">{{ $topEnteNombre }}</h2>
                        <small>{{ $topEnteTotal }} solicitudes recibidas</small>
                    </div>
                    <i class="bi bi-trophy-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #dc3545;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1 fw-bold">Denuncias</h6>
                            <h2 class="mb-0 fw-bold text-danger">{{ $denunciasMes }}</h2>
                            <small class="text-muted">Registradas este mes</small>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #ffc107;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1 fw-bold">Quejas y Sugerencias</h6>
                            <h2 class="mb-0 fw-bold text-warning" style="color: #ffc107 !important;">{{ $quejasMes }}</h2>
                            <small class="text-muted">Registradas este mes</small>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                            <i class="bi bi-megaphone-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ESTADÍSTICAS DEL MES --}}
    <h5 class="mb-3 border-bottom pb-2">Estadísticas simples del Mes ({{ ucfirst($nombreMes) }})</h5>
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Solicitudes por Municipio</div>
                <div class="card-body">
                    <canvas id="chartMunMes"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Solicitudes por Tipo de Ente</div>
                <div class="card-body">
                    <canvas id="chartEnteMes"></canvas>
                </div>
            </div>
        </div>
    </div>

        {{-- === SECCIÓN NUEVA: EVOLUCIÓN MENSUAL DETALLADA === --}}
    <h5 class="mb-3 border-bottom pb-2 mt-5">Evolución Mensual Detallada ({{ $anio }})</h5>
    <div class="row g-4 mb-5">
        
        {{-- Gráfico Mensual por Municipio --}}
        <div class="col-12">
            <div class="card card-gradient-body shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-calendar-range me-2"></i>Solicitudes por Municipio al Mes
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="chartMensualMun"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráfico Mensual por Ente --}}
        <div class="col-12">
            <div class="card card-gradient-body shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-building me-2"></i>Solicitudes por Ente al Mes
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="chartMensualEnte"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Estatus Global por mes</div>
                <div class="card-body">
                    <canvas id="chartEstatus"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Nivel de Urgencia</div>
                <div class="card-body">
                    <canvas id="chartUrgencia" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Perfil del Solicitante</div>
                <div class="card-body">
                    <canvas id="chartTipoSolicitante" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

        {{-- ANÁLISIS TRIMESTRAL --}}
    <h5 class="mb-3 border-bottom pb-2 mt-5">Análisis Trimestral y por Ente ({{ $anio }})</h5>
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold text-success">
                    <i class="bi bi-pie-chart-fill me-2"></i>Totales Globales por Trimestre
                </div>
                <div class="card-body">
                    <canvas id="chartGlobalTrimestres"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-bar-chart-steps me-2"></i>Desglose Trimestral por Municipio
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="chartTrimestreMun"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRÁFICO DE LÍNEA ANUAL --}}
    <h5 class="mb-3 border-bottom pb-2">Métricas Globales Anuales</h5>
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card card-gradient-body shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Comportamiento Anual ({{ $anio }})</div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="chartMeses"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DISTRIBUCIÓN GEOGRÁFICA ANUAL --}}
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


        <div class="col-12">
            <div class="card card-gradient-body shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-diagram-3-fill me-2"></i>Distribución de Tipos de Ente por Municipio (Total Anual)
                </div>
                <div class="card-body">
                    <div style="height: 500px;">
                        <canvas id="chartEnteMun"></canvas>
                    </div>

<div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-muted mb-2"><i class="bi bi-list-check me-2"></i>Totales Anuales por Municipio:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($totalesAnualesPorMunicipio as $municipio => $total)
                                <span class="badge bg-light text-dark border border-secondary shadow-sm p-2">
                                    {{ $municipio }}: <strong class="text-primary fs-6">{{ $total }}</strong>
                                </span>
                            @empty
                                <span class="text-muted small">No hay registros este año.</span>
                            @endforelse
                        </div>
                    </div>
                    

                </div>
            </div>
        </div>
    </div>


<div class="col-12">
            <div class="card card-gradient-body shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold text-primary">
                    <i class="bi bi-bank me-2"></i>Total de Solicitudes por Ente (Anual)
                </div>
                <div class="card-body">
                    {{-- Canvas del Gráfico --}}
                    <canvas id="chartAnualEntes" style="max-height: 400px;"></canvas>
                    
                    {{-- LEYENDA DE TOTALES --}}
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold text-muted mb-2"><i class="bi bi-list-check me-2"></i>Detalle de Totales por Ente:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            {{-- Iteramos usando los mismos datos del gráfico --}}
                            @foreach($labelsAnualEntes as $index => $nombreEnte)
                                <span class="badge bg-light text-dark border border-secondary shadow-sm p-2">
                                    {{ $nombreEnte }}: 
                                    <strong class="text-primary fs-6">
                                        {{ $dataAnualEntes[$index] ?? 0 }}
                                    </strong>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


    

    {{-- CALENDARIO --}}
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

{{-- Procesamiento de Datos PHP --}}
@php
    // Datos Trimestrales
    $dataTrimestreMun = $dataTrimestreMun ?? [];
    $labelsMun = array_keys($dataTrimestreMun);
    $d1=[]; $d2=[]; $d3=[]; $d4=[];
    foreach($dataTrimestreMun as $m => $v) {
        $d1[] = $v[1] ?? 0; $d2[] = $v[2] ?? 0; $d3[] = $v[3] ?? 0; $d4[] = $v[4] ?? 0;
    }

    // Datos Ente x Municipio (Total Anual)
    $dataTrimestreMunEnte = $dataTrimestreMunEnte ?? [];
    $labelsMunEnte = array_keys($dataTrimestreMunEnte);
    $allEntes = [];
    foreach($dataTrimestreMunEnte as $m => $entes) {
        foreach($entes as $eName => $v) { $allEntes[$eName] = 1; }
    }
    $allEntes = array_keys($allEntes);
    sort($allEntes);

    $datasetsEnte = [];
    foreach($allEntes as $eName) {
        $data = [];
        foreach($labelsMunEnte as $mName) {
            $data[] = $dataTrimestreMunEnte[$mName][$eName]['total'] ?? 0;
        }
        $datasetsEnte[] = [ 'label' => $eName, 'data' => $data, 'stack' => 'entes' ];
    }

    // --- NUEVO: PREPARAR DATASETS MENSUALES ---
    // 1. Municipios Mensual
    $dataMensualMun = $dataMensualMun ?? [];
    $datasetsMensualMun = [];
    foreach($dataMensualMun as $mun => $valoresMes) {
        $data = array_values($valoresMes); // [Ene, Feb... Dic]
        $datasetsMensualMun[] = [
            'label' => $mun,
            'data' => $data,
            'stack' => 'mun' // Apilados
        ];
    }

    // 2. Entes Mensual
    $dataMensualEnte = $dataMensualEnte ?? [];
    $datasetsMensualEnte = [];
    foreach($dataMensualEnte as $ente => $valoresMes) {
        $data = array_values($valoresMes);
        $datasetsMensualEnte[] = [
            'label' => $ente,
            'data' => $data,
            'stack' => 'ente' // Apilados
        ];
    }
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
    const colores = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6610f2', '#fd7e14', '#20c997', '#0dcaf0', '#6c757d'];
    const mesesLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    // GRÁFICOS ORIGINALES
    new Chart(document.getElementById('chartMunMes'), {
        type: 'bar',
        data: { labels: {!! json_encode($labelsMunMes) !!}, datasets: [{ label: 'Solicitudes', data: {!! json_encode($dataMunMes) !!}, backgroundColor: '#0d6efd', borderRadius: 4 }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
    });

    new Chart(document.getElementById('chartEnteMes'), {
        type: 'bar',
        data: { labels: {!! json_encode($labelsEnteMes) !!}, datasets: [{ label: 'Cantidad', data: {!! json_encode($dataEnteMes) !!}, backgroundColor: colores, borderRadius: 4 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
    });

    new Chart(document.getElementById('chartEstatus'), {
        type: 'doughnut',
        data: { labels: {!! json_encode($labelsEstatus) !!}, datasets: [{ data: {!! json_encode($dataEstatus) !!}, backgroundColor: ['#0dcaf0', '#ffc107', '#198754', '#dc3545', '#0d6efd', '#6c757d'] }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('chartUrgencia'), {
        type: 'pie',
        data: { labels: {!! json_encode($labelsUrgencia) !!}, datasets: [{ data: {!! json_encode($dataUrgencia) !!}, backgroundColor: ['#dc3545', '#ffc107', '#0d6efd'] }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('chartTipoSolicitante'), {
        type: 'bar',
        data: { labels: {!! json_encode($labelsTipoSolicitante) !!}, datasets: [{ label: 'Cantidad', data: {!! json_encode($dataTipoSolicitante) !!}, backgroundColor: '#0f79ea' }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
    });

    new Chart(document.getElementById('chartMeses'), {
        type: 'line',
        data: { labels: mesesLabels, datasets: [{ label: 'Solicitudes', data: {!! json_encode($dataMeses) !!}, borderColor: '#198754', backgroundColor: 'rgba(25, 135, 84, 0.1)', fill: true, tension: 0.3 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
    });

    new Chart(document.getElementById('chartMunAnio').getContext('2d'), {
        type: 'bar',
        data: { labels: {!! json_encode($labelsMunAnio) !!}, datasets: [{ label: 'Total {{ $anio }}', data: {!! json_encode($dataMunAnio) !!}, backgroundColor: 'rgba(253, 126, 20, 0.7)', borderColor: '#fd7e14', borderWidth: 1, borderRadius: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
    });

    // 1. GLOBAL TRIMESTRAL
    new Chart(document.getElementById('chartGlobalTrimestres'), {
        type: 'bar',
        data: {
            labels: ['Tri 1 (Ene-Mar)', 'Tri 2 (Abr-Jun)', 'Tri 3 (Jul-Sep)', 'Tri 4 (Oct-Dic)'],
            datasets: [{ label: 'Total Solicitudes', data: {!! json_encode($dataGlobalTri) !!}, backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545'], borderRadius: 4 }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
    });

    // 2. DESGLOSE TRIMESTRAL MUNICIPIO
    new Chart(document.getElementById('chartTrimestreMun'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelsMun) !!},
            datasets: [
                { label: 'T1', data: {!! json_encode($d1) !!}, backgroundColor: '#0d6efd', stack: 'q' },
                { label: 'T2', data: {!! json_encode($d2) !!}, backgroundColor: '#198754', stack: 'q' },
                { label: 'T3', data: {!! json_encode($d3) !!}, backgroundColor: '#ffc107', stack: 'q' },
                { label: 'T4', data: {!! json_encode($d4) !!}, backgroundColor: '#dc3545', stack: 'q' }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } } }
    });

    // 3. ENTES POR MUNICIPIO (TOTAL)
    const rawDatasetsEnte = {!! json_encode($datasetsEnte) !!};
    // Colores aleatorios cíclicos para Entes
    const extendedColors = ['#0d6efd', '#272b8c', '#42c188', '#7de024', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c9c9', '#f03e0d', '#6c757d', '#343a40', '#c98853', '#537ac9'];
    rawDatasetsEnte.forEach((ds, index) => { ds.backgroundColor = extendedColors[index % extendedColors.length]; });

    new Chart(document.getElementById('chartEnteMun'), {
        type: 'bar',
        data: { labels: {!! json_encode($labelsMunEnte) !!}, datasets: rawDatasetsEnte },
        options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }, plugins: { legend: { position: 'bottom' } } }
    });

    // === 4. MENSUAL POR MUNICIPIO ===
    const dsMensualMun = {!! json_encode($datasetsMensualMun) !!};
    dsMensualMun.forEach((ds, index) => { ds.backgroundColor = extendedColors[index % extendedColors.length]; });

    new Chart(document.getElementById('chartMensualMun'), {
        type: 'bar',
        data: { labels: mesesLabels, datasets: dsMensualMun },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
            plugins: { tooltip: { callbacks: { footer: (items) => 'Total Mes: ' + items.reduce((a, b) => a + b.parsed.y, 0) } } }
        }
    });

    // === 5. MENSUAL POR ENTE ===
    const dsMensualEnte = {!! json_encode($datasetsMensualEnte) !!};
    dsMensualEnte.forEach((ds, index) => { ds.backgroundColor = extendedColors[index % extendedColors.length]; });

    new Chart(document.getElementById('chartMensualEnte'), {
        type: 'bar',
        data: { labels: mesesLabels, datasets: dsMensualEnte },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
            plugins: { tooltip: { callbacks: { footer: (items) => 'Total Mes: ' + items.reduce((a, b) => a + b.parsed.y, 0) } } }
        }
    });


    // 4. NUEVO: ANUAL ENTES
new Chart(document.getElementById('chartAnualEntes'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelsAnualEntes) !!},
            datasets: [{
                label: 'Total Solicitudes',
                data: {!! json_encode($dataAnualEntes) !!},
                backgroundColor: extendedColors,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false }
            },
            scales: { 
                y: {
                    beginAtZero: true, 
                    ticks: { stepSize: 1, precision: 0 } 
                } 
            }
        }
    });


    // CALENDARIO
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth', locale: 'es',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
            buttonText: { today: 'Hoy', month: 'Mes', list: 'Lista' },
            events: "{{ route('estadisticas.dataCalendario') }}",
            eventClick: function(info) { }
        });
        calendar.render();
    });
</script>

@endsection