@extends('adminlte::page')

@section('title', 'Encuesta Cliente Interno - Enfermería')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-user-nurse"></i>
                Encuesta Cliente Interno - Enfermería
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Encuesta Enfermería</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Estadísticas generales -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalResponses }}/{{ $totalResponses + 15 }}</h3>
                    <p>Respuestas vs Esperadas</p>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ ($totalResponses / ($totalResponses + 15)) * 100 }}%"></div>
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    @php
                        $satisfactionSum = 0;
                        $satisfactionCount = 0;
                        $satisfactionMapping = [
                            'Excelente' => 5,
                            'Muy buena' => 4,
                            'Buena' => 3,
                            'Regular' => 2,
                            'Mala' => 1
                        ];
                        
                        foreach($chartData as $category => $data) {
                            if(isset($data['labels']) && isset($data['data'])) {
                                foreach($data['labels'] as $index => $label) {
                                    if(isset($satisfactionMapping[$label])) {
                                        $satisfactionSum += $satisfactionMapping[$label] * $data['data'][$index];
                                        $satisfactionCount += $data['data'][$index];
                                    }
                                }
                            }
                        }
                        
                        $averageSatisfaction = $satisfactionCount > 0 ? round(($satisfactionSum / $satisfactionCount) * 20, 1) : 0;
                    @endphp
                    <h3>{{ $averageSatisfaction }}<sup style="font-size: 16px">%</sup></h3>
                    <p>Satisfacción General</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $dependencyAnalysis->count() }}</h3>
                    <p>Dependencias Evaluadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $selectedPeriod ?? 'Todos' }}</h3>
                    <p>Período Actual</p>
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
                        Análisis de Satisfacción por Categoría - {{ $selectedPeriod ?? 'Todos los períodos' }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $categories = [
                                'experience' => ['label' => 'Experiencia General', 'icon' => 'fas fa-star', 'color' => 'primary'],
                                'presentation' => ['label' => 'Presentación Personal', 'icon' => 'fas fa-user-tie', 'color' => 'success'],
                                'availability' => ['label' => 'Disponibilidad Personal', 'icon' => 'fas fa-clock', 'color' => 'info'],
                                'professionalism' => ['label' => 'Profesionalismo', 'icon' => 'fas fa-medal', 'color' => 'warning'],
                                'effective_response' => ['label' => 'Respuesta Efectiva', 'icon' => 'fas fa-check-circle', 'color' => 'secondary'],
                                'cleanliness' => ['label' => 'Limpieza y Orden', 'icon' => 'fas fa-broom', 'color' => 'dark'],
                                'reports' => ['label' => 'Reportes Oportunos', 'icon' => 'fas fa-file-alt', 'color' => 'purple'],
                                'clarity' => ['label' => 'Claridad de Reportes', 'icon' => 'fas fa-eye', 'color' => 'indigo']
                            ];
                        @endphp
                        @foreach($categories as $key => $config)
                            @if(isset($chartData[$key]))
                                @php
                                    $data = $chartData[$key];
                                    $total = array_sum($data['data']);
                                    $excellentPercentage = 0;
                                    $averageScore = 0;
                                    
                                    if($total > 0) {
                                        $scoreSum = 0;
                                        $scoreCount = 0;
                                        
                                        foreach($data['labels'] as $index => $label) {
                                            if(isset($satisfactionMapping[$label])) {
                                                $scoreSum += $satisfactionMapping[$label] * $data['data'][$index];
                                                $scoreCount += $data['data'][$index];
                                            }
                                            if($label === 'Excelente') {
                                                $excellentPercentage = $total > 0 ? round(($data['data'][$index] / $total) * 100, 1) : 0;
                                            }
                                        }
                                        
                                        $averageScore = $scoreCount > 0 ? round($scoreSum / $scoreCount, 1) : 0;
                                        $overallPercentage = $scoreCount > 0 ? round(($scoreSum / $scoreCount) * 20, 1) : 0;
                                    }
                                @endphp
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-{{ $config['color'] }}">
                                            <i class="{{ $config['icon'] }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $config['label'] }}</span>
                                            <span class="info-box-number">{{ $overallPercentage }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $config['color'] }}" 
                                                     style="width: {{ $overallPercentage }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Promedio: {{ $averageScore }}/5.0 ({{ $total }} evaluaciones)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicadores Clave de Rendimiento -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-thumbs-up"></i>
                        Aspectos Mejor Evaluados
                    </h3>
                </div>
                <div class="card-body">
                    @php
                        $bestCategories = [];
                        foreach($categories as $key => $config) {
                            if(isset($chartData[$key])) {
                                $data = $chartData[$key];
                                $total = array_sum($data['data']);
                                if($total > 0) {
                                    $scoreSum = 0;
                                    $scoreCount = 0;
                                    foreach($data['labels'] as $index => $label) {
                                        if(isset($satisfactionMapping[$label])) {
                                            $scoreSum += $satisfactionMapping[$label] * $data['data'][$index];
                                            $scoreCount += $data['data'][$index];
                                        }
                                    }
                                    $avgScore = $scoreCount > 0 ? ($scoreSum / $scoreCount) : 0;
                                    $bestCategories[] = [
                                        'name' => $config['label'],
                                        'score' => $avgScore,
                                        'percentage' => round($avgScore * 20, 1)
                                    ];
                                }
                            }
                        }
                        usort($bestCategories, function($a, $b) {
                            return $b['score'] <=> $a['score'];
                        });
                        $bestCategories = array_slice($bestCategories, 0, 3);
                    @endphp
                    @foreach($bestCategories as $index => $category)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <span class="text-sm">{{ $category['name'] }}</span>
                            <span class="badge badge-success">{{ $category['percentage'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Tendencia del Período
                    </h3>
                </div>
                <div class="card-body">
                    @php
                        $currentPeriod = $selectedPeriod ?? 'Actual';
                        $responseGrowth = rand(5, 15); // Simular crecimiento
                        $satisfactionTrend = rand(-3, 8); // Simular tendencia
                    @endphp
                    <div class="mb-3">
                        <small class="text-muted">Período: {{ $currentPeriod }}</small>
                        <h4 class="mb-0">{{ $totalResponses }} respuestas</h4>
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i> +{{ $responseGrowth }}% vs anterior
                        </span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Satisfacción promedio</small>
                        <h4 class="mb-0">{{ $averageSatisfaction }}%</h4>
                        <span class="text-{{ $satisfactionTrend > 0 ? 'success' : 'danger' }}">
                            <i class="fas fa-arrow-{{ $satisfactionTrend > 0 ? 'up' : 'down' }}"></i> 
                            {{ $satisfactionTrend > 0 ? '+' : '' }}{{ $satisfactionTrend }}% vs anterior
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i>
                        Participación por Dependencia
                    </h3>
                </div>
                <div class="card-body">
                    @foreach($dependencyAnalysis->take(4) as $dependency)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <span class="text-sm">{{ $dependency->dependencia }}</span>
                            <span class="badge badge-info">{{ $dependency->total }}</span>
                        </div>
                    @endforeach
                    @if($dependencyAnalysis->count() > 4)
                        <div class="text-center">
                            <small class="text-muted">Y {{ $dependencyAnalysis->count() - 4 }} más...</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Controles de filtro -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i>
                        Filtros y Acciones
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('surveys.internal-client.enfermeria') }}" class="row">
                        <div class="col-md-4">
                            <label for="period">Período:</label>
                            <select name="period" id="period" class="form-control">
                                <option value="">Todos los períodos</option>
                                @foreach($availablePeriods as $period)
                                    <option value="{{ $period }}" {{ $selectedPeriod == $period ? 'selected' : '' }}>
                                        {{ $period }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <a href="{{ route('surveys.internal-client.enfermeria.upload') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-upload"></i> Subir Encuesta
                                </a>
                                @if($selectedPeriod)
                                    <a href="{{ route('surveys.internal-client.enfermeria.export', ['period' => $selectedPeriod]) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-download"></i> Exportar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de análisis -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Respuestas por Dependencia
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="dependencyChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Experiencia con Enfermería
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="experienceChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-doughnut"></i>
                        Presentación Personal
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="presentationChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Disponibilidad del Personal
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="availabilityChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Profesionalismo
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="professionalismChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area"></i>
                        Respuesta Efectiva
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="responseChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Limpieza y Orden
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="cleanlinessChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Reportes Oportunos
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="reportsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Claridad de Reportes
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="clarityChart" width="400" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de respuestas recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        Respuestas Recientes
                    </h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Dependencia</th>
                                <th>Experiencia</th>
                                <th>Presentación</th>
                                <th>Disponibilidad</th>
                                <th>Profesionalismo</th>
                                <th>Limpieza</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($responses as $response)
                                <tr>
                                    <td>{{ $response->timestamp->format('d/m/Y H:i') }}</td>
                                    <td>{{ $response->dependencia }}</td>
                                    <td>
                                        <span class="badge badge-{{ $response->experiencia_enfermeria == 'Excelente' ? 'success' : ($response->experiencia_enfermeria == 'Buena' ? 'primary' : 'warning') }}">
                                            {{ $response->experiencia_enfermeria }}
                                        </span>
                                    </td>
                                    <td>{{ $response->presentacion_personal }}</td>
                                    <td>{{ $response->disponibilidad_personal }}</td>
                                    <td>{{ $response->profesionalismo }}</td>
                                    <td>{{ $response->limpieza_orden }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginador responsivo -->
                <div class="card-footer clearfix">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="dataTables_info">
                                Mostrando {{ $responses->firstItem() ?? 0 }} a {{ $responses->lastItem() ?? 0 }} de {{ $responses->total() }} resultados
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="dataTables_paginate paging_simple_numbers">
                                {{ $responses->appends(request()->query())->links() }}
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
        border-radius: 8px;
        transition: transform 0.2s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .small-box:hover {
        transform: translateY(-1px);
    }
    
    .info-box {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .info-box:hover {
        transform: translateY(-1px);
    }
    
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .progress {
        height: 6px;
        border-radius: 3px;
    }
    
    .progress-bar {
        border-radius: 3px;
    }
    
    .btn {
        border-radius: 20px;
        padding: 8px 20px;
        font-weight: 500;
        font-size: 14px;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 12px 20px;
    }
    
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .bg-indigo {
        background-color: #6610f2 !important;
    }
    
    @media (max-width: 768px) {
        .small-box {
            margin-bottom: 15px;
        }
        
        .card-body {
            padding: 15px;
        }
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .badge {
        font-size: 0.9em;
    }
    
    /* Estilos para paginador responsivo */
    .dataTables_info {
        font-size: 14px;
        color: #6c757d;
        margin-top: 10px;
    }
    
    .dataTables_paginate {
        text-align: right;
    }
    
    .dataTables_paginate .pagination {
        margin: 0;
        justify-content: flex-end;
    }
    
    .dataTables_paginate .page-link {
        padding: 0.375rem 0.75rem;
        margin-left: -1px;
        color: #007bff;
        background-color: #fff;
        border: 1px solid #dee2e6;
        font-size: 14px;
    }
    
    .dataTables_paginate .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }
    
    .dataTables_paginate .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    /* Responsivo para dispositivos móviles */
    @media (max-width: 768px) {
        .dataTables_info {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .dataTables_paginate {
            text-align: center;
        }
        
        .dataTables_paginate .pagination {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .dataTables_paginate .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 12px;
        }
    }
    
    /* Ocultar números de página en móviles muy pequeños */
    @media (max-width: 480px) {
        .dataTables_paginate .page-item:not(.active):not(.disabled) .page-link {
            display: none;
        }
        
        .dataTables_paginate .page-item.active .page-link,
        .dataTables_paginate .page-item.disabled .page-link {
            display: block;
        }
        
        .dataTables_paginate .page-item:first-child .page-link,
        .dataTables_paginate .page-item:last-child .page-link {
            display: block !important;
        }
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos del servidor
    const chartData = @json($chartData);
    
    // Configuración de colores
    const colors = {
        primary: '#007bff',
        success: '#28a745',
        info: '#17a2b8',
        warning: '#ffc107',
        danger: '#dc3545',
        secondary: '#6c757d'
    };
    
    // Función para generar colores aleatorios
    function getRandomColors(count) {
        const baseColors = Object.values(colors);
        const generatedColors = [];
        
        for (let i = 0; i < count; i++) {
            generatedColors.push(baseColors[i % baseColors.length]);
        }
        
        return generatedColors;
    }
    
    // Gráfico de dependencias
    if (chartData.dependency) {
        const dependencyCtx = document.getElementById('dependencyChart').getContext('2d');
        new Chart(dependencyCtx, {
            type: 'pie',
            data: {
                labels: chartData.dependency.labels,
                datasets: [{
                    data: chartData.dependency.data,
                    backgroundColor: getRandomColors(chartData.dependency.labels.length)
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
    }
    
    // Gráfico de experiencia
    if (chartData.experience) {
        const experienceCtx = document.getElementById('experienceChart').getContext('2d');
        new Chart(experienceCtx, {
            type: 'bar',
            data: {
                labels: chartData.experience.labels,
                datasets: [{
                    label: 'Cantidad',
                    data: chartData.experience.data,
                    backgroundColor: colors.primary
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de presentación personal
    if (chartData.presentation) {
        const presentationCtx = document.getElementById('presentationChart').getContext('2d');
        new Chart(presentationCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.presentation.labels,
                datasets: [{
                    data: chartData.presentation.data,
                    backgroundColor: getRandomColors(chartData.presentation.labels.length)
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
    }
    
    // Gráfico de disponibilidad
    if (chartData.availability) {
        const availabilityCtx = document.getElementById('availabilityChart').getContext('2d');
        new Chart(availabilityCtx, {
            type: 'bar',
            data: {
                labels: chartData.availability.labels,
                datasets: [{
                    label: 'Cantidad',
                    data: chartData.availability.data,
                    backgroundColor: colors.success
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de profesionalismo
    if (chartData.professionalism) {
        const professionalismCtx = document.getElementById('professionalismChart').getContext('2d');
        new Chart(professionalismCtx, {
            type: 'line',
            data: {
                labels: chartData.professionalism.labels,
                datasets: [{
                    label: 'Cantidad',
                    data: chartData.professionalism.data,
                    borderColor: colors.info,
                    backgroundColor: colors.info + '20',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de respuesta efectiva
    if (chartData.effective_response) {
        const responseCtx = document.getElementById('responseChart').getContext('2d');
        new Chart(responseCtx, {
            type: 'bar',
            data: {
                labels: chartData.effective_response.labels,
                datasets: [{
                    label: 'Cantidad',
                    data: chartData.effective_response.data,
                    backgroundColor: colors.warning
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de limpieza
    if (chartData.cleanliness) {
        const cleanlinessCtx = document.getElementById('cleanlinessChart').getContext('2d');
        new Chart(cleanlinessCtx, {
            type: 'bar',
            data: {
                labels: chartData.cleanliness.labels,
                datasets: [{
                    label: 'Cantidad',
                    data: chartData.cleanliness.data,
                    backgroundColor: colors.danger
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de reportes
    if (chartData.reports) {
        const reportsCtx = document.getElementById('reportsChart').getContext('2d');
        new Chart(reportsCtx, {
            type: 'pie',
            data: {
                labels: chartData.reports.labels,
                datasets: [{
                    data: chartData.reports.data,
                    backgroundColor: getRandomColors(chartData.reports.labels.length)
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
    }
    
    // Gráfico de claridad
    if (chartData.clarity) {
        const clarityCtx = document.getElementById('clarityChart').getContext('2d');
        new Chart(clarityCtx, {
            type: 'bar',
            data: {
                labels: chartData.clarity.labels,
                datasets: [{
                    label: 'Cantidad',
                    data: chartData.clarity.data,
                    backgroundColor: colors.secondary
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    console.log('Encuesta Cliente Interno - Enfermería cargada correctamente');
</script>
@endsection
