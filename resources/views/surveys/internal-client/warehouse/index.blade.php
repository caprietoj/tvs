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

.alert-sm {
    padding: 8px 12px;
    margin-bottom: 8px;
    font-size: 0.875rem;
}

.badge-lg {
    padding: 8px 12px;
    font-size: 0.875rem;
}

/* Estilos para paginadores */
.highlight-item, .improvement-item {
    transition: opacity 0.3s ease-in-out;
}

.pagination-controls {
    background-color: rgba(0,0,0,0.05);
    border-radius: 8px;
    padding: 8px 12px;
    margin-top: 15px;
}

.btn-outline-success:disabled,
.btn-outline-warning:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-outline-success:hover:not(:disabled),
.btn-outline-warning:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .small-box {
        margin-bottom: 15px;
    }
    
    .pagination-controls .d-flex {
        flex-direction: column;
        gap: 8px;
    }
    
    .pagination-controls .btn {
        font-size: 0.8rem;
        padding: 4px 8px;
    }
}

/* Estilos para DataTables */
#surveyResponsesTable {
    font-size: 13px;
}

#surveyResponsesTable th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    padding: 8px 4px;
    font-size: 12px;
}

#surveyResponsesTable td {
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

#surveyResponsesTable_wrapper .row {
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

    // Gráfico de tendencia
    const trendData = {!! json_encode($dashboardData['trend_data'] ?? ['labels' => [], 'values' => []]) !!};
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    window.trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Satisfacción General (%)',
                data: trendData.values,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.1,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: '#0056b3',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        padding: 10,
                        font: {
                            size: 12
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
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
    
    // Calcular y mostrar estadísticas del gráfico de tendencia
    if (trendData.values && trendData.values.length > 0) {
        const values = trendData.values.filter(val => val !== null && val !== undefined);
        if (values.length > 0) {
            const avg = values.reduce((a, b) => a + b, 0) / values.length;
            const max = Math.max(...values);
            const min = Math.min(...values);
            
            document.getElementById('trendAvg').textContent = `Promedio: ${avg.toFixed(1)}%`;
            document.getElementById('trendMax').textContent = `Máximo: ${max.toFixed(1)}%`;
            document.getElementById('trendMin').textContent = `Mínimo: ${min.toFixed(1)}%`;
        }
    }
    
    // Gráfico de categorías
    const categoryData = {!! json_encode($chartData ?? []) !!};
    const categoryLabels = categoryData.map(item => item.name);
    const categoryValues = categoryData.map(item => {
        // Usar el promedio ya calculado y convertir a porcentaje
        const avg = (item.average || 0) * 25;
        return avg.toFixed(1);
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
    
    // Verificar si hay datos
    if (Object.keys(departmentData).length === 0) {
        console.log('No hay datos de dependencias disponibles');
        // Ocultar el canvas y mostrar mensaje
        const depCanvas = document.getElementById('departmentChart');
        const depContainer = depCanvas.parentElement;
        depContainer.innerHTML = '<div class="text-center text-muted"><i class="fas fa-info-circle fa-2x mb-3"></i><p>No hay datos de dependencias disponibles</p></div>';
    } else {
        // Extraer solo los conteos (no la satisfacción)
        const depLabels = Object.keys(departmentData);
        const depValues = Object.values(departmentData).map(item => {
            // Si el valor es un objeto con 'count', usar eso; si no, usar el valor directamente
            return typeof item === 'object' && item.count ? item.count : (typeof item === 'number' ? item : 0);
        });
        
        console.log('Datos de dependencias:', { labels: depLabels, values: depValues });
    
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
                                return `${context.label}: ${value} respuestas (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    duration: 300
                }
            }
        });
    }
});

function toggleChartAnimation(chartId) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        chart.update('active');
    }
}

// Variables para paginación
let highlightsPage = 0;
let issuesPage = 0;
const itemsPerPage = 3;

// Función para navegar entre aspectos destacados
function navigateHighlights(direction) {
    if (!window.highlightsData) return;
    
    const totalItems = window.highlightsData.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    highlightsPage += direction;
    
    // Verificar límites
    if (highlightsPage < 0) highlightsPage = 0;
    if (highlightsPage >= totalPages) highlightsPage = totalPages - 1;
    
    updateHighlightsDisplay();
}

// Función para navegar entre áreas de mejora
function navigateIssues(direction) {
    if (!window.issuesData) return;
    
    const totalItems = window.issuesData.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    issuesPage += direction;
    
    // Verificar límites
    if (issuesPage < 0) issuesPage = 0;
    if (issuesPage >= totalPages) issuesPage = totalPages - 1;
    
    updateIssuesDisplay();
}

// Actualizar la visualización de aspectos destacados
function updateHighlightsDisplay() {
    if (!window.highlightsData) return;
    
    const container = document.getElementById('highlights-container');
    const startIndex = highlightsPage * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, window.highlightsData.length);
    const pageData = window.highlightsData.slice(startIndex, endIndex);
    
    // Limpiar contenedor
    container.innerHTML = '';
    
    // Agregar elementos
    pageData.forEach((highlight, index) => {
        const globalIndex = startIndex + index;
        const itemHTML = `
            <div class="highlight-item" data-highlight-index="${globalIndex}">
                <div class="highlight-icon">
                    <i class="fas fa-star text-warning"></i>
                </div>
                <div class="highlight-content">
                    <h5>Aspecto Destacado ${globalIndex + 1}</h5>
                    <p class="text-muted mb-1">"${highlight.text}"</p>
                    <small class="text-success">
                        <i class="fas fa-users mr-1"></i>
                        Mencionado ${highlight.count} vez${highlight.count > 1 ? 'es' : ''}
                    </small>
                </div>
            </div>
            ${index < pageData.length - 1 ? '<hr class="my-2">' : ''}
        `;
        container.innerHTML += itemHTML;
    });
    
    // Actualizar paginador
    const totalPages = Math.ceil(window.highlightsData.length / itemsPerPage);
    document.getElementById('highlights-current').textContent = `${startIndex + 1}-${endIndex}`;
    document.getElementById('prev-highlights').disabled = highlightsPage === 0;
    document.getElementById('next-highlights').disabled = highlightsPage === totalPages - 1;
}

// Actualizar la visualización de áreas de mejora
function updateIssuesDisplay() {
    if (!window.issuesData) return;
    
    const container = document.getElementById('issues-container');
    const startIndex = issuesPage * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, window.issuesData.length);
    const pageData = window.issuesData.slice(startIndex, endIndex);
    
    // Limpiar contenedor
    container.innerHTML = '';
    
    // Agregar elementos
    pageData.forEach((issue, index) => {
        const globalIndex = startIndex + index;
        const itemHTML = `
            <div class="improvement-item" data-issue-index="${globalIndex}">
                <div class="improvement-icon">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                </div>
                <div class="improvement-content">
                    <h5>Área de Mejora ${globalIndex + 1}</h5>
                    <p class="text-muted mb-1">"${issue.text}"</p>
                    <small class="text-warning">
                        <i class="fas fa-users mr-1"></i>
                        Mencionado ${issue.count} vez${issue.count > 1 ? 'es' : ''}
                    </small>
                </div>
            </div>
            ${index < pageData.length - 1 ? '<hr class="my-2">' : ''}
        `;
        container.innerHTML += itemHTML;
    });
    
    // Actualizar paginador
    const totalPages = Math.ceil(window.issuesData.length / itemsPerPage);
    document.getElementById('issues-current').textContent = `${startIndex + 1}-${endIndex}`;
    document.getElementById('prev-issues').disabled = issuesPage === 0;
    document.getElementById('next-issues').disabled = issuesPage === totalPages - 1;
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
    // Inicializar DataTable
    $('#surveyResponsesTable').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
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

    // Tooltip para celdas truncadas
    $('#surveyResponsesTable').on('mouseenter', 'td', function() {
        var $this = $(this);
        if (this.offsetWidth < this.scrollWidth && !$this.attr('title')) {
            $this.attr('title', $this.text());
        }
    });
});
</script>
@endpush

@section('title', 'Encuesta Almacén')

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
    <!-- Alert de información -->
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-info"></i> Información del Dashboard</h5>
        Este dashboard presenta el análisis estadístico de la satisfacción del personal interno con los servicios del área de Almacén.
        <br><strong>Último período evaluado:</strong> {{ $selectedPeriod ?? 'Sin datos' }}
        <br><strong>Sistema de calificación:</strong> 
        <span class="badge badge-danger">25% = Deficiente</span>
        <span class="badge badge-warning">50% = Regular</span>
        <span class="badge badge-info">75% = Bueno</span>
        <span class="badge badge-success">100% = Excelente</span>
        @if(isset($latestStats['satisfaction_average']) && $latestStats['satisfaction_average'] <= 50)
            <br><div class="mt-2">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <strong>Nota:</strong> El nivel actual de satisfacción ({{ number_format($latestStats['satisfaction_average'], 1) }}%) indica áreas importantes de mejora.
            </div>
        @endif
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
                        @if(($totalResponses ?? 0) > 0 && isset($latestStats['satisfaction_average']))
                            {{ number_format($latestStats['satisfaction_average'], 1) }}<sup style="font-size: 20px">%</sup>
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
                    @if(!empty($dependenciesData))
                        @php
                            // Buscar dependencias con mejor y peor satisfacción
                            $bestDep = null;
                            $worstDep = null;
                            $bestSatisfaction = -1;
                            $worstSatisfaction = 999;
                            
                            foreach($dependenciesData as $depName => $data) {
                                $satisfaction = is_array($data) && isset($data['satisfaction']) ? $data['satisfaction'] : 0;
                                
                                if ($satisfaction > $bestSatisfaction) {
                                    $bestSatisfaction = $satisfaction;
                                    $bestDep = $depName;
                                }
                                
                                if ($satisfaction < $worstSatisfaction) {
                                    $worstSatisfaction = $satisfaction;
                                    $worstDep = $depName;
                                }
                            }
                        @endphp
                        @if($bestDep && $worstDep)
                            <small class="text-white">
                                <strong>Mejor:</strong> {{ $bestDep }}<br>
                                <strong>Menor:</strong> {{ $worstDep }}
                            </small>
                        @endif
                    @endif
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="small-box-footer">
                    &nbsp;
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
                        @foreach($chartData ?? [] as $category)
                        @php
                            // Usar el promedio ya calculado en el controlador
                            $average = $category['average'] ?? 0;
                            $percentage = round($average * 25, 1); // Convertir de escala 1-4 a porcentaje
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
                            $icons = ['fas fa-boxes', 'fas fa-truck', 'fas fa-clipboard-list', 'fas fa-clock', 'fas fa-users', 'fas fa-tools', 'fas fa-chart-bar'];
                            $colorIndex = $loop->index % count($colors);
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ $colors[$colorIndex] }}">
                                    <i class="{{ $icons[$colorIndex] ?? 'fas fa-chart-bar' }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ $category['name'] }}</span>
                                    <span class="info-box-number">{{ $percentage }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $colors[$colorIndex] }}" 
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Promedio: {{ number_format($average, 1) }}/4.0 ({{ array_sum($category['data'] ?? []) }} evaluaciones)
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        @if(empty($chartData))
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
                                    $totalResponses = 0;
                                    $depCount = 0;
                                    foreach($dependenciesData ?? [] as $dep => $data) {
                                        if (is_array($data) && isset($data['count'])) {
                                            $totalResponses += $data['count'];
                                            $depCount++;
                                        } elseif (is_numeric($data)) {
                                            $totalResponses += $data;
                                            $depCount++;
                                        }
                                    }
                                    $avgResponses = $depCount > 0 ? number_format($totalResponses / $depCount, 1) : 0;
                                @endphp
                                <span class="badge badge-secondary" id="departmentTotal">Total: {{ $totalResponses }}</span>
                                <span class="badge badge-info" id="departmentAvg">Promedio: {{ $avgResponses }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card highlights-card">
                <div class="card-header bg-gradient-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        Aspectos Más Destacados
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(!empty($chartData))
                        @php
                            // Obtener aspectos destacados de los datos reales
                            $highlights = $latestStats['top_highlights'] ?? [];
                            $bestCategory = collect($chartData)->sortByDesc(function($category) {
                                return $category['average'] ?? 0;
                            })->first();
                            $bestAvg = ($bestCategory['average'] ?? 0) * 25;
                        @endphp
                        
                        @if(!empty($highlights))
                            <!-- Aspectos destacados de las encuestas -->
                            <div id="highlights-container">
                                @foreach(array_slice($highlights, 0, 3) as $index => $highlight)
                                <div class="highlight-item" data-highlight-index="{{ $index }}">
                                    <div class="highlight-icon">
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <div class="highlight-content">
                                        <h5>Aspecto Destacado {{ $index + 1 }}</h5>
                                        <p class="text-muted mb-1">"{{ $highlight['text'] }}"</p>
                                        <small class="text-success">
                                            <i class="fas fa-users mr-1"></i>
                                            Mencionado {{ $highlight['count'] }} vez{{ $highlight['count'] > 1 ? 'es' : '' }}
                                        </small>
                                    </div>
                                </div>
                                @if(!$loop->last)<hr class="my-2">@endif
                                @endforeach
                            </div>
                            
                            @if(count($highlights) > 3)
                            <!-- Paginador para aspectos destacados -->
                            <div class="pagination-controls" id="highlights-pagination">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-sm btn-outline-success" id="prev-highlights" onclick="navigateHighlights(-1)" disabled>
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </button>
                                    <small class="text-muted font-weight-bold">
                                        <span id="highlights-current">1-3</span> de {{ count($highlights) }} aspectos
                                    </small>
                                    <button class="btn btn-sm btn-outline-success" id="next-highlights" onclick="navigateHighlights(1)">
                                        Siguiente <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Datos para JavaScript -->
                            <script type="text/javascript">
                                window.highlightsData = {!! json_encode($highlights) !!};
                            </script>
                            @endif
                        @else
                            <!-- Mostrar mejor categoría si no hay aspectos destacados específicos -->
                            <div class="highlight-item">
                                <div class="highlight-icon">
                                    <i class="fas fa-trophy text-warning"></i>
                                </div>
                                <div class="highlight-content">
                                    <h5>{{ $bestCategory['name'] ?? 'N/A' }}</h5>
                                    <p class="text-muted mb-1">Categoría con mejor desempeño</p>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" style="width: {{ round($bestAvg, 1) }}%"></div>
                                    </div>
                                    <small class="text-success font-weight-bold">{{ round($bestAvg, 1) }}% de satisfacción</small>
                                </div>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="text-center">
                                <div class="alert alert-info alert-sm mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Sin comentarios específicos</strong>
                                </div>
                                <small class="text-muted">No se encontraron aspectos destacados específicos en las respuestas</small>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No hay datos para mostrar aspectos destacados.</p>
                            <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-upload mr-1"></i>Cargar Primera Encuesta
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-gradient-warning text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Áreas de Mejora
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(!empty($chartData))
                        @php
                            // Obtener oportunidades de mejora de los datos reales
                            $issues = $latestStats['top_issues'] ?? [];
                            $worstCategory = collect($chartData)->sortBy(function($category) {
                                return $category['average'] ?? 0;
                            })->first();
                            $worstAvg = ($worstCategory['average'] ?? 0) * 25;
                            $generalSatisfaction = $latestStats['satisfaction_average'] ?? 0;
                        @endphp
                        
                        @if(!empty($issues))
                            <!-- Oportunidades de mejora de las encuestas -->
                            <div id="issues-container">
                                @foreach(array_slice($issues, 0, 3) as $index => $issue)
                                <div class="improvement-item" data-issue-index="{{ $index }}">
                                    <div class="improvement-icon">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    </div>
                                    <div class="improvement-content">
                                        <h5>Área de Mejora {{ $index + 1 }}</h5>
                                        <p class="text-muted mb-1">"{{ $issue['text'] }}"</p>
                                        <small class="text-warning">
                                            <i class="fas fa-users mr-1"></i>
                                            Mencionado {{ $issue['count'] }} vez{{ $issue['count'] > 1 ? 'es' : '' }}
                                        </small>
                                    </div>
                                </div>
                                @if(!$loop->last)<hr class="my-2">@endif
                                @endforeach
                            </div>
                            
                            @if(count($issues) > 3)
                            <!-- Paginador para áreas de mejora -->
                            <div class="pagination-controls" id="issues-pagination">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-sm btn-outline-warning" id="prev-issues" onclick="navigateIssues(-1)" disabled>
                                        <i class="fas fa-chevron-left"></i> Anterior
                                    </button>
                                    <small class="text-muted font-weight-bold">
                                        <span id="issues-current">1-3</span> de {{ count($issues) }} áreas
                                    </small>
                                    <button class="btn btn-sm btn-outline-warning" id="next-issues" onclick="navigateIssues(1)">
                                        Siguiente <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Datos para JavaScript -->
                            <script type="text/javascript">
                                window.issuesData = {!! json_encode($issues) !!};
                            </script>
                            @endif
                        @else
                            <!-- Mostrar peor categoría si no hay oportunidades específicas -->
                            <div class="improvement-item">
                                <div class="improvement-icon">
                                    <i class="fas fa-arrow-up text-warning"></i>
                                </div>
                                <div class="improvement-content">
                                    <h5>{{ $worstCategory['name'] ?? 'N/A' }}</h5>
                                    <p class="text-muted mb-1">Categoría con menor puntuación</p>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" style="width: {{ round($worstAvg, 1) }}%"></div>
                                    </div>
                                    <small class="text-warning font-weight-bold">{{ round($worstAvg, 1) }}% de satisfacción</small>
                                </div>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="text-center">
                                <div class="alert alert-info alert-sm mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Sin comentarios específicos</strong>
                                </div>
                                <small class="text-muted">No se encontraron oportunidades de mejora específicas en las respuestas</small>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No hay datos para identificar áreas de mejora.</p>
                            <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-upload mr-1"></i>Cargar Primera Encuesta
                            </a>
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
                        Respuestas Detalladas del Cuestionario
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(!empty($surveyResponses) && $surveyResponses->count() > 0)
                        <div class="table-responsive">
                            <table id="surveyResponsesTable" class="table table-bordered table-striped table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 80px;">Fecha</th>
                                        <th style="width: 100px;">Dependencia</th>
                                        <th style="width: 120px;">Experiencia General</th>
                                        <th style="width: 120px;">Tiempos de Entrega</th>
                                        <th style="width: 120px;">Requerimiento Oportuno</th>
                                        <th style="width: 120px;">Materiales Disponibles</th>
                                        <th style="width: 120px;">Servicio Personal</th>
                                        <th style="width: 120px;">Calidad Materiales</th>
                                        <th style="width: 120px;">Opciones Cotizaciones</th>
                                        <th style="width: 120px;">Proveedores Cumplen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($surveyResponses as $response)
                                    <tr>
                                        <td>{{ $response->timestamp ? $response->timestamp->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ $response->dependencia ?? 'N/A' }}</td>
                                        <td>{{ $response->califica_experiencia ?? 'N/A' }}</td>
                                        <td>{{ $response->califica_tiempos ?? 'N/A' }}</td>
                                        <td>{{ $response->requerimiento_oportuno ?? 'N/A' }}</td>
                                        <td>{{ $response->materiales_disponibles ?? 'N/A' }}</td>
                                        <td>{{ $response->califica_servicio_persona ?? 'N/A' }}</td>
                                        <td>{{ $response->califica_calidad_materiales ?? 'N/A' }}</td>
                                        <td>{{ $response->opciones_cotizaciones ?? 'N/A' }}</td>
                                        <td>{{ $response->proveedores_cumplen ?? 'N/A' }}</td>
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
                            <a href="{{ route('surveys.internal-client.warehouse.upload') }}" class="btn btn-success btn-block">
                                <i class="fas fa-upload"></i>
                                Cargar Nueva Encuesta
                            </a>
                        </div>
                        <div class="col-md-4">
                            @if($selectedPeriod ?? null)
                                <a href="{{ route('surveys.internal-client.warehouse.export', ['period' => $selectedPeriod]) }}" class="btn btn-primary btn-block">
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
@stop