@extends('adminlte::page')

@section('title', 'Resultados Históricos - Encuesta Sistemas')

@section('content_header')
    <h1>
        <i class="fas fa-chart-line"></i>
        Resultados Históricos - Encuesta Sistemas
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        Consulta de Resultados Históricos
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year_filter">
                                    <i class="fas fa-calendar-alt"></i>
                                    Año
                                </label>
                                <select class="form-control" id="year_filter">
                                    <option value="">Todos los años</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="month_filter">
                                    <i class="fas fa-calendar"></i>
                                    Mes
                                </label>
                                <select class="form-control" id="month_filter">
                                    <option value="">Todos los meses</option>
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department_filter">
                                    <i class="fas fa-building"></i>
                                    Dependencia
                                </label>
                                <select class="form-control" id="department_filter">
                                    <option value="">Todas las dependencias</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department }}">{{ $department }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" id="filterBtn">
                                <i class="fas fa-filter"></i>
                                Filtrar Resultados
                            </button>
                            <button type="button" class="btn btn-success ml-2" id="exportBtn">
                                <i class="fas fa-download"></i>
                                Exportar a Excel
                            </button>
                            <a href="{{ route('surveys.internal-client.systems') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-arrow-left"></i>
                                Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Resumen -->
    <div class="row" id="summarySection">
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon">
                    <i class="fas fa-users"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Respuestas</span>
                    <span class="info-box-number" id="totalResponses">{{ $totalResponses }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon">
                    <i class="fas fa-percentage"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Satisfacción Promedio</span>
                    <span class="info-box-number" id="avgSatisfaction">{{ $avgSatisfaction }}%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon">
                    <i class="fas fa-building"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Dependencias</span>
                    <span class="info-box-number" id="totalDepartments">{{ $totalDepartments }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon">
                    <i class="fas fa-calendar"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Último Período</span>
                    <span class="info-box-number" id="lastPeriod">{{ $lastPeriod }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Evolución de Satisfacción
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="satisfactionTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Satisfacción por Categoría
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Resultados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        Resultados Detallados
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Dependencia</th>
                                    <th>Tiempos Respuesta</th>
                                    <th>Efectividad</th>
                                    <th>Profesionalismo</th>
                                    <th>Estado Equipos</th>
                                    <th>Calidad Internet</th>
                                    <th>Intervención Eventos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                    <tr>
                                        <td>{{ $result->response_timestamp->format('d/m/Y H:i') }}</td>
                                        <td>{{ $result->dependencia }}</td>
                                        <td>
                                            <span class="badge badge-{{ $result->tiempos_respuesta == 'Excelente' ? 'success' : ($result->tiempos_respuesta == 'Buena' ? 'primary' : 'warning') }}">
                                                {{ $result->tiempos_respuesta }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $result->efectividad_tecnica == 'Muy efectiva' ? 'success' : ($result->efectividad_tecnica == 'Efectiva' ? 'primary' : 'warning') }}">
                                                {{ $result->efectividad_tecnica }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $result->profesionalismo == 'Excelente' ? 'success' : ($result->profesionalismo == 'Bueno' ? 'primary' : 'warning') }}">
                                                {{ $result->profesionalismo }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $result->estado_equipos == 'Excelente' ? 'success' : ($result->estado_equipos == 'Bueno' ? 'primary' : 'warning') }}">
                                                {{ $result->estado_equipos }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $result->calidad_internet == 'Excelente' ? 'success' : ($result->calidad_internet == 'Buena' ? 'primary' : 'warning') }}">
                                                {{ $result->calidad_internet }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $result->intervencion_eventos == 'Excelente' ? 'success' : ($result->intervencion_eventos == 'Buenas' ? 'primary' : 'warning') }}">
                                                {{ $result->intervencion_eventos }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    onclick="showDetails({{ $result->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
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

<!-- Modal para ver detalles -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-info-circle"></i>
                    Detalles de la Respuesta
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .info-box {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .badge {
        font-size: 11px;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#resultsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 0, "desc" ]],
        "pageLength": 25
    });

    // Crear gráficos iniciales
    createCharts();

    // Filtrar resultados
    $('#filterBtn').click(function() {
        const year = $('#year_filter').val();
        const month = $('#month_filter').val();
        const department = $('#department_filter').val();

        // Aquí iría la lógica para filtrar via AJAX
        // Por ahora solo recargamos la página con parámetros
        let url = new URL(window.location.href);
        if (year) url.searchParams.set('year', year);
        if (month) url.searchParams.set('month', month);
        if (department) url.searchParams.set('department', department);
        
        window.location.href = url.toString();
    });

    // Exportar a Excel
    $('#exportBtn').click(function() {
        const year = $('#year_filter').val();
        const month = $('#month_filter').val();
        const department = $('#department_filter').val();

        let url = '{{ route("surveys.internal-client.systems.export") }}';
        let params = new URLSearchParams();
        if (year) params.append('year', year);
        if (month) params.append('month', month);
        if (department) params.append('department', department);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        window.open(url, '_blank');
    });
});

function createCharts() {
    // Datos de ejemplo - en producción vendrían del controlador
    const trendData = {!! json_encode($trendData) !!};
    const categoryData = {!! json_encode($categoryData) !!};

    // Gráfico de tendencia
    const ctx1 = document.getElementById('satisfactionTrendChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Satisfacción %',
                data: trendData.values,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Gráfico de categorías
    const ctx2 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: categoryData.labels,
            datasets: [{
                label: 'Satisfacción %',
                data: categoryData.values,
                backgroundColor: [
                    '#4CAF50', '#2196F3', '#FF9800', '#9C27B0', 
                    '#F44336', '#00BCD4', '#795548'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function showDetails(id) {
    // Hacer petición AJAX para obtener detalles
    $.ajax({
        url: '{{ route("surveys.internal-client.systems.details", ":id") }}'.replace(':id', id),
        method: 'GET',
        success: function(response) {
            $('#detailsContent').html(response);
            $('#detailsModal').modal('show');
        },
        error: function() {
            alert('Error al cargar los detalles');
        }
    });
}
</script>
@stop
