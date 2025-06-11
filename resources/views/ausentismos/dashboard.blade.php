@extends('adminlte::page')

@section('title', 'Dashboard Ausentismos')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Dashboard de Ausentismos</h1>
        </div>
        <div class="col-sm-6">
            <select id="dashboardMesFilter" class="form-control float-right" style="width: 200px;">
                <option value="">Todos los meses</option>
                @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $mes)
                    <option value="{{ $mes }}">{{ $mes }}</option>
                @endforeach
            </select>
        </div>
    </div>
@stop

@section('content')
    <!-- Indicadores principales -->
    <div class="row">
        <div class="col-lg-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalAusencias">{{ $totalAusencias }}</h3>
                    <p>Total Ausencias</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="motivoComun">{{ $motivoComun->motivo_de_ausencia ?? 'N/A' }}</h3>
                    <p>Permiso más común con <span id="motivoComunTotal">{{ $motivoComun->total ?? 0 }}</span> ausencias</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="dependenciaAfectada">{{ $dependenciaAfectada->dependencia ?? 'N/A' }}</h3>
                    <p>Dependencia más afectada con <span id="dependenciaAfectadaTotal">{{ $dependenciaAfectada->total ?? 0 }}</span> ausencias</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ausencias por Motivo</h3>
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="motivosChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ausencias por Dependencia</h3>
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="dependenciasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de datos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Ausentismos</h3>
        </div>
        <div class="card-body">
            <!-- Filtros de la tabla -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <select id="tableMesFilter" class="form-control">
                        <option value="">Todos los meses</option>
                        @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $mes)
                            <option value="{{ $mes }}">{{ $mes }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="dependenciaFilter" class="form-control">
                        <option value="">Todas las dependencias</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="duracionFilter" class="form-control">
                        <option value="">Todas las duraciones</option>
                        <option value="corta">3 días o menos</option>
                        <option value="larga">Más de 3 días</option>
                    </select>
                </div>
            </div>

            <table id="ausentismosTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Persona</th>
                        <th>Dependencia</th>
                        <th>Fecha Desde</th>
                        <th>Fecha Hasta</th>
                        <th>Motivo</th>
                        <th>Duración (días)</th>
                        <th>Mes</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<style>
    .small-box { transition: transform .3s; }
    .small-box:hover { transform: translateY(-5px); }
    .card { transition: all .3s ease; }
    .card:hover { box-shadow: 0 4px 8px rgba(0,0,0,.1); }
    select.form-control { height: calc(2.25rem + 2px); }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Inicialización de gráficos
    let motivosChart = new Chart(document.getElementById('motivosChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($motivosPorcentaje->pluck('motivo_de_ausencia')) !!},
            datasets: [{
                data: {!! json_encode($motivosPorcentaje->pluck('total')) !!},
                backgroundColor: [
                    '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de',
                    '#605ca8', '#ff851b', '#39cccc', '#01ff70', '#3d9970', '#001f3f',
                    '#85144b', '#e83e8c', '#6610f2', '#6f42c1', '#fd7e14', '#e74c3c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 15,
                        font: {
                            size: 12
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map(function(label, i) {
                                    const meta = chart.getDatasetMeta(0);
                                    const style = meta.controller.getStyle(i);
                                    
                                    return {
                                        text: label + ' (' + data.datasets[0].data[i] + ')',
                                        fillStyle: style.backgroundColor,
                                        strokeStyle: style.borderColor,
                                        lineWidth: style.borderWidth,
                                        hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    },
                    onClick: function(e, legendItem, legend) {
                        const index = legendItem.index;
                        const ci = legend.chart;
                        const meta = ci.getDatasetMeta(0);
                        
                        meta.data[index].hidden = !meta.data[index].hidden;
                        ci.update();
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    let dependenciasChart = new Chart(document.getElementById('dependenciasChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($dependenciasPorcentaje->pluck('dependencia')) !!},
            datasets: [{
                label: 'Ausencias por Dependencia',
                data: {!! json_encode($dependenciasPorcentaje->pluck('total')) !!},
                backgroundColor: [
                    '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de', 
                    '#605ca8', '#ff851b', '#39cccc', '#01ff70', '#3d9970', '#001f3f',
                    '#85144b', '#e83e8c', '#6610f2', '#6f42c1', '#fd7e14', '#e74c3c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map(function(label, i) {
                                    const meta = chart.getDatasetMeta(0);
                                    const style = meta.controller.getStyle(i);
                                    
                                    return {
                                        text: label,
                                        fillStyle: style.backgroundColor,
                                        strokeStyle: style.borderColor,
                                        lineWidth: style.borderWidth,
                                        hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    },
                    onClick: function(e, legendItem, legend) {
                        const index = legendItem.index;
                        const ci = legend.chart;
                        const meta = ci.getDatasetMeta(0);
                        
                        meta.data[index].hidden = !meta.data[index].hidden;
                        ci.update();
                    }
                }
            }
        }
    });

    // Inicialización de DataTable con los filtros mejorados
    let table = $('#ausentismosTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("ausentismos.data") }}',
            data: function(d) {
                d.mes = $('#tableMesFilter').val();
                d.dependencia = $('#dependenciaFilter').val();
                d.duracion = $('#duracionFilter').val();
            }
        },
        columns: [
            {data: 'persona', name: 'persona'},
            {data: 'dependencia', name: 'dependencia'},
            {data: 'fecha_ausencia_desde', name: 'fecha_ausencia_desde'},
            {data: 'fecha_hasta', name: 'fecha_hasta'},
            {data: 'motivo_de_ausencia', name: 'motivo_de_ausencia'},
            {data: 'duracion_ausencia', name: 'duracion_ausencia'}, // Cambiado para mostrar el valor original
            {data: 'mes', name: 'mes'}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
        },
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });

    // Función para actualizar el dashboard
    function actualizarDashboard(mes = '') {
        $.ajax({
            url: '{{ route("ausentismos.data") }}',
            data: { mes: mes, dashboard: true },
            success: function(response) {
                // Actualizar indicadores
                $('#totalAusencias').text(response.totalAusencias || 0);
                $('#motivoComun').text(response.motivoComun || 'N/A');
                $('#motivoComunTotal').text(response.motivosPorcentaje.find(m => m.motivo_de_ausencia === response.motivoComun)?.total || 0);
                $('#dependenciaAfectada').text(response.dependenciaAfectada || 'N/A');
                $('#dependenciaAfectadaTotal').text(response.dependenciasPorcentaje.find(d => d.dependencia === response.dependenciaAfectada)?.total || 0);

                // Actualizar gráficos
                motivosChart.data.labels = response.motivosPorcentaje.map(item => item.motivo_de_ausencia);
                motivosChart.data.datasets[0].data = response.motivosPorcentaje.map(item => item.total);
                // Asegurar que haya suficientes colores para todos los motivos
                const motivosColors = [
                    '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de',
                    '#605ca8', '#ff851b', '#39cccc', '#01ff70', '#3d9970', '#001f3f',
                    '#85144b', '#e83e8c', '#6610f2', '#6f42c1', '#fd7e14', '#e74c3c'
                ];
                motivosChart.data.datasets[0].backgroundColor = motivosColors.slice(0, response.motivosPorcentaje.length);
                motivosChart.update();

                dependenciasChart.data.labels = response.dependenciasPorcentaje.map(item => item.dependencia);
                dependenciasChart.data.datasets[0].data = response.dependenciasPorcentaje.map(item => item.total);
                // Asegurar que haya suficientes colores para todas las dependencias
                const colors = [
                    '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de', 
                    '#605ca8', '#ff851b', '#39cccc', '#01ff70', '#3d9970', '#001f3f',
                    '#85144b', '#e83e8c', '#6610f2', '#6f42c1', '#fd7e14', '#e74c3c'
                ];
                dependenciasChart.data.datasets[0].backgroundColor = colors.slice(0, response.dependenciasPorcentaje.length);
                dependenciasChart.update();

                // Actualizar filtro de dependencias
                if (response.dependenciasPorcentaje.length > 0) {
                    $('#dependenciaFilter').empty().append('<option value="">Todas las dependencias</option>');
                    response.dependenciasPorcentaje.forEach(dep => {
                        $('#dependenciaFilter').append($('<option>', {
                            value: dep.dependencia,
                            text: dep.dependencia
                        }));
                    });
                }
            }
        });
    }

    // Event listeners mejorados para los filtros
    $('#dashboardMesFilter').change(function() {
        actualizarDashboard($(this).val());
    });

    $('#tableMesFilter, #dependenciaFilter, #duracionFilter').on('change', function() {
        table.ajax.reload();
    });

    // Cargar dependencias únicas para el filtro al inicio
    $.ajax({
        url: '{{ route("ausentismos.data") }}',
        method: 'GET',
        data: { get_dependencias: true },
        success: function(response) {
            if (response.dependenciasPorcentaje) {
                let dependencias = response.dependenciasPorcentaje;
                $('#dependenciaFilter').empty().append('<option value="">Todas las dependencias</option>');
                dependencias.forEach(function(dep) {
                    $('#dependenciaFilter').append(`<option value="${dep.dependencia}">${dep.dependencia}</option>`);
                });
            }
        }
    });

    // Cargar datos iniciales
    actualizarDashboard();
});
</script>
@stop
