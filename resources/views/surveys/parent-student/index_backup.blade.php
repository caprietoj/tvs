@extends('adminlte::page')

@section('title', 'Análisis de Encuestas Padre-Estudiante')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-users text-primary"></i>
                <i class="fas fa-graduation-cap text-success"></i>
                Análisis de Encuestas Padre-Estudiante
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Encuestas</a></li>
                <li class="breadcrumb-item active">Padre-Estudiante</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    @if(!isset($dashboardData['has_data']) || !$dashboardData['has_data'])
        <!-- No Data State -->
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            No hay datos disponibles
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">¡Comencemos con el análisis!</h4>
                        <p class="text-muted">Para comenzar a analizar las encuestas padre-estudiante, primero debe cargar los datos de las encuestas.</p>
                        
                        <div class="mt-4">
                            <a href="{{ route('surveys.parent-student.upload') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i>
                                Cargar Primera Encuesta
                            </a>
                        </div>

                        <div class="mt-4">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info-circle"></i> Formato de archivo esperado:</h5>
                                <p class="mb-0">El sistema acepta archivos Excel (.xlsx) con las siguientes columnas:</p>
                                <ul class="text-left mt-2">
                                    <li>Información del estudiante (grado, proveedor, etc.)</li>
                                    <li>Preguntas de Cafetería (satisfacción con el servicio)</li>
                                    <li>Preguntas de Transporte (evaluación del servicio de transporte)</li>
                                    <li>Datos del período académico</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Dashboard with Data -->
        
        <!-- Control Panel -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs"></i>
                            Panel de Control
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('surveys.parent-student.upload') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-upload"></i>
                                    Cargar Nueva Encuesta
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('surveys.parent-student.comparison') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-chart-bar"></i>
                                    Comparar Períodos
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info btn-block" onclick="generateReport()">
                                    <i class="fas fa-file-pdf"></i>
                                    Generar Reporte
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-warning btn-block" onclick="exportData()">
                                    <i class="fas fa-download"></i>
                                    Exportar Datos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Resumen Ejecutivo -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $dashboardData['total_responses'] }}/{{ $dashboardData['expected_responses'] ?? 'N/A' }}</h3>
                        <p>Respuestas Obtenidas/Esperadas</p>
                        <small>Período: {{ $dashboardData['latest_period'] }} 
                            @if(isset($dashboardData['expected_responses']) && $dashboardData['expected_responses'] > 0)
                                ({{ round(($dashboardData['total_responses'] / $dashboardData['expected_responses']) * 100, 1) }}%)
                            @endif
                        </small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dashboardData['cafeteria_users'] }}</h3>
                        <p>Usuarios Cafetería</p>
                        <small>{{ $dashboardData['total_responses'] > 0 ? round(($dashboardData['cafeteria_users'] / $dashboardData['total_responses']) * 100, 1) : 0 }}% del total</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $dashboardData['transport_users'] }}</h3>
                        <p>Usuarios Transporte</p>
                        <small>{{ $dashboardData['total_responses'] > 0 ? round(($dashboardData['transport_users'] / $dashboardData['total_responses']) * 100, 1) : 0 }}% del total</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bus"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ count($dashboardData['grades'] ?? []) }}</h3>
                        <p>Grados Participantes</p>
                        <small>Distribución por nivel académico</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores Detallados -->
        <div class="row">
            <!-- Progreso de Respuestas -->
            <div class="col-lg-6">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-percentage"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Progreso de Respuestas</span>
                        <span class="info-box-number">
                            @if(isset($dashboardData['expected_responses']) && $dashboardData['expected_responses'] > 0)
                                {{ round(($dashboardData['total_responses'] / $dashboardData['expected_responses']) * 100, 1) }}%
                            @else
                                N/A
                            @endif
                        </span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ isset($dashboardData['expected_responses']) && $dashboardData['expected_responses'] > 0 ? round(($dashboardData['total_responses'] / $dashboardData['expected_responses']) * 100, 1) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $dashboardData['total_responses'] }} de {{ $dashboardData['expected_responses'] ?? 'N/A' }} respuestas esperadas
                        </span>
                    </div>
                </div>
            </div>

            <!-- Participación Cafetería -->
            <div class="col-lg-6">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-utensils"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Participación en Cafetería</span>
                        <span class="info-box-number">{{ $dashboardData['total_responses'] > 0 ? round(($dashboardData['cafeteria_users'] / $dashboardData['total_responses']) * 100, 1) : 0 }}%</span>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ $dashboardData['total_responses'] > 0 ? round(($dashboardData['cafeteria_users'] / $dashboardData['total_responses']) * 100, 1) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $dashboardData['cafeteria_users'] }} de {{ $dashboardData['total_responses'] }} estudiantes usan cafetería
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Participación Transporte -->
            <div class="col-lg-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-bus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Participación en Transporte</span>
                        <span class="info-box-number">{{ $dashboardData['total_responses'] > 0 ? round(($dashboardData['transport_users'] / $dashboardData['total_responses']) * 100, 1) : 0 }}%</span>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: {{ $dashboardData['total_responses'] > 0 ? round(($dashboardData['transport_users'] / $dashboardData['total_responses']) * 100, 1) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $dashboardData['transport_users'] }} de {{ $dashboardData['total_responses'] }} estudiantes usan transporte
                        </span>
                    </div>
                </div>
            </div>

            <!-- Distribución por Grados -->
            <div class="col-lg-6">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-chart-pie"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Distribución por Grados</span>
                        <span class="info-box-number">{{ count($dashboardData['grades'] ?? []) }} grados</span>
                        <div class="progress">
                            <div class="progress-bar bg-secondary" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Respuestas distribuidas en {{ count($dashboardData['grades'] ?? []) }} grados diferentes
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis Detallado de Servicios -->
        <div class="row">
            <!-- Análisis de Cafetería -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-utensils text-success"></i>
                            Análisis de Cafetería
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($dashboardData['cafeteria']['total_usuarios'] > 0)
                            <div class="chart-responsive">
                                <canvas id="cafeteriaChart" height="150"></canvas>
                            </div>
                            
                            <div class="mt-3">
                                <div class="progress-group">
                                    Calidad de Sabor
                                    <span class="float-right"><b>{{ $dashboardData['cafeteria']['calidad_sabor'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" style="width: {{ $dashboardData['cafeteria']['calidad_sabor'] }}%"></div>
                                    </div>
                                </div>
                                <div class="progress-group">
                                    Satisfacción con Porciones
                                    <span class="float-right"><b>{{ $dashboardData['cafeteria']['porcion_satisfaccion'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" style="width: {{ $dashboardData['cafeteria']['porcion_satisfaccion'] }}%"></div>
                                    </div>
                                </div>
                                <div class="progress-group">
                                    Calidad del Menú
                                    <span class="float-right"><b>{{ $dashboardData['cafeteria']['menu_calidad'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-info" style="width: {{ $dashboardData['cafeteria']['menu_calidad'] }}%"></div>
                                    </div>
                                </div>
                                <div class="progress-group">
                                    Variedad del Menú
                                    <span class="float-right"><b>{{ $dashboardData['cafeteria']['variedad_menu'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-primary" style="width: {{ $dashboardData['cafeteria']['variedad_menu'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-utensils" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay datos de cafetería para este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Análisis de Transporte -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bus text-warning"></i>
                            Análisis de Transporte
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($dashboardData['transport']['total_usuarios'] > 0)
                            <div class="chart-responsive">
                                <canvas id="transportChart" height="150"></canvas>
                            </div>
                            
                            <div class="mt-3">
                                <div class="progress-group">
                                    Puntualidad
                                    <span class="float-right"><b>{{ $dashboardData['transport']['puntualidad'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" style="width: {{ $dashboardData['transport']['puntualidad'] }}%"></div>
                                    </div>
                                </div>
                                <div class="progress-group">
                                    Limpieza del Vehículo
                                    <span class="float-right"><b>{{ $dashboardData['transport']['limpieza_vehiculo'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" style="width: {{ $dashboardData['transport']['limpieza_vehiculo'] }}%"></div>
                                    </div>
                                </div>
                                <div class="progress-group">
                                    Trato del Personal
                                    <span class="float-right"><b>{{ $dashboardData['transport']['trato_personal'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-info" style="width: {{ $dashboardData['transport']['trato_personal'] }}%"></div>
                                    </div>
                                </div>
                                <div class="progress-group">
                                    Comunicación
                                    <span class="float-right"><b>{{ $dashboardData['transport']['comunicacion'] }}%</b></span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-primary" style="width: {{ $dashboardData['transport']['comunicacion'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-bus" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay datos de transporte para este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Períodos Disponibles -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt"></i>
                            Períodos Disponibles
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($periods->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Período</th>
                                            <th>Año</th>
                                            <th>Mes</th>
                                            <th>Fecha de Carga</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($periods as $period)
                                            <tr class="{{ $period['id'] == $dashboardData['latest_period'] ? 'table-active' : '' }}">
                                                <td>
                                                    {{ $period['label'] }}
                                                    @if($period['id'] == $dashboardData['latest_period'])
                                                        <span class="badge badge-primary">Actual</span>
                                                    @endif
                                                </td>
                                                <td>{{ $period['year'] }}</td>
                                                <td>{{ str_pad($period['month'], 2, '0', STR_PAD_LEFT) }}</td>
                                                <td>{{ \Carbon\Carbon::parse($period['date'])->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('surveys.parent-student.analysis', ['period' => $period['id']]) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-chart-line"></i>
                                                        Analizar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-calendar-times" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay períodos disponibles</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
<style>
    .small-box .icon {
        font-size: 60px;
    }
    
    .progress-group {
        margin-bottom: 15px;
    }
    
    .card {
        border-radius: 10px;
    }
    
    .info-box {
        border-radius: 10px;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    
    .small-box {
        border-radius: 10px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
        // Gráfico de Cafetería
        @if($dashboardData['cafeteria']['total_usuarios'] > 0)
        const cafeteriaCtx = document.getElementById('cafeteriaChart').getContext('2d');
        const cafeteriaChart = new Chart(cafeteriaCtx, {
            type: 'radar',
            data: {
                labels: ['Calidad Sabor', 'Porciones', 'Menú', 'Variedad', 'Temperatura', 'Limpieza', 'Personal'],
                datasets: [{
                    label: 'Satisfacción (%)',
                    data: [
                        {{ $dashboardData['cafeteria']['calidad_sabor'] }},
                        {{ $dashboardData['cafeteria']['porcion_satisfaccion'] }},
                        {{ $dashboardData['cafeteria']['menu_calidad'] }},
                        {{ $dashboardData['cafeteria']['variedad_menu'] }},
                        {{ $dashboardData['cafeteria']['temperatura_adecuada'] }},
                        {{ $dashboardData['cafeteria']['limpieza_comedor'] }},
                        {{ $dashboardData['cafeteria']['trato_personal'] }}
                    ],
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    pointBackgroundColor: 'rgba(40, 167, 69, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(40, 167, 69, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        @endif

        // Gráfico de Transporte
        @if($dashboardData['transport']['total_usuarios'] > 0)
        const transportCtx = document.getElementById('transportChart').getContext('2d');
        const transportChart = new Chart(transportCtx, {
            type: 'radar',
            data: {
                labels: ['Puntualidad', 'Limpieza', 'Personal', 'Comunicación'],
                datasets: [{
                    label: 'Satisfacción (%)',
                    data: [
                        {{ $dashboardData['transport']['puntualidad'] }},
                        {{ $dashboardData['transport']['limpieza_vehiculo'] }},
                        {{ $dashboardData['transport']['trato_personal'] }},
                        {{ $dashboardData['transport']['comunicacion'] }}
                    ],
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    pointBackgroundColor: 'rgba(255, 193, 7, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 193, 7, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        @endif
    @endif

    function generateReport() {
        alert('Funcionalidad de generación de reportes será implementada próximamente.');
    }

    function exportData() {
        alert('Funcionalidad de exportación de datos será implementada próximamente.');
    }
</script>
@stop
