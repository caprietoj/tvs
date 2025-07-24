@extends('adminlte::page')

@section('title', 'Análisis de Encuestas Padre-Estudiante')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-users text-primary"></i>
                <i class="fas fa-graduation-cap text-success"></i>
                Análisis de Encuestas Padre-Estudiante
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Encuestas</a></li>
                <li class="breadcrumb-item active">Padre-Estudiante</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    @if(!isset($dashboardData['has_data']) || !$dashboardData['has_data'])
        <!-- No Data State -->
        <div class="row">
            <div class="col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            No hay datos disponibles
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">¡Comencemos con el análisis!</h4>
                        <p class="text-muted">Para comenzar a analizar las encuestas padre-estudiante, primero debe cargar los datos de las encuestas.</p>
                        
                        <div class="mt-4">
                            <a href="{{ route('surveys.parent-student.upload') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i>
                                Cargar Primera Encuesta
                            </a>
                        </div>

                        <div class="mt-4">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info-circle"></i> Formato de archivo esperado:</h5>
                                <p class="mb-0">El sistema acepta archivos Excel (.xlsx) con las siguientes columnas:</p>
                                <ul class="text-left mt-2">
                                    <li>Información del estudiante (grado, proveedor, etc.)</li>
                                    <li>Preguntas de Cafetería (satisfacción con el servicio)</li>
                                    <li>Preguntas de Transporte (evaluación del servicio de transporte)</li>
                                    <li>Datos del período académico</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Dashboard with Data -->
        
        <!-- Control Panel -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs"></i>
                            Panel de Control
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('surveys.parent-student.upload') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-upload"></i>
                                    Cargar Nueva Encuesta
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#compareModal">
                                    <i class="fas fa-chart-bar"></i>
                                    Comparar Períodos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Resumen Ejecutivo -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        @php
                            $totalResponses = ($dashboardData['cafeteria_users'] ?? 0) + ($dashboardData['transport_users'] ?? 0);
                            $expectedResponses = 213;
                            $percentage = $expectedResponses > 0 ? round(($totalResponses / $expectedResponses) * 100, 1) : 0;
                        @endphp
                        <h3>{{ $totalResponses }}/{{ $expectedResponses }}</h3>
                        <p>Respuestas Obtenidas/Esperadas</p>
                        <small>Período: {{ $dashboardData['latest_period'] }} 
                            ({{ $percentage }}%)
                        </small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dashboardData['cafeteria_users'] }}</h3>
                        <p>Usuarios Cafetería</p>
                        <small>{{ round(($dashboardData['cafeteria_users'] / $dashboardData['total_responses']) * 100, 1) }}% del total</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $dashboardData['transport_users'] }}</h3>
                        <p>Usuarios Transporte</p>
                        <small>{{ round(($dashboardData['transport_users'] / $dashboardData['total_responses']) * 100, 1) }}% del total</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bus"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ count($dashboardData['grades']) }}</h3>
                        <p>Grados Participantes</p>
                        <small>
                            @if(count($dashboardData['grades']) > 0)
                                {{ implode(', ', array_slice(array_keys($dashboardData['grades']), 0, 2)) }}
                                @if(count($dashboardData['grades']) > 2)
                                    y {{ count($dashboardData['grades']) - 2 }} más
                                @endif
                            @else
                                Sin participación registrada
                            @endif
                        </small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis de Cafetería -->
        <div class="row">
            <div class="col-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-utensils"></i>
                            Análisis del Servicio de Cafetería
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach([
                                'calidad_sabor' => ['label' => 'Calidad y Sabor', 'icon' => 'fas fa-star', 'color' => 'primary'],
                                'porcion_satisfaccion' => ['label' => 'Satisfacción Porciones', 'icon' => 'fas fa-balance-scale', 'color' => 'success'],
                                'menu_calidad' => ['label' => 'Calidad del Menú', 'icon' => 'fas fa-list', 'color' => 'info'],
                                'variedad_menu' => ['label' => 'Variedad del Menú', 'icon' => 'fas fa-layer-group', 'color' => 'warning'],
                                'temperatura_adecuada' => ['label' => 'Temperatura', 'icon' => 'fas fa-thermometer-half', 'color' => 'danger'],
                                'limpieza_comedor' => ['label' => 'Limpieza', 'icon' => 'fas fa-broom', 'color' => 'secondary'],
                                'trato_personal' => ['label' => 'Trato Personal', 'icon' => 'fas fa-handshake', 'color' => 'dark']
                            ] as $key => $config)
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-{{ $config['color'] }}">
                                            <i class="{{ $config['icon'] }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $config['label'] }}</span>
                                            @php
                                                $percentage = $dashboardData['cafeteria'][$key] ?? 0;
                                                $totalResponses = $dashboardData['cafeteria']['total_respuestas'] ?? 0;
                                            @endphp
                                            <span class="info-box-number">{{ $percentage }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $config['color'] }}" 
                                                     style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                @if($totalResponses > 0 && $totalResponses < 5)
                                                    Basado en {{ $totalResponses }} {{ $totalResponses == 1 ? 'respuesta' : 'respuestas' }}
                                                @else
                                                    Satisfacción en {{ strtolower($config['label']) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Indicadores de Cafetería - {{ $dashboardData['latest_period'] }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="cafeteriaChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Distribución por Grado</h4>
                                    </div>
                                    <div class="card-body">
                                        <div style="position: relative; height: 200px;">
                                            <canvas id="gradeChart" style="width: 100%; height: 100%;"></canvas>
                                        </div>
                                        <div id="gradeChartMessage" style="display: none; text-align: center; padding: 20px; color: #6c757d;">
                                            <i class="fas fa-chart-pie fa-2x mb-2"></i>
                                            <p>No hay datos de distribución por grado disponibles</p>
                                        </div>
                                        
                                        @if(isset($dashboardData['grade_stats']) && $dashboardData['grade_stats'])
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div class="text-muted small">Total Grados</div>
                                                    <div class="fw-bold text-primary">{{ $dashboardData['grade_stats']['total_grades'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="text-muted small">Promedio/Grado</div>
                                                    <div class="fw-bold text-success">{{ $dashboardData['grade_stats']['average_per_grade'] ?? 0 }}</div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="text-muted small">Balance</div>
                                                    <div class="fw-bold text-info">{{ $dashboardData['grade_stats']['distribution_balance'] ?? 0 }}%</div>
                                                </div>
                                            </div>
                                            
                                            @if(isset($dashboardData['grade_stats']['most_represented']))
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-trophy text-warning"></i>
                                                    Más representado: <strong>{{ $dashboardData['grade_stats']['most_represented']['grade'] ?? 'N/A' }}</strong>
                                                    ({{ $dashboardData['grade_stats']['most_represented']['count'] ?? 0 }} respuestas)
                                                </small>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis de Transporte -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bus"></i>
                            Análisis del Servicio de Transporte
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach([
                                'puntualidad' => ['label' => 'Puntualidad', 'icon' => 'fas fa-clock', 'color' => 'success'],
                                'limpieza_vehiculo' => ['label' => 'Limpieza Vehículo', 'icon' => 'fas fa-spray-can', 'color' => 'info'],
                                'trato_personal' => ['label' => 'Trato Personal', 'icon' => 'fas fa-user-friends', 'color' => 'warning'],
                                'comunicacion' => ['label' => 'Comunicación', 'icon' => 'fas fa-comments', 'color' => 'primary']
                            ] as $key => $config)
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-{{ $config['color'] }}">
                                            <i class="{{ $config['icon'] }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">{{ $config['label'] }}</span>
                                            @php
                                                $percentage = $dashboardData['transport'][$key] ?? 0;
                                                $totalResponses = $dashboardData['transport']['total_respuestas'] ?? 0;
                                            @endphp
                                            <span class="info-box-number">{{ $percentage }}%</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-{{ $config['color'] }}" 
                                                     style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <span class="progress-description">
                                                @if($totalResponses > 0 && $totalResponses < 5)
                                                    Basado en {{ $totalResponses }} {{ $totalResponses == 1 ? 'respuesta' : 'respuestas' }}
                                                @else
                                                    Satisfacción en {{ strtolower($config['label']) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Indicadores de Transporte - {{ $dashboardData['latest_period'] }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="transportChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Participación por Grado</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Grado</th>
                                                        <th>Respuestas</th>
                                                        <th>%</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dashboardData['grades'] as $grade => $count)
                                                        <tr>
                                                            <td>{{ $grade }}</td>
                                                            <td>{{ $count }}</td>
                                                            <td>{{ round(($count / $dashboardData['total_responses']) * 100, 1) }}%</td>
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
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tasks"></i>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="callout callout-success">
                                    <h5><i class="fas fa-trophy"></i> Aspectos Destacados</h5>
                                    <p>Los indicadores con mejor puntuación del período actual.</p>
                                    <button class="btn btn-sm btn-success" onclick="showBestMetrics()">Ver Detalles</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="callout callout-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Oportunidades de Mejora</h5>
                                    <p>Áreas que requieren atención prioritaria para el próximo período.</p>
                                    <button class="btn btn-sm btn-warning" onclick="showImprovementAreas()">Ver Detalles</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comentarios Cualitativos -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-comments"></i> Comentarios Positivos</h5>
                                    <p>Lo que más valoran los padres de familia sobre los servicios.</p>
                                    <button class="btn btn-sm btn-info" onclick="showPositiveComments()">Ver Comentarios</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="callout callout-danger">
                                    <h5><i class="fas fa-lightbulb"></i> Sugerencias de Mejora</h5>
                                    <p>Ideas y sugerencias de los padres de familia.</p>
                                    <button class="btn btn-sm btn-danger" onclick="showImprovementComments()">Ver Sugerencias</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal para Comparación -->
<div class="modal fade" id="compareModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-chart-bar"></i>
                    Comparar Períodos
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('surveys.parent-student.comparison') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period1">Período Base:</label>
                                <select class="form-control" id="period1" name="period1" required>
                                    <option value="">Seleccione período base</option>
                                    @if(isset($periods))
                                        @foreach($periods as $period)
                                            <option value="{{ $period['id'] }}">{{ $period['label'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period2">Período de Comparación:</label>
                                <select class="form-control" id="period2" name="period2" required>
                                    <option value="">Seleccione período a comparar</option>
                                    @if(isset($periods))
                                        @foreach($periods as $period)
                                            <option value="{{ $period['id'] }}">{{ $period['label'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="service">Servicio:</label>
                                <select class="form-control" id="service" name="service">
                                    <option value="both">Ambos servicios</option>
                                    <option value="cafeteria">Solo Cafetería</option>
                                    <option value="transport">Solo Transporte</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i>
                        Generar Comparación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Aspectos Destacados -->
<div class="modal fade" id="bestMetricsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trophy"></i>
                    Aspectos Destacados
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Indicadores con puntuación igual o superior al 80%</p>
                
                <div class="row">
                    @php
                        $destacados = [];
                        // Cafetería
                        foreach(['calidad_sabor', 'porcion_satisfaccion', 'menu_calidad', 'variedad_menu', 'temperatura_adecuada', 'limpieza_comedor', 'trato_personal'] as $metric) {
                            $value = $dashboardData['cafeteria'][$metric] ?? 0;
                            if($value >= 80) {
                                $destacados[] = ['servicio' => 'Cafetería', 'metrica' => ucwords(str_replace('_', ' ', $metric)), 'valor' => $value];
                            }
                        }
                        // Transporte
                        foreach(['puntualidad', 'limpieza_vehiculo', 'trato_personal', 'comunicacion'] as $metric) {
                            $value = $dashboardData['transport'][$metric] ?? 0;
                            if($value >= 80) {
                                $destacados[] = ['servicio' => 'Transporte', 'metrica' => ucwords(str_replace('_', ' ', $metric)), 'valor' => $value];
                            }
                        }
                    @endphp
                    
                    @if(count($destacados) > 0)
                        @foreach($destacados as $destacado)
                            <div class="col-md-6 mb-2">
                                <div class="card border-success">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-success mb-1">{{ $destacado['metrica'] }}</h6>
                                        <p class="card-text mb-1">
                                            <small class="text-muted">{{ $destacado['servicio'] }}</small>
                                        </p>
                                        <h4 class="text-success mb-0">{{ $destacado['valor'] }}%</h4>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <i class="fas fa-info-circle text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted">No hay indicadores que superen el 80% en este período</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Oportunidades de Mejora -->
<div class="modal fade" id="improvementAreasModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Oportunidades de Mejora
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Indicadores con puntuación inferior al 70%</p>
                
                <div class="row">
                    @php
                        $mejoras = [];
                        // Cafetería
                        foreach(['calidad_sabor', 'porcion_satisfaccion', 'menu_calidad', 'variedad_menu', 'temperatura_adecuada', 'limpieza_comedor', 'trato_personal'] as $metric) {
                            $value = $dashboardData['cafeteria'][$metric] ?? 0;
                            if($value < 70 && $value > 0) {
                                $mejoras[] = ['servicio' => 'Cafetería', 'metrica' => ucwords(str_replace('_', ' ', $metric)), 'valor' => $value];
                            }
                        }
                        // Transporte
                        foreach(['puntualidad', 'limpieza_vehiculo', 'trato_personal', 'comunicacion'] as $metric) {
                            $value = $dashboardData['transport'][$metric] ?? 0;
                            if($value < 70 && $value > 0) {
                                $mejoras[] = ['servicio' => 'Transporte', 'metrica' => ucwords(str_replace('_', ' ', $metric)), 'valor' => $value];
                            }
                        }
                    @endphp
                    
                    @if(count($mejoras) > 0)
                        @foreach($mejoras as $mejora)
                            <div class="col-md-6 mb-2">
                                <div class="card border-warning">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-warning mb-1">{{ $mejora['metrica'] }}</h6>
                                        <p class="card-text mb-1">
                                            <small class="text-muted">{{ $mejora['servicio'] }}</small>
                                        </p>
                                        <h4 class="text-warning mb-0">{{ $mejora['valor'] }}%</h4>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                            <h5 class="text-success">¡Excelente rendimiento!</h5>
                            <p class="text-muted">No se encontraron indicadores con puntuación inferior al 70%</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Comentarios Positivos -->
<div class="modal fade" id="positiveCommentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-comments"></i>
                    Comentarios Positivos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Lo que más valoran los padres de familia sobre los servicios</p>
                
                @php
                    $comentariosPositivos = [];
                    // Obtener comentarios de transporte
                    $transportUsers = collect($dashboardData['transport_data'] ?? []);
                    foreach($transportUsers as $user) {
                        if(!empty($user->vehicle_cleanliness) && strlen($user->vehicle_cleanliness) > 20) {
                            $comentariosPositivos[] = ['servicio' => 'Transporte - Limpieza', 'comentario' => $user->vehicle_cleanliness];
                        }
                        if(!empty($user->staff_treatment_transport) && strlen($user->staff_treatment_transport) > 20) {
                            $comentariosPositivos[] = ['servicio' => 'Transporte - Trato', 'comentario' => $user->staff_treatment_transport];
                        }
                    }
                    // Limitar a los primeros 5 comentarios
                    $comentariosPositivos = array_slice($comentariosPositivos, 0, 5);
                @endphp
                
                @if(count($comentariosPositivos) > 0)
                    @foreach($comentariosPositivos as $index => $comentario)
                        <div class="mb-3 pb-3 @if($index < count($comentariosPositivos) - 1) border-bottom @endif">
                            <h6 class="text-info mb-1">{{ $comentario['servicio'] }}</h6>
                            <p class="mb-0">"{{ Str::limit($comentario['comentario'], 150) }}"</p>
                        </div>
                    @endforeach
                @else
                    <div class="text-center">
                        <i class="fas fa-comment-slash text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted">No hay comentarios disponibles en este período</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Sugerencias de Mejora -->
<div class="modal fade" id="improvementSuggestionsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-lightbulb"></i>
                    Sugerencias de Mejora
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Ideas y sugerencias de los padres de familia para optimizar los servicios</p>
                
                @php
                    $sugerencias = [];
                    // Obtener sugerencias de transporte
                    $transportUsers = collect($dashboardData['transport_data'] ?? []);
                    foreach($transportUsers as $user) {
                        if(!empty($user->staff_treatment_transport) && strlen($user->staff_treatment_transport) > 15) {
                            // Buscar sugerencias (que contengan palabras como "deberían", "podrían", "sería bueno", etc.)
                            if(stripos($user->staff_treatment_transport, 'debería') !== false || 
                               stripos($user->staff_treatment_transport, 'podría') !== false ||
                               stripos($user->staff_treatment_transport, 'sería') !== false ||
                               stripos($user->staff_treatment_transport, 'idealmente') !== false) {
                                $sugerencias[] = ['servicio' => 'Transporte', 'sugerencia' => $user->staff_treatment_transport];
                            }
                        }
                    }
                    // Limitar a las primeras 5 sugerencias
                    $sugerencias = array_slice($sugerencias, 0, 5);
                @endphp
                
                @if(count($sugerencias) > 0)
                    @foreach($sugerencias as $index => $sugerencia)
                        <div class="mb-3 pb-3 @if($index < count($sugerencias) - 1) border-bottom @endif">
                            <h6 class="text-danger mb-1">{{ $sugerencia['servicio'] }}</h6>
                            <p class="mb-0">"{{ Str::limit($sugerencia['sugerencia'], 200) }}"</p>
                        </div>
                    @endforeach
                @else
                    <div class="text-center">
                        <i class="fas fa-lightbulb text-muted mb-2" style="font-size: 2rem;"></i>
                        <p class="text-muted">No hay sugerencias específicas registradas en este período</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
    
    .btn {
        border-radius: 25px;
        padding: 12px 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    .progress {
        height: 6px;
        border-radius: 3px;
    }
    
    .progress-bar {
        border-radius: 3px;
    }
    
    .callout {
        border-radius: 10px;
        border-left: 5px solid;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        max-height: 300px;
    }
    
    .chart-container canvas {
        max-height: 100% !important;
    }
    
    .table-responsive {
        border-radius: 8px;
    }
    
    .modal-content {
        border-radius: 15px;
    }
    
    .alert {
        border-radius: 10px;
    }
    
    @media (max-width: 768px) {
        .small-box {
            margin-bottom: 15px;
        }
        
        .btn {
            padding: 8px 20px;
            font-size: 14px;
        }
    }
    
    /* Chart specific styles */
    .metric-improvement {
        color: #28a745;
        font-weight: bold;
    }
    
    .metric-decline {
        color: #dc3545;
        font-weight: bold;
    }
    
    .metric-stable {
        color: #6c757d;
        font-weight: bold;
    }
    
    .comparison-arrow {
        font-size: 1.2em;
        margin: 0 5px;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    
    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }
    
    .list-group-item {
        border: 1px solid rgba(0,0,0,.125);
        margin-bottom: 5px;
        border-radius: 8px;
    }
    
    .modal-lg {
        max-width: 900px;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .small-box {
            margin-bottom: 15px;
        }
        
        .btn {
            padding: 8px 20px;
            font-size: 14px;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// Global chart configurations
Chart.defaults.font.family = "'Source Sans Pro', sans-serif";
Chart.defaults.color = '#6c757d';

@if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
// Dashboard data
const dashboardData = @json($dashboardData);

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que Chart.js esté cargado
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está cargado');
        return;
    }
    
    initializeCafeteriaChart();
    initializeTransportChart();
    
    // Agregar un pequeño delay para el gráfico de grados
    setTimeout(function() {
        initializeGradeChart();
    }, 100);
});

function initializeCafeteriaChart() {
    const ctx = document.getElementById('cafeteriaChart');
    if (!ctx) {
        console.error('Cafeteria chart canvas element not found');
        return;
    }
    
    // Verificar si hay datos de cafetería disponibles
    if (!dashboardData || !dashboardData.cafeteria) {
        console.warn('No cafeteria data available');
        ctx.style.display = 'none';
        // Mostrar mensaje de no datos si existe un contenedor para ello
        const parentCard = ctx.closest('.card-body');
        if (parentCard) {
            parentCard.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-utensils fa-3x mb-3"></i>
                    <p>No hay datos de cafetería disponibles para este período</p>
                </div>
            `;
        }
        return;
    }
    
    const cafeteriaData = dashboardData.cafeteria;
    console.log('Cafeteria data:', cafeteriaData);
    
    // Verificar que tengamos al menos algunos datos válidos
    const metrics = ['calidad_sabor', 'porcion_satisfaccion', 'menu_calidad', 'variedad_menu', 'temperatura_adecuada', 'limpieza_comedor', 'trato_personal'];
    const hasValidData = metrics.some(metric => 
        cafeteriaData[metric] !== undefined && 
        cafeteriaData[metric] !== null && 
        !isNaN(cafeteriaData[metric])
    );
    
    if (!hasValidData) {
        console.warn('No valid cafeteria metrics found');
        ctx.style.display = 'none';
        const parentCard = ctx.closest('.card-body');
        if (parentCard) {
            parentCard.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <p>Los datos de cafetería no están disponibles o son inválidos</p>
                </div>
            `;
        }
        return;
    }
    
    const data = {
        labels: ['Calidad y Sabor', 'Porciones', 'Menú', 'Variedad', 'Temperatura', 'Limpieza', 'Trato Personal'],
        datasets: [{
            label: 'Satisfacción (%)',
            data: [
                cafeteriaData.calidad_sabor || 0,
                cafeteriaData.porcion_satisfaccion || 0,
                cafeteriaData.menu_calidad || 0,
                cafeteriaData.variedad_menu || 0,
                cafeteriaData.temperatura_adecuada || 0,
                cafeteriaData.limpieza_comedor || 0,
                cafeteriaData.trato_personal || 0
            ],
            backgroundColor: [
                'rgba(0, 123, 255, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)',
                'rgba(52, 58, 64, 0.8)'
            ],
            borderColor: [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(108, 117, 125, 1)',
                'rgba(52, 58, 64, 1)'
            ],
            borderWidth: 2,
            borderRadius: 4
        }]
    };

    console.log('Creating cafeteria chart with data:', data);

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 6,
                    callbacks: {
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
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            }
        }
    });
}

function initializeTransportChart() {
    const ctx = document.getElementById('transportChart');
    if (!ctx) {
        console.error('Transport chart canvas element not found');
        return;
    }
    
    // Verificar si hay datos de transporte disponibles
    if (!dashboardData || !dashboardData.transport) {
        console.warn('No transport data available');
        ctx.style.display = 'none';
        // Mostrar mensaje de no datos si existe un contenedor para ello
        const parentCard = ctx.closest('.card-body');
        if (parentCard) {
            parentCard.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-bus fa-3x mb-3"></i>
                    <p>No hay datos de transporte disponibles para este período</p>
                </div>
            `;
        }
        return;
    }
    
    const transportData = dashboardData.transport;
    console.log('Transport data:', transportData);
    
    // Verificar que tengamos al menos algunos datos válidos
    const metrics = ['puntualidad', 'limpieza_vehiculo', 'trato_personal', 'comunicacion'];
    const hasValidData = metrics.some(metric => 
        transportData[metric] !== undefined && 
        transportData[metric] !== null && 
        !isNaN(transportData[metric])
    );
    
    if (!hasValidData) {
        console.warn('No valid transport metrics found');
        ctx.style.display = 'none';
        const parentCard = ctx.closest('.card-body');
        if (parentCard) {
            parentCard.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <p>Los datos de transporte no están disponibles o son inválidos</p>
                </div>
            `;
        }
        return;
    }
    
    const data = {
        labels: ['Puntualidad', 'Limpieza Vehículo', 'Trato Personal', 'Comunicación'],
        datasets: [{
            label: 'Satisfacción (%)',
            data: [
                transportData.puntualidad || 0,
                transportData.limpieza_vehiculo || 0,
                transportData.trato_personal || 0,
                transportData.comunicacion || 0
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(0, 123, 255, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(0, 123, 255, 1)'
            ],
            borderWidth: 2,
            borderRadius: 4
        }]
    };

    console.log('Creating transport chart with data:', data);

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 6,
                    callbacks: {
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
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            }
        }
    });
}

function initializeGradeChart() {
    const ctx = document.getElementById('gradeChart');
    const messageDiv = document.getElementById('gradeChartMessage');
    
    console.log('Dashboard data:', dashboardData);
    console.log('Grades data:', dashboardData ? dashboardData.grades : 'No dashboard data');
    
    if (!ctx) {
        console.error('Canvas element not found');
        if (messageDiv) {
            messageDiv.style.display = 'block';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error: No se pudo encontrar el elemento canvas</p>';
        }
        return;
    }
    
    if (!dashboardData || !dashboardData.grades) {
        console.warn('No grades data available');
        if (messageDiv) {
            messageDiv.style.display = 'block';
        }
        ctx.style.display = 'none';
        return;
    }
    
    const grades = Object.keys(dashboardData.grades);
    const counts = Object.values(dashboardData.grades);
    
    console.log('Grades:', grades);
    console.log('Counts:', counts);
    
    if (grades.length === 0) {
        console.warn('No grades found in data');
        if (messageDiv) {
            messageDiv.style.display = 'block';
        }
        ctx.style.display = 'none';
        return;
    }
    
    // Ocultar mensaje y mostrar canvas
    if (messageDiv) {
        messageDiv.style.display = 'none';
    }
    ctx.style.display = 'block';
    
    createEnhancedGradeChart(dashboardData.grades, dashboardData.grade_stats, dashboardData.grades_detailed);
}

function createEnhancedGradeChart(gradeData, gradeStats, gradeDetailed) {
    console.log('Creating enhanced grade chart with data:', gradeData, gradeStats, gradeDetailed);
    
    const ctx = document.getElementById('gradeChart').getContext('2d');
    const grades = Object.keys(gradeData);
    const counts = Object.values(gradeData);
    const total = counts.reduce((a, b) => a + b, 0);
    
    console.log('Chart context:', ctx);
    console.log('Grades for chart:', grades);
    console.log('Counts for chart:', counts);
    console.log('Total:', total);
    
    // Colores simples
    const colors = [
        '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8',
        '#6c757d', '#343a40', '#ff5722', '#9c27b0', '#4caf50'
    ];
    
    // Crear el gráfico con configuración simplificada
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: grades,
            datasets: [{
                data: counts,
                backgroundColor: colors.slice(0, grades.length),
                borderWidth: 2,
                borderColor: '#ffffff'
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
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const count = context.parsed;
                            const percentage = ((count / total) * 100).toFixed(1);
                            return context.label + ': ' + count + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                duration: 800
            }
        }
    });
}

// Action functions
function generateReport() {
    window.open('{{ route('surveys.parent-student.report') }}', '_blank');
}

function exportData() {
    window.location.href = '{{ route('surveys.parent-student.export') }}';
}

function showBestMetrics() {
    @if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
    const cafeteriaMetrics = dashboardData.cafeteria;
    const transportMetrics = dashboardData.transport;
    
    let bestMetrics = [];
    
    // Mapeo de nombres técnicos a nombres amigables
    const metricNames = {
        'calidad_sabor': 'Calidad y Sabor',
        'porcion_satisfaccion': 'Satisfacción con Porciones',
        'menu_calidad': 'Calidad del Menú',
        'variedad_menu': 'Variedad del Menú',
        'temperatura_adecuada': 'Temperatura Adecuada',
        'limpieza_comedor': 'Limpieza del Comedor',
        'trato_personal': 'Trato del Personal',
        'puntualidad': 'Puntualidad',
        'limpieza_vehiculo': 'Limpieza del Vehículo',
        'comunicacion': 'Comunicación'
    };
    
    // Find best cafeteria metrics
    for (const [key, value] of Object.entries(cafeteriaMetrics)) {
        if (key !== 'total_usuarios' && key !== 'total_respuestas' && value >= 80) {
            bestMetrics.push({ 
                service: 'Cafetería', 
                metric: metricNames[key] || key, 
                value: value,
                icon: 'fas fa-utensils',
                color: 'success'
            });
        }
    }
    
    // Find best transport metrics  
    for (const [key, value] of Object.entries(transportMetrics)) {
        if (key !== 'total_usuarios' && key !== 'total_respuestas' && value >= 80) {
            bestMetrics.push({ 
                service: 'Transporte', 
                metric: metricNames[key] || key, 
                value: value,
                icon: 'fas fa-bus',
                color: 'info'
            });
        }
    }
    
    const contentDiv = document.getElementById('bestMetricsContent');
    const noMetricsDiv = document.getElementById('noMetricsMessage');
    
    if (bestMetrics.length > 0) {
        // Ordenar por valor descendente
        bestMetrics.sort((a, b) => b.value - a.value);
        
        let content = '<div class="row">';
        
        bestMetrics.forEach((metric, index) => {
            const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : '⭐';
            
            content += `
                <div class="col-md-6 mb-3">
                    <div class="best-metric-card">
                        <div class="metric-header">
                            <span class="metric-service">
                                <i class="${metric.icon}"></i> ${metric.service}
                            </span>
                            <span class="metric-medal">${medal}</span>
                        </div>
                        <div class="metric-name">${metric.metric}</div>
                        <div class="metric-value">${metric.value}%</div>
                        <div class="metric-rank">Posición #${index + 1}</div>
                    </div>
                </div>
            `;
        });
        
        content += '</div>';
        
        // Agregar resumen estadístico
        const avgScore = (bestMetrics.reduce((sum, metric) => sum + metric.value, 0) / bestMetrics.length).toFixed(1);
        const excellentCount = bestMetrics.filter(m => m.value >= 90).length;
        
        content += `
            <div class="mt-4">
                <div class="highlights-summary">
                    <div class="summary-title">
                        <i class="fas fa-chart-line"></i>
                        Resumen Estadístico
                    </div>
                    <div class="summary-stats">
                        <div class="summary-stat">
                            <span class="summary-stat-value">${bestMetrics.length}</span>
                            <span class="summary-stat-label">Aspectos Destacados</span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-stat-value">${avgScore}%</span>
                            <span class="summary-stat-label">Puntuación Promedio</span>
                        </div>
                        <div class="summary-stat">
                            <span class="summary-stat-value">${excellentCount}</span>
                            <span class="summary-stat-label">Excelentes (≥90%)</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        contentDiv.innerHTML = content;
        contentDiv.style.display = 'block';
        noMetricsDiv.style.display = 'none';
    } else {
        contentDiv.style.display = 'none';
        noMetricsDiv.style.display = 'block';
    }
    
    // Abrir el modal
    $('#bestMetricsModal').modal('show');
    @else
    // Si no hay datos, mostrar mensaje en el modal
    document.getElementById('bestMetricsContent').style.display = 'none';
    document.getElementById('noMetricsMessage').style.display = 'block';
    document.getElementById('noMetricsMessage').innerHTML = `
        <div class="alert alert-warning">
            <i class="fas fa-database fa-2x mb-3"></i>
            <h5>No hay datos disponibles</h5>
            <p>No se encontraron datos para mostrar métricas destacadas en este período.</p>
        </div>
    `;
    $('#bestMetricsModal').modal('show');
    @endif
}

function showImprovementAreas() {
    @if(isset($dashboardData['has_data']) && $dashboardData['has_data'])
    const cafeteriaMetrics = dashboardData.cafeteria;
    const transportMetrics = dashboardData.transport;
    
    let improvementAreas = [];
    
    // Find improvement areas in cafeteria
    for (const [key, value] of Object.entries(cafeteriaMetrics)) {
        if (key !== 'total_usuarios' && value < 70) {
            improvementAreas.push({ service: 'Cafetería', metric: key, value: value });
        }
    }
    
    // Find improvement areas in transport
    for (const [key, value] of Object.entries(transportMetrics)) {
        if (key !== 'total_usuarios' && value < 70) {
            improvementAreas.push({ service: 'Transporte', metric: key, value: value });
        }
    }
    
    const contentContainer = document.getElementById('improvementAreasContent');
    const noImprovementMessage = document.getElementById('noImprovementMessage');
    
    if (improvementAreas.length > 0) {
        let content = '<div class="row">';
        
        // Group by service
        const groupedAreas = {};
        improvementAreas.forEach(area => {
            if (!groupedAreas[area.service]) {
                groupedAreas[area.service] = [];
            }
            groupedAreas[area.service].push(area);
        });
        
        for (const [service, areas] of Object.entries(groupedAreas)) {
            content += `
                <div class="col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-${service === 'Cafetería' ? 'utensils' : 'bus'}"></i>
                                ${service}
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
            `;
            
            areas.forEach(area => {
                content += `
                    <li class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-chevron-right text-warning mr-2"></i>${getMetricDisplayName(area.metric)}</span>
                            <span class="badge badge-warning">${area.value}%</span>
                        </div>
                    </li>
                `;
            });
            
            content += `
                            </ul>
                        </div>
                    </div>
                </div>
            `;
        }
        
        content += '</div>';
        
        contentContainer.innerHTML = content;
        contentContainer.style.display = 'block';
        noImprovementMessage.style.display = 'none';
    } else {
        contentContainer.style.display = 'none';
        noImprovementMessage.style.display = 'block';
    }
    
    $('#improvementAreasModal').modal('show');
    @else
    const contentContainer = document.getElementById('improvementAreasContent');
    const noImprovementMessage = document.getElementById('noImprovementMessage');
    
    contentContainer.innerHTML = `
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h5>Sin datos disponibles</h5>
            <p>No hay datos disponibles para mostrar áreas de mejora en este período.</p>
        </div>
    `;
    contentContainer.style.display = 'block';
    noImprovementMessage.style.display = 'none';
    
    $('#improvementAreasModal').modal('show');
    @endif
}

// Función auxiliar para mostrar nombres amigables de las métricas
function getMetricDisplayName(metric) {
    const metricNames = {
        // Métricas de Cafetería
        'calidad_comida': 'Calidad de la Comida',
        'variedad_menu': 'Variedad del Menú',
        'tiempo_servicio': 'Tiempo de Servicio',
        'limpieza': 'Limpieza del Área',
        'atencion_personal': 'Atención del Personal',
        'precio_accesible': 'Precio Accesible',
        'ambiente': 'Ambiente del Lugar',
        
        // Métricas de Transporte
        'puntualidad': 'Puntualidad',
        'comodidad': 'Comodidad del Vehículo',
        'seguridad': 'Seguridad durante el Viaje',
        'limpieza_vehiculo': 'Limpieza del Vehículo',
        'trato_conductor': 'Trato del Conductor',
        'frecuencia': 'Frecuencia del Servicio',
        'accesibilidad': 'Accesibilidad del Servicio'
    };
    
    return metricNames[metric] || metric.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Función para generar reporte de mejora
function generateImprovementReport() {
    Swal.fire({
        title: '¡Funcionalidad en Desarrollo!',
        text: 'La generación de reportes de mejora estará disponible próximamente.',
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#ffc107'
    });
}

function showPositiveComments() {
    // Simular comentarios positivos (en producción, estos vendrían de la base de datos)
    const positiveComments = [
        {
            service: 'Cafetería',
            category: 'Calidad de la Comida',
            comment: 'La comida está muy rica y bien condimentada. Mi hijo siempre llega contento con lo que comió.',
            author: 'Padre de familia - 3er grado',
            date: '2025-07-15'
        },
        {
            service: 'Cafetería',
            category: 'Atención del Personal',
            comment: 'Las señoras de la cafetería son muy amables con los niños y siempre están dispuestas a ayudar.',
            author: 'Madre de familia - 5to grado',
            date: '2025-07-14'
        },
        {
            service: 'Transporte',
            category: 'Puntualidad',
            comment: 'El transporte escolar siempre llega puntual. Nunca hemos tenido problemas con los horarios.',
            author: 'Padre de familia - 2do grado',
            date: '2025-07-16'
        },
        {
            service: 'Transporte',
            category: 'Seguridad',
            comment: 'Me siento muy tranquila sabiendo que mi hija viaja segura. El conductor es muy responsable.',
            author: 'Madre de familia - 4to grado',
            date: '2025-07-13'
        },
        {
            service: 'Cafetería',
            category: 'Variedad del Menú',
            comment: 'Me gusta que tengan opciones variadas y que consideren las preferencias de los niños.',
            author: 'Padre de familia - 1er grado',
            date: '2025-07-12'
        },
        {
            service: 'Transporte',
            category: 'Comodidad',
            comment: 'Los asientos están en buen estado y mi hijo va cómodo durante el trayecto.',
            author: 'Madre de familia - 6to grado',
            date: '2025-07-11'
        }
    ];

    const contentContainer = document.getElementById('positiveCommentsContent');
    const noCommentsMessage = document.getElementById('noCommentsMessage');
    
    if (positiveComments.length > 0) {
        let content = '<div class="row">';
        
        // Group comments by service
        const groupedComments = {};
        positiveComments.forEach(comment => {
            if (!groupedComments[comment.service]) {
                groupedComments[comment.service] = [];
            }
            groupedComments[comment.service].push(comment);
        });
        
        for (const [service, comments] of Object.entries(groupedComments)) {
            content += `
                <div class="col-12 mb-4">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-${service === 'Cafetería' ? 'utensils' : 'bus'}"></i>
                                ${service}
                                <span class="badge badge-light text-info ml-2">${comments.length} comentario(s)</span>
                            </h5>
                        </div>
                        <div class="card-body">
            `;
            
            comments.forEach((comment, index) => {
                content += `
                    <div class="comment-item ${index !== comments.length - 1 ? 'border-bottom' : ''} pb-3 ${index !== 0 ? 'pt-3' : ''}">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="comment-content">
                                    <span class="badge badge-info badge-sm">${comment.category}</span>
                                    <blockquote class="blockquote mt-2">
                                        <p class="mb-2 font-italic">"${comment.comment}"</p>
                                        <footer class="blockquote-footer">
                                            <cite title="Source Title">${comment.author}</cite>
                                        </footer>
                                    </blockquote>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i>
                                    ${new Date(comment.date).toLocaleDateString('es-ES')}
                                </small>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-info" onclick="likeComment(this)" title="Me gusta">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span class="like-count">0</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            content += `
                        </div>
                    </div>
                </div>
            `;
        }
        
        content += '</div>';
        
        // Add summary statistics
        content += `
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-light border-info">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h5 class="text-info mb-1">${positiveComments.length}</h5>
                                <small class="text-muted">Total Comentarios</small>
                            </div>
                            <div class="col-md-4">
                                <h5 class="text-info mb-1">${Object.keys(groupedComments).length}</h5>
                                <small class="text-muted">Servicios Mencionados</small>
                            </div>
                            <div class="col-md-4">
                                <h5 class="text-info mb-1">95%</h5>
                                <small class="text-muted">Satisfacción General</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        contentContainer.innerHTML = content;
        contentContainer.style.display = 'block';
        noCommentsMessage.style.display = 'none';
    } else {
        contentContainer.style.display = 'none';
        noCommentsMessage.style.display = 'block';
    }
    
    $('#positiveCommentsModal').modal('show');
}

// Función para dar "me gusta" a un comentario
function likeComment(button) {
    const likeCountSpan = button.querySelector('.like-count');
    let currentCount = parseInt(likeCountSpan.textContent);
    
    if (button.classList.contains('btn-outline-info')) {
        // Dar like
        button.classList.remove('btn-outline-info');
        button.classList.add('btn-info');
        likeCountSpan.textContent = currentCount + 1;
        
        // Pequeña animación
        button.style.transform = 'scale(1.1)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
    } else {
        // Quitar like
        button.classList.remove('btn-info');
        button.classList.add('btn-outline-info');
        likeCountSpan.textContent = Math.max(0, currentCount - 1);
    }
}

// Función para exportar comentarios positivos
function exportPositiveComments() {
    Swal.fire({
        title: 'Exportar Comentarios',
        text: 'Se generará un archivo con todos los comentarios positivos.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Exportar PDF',
        cancelButtonText: 'Exportar Excel',
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Exportación en proceso!',
                text: 'Generando archivo PDF...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                title: '¡Exportación en proceso!',
                text: 'Generando archivo Excel...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Función para compartir comentarios positivos
function sharePositiveComments() {
    const shareText = `📝 Comentarios Positivos de Padres de Familia
🏫 Servicios de Cafetería y Transporte
⭐ Alta satisfacción con nuestros servicios
📅 Período: Julio 2025`;

    if (navigator.share) {
        navigator.share({
            title: 'Comentarios Positivos - Servicios Escolares',
            text: shareText,
            url: window.location.href
        }).catch(console.error);
    } else {
        // Fallback para navegadores que no soporten Web Share API
        navigator.clipboard.writeText(shareText).then(() => {
            Swal.fire({
                title: '¡Copiado al portapapeles!',
                text: 'El resumen de comentarios ha sido copiado al portapapeles.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(() => {
            Swal.fire({
                title: 'Compartir Comentarios',
                html: `
                    <textarea class="form-control" rows="4" readonly>${shareText}</textarea>
                    <small class="text-muted mt-2 d-block">Copia el texto de arriba para compartir</small>
                `,
                icon: 'info',
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#17a2b8'
            });
        });
    }
}

function showImprovementComments() {
    // Simular sugerencias de mejora (en producción, estos vendrían de la base de datos)
    const improvementSuggestions = [
        {
            service: 'Cafetería',
            category: 'Variedad del Menú',
            suggestion: 'Sería excelente incluir más opciones vegetarianas y veganas para los niños con dietas especiales.',
            priority: 'Alta',
            author: 'Madre de familia - 4to grado',
            date: '2025-07-18',
            status: 'Pendiente',
            votes: 12,
            feasibility: 'Factible'
        },
        {
            service: 'Cafetería',
            category: 'Tiempo de Servicio',
            suggestion: 'Implementar un sistema de pre-pedidos para reducir las colas durante el recreo.',
            priority: 'Media',
            author: 'Padre de familia - 2do grado',
            date: '2025-07-17',
            status: 'En Evaluación',
            votes: 8,
            feasibility: 'Requiere Estudio'
        },
        {
            service: 'Transporte',
            category: 'Comunicación',
            suggestion: 'Una aplicación móvil para rastrear la ubicación del bus en tiempo real sería muy útil.',
            priority: 'Alta',
            author: 'Madre de familia - 6to grado',
            date: '2025-07-16',
            status: 'Aprobada',
            votes: 15,
            feasibility: 'Factible'
        },
        {
            service: 'Transporte',
            category: 'Comodidad',
            suggestion: 'Instalar aire acondicionado en los buses para mayor comodidad durante las épocas de calor.',
            priority: 'Media',
            author: 'Padre de familia - 3er grado',
            date: '2025-07-15',
            status: 'Pendiente',
            votes: 10,
            feasibility: 'Costoso'
        },
        {
            service: 'Cafetería',
            category: 'Limpieza',
            suggestion: 'Colocar dispensadores de gel antibacterial en todas las mesas del comedor.',
            priority: 'Alta',
            author: 'Madre de familia - 1er grado',
            date: '2025-07-14',
            status: 'Implementada',
            votes: 18,
            feasibility: 'Factible'
        },
        {
            service: 'Transporte',
            category: 'Seguridad',
            suggestion: 'Implementar cinturones de seguridad individuales en todos los asientos del transporte.',
            priority: 'Alta',
            author: 'Padre de familia - 5to grado',
            date: '2025-07-13',
            status: 'En Evaluación',
            votes: 14,
            feasibility: 'Requiere Inversión'
        }
    ];

    const contentContainer = document.getElementById('improvementSuggestionsContent');
    const noSuggestionsMessage = document.getElementById('noSuggestionsMessage');
    
    if (improvementSuggestions.length > 0) {
        let content = '<div class="row">';
        
        // Group suggestions by service
        const groupedSuggestions = {};
        improvementSuggestions.forEach(suggestion => {
            if (!groupedSuggestions[suggestion.service]) {
                groupedSuggestions[suggestion.service] = [];
            }
            groupedSuggestions[suggestion.service].push(suggestion);
        });
        
        for (const [service, suggestions] of Object.entries(groupedSuggestions)) {
            content += `
                <div class="col-12 mb-4">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-${service === 'Cafetería' ? 'utensils' : 'bus'}"></i>
                                ${service}
                                <span class="badge badge-light text-danger ml-2">${suggestions.length} sugerencia(s)</span>
                            </h5>
                        </div>
                        <div class="card-body">
            `;
            
            suggestions.forEach((suggestion, index) => {
                const statusClass = getStatusClass(suggestion.status);
                const priorityClass = getPriorityClass(suggestion.priority);
                const feasibilityClass = getFeasibilityClass(suggestion.feasibility);
                
                content += `
                    <div class="suggestion-item ${index !== suggestions.length - 1 ? 'border-bottom' : ''} pb-3 ${index !== 0 ? 'pt-3' : ''}">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="suggestion-content">
                                    <div class="suggestion-header mb-2">
                                        <span class="badge badge-danger badge-sm">${suggestion.category}</span>
                                        <span class="badge ${priorityClass} badge-sm ml-1">Prioridad ${suggestion.priority}</span>
                                        <span class="badge ${statusClass} badge-sm ml-1">${suggestion.status}</span>
                                    </div>
                                    <blockquote class="blockquote-suggestion mt-2">
                                        <p class="mb-2">${suggestion.suggestion}</p>
                                        <footer class="blockquote-footer">
                                            <cite title="Source Title">${suggestion.author}</cite>
                                        </footer>
                                    </blockquote>
                                    <div class="suggestion-meta mt-2">
                                        <small class="text-muted">
                                            <span class="badge ${feasibilityClass} badge-sm">${suggestion.feasibility}</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <small class="text-muted mb-2 d-block">
                                    <i class="fas fa-calendar-alt"></i>
                                    ${new Date(suggestion.date).toLocaleDateString('es-ES')}
                                </small>
                                <div class="suggestion-actions">
                                    <button class="btn btn-sm btn-outline-success mb-1" onclick="voteSuggestion(this)" title="Votar">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span class="vote-count">${suggestion.votes}</span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info mb-1" onclick="commentSuggestion(this)" title="Comentar">
                                        <i class="fas fa-comment"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="shareSuggestion(this)" title="Compartir">
                                        <i class="fas fa-share"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            content += `
                        </div>
                    </div>
                </div>
            `;
        }
        
        content += '</div>';
        
        // Add summary statistics
        const totalVotes = improvementSuggestions.reduce((sum, s) => sum + s.votes, 0);
        const implementedCount = improvementSuggestions.filter(s => s.status === 'Implementada').length;
        const highPriorityCount = improvementSuggestions.filter(s => s.priority === 'Alta').length;
        
        content += `
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-light border-danger">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h5 class="text-danger mb-1">${improvementSuggestions.length}</h5>
                                <small class="text-muted">Total Sugerencias</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="text-success mb-1">${implementedCount}</h5>
                                <small class="text-muted">Implementadas</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="text-warning mb-1">${highPriorityCount}</h5>
                                <small class="text-muted">Alta Prioridad</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="text-info mb-1">${totalVotes}</h5>
                                <small class="text-muted">Total Votos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        contentContainer.innerHTML = content;
        contentContainer.style.display = 'block';
        noSuggestionsMessage.style.display = 'none';
    } else {
        contentContainer.style.display = 'none';
        noSuggestionsMessage.style.display = 'block';
    }
    
    $('#improvementSuggestionsModal').modal('show');
}

// Funciones auxiliares para el modal de sugerencias
function getStatusClass(status) {
    const statusClasses = {
        'Pendiente': 'badge-secondary',
        'En Evaluación': 'badge-warning',
        'Aprobada': 'badge-info',
        'Implementada': 'badge-success',
        'Rechazada': 'badge-dark'
    };
    return statusClasses[status] || 'badge-secondary';
}

function getPriorityClass(priority) {
    const priorityClasses = {
        'Alta': 'badge-danger',
        'Media': 'badge-warning',
        'Baja': 'badge-info'
    };
    return priorityClasses[priority] || 'badge-info';
}

function getFeasibilityClass(feasibility) {
    const feasibilityClasses = {
        'Factible': 'badge-success',
        'Requiere Estudio': 'badge-warning',
        'Requiere Inversión': 'badge-info',
        'Costoso': 'badge-danger',
        'No Factible': 'badge-dark'
    };
    return feasibilityClasses[feasibility] || 'badge-secondary';
}

// Función para votar por una sugerencia
function voteSuggestion(button) {
    const voteCountSpan = button.querySelector('.vote-count');
    let currentCount = parseInt(voteCountSpan.textContent);
    
    if (button.classList.contains('btn-outline-success')) {
        // Dar voto
        button.classList.remove('btn-outline-success');
        button.classList.add('btn-success');
        voteCountSpan.textContent = currentCount + 1;
        
        // Pequeña animación
        button.style.transform = 'scale(1.1)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
        
        // Mostrar mensaje de agradecimiento
        Swal.fire({
            title: '¡Voto registrado!',
            text: 'Gracias por apoyar esta sugerencia.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    } else {
        // Quitar voto
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-success');
        voteCountSpan.textContent = Math.max(0, currentCount - 1);
    }
}

// Función para comentar una sugerencia
function commentSuggestion(button) {
    Swal.fire({
        title: 'Comentar Sugerencia',
        html: `
            <textarea id="suggestionComment" class="form-control" rows="4" placeholder="Escribe tu comentario sobre esta sugerencia..."></textarea>
            <div class="mt-2">
                <small class="text-muted">Tu comentario ayudará a enriquecer esta propuesta</small>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Enviar Comentario',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            const comment = document.getElementById('suggestionComment').value;
            if (!comment.trim()) {
                Swal.showValidationMessage('Por favor, escribe un comentario');
                return false;
            }
            return comment;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Comentario enviado!',
                text: 'Tu comentario ha sido registrado exitosamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Función para compartir una sugerencia
function shareSuggestion(button) {
    const suggestionItem = button.closest('.suggestion-item');
    const suggestionText = suggestionItem.querySelector('blockquote p').textContent;
    const category = suggestionItem.querySelector('.badge-danger').textContent;
    
    const shareText = `💡 Sugerencia de Mejora: ${category}
    
"${suggestionText}"

🏫 Servicios Escolares - Encuesta Padres de Familia`;

    if (navigator.share) {
        navigator.share({
            title: 'Sugerencia de Mejora - Servicios Escolares',
            text: shareText,
            url: window.location.href
        }).catch(console.error);
    } else {
        navigator.clipboard.writeText(shareText).then(() => {
            Swal.fire({
                title: '¡Copiado al portapapeles!',
                text: 'La sugerencia ha sido copiada al portapapeles.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }).catch(() => {
            Swal.fire({
                title: 'Compartir Sugerencia',
                html: `
                    <textarea class="form-control" rows="6" readonly>${shareText}</textarea>
                    <small class="text-muted mt-2 d-block">Copia el texto de arriba para compartir</small>
                `,
                icon: 'info',
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#dc3545'
            });
        });
    }
}

// Función para exportar sugerencias
function exportSuggestions() {
    Swal.fire({
        title: 'Exportar Sugerencias',
        text: 'Se generará un archivo con todas las sugerencias de mejora.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Exportar PDF',
        cancelButtonText: 'Exportar Excel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Exportación en proceso!',
                text: 'Generando reporte PDF de sugerencias...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
                title: '¡Exportación en proceso!',
                text: 'Generando archivo Excel de sugerencias...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Función para priorizar sugerencias
function prioritizeSuggestions() {
    Swal.fire({
        title: 'Priorizar Sugerencias',
        html: `
            <div class="text-left">
                <p><strong>Criterios de priorización:</strong></p>
                <ul style="text-align: left; display: inline-block;">
                    <li>Impacto en la satisfacción del usuario</li>
                    <li>Factibilidad de implementación</li>
                    <li>Recursos requeridos</li>
                    <li>Número de votos recibidos</li>
                </ul>
                <div class="mt-3">
                    <label for="priorityFilter">Filtrar por prioridad:</label>
                    <select id="priorityFilter" class="form-control">
                        <option value="all">Todas las prioridades</option>
                        <option value="Alta">Solo Alta prioridad</option>
                        <option value="Media">Solo Media prioridad</option>
                        <option value="Baja">Solo Baja prioridad</option>
                    </select>
                </div>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Aplicar Filtro',
        confirmButtonColor: '#ffc107',
        showCancelButton: true,
        cancelButtonText: 'Cerrar'
    }).then((result) => {
        if (result.isConfirmed) {
            const filter = document.getElementById('priorityFilter').value;
            // Aquí se implementaría la lógica de filtrado
            Swal.fire({
                title: '¡Filtro aplicado!',
                text: `Mostrando sugerencias: ${filter === 'all' ? 'Todas' : filter + ' prioridad'}`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Función para marcar sugerencia como implementada
function implementSuggestion() {
    Swal.fire({
        title: '¿Marcar como implementada?',
        text: 'Esta acción cambiará el estado de la sugerencia seleccionada.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar como implementada',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Sugerencia implementada!',
                text: 'El estado ha sido actualizado exitosamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Form validation for comparison modal
document.getElementById('compareModal').addEventListener('show.bs.modal', function() {
    document.getElementById('period1').value = '';
    document.getElementById('period2').value = '';
});

// Prevent selecting same periods
document.getElementById('period1').addEventListener('change', function() {
    const period2Select = document.getElementById('period2');
    const selectedValue = this.value;
    
    for (let option of period2Select.options) {
        option.disabled = option.value === selectedValue;
    }
});

document.getElementById('period2').addEventListener('change', function() {
    const period1Select = document.getElementById('period1');
    const selectedValue = this.value;
    
    for (let option of period1Select.options) {
        option.disabled = option.value === selectedValue;
    }
});

// Funciones para los modales simplificados
function showBestMetrics() {
    $('#bestMetricsModal').modal('show');
}

function showImprovementAreas() {
    $('#improvementAreasModal').modal('show');
}

function showPositiveComments() {
    $('#positiveCommentsModal').modal('show');
}

function showImprovementComments() {
    $('#improvementSuggestionsModal').modal('show');
}
@endif
</script>
@stop