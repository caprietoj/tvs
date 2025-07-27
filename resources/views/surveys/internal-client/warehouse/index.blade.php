@extends('adminlte::page')

@push('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<style>
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    padding: 10px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px;
    margin-bottom: 10px;
}

.small-box {
    border-radius: 8px;
    transition: transform 0.2s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.small-box:hover {
    transform: translateY(-1px);
}

.progress {
    height: 6px;
    border-radius: 3px;
    margin-top: 8px;
}

.progress-bar {
    border-radius: 3px;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.btn {
    border-radius: 20px;
    padding: 8px 20px;
    font-weight: 500;
    font-size: 14px;
}

.highlight-item, .improvement-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.highlight-icon, .improvement-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    background: rgba(255,255,255,0.1);
}

.highlight-content, .improvement-content {
    flex: 1;
}

.highlight-content h5, .improvement-content h5 {
    margin: 0 0 5px 0;
    font-size: 1rem;
}

.progress-sm {
    height: 8px;
    margin: 8px 0;
}

/* Estilos mejorados para el gráfico */
.chart-stats {
    display: flex;
    flex-direction: column;
}

.chart-stats .d-flex {
    gap: 8px;
}

.chart-controls {
    margin-left: auto;
}

.chart-legend {
    text-align: right;
    padding-left: 15px;
    border-left: 1px solid #dee2e6;
}

.legend-color {
    display: inline-block;
    vertical-align: middle;
}

.bg-gradient-primary {
    background: #233e6c !important;
}

.trend-analysis .badge {
    font-size: 0.85rem;
    padding: 6px 12px;
}

.trend-additional-info small {
    display: block;
    margin-bottom: 2px;
}

@media (max-width: 768px) {
    .small-box {
        margin-bottom: 15px;
    }
    
    .chart-legend {
        text-align: left;
        padding-left: 0;
        border-left: none;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        margin-top: 10px;
    }
    
    .chart-stats .d-flex {
        flex-direction: column;
        gap: 4px;
    }
}

/* Estilos para el modal de áreas de mejora */
.modal-lg {
    max-width: 900px;
}

.improvement-details {
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    margin-top: 10px;
}

.card.border-danger {
    border-width: 2px !important;
}

.card.border-warning {
    border-width: 2px !important;
}

.card.border-success {
    border-width: 2px !important;
}

.modal-header.bg-warning {
    border-bottom: 1px solid #ffc107;
}

.priority-badge {
    font-size: 0.75rem;
    padding: 4px 8px;
}

/* Estilos para DataTables */
#warehouseSurveyTable {
    font-size: 13px;
}

#warehouseSurveyTable th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    padding: 8px 4px;
    font-size: 12px;
}

#warehouseSurveyTable td {
    vertical-align: middle;
    padding: 6px 4px;
    text-align: center;
}

.dataTables_wrapper .dataTables_filter input {
    border-radius: 20px;
    border: 1px solid #ddd;
    padding: 5px 15px;
}

.dataTables_wrapper .dataTables_length select {
    border-radius: 15px;
    border: 1px solid #ddd;
    padding: 2px 8px;
}

.table-responsive {
    border-radius: 8px;
    overflow-x: auto;
}

#warehouseSurveyTable_wrapper .row {
    margin: 0;
}

.dataTables_info {
    font-size: 13px;
    color: #6c757d;
}

.dataTables_paginate .paginate_button {
    border-radius: 15px !important;
    margin: 0 2px;
}
</style>
@endpush

@push('js')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Esperar a que Chart.js esté completamente cargado
window.addEventListener('load', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está cargado');
        return;
    }

    // Gráfico de tendencia mejorado y más diciente
    const trendData = {!! json_encode($dashboardData['trend_data'] ?? ['labels' => [], 'datasets' => [], 'analysis' => [], 'summary' => []]) !!};
    const historicalData = @json($historicalData ?? []);
    
    // Crear gráfico de tendencia con datos históricos
    if (historicalData && historicalData.length > 0) {
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        
        // Preparar datos para el gráfico
        const labels = historicalData.map(d => d.survey_period);
        const satisfactionData = historicalData.map(d => parseFloat(d.satisfaction_percentage));
        const responseData = historicalData.map(d => parseInt(d.total_responses));
        
        window.trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Satisfacción General (%)',
                    data: satisfactionData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#007bff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y'
                }, {
                    label: 'Total Respuestas',
                    data: responseData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'start',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Satisfacción: ' + context.parsed.y.toFixed(1) + '%';
                                } else {
                                    return 'Respuestas: ' + context.parsed.y;
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Período de Evaluación',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Porcentaje de Satisfacción (%)',
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            color: '#007bff'
                        },
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            color: '#007bff'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Número de Respuestas',
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            color: '#28a745'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            color: '#28a745'
                        }
                    }
                }
            }
        });

        // Agregar información estadística del gráfico de tendencia
        if (satisfactionData.length > 0) {
            const avg = (satisfactionData.reduce((a, b) => a + b, 0) / satisfactionData.length).toFixed(1);
            const max = Math.max(...satisfactionData).toFixed(1);
            const min = Math.min(...satisfactionData).toFixed(1);
            const totalResponses = responseData.reduce((a, b) => a + b, 0);
            
            document.getElementById('trendAvg').innerText = `Promedio: ${avg}%`;
            document.getElementById('trendMax').innerText = `Máximo: ${max}%`;
            document.getElementById('trendMin').innerText = `Mínimo: ${min}%`;
            
            // Análisis de tendencia
            const trendAnalysisElement = document.getElementById('trendAnalysis');
            if (trendAnalysisElement && satisfactionData.length > 1) {
                let trendText = '';
                const lastValue = satisfactionData[satisfactionData.length - 1];
                const firstValue = satisfactionData[0];
                const trend = lastValue - firstValue;
                
                if (trend > 5) {
                    trendText = '<span class="badge badge-success"><i class="fas fa-arrow-up"></i> Tendencia ascendente (+' + trend.toFixed(1) + '%)</span>';
                } else if (trend < -5) {
                    trendText = '<span class="badge badge-danger"><i class="fas fa-arrow-down"></i> Tendencia descendente (' + trend.toFixed(1) + '%)</span>';
                } else {
                    trendText = '<span class="badge badge-warning"><i class="fas fa-arrows-alt-h"></i> Tendencia estable (' + trend.toFixed(1) + '%)</span>';
                }
                
                trendAnalysisElement.innerHTML = trendText;
            }
            
            // Información adicional
            const additionalInfoElement = document.getElementById('trendAdditionalInfo');
            if (additionalInfoElement) {
                const bestPeriodIndex = satisfactionData.indexOf(Math.max(...satisfactionData));
                const worstPeriodIndex = satisfactionData.indexOf(Math.min(...satisfactionData));
                
                let additionalInfo = '';
                additionalInfo += `<small class="text-success">Mejor período: ${labels[bestPeriodIndex]} (${max}%)</small><br>`;
                additionalInfo += `<small class="text-warning">Período más bajo: ${labels[worstPeriodIndex]} (${min}%)</small><br>`;
                additionalInfo += `<small class="text-info">Total respuestas: ${totalResponses} evaluaciones</small>`;
                
                additionalInfoElement.innerHTML = additionalInfo;
            }
        }
    } else {
        // Si no hay datos históricos, mostrar mensaje
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        trendCtx.font = '16px Arial';
        trendCtx.fillStyle = '#6c757d';
        trendCtx.textAlign = 'center';
        trendCtx.fillText('No hay datos históricos disponibles', trendCtx.canvas.width / 2, trendCtx.canvas.height / 2);
    }
});
</script>
@endpush

@section('title', 'Dashboard - Encuesta Cliente Interno Almacén')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-warehouse mr-2"></i>Dashboard de Almacén</h1>
        <div>
            <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="btn btn-success">
                <i class="fas fa-upload mr-1"></i>Subir Encuesta
            </a>
            @if($selectedPeriod ?? null)
                <a href="{{ route('surveys.internal-client.warehouse.export', ['period' => $selectedPeriod]) }}" class="btn btn-info">
                    <i class="fas fa-download mr-1"></i>Exportar
                </a>
            @endif
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
        <!-- Alert de información -->
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-info"></i> Información</h5>
            Este dashboard presenta el análisis estadístico de la satisfacción del personal interno con los servicios del área de Almacén.
            <strong>Último período evaluado:</strong> {{ $selectedPeriod ?? 'Sin datos' }}
        </div>

        <!-- KPIs Principales -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalResponses ?? 0 }}/100</h3>
                        <p>Respuestas vs Esperadas</p>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $totalResponses > 0 ? (($totalResponses / 100) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="small-box-footer">
                        &nbsp;
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $latestStats['satisfaction_average'] ?? 0 }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Satisfacción General</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="small-box-footer">
                        &nbsp;
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ count($latestStats['by_dependencia'] ?? []) }}</h3>
                        <p>Dependencias Evaluadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="small-box-footer">
                        @if(isset($latestStats['by_dependencia']) && count($latestStats['by_dependencia']) > 0)
                            @foreach($latestStats['by_dependencia'] as $dep => $data)
                                <span class="badge badge-light mr-1">{{ $dep }}</span>
                            @endforeach
                        @else
                            &nbsp;
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $selectedPeriod ?? 'N/A' }}</h3>
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
                            Análisis de Satisfacción por Categoría - {{ $selectedPeriod ?? 'Sin período' }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $categoryNames = [
                                    'experiencia' => 'Experiencia General',
                                    'tiempos' => 'Tiempos de Atención',
                                    'oportunidad' => 'Resolución Oportuna',
                                    'disponibilidad' => 'Disponibilidad Materiales',
                                    'servicio_persona' => 'Atención Personal',
                                    'calidad_materiales' => 'Calidad Materiales',
                                    'cotizaciones' => 'Opciones Cotizaciones',
                                    'proveedores' => 'Cumplimiento Proveedores'
                                ];
                                $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark', 'purple'];
                                $icons = ['fas fa-star', 'fas fa-clock', 'fas fa-check-circle', 'fas fa-boxes', 'fas fa-user-tie', 'fas fa-gem', 'fas fa-file-invoice', 'fas fa-handshake'];
                                $colorIndex = 0;
                            @endphp
                            
                            @if(isset($latestStats['by_question']) && count($latestStats['by_question']) > 0)
                                @foreach($latestStats['by_question'] as $key => $stats)
                                @php
                                    // Calcular porcentaje basado en el tipo de estadística
                                    if (isset($stats['si'])) {
                                        // Para preguntas de Sí/No
                                        $percentage = $stats['si'];
                                    } else {
                                        // Para preguntas de calificación (Excelente, Bueno, Regular, Deficiente)
                                        $percentage = ($stats['excelente'] ?? 0) * 1 + 
                                                     ($stats['bueno'] ?? 0) * 0.75 + 
                                                     ($stats['regular'] ?? 0) * 0.5 + 
                                                     ($stats['deficiente'] ?? 0) * 0.25;
                                    }
                                    
                                    $currentColor = $colors[$colorIndex % count($colors)];
                                    $currentIcon = $icons[$colorIndex % count($icons)];
                                    $colorIndex++;
                                @endphp
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-{{ $currentColor }}">
                                            <i class="{{ $currentIcon }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $categoryNames[$key] ?? ucfirst($key) }}</span>
                                            <span class="info-box-number">{{ round($percentage, 1) }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $currentColor }}" 
                                                     style="width: {{ min($percentage, 100) }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Promedio: {{ $stats['average_score'] ?? 0 }}/4.0
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <div class="alert alert-warning text-center">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                        <h4>No hay datos disponibles</h4>
                                        <p>No se encontraron datos para mostrar. Por favor, cargue algunas encuestas primero.</p>
                                        <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="btn btn-warning">
                                            <i class="fas fa-upload mr-1"></i>Cargar Primera Encuesta
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de Análisis -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-gradient-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-line mr-2"></i>
                            Evolución de Satisfacción
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light mr-2">
                                <i class="fas fa-calendar mr-1"></i>Últimos períodos
                            </span>
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Tendencia histórica de satisfacción
                                    </small>
                                    <div class="chart-controls">
                                        <button class="btn btn-sm btn-outline-primary" onclick="toggleChartType()">
                                            <i class="fas fa-exchange-alt mr-1"></i>Cambiar vista
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="trendChart" width="600" height="280"></canvas>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="chart-stats">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge badge-primary" id="trendAvg">Promedio: --</span>
                                        <span class="badge badge-success" id="trendMax">Máximo: --</span>
                                        <span class="badge badge-warning" id="trendMin">Mínimo: --</span>
                                    </div>
                                    <div class="trend-analysis mb-2" id="trendAnalysis">
                                        <span class="badge badge-light">Analizando tendencia...</span>
                                    </div>
                                    <div class="trend-additional-info" id="trendAdditionalInfo">
                                        <small class="text-muted">Cargando estadísticas...</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="chart-legend">
                                    <div class="d-flex align-items-center mb-1">
                                        <div class="legend-color" style="background-color: #007bff; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px;"></div>
                                        <small class="text-muted">Satisfacción (%)</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="legend-color" style="background-color: #28a745; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px;"></div>
                                        <small class="text-muted">Total Respuestas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card chart-card">
                    <div class="card-header bg-gradient-info text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Distribución por Dependencias
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="departmentChart" width="600" height="280"></canvas>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Distribución de respuestas por dependencia
                            </small>
                        </div>
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
                        @forelse($latestStats['top_highlights'] ?? [] as $highlight)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded highlight-item">
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
                    <div class="card-header text-white">
                        <h3 class="card-title">
                            <i class="fas fa-tools"></i>
                            Principales Oportunidades de Mejora
                        </h3>
                    </div>
                    <div class="card-body">
                        @forelse($latestStats['top_issues'] ?? [] as $issue)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded improvement-item">
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

        <!-- Tabla de Respuestas del Cuestionario -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-table mr-2"></i>
                            Respuestas Detalladas del Cuestionario de Almacén
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            // Funciones helper para limpiar texto
                            if (!function_exists('cleanWarehouseText')) {
                                function cleanWarehouseText($text) {
                                    if (empty($text) || $text === null) {
                                        return 'N/A';
                                    }
                                    // Eliminar tags HTML
                                    $text = strip_tags($text);
                                    // Decodificar entidades HTML
                                    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    // Eliminar caracteres problemáticos
                                    $text = preg_replace('/[<>&"\']/', '', $text);
                                    // Eliminar caracteres de control y espacios extras
                                    $text = preg_replace('/\s+/', ' ', $text);
                                    $text = trim($text);
                                    // Si queda vacío después de limpiar, retornar N/A
                                    return empty($text) ? 'N/A' : $text;
                                }
                            }
                            
                            if (!function_exists('cleanWarehouseComment')) {
                                function cleanWarehouseComment($text) {
                                    if (empty($text) || $text === null) {
                                        return 'Sin comentarios';
                                    }
                                    // Eliminar tags HTML
                                    $text = strip_tags($text);
                                    // Decodificar entidades HTML
                                    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    // Eliminar caracteres problemáticos específicos
                                    $text = preg_replace('/[<>&"\']/', '', $text);
                                    // Eliminar saltos de línea y espacios extras
                                    $text = preg_replace('/\s+/', ' ', $text);
                                    $text = trim($text);
                                    // Si queda vacío después de limpiar, retornar mensaje por defecto
                                    return empty($text) ? 'Sin comentarios' : $text;
                                }
                            }
                        @endphp
                        @if(!empty($surveyResponses) && $surveyResponses->count() > 0)
                            <div class="table-responsive">
                                <table id="warehouseSurveyTable" class="table table-bordered table-striped table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 80px;">Fecha</th>
                                            <th style="width: 100px;">Dependencia</th>
                                            <th style="width: 120px;">Experiencia</th>
                                            <th style="width: 120px;">Tiempos</th>
                                            <th style="width: 120px;">Req. Oportuno</th>
                                            <th style="width: 120px;">Disponibilidad</th>
                                            <th style="width: 120px;">Servicio Personal</th>
                                            <th style="width: 120px;">Calidad Mat.</th>
                                            <th style="width: 120px;">Cotizaciones</th>
                                            <th style="width: 120px;">Proveedores</th>
                                            <th style="width: 200px;">Comentarios</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($surveyResponses as $response)
                                        <tr>
                                            <td>{{ $response->timestamp ? $response->timestamp->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ cleanWarehouseText($response->dependencia) }}</td>
                                            <td>{{ cleanWarehouseText($response->califica_experiencia) }}</td>
                                            <td>{{ cleanWarehouseText($response->califica_tiempos) }}</td>
                                            <td>{{ cleanWarehouseText($response->requerimiento_oportuno) }}</td>
                                            <td>{{ cleanWarehouseText($response->materiales_disponibles) }}</td>
                                            <td>{{ cleanWarehouseText($response->califica_servicio_persona) }}</td>
                                            <td>{{ cleanWarehouseText($response->califica_calidad_materiales) }}</td>
                                            <td>{{ cleanWarehouseText($response->opciones_cotizaciones) }}</td>
                                            <td>{{ cleanWarehouseText($response->proveedores_cumplen) }}</td>
                                            <td>
                                                @php
                                                    $comments = [];
                                                    if (!empty($response->comentarios_disponibilidad)) {
                                                        $comments[] = 'Disp: ' . substr(cleanWarehouseComment($response->comentarios_disponibilidad), 0, 50);
                                                    }
                                                    if (!empty($response->comentarios_calidad)) {
                                                        $comments[] = 'Cal: ' . substr(cleanWarehouseComment($response->comentarios_calidad), 0, 50);
                                                    }
                                                    if (!empty($response->aspectos_destacados)) {
                                                        $comments[] = 'Dest: ' . substr(cleanWarehouseComment($response->aspectos_destacados), 0, 50);
                                                    }
                                                    $commentText = !empty($comments) ? implode(' | ', $comments) : 'Sin comentarios';
                                                @endphp
                                                <small>{{ $commentText }}</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-table fa-3x mb-3"></i>
                                <h5>No hay respuestas disponibles</h5>
                                <p>No se encontraron respuestas del cuestionario para mostrar.</p>
                                <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="btn btn-info">
                                    <i class="fas fa-upload mr-1"></i>Cargar Encuestas
                                </a>
                            </div>
                        @endif
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
                            <div class="col-md-4">
                                <div class="text-center">
                                    <a href="{{ route('surveys.internal-client.warehouse.upload') }}" 
                                       class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-upload"></i><br>
                                        Subir Nueva Encuesta
                                    </a>
                                    <small class="text-muted">Sube resultados de encuesta en Excel</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <a href="{{ route('surveys.internal-client.warehouse.export') }}" 
                                       class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-download"></i><br>
                                        Exportar Datos
                                    </a>
                                    <small class="text-muted">Descarga datos en Excel</small>
                                </div>
                            </div>
                            <div class="col-md-4">
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
        height: 280px;
        margin: 20px 0;
    }
    
    .highlight-item, .improvement-item {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .highlight-item:hover {
        background-color: #d4edda !important;
        border-left-color: #28a745;
    }
    
    .improvement-item:hover {
        background-color: #fff3cd !important;
        border-left-color: #ffc107;
    }
    
    .badge {
        font-size: 0.9em;
        padding: 6px 12px;
        border-radius: 15px;
    }
    
    .card-header {
        background: #233e6c !important;
        color: white;
        border-radius: 10px 10px 0 0;
    }
    
    .card-success .card-header {
        background: #233e6c !important;
    }
    
    .card-warning .card-header {
        background: #233e6c !important;
    }
    
    .card-primary .card-header {
        background: #233e6c !important;
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
    const historicalData = @json($historicalData ?? []);
    const dependencyData = @json($latestStats['by_dependencia'] ?? []);
    
    // Configuración global de Chart.js
    Chart.defaults.font.family = 'Arial, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#495057';
    
    // Gráfico de distribución por dependencia
    if (Object.keys(dependencyData).length > 0) {
        const depCtx = document.getElementById('departmentChart').getContext('2d');
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
    }
    
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
    
    // Inicializar DataTable para la tabla de warehouse
    try {
        if ($.fn.DataTable) {
            $('#warehouseSurveyTable').DataTable({
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                order: [[0, 'desc']], // Ordenar por fecha descendente
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                language: {
                    "decimal": "",
                    "emptyTable": "No hay datos disponibles en la tabla",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activar para ordenar la columna ascendente",
                        "sortDescending": ": activar para ordenar la columna descendente"
                    }
                },
                columnDefs: [
                    { 
                        targets: [10], // Columna de comentarios
                        orderable: false 
                    },
                    {
                        targets: '_all',
                        className: 'text-center'
                    }
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });
            
            console.log('DataTable inicializado correctamente para warehouse');
        }
    } catch (error) {
        console.error('Error al inicializar DataTable:', error);
    }

    // Tooltip para celdas truncadas
    $('#warehouseSurveyTable').on('mouseenter', 'td', function() {
        var $this = $(this);
        if (this.offsetWidth < this.scrollWidth && !$this.attr('title')) {
            $this.attr('title', $this.text());
        }
    });
    @endif
    
    // Funciones utilitarias
    function printDashboard() {
        window.print();
    }
    
    function refreshData() {
        location.reload();
    }
    
    // Función para cambiar el tipo de gráfico
    function toggleChartType() {
        if (window.trendChart) {
            const currentType = window.trendChart.config.type;
            const newType = currentType === 'line' ? 'bar' : 'line';
            
            // Actualizar configuración según el tipo
            if (newType === 'bar') {
                window.trendChart.config.data.datasets[0].backgroundColor = 'rgba(0, 123, 255, 0.6)';
                window.trendChart.config.data.datasets[0].borderColor = '#007bff';
                window.trendChart.config.data.datasets[0].borderWidth = 2;
                window.trendChart.config.data.datasets[1].backgroundColor = 'rgba(40, 167, 69, 0.6)';
                window.trendChart.config.data.datasets[1].borderColor = '#28a745';
                window.trendChart.config.data.datasets[1].borderWidth = 2;
            } else {
                window.trendChart.config.data.datasets[0].backgroundColor = 'rgba(0, 123, 255, 0.1)';
                window.trendChart.config.data.datasets[0].borderColor = '#007bff';
                window.trendChart.config.data.datasets[0].borderWidth = 3;
                window.trendChart.config.data.datasets[1].backgroundColor = 'rgba(40, 167, 69, 0.1)';
                window.trendChart.config.data.datasets[1].borderColor = '#28a745';
                window.trendChart.config.data.datasets[1].borderWidth = 3;
            }
            
            window.trendChart.config.type = newType;
            window.trendChart.update('active');
            
            // Cambiar texto del botón
            const button = document.querySelector('[onclick="toggleChartType()"]');
            if (button) {
                const icon = button.querySelector('i');
                const text = newType === 'line' ? 'Vista barras' : 'Vista líneas';
                button.innerHTML = `<i class="fas fa-exchange-alt mr-1"></i>${text}`;
            }
        }
    }
    
    console.log('Dashboard de Encuesta de Almacén cargado correctamente');
</script>
@stop
