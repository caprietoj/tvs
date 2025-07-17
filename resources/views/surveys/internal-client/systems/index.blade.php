@extends('adminlte::page')

@push('css')
<style>
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
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
    const trendData = {!! json_encode($dashboardData['trend_data']) !!};
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    window.trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Satisfacción General (%)',
                data: trendData.values,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#4CAF50',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#388E3C',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'nearest'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(33, 33, 33, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 6,
                    displayColors: false,
                    callbacks: {
                        title: function(tooltipItems) {
                            return 'Período: ' + tooltipItems[0].label;
                        },
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
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    border: {
                        dash: [4, 4]
                    },
                    ticks: {
                        padding: 8,
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        padding: 8,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.3
                },
                point: {
                    hoverRadius: 7,
                    hitRadius: 10
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            },
            hover: {
                mode: 'nearest',
                intersect: false
            }
        }
    });
    
    // Calcular estadísticas del gráfico de tendencia
    if (trendData.values.length > 0) {
        const avg = (trendData.values.reduce((a, b) => a + b, 0) / trendData.values.length).toFixed(1);
        const max = Math.max(...trendData.values).toFixed(1);
        const min = Math.min(...trendData.values).toFixed(1);
        
        document.getElementById('trendAvg').textContent = `Promedio: ${avg}%`;
        document.getElementById('trendMax').textContent = `Máximo: ${max}%`;
        document.getElementById('trendMin').textContent = `Mínimo: ${min}%`;
    }
    
    // Gráfico de categorías
    const categoryData = {!! json_encode($dashboardData['category_comparison']) !!};
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    
    window.categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.labels,
            datasets: [{
                label: 'Satisfacción (%)',
                data: categoryData.values,
                backgroundColor: [
                    '#28a745', '#17a2b8', '#6f42c1', '#fd7e14', 
                    '#6c757d', '#dc3545', '#343a40'
                ],
                borderColor: [
                    '#28a745', '#17a2b8', '#6f42c1', '#fd7e14', 
                    '#6c757d', '#dc3545', '#343a40'
                ],
                borderWidth: 1,
                borderRadius: 4
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
    
    // Gráfico de dependencias
    const departmentData = {!! json_encode($latestData['dependencias']) !!};
    const labels = Object.keys(departmentData);
    const values = Object.values(departmentData);
    
    const ctx = document.getElementById('departmentChart').getContext('2d');
    window.departmentChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545',
                    '#6f42c1', '#17a2b8', '#fd7e14', '#20c997',
                    '#6c757d', '#343a40'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        padding: 15
                    }
                },
                tooltip: {
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
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
});

// Funciones auxiliares para los gráficos
function switchDepartmentView(type) {
    const chart = Chart.getChart('departmentChart');
    if (!chart) return;
    
    const config = chart.config;
    
    switch(type) {
        case 'pie':
            config.type = 'pie';
            config.options.cutout = '0%';
            break;
        case 'doughnut':
            config.type = 'doughnut';
            config.options.cutout = '60%';
            break;
        case 'bar':
            config.type = 'bar';
            config.options.cutout = undefined;
            config.options.indexAxis = 'y';
            config.options.plugins.legend.position = 'right';
            break;
    }
    
    chart.update('active');
}

function toggleDepartmentLabels() {
    const chart = Chart.getChart('departmentChart');
    if (!chart) return;
    
    chart.options.plugins.legend.display = !chart.options.plugins.legend.display;
    chart.update();
}

function toggleDepartmentPercentages() {
    const chart = Chart.getChart('departmentChart');
    if (!chart) return;
    
    const showPercentage = !chart.options.plugins.tooltip.callbacks.label.toString().includes('percentage');
    
    chart.options.plugins.tooltip.callbacks.label = function(context) {
        const total = context.dataset.data.reduce((a, b) => a + b, 0);
        const value = context.raw;
        if (showPercentage) {
            const percentage = ((value / total) * 100).toFixed(1);
            return `${context.label}: ${value} (${percentage}%)`;
        }
        return `${context.label}: ${value}`;
    };
    
    chart.update();
}

function updateStatistics() {
    const chart = Chart.getChart('departmentChart');
    if (!chart) return;
    
    const data = chart.data.datasets[0].data;
    const total = data.reduce((a, b) => a + b, 0);
    const max = Math.max(...data);
    const average = (total / data.length).toFixed(0);
    
    $('#departmentStats .stat-value').eq(0).text(total);
    $('#departmentStats .stat-value').eq(1).text(average);
    $('#departmentStats .stat-value').eq(2).text(max);
}

function resizeCharts() {
    const trendChart = Chart.getChart('trendChart');
    const departmentChart = Chart.getChart('departmentChart');
    const categoryChart = Chart.getChart('categoryChart');
    
    if (trendChart) {
        trendChart.resize();
    }
    if (departmentChart) {
        departmentChart.resize();
    }
    if (categoryChart) {
        categoryChart.resize();
    }
}

function downloadChart(chartId, filename, format = 'png') {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    let url;
    
    if (format === 'png') {
        url = canvas.toDataURL('image/png');
    } else if (format === 'jpeg') {
        url = canvas.toDataURL('image/jpeg', 0.9);
    } else if (format === 'pdf') {
        showToast('Funcionalidad PDF en desarrollo', 'info');
        return;
    }
    
    const link = document.createElement('a');
    link.download = filename + '.' + format;
    link.href = url;
    link.click();
    
    showToast('Gráfico descargado exitosamente', 'success');
}

function printChart(chartId) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Imprimir Gráfico</title>');
    printWindow.document.write('<style>body{margin:0;padding:20px;text-align:center;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<img src="' + canvas.toDataURL() + '" style="max-width:100%;"/>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

function shareChart(chartId) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    canvas.toBlob(function(blob) {
        if (navigator.share) {
            const file = new File([blob], 'grafico.png', { type: 'image/png' });
            navigator.share({
                title: 'Gráfico de Satisfacción',
                text: 'Compartiendo gráfico del dashboard',
                files: [file]
            });
        } else {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'grafico.png';
            link.click();
            URL.revokeObjectURL(url);
        }
    });
}

function showChartData(chartId) {
    const chart = Chart.getChart(chartId);
    if (!chart) return;
    
    const data = chart.data;
    let tableHTML = '<div class="table-responsive"><table class="table table-striped table-sm">';
    tableHTML += '<thead><tr><th>Período/Categoría</th><th>Valor</th></tr></thead><tbody>';
    
    data.labels.forEach((label, index) => {
        const value = data.datasets[0].data[index];
        tableHTML += `<tr><td>${label}</td><td>${value}${chartId === 'trendChart' ? '%' : ''}</td></tr>`;
    });
    
    tableHTML += '</tbody></table></div>';
    
    const modal = $(`
        <div class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Datos del Gráfico</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">${tableHTML}</div>
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

function toggleChartAnimation(chartId) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        chart.update('active');
        showToast('Gráfico actualizado', 'info');
    }
}

function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast-notification ${type}">
            <i class="fas fa-check-circle mr-2"></i>
            ${message}
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => {
        toast.addClass('show');
    }, 100);
    
    setTimeout(() => {
        toast.removeClass('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Agregar estilos para el toast
const toastStyles = `
    <style>
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .toast-notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast-notification.success {
            background: #28a745;
        }
        
        .toast-notification.info {
            background: #17a2b8;
        }
        
        .toast-notification.warning {
            background: #ffc107;
            color: #212529;
        }
        
        .toast-notification.error {
            background: #dc3545;
        }
    </style>
`;

$('head').append(toastStyles);
</script>
@endpush

@section('title', 'Dashboard - Encuesta Cliente Interno Sistemas')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-desktop text-primary"></i>
                Dashboard - Encuesta Cliente Interno Sistemas
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Encuestas</a></li>
                <li class="breadcrumb-item active">Sistemas</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alert de información -->
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-info"></i> Información</h5>
        Este dashboard presenta el análisis estadístico de la satisfacción del personal interno con los servicios del área de Sistemas.
        <strong>Último período evaluado:</strong> {{ $latestData['ultimo_periodo'] }}
    </div>

    <!-- KPIs Principales -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $latestData['total_respuestas'] }}</h3>
                    <p>Total de Respuestas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('surveys.internal-client.systems.results') }}" class="small-box-footer">
                    Ver detalles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $latestData['satisfaccion_general'] }}<sup style="font-size: 20px">%</sup></h3>
                    <p>Satisfacción General</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('surveys.internal-client.systems.results') }}" class="small-box-footer">
                    Ver análisis <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ count($latestData['dependencias']) }}</h3>
                    <p>Dependencias Evaluadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <a href="{{ route('surveys.internal-client.systems.results') }}" class="small-box-footer">
                    Ver distribución <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $latestData['ultimo_periodo'] }}</h3>
                    <p>Último Período</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <a href="{{ route('surveys.internal-client.systems.upload') }}" class="small-box-footer">
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
                        Análisis de Satisfacción por Categoría - {{ $latestData['ultimo_periodo'] }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($latestData['estadisticas_por_categoria'] as $category => $stats)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ $stats['color'] }}">
                                    <i class="{{ $stats['icon'] }}"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ $stats['label'] }}</span>
                                    <span class="info-box-number">{{ $stats['porcentaje'] }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $stats['color'] }}" 
                                             style="width: {{ $stats['porcentaje'] }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Promedio: {{ $stats['promedio'] }}/5.0 ({{ $stats['total_respuestas'] }} evaluaciones)
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
        <div class="col-md-6">
            <div class="card chart-card trend-chart-card">
                <div class="card-header bg-gradient-primary text-white position-relative">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-chart-line mr-2 animated-icon"></i>
                        Evolución de Satisfacción (Últimos 6 meses)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" id="trendFullscreen" title="Pantalla completa">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" id="trendRefresh" title="Actualizar datos">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <div class="dropdown">
                            <button type="button" class="btn btn-tool text-white dropdown-toggle" data-toggle="dropdown" title="Opciones">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <h6 class="dropdown-header">Descargar</h6>
                                <a class="dropdown-item" href="#" onclick="downloadChart('trendChart', 'evolucion-satisfaccion')">
                                    <i class="fas fa-download mr-2"></i>PNG
                                </a>
                                <a class="dropdown-item" href="#" onclick="downloadChart('trendChart', 'evolucion-satisfaccion', 'jpeg')">
                                    <i class="fas fa-image mr-2"></i>JPEG
                                </a>
                                <a class="dropdown-item" href="#" onclick="downloadChart('trendChart', 'evolucion-satisfaccion', 'pdf')">
                                    <i class="fas fa-file-pdf mr-2"></i>PDF
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Acciones</h6>
                                <a class="dropdown-item" href="#" onclick="printChart('trendChart')">
                                    <i class="fas fa-print mr-2"></i>Imprimir
                                </a>
                                <a class="dropdown-item" href="#" onclick="shareChart('trendChart')">
                                    <i class="fas fa-share-alt mr-2"></i>Compartir
                                </a>
                                <a class="dropdown-item" href="#" onclick="showChartData('trendChart')">
                                    <i class="fas fa-table mr-2"></i>Ver datos
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="chart-status-indicator" id="trendStatus">
                        <i class="fas fa-circle text-success"></i>
                        <span class="text-white">Actualizado</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="chart-container-enhanced">
                        <div class="chart-controls">
                            <div class="chart-controls-left">
                                <button class="btn btn-sm btn-outline-primary" onclick="toggleChartType('trendChart')">
                                    <i class="fas fa-chart-bar mr-1"></i>
                                    Cambiar tipo
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleDataPoints('trendChart')">
                                    <i class="fas fa-circle mr-1"></i>
                                    Puntos
                                </button>
                            </div>
                            <div class="chart-controls-right">
                                <div class="chart-zoom-controls">
                                    <button class="btn btn-sm btn-outline-info" onclick="zoomIn('trendChart')">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="zoomOut('trendChart')">
                                        <i class="fas fa-search-minus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="resetZoom('trendChart')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <canvas id="trendChart" width="800" height="280"></canvas>
                        <div class="chart-overlay d-none" id="trendChartOverlay">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando gráfico...</p>
                            </div>
                        </div>
                        <div class="chart-no-data d-none" id="trendNoData">
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No hay datos disponibles</h5>
                                <p class="text-muted">Parece que no hay información para mostrar</p>
                            </div>
                        </div>
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
                        <div class="chart-legend">
                            <span class="legend-item">
                                <span class="legend-color" style="background-color: #007bff;"></span>
                                Satisfacción General
                            </span>
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
                        <button type="button" class="btn btn-tool text-white dropdown-toggle" data-toggle="dropdown" title="Opciones">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" onclick="downloadChart('categoryChart', 'comparacion-categorias')">
                                <i class="fas fa-download mr-2"></i>Descargar PNG
                            </a>
                            <a class="dropdown-item" href="#" onclick="printChart('categoryChart')">
                                <i class="fas fa-print mr-2"></i>Imprimir
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart" width="600" height="280"></canvas>
                        <div class="chart-overlay d-none" id="categoryChartOverlay">
                            <div class="chart-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Cargando gráfico...</p>
                            </div>
                        </div>
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
            <div class="card chart-card department-chart-card">
                <div class="card-header bg-gradient-secondary text-white position-relative">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-building mr-2 animated-icon"></i>
                        Distribución por Dependencias
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" id="departmentFullscreen" title="Pantalla completa">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" id="departmentRefresh" title="Actualizar datos">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <div class="dropdown">
                            <button type="button" class="btn btn-tool text-white dropdown-toggle" data-toggle="dropdown" title="Opciones">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <h6 class="dropdown-header">Descargar</h6>
                                <a class="dropdown-item" href="#" onclick="downloadChart('departmentChart', 'distribucion-dependencias')">
                                    <i class="fas fa-download mr-2"></i>PNG
                                </a>
                                <a class="dropdown-item" href="#" onclick="downloadChart('departmentChart', 'distribucion-dependencias', 'jpeg')">
                                    <i class="fas fa-image mr-2"></i>JPEG
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Visualización</h6>
                                <a class="dropdown-item" href="#" onclick="toggleDepartmentView('pie')">
                                    <i class="fas fa-chart-pie mr-2"></i>Gráfico de torta
                                </a>
                                <a class="dropdown-item" href="#" onclick="toggleDepartmentView('bar')">
                                    <i class="fas fa-chart-bar mr-2"></i>Gráfico de barras
                                </a>
                                <a class="dropdown-item" href="#" onclick="toggleDepartmentView('polar')">
                                    <i class="fas fa-chart-area mr-2"></i>Área polar
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Acciones</h6>
                                <a class="dropdown-item" href="#" onclick="printChart('departmentChart')">
                                    <i class="fas fa-print mr-2"></i>Imprimir
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportDepartmentData()">
                                    <i class="fas fa-file-excel mr-2"></i>Exportar datos
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="chart-status-indicator" id="departmentStatus">
                        <i class="fas fa-circle text-success"></i>
                        <span class="text-white">Actualizado</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="chart-container-enhanced">
                        <div class="chart-controls">
                            <div class="chart-controls-left">
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleDepartmentLabels('departmentChart')">
                                    <i class="fas fa-tag mr-1"></i>
                                    Etiquetas
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="toggleDepartmentPercentages('departmentChart')">
                                    <i class="fas fa-percent mr-1"></i>
                                    Porcentajes
                                </button>
                            </div>
                            <div class="chart-controls-right">
                                <div class="chart-view-toggle">
                                    <button class="btn btn-sm btn-outline-primary active" data-view="doughnut" onclick="switchDepartmentView('doughnut')">
                                        <i class="fas fa-circle-notch"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" data-view="pie" onclick="switchDepartmentView('pie')">
                                        <i class="fas fa-chart-pie"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" data-view="bar" onclick="switchDepartmentView('bar')">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <canvas id="departmentChart" width="400" height="220"></canvas>
                        <div class="chart-overlay d-none" id="departmentChartOverlay">
                            <div class="chart-loading">
                                <div class="spinner-border text-secondary" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando gráfico...</p>
                            </div>
                        </div>
                        <div class="chart-no-data d-none" id="departmentNoData">
                            <div class="text-center py-4">
                                <i class="fas fa-building text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No hay datos disponibles</h5>
                                <p class="text-muted">No se encontraron dependencias para mostrar</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="chart-info">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                {{ count($latestData['dependencias']) }} dependencias participantes
                            </small>
                            <div class="chart-stats mt-1">
                                <span class="badge badge-secondary" id="departmentTotal">Total: {{ array_sum($latestData['dependencias']) }}</span>
                                <span class="badge badge-info" id="departmentAvg">Promedio: {{ count($latestData['dependencias']) > 0 ? number_format(array_sum($latestData['dependencias'])/count($latestData['dependencias']), 1) : 0 }}</span>
                            </div>
                        </div>
                        <div class="chart-actions">
                            <button class="btn btn-sm btn-outline-secondary" onclick="animateDepartmentChart('departmentChart')">
                                <i class="fas fa-play mr-1"></i>
                                Animar
                            </button>
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
                        <button type="button" class="btn btn-tool text-white" data-toggle="modal" data-target="#highlightsModal" title="Ver todos">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="highlights-container">
                        @forelse($dashboardData['top_highlights'] as $index => $highlight)
                        <div class="highlight-item position-relative" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $highlight->aspectos_destacados }}">
                            <div class="highlight-content">
                                <div class="highlight-icon">
                                    <i class="fas fa-quote-left text-success"></i>
                                </div>
                                <div class="highlight-text">
                                    <p class="mb-1">{{ Str::limit($highlight->aspectos_destacados, 75) }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ $highlight->count }} {{ $highlight->count == 1 ? 'persona menciona' : 'personas mencionan' }}
                                    </small>
                                </div>
                                <div class="highlight-actions">
                                    <button class="btn btn-sm btn-outline-success btn-expand" data-full-text="{{ $highlight->aspectos_destacados }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="highlight-rank">
                                <span class="rank-badge">#{{ $index + 1 }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="empty-state text-center py-4">
                            <i class="fas fa-comment-slash text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No hay aspectos destacados disponibles</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @if($dashboardData['top_highlights']->count() > 0)
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Mostrando los {{ $dashboardData['top_highlights']->count() }} aspectos más mencionados
                        </small>
                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#highlightsModal">
                            <i class="fas fa-list mr-1"></i>
                            Ver todos
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Modal para ver todos los aspectos destacados -->
        <div class="modal fade" id="highlightsModal" tabindex="-1" role="dialog" aria-labelledby="highlightsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="highlightsModalLabel">
                            <i class="fas fa-thumbs-up mr-2"></i>
                            Todos los Aspectos Destacados
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @foreach($dashboardData['top_highlights'] as $index => $highlight)
                            <div class="col-12 mb-3">
                                <div class="card border-left-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="text-success mb-1">Aspecto #{{ $index + 1 }}</h6>
                                                <p class="mb-2">{{ $highlight->aspectos_destacados }}</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-users mr-1"></i>
                                                    Mencionado por {{ $highlight->count }} {{ $highlight->count == 1 ? 'persona' : 'personas' }}
                                                </small>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-success">{{ $highlight->count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card opportunities-card">
                <div class="card-header bg-gradient-warning text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-tools mr-2"></i>
                        Principales Oportunidades de Mejora
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse" title="Colapsar">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool text-white" data-toggle="modal" data-target="#opportunitiesModal" title="Ver todas">
                            <i class="fas fa-expand-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="opportunities-container">
                        @forelse($dashboardData['top_issues'] as $index => $issue)
                        <div class="opportunity-item position-relative" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $issue->oportunidades_mejora }}" style="animation-delay: {{ $index * 0.1 }}s;">
                            <div class="opportunity-content">
                                <div class="opportunity-icon">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                </div>
                                <div class="opportunity-text">
                                    <p class="mb-1">{{ Str::limit($issue->oportunidades_mejora, 65) }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-users mr-1"></i>
                                        {{ $issue->count }} {{ $issue->count == 1 ? 'persona menciona' : 'personas mencionan' }}
                                    </small>
                                </div>
                                <div class="opportunity-actions">
                                    <button class="btn btn-sm btn-outline-warning btn-expand" data-full-text="{{ $issue->oportunidades_mejora }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="opportunity-rank">
                                <span class="rank-badge rank-warning">#{{ $index + 1 }}</span>
                            </div>
                            <div class="opportunity-priority">
                                <span class="priority-badge priority-{{ $issue->count > 2 ? 'high' : ($issue->count > 1 ? 'medium' : 'low') }}">
                                    {{ $issue->count > 2 ? 'Alta' : ($issue->count > 1 ? 'Media' : 'Baja') }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="empty-state text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No hay oportunidades de mejora reportadas</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @if($dashboardData['top_issues']->count() > 0)
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Mostrando las {{ $dashboardData['top_issues']->count() }} principales oportunidades
                        </small>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#opportunitiesModal">
                            <i class="fas fa-list mr-1"></i>
                            Ver todas
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para ver todas las oportunidades de mejora -->
    <div class="modal fade" id="opportunitiesModal" tabindex="-1" role="dialog" aria-labelledby="opportunitiesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="opportunitiesModalLabel">
                        <i class="fas fa-tools mr-2"></i>
                        Todas las Oportunidades de Mejora
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @foreach($dashboardData['top_issues'] as $index => $issue)
                        <div class="col-12 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="text-warning mb-1">
                                                Oportunidad #{{ $index + 1 }}
                                                <span class="badge badge-{{ $issue->count > 2 ? 'danger' : ($issue->count > 1 ? 'warning' : 'secondary') }} ml-2">
                                                    {{ $issue->count > 2 ? 'Alta Prioridad' : ($issue->count > 1 ? 'Media Prioridad' : 'Baja Prioridad') }}
                                                </span>
                                            </h6>
                                            <p class="mb-2">{{ $issue->oportunidades_mejora }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-users mr-1"></i>
                                                Mencionado por {{ $issue->count }} {{ $issue->count == 1 ? 'persona' : 'personas' }}
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-warning">{{ $issue->count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-warning" onclick="exportOpportunities()">
                        <i class="fas fa-download mr-1"></i>
                        Exportar Lista
                    </button>
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
                                <a href="{{ route('surveys.internal-client.systems.upload') }}" 
                                   class="btn btn-success btn-lg btn-block">
                                    <i class="fas fa-upload"></i><br>
                                    Subir Nueva Encuesta
                                </a>
                                <small class="text-muted">Sube resultados de encuesta en Excel</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <a href="{{ route('surveys.internal-client.systems.results') }}" 
                                   class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-chart-line"></i><br>
                                    Análisis Histórico
                                </a>
                                <small class="text-muted">Consulta resultados históricos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <a href="{{ route('surveys.internal-client.systems.export') }}" 
                                   class="btn btn-warning btn-lg btn-block">
                                    <i class="fas fa-download"></i><br>
                                    Exportar Datos
                                </a>
                                <small class="text-muted">Descarga datos en Excel</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <button type="button" class="btn btn-info btn-lg btn-block" 
                                        onclick="window.print()">
                                    <i class="fas fa-print"></i><br>
                                    Imprimir Dashboard
                                </button>
                                <small class="text-muted">Imprime este dashboard</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    
    /* Estilos mejorados para gráficos */
    .chart-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: none;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        height: auto;
        max-height: 600px;
    }
    
    .chart-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 40px rgba(0,0,0,0.15);
    }
    
    /* Contenedor de gráficos con dimensiones controladas */
    .chart-container {
        position: relative;
        height: 300px;
        max-height: 300px;
        width: 100%;
        overflow: hidden;
    }
    
    .chart-container canvas {
        max-width: 100% !important;
        height: 100% !important;
        width: 100% !important;
    }
    
    /* Estilos específicos para gráficos mejorados */
    .trend-chart-card {
        background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
    }
    
    .department-chart-card {
        background: linear-gradient(135deg, #ffffff 0%, #f3e5f5 100%);
    }
    
    .department-chart-card .chart-container-enhanced {
        height: 300px;
        max-height: 300px;
    }
    
    .department-chart-card .chart-container-enhanced canvas {
        max-height: 220px !important;
        height: 220px !important;
    }
    
    .chart-container-enhanced {
        position: relative;
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        height: 350px;
        max-height: 350px;
        overflow: hidden;
    }
    
    .chart-container-enhanced canvas {
        max-width: 100% !important;
        max-height: 280px !important;
        width: 100% !important;
        height: 280px !important;
    }
    
    .chart-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .chart-controls-left,
    .chart-controls-right {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .chart-view-toggle {
        display: flex;
        gap: 2px;
        background: white;
        border-radius: 6px;
        padding: 2px;
        border: 1px solid #dee2e6;
    }
    
    .chart-view-toggle .btn {
        border: none;
        padding: 6px 10px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .chart-view-toggle .btn.active {
        background: #007bff;
        color: white;
        box-shadow: 0 2px 5px rgba(0,123,255,0.3);
    }
    
    .chart-zoom-controls {
        display: flex;
        gap: 2px;
        background: white;
        border-radius: 6px;
        padding: 2px;
        border: 1px solid #dee2e6;
    }
    
    .chart-zoom-controls .btn {
        border: none;
        padding: 6px 8px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .chart-status-indicator {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,255,255,0.2);
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .chart-status-indicator i {
        animation: pulse 2s infinite;
    }
    
    .animated-icon {
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-3px);
        }
        60% {
            transform: translateY(-2px);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .chart-stats {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .chart-stats .badge {
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 12px;
    }
    
    .chart-info {
        flex: 1;
    }
    
    .chart-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    
    .chart-no-data {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        z-index: 5;
    }
    
    .chart-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 10px;
    }
    
    .chart-loading {
        text-align: center;
        color: #6c757d;
    }
    
    .chart-loading .spinner-border {
        margin-bottom: 15px;
    }
    
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-left: 10px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
        display: inline-block;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    /* Dropdown mejorado */
    .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        border: none;
        padding: 10px 0;
    }
    
    .dropdown-header {
        color: #6c757d;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 20px 5px;
    }
    
    .dropdown-item {
        padding: 8px 20px;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transform: translateX(3px);
    }
    
    .dropdown-item i {
        width: 16px;
        text-align: center;
    }
    
    .dropdown-divider {
        margin: 8px 0;
        border-top: 1px solid #e9ecef;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .chart-controls {
            flex-direction: column;
            gap: 10px;
        }
        
        .chart-controls-left,
        .chart-controls-right {
            justify-content: center;
        }
        
        .chart-stats {
            justify-content: center;
        }
        
        .chart-actions {
            justify-content: center;
        }
        
        .chart-status-indicator {
            position: static;
            margin-top: 10px;
            justify-content: center;
        }
        
        .chart-container {
            height: 250px;
            max-height: 250px;
        }
        
        .chart-container-enhanced {
            height: 280px;
            max-height: 280px;
        }
        
        .chart-container-enhanced canvas {
            max-height: 200px !important;
            height: 200px !important;
        }
        
        .department-chart-card .chart-container-enhanced {
            height: 250px;
            max-height: 250px;
        }
        
        .department-chart-card .chart-container-enhanced canvas {
            max-height: 180px !important;
            height: 180px !important;
        }
    }
    
    /* Prevenir overflow en todos los tamaños */
    .chart-container,
    .chart-container-enhanced {
        overflow: hidden;
        position: relative;
    }
    
    .chart-container canvas,
    .chart-container-enhanced canvas {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    
    /* Animaciones para los botones */
    .btn {
        transition: all 0.2s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .btn-tool:hover {
        transform: scale(1.1);
        background: rgba(255,255,255,0.2) !important;
    }
    
    .card-header .btn-tool {
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    /* Efectos de hover para las tarjetas */
    .chart-card .card-header {
        background: linear-gradient(135deg, var(--header-color-1), var(--header-color-2));
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .chart-card .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .chart-card:hover .card-header::before {
        left: 100%;
    }
    
    .trend-chart-card .card-header {
        --header-color-1: #007bff;
        --header-color-2: #0056b3;
    }
    
    .department-chart-card .card-header {
        --header-color-1: #6c757d;
        --header-color-2: #495057;
    }
    
    /* Estilos para oportunidades de mejora */
    .opportunities-card {
        background: linear-gradient(135deg, #ffffff 0%, #fff3cd 100%);
        border: none;
        overflow: hidden;
    }
    
    .opportunities-container {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .opportunity-item {
        padding: 15px;
        border-bottom: 1px solid #f8f9fa;
        transition: all 0.3s ease;
        background: white;
        margin: 5px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        animation: fadeInUp 0.5s ease-out;
        animation-fill-mode: both;
    }
    
    .opportunity-item:hover {
        background: #fff8e1;
        transform: translateX(5px);
        box-shadow: 0 4px 20px rgba(255,193,7,0.2);
    }
    
    .opportunity-content {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .opportunity-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ffc107, #ffb300);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        box-shadow: 0 2px 10px rgba(255,193,7,0.3);
    }
    
    .opportunity-text {
        flex: 1;
    }
    
    .opportunity-actions {
        display: flex;
        gap: 5px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .opportunity-item:hover .opportunity-actions {
        opacity: 1;
    }
    
    .opportunity-rank {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    
    .rank-badge.rank-warning {
        background: linear-gradient(135deg, #ff6b35, #ffc107);
        color: white;
        padding: 4px 8px;
        border-radius: 15px;
        font-size: 10px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(255,107,53,0.3);
    }
    
    .opportunity-priority {
        position: absolute;
        bottom: 10px;
        right: 10px;
    }
    
    .priority-badge {
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .priority-high {
        background: #dc3545;
        color: white;
    }
    
    .priority-medium {
        background: #ffc107;
        color: #212529;
    }
    
    .priority-low {
        background: #6c757d;
        color: white;
    }
    
    /* Animaciones */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    /* Efectos hover mejorados */
    .btn-tool:hover {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }
    
    .card-header .btn-tool {
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .card-header .btn-tool:hover {
        background: rgba(255,255,255,0.2);
    }
    
    /* Estilos para modales */
    .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 50px rgba(0,0,0,0.3);
    }
    
    .modal-header {
        border-radius: 15px 15px 0 0;
        border-bottom: none;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        border-top: none;
        padding: 15px 20px;
    }
    
    /* Efectos de carga */
    .loading-pulse {
        animation: pulse 1.5s infinite;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .opportunity-content {
            flex-direction: column;
            gap: 8px;
        }
        
        .opportunity-actions {
            opacity: 1;
            justify-content: center;
        }
        
        .opportunity-rank,
        .opportunity-priority {
            position: static;
            display: inline-block;
            margin-top: 10px;
        }
    }
    
    /* Estilos para tooltips personalizados */
    .tooltip {
        font-size: 12px;
    }
    
    .tooltip-inner {
        background: rgba(0,0,0,0.9);
        border-radius: 8px;
        padding: 8px 12px;
        max-width: 300px;
    }
    
    /* Estilos para elementos destacados existentes */
    .highlights-card {
        background: linear-gradient(135deg, #ffffff 0%, #e8f5e8 100%);
        border: none;
        overflow: hidden;
    }
    
    .highlights-container {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .highlight-item {
        padding: 15px;
        border-bottom: 1px solid #f8f9fa;
        transition: all 0.3s ease;
        background: white;
        margin: 5px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        animation: fadeInUp 0.5s ease-out;
        animation-fill-mode: both;
    }
    
    .highlight-item:hover {
        background: #e8f5e8;
        transform: translateX(5px);
        box-shadow: 0 4px 20px rgba(40,167,69,0.2);
    }
    
    .highlight-content {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .highlight-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        box-shadow: 0 2px 10px rgba(40,167,69,0.3);
    }
    
    .highlight-text {
        flex: 1;
    }
    
    .highlight-actions {
        display: flex;
        gap: 5px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .highlight-item:hover .highlight-actions {
        opacity: 1;
    }
    
    .highlight-rank {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    
    .rank-badge {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 4px 8px;
        border-radius: 15px;
        font-size: 10px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(40,167,69,0.3);
    }
    
    /* Estilos para acciones de exportación */
    .export-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        min-width: 150px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }
    
    .export-menu.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .export-menu a {
        display: block;
        padding: 10px 15px;
        color: #495057;
        text-decoration: none;
        transition: background 0.2s ease;
    }
    
    .export-menu a:hover {
        background: #f8f9fa;
        color: #007bff;
    }
    
    /* Efecto de carga para gráficos */
    .chart-loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
        height: 20px;
        margin: 10px 0;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    /* Modo print */
    @media print {
        .card-tools,
        .btn,
        .modal {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
        
        .card,
        .small-box,
        .info-box {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
    }
</style>
@endsection

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
    const trendData = {!! json_encode($dashboardData['trend_data']) !!};
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    
    window.trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Satisfacción General (%)',
                data: trendData.values,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#4CAF50',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#388E3C',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'nearest'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15,
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(33, 33, 33, 0.9)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 6,
                    displayColors: false,
                    callbacks: {
                        title: function(tooltipItems) {
                            return 'Período: ' + tooltipItems[0].label;
                        },
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
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    border: {
                        dash: [4, 4]
                    },
                    ticks: {
                        padding: 8,
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        padding: 8,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.3
                },
                point: {
                    hoverRadius: 7,
                    hitRadius: 10
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            },
            hover: {
                mode: 'nearest',
                intersect: false
            }
        }
    });
    
    // Calcular estadísticas del gráfico de tendencia
    if (trendData.values.length > 0) {
        const avg = (trendData.values.reduce((a, b) => a + b, 0) / trendData.values.length).toFixed(1);
        const max = Math.max(...trendData.values).toFixed(1);
        const min = Math.min(...trendData.values).toFixed(1);
        
        document.getElementById('trendAvg').textContent = `Promedio: ${avg}%`;
        document.getElementById('trendMax').textContent = `Máximo: ${max}%`;
        document.getElementById('trendMin').textContent = `Mínimo: ${min}%`;
    }
    
    // Gráfico de categorías
    const categoryData = {!! json_encode($dashboardData['category_comparison']) !!};
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: categoryData.labels,
            datasets: [{
                label: 'Satisfacción (%)',
                data: categoryData.values,
                backgroundColor: [
                    '#28a745', '#17a2b8', '#6f42c1', '#fd7e14', 
                    '#6c757d', '#dc3545', '#343a40'
                ],
                borderColor: [
                    '#28a745', '#17a2b8', '#6f42c1', '#fd7e14', 
                    '#6c757d', '#dc3545', '#343a40'
                ],
                borderWidth: 1,
                borderRadius: 4
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
    
    function initializeCharts() {
        // Gráfico de dependencias
        const departmentData = {!! json_encode($latestData['dependencias']) !!};
        const labels = Object.keys(departmentData);
        const values = Object.values(departmentData);
        
        const ctx = document.getElementById('departmentChart').getContext('2d');
        window.departmentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545',
                        '#6f42c1', '#17a2b8', '#fd7e14', '#20c997',
                        '#6c757d', '#343a40'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
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
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Actualizar estadísticas iniciales
        updateStatistics();
        
        // Configurar eventos de redimensionamiento
        window.addEventListener('resize', function() {
            if (window.departmentChart) {
                window.departmentChart.resize();
            }
        });
        
        // Manejar el cambio de tipo de gráfico
        window.switchDepartmentView = function(type) {
            const chart = Chart.getChart('departmentChart');
            if (!chart) return;
            
            const config = chart.config;
            
            switch(type) {
                case 'pie':
                    config.type = 'pie';
                    config.options.cutout = '0%';
                    break;
                case 'doughnut':
                    config.type = 'doughnut';
                    config.options.cutout = '60%';
                    break;
                case 'bar':
                    config.type = 'bar';
                    config.options.cutout = undefined;
                    config.options.indexAxis = 'y';
                    config.options.plugins.legend.position = 'right';
                    break;
            }
            
            chart.update('active');
        };
        
        // Manejar el redimensionamiento de la ventana
        $(window).resize(function() {
            const departmentChart = Chart.getChart('departmentChart');
            if (departmentChart) {
                departmentChart.resize();
            }
        });
    }
    
    // Animación de números
    function animateNumbers() {
        $('.small-box .inner h3').each(function() {
            const $this = $(this);
            const text = $this.text();
            const suffix = text.includes('%') ? '%' : '';
            
            // Excluir campos que contienen texto (como fechas o períodos)
            // Si contiene letras, no animar
            if (/[a-zA-ZáéíóúñÁÉÍÓÚÑ]/.test(text)) {
                return; // Skip animation for text fields
            }
            
            // Extraer el número considerando decimales
            const numberMatch = text.match(/[\d.]+/);
            const number = numberMatch ? parseFloat(numberMatch[0]) : 0;
            
            if (number > 0) {
                $({ value: 0 }).animate({ value: number }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        // Mantener decimales si los hay
                        const displayValue = number % 1 === 0 ? Math.floor(this.value) : this.value.toFixed(1);
                        $this.html(displayValue + '<sup style="font-size: 20px">' + suffix + '</sup>');
                    }
                });
            }
        });
    }
    
    // Inicializar gráficos
    initializeCharts();
    
    // Ejecutar animación después de cargar los gráficos
    setTimeout(animateNumbers, 500);
    
    // Funcionalidades para aspectos destacados
    initializeHighlights();
    
    // Funcionalidades para oportunidades de mejora
    initializeOpportunities();
    
    // Funcionalidades para gráficos mejorados
    initializeChartFunctionalities();
    
    function initializeHighlights() {
        // Inicializar tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Expandir/contraer texto completo
        $('.btn-expand').click(function() {
            const fullText = $(this).data('full-text');
            const $item = $(this).closest('.highlight-item');
            const $textElement = $item.find('.highlight-text p');
            
            if ($(this).hasClass('expanded')) {
                // Contraer
                $textElement.text(fullText.substring(0, 75) + (fullText.length > 75 ? '...' : ''));
                $(this).removeClass('expanded').html('<i class="fas fa-eye"></i>');
                $(this).attr('title', 'Ver texto completo');
            } else {
                // Expandir
                $textElement.text(fullText);
                $(this).addClass('expanded').html('<i class="fas fa-eye-slash"></i>');
                $(this).attr('title', 'Contraer texto');
            }
        });
        
        // Animación de entrada para los elementos
        $('.highlight-item').each(function(index) {
            $(this).css('animation-delay', (index * 0.1) + 's');
        });
        
        // Efecto de hover mejorado
        $('.highlight-item').hover(
            function() {
                $(this).find('.highlight-icon i').addClass('fa-beat');
            },
            function() {
                $(this).find('.highlight-icon i').removeClass('fa-beat');
            }
        );
        
        // Copiar texto al portapapeles
        $('.highlight-item').dblclick(function() {
            const text = $(this).find('.highlight-text p').text();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    // Mostrar feedback visual
                    const $item = $(this);
                    const originalBg = $item.css('background-color');
                    $item.css('background-color', '#d4edda');
                    
                    setTimeout(() => {
                        $item.css('background-color', originalBg);
                    }, 500);
                    
                    // Mostrar toast de confirmación
                    showToast('Texto copiado al portapapeles', 'success');
                }.bind(this));
            }
        });
    }
    
    function initializeOpportunities() {
        // Inicializar tooltips para oportunidades
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Expandir/contraer texto completo para oportunidades
        $('.opportunity-item .btn-expand').click(function() {
            const fullText = $(this).data('full-text');
            const $item = $(this).closest('.opportunity-item');
            const $textElement = $item.find('.opportunity-text p');
            
            if ($(this).hasClass('expanded')) {
                // Contraer
                $textElement.text(fullText.substring(0, 65) + (fullText.length > 65 ? '...' : ''));
                $(this).removeClass('expanded').html('<i class="fas fa-eye"></i>');
                $(this).attr('title', 'Ver texto completo');
            } else {
                // Expandir
                $textElement.text(fullText);
                $(this).addClass('expanded').html('<i class="fas fa-eye-slash"></i>');
                $(this).attr('title', 'Contraer texto');
            }
        });
        
        // Animación de entrada para las oportunidades
        $('.opportunity-item').each(function(index) {
            $(this).css('animation-delay', (index * 0.1) + 's');
        });
        
        // Efecto de hover mejorado para oportunidades
        $('.opportunity-item').hover(
            function() {
                $(this).find('.opportunity-icon i').addClass('fa-bounce');
            },
            function() {
                $(this).find('.opportunity-icon i').removeClass('fa-bounce');
            }
        );
        
        // Copiar texto al portapapeles para oportunidades
        $('.opportunity-item').dblclick(function() {
            const text = $(this).find('.opportunity-text p').text();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    // Mostrar feedback visual
                    const $item = $(this);
                    const originalBg = $item.css('background-color');
                    $item.css('background-color', '#fff3cd');
                    
                    setTimeout(() => {
                        $item.css('background-color', originalBg);
                    }, 500);
                    
                    // Mostrar toast de confirmación
                    showToast('Oportunidad copiada al portapapeles', 'warning');
                }.bind(this));
            }
        });
    }
    
    function initializeChartFunctionalities() {
        // Funcionalidad de pantalla completa para gráficos
        $('#trendFullscreen').click(function() {
            toggleFullscreen('trendChart');
        });
        
        $('#categoryFullscreen').click(function() {
            toggleFullscreen('categoryChart');
        });
        
        $('#departmentFullscreen').click(function() {
            toggleFullscreen('departmentChart');
        });
        
        // Efectos de carga para gráficos
        showChartLoading();
        
        // Ocultar loading después de que los gráficos se carguen
        setTimeout(() => {
            hideChartLoading();
        }, 1000);
    }
    
    function toggleFullscreen(chartId) {
        const canvas = document.getElementById(chartId);
        const container = canvas.parentElement;
        
        if (!document.fullscreenElement) {
            container.requestFullscreen().then(() => {
                // Redimensionar gráfico en pantalla completa
                const chart = Chart.getChart(chartId);
                if (chart) {
                    chart.resize();
                }
            });
        } else {
            document.exitFullscreen();
        }
    }
    
    function downloadChart(chartId, filename, format = 'png') {
        const departmentChart = Chart.getChart('departmentChart');
            if (departmentChart && chartId === 'departmentChart') {
            const container = document.getElementById(chartId);
            const svg = container.getElementsByTagName('svg')[0];
            if (svg) {
                const svgData = new XMLSerializer().serializeToString(svg);
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();
                
                img.onload = function() {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);
                    const link = document.createElement('a');
                    link.download = `${filename}.${format}`;
                    link.href = canvas.toDataURL(`image/${format}`);
                    link.click();
                    showToast('Gráfico descargado exitosamente', 'success');
                };
                
                img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
            }
        } else {
            const canvas = document.getElementById(chartId);
            let url;
            
            if (format === 'png') {
                url = canvas.toDataURL('image/png');
            } else if (format === 'jpeg') {
                url = canvas.toDataURL('image/jpeg', 0.9);
            } else if (format === 'pdf') {
                showToast('Funcionalidad PDF en desarrollo', 'info');
                return;
            }
            
            const link = document.createElement('a');
            link.download = filename + '.' + format;
            link.href = url;
            link.click();
            
            showToast('Gráfico descargado exitosamente', 'success');
        }
    }
    
    function printChart(chartId) {
        const canvas = document.getElementById(chartId);
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Imprimir Gráfico</title>');
        printWindow.document.write('<style>body{margin:0;padding:20px;text-align:center;}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<img src="' + canvas.toDataURL() + '" style="max-width:100%;"/>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
    
    function shareChart(chartId) {
        const canvas = document.getElementById(chartId);
        canvas.toBlob(function(blob) {
            if (navigator.share) {
                const file = new File([blob], 'grafico.png', { type: 'image/png' });
                navigator.share({
                    title: 'Gráfico de Satisfacción',
                    text: 'Compartiendo gráfico del dashboard',
                    files: [file]
                });
            } else {
                // Fallback para navegadores que no soportan Web Share API
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'grafico.png';
                link.click();
                URL.revokeObjectURL(url);
            }
        });
    }
    
    function showChartData(chartId) {
        let tableHTML = '<div class="table-responsive"><table class="table table-striped table-sm">';
        tableHTML += '<thead><tr><th>Período/Categoría</th><th>Valor</th></tr></thead><tbody>';
        
        const chart = Chart.getChart(chartId);
        if (!chart) return;
        
        const data = chart.data;
        data.labels.forEach((label, index) => {
            const value = data.datasets[0].data[index];
            tableHTML += `<tr><td>${label}</td><td>${value}${chartId === 'trendChart' ? '%' : ''}</td></tr>`;
        });
        }
        
        tableHTML += '</tbody></table></div>';
        
        // Mostrar en modal
        const modal = $(`
            <div class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Datos del Gráfico</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">${tableHTML}</div>
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
    
    // Funciones de manipulación solo para gráficos que no son departmentChart
    function toggleChartType(chartId) {
        if (chartId === 'departmentChart') return;
        const chart = Chart.getChart(chartId);
        if (!chart) return;
        
        const currentType = chart.config.type;
        const newType = currentType === 'line' ? 'bar' : 'line';
        
        chart.config.type = newType;
        chart.update('active');
        
        showToast(`Gráfico cambiado a ${newType === 'line' ? 'línea' : 'barras'}`, 'info');
    }
    
    function toggleDataPoints(chartId) {
        if (chartId === 'departmentChart') return;
        const chart = Chart.getChart(chartId);
        if (!chart) return;
        
        const dataset = chart.data.datasets[0];
        const currentRadius = dataset.pointRadius;
        
        dataset.pointRadius = currentRadius === 0 ? 6 : 0;
        dataset.pointHoverRadius = currentRadius === 0 ? 8 : 0;
        
        chart.update('active');
        
        showToast(`Puntos ${currentRadius === 0 ? 'activados' : 'desactivados'}`, 'info');
    }
    
    function zoomIn(chartId) {
        if (chartId === 'departmentChart') return;
        const chart = Chart.getChart(chartId);
        if (!chart) return;
        
        const scales = chart.options.scales;
        if (scales.y) {
            const currentMax = scales.y.max || 100;
            scales.y.max = currentMax * 0.8;
            chart.update('none');
            showToast('Zoom aplicado', 'info');
        }
    }
    
    function zoomOut(chartId) {
        if (chartId === 'departmentChart') return;
        const chart = Chart.getChart(chartId);
        if (!chart) return;
        
        const scales = chart.options.scales;
        if (scales.y) {
            const currentMax = scales.y.max || 100;
            scales.y.max = Math.min(currentMax * 1.2, 100);
            chart.update('none');
            showToast('Zoom reducido', 'info');
        }
    }
    
    function resetZoom(chartId) {
        if (chartId === 'departmentChart') return;
        const chart = Chart.getChart(chartId);
        if (!chart) return;
        
        const scales = chart.options.scales;
        if (scales.y) {
            scales.y.max = 100;
            chart.update('none');
            showToast('Zoom restaurado', 'info');
        }
    }
    
    function switchDepartmentView(viewType) {
        const chart = Chart.getChart('departmentChart');
            if (chart) {
            
            if (viewType === 'bar') {
                chart.config.type = 'bar';
                chart.options.indexAxis = 'y';
                chart.options.plugins.legend.position = 'right';
                chart.options.cutout = undefined;
            } else {
                chart.config.type = viewType === 'doughnut' ? 'doughnut' : 'pie';
                chart.options.indexAxis = undefined;
                chart.options.plugins.legend.position = 'bottom';
                chart.options.cutout = viewType === 'doughnut' ? '60%' : '0%';
            }
            
            chart.update('active');
            
            // Actualizar botones activos
            document.querySelectorAll('.chart-view-toggle .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-view="${viewType}"]`).classList.add('active');
            
            showToast(`Vista cambiada a ${viewType}`, 'info');
        }
    }
    
    function toggleDepartmentLabels() {
        const chart = Chart.getChart('departmentChart');
            if (chart) {
            chart.options.plugins.legend.display = !chart.options.plugins.legend.display;
            chart.update();
            showToast(`Etiquetas ${chart.options.plugins.legend.display ? 'activadas' : 'desactivadas'}`, 'info');
        }
    }
    
    function toggleDepartmentPercentages() {
        const chart = Chart.getChart('departmentChart');
            if (chart) {
            const showPercentage = !chart.options.plugins.tooltip.callbacks.label.toString().includes('percentage');
            
            chart.options.plugins.tooltip.callbacks.label = function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const value = context.raw;
                if (showPercentage) {
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${context.label}: ${value} (${percentage}%)`;
                }
                return `${context.label}: ${value}`;
            };
            
            chart.update();
            showToast(`Porcentajes ${showPercentage ? 'activados' : 'desactivados'}`, 'info');
        }
    }
    
    // La animación ahora es manejada por las opciones de Google Charts
    
    function exportDepartmentData() {
        const chart = Chart.getChart('departmentChart');
        if (chart) {
            const data = chart.data;
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Dependencia,Cantidad\\n";
            
            for (let i = 0; i < data.labels.length; i++) {
                const label = data.labels[i];
                const value = data.datasets[0].data[i];
                csvContent += `"${label}","${value}"\\n`;
            }
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "distribucion_dependencias.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showToast('Datos exportados exitosamente', 'success');
        }
    }
    
    // Botones de actualización
    document.getElementById('trendRefresh').addEventListener('click', function() {
        showChartLoading();
        setTimeout(() => {
            hideChartLoading();
            updateChartStatus('trendStatus', 'success', 'Actualizado');
            showToast('Datos actualizados', 'success');
        }, 1000);
    });
    
    document.getElementById('departmentRefresh').addEventListener('click', function() {
        showChartLoading();
        setTimeout(() => {
            hideChartLoading();
            updateChartStatus('departmentStatus', 'success', 'Actualizado');
            showToast('Datos actualizados', 'success');
        }, 1000);
    });
    
    function updateChartStatus(statusId, type, message) {
        const status = document.getElementById(statusId);
        const icon = status.querySelector('i');
        const text = status.querySelector('span');
        
        icon.className = `fas fa-circle text-${type}`;
        text.textContent = message;
    }
    
    function showChartLoading() {
        $('.chart-overlay').removeClass('d-none');
    }
    
    function hideChartLoading() {
        $('.chart-overlay').addClass('d-none');
    }
    
    function exportOpportunities() {
        const opportunities = [];
        $('.opportunity-item').each(function() {
            const text = $(this).find('.opportunity-text p').text();
            const count = $(this).find('.opportunity-text small').text();
            opportunities.push({
                text: text,
                mentions: count
            });
        });
        
        // Crear CSV
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Oportunidad,Menciones\\n";
        
        opportunities.forEach(function(opportunity) {
            csvContent += `"${opportunity.text}","${opportunity.mentions}"\\n`;
        });
        
        // Descargar archivo
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "oportunidades_mejora.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('Lista de oportunidades exportada', 'success');
    }
    
    // Función para mostrar toast
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="toast-notification ${type}">
                <i class="fas fa-check-circle mr-2"></i>
                ${message}
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => {
            toast.addClass('show');
        }, 100);
        
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Funciones adicionales para los gráficos
    function showChartLoading() {
        const loader = '<div class="overlay"><i class="fas fa-sync fa-spin"></i></div>';
        $('.chart-container').append(loader);
    }
    
    function hideChartLoading() {
        $('.chart-container .overlay').remove();
    }
    
    function showStatusMessage(message, type = 'info') {
        const statusHtml = `
            <div class="alert alert-${type} alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-${type === 'success' ? 'check' : 'info'}"></i> Estado</h5>
                ${message}
            </div>
        `;
        
        $('.chart-statistics').prepend(statusHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    function generateRandomColor() {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    function updateStatistics() {
        const trendChart = Chart.getChart('trendChart');
        
        if (trendChart) {
            const data = trendChart.data.datasets[0].data;
            const average = (data.reduce((a, b) => a + b, 0) / data.length).toFixed(1);
            const max = Math.max(...data).toFixed(1);
            const min = Math.min(...data).toFixed(1);
            
            $('#trendStats .stat-value').eq(0).text(average + '%');
            $('#trendStats .stat-value').eq(1).text(max + '%');
            $('#trendStats .stat-value').eq(2).text(min + '%');
        }
        
        const departmentChart = Chart.getChart('departmentChart');
        if (departmentChart) {
            const data = departmentChart.data.datasets[0].data;
            const total = data.reduce((a, b) => a + b, 0);
            const max = Math.max(...data);
            const average = (total / data.length).toFixed(0);
            
            $('#departmentStats .stat-value').eq(0).text(total);
            $('#departmentStats .stat-value').eq(1).text(average);
            $('#departmentStats .stat-value').eq(2).text(max);
        }
    }
    
    // Llamar a updateStatistics cuando se cargan los gráficos
    setTimeout(updateStatistics, 1000);
    
    // Función para redimensionar gráficos
    function resizeCharts() {
        const trendChart = Chart.getChart('trendChart');
        const categoryChart = Chart.getChart('categoryChart');
        
        if (trendChart) {
            trendChart.resize();
        }
        const departmentChart = Chart.getChart('departmentChart');
            if (departmentChart) {
                departmentChart.resize();
        }
        if (categoryChart) {
            categoryChart.resize();
        }
    }
    
    // Redimensionar gráficos cuando se redimensiona la ventana
    $(window).on('resize', function() {
        setTimeout(resizeCharts, 100);
    });
    
    // Redimensionar gráficos cuando se carga la página
    $(window).on('load', function() {
        setTimeout(resizeCharts, 500);
    });
    
    // Observer para detectar cambios en el tamaño del contenedor
    const resizeObserver = new ResizeObserver(function(entries) {
        let resizeTimer;
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            resizeCharts();
        }, 250);
    });
    
    // Observar cambios en los contenedores de gráficos
    const chartContainers = document.querySelectorAll('.chart-container, .chart-container-enhanced');
    chartContainers.forEach(container => {
        resizeObserver.observe(container);
    });
    
    // Limpiar observer cuando se cierra la página
    $(window).on('beforeunload', function() {
        resizeObserver.disconnect();
    });
});

// Las funciones de manipulación de gráficos han sido actualizadas y movidas arriba
    if (!chart) return;
    
    chart.update('active');
    showToast('Gráfico animado', 'info');
}

function exportDepartmentData() {
    const chart = Chart.getChart('departmentChart');
    if (!chart) return;
    
    const data = chart.data;
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Dependencia,Cantidad\\n";
    
    data.labels.forEach((label, index) => {
        const value = data.datasets[0].data[index];
        csvContent += `"${label}","${value}"\\n`;
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "distribucion_dependencias.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showToast('Datos exportados exitosamente', 'success');
}

function updateChartStatus(statusId, type, message) {
    const status = document.getElementById(statusId);
    if (!status) return;
    
    const icon = status.querySelector('i');
    const text = status.querySelector('span');
    
    if (icon) icon.className = `fas fa-circle text-${type}`;
    if (text) text.textContent = message;
}

function showChartLoading() {
    const loader = '<div class="overlay"><i class="fas fa-sync fa-spin"></i></div>';
    $('.chart-container').append(loader);
}

function hideChartLoading() {
    $('.chart-container .overlay').remove();
}

function showStatusMessage(message, type = 'info') {
    const statusHtml = `
        <div class="alert alert-${type} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-${type === 'success' ? 'check' : 'info'}"></i> Estado</h5>
            ${message}
        </div>
    `;
    
    $('.chart-statistics').prepend(statusHtml);
    
    setTimeout(() => {
        $('.alert').fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}

function generateRandomColor() {
    const colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}

function updateStatistics() {
    const trendChart = Chart.getChart('trendChart');
    
    if (trendChart) {
        const data = trendChart.data.datasets[0].data;
        const average = (data.reduce((a, b) => a + b, 0) / data.length).toFixed(1);
        const max = Math.max(...data).toFixed(1);
        const min = Math.min(...data).toFixed(1);
        
        $('#trendStats .stat-value').eq(0).text(average + '%');
        $('#trendStats .stat-value').eq(1).text(max + '%');
        $('#trendStats .stat-value').eq(2).text(min + '%');
    }
    
    const departmentChart = Chart.getChart('departmentChart');
            if (departmentChart) {
                const data = departmentChart.data.datasets[0].data;
        const total = data.reduce((a, b) => a + b, 0);
        const max = Math.max(...data);
        const average = (total / data.length).toFixed(0);
        
        $('#departmentStats .stat-value').eq(0).text(total);
        $('#departmentStats .stat-value').eq(1).text(average);
        $('#departmentStats .stat-value').eq(2).text(max);
    }
}

function resizeCharts() {
    const trendChart = Chart.getChart('trendChart');
    const departmentChart = Chart.getChart('departmentChart');
    const categoryChart = Chart.getChart('categoryChart');
    
    if (trendChart) {
        trendChart.resize();
    }
    if (departmentChart) {
        departmentChart.resize();
    }
    if (categoryChart) {
        categoryChart.resize();
    }
}

function downloadChart(chartId, filename, format = 'png') {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    let url;
    
    if (format === 'png') {
        url = canvas.toDataURL('image/png');
    } else if (format === 'jpeg') {
        url = canvas.toDataURL('image/jpeg', 0.9);
    } else if (format === 'pdf') {
        // Para PDF necesitaríamos una librería como jsPDF
        showToast('Funcionalidad PDF en desarrollo', 'info');
        return;
    }
    
    const link = document.createElement('a');
    link.download = filename + '.' + format;
    link.href = url;
    link.click();
    
    showToast('Gráfico descargado exitosamente', 'success');
}

function printChart(chartId) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Imprimir Gráfico</title>');
    printWindow.document.write('<style>body{margin:0;padding:20px;text-align:center;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<img src="' + canvas.toDataURL() + '" style="max-width:100%;"/>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

function shareChart(chartId) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    canvas.toBlob(function(blob) {
        if (navigator.share) {
            const file = new File([blob], 'grafico.png', { type: 'image/png' });
            navigator.share({
                title: 'Gráfico de Satisfacción',
                text: 'Compartiendo gráfico del dashboard',
                files: [file]
            });
        } else {
            // Fallback para navegadores que no soportan Web Share API
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'grafico.png';
            link.click();
            URL.revokeObjectURL(url);
        }
    });
}

function showChartData(chartId) {
    const chart = Chart.getChart(chartId);
    if (!chart) return;
    
    const data = chart.data;
    let tableHTML = '<div class="table-responsive"><table class="table table-striped table-sm">';
    tableHTML += '<thead><tr><th>Período/Categoría</th><th>Valor</th></tr></thead><tbody>';
    
    data.labels.forEach((label, index) => {
        const value = data.datasets[0].data[index];
        tableHTML += `<tr><td>${label}</td><td>${value}${chartId === 'trendChart' ? '%' : ''}</td></tr>`;
    });
    
    tableHTML += '</tbody></table></div>';
    
    // Mostrar en modal
    const modal = $(`
        <div class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Datos del Gráfico</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">${tableHTML}</div>
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

function toggleChartAnimation(chartId) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        chart.update('active');
        showToast('Gráfico actualizado', 'info');
    }
}

function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast-notification ${type}">
            <i class="fas fa-check-circle mr-2"></i>
            ${message}
        </div>
    `);
    
    $('body').append(toast);
    
    setTimeout(() => {
        toast.addClass('show');
    }, 100);
    
    setTimeout(() => {
        toast.removeClass('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Agregar estilos para el toast
const toastStyles = `
    <style>
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .toast-notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast-notification.success {
            background: #28a745;
        }
        
        .toast-notification.info {
            background: #17a2b8;
        }
        
        .toast-notification.warning {
            background: #ffc107;
            color: #212529;
        }
        
        .toast-notification.error {
            background: #dc3545;
        }
    </style>
`;

$('head').append(toastStyles);
</script>
@endpush
