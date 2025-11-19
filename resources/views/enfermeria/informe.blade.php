@extends('adminlte::page')

@section('title', 'Informe de Enfermería')

@section('content_header')
    <h1>
        <i class="fas fa-chart-line"></i> Informe de Enfermería
        <small>Dashboard Estadístico</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('enfermeria.informe') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_inicio">
                                <i class="far fa-calendar-alt"></i> Fecha Inicio
                            </label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                   value="{{ request('fecha_inicio', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_fin">
                                <i class="far fa-calendar-check"></i> Fecha Fin
                            </label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                   value="{{ request('fecha_fin', now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo">
                                <i class="fas fa-users"></i> Tipo de Usuario
                            </label>
                            <select class="form-control" id="tipo" name="tipo">
                                <option value="ambos" {{ request('tipo', 'ambos') == 'ambos' ? 'selected' : '' }}>Ambos</option>
                                <option value="estudiantes" {{ request('tipo') == 'estudiantes' ? 'selected' : '' }}>Estudiantes</option>
                                <option value="colaboradores" {{ request('tipo') == 'colaboradores' ? 'selected' : '' }}>Colaboradores</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Generar Informe
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabs -->
    @if(in_array(request('tipo', 'ambos'), ['ambos', 'estudiantes']))
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="estudiantes-tab" data-toggle="pill" href="#estudiantes" role="tab">
                        <i class="fas fa-user-graduate"></i> Estudiantes
                    </a>
                </li>
                @if(request('tipo', 'ambos') == 'ambos')
                <li class="nav-item">
                    <a class="nav-link" id="colaboradores-tab" data-toggle="pill" href="#colaboradores" role="tab">
                        <i class="fas fa-user-tie"></i> Colaboradores
                    </a>
                </li>
                @endif
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Tab Estudiantes -->
                <div class="tab-pane fade show active" id="estudiantes">
                    
                    <!-- Small Boxes (Stat box) -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $estadisticasEstudiantes['total'] ?? 0 }}</h3>
                                    <p>Total Ingresos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $estadisticasEstudiantes['derivacion_medico'] ?? 0 }}</h3>
                                    <p>Derivados al Médico</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-md"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $estadisticasEstudiantes['retorno_salon'] ?? 0 }}</h3>
                                    <p>Retorno a Salón</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $estadisticasEstudiantes['salida_casa'] ?? 0 }}</h3>
                                    <p>Salida a Casa</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-home"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $estadisticasEstudiantes['seguimiento'] ?? 0 }}</h3>
                                    <p>En Seguimiento</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-purple">
                                <div class="inner">
                                    <h3>{{ $estadisticasEstudiantes['psicologia'] ?? 0 }}</h3>
                                    <p>Derivados a Psicología</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-brain"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Ingresos por Motivo</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartMotivoEstudiantes" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Ingresos por Curso</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartCursoEstudiantes" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Procedencia del Estudiante</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartVieneDeEstudiantes" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-procedures"></i> Estado de Derivación</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartDerivacionEstudiantes" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Tendencia Temporal de Ingresos</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartTendenciaEstudiantes" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Fin Tab Estudiantes -->

                @if(request('tipo', 'ambos') == 'ambos')
                <!-- Tab Colaboradores -->
                <div class="tab-pane fade" id="colaboradores">
                    
                    <!-- Small Boxes -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $estadisticasColaboradores['total'] ?? 0 }}</h3>
                                    <p>Total Ingresos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $estadisticasColaboradores['en_observacion'] ?? 0 }}</h3>
                                    <p>En Observación</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $estadisticasColaboradores['alta'] ?? 0 }}</h3>
                                    <p>Alta</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $estadisticasColaboradores['requiere_seguimiento'] ?? 0 }}</h3>
                                    <p>Requiere Seguimiento</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Ingresos por Motivo</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartMotivoColaboradores" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-warning">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Estado de Seguimiento</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartSeguimientoColaboradores" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Tendencia Temporal de Ingresos</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartTendenciaColaboradores" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Fin Tab Colaboradores -->
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(request('tipo') == 'colaboradores')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-tie"></i> Colaboradores</h3>
        </div>
        <div class="card-body">
            
            <!-- Small Boxes -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $estadisticasColaboradores['total'] ?? 0 }}</h3>
                            <p>Total Ingresos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $estadisticasColaboradores['en_observacion'] ?? 0 }}</h3>
                            <p>En Observación</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $estadisticasColaboradores['alta'] ?? 0 }}</h3>
                            <p>Alta</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $estadisticasColaboradores['requiere_seguimiento'] ?? 0 }}</h3>
                            <p>Requiere Seguimiento</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Ingresos por Motivo</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartMotivoColaboradores2" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Estado de Seguimiento</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartSeguimientoColaboradores2" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line"></i> Tendencia Temporal de Ingresos</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="chartTendenciaColaboradores2" style="min-height: 250px; height: 250px; max-height: 250px;"></canvas>
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
    .small-box.bg-purple {
        background-color: #6f42c1 !important;
        color: white;
    }
    .small-box .icon {
        font-size: 70px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
    // Colores para gráficos
    const colores = [
        '#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8',
        '#6610f2', '#e83e8c', '#fd7e14', '#20c997', '#6c757d'
    ];

    // Plugin de datalabels configurado para mostrar valores y porcentajes
    const pluginDatalabels = {
        anchor: 'center',
        align: 'center',
        color: '#fff',
        font: {
            weight: 'bold',
            size: 14
        },
        formatter: (value, ctx) => {
            let sum = 0;
            let dataArr = ctx.chart.data.datasets[0].data;
            dataArr.map(data => {
                sum += data;
            });
            let percentage = (value * 100 / sum).toFixed(1) + "%";
            return value + '\n' + percentage;
        }
    };

    @if(in_array(request('tipo', 'ambos'), ['ambos', 'estudiantes']))
    // Gráfico Motivos Estudiantes
    const dataMotivoEst = @json($estadisticasEstudiantes['por_motivo'] ?? []);
    if(dataMotivoEst.length > 0) {
        new Chart(document.getElementById('chartMotivoEstudiantes'), {
            type: 'bar',
            data: {
                labels: dataMotivoEst.map(i => i.motivo || 'Sin especificar'),
                datasets: [{
                    label: 'Cantidad',
                    data: dataMotivoEst.map(i => i.total),
                    backgroundColor: colores
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        font: { weight: 'bold', size: 12 }
                    }
                },
                scales: {
                    x: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Gráfico Cursos Estudiantes
    const dataCursoEst = @json($estadisticasEstudiantes['por_curso'] ?? []);
    if(dataCursoEst.length > 0) {
        new Chart(document.getElementById('chartCursoEstudiantes'), {
            type: 'bar',
            data: {
                labels: dataCursoEst.map(i => i.curso || 'Sin especificar'),
                datasets: [{
                    label: 'Cantidad',
                    data: dataCursoEst.map(i => i.total),
                    backgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#333',
                        font: { weight: 'bold', size: 12 }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Gráfico Viene De Estudiantes
    const dataVieneDeEst = @json($estadisticasEstudiantes['por_viene_de'] ?? []);
    if(dataVieneDeEst.length > 0) {
        new Chart(document.getElementById('chartVieneDeEstudiantes'), {
            type: 'doughnut',
            data: {
                labels: dataVieneDeEst.map(i => i.viene_de || 'Sin especificar'),
                datasets: [{
                    data: dataVieneDeEst.map(i => i.total),
                    backgroundColor: colores
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            font: { size: 11 }
                        }
                    },
                    datalabels: pluginDatalabels
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Gráfico Derivación Estudiantes
    const dataDerivEst = @json($estadisticasEstudiantes['por_derivacion'] ?? []);
    if(dataDerivEst.length > 0) {
        new Chart(document.getElementById('chartDerivacionEstudiantes'), {
            type: 'pie',
            data: {
                labels: dataDerivEst.map(i => i.derivacion || 'Sin especificar'),
                datasets: [{
                    data: dataDerivEst.map(i => i.total),
                    backgroundColor: colores
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            font: { size: 11 }
                        }
                    },
                    datalabels: pluginDatalabels
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Gráfico Tendencia Estudiantes
    const dataTendEst = @json($estadisticasEstudiantes['por_dia'] ?? []);
    if(dataTendEst.length > 0) {
        new Chart(document.getElementById('chartTendenciaEstudiantes'), {
            type: 'line',
            data: {
                labels: dataTendEst.map(i => i.dia),
                datasets: [{
                    label: 'Ingresos',
                    data: dataTendEst.map(i => i.total),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0,123,255,0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: false
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            },
            plugins: [ChartDataLabels]
        });
    }
    @endif

    @if(in_array(request('tipo', 'ambos'), ['ambos', 'colaboradores']) || request('tipo') == 'colaboradores')
    // Gráfico Motivos Colaboradores
    const dataMotivoCol = @json($estadisticasColaboradores['por_motivo'] ?? []);
    const canvasIdMotivo = '{{ request("tipo") }}' == 'colaboradores' ? 'chartMotivoColaboradores2' : 'chartMotivoColaboradores';
    if(dataMotivoCol.length > 0 && document.getElementById(canvasIdMotivo)) {
        new Chart(document.getElementById(canvasIdMotivo), {
            type: 'bar',
            data: {
                labels: dataMotivoCol.map(i => i.motivo || 'Sin especificar'),
                datasets: [{
                    label: 'Cantidad',
                    data: dataMotivoCol.map(i => i.total),
                    backgroundColor: colores
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        font: { weight: 'bold', size: 12 }
                    }
                },
                scales: {
                    x: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Gráfico Estado de Seguimiento Colaboradores
    const dataSeguimientoCol = @json($estadisticasColaboradores['por_seguimiento'] ?? []);
    const canvasIdSeguimiento = '{{ request("tipo") }}' == 'colaboradores' ? 'chartSeguimientoColaboradores2' : 'chartSeguimientoColaboradores';
    if(dataSeguimientoCol.length > 0 && document.getElementById(canvasIdSeguimiento)) {
        new Chart(document.getElementById(canvasIdSeguimiento), {
            type: 'pie',
            data: {
                labels: dataSeguimientoCol.map(i => i.seguimiento || 'Sin especificar'),
                datasets: [{
                    data: dataSeguimientoCol.map(i => i.total),
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            font: { size: 11 }
                        }
                    },
                    datalabels: pluginDatalabels
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // Gráfico Tendencia Colaboradores
    const dataTendCol = @json($estadisticasColaboradores['por_dia'] ?? []);
    const canvasIdTend = '{{ request("tipo") }}' == 'colaboradores' ? 'chartTendenciaColaboradores2' : 'chartTendenciaColaboradores';
    if(dataTendCol.length > 0 && document.getElementById(canvasIdTend)) {
        new Chart(document.getElementById(canvasIdTend), {
            type: 'line',
            data: {
                labels: dataTendCol.map(i => i.dia),
                datasets: [{
                    label: 'Ingresos',
                    data: dataTendCol.map(i => i.total),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: false
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            },
            plugins: [ChartDataLabels]
        });
    }
    @endif
</script>
@stop
