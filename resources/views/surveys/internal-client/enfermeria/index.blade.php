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

@media (max-width: 768px) {
    .small-box {
        margin-bottom: 15px;
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
#nursingSurveyTable {
    font-size: 13px;
}

#nursingSurveyTable th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    padding: 8px 4px;
    font-size: 12px;
}

#nursingSurveyTable td {
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

#nursingSurveyTable_wrapper .row {
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
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    window.trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.labels || [],
            datasets: trendData.datasets || []
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
                    display: true,
                    position: 'top',
                    labels: {
                        padding: 15,
                        font: {
                            size: 11
                        },
                        usePointStyle: true,
                        pointStyle: 'line'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 6,
                    padding: 12,
                    callbacks: {
                        title: function(context) {
                            return `Período: ${context[0].label}`;
                        },
                        label: function(context) {
                            return `${context.dataset.label}: ${context.parsed.y}%`;
                        },
                        afterBody: function(context) {
                            if (trendData.totalResponses && trendData.totalResponses[context[0].dataIndex]) {
                                return [``, `Total respuestas: ${trendData.totalResponses[context[0].dataIndex]}`];
                            }
                            return [];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        },
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6,
                    borderWidth: 2,
                    hoverBorderWidth: 3
                },
                line: {
                    borderWidth: 3,
                    fill: true
                }
            }
        }
    });
    
    // Mostrar estadísticas mejoradas del gráfico de tendencias
    if (trendData.summary) {
        const summary = trendData.summary;
        document.getElementById('trendAvg').textContent = `Promedio: ${summary.avgSatisfaction || 0}%`;
        document.getElementById('trendMax').textContent = `Máximo: ${summary.maxSatisfaction || 0}%`;
        document.getElementById('trendMin').textContent = `Mínimo: ${summary.minSatisfaction || 0}%`;
        
        // Agregar análisis de tendencia si existe
        if (trendData.analysis && trendData.analysis.message) {
            const trendAnalysisElement = document.getElementById('trendAnalysis');
            if (trendAnalysisElement) {
                const trendClass = trendData.analysis.trend === 'improving' ? 'badge-success' : 
                                 trendData.analysis.trend === 'declining' ? 'badge-danger' : 'badge-secondary';
                trendAnalysisElement.innerHTML = `<span class="badge ${trendClass}">${trendData.analysis.message}</span>`;
            }
        }
        
        // Mostrar información adicional
        const additionalInfoElement = document.getElementById('trendAdditionalInfo');
        if (additionalInfoElement && trendData.analysis) {
            let additionalInfo = '';
            if (trendData.analysis.bestPeriod) {
                additionalInfo += `<small class="text-success">Mejor período: ${trendData.analysis.bestPeriod.period} (${trendData.analysis.bestPeriod.score}%)</small><br>`;
            }
            if (trendData.analysis.worstPeriod) {
                additionalInfo += `<small class="text-warning">Período más bajo: ${trendData.analysis.worstPeriod.period} (${trendData.analysis.worstPeriod.score}%)</small><br>`;
            }
            if (summary.totalResponsesSum) {
                additionalInfo += `<small class="text-info">Total respuestas analizadas: ${summary.totalResponsesSum}</small>`;
            }
            additionalInfoElement.innerHTML = additionalInfo;
        }
    }
    
    // Gráfico de categorías
    const categoryData = {!! json_encode($chartData ?? []) !!};
    
    // Transformar los datos para el gráfico
    const categoryLabels = [];
    const categoryValues = [];
    
    // Mapear las categorías con nombres legibles
    const categoryNames = {
        'experience': 'Experiencia con Enfermería',
        'presentation': 'Presentación Personal',
        'availability': 'Disponibilidad del Personal',
        'professionalism': 'Profesionalismo',
        'effective_response': 'Respuesta Efectiva',
        'cleanliness': 'Limpieza y Orden',
        'reports': 'Reportes Oportunos',
        'clarity': 'Claridad de Reportes'
    };
    
    // Calcular promedios por categoría
    Object.keys(categoryData).forEach(key => {
        if (categoryData[key] && categoryData[key].data && Array.isArray(categoryData[key].data)) {
            const categoryInfo = categoryData[key];
            const avg = categoryInfo.data.length > 0 ? 
                (categoryInfo.data.reduce((a, b) => a + b, 0) / categoryInfo.data.length) : 0;
            
            categoryLabels.push(categoryNames[key] || key);
            categoryValues.push(avg.toFixed(1));
        }
    });
    
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    window.categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Satisfacción (%)',
                data: categoryValues,
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545',
                    '#6f42c1', '#17a2b8', '#fd7e14', '#20c997'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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
                    }
                }
            }
        }
    });
    
    // Gráfico de dependencias
    const departmentData = {!! json_encode($dependenciesData ?? []) !!};
    const depLabels = departmentData.map(item => item.dependencia);
    const depValues = departmentData.map(item => item.total);
    
    const depCtx = document.getElementById('departmentChart').getContext('2d');
    window.departmentChart = new Chart(depCtx, {
        type: 'doughnut',
        data: {
            labels: depLabels,
            datasets: [{
                data: depValues,
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545',
                    '#6f42c1', '#17a2b8', '#fd7e14', '#20c997',
                    '#6c757d', '#343a40'
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 10
                        },
                        padding: 8,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 4,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 300
            }
        }
    });
});

function toggleChartAnimation(chartId) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        chart.update('active');
    }
}
</script>

<!-- DataTables JavaScript -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Verificar que jQuery y DataTables estén cargados
    if (typeof $ === 'undefined') {
        console.error('jQuery no está cargado');
        return;
    }
    
    if (!$.fn.DataTable) {
        console.error('DataTables no está cargado');
        return;
    }
    
    // Verificar que la tabla existe
    var table = $('#nursingSurveyTable');
    if (table.length === 0) {
        console.error('Tabla #nursingSurveyTable no encontrada');
        return;
    }
    
    console.log('Inicializando DataTable...');
    
    // Inicializar DataTable
    try {
        $('#nursingSurveyTable').DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            order: [[0, 'desc']], // Ordenar por fecha descendente
            columnDefs: [
                {
                    targets: [0], // Columna de fecha
                    type: 'date',
                    render: function(data, type, row) {
                        return data || 'N/A';
                    }
                },
                {
                    targets: [1], // Columna de dependencia
                    searchable: true
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
        
        console.log('DataTable inicializado correctamente');
        
    } catch (error) {
        console.error('Error al inicializar DataTable:', error);
        // Si DataTable falla, al menos mantener la tabla básica funcional
        console.log('Aplicando estilos básicos a la tabla...');
    }

    // Tooltip para celdas truncadas
    $('#nursingSurveyTable').on('mouseenter', 'td', function() {
        var $this = $(this);
        if (this.offsetWidth < this.scrollWidth && !$this.attr('title')) {
            $this.attr('title', $this.text());
        }
    });
});
</script>
@endpush

@section('title', 'Encuesta Enfermería')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-nurse mr-2"></i>Dashboard de Enfermería</h1>
        <div>
            <a href="{{ route('surveys.internal-client.enfermeria.upload') }}" class="btn btn-success">
                <i class="fas fa-upload mr-1"></i>Subir Encuesta
            </a>
            @if($selectedPeriod ?? null)
                <a href="{{ route('surveys.internal-client.enfermeria.export', ['period' => $selectedPeriod]) }}" class="btn btn-info">
                    <i class="fas fa-download mr-1"></i>Exportar
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alert de información -->
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-info"></i> Información</h5>
        Este dashboard presenta el análisis estadístico de la satisfacción del personal interno con los servicios del área de Enfermería.
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
                    <h3>
                        @if(($totalResponses ?? 0) > 0 && !empty($chartData))
                            @php
                                $scores = [
                                    'Excelente' => 100,
                                    'Muy buena' => 85,
                                    'Buena' => 70,
                                    'Regular' => 50,
                                    'Mala' => 25,
                                    'Muy mala' => 10,
                                    'Sí' => 100,
                                    'No' => 0
                                ];
                                
                                $allCategoryPercentages = [];
                                foreach($chartData as $category) {
                                    if(isset($category['data']) && is_array($category['data']) && count($category['data']) > 0) {
                                        $totalCount = array_sum($category['data']);
                                        $weightedScore = 0;
                                        
                                        if($totalCount > 0) {
                                            for($i = 0; $i < count($category['labels']); $i++) {
                                                $label = $category['labels'][$i] ?? '';
                                                $count = $category['data'][$i] ?? 0;
                                                $score = $scores[$label] ?? 50;
                                                $weightedScore += ($score * $count);
                                            }
                                            $categoryPercentage = $weightedScore / $totalCount;
                                            $allCategoryPercentages[] = $categoryPercentage;
                                        }
                                    }
                                }
                                $generalAverage = count($allCategoryPercentages) > 0 ? collect($allCategoryPercentages)->avg() : 0;
                            @endphp
                            {{ number_format($generalAverage, 1) }}<sup style="font-size: 20px">%</sup>
                        @else
                            0<sup style="font-size: 20px">%</sup>
                        @endif
                    </h3>
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
                    <h3>{{ count($dependenciesData ?? []) }}</h3>
                    <p>Dependencias Evaluadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="small-box-footer">
                    @if(isset($dependenciesData) && count($dependenciesData) > 0)
                        @foreach($dependenciesData as $dependency)
                            <span class="badge badge-light mr-1">{{ $dependency->dependencia }}</span>
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
                <a href="{{ route('surveys.internal-client.enfermeria.upload') }}" class="small-box-footer">
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
                                'experience' => 'Experiencia con Enfermería',
                                'presentation' => 'Presentación Personal',
                                'availability' => 'Disponibilidad del Personal',
                                'professionalism' => 'Profesionalismo',
                                'effective_response' => 'Respuesta Efectiva',
                                'cleanliness' => 'Limpieza y Orden',
                                'reports' => 'Reportes Oportunos',
                                'clarity' => 'Claridad de Reportes'
                            ];
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark', 'light'];
                            $icons = ['fas fa-user-nurse', 'fas fa-stethoscope', 'fas fa-pills', 'fas fa-heartbeat', 'fas fa-hospital', 'fas fa-plus-square', 'fas fa-notes-medical', 'fas fa-clipboard-check'];
                            $colorIndex = 0;
                        @endphp
                        
                        @foreach($chartData ?? [] as $key => $category)
                        @if(isset($category['data']) && is_array($category['data']) && count($category['data']) > 0)
                        @php
                            // Mapear los datos a sus puntuaciones
                            $scores = [
                                'Excelente' => 100,
                                'Muy buena' => 85,
                                'Buena' => 70,
                                'Regular' => 50,
                                'Mala' => 25,
                                'Muy mala' => 10,
                                'Sí' => 100,
                                'No' => 0
                            ];
                            
                            // Calcular el porcentaje basado en las etiquetas y datos
                            $totalCount = array_sum($category['data']);
                            $weightedScore = 0;
                            
                            if($totalCount > 0) {
                                for($i = 0; $i < count($category['labels']); $i++) {
                                    $label = $category['labels'][$i] ?? '';
                                    $count = $category['data'][$i] ?? 0;
                                    $score = $scores[$label] ?? 50; // Default a Regular si no se encuentra
                                    $weightedScore += ($score * $count);
                                }
                                $percentage = round($weightedScore / $totalCount, 1);
                            } else {
                                $percentage = 0;
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
                                    <span class="info-box-number">{{ $percentage }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $currentColor }}" 
                                             style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Promedio: {{ number_format($percentage/20, 1) }}/5.0 ({{ $totalCount }} evaluaciones)
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                        
                        @if(empty($chartData) || collect($chartData)->every(function($category) { return empty($category['data'] ?? []); }))
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <h4>No hay datos disponibles</h4>
                                <p>No se encontraron datos para mostrar. Por favor, cargue algunas encuestas primero.</p>
                                <a href="{{ route('surveys.internal-client.enfermeria.upload') }}" class="btn btn-warning">
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
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2"></i>
                        Evolución de Satisfacción (Últimos 6 meses)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendChart" width="600" height="280"></canvas>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="chart-info">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Datos de los últimos 6 períodos
                            </small>
                            <div class="chart-stats mt-1">
                                <span class="badge badge-primary" id="trendAvg">Promedio: --</span>
                                <span class="badge badge-success" id="trendMax">Máximo: --</span>
                                <span class="badge badge-warning" id="trendMin">Mínimo: --</span>
                            </div>
                            <div class="trend-analysis mt-2" id="trendAnalysis">
                                <!-- Análisis de tendencia se mostrará aquí -->
                            </div>
                            <div class="trend-additional-info mt-2" id="trendAdditionalInfo">
                                <!-- Información adicional se mostrará aquí -->
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
                        Comparación por Categorías (Promedio General)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" id="categoryFullscreen" title="Pantalla completa">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart" width="600" height="280"></canvas>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Porcentaje de satisfacción por área
                        </small>
                        <button class="btn btn-sm btn-outline-info" onclick="toggleChartAnimation('categoryChart')">
                            <i class="fas fa-play mr-1"></i>
                            Animar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución por Dependencias -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-building mr-2"></i>
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
                        <canvas id="departmentChart" width="400" height="220"></canvas>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="chart-info">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ count($dependenciesData ?? []) }} dependencias participantes
                            </small>
                            <div class="chart-stats mt-1">
                                @php
                                    $totalResponses = collect($dependenciesData ?? [])->sum('total');
                                    $avgResponses = count($dependenciesData ?? []) > 0 ? $totalResponses / count($dependenciesData ?? []) : 0;
                                @endphp
                                <span class="badge badge-secondary" id="departmentTotal">Total: {{ $totalResponses }}</span>
                                <span class="badge badge-info" id="departmentAvg">Promedio: {{ number_format($avgResponses, 1) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        Aspectos Destacados
                    </h3>
                </div>
                <div class="card-body">
                    @if(!empty($chartData))
                        @php
                            $categoryNames = [
                                'experience' => 'Experiencia con Enfermería',
                                'presentation' => 'Presentación Personal',
                                'availability' => 'Disponibilidad del Personal',
                                'professionalism' => 'Profesionalismo',
                                'effective_response' => 'Respuesta Efectiva',
                                'cleanliness' => 'Limpieza y Orden',
                                'reports' => 'Reportes Oportunos',
                                'clarity' => 'Claridad de Reportes'
                            ];
                            
                            $bestCategories = [];
                            
                            foreach($chartData as $key => $category) {
                                if(isset($category['data']) && is_array($category['data']) && count($category['data']) > 0) {
                                    $avg = collect($category['data'])->avg();
                                    $bestCategories[] = [
                                        'key' => $key, 
                                        'name' => $categoryNames[$key] ?? ucfirst($key), 
                                        'avg' => $avg,
                                        'count' => count($category['data'])
                                    ];
                                }
                            }
                            
                            // Ordenar por promedio descendente y tomar los primeros 5
                            $bestCategories = collect($bestCategories)->sortByDesc('avg')->take(5);
                        @endphp
                        @foreach($bestCategories as $category)
                            <div class="d-flex justify-content-between align-items-start mb-3 p-2 border-bottom">
                                <div class="flex-grow-1">
                                    {{ $category['name'] }}
                                    <br>
                                    <small class="text-muted">{{ $category['count'] }} {{ $category['count'] == 1 ? 'evaluación' : 'evaluaciones' }}</small>
                                </div>
                                <span class="badge badge-success ml-2">{{ round($category['avg'], 1) }}%</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle mb-2"></i>
                            <p>No hay datos para mostrar aspectos destacados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Oportunidades de Mejora
                    </h3>
                </div>
                <div class="card-body">
                    @if(!empty($chartData))
                        @php
                            $categoryNames = [
                                'experience' => 'Experiencia con Enfermería',
                                'presentation' => 'Presentación Personal',
                                'availability' => 'Disponibilidad del Personal',
                                'professionalism' => 'Profesionalismo',
                                'effective_response' => 'Respuesta Efectiva',
                                'cleanliness' => 'Limpieza y Orden',
                                'reports' => 'Reportes Oportunos',
                                'clarity' => 'Claridad de Reportes'
                            ];
                            
                            $worstCategories = [];
                            
                            foreach($chartData as $key => $category) {
                                if(isset($category['data']) && is_array($category['data']) && count($category['data']) > 0) {
                                    $avg = collect($category['data'])->avg();
                                    
                                    // Obtener dependencias que evaluaron esta categoría
                                    $categoryDependencies = [];
                                    if(isset($category['dependencies']) && is_array($category['dependencies'])) {
                                        foreach($category['dependencies'] as $deps) {
                                            if(is_array($deps)) {
                                                $categoryDependencies = array_merge($categoryDependencies, $deps);
                                            }
                                        }
                                        $categoryDependencies = array_unique($categoryDependencies);
                                    }
                                    
                                    $worstCategories[] = [
                                        'key' => $key, 
                                        'name' => $categoryNames[$key] ?? ucfirst($key), 
                                        'avg' => $avg,
                                        'count' => count($category['data']),
                                        'dependencies' => $categoryDependencies
                                    ];
                                }
                            }
                            
                            // Ordenar por promedio ascendente y tomar los primeros 5
                            $worstCategories = collect($worstCategories)->sortBy('avg')->take(5);
                        @endphp
                        @foreach($worstCategories as $category)
                            <div class="d-flex justify-content-between align-items-start mb-3 p-2 border-bottom">
                                <div class="flex-grow-1">
                                    {{ $category['name'] }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $category['count'] }} {{ $category['count'] == 1 ? 'evaluación' : 'evaluaciones' }}
                                        @if(!empty($category['dependencies']))
                                            <br>
                                            <i class="fas fa-building mr-1"></i>
                                            @php
                                                $deps = $category['dependencies'];
                                                $displayDeps = array_slice($deps, 0, 3);
                                                $remainingCount = count($deps) - 3;
                                            @endphp
                                            {{ implode(', ', $displayDeps) }}
                                            @if($remainingCount > 0)
                                                <span class="badge badge-secondary badge-sm ml-1">+{{ $remainingCount }}</span>
                                            @endif
                                        @endif
                                    </small>
                                </div>
                                <span class="badge badge-warning ml-2">{{ round($category['avg'], 1) }}%</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle mb-2"></i>
                            <p>No hay datos para identificar áreas de mejora</p>
                        </div>
                    @endif
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
                        Respuestas Detalladas del Cuestionario de Enfermería
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        // Funciones helper para limpiar texto - definidas una sola vez
                        if (!function_exists('cleanNursingText')) {
                            function cleanNursingText($text) {
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
                        
                        if (!function_exists('cleanNursingComment')) {
                            function cleanNursingComment($text) {
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
                    @if(!empty($responses) && $responses->count() > 0)
                        <div class="table-responsive">
                            <table id="nursingSurveyTable" class="table table-bordered table-striped table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 80px;">Fecha</th>
                                        <th style="width: 100px;">Dependencia</th>
                                        <th style="width: 150px;">Experiencia</th>
                                        <th style="width: 150px;">Presentación</th>
                                        <th style="width: 150px;">Disponibilidad</th>
                                        <th style="width: 150px;">Profesionalismo</th>
                                        <th style="width: 150px;">Respuesta Efectiva</th>
                                        <th style="width: 150px;">Limpieza</th>
                                        <th style="width: 150px;">Reportes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($responses as $response)
                                    <tr>
                                        <td>{{ $response->timestamp ? $response->timestamp->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ cleanNursingText($response->dependencia) }}</td>
                                        <td>{{ cleanNursingText($response->experiencia_enfermeria) }}</td>
                                        <td>{{ cleanNursingText($response->presentacion_personal) }}</td>
                                        <td>{{ cleanNursingText($response->disponibilidad_personal) }}</td>
                                        <td>{{ cleanNursingText($response->profesionalismo) }}</td>
                                        <td>{{ cleanNursingText($response->respuesta_efectiva) }}</td>
                                        <td>{{ cleanNursingText($response->limpieza_orden) }}</td>
                                        <td>{{ cleanNursingText($response->reportes_oportunos) }}</td>
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
                            <a href="{{ route('surveys.internal-client.enfermeria.upload') }}" class="btn btn-info">
                                <i class="fas fa-upload mr-1"></i>Cargar Encuestas
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('surveys.internal-client.enfermeria.upload') }}" class="btn btn-success btn-block">
                                <i class="fas fa-upload"></i>
                                Cargar Nueva Encuesta
                            </a>
                        </div>
                        <div class="col-md-4">
                            @if($selectedPeriod ?? null)
                                <a href="{{ route('surveys.internal-client.enfermeria.export', ['period' => $selectedPeriod]) }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-download"></i>
                                    Exportar Datos
                                </a>
                            @else
                                <button class="btn btn-secondary btn-block" disabled>
                                    <i class="fas fa-download"></i>
                                    Sin Datos para Exportar
                                </button>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-warning btn-block" onclick="location.reload()">
                                <i class="fas fa-sync"></i>
                                Actualizar Dashboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Áreas de Mejora -->
<div class="modal fade" id="improvementModal" tabindex="-1" role="dialog" aria-labelledby="improvementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="improvementModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Áreas de Mejora Detalladas - Enfermería
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(!empty($chartData))
                    @php
                        $categoryNames = [
                            'experience' => 'Experiencia con Enfermería',
                            'presentation' => 'Presentación Personal',
                            'availability' => 'Disponibilidad del Personal',
                            'professionalism' => 'Profesionalismo',
                            'effective_response' => 'Respuesta Efectiva',
                            'cleanliness' => 'Limpieza y Orden',
                            'reports' => 'Reportes Oportunos',
                            'clarity' => 'Claridad de Reportes'
                        ];
                        
                        // Ordenar categorías por puntuación (de menor a mayor)
                        $sortedCategories = [];
                        foreach($chartData as $key => $category) {
                            if(isset($category['data']) && is_array($category['data']) && count($category['data']) > 0) {
                                $avg = collect($category['data'])->avg();
                                $sortedCategories[] = [
                                    'key' => $key,
                                    'name' => $categoryNames[$key] ?? ucfirst($key),
                                    'avg' => $avg,
                                    'count' => count($category['data']),
                                    'data' => $category['data']
                                ];
                            }
                        }
                        
                        // Ordenar por promedio ascendente (peores primero)
                        usort($sortedCategories, function($a, $b) {
                            return $a['avg'] <=> $b['avg'];
                        });
                    @endphp
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Análisis de Áreas de Mejora</strong><br>
                        Las siguientes categorías están ordenadas por prioridad de atención, comenzando por las que requieren mayor mejora.
                        <br><small class="text-muted">Período evaluado: {{ $selectedPeriod ?? 'Sin período' }}</small>
                    </div>
                    
                    <div class="row">
                        @foreach($sortedCategories as $index => $category)
                            @php
                                $percentage = round($category['avg'], 1);
                                $priority = $percentage < 60 ? 'Alta' : ($percentage < 80 ? 'Media' : 'Baja');
                                $priorityColor = $percentage < 60 ? 'danger' : ($percentage < 80 ? 'warning' : 'success');
                                $priorityIcon = $percentage < 60 ? 'fas fa-exclamation-triangle' : ($percentage < 80 ? 'fas fa-exclamation' : 'fas fa-check-circle');
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card border-{{ $priorityColor }}">
                                    <div class="card-header bg-{{ $priorityColor }} text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="{{ $priorityIcon }} mr-2"></i>
                                            {{ $category['name'] }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Puntuación Actual</small>
                                                <h4 class="text-{{ $priorityColor }}">{{ $percentage }}%</h4>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Prioridad</small>
                                                <h5><span class="badge badge-{{ $priorityColor }}">{{ $priority }}</span></h5>
                                            </div>
                                        </div>
                                        
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar bg-{{ $priorityColor }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        
                                        <div class="improvement-details">
                                            <small class="text-muted">
                                                <i class="fas fa-users mr-1"></i>{{ $category['count'] }} evaluaciones
                                                <br>
                                                <i class="fas fa-target mr-1"></i>Meta: 80%+
                                                @if($percentage < 80)
                                                    <br><i class="fas fa-arrow-up mr-1"></i>Mejora requerida: {{ round(80 - $percentage, 1) }}%
                                                @endif
                                            </small>
                                        </div>
                                        
                                        @if($percentage < 80)
                                        <div class="mt-2">
                                            <small class="text-{{ $priorityColor }} font-weight-bold">
                                                <i class="fas fa-lightbulb mr-1"></i>
                                                Recomendación: {{ $priority == 'Alta' ? 'Requiere acción inmediata' : ($priority == 'Media' ? 'Implementar mejoras graduales' : 'Mantener y optimizar') }}
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-3">
                        <h6><i class="fas fa-chart-line mr-2"></i>Resumen de Prioridades</h6>
                        <div class="row">
                            @php
                                $highPriority = collect($sortedCategories)->where('avg', '<', 60)->count();
                                $mediumPriority = collect($sortedCategories)->where('avg', '>=', 60)->where('avg', '<', 80)->count();
                                $lowPriority = collect($sortedCategories)->where('avg', '>=', 80)->count();
                            @endphp
                            <div class="col-4 text-center">
                                <h4 class="text-danger">{{ $highPriority }}</h4>
                                <small class="text-muted">Alta Prioridad</small>
                            </div>
                            <div class="col-4 text-center">
                                <h4 class="text-warning">{{ $mediumPriority }}</h4>
                                <small class="text-muted">Media Prioridad</small>
                            </div>
                            <div class="col-4 text-center">
                                <h4 class="text-success">{{ $lowPriority }}</h4>
                                <small class="text-muted">Baja Prioridad</small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h5>No hay datos disponibles</h5>
                        <p>No se encontraron datos para analizar áreas de mejora.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cerrar
                </button>
                <a href="{{ route('surveys.internal-client.enfermeria.results') }}" class="btn btn-warning">
                    <i class="fas fa-external-link-alt mr-1"></i>Ver Resultados Completos
                </a>
                @if($selectedPeriod ?? null)
                <a href="{{ route('surveys.internal-client.enfermeria.export', ['period' => $selectedPeriod]) }}" class="btn btn-success">
                    <i class="fas fa-download mr-1"></i>Exportar Datos
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .bg-primary {
        background-color: #233e6c !important;
    }
    
    .bg-gradient-info {
        background: #233e6c !important;
    }
    
    .bg-secondary {
        background-color: #233e6c !important;
    }
    
    .bg-success {
        background-color: #233e6c !important;
    }
    
    .bg-warning {
        background-color: #233e6c !important;
    }
    
    .bg-info {
        background-color: #233e6c !important;
    }
    
    .card-header {
        background-color: #233e6c !important;
        color: white !important;
    }
    
    /* Excepciones para mantener colores originales en small-box */
    .small-box.bg-info {
        background-color: #17a2b8 !important;
    }
    
    .small-box.bg-success {
        background-color: #28a745 !important;
    }
    
    .small-box.bg-warning {
        background-color: #ffc107 !important;
    }
    
    .small-box.bg-danger {
        background-color: #dc3545 !important;
    }
    
    /* Hacer que los iconos en info-box-icon tengan color blanco */
    .info-box-icon i {
        color: white !important;
    }
</style>
@stop