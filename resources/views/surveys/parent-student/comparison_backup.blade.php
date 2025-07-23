@extends('adminlte::page')

@section('title', 'Comparativo de Períodos - Encuestas Padres de Familia')

@section('content_header')
    <h1>
        <i class="fas fa-balance-scale"></i> Comparativo de Períodos
        <small>Análisis de Cambio de Proveedor</small>
    </h1>
@endsection

@section('content')
    <!-- Selección de Períodos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i> Seleccionar Períodos a Comparar
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('surveys.parent-student.comparison') }}">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="period1">Período 1 (Anterior)</label>
                                    <select name="period1" id="period1" class="form-control">
                                        <option value="">Seleccionar período...</option>
                                        @foreach($periods as $period)
                                            <option value="{{ $period }}" {{ $period1 == $period ? 'selected' : '' }}>
                                                {{ $period }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label>&nbsp;</label>
                                <div class="form-group">
                                    <i class="fas fa-arrow-right text-muted" style="font-size: 24px; margin-top: 10px;"></i>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="period2">Período 2 (Actual)</label>
                                    <select name="period2" id="period2" class="form-control">
                                        <option value="">Seleccionar período...</option>
                                        @foreach($periods as $period)
                                            <option value="{{ $period }}" {{ $period2 == $period ? 'selected' : '' }}>
                                                {{ $period }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Comparar Períodos
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($comparisonData)
        <!-- Resumen Ejecutivo -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i> Resumen Ejecutivo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $comparisonData['period1'] }}</span>
                                        <span class="info-box-number">{{ $comparisonData['provider1'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $comparisonData['period2'] }}</span>
                                        <span class="info-box-number">{{ $comparisonData['provider2'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparativo de Cafetería -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-utensils"></i> Comparativo de Cafetería
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="cafeteriaComparisonChart" height="300"></canvas>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Métrica</th>
                                                <th>{{ $comparisonData['provider1'] }}</th>
                                                <th>{{ $comparisonData['provider2'] }}</th>
                                                <th>Cambio</th>
                                                <th>Tendencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($comparisonData['cafeteria_comparison']['differences'] as $metric => $data)
                                                @if($metric !== 'total_responses')
                                                    <tr>
                                                        <td>{{ ucfirst(str_replace('_', ' ', $metric)) }}</td>
                                                        <td>{{ $data['period1'] }}%</td>
                                                        <td>{{ $data['period2'] }}%</td>
                                                        <td>
                                                            <span class="badge badge-{{ $data['trend'] == 'improvement' ? 'success' : ($data['trend'] == 'decline' ? 'danger' : 'secondary') }}">
                                                                {{ $data['difference'] > 0 ? '+' : '' }}{{ $data['difference'] }}%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($data['trend'] == 'improvement')
                                                                <i class="fas fa-arrow-up text-success"></i>
                                                            @elseif($data['trend'] == 'decline')
                                                                <i class="fas fa-arrow-down text-danger"></i>
                                                            @else
                                                                <i class="fas fa-minus text-secondary"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
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

        <!-- Comparativo de Transporte -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bus"></i> Comparativo de Transporte
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="transportComparisonChart" height="300"></canvas>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Métrica</th>
                                                <th>{{ $comparisonData['provider1'] }}</th>
                                                <th>{{ $comparisonData['provider2'] }}</th>
                                                <th>Cambio</th>
                                                <th>Tendencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($comparisonData['transport_comparison']['differences'] as $metric => $data)
                                                @if($metric !== 'total_responses')
                                                    <tr>
                                                        <td>{{ ucfirst(str_replace('_', ' ', $metric)) }}</td>
                                                        <td>{{ $data['period1'] }}%</td>
                                                        <td>{{ $data['period2'] }}%</td>
                                                        <td>
                                                            <span class="badge badge-{{ $data['trend'] == 'improvement' ? 'success' : ($data['trend'] == 'decline' ? 'danger' : 'secondary') }}">
                                                                {{ $data['difference'] > 0 ? '+' : '' }}{{ $data['difference'] }}%
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($data['trend'] == 'improvement')
                                                                <i class="fas fa-arrow-up text-success"></i>
                                                            @elseif($data['trend'] == 'decline')
                                                                <i class="fas fa-arrow-down text-danger"></i>
                                                            @else
                                                                <i class="fas fa-minus text-secondary"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
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

        <!-- Análisis de Impacto -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i> Análisis de Impacto
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Mejoras Principales -->
                            <div class="col-md-6">
                                <h5 class="text-success">
                                    <i class="fas fa-thumbs-up"></i> Principales Mejoras
                                </h5>
                                <ul class="list-unstyled">
                                    @php
                                        $improvements = [];
                                        foreach(array_merge($comparisonData['cafeteria_comparison']['differences'], $comparisonData['transport_comparison']['differences']) as $metric => $data) {
                                            if($data['trend'] == 'improvement' && $data['difference'] > 5) {
                                                $improvements[] = ['metric' => $metric, 'improvement' => $data['difference']];
                                            }
                                        }
                                        usort($improvements, function($a, $b) { return $b['improvement'] - $a['improvement']; });
                                    @endphp
                                    @foreach(array_slice($improvements, 0, 5) as $improvement)
                                        <li>
                                            <i class="fas fa-arrow-up text-success"></i>
                                            {{ ucfirst(str_replace('_', ' ', $improvement['metric'])) }}: 
                                            <strong>+{{ $improvement['improvement'] }}%</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            
                            <!-- Áreas de Oportunidad -->
                            <div class="col-md-6">
                                <h5 class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Áreas de Oportunidad
                                </h5>
                                <ul class="list-unstyled">
                                    @php
                                        $declines = [];
                                        foreach(array_merge($comparisonData['cafeteria_comparison']['differences'], $comparisonData['transport_comparison']['differences']) as $metric => $data) {
                                            if($data['trend'] == 'decline' && $data['difference'] < -2) {
                                                $declines[] = ['metric' => $metric, 'decline' => $data['difference']];
                                            }
                                        }
                                        usort($declines, function($a, $b) { return $a['decline'] - $b['decline']; });
                                    @endphp
                                    @foreach(array_slice($declines, 0, 5) as $decline)
                                        <li>
                                            <i class="fas fa-arrow-down text-danger"></i>
                                            {{ ucfirst(str_replace('_', ' ', $decline['metric'])) }}: 
                                            <strong>{{ $decline['decline'] }}%</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <h4><i class="icon fas fa-info"></i> Información</h4>
            Selecciona dos períodos para ver el análisis comparativo.
        </div>
    @endif
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
    </style>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        @if($comparisonData)
        // Preparar datos para gráficas
        const cafeteriaLabels = ['Calidad Comida', 'Satisfacción Porciones', 'Menú Ofrecido', 'Variedad Menú', 'Temperatura', 'Limpieza', 'Atención'];
        const cafeteriaData1 = [
            {{ $comparisonData['cafeteria_comparison']['period1']['food_quality'] }},
            {{ $comparisonData['cafeteria_comparison']['period1']['portion_satisfaction'] }},
            {{ $comparisonData['cafeteria_comparison']['period1']['menu_offered'] }},
            {{ $comparisonData['cafeteria_comparison']['period1']['menu_variety'] }},
            {{ $comparisonData['cafeteria_comparison']['period1']['food_temperature'] }},
            {{ $comparisonData['cafeteria_comparison']['period1']['dining_cleanliness'] }},
            {{ $comparisonData['cafeteria_comparison']['period1']['staff_treatment'] }}
        ];
        const cafeteriaData2 = [
            {{ $comparisonData['cafeteria_comparison']['period2']['food_quality'] }},
            {{ $comparisonData['cafeteria_comparison']['period2']['portion_satisfaction'] }},
            {{ $comparisonData['cafeteria_comparison']['period2']['menu_offered'] }},
            {{ $comparisonData['cafeteria_comparison']['period2']['menu_variety'] }},
            {{ $comparisonData['cafeteria_comparison']['period2']['food_temperature'] }},
            {{ $comparisonData['cafeteria_comparison']['period2']['dining_cleanliness'] }},
            {{ $comparisonData['cafeteria_comparison']['period2']['staff_treatment'] }}
        ];

        // Gráfica de Comparación de Cafetería
        const cafeteriaComparisonCtx = document.getElementById('cafeteriaComparisonChart').getContext('2d');
        const cafeteriaComparisonChart = new Chart(cafeteriaComparisonCtx, {
            type: 'radar',
            data: {
                labels: cafeteriaLabels,
                datasets: [{
                    label: '{{ $comparisonData["provider1"] }}',
                    data: cafeteriaData1,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    borderWidth: 2
                }, {
                    label: '{{ $comparisonData["provider2"] }}',
                    data: cafeteriaData2,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointBorderColor: '#fff',
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

        // Preparar datos de transporte
        const transportLabels = ['Puntualidad', 'Limpieza Vehículo', 'Atención Personal', 'Comunicación'];
        const transportData1 = [
            {{ $comparisonData['transport_comparison']['period1']['punctuality'] }},
            {{ $comparisonData['transport_comparison']['period1']['vehicle_cleanliness'] }},
            {{ $comparisonData['transport_comparison']['period1']['staff_treatment'] }},
            {{ $comparisonData['transport_comparison']['period1']['communication'] }}
        ];
        const transportData2 = [
            {{ $comparisonData['transport_comparison']['period2']['punctuality'] }},
            {{ $comparisonData['transport_comparison']['period2']['vehicle_cleanliness'] }},
            {{ $comparisonData['transport_comparison']['period2']['staff_treatment'] }},
            {{ $comparisonData['transport_comparison']['period2']['communication'] }}
        ];

        // Gráfica de Comparación de Transporte
        const transportComparisonCtx = document.getElementById('transportComparisonChart').getContext('2d');
        const transportComparisonChart = new Chart(transportComparisonCtx, {
            type: 'bar',
            data: {
                labels: transportLabels,
                datasets: [{
                    label: '{{ $comparisonData["provider1"] }}',
                    data: transportData1,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, {
                    label: '{{ $comparisonData["provider2"] }}',
                    data: transportData2,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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
        @endif
    </script>
@endsection
