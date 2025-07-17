@extends('adminlte::page')

@section('title', 'Dashboard - Encuesta Cliente Interno Almacén')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-warehouse text-primary"></i>
                Dashboard - Encuesta Cliente Interno Almacén
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Encuestas</a></li>
                <li class="breadcrumb-item active">Almacén</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(!$hasData)
        <!-- Mensaje cuando no hay datos -->
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Sin datos</h5>
            {{ $message }}
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-upload"></i>
                            Subir Primera Encuesta
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <p class="lead">Para comenzar a visualizar los análisis estadísticos, sube el primer archivo de resultados de la encuesta.</p>
                        <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload"></i>
                            Subir Archivo Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Dashboard con datos -->
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-info"></i> Información</h5>
            Este dashboard presenta el análisis estadístico de la satisfacción del personal interno con los servicios del área de Almacén.
            <strong>Último período evaluado:</strong> {{ $latestPeriod }}
        </div>

        <!-- KPIs Principales -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $latestStats['total_responses'] }}</h3>
                        <p>Total de Respuestas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Ver detalles <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $latestStats['satisfaction_average'] }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Satisfacción General</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Ver análisis <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ count($latestStats['by_dependencia']) }}</h3>
                        <p>Dependencias Evaluadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Ver distribución <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $latestPeriod }}</h3>
                        <p>Último Período</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="small-box-footer">
                        Subir nueva encuesta <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Análisis por Categorías -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Análisis de Satisfacción por Categoría - {{ $latestPeriod }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach([
                                'experiencia' => ['label' => 'Experiencia General', 'icon' => 'fas fa-star', 'color' => 'primary'],
                                'tiempos' => ['label' => 'Tiempos de Atención', 'icon' => 'fas fa-clock', 'color' => 'success'],
                                'oportunidad' => ['label' => 'Resolución Oportuna', 'icon' => 'fas fa-check-circle', 'color' => 'info'],
                                'disponibilidad' => ['label' => 'Disponibilidad Materiales', 'icon' => 'fas fa-boxes', 'color' => 'warning'],
                                'servicio_persona' => ['label' => 'Atención Personal', 'icon' => 'fas fa-user-tie', 'color' => 'secondary'],
                                'calidad_materiales' => ['label' => 'Calidad Materiales', 'icon' => 'fas fa-quality', 'color' => 'dark'],
                                'cotizaciones' => ['label' => 'Opciones Cotizaciones', 'icon' => 'fas fa-file-invoice', 'color' => 'purple'],
                                'proveedores' => ['label' => 'Cumplimiento Proveedores', 'icon' => 'fas fa-handshake', 'color' => 'indigo']
                            ] as $key => $config)
                            @php
                                $stats = $latestStats['by_question'][$key];
                                $percentage = isset($stats['si']) ? $stats['si'] : 
                                    ($stats['excelente'] * 1 + $stats['bueno'] * 0.75 + $stats['regular'] * 0.5 + $stats['deficiente'] * 0.25);
                            @endphp
                            <div class="col-md-6 col-lg-3 mb-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-{{ $config['color'] }}">
                                        <i class="{{ $config['icon'] }}"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $config['label'] }}</span>
                                        <span class="info-box-number">{{ round($percentage, 1) }}%</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-{{ $config['color'] }}" 
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="progress-description">
                                            Promedio: {{ $stats['average_score'] }}/4.0
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de Análisis -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Evolución de Satisfacción
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="satisfactionTrendChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-pie-chart"></i>
                            Distribución por Dependencia
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="dependencyChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis Detallado por Preguntas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Análisis Detallado por Pregunta
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="questionAnalysisChart" style="height: 500px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aspectos Destacados y Oportunidades de Mejora -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-thumbs-up"></i>
                            Aspectos Más Destacados
                        </h3>
                    </div>
                    <div class="card-body">
                        @forelse($latestStats['top_highlights'] as $highlight)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <span>{{ $highlight['text'] }}</span>
                            <span class="badge badge-success">{{ $highlight['count'] }}</span>
                        </div>
                        @empty
                        <p class="text-muted">No hay aspectos destacados registrados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tools"></i>
                            Principales Oportunidades de Mejora
                        </h3>
                    </div>
                    <div class="card-body">
                        @forelse($latestStats['top_issues'] as $issue)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <span>{{ $issue['text'] }}</span>
                            <span class="badge badge-warning">{{ $issue['count'] }}</span>
                        </div>
                        @empty
                        <p class="text-muted">No hay oportunidades de mejora registradas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Principales -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs"></i>
                            Acciones Disponibles
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <a href="{{ route('surveys.internal-client.warehouse.upload') }}" 
                                       class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-upload"></i><br>
                                        Subir Nueva Encuesta
                                    </a>
                                    <small class="text-muted">Sube resultados de encuesta en Excel</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <a href="{{ route('surveys.internal-client.warehouse.export') }}" 
                                       class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-download"></i><br>
                                        Exportar Datos
                                    </a>
                                    <small class="text-muted">Descarga datos en Excel</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <button class="btn btn-warning btn-lg btn-block" onclick="printDashboard()">
                                        <i class="fas fa-print"></i><br>
                                        Imprimir Reporte
                                    </button>
                                    <small class="text-muted">Genera reporte imprimible</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <button class="btn btn-info btn-lg btn-block" onclick="refreshData()">
                                        <i class="fas fa-sync"></i><br>
                                        Actualizar Datos
                                    </button>
                                    <small class="text-muted">Recarga la información</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
<style>
    .info-box {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .info-box:hover {
        transform: translateY(-2px);
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .small-box {
        border-radius: 10px;
        transition: transform 0.2s;
    }
    
    .small-box:hover {
        transform: translateY(-2px);
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .progress {
        height: 8px;
        border-radius: 5px;
    }
    
    .progress-bar {
        border-radius: 5px;
    }
    
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
    }
    
    .chart-container {
        position: relative;
        height: 400px;
        margin: 20px 0;
    }
    
    .highlight-item, .issue-item {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .highlight-item:hover {
        background-color: #d4edda !important;
        border-left-color: #28a745;
    }
    
    .issue-item:hover {
        background-color: #fff3cd !important;
        border-left-color: #ffc107;
    }
    
    .badge {
        font-size: 0.9em;
        padding: 6px 12px;
        border-radius: 15px;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }
    
    .card-success .card-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .card-warning .card-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .card-primary .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    @media print {
        .btn, .card-tools {
            display: none !important;
        }
        
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if($hasData)
    // Datos para los gráficos
    const historicalData = @json($historicalData);
    const dependencyData = @json($latestStats['by_dependencia']);
    const questionData = @json($latestStats['by_question']);
    
    // Configuración global de Chart.js
    Chart.defaults.font.family = 'Arial, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#495057';
    
    // Gráfico de tendencia de satisfacción
    const trendCtx = document.getElementById('satisfactionTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: historicalData.map(d => d.survey_period),
            datasets: [{
                label: 'Satisfacción General (%)',
                data: historicalData.map(d => d.satisfaction_percentage),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Satisfacción: ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Gráfico de distribución por dependencia
    const depCtx = document.getElementById('dependencyChart').getContext('2d');
    new Chart(depCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(dependencyData),
            datasets: [{
                data: Object.values(dependencyData).map(d => d.count),
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
    
    // Gráfico de análisis por pregunta
    const questionCtx = document.getElementById('questionAnalysisChart').getContext('2d');
    const questionLabels = [
        'Experiencia General',
        'Tiempos de Atención',
        'Resolución Oportuna',
        'Disponibilidad Materiales',
        'Atención Personal',
        'Calidad Materiales',
        'Opciones Cotizaciones',
        'Cumplimiento Proveedores'
    ];
    
    const questionKeys = Object.keys(questionData);
    const excellentData = questionKeys.map(key => questionData[key].excelente || questionData[key].si || 0);
    const goodData = questionKeys.map(key => questionData[key].bueno || 0);
    const regularData = questionKeys.map(key => questionData[key].regular || 0);
    const deficientData = questionKeys.map(key => questionData[key].deficiente || questionData[key].no || 0);
    
    new Chart(questionCtx, {
        type: 'bar',
        data: {
            labels: questionLabels,
            datasets: [
                {
                    label: 'Excelente/Sí',
                    data: excellentData,
                    backgroundColor: '#28a745',
                    borderRadius: 4
                },
                {
                    label: 'Bueno',
                    data: goodData,
                    backgroundColor: '#17a2b8',
                    borderRadius: 4
                },
                {
                    label: 'Regular',
                    data: regularData,
                    backgroundColor: '#ffc107',
                    borderRadius: 4
                },
                {
                    label: 'Deficiente/No',
                    data: deficientData,
                    backgroundColor: '#dc3545',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true,
                    ticks: {
                        maxRotation: 45,
                        font: {
                            size: 10
                        }
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Animación de números
    function animateNumbers() {
        $('.info-box-number').each(function() {
            const $this = $(this);
            const number = parseFloat($this.text());
            const suffix = $this.text().includes('%') ? '%' : '';
            
            if (!isNaN(number)) {
                $this.prop('Counter', 0).animate({
                    Counter: number
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        const displayValue = number % 1 === 0 ? Math.floor(this.Counter) : this.Counter.toFixed(1);
                        $this.text(displayValue + suffix);
                    }
                });
            }
        });
    }
    
    // Ejecutar animaciones
    setTimeout(animateNumbers, 500);
    @endif
    
    // Funciones utilitarias
    function printDashboard() {
        window.print();
    }
    
    function refreshData() {
        location.reload();
    }
    
    // Mostrar toast de notificación
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="toast-notification ${type}" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#28a745' : '#17a2b8'};
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s ease;
            ">
                <i class="fas fa-check-circle mr-2"></i>
                ${message}
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => {
            toast.css('opacity', '1');
        }, 100);
        
        setTimeout(() => {
            toast.css('opacity', '0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    console.log('Dashboard de Encuesta de Almacén cargado correctamente');
</script>
@stop
