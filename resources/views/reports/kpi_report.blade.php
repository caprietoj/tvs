@extends('adminlte::page')

@section('title', 'Dashboard de KPIs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Dashboard de Indicadores de Gestión</h1>
        <select id="monthFilter" class="form-control" style="width: 200px;">
            <option value="">Todos los meses</option>
            @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $mes)
                <option value="{{ $mes }}">{{ $mes }}</option>
            @endforeach
        </select>
    </div>
@stop

@section('content')
    @if(isset($error))
        <div class="alert alert-danger">{{ $error }}</div>
    @else
        <!-- Tarjetas de Análisis -->
        <div class="row">
            <!-- Enfermería -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="enfermeriaPercentage">{{ number_format($enfermeriaAnalysis['avg_percentage'], 1) }}%</h3>
                        <p>Estado del Indicador<br><small>Desempeño general de Enfermería</small></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="small-box-footer">
                        {{ $enfermeriaAnalysis['status'] }}
                    </div>
                </div>
            </div>

            <!-- Compras -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="comprasPercentage">{{ number_format($comprasAnalysis['avg_percentage'], 1) }}%</h3>
                        <p>Estado del Indicador<br><small>Desempeño general de Compras</small></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="small-box-footer">
                        {{ $comprasAnalysis['status'] }}
                    </div>
                </div>
            </div>

            <!-- RRHH -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="rrhhPercentage">{{ number_format($rrhhAnalysis['avg_percentage'], 1) }}%</h3>
                        <p>Estado del Indicador<br><small>Desempeño general de RRHH</small></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="small-box-footer">
                        {{ $rrhhAnalysis['status'] }}
                    </div>
                </div>
            </div>

            <!-- Sistemas -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="sistemasPercentage">{{ number_format($sistemasAnalysis['avg_percentage'], 1) }}%</h3>
                        <p>Estado del Indicador<br><small>Desempeño general de Sistemas</small></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="small-box-footer">
                        {{ $sistemasAnalysis['status'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mt-4">
            <!-- Gráfico de Rendimiento Mensual por Área -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Rendimiento Mensual por Área</h3>
                        <div class="card-tools">
                            <small class="text-muted">Comparativa del rendimiento actual vs objetivo</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Comparación con Umbrales -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Estado de KPIs vs Objetivos</h3>
                        <div class="card-tools">
                            <small class="text-muted">Porcentaje de cumplimiento por área</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="thresholdChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tablas de KPIs -->
        <div class="row">
            @foreach([
                'enfermeria' => ['title' => 'Enfermería', 'data' => $kpis, 'thresholds' => $enfermeriaThresholds],
                'compras' => ['title' => 'Compras', 'data' => $comprasKpis, 'thresholds' => $comprasThresholds],
                'rrhh' => ['title' => 'Recursos Humanos', 'data' => $recursosKpi, 'thresholds' => $rrhhThresholds],
                'sistemas' => ['title' => 'Sistemas', 'data' => $sistemasKpi, 'thresholds' => $sistemasThresholds]
            ] as $key => $section)
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">KPIs {{ $section['title'] }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" data-area="{{ $key }}">
                                    <thead>
                                        <tr>
                                            <th>KPI</th>
                                            <th>Valor Actual</th>
                                            <th>Indicador</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($section['data'] as $kpi)
                                            <tr>
                                                <td>{{ $kpi->name }}</td>
                                                <td>{{ number_format($kpi->percentage, 1) }}%</td>
                                                <td>
                                                    @php
                                                        $threshold = $section['thresholds']->where('kpi_name', $kpi->name)->first();
                                                    @endphp
                                                    {{ $threshold ? number_format($threshold->value, 1) . '%' : 'N/A' }}
                                                </td>
                                                <td>
                                                    @if($threshold)
                                                        @if($kpi->percentage >= $threshold->value)
                                                            <span class="badge badge-success">Alcanzado</span>
                                                        @else
                                                            <span class="badge badge-danger">No Alcanzado</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-warning">Sin Umbral</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@stop

@section('css')
<style>
    .small-box { transition: all .3s ease; }
    .small-box:hover { transform: translateY(-3px); }
    .table td, .table th { vertical-align: middle; }
    .badge { font-size: 0.9em; padding: 0.5em 0.75em; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Configuración inicial de DataTables
    let tables = $('.table').DataTable({
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad"
            }
        },
        "pageLength": 10,
        "order": [[0, "asc"]]
    });

    // Nuevo gráfico de Rendimiento Mensual
    const performanceChart = new Chart(document.getElementById('performanceChart'), {
        type: 'radar',
        data: {
            labels: ['Cumplimiento', 'Eficiencia', 'Calidad', 'Tiempo de Respuesta', 'Satisfacción'],
            datasets: [
                {
                    label: 'Enfermería',
                    data: calculatePerformanceMetrics(@json($kpis)),
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.2)'
                },
                {
                    label: 'Compras',
                    data: calculatePerformanceMetrics(@json($comprasKpis)),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)'
                },
                {
                    label: 'RRHH',
                    data: calculatePerformanceMetrics(@json($recursosKpi)),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.2)'
                },
                {
                    label: 'Sistemas',
                    data: calculatePerformanceMetrics(@json($sistemasKpi)),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.2)'
                }
            ]
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
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.r.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Comparación mejorado
    const thresholdChart = new Chart(document.getElementById('thresholdChart'), {
        type: 'bar',
        data: {
            labels: ['Enfermería', 'Compras', 'RRHH', 'Sistemas'],
            datasets: [
                {
                    label: 'Rendimiento Actual',
                    data: [
                        {{ $enfermeriaAnalysis['avg_percentage'] }},
                        {{ $comprasAnalysis['avg_percentage'] }},
                        {{ $rrhhAnalysis['avg_percentage'] }},
                        {{ $sistemasAnalysis['avg_percentage'] }}
                    ],
                    backgroundColor: ['rgba(23, 162, 184, 0.8)', 'rgba(40, 167, 69, 0.8)', 
                                   'rgba(255, 193, 7, 0.8)', 'rgba(220, 53, 69, 0.8)']
                },
                {
                    label: 'Objetivo',
                    data: [
                        {{ $enfermeriaAnalysis['avg_threshold'] }},
                        {{ $comprasAnalysis['avg_threshold'] }},
                        {{ $rrhhAnalysis['avg_threshold'] }},
                        {{ $sistemasAnalysis['avg_threshold'] }}
                    ],
                    type: 'line',
                    borderColor: '#6c757d',
                    borderWidth: 2,
                    fill: false,
                    pointStyle: 'rectRot',
                    pointRadius: 8,
                    pointHoverRadius: 10
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Porcentaje de Cumplimiento'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Áreas'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
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

    // Mejorado el filtrado por mes
    $('#monthFilter').change(function() {
        let selectedMonth = $(this).val();
        
        $.ajax({
            url: '{{ route("kpi-report.index") }}',
            method: 'GET',
            data: { month: selectedMonth },
            beforeSend: function() {
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Cargando...',
                    text: 'Actualizando datos',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                // Cerrar indicador de carga
                Swal.close();
                
                // Actualizar gráficos
                updateCharts(response);
                
                // Actualizar tablas
                updateTables(response);
                
                // Actualizar tarjetas
                updateAnalysisCards(response);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos'
                });
            }
        });
    });

    function updateCharts(data) {
        performanceChart.data.datasets[0].data = calculatePerformanceMetrics(data.kpis);
        performanceChart.data.datasets[1].data = calculatePerformanceMetrics(data.comprasKpis);
        performanceChart.data.datasets[2].data = calculatePerformanceMetrics(data.recursosKpi);
        performanceChart.data.datasets[3].data = calculatePerformanceMetrics(data.sistemasKpi);
        performanceChart.update();

        thresholdChart.data.datasets[0].data = [
            data.enfermeriaAnalysis.avg_percentage,
            data.comprasAnalysis.avg_percentage,
            data.rrhhAnalysis.avg_percentage,
            data.sistemasAnalysis.avg_percentage
        ];
        thresholdChart.update();
    }

    function updateTables(data) {
        // Actualizar tabla de Enfermería
        updateTableContent('enfermeria', data.kpis);
        updateTableContent('compras', data.comprasKpis);
        updateTableContent('rrhh', data.recursosKpi);
        updateTableContent('sistemas', data.sistemasKpi);
    }

    function updateTableContent(area, data) {
        const table = $(`table[data-area="${area}"]`).DataTable();
        table.clear();
        data.forEach(item => {
            // Convertir percentage a número si viene como string y formatear
            const percentageValue = typeof item.percentage === 'number' ? item.percentage.toFixed(1) : parseFloat(item.percentage).toFixed(1);
            // Formatear threshold si existe
            const thresholdValue = item.threshold ? 
                (typeof item.threshold === 'number' ? item.threshold.toFixed(1) : parseFloat(item.threshold).toFixed(1)) 
                : 'N/A';
            
            table.row.add([
                item.name,
                percentageValue + '%',
                thresholdValue !== 'N/A' ? thresholdValue + '%' : thresholdValue,
                getStatusBadge(
                    typeof item.percentage === 'number' ? item.percentage : parseFloat(item.percentage), 
                    item.threshold ? (typeof item.threshold === 'number' ? item.threshold : parseFloat(item.threshold)) : null
                )
            ]);
        });
        table.draw();
    }

    function getStatusBadge(percentage, threshold) {
        if (!threshold) return '<span class="badge badge-warning">Sin Umbral</span>';
        return percentage >= threshold 
            ? '<span class="badge badge-success">Alcanzado</span>'
            : '<span class="badge badge-danger">No Alcanzado</span>';
    }

    function updateAnalysisCards(data) {
        $('#enfermeriaPercentage').text(data.enfermeriaAnalysis.avg_percentage.toFixed(1) + '%');
        $('#comprasPercentage').text(data.comprasAnalysis.avg_percentage.toFixed(1) + '%');
        $('#rrhhPercentage').text(data.rrhhAnalysis.avg_percentage.toFixed(1) + '%');
        $('#sistemasPercentage').text(data.sistemasAnalysis.avg_percentage.toFixed(1) + '%');
    }

    function calculatePerformanceMetrics(kpis) {
        // Esta función calcula métricas de rendimiento basadas en los KPIs
        // Puedes ajustar la lógica según tus necesidades específicas
        return [
            calculateAverage(kpis, 'percentage'), // Cumplimiento
            calculateEfficiency(kpis),            // Eficiencia
            calculateQuality(kpis),               // Calidad
            calculateResponseTime(kpis),          // Tiempo de Respuesta
            calculateSatisfaction(kpis)           // Satisfacción
        ];
    }

    function calculateAverage(kpis, field) {
        return kpis.reduce((acc, kpi) => acc + kpi[field], 0) / kpis.length;
    }

    function calculateEfficiency(kpis) {
        // Ejemplo: basado en tiempo de completitud de tareas
        return calculateAverage(kpis, 'percentage') * 0.9;
    }

    function calculateQuality(kpis) {
        // Ejemplo: basado en errores o precisión
        return calculateAverage(kpis, 'percentage') * 0.95;
    }

    function calculateResponseTime(kpis) {
        // Ejemplo: basado en tiempo de respuesta
        return calculateAverage(kpis, 'percentage') * 0.85;
    }

    function calculateSatisfaction(kpis) {
        // Ejemplo: basado en feedback o satisfacción del usuario
        return calculateAverage(kpis, 'percentage') * 0.88;
    }
});
</script>
@stop