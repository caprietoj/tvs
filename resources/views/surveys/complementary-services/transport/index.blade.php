@extends('adminlte::page')

@section('title', 'Análisis de Encuestas - Servicios Complementarios')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-utensils text-warning"></i>
                <i class="fas fa-bus text-primary"></i>
                Análisis de Servicios Complementarios
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Encuestas</a></li>
                <li class="breadcrumb-item active">Servicios Complementarios</li>
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
                        <p class="text-muted">Para comenzar a analizar los servicios de cafetería y transporte, primero debe cargar los datos de las encuestas.</p>
                        
                        <div class="mt-4">
                            <a href="{{ route('surveys.complementary-services.transport.upload') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i>
                                Cargar Primera Encuesta
                            </a>
                        </div>

                        <div class="mt-4">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info-circle"></i> Formato de archivo esperado:</h5>
                                <p class="mb-0">El sistema acepta archivos Excel (.xlsx) con las siguientes columnas:</p>
                                <ul class="text-left mt-2">
                                    <li>Marca temporal</li>
                                    <li>Dependencia (Académico, Administración, Departamento de Apoyo)</li>
                                    <li>Preguntas de Cafetería (12 preguntas)</li>
                                    <li>Preguntas de Transporte (8 preguntas)</li>
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
                            <div class="col-md-6">
                                <a href="{{ route('surveys.complementary-services.transport.upload') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-upload"></i>
                                    Cargar Nueva Encuesta
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#compareModal">
                                    <i class="fas fa-chart-bar"></i>
                                    Comparar Períodos
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
                        <h3>{{ $dashboardData['total_responses'] }}/{{ $dashboardData['expected_responses'] }}</h3>
                        <p>Respuestas Obtenidas/Esperadas</p>
                        <small>Período: {{ $dashboardData['latest_period'] }} 
                            ({{ round(($dashboardData['total_responses'] / $dashboardData['expected_responses']) * 100, 1) }}%)
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
                        <p>Respuestas Cafetería</p>
                        <small>{{ round(($dashboardData['cafeteria_users'] / $dashboardData['total_responses']) * 100, 1) }}% del total</small>
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
                        <p>Respuestas Transporte</p>
                        <small>{{ round(($dashboardData['transport_users'] / $dashboardData['total_responses']) * 100, 1) }}% del total</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bus"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ count($dashboardData['dependencies']) }}</h3>
                        <p>Dependencias Participantes</p>
                        <small>
                            @if(count($dashboardData['dependencies']) > 0)
                                {{ implode(', ', array_slice(array_keys($dashboardData['dependencies']->toArray()), 0, 2)) }}
                                @if(count($dashboardData['dependencies']) > 2)
                                    y {{ count($dashboardData['dependencies']) - 2 }} más
                                @endif
                            @else
                                Sin participación registrada
                            @endif
                        </small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis de Cafetería -->
        <div class="row">
            <div class="col-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-utensils"></i>
                            Análisis del Servicio de Cafetería
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach([
                                'calidad_sabor' => ['label' => 'Calidad y Sabor', 'icon' => 'fas fa-star', 'color' => 'primary'],
                                'porcion_satisfaccion' => ['label' => 'Satisfacción Porciones', 'icon' => 'fas fa-balance-scale', 'color' => 'success'],
                                'menu_calidad' => ['label' => 'Calidad del Menú', 'icon' => 'fas fa-list', 'color' => 'info'],
                                'variedad_menu' => ['label' => 'Variedad del Menú', 'icon' => 'fas fa-layer-group', 'color' => 'warning'],
                                'temperatura_adecuada' => ['label' => 'Temperatura', 'icon' => 'fas fa-thermometer-half', 'color' => 'danger'],
                                'limpieza_comedor' => ['label' => 'Limpieza', 'icon' => 'fas fa-broom', 'color' => 'secondary'],
                                'trato_personal' => ['label' => 'Trato Personal', 'icon' => 'fas fa-handshake', 'color' => 'dark']
                            ] as $key => $config)
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-{{ $config['color'] }}">
                                            <i class="{{ $config['icon'] }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $config['label'] }}</span>
                                            <span class="info-box-number">{{ $dashboardData['cafeteria'][$key] ?? 0 }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $config['color'] }}" 
                                                     style="width: {{ $dashboardData['cafeteria'][$key] ?? 0 }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Satisfacción en {{ strtolower($config['label']) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Indicadores de Cafetería - {{ $dashboardData['latest_period'] }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="cafeteriaChart" height="100"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Distribución por Dependencia</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="dependencyChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis de Transporte -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bus"></i>
                            Análisis del Servicio de Transporte
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach([
                                'puntualidad' => ['label' => 'Puntualidad', 'icon' => 'fas fa-clock', 'color' => 'success'],
                                'limpieza_vehiculo' => ['label' => 'Limpieza Vehículo', 'icon' => 'fas fa-spray-can', 'color' => 'info'],
                                'trato_personal' => ['label' => 'Trato Personal', 'icon' => 'fas fa-user-friends', 'color' => 'warning'],
                                'comunicacion' => ['label' => 'Comunicación', 'icon' => 'fas fa-comments', 'color' => 'primary']
                            ] as $key => $config)
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-{{ $config['color'] }}">
                                            <i class="{{ $config['icon'] }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $config['label'] }}</span>
                                            <span class="info-box-number">{{ $dashboardData['transport'][$key] ?? 0 }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $config['color'] }}" 
                                                     style="width: {{ $dashboardData['transport'][$key] ?? 0 }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Satisfacción en {{ strtolower($config['label']) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Indicadores de Transporte - {{ $dashboardData['latest_period'] }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="transportChart" height="100"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Participación por Dependencia</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Dependencia</th>
                                                        <th>Respuestas</th>
                                                        <th>%</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dashboardData['dependencies'] as $dep => $count)
                                                        <tr>
                                                            <td>{{ $dep }}</td>
                                                            <td>{{ $count }}</td>
                                                            <td>{{ round(($count / $dashboardData['total_responses']) * 100, 1) }}%</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tasks"></i>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="callout callout-success">
                                    <h5><i class="fas fa-trophy"></i> Aspectos Destacados</h5>
                                    <p>Los indicadores con mejor puntuación del período actual.</p>
                                    <button class="btn btn-sm btn-success" onclick="showBestMetrics()">Ver Detalles</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="callout callout-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Oportunidades de Mejora</h5>
                                    <p>Áreas que requieren atención prioritaria para el próximo período.</p>
                                    <button class="btn btn-sm btn-warning" onclick="showImprovementAreas()">Ver Detalles</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comentarios Cualitativos -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-comments"></i> Comentarios Positivos</h5>
                                    <p>Lo que más valoran los usuarios de los servicios.</p>
                                    <button class="btn btn-sm btn-info" onclick="showPositiveComments()">Ver Comentarios</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="callout callout-danger">
                                    <h5><i class="fas fa-lightbulb"></i> Sugerencias de Mejora</h5>
                                    <p>Ideas y sugerencias de la comunidad educativa.</p>
                                    <button class="btn btn-sm btn-danger" onclick="showImprovementComments()">Ver Sugerencias</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal para Comparación -->
<div class="modal fade" id="compareModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-chart-bar"></i>
                    Comparar Períodos
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('surveys.complementary-services.transport.compare') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period1">Período Base:</label>
                                <select class="form-control" id="period1" name="period1" required>
                                    <option value="">Seleccione período base</option>
                                    @if(isset($periods))
                                        @foreach($periods as $period)
                                            <option value="{{ $period['id'] }}">{{ $period['label'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period2">Período de Comparación:</label>
                                <select class="form-control" id="period2" name="period2" required>
                                    <option value="">Seleccione período a comparar</option>
                                    @if(isset($periods))
                                        @foreach($periods as $period)
                                            <option value="{{ $period['id'] }}">{{ $period['label'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="service">Servicio:</label>
                                <select class="form-control" id="service" name="service">
                                    <option value="both">Ambos servicios</option>
                                    <option value="cafeteria">Solo Cafetería</option>
                                    <option value="transport">Solo Transporte</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dependency">Dependencia:</label>
                                <select class="form-control" id="dependency" name="dependency">
                                    <option value="all">Todas las dependencias</option>
                                    <option value="Académico">Académico</option>
                                    <option value="Administración">Administración</option>
                                    <option value="Departamento de Apoyo">Departamento de Apoyo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i>
                        Generar Comparación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .small-box:hover {
        transform: translateY(-2px);
    }
    
    .info-box {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .info-box:hover {
        transform: translateY(-1px);
    }
    
    .card {
        border-radius: 15px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 30px rgba(0,0,0,0.15);
    }
    
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    .progress {
        height: 6px;
        border-radius: 3px;
    }
    
    .progress-bar {
        border-radius: 3px;
    }
    
    .callout {
        border-radius: 10px;
        border-left: 5px solid;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        max-height: 300px;
    }
    
    .chart-container canvas {
        max-height: 100% !important;
    }
    
    .table-responsive {
        border-radius: 8px;
    }
    
    .modal-content {
        border-radius: 15px;
    }
    
    .alert {
        border-radius: 10px;
    }
    
    @media (max-width: 768px) {
        .small-box {
            margin-bottom: 15px;
        }
        
        .btn {
            padding: 8px 20px;
            font-size: 14px;
        }
    }
    
    /* Chart specific styles */
    .metric-improvement {
        color: #28a745;
        font-weight: bold;
    }
    
    .metric-decline {
        color: #dc3545;
        font-weight: bold;
    }
    
    .metric-stable {
        color: #6c757d;
        font-weight: bold;
    }
    
    .comparison-arrow {
        font-size: 1.2em;
        margin: 0 5px;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    
    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }
    
    .list-group-item {
        border: 1px solid rgba(0,0,0,.125);
        margin-bottom: 5px;
        border-radius: 8px;
    }
    
    .modal-lg {
        max-width: 900px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// Global chart configurations
Chart.defaults.font.family = "'Source Sans Pro', sans-serif";
Chart.defaults.color = '#6c757d';

@if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
// Dashboard data
const dashboardData = @json($dashboardData);

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCafeteriaChart();
    initializeTransportChart();
    initializeDependencyChart();
});

function initializeCafeteriaChart() {
    const ctx = document.getElementById('cafeteriaChart');
    if (!ctx) return;
    
    const data = {
        labels: ['Calidad y Sabor', 'Porciones', 'Menú', 'Variedad', 'Temperatura', 'Limpieza', 'Trato Personal'],
        datasets: [{
            label: 'Satisfacción (%)',
            data: [
                dashboardData.cafeteria.calidad_sabor || 0,
                dashboardData.cafeteria.porcion_satisfaccion || 0,
                dashboardData.cafeteria.menu_calidad || 0,
                dashboardData.cafeteria.variedad_menu || 0,
                dashboardData.cafeteria.temperatura_adecuada || 0,
                dashboardData.cafeteria.limpieza_comedor || 0,
                dashboardData.cafeteria.trato_personal || 0
            ],
            backgroundColor: [
                'rgba(0, 123, 255, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)',
                'rgba(52, 58, 64, 0.8)'
            ],
            borderColor: [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(108, 117, 125, 1)',
                'rgba(52, 58, 64, 1)'
            ],
            borderWidth: 2,
            borderRadius: 4
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return 'Satisfacción: ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            }
        }
    });
}

function initializeTransportChart() {
    const ctx = document.getElementById('transportChart');
    if (!ctx) return;
    
    const data = {
        labels: ['Puntualidad', 'Limpieza Vehículo', 'Trato Personal', 'Comunicación'],
        datasets: [{
            label: 'Satisfacción (%)',
            data: [
                dashboardData.transport.puntualidad || 0,
                dashboardData.transport.limpieza_vehiculo || 0,
                dashboardData.transport.trato_personal || 0,
                dashboardData.transport.comunicacion || 0
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(0, 123, 255, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(0, 123, 255, 1)'
            ],
            borderWidth: 2,
            borderRadius: 4
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return 'Satisfacción: ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            }
        }
    });
}

function initializeDependencyChart() {
    const ctx = document.getElementById('dependencyChart');
    if (!ctx) return;
    
    const dependencies = dashboardData.dependencies;
    const labels = Object.keys(dependencies);
    const data = Object.values(dependencies);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(0, 123, 255, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            }
        }
    });
}
@endif

// Utility functions
function generateReport() {
    showToast('Generando reporte...', 'info');
    // Implementation for PDF report generation
    setTimeout(() => {
        showToast('Reporte generado exitosamente', 'success');
    }, 2000);
}

function exportData() {
    showToast('Exportando datos...', 'info');
    // Implementation for data export
    setTimeout(() => {
        showToast('Datos exportados exitosamente', 'success');
    }, 1500);
}

function showBestMetrics() {
    @if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
    const cafeteria = dashboardData.cafeteria;
    const transport = dashboardData.transport;
    
    let bestMetrics = [];
    
    // Find best cafeteria metrics
    Object.keys(cafeteria).forEach(key => {
        if (!['total_respuestas', 'total_usuarios', 'aspectos_positivos', 'oportunidades_mejora'].includes(key) && 
            typeof cafeteria[key] === 'number' && cafeteria[key] >= 80) {
            bestMetrics.push({
                service: 'Cafetería',
                metric: getMetricLabel(key),
                value: cafeteria[key]
            });
        }
    });
    
    // Find best transport metrics
    Object.keys(transport).forEach(key => {
        if (!['total_respuestas', 'total_usuarios', 'aspectos_positivos', 'oportunidades_mejora'].includes(key) && 
            typeof transport[key] === 'number' && transport[key] >= 80) {
            bestMetrics.push({
                service: 'Transporte',
                metric: getMetricLabel(key),
                value: transport[key]
            });
        }
    });
    
    bestMetrics.sort((a, b) => b.value - a.value);
    
    let content = '<div class="table-responsive"><table class="table table-striped">';
    content += '<thead><tr><th>Servicio</th><th>Indicador</th><th>Puntuación</th></tr></thead><tbody>';
    
    bestMetrics.forEach(metric => {
        content += `<tr>
            <td><span class="badge badge-${metric.service === 'Cafetería' ? 'success' : 'primary'}">${metric.service}</span></td>
            <td>${metric.metric}</td>
            <td><strong>${metric.value}%</strong></td>
        </tr>`;
    });
    
    content += '</tbody></table></div>';
    
    showModal('Aspectos Destacados', content);
    @else
    showToast('No hay datos disponibles para mostrar', 'warning');
    @endif
}

function showImprovementAreas() {
    @if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
    const cafeteria = dashboardData.cafeteria;
    const transport = dashboardData.transport;
    
    let improvementAreas = [];
    
    // Collect all cafeteria metrics
    Object.keys(cafeteria).forEach(key => {
        if (!['total_respuestas', 'total_usuarios', 'aspectos_positivos', 'oportunidades_mejora'].includes(key) && 
            typeof cafeteria[key] === 'number') {
            improvementAreas.push({
                service: 'Cafetería',
                metric: getMetricLabel(key),
                value: cafeteria[key],
                priority: cafeteria[key] < 50 ? 'Alta' : cafeteria[key] < 70 ? 'Media' : 'Baja'
            });
        }
    });
    
    // Collect all transport metrics
    Object.keys(transport).forEach(key => {
        if (!['total_respuestas', 'total_usuarios', 'aspectos_positivos', 'oportunidades_mejora'].includes(key) && 
            typeof transport[key] === 'number') {
            improvementAreas.push({
                service: 'Transporte',
                metric: getMetricLabel(key),
                value: transport[key],
                priority: transport[key] < 50 ? 'Alta' : transport[key] < 70 ? 'Media' : 'Baja'
            });
        }
    });
    
    // Sort by value ascending (worst first) and take the bottom 5 metrics as improvement areas
    improvementAreas.sort((a, b) => a.value - b.value);
    
    // Show the 5 worst performing metrics as improvement opportunities
    improvementAreas = improvementAreas.slice(0, 5);
    
    let content = '<div class="table-responsive"><table class="table table-striped">';
    content += '<thead><tr><th>Servicio</th><th>Indicador</th><th>Puntuación</th><th>Prioridad</th></tr></thead><tbody>';
    
    improvementAreas.forEach(area => {
        const priorityClass = area.priority === 'Alta' ? 'danger' : area.priority === 'Media' ? 'warning' : 'info';
        content += `<tr>
            <td><span class="badge badge-${area.service === 'Cafetería' ? 'success' : 'primary'}">${area.service}</span></td>
            <td>${area.metric}</td>
            <td><strong>${area.value}%</strong></td>
            <td><span class="badge badge-${priorityClass}">${area.priority}</span></td>
        </tr>`;
    });
    
    content += '</tbody></table></div>';
    
    // Add explanation about the improvement areas
    if (improvementAreas.length > 0) {
        content += '<div class="mt-3"><div class="alert alert-info">';
        content += '<i class="fas fa-lightbulb"></i> <strong>Nota:</strong> ';
        content += 'Se muestran las 5 métricas con menores puntuaciones como oportunidades prioritarias de mejora.';
        content += '</div></div>';
    } else {
        content = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay métricas disponibles para analizar.</div>';
    }
    
    showModal('Oportunidades de Mejora', content);
    @else
    showToast('No hay datos disponibles para mostrar', 'warning');
    @endif
}

function getMetricLabel(key) {
    const labels = {
        'calidad_sabor': 'Calidad y Sabor',
        'porcion_satisfaccion': 'Satisfacción con Porciones',
        'menu_calidad': 'Calidad del Menú',
        'variedad_menu': 'Variedad del Menú',
        'temperatura_adecuada': 'Temperatura Adecuada',
        'limpieza_comedor': 'Limpieza del Comedor',
        'trato_personal': 'Trato del Personal',
        'puntualidad': 'Puntualidad',
        'limpieza_vehiculo': 'Limpieza del Vehículo',
        'comunicacion': 'Comunicación'
    };
    return labels[key] || key;
}

function showModal(title, content) {
    const modal = $(`
        <div class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">${title}</h4>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    modal.modal('show');
    modal.on('hidden.bs.modal', function() {
        modal.remove();
    });
}

function showToast(message, type = 'info') {
    const iconMap = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    const toast = $(`
        <div class="toast-notification toast-${type}" style="
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#17a2b8'};
            color: ${type === 'warning' ? '#212529' : 'white'};
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        ">
            <i class="${iconMap[type] || 'fas fa-info-circle'} mr-2"></i>
            ${message}
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => {
        toast.css({
            'opacity': '1',
            'transform': 'translateX(0)'
        });
    }, 100);
    
    setTimeout(() => {
        toast.css({
            'opacity': '0',
            'transform': 'translateX(100%)'
        });
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showPositiveComments() {
    @if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
    const cafeteria = dashboardData.cafeteria;
    const transport = dashboardData.transport;
    
    let content = '<div class="row">';
    
    // Comentarios positivos de cafetería
    if (cafeteria.aspectos_positivos && cafeteria.aspectos_positivos.length > 0) {
        content += '<div class="col-md-6">';
        content += '<h5><i class="fas fa-utensils text-success"></i> Cafetería</h5>';
        content += '<ul class="list-group">';
        cafeteria.aspectos_positivos.forEach(comment => {
            content += `<li class="list-group-item border-left-success"><i class="fas fa-quote-left text-muted"></i> ${comment}</li>`;
        });
        content += '</ul>';
        content += '</div>';
    }
    
    // Comentarios positivos de transporte
    if (transport.aspectos_positivos && transport.aspectos_positivos.length > 0) {
        content += '<div class="col-md-6">';
        content += '<h5><i class="fas fa-bus text-primary"></i> Transporte</h5>';
        content += '<ul class="list-group">';
        transport.aspectos_positivos.forEach(comment => {
            content += `<li class="list-group-item border-left-primary"><i class="fas fa-quote-left text-muted"></i> ${comment}</li>`;
        });
        content += '</ul>';
        content += '</div>';
    }
    
    content += '</div>';
    
    if (!cafeteria.aspectos_positivos?.length && !transport.aspectos_positivos?.length) {
        content = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay comentarios positivos registrados para este período.</div>';
    }
    
    showModal('Comentarios Positivos', content);
    @else
    showToast('No hay datos disponibles para mostrar', 'warning');
    @endif
}

function showImprovementComments() {
    @if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
    const cafeteria = dashboardData.cafeteria;
    const transport = dashboardData.transport;
    
    let content = '<div class="row">';
    
    // Oportunidades de mejora de cafetería
    if (cafeteria.oportunidades_mejora && cafeteria.oportunidades_mejora.length > 0) {
        content += '<div class="col-md-6">';
        content += '<h5><i class="fas fa-utensils text-warning"></i> Cafetería</h5>';
        content += '<ul class="list-group">';
        cafeteria.oportunidades_mejora.forEach(comment => {
            content += `<li class="list-group-item border-left-warning"><i class="fas fa-lightbulb text-warning"></i> ${comment}</li>`;
        });
        content += '</ul>';
        content += '</div>';
    }
    
    // Oportunidades de mejora de transporte
    if (transport.oportunidades_mejora && transport.oportunidades_mejora.length > 0) {
        content += '<div class="col-md-6">';
        content += '<h5><i class="fas fa-bus text-danger"></i> Transporte</h5>';
        content += '<ul class="list-group">';
        transport.oportunidades_mejora.forEach(comment => {
            content += `<li class="list-group-item border-left-danger"><i class="fas fa-lightbulb text-danger"></i> ${comment}</li>`;
        });
        content += '</ul>';
        content += '</div>';
    }
    
    content += '</div>';
    
    if (!cafeteria.oportunidades_mejora?.length && !transport.oportunidades_mejora?.length) {
        content = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay sugerencias de mejora registradas para este período.</div>';
    }
    
    showModal('Sugerencias de Mejora', content);
    @else
    showToast('No hay datos disponibles para mostrar', 'warning');
    @endif
}

console.log('Servicios Complementarios - Sistema de Análisis cargado correctamente');
</script>
@stop
