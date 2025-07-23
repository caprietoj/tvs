@extends('adminlte::page')

@section('title', 'Análisis Detallado - Encuestas Padres de Familia')

@section('content_header')
    <h1>
        <i class="fas fa-chart-line"></i> Análisis Detallado
        <small>Cafetería y Transporte</small>
    </h1>
@endsection

@section('content')
    <!-- Filtros -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i> Filtros de Análisis
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('surveys.parent-student.analysis') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="period">Período</label>
                                    <select name="period" id="period" class="form-control">
                                        <option value="">Todos los períodos</option>
                                        @foreach($periods as $period)
                                            <option value="{{ $period }}" {{ $selectedPeriod == $period ? 'selected' : '' }}>
                                                {{ $period }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="grade">Grado</label>
                                    <select name="grade" id="grade" class="form-control">
                                        <option value="">Todos los grados</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade }}" {{ $selectedGrade == $grade ? 'selected' : '' }}>
                                                {{ $grade }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="provider">Proveedor</label>
                                    <select name="provider" id="provider" class="form-control">
                                        <option value="">Todos los proveedores</option>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider }}" {{ $selectedProvider == $provider ? 'selected' : '' }}>
                                                {{ $provider }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de Cafetería -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-utensils"></i> Análisis de Cafetería
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $cafeteriaMetrics['total_responses'] }} respuestas</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Gráfica Radar -->
                        <div class="col-md-6">
                            <canvas id="cafeteriaRadarChart" height="300"></canvas>
                        </div>
                        
                        <!-- Métricas Detalladas -->
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-star"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Calidad de Comida</span>
                                            <span class="info-box-number">{{ $cafeteriaMetrics['food_quality'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-info" style="width: {{ $cafeteriaMetrics['food_quality'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success">
                                            <i class="fas fa-plate-wheat"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Satisfacción Porciones</span>
                                            <span class="info-box-number">{{ $cafeteriaMetrics['portion_satisfaction'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: {{ $cafeteriaMetrics['portion_satisfaction'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-list"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Menú Ofrecido</span>
                                            <span class="info-box-number">{{ $cafeteriaMetrics['menu_offered'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-warning" style="width: {{ $cafeteriaMetrics['menu_offered'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger">
                                            <i class="fas fa-thermometer-half"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Temperatura</span>
                                            <span class="info-box-number">{{ $cafeteriaMetrics['food_temperature'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-danger" style="width: {{ $cafeteriaMetrics['food_temperature'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de Transporte -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bus"></i> Análisis de Transporte
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success">{{ $transportMetrics['total_responses'] }} respuestas</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Gráfica de Barras -->
                        <div class="col-md-6">
                            <canvas id="transportBarChart" height="300"></canvas>
                        </div>
                        
                        <!-- Métricas Detalladas -->
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Puntualidad</span>
                                            <span class="info-box-number">{{ $transportMetrics['punctuality'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" style="width: {{ $transportMetrics['punctuality'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-broom"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Limpieza Vehículo</span>
                                            <span class="info-box-number">{{ $transportMetrics['vehicle_cleanliness'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-info" style="width: {{ $transportMetrics['vehicle_cleanliness'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success">
                                            <i class="fas fa-handshake"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Atención Personal</span>
                                            <span class="info-box-number">{{ $transportMetrics['staff_treatment'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" style="width: {{ $transportMetrics['staff_treatment'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-comments"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Comunicación</span>
                                            <span class="info-box-number">{{ $transportMetrics['communication'] }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-warning" style="width: {{ $transportMetrics['communication'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis por Grado -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-graduation-cap"></i> Distribución por Grado
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="gradeDistributionChart" height="300"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Grado</th>
                                            <th>Respuestas</th>
                                            <th>Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gradeAnalysis as $grade)
                                            <tr>
                                                <td>{{ $grade->student_grade }}</td>
                                                <td>{{ $grade->total }}</td>
                                                <td>{{ $gradeAnalysis->sum('total') > 0 ? round(($grade->total / $gradeAnalysis->sum('total')) * 100, 1) : 0 }}%</td>
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
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
    <style>
        .info-box {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .progress {
            height: 4px;
            margin-top: 5px;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        // Gráfica Radar de Cafetería
        const cafeteriaRadarCtx = document.getElementById('cafeteriaRadarChart').getContext('2d');
        const cafeteriaRadarChart = new Chart(cafeteriaRadarCtx, {
            type: 'radar',
            data: {
                labels: {!! json_encode($cafeteriaChartData['labels']) !!},
                datasets: [{
                    label: 'Satisfacción (%)',
                    data: {!! json_encode($cafeteriaChartData['data']) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }]
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
                }
            }
        });

        // Gráfica de Barras de Transporte
        const transportBarCtx = document.getElementById('transportBarChart').getContext('2d');
        const transportBarChart = new Chart(transportBarCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($transportChartData['labels']) !!},
                datasets: [{
                    label: 'Satisfacción (%)',
                    data: {!! json_encode($transportChartData['data']) !!},
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
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
                }
            }
        });

        // Gráfica de Distribución por Grado
        const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        const gradeDistributionChart = new Chart(gradeDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($gradeAnalysis->pluck('student_grade')->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($gradeAnalysis->pluck('total')->toArray()) !!},
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
@endsection
