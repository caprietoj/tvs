@extends('adminlte::page')

@section('title', 'Análisis Comparativo - Servicios Complementarios')

@push('css')
<style>
    .border-left-success {
        border-left: 0.25rem solid #28a745 !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #ffc107 !important;
    }
    .action-items .action-item {
        padding: 0.75rem;
        border-radius: 0.375rem;
        background-color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0.5rem;
        transition: all 0.2s ease-in-out;
    }
    .action-items .action-item:hover {
        background-color: rgba(255, 255, 255, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }
    .action-icon {
        width: 40px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    .action-icon i {
        font-size: 1.2rem;
    }
    .action-content {
        flex: 1;
    }
    .action-content strong {
        color: #495057;
        font-size: 0.95rem;
    }
    .action-content ul {
        font-size: 0.875rem;
        color: #6c757d;
        padding-left: 1.2rem;
    }
    .action-content ul li {
        margin-bottom: 0.25rem;
        line-height: 1.4;
    }
    .metric-box {
        padding: 1rem;
        border-radius: 0.5rem;
        background-color: rgba(255, 255, 255, 0.9);
        transition: all 0.2s ease-in-out;
    }
    .metric-box:hover {
        background-color: rgba(255, 255, 255, 1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .h-100 {
        height: 100% !important;
    }
    .shadow {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
</style>
@endpush

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-chart-line text-primary"></i> Análisis Comparativo de Encuestas de Satisfacción</h1>
            @if(isset($comparisonData))
                <div class="mt-2">
                    @if(isset($comparisonData['service']) && $comparisonData['service'] !== 'both')
                        <span class="badge badge-primary mr-2">
                            <i class="fas fa-filter"></i> 
                            @if($comparisonData['service'] === 'cafeteria')
                                Solo Cafetería
                            @elseif($comparisonData['service'] === 'transport')
                                Solo Transporte
                            @endif
                        </span>
                    @endif
                    @if(isset($comparisonData['dependency']) && $comparisonData['dependency'] !== 'all')
                        <span class="badge badge-secondary">
                            <i class="fas fa-building"></i> {{ $comparisonData['dependency'] }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
        <a href="{{ route('surveys.complementary-services.transport.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    @if(isset($comparisonData) && !empty($comparisonData))
    
    <!-- Resumen Ejecutivo Mejorado -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-analytics"></i> Dashboard Ejecutivo de Comparación
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    {{ $comparisonData['period1'] }} vs {{ $comparisonData['period2'] }}
                </span>
                <button type="button" class="btn btn-tool" data-toggle="tooltip" title="Exportar Dashboard">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Metodología y Alertas -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Metodología de Evaluación</h5>
                        <p>Las variables fueron normalizadas y categorizadas automáticamente. 
                           Se aplicaron filtros de calidad para garantizar la confiabilidad de los resultados.</p>
                        <p><strong>Comparativo {{ $comparisonData['period1'] }} vs {{ $comparisonData['period2'] }}:</strong></p>
                        @if(isset($comparisonData['service']) && $comparisonData['service'] !== 'both')
                            <p><span class="badge badge-primary"><i class="fas fa-filter"></i> Servicio: 
                                @if($comparisonData['service'] === 'cafeteria')
                                    Solo Cafetería
                                @elseif($comparisonData['service'] === 'transport')
                                    Solo Transporte
                                @endif
                            </span></p>
                        @endif
                        @if(isset($comparisonData['dependency']) && $comparisonData['dependency'] !== 'all')
                            <p><span class="badge badge-secondary"><i class="fas fa-building"></i> Dependencia: {{ $comparisonData['dependency'] }}</span></p>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    @if(isset($comparisonData['alerts']) && count($comparisonData['alerts']) > 0)
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Alertas Automáticas</h6>
                            @foreach($comparisonData['alerts'] as $alert)
                                <small class="d-block">• {{ $alert }}</small>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Sin Alertas</h6>
                            <small>Todos los indicadores dentro de rangos normales</small>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Métricas Principales -->
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas {{ $comparisonData['period1'] }}</span>
                            <span class="info-box-number">{{ $comparisonData['responses_period1'] ?? 0 }}</span>
                            <div class="progress">
                                @php
                                    $maxResponses = max($comparisonData['responses_period1'] ?? 0, $comparisonData['responses_period2'] ?? 0);
                                    $period1Width = $maxResponses > 0 ? (($comparisonData['responses_period1'] ?? 0) / $maxResponses) * 100 : 50;
                                @endphp
                                <div class="progress-bar" style="width: {{ $period1Width }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas {{ $comparisonData['period2'] }}</span>
                            <span class="info-box-number">{{ $comparisonData['responses_period2'] ?? 0 }}</span>
                            <div class="progress">
                                @php
                                    $period2Width = $maxResponses > 0 ? (($comparisonData['responses_period2'] ?? 0) / $maxResponses) * 100 : 50;
                                @endphp
                                <div class="progress-bar bg-success" style="width: {{ $period2Width }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Variación de Respuestas</span>
                            <span class="info-box-number">
                                @php
                                    $responses1 = $comparisonData['responses_period1'] ?? 0;
                                    $responses2 = $comparisonData['responses_period2'] ?? 0;
                                    $responseChange = $responses1 > 0 
                                        ? round((($responses2 - $responses1) / $responses1) * 100, 1)
                                        : 0;
                                    $changeIcon = $responseChange > 0 ? 'fa-arrow-up text-success' : 
                                                 ($responseChange < 0 ? 'fa-arrow-down text-danger' : 'fa-minus text-warning');
                                @endphp
                                <i class="fas {{ $changeIcon }}"></i> 
                                {{ $responseChange > 0 ? '+' : '' }}{{ $responseChange }}%
                            </span>
                            @if($responseChange == 0 && $responses1 > 0)
                                <small class="d-block text-muted">Sin variación</small>
                            @elseif($responses1 == 0)
                                <small class="d-block text-muted">Sin datos anteriores</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    @php
                        $trend = $comparisonData['general_trend'] ?? 'Positiva';
                        $trendIcon = $trend === 'Positiva' ? 'fa-arrow-up text-success' : 
                                    ($trend === 'Negativa' ? 'fa-arrow-down text-danger' : 'fa-arrows-alt-h text-warning');
                        $trendBgColor = $trend === 'Positiva' ? 'bg-gradient-success' : 
                                       ($trend === 'Negativa' ? 'bg-gradient-danger' : 'bg-gradient-warning');
                    @endphp
                    <div class="info-box {{ $trendBgColor }}">
                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tendencia General</span>
                            <span class="info-box-number">
                                <i class="fas {{ $trendIcon }}"></i> {{ $trend }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información específica de Dependencia -->
            @if(isset($comparisonData['dependency']) && $comparisonData['dependency'] !== 'all')
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card bg-light border-left-info">
                        <div class="card-body">
                            <h6 class="mb-2"><i class="fas fa-building text-info"></i> Análisis Específico por Dependencia</h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="mb-1"><strong>Dependencia Analizada:</strong> {{ $comparisonData['dependency'] }}</p>
                                    <p class="mb-1"><strong>Alcance del Análisis:</strong> Este informe presenta los resultados específicos para la dependencia seleccionada.</p>
                                    <small class="text-muted">Los datos han sido filtrados para mostrar únicamente las respuestas de {{ $comparisonData['dependency'] }}.</small>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="d-flex justify-content-around">
                                            <div>
                                                <span class="text-primary font-weight-bold h5">{{ $comparisonData['responses_period1'] ?? 0 }}</span>
                                                <br><small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                            </div>
                                            <div>
                                                <span class="text-success font-weight-bold h5">{{ $comparisonData['responses_period2'] ?? 0 }}</span>
                                                <br><small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">Respuestas por período</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Categorización de Respuestas -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <h5><i class="fas fa-chart-pie"></i> Resumen de Satisfacción por Servicio</h5>
                    <div class="row">
                        @if(isset($comparisonData['service']) && ($comparisonData['service'] === 'both' || $comparisonData['service'] === 'cafeteria'))
                        <div class="col-md-{{ ($comparisonData['service'] === 'both') ? '6' : '12' }}">
                            <div class="metric-display">
                                <h6><i class="fas fa-utensils text-success"></i> Servicio de Cafetería</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <span class="badge badge-info">{{ $comparisonData['period1'] }}</span>
                                        <strong>
                                            @if(isset($comparisonData['cafeteria_period1']['calidad_sabor']))
                                                {{ number_format(($comparisonData['cafeteria_period1']['calidad_sabor'] + 
                                                                 $comparisonData['cafeteria_period1']['porcion_satisfaccion'] + 
                                                                 $comparisonData['cafeteria_period1']['menu_calidad']) / 3, 1) }}%
                                            @else
                                                No hay datos
                                            @endif
                                        </strong>
                                        <small class="d-block text-muted">Promedio General</small>
                                    </div>
                                    <div class="col-6">
                                        <span class="badge badge-success">{{ $comparisonData['period2'] }}</span>
                                        <strong>
                                            @if(isset($comparisonData['cafeteria_period2']['calidad_sabor']))
                                                {{ number_format(($comparisonData['cafeteria_period2']['calidad_sabor'] + 
                                                                 $comparisonData['cafeteria_period2']['porcion_satisfaccion'] + 
                                                                 $comparisonData['cafeteria_period2']['menu_calidad']) / 3, 1) }}%
                                            @else
                                                No hay datos
                                            @endif
                                        </strong>
                                        <small class="d-block text-muted">Promedio General</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(isset($comparisonData['service']) && ($comparisonData['service'] === 'both' || $comparisonData['service'] === 'transport'))
                        <div class="col-md-{{ ($comparisonData['service'] === 'both') ? '6' : '12' }}">
                            <div class="metric-display">
                                <h6><i class="fas fa-bus text-warning"></i> Servicio de Transporte</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <span class="badge badge-info">{{ $comparisonData['period1'] }}</span>
                                        <strong>
                                            @if(isset($comparisonData['transport_period1']['puntualidad']))
                                                {{ number_format(($comparisonData['transport_period1']['puntualidad'] + 
                                                                 $comparisonData['transport_period1']['limpieza_vehiculo'] + 
                                                                 $comparisonData['transport_period1']['trato_personal']) / 3, 1) }}%
                                            @else
                                                No hay datos
                                            @endif
                                        </strong>
                                        <small class="d-block text-muted">Promedio General</small>
                                    </div>
                                    <div class="col-6">
                                        <span class="badge badge-success">{{ $comparisonData['period2'] }}</span>
                                        <strong>
                                            @if(isset($comparisonData['transport_period2']['puntualidad']))
                                                {{ number_format(($comparisonData['transport_period2']['puntualidad'] + 
                                                                 $comparisonData['transport_period2']['limpieza_vehiculo'] + 
                                                                 $comparisonData['transport_period2']['trato_personal']) / 3, 1) }}%
                                            @else
                                                No hay datos
                                            @endif
                                        </strong>
                                        <small class="d-block text-muted">Promedio General</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis del Servicio de Cafetería -->
    @if(isset($comparisonData['service']) && ($comparisonData['service'] === 'both' || $comparisonData['service'] === 'cafeteria'))
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-utensils"></i> ANÁLISIS DEL SERVICIO DE CAFETERÍA
                @if(isset($comparisonData['dependency']) && $comparisonData['dependency'] !== 'all')
                    <span class="badge badge-light ml-2">{{ $comparisonData['dependency'] }}</span>
                @endif
            </h3>
        </div>
        <div class="card-body">
            @if($comparisonData['cafeteria_period1']['total_usuarios'] > 0 || $comparisonData['cafeteria_period2']['total_usuarios'] > 0)
                
                <!-- Calidad y Sabor -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Calidad y Sabor:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['cafeteria_period1']['calidad_sabor'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['cafeteria_period2']['calidad_sabor'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['cafeteria_differences']['calidad_sabor']))
                                        @php $diff = $comparisonData['cafeteria_differences']['calidad_sabor']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period1']['calidad_sabor'] }}%">
                                        {{ $comparisonData['cafeteria_period1']['calidad_sabor'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['calidad_sabor'] }}%">
                                        {{ $comparisonData['cafeteria_period2']['calidad_sabor'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Porciones Ofrecidas -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Porciones Ofrecidas:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['cafeteria_period1']['porcion_satisfaccion'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['cafeteria_period2']['porcion_satisfaccion'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['cafeteria_differences']['porcion_satisfaccion']))
                                        @php $diff = $comparisonData['cafeteria_differences']['porcion_satisfaccion']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period1']['porcion_satisfaccion'] }}%">
                                        {{ $comparisonData['cafeteria_period1']['porcion_satisfaccion'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['porcion_satisfaccion'] }}%">
                                        {{ $comparisonData['cafeteria_period2']['porcion_satisfaccion'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menú Ofrecido -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Menú Ofrecido:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['cafeteria_period1']['menu_calidad'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['cafeteria_period2']['menu_calidad'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['cafeteria_differences']['menu_calidad']))
                                        @php $diff = $comparisonData['cafeteria_differences']['menu_calidad']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period1']['menu_calidad'] }}%">
                                        {{ $comparisonData['cafeteria_period1']['menu_calidad'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['menu_calidad'] }}%">
                                        {{ $comparisonData['cafeteria_period2']['menu_calidad'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variedad del Menú -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Variedad del Menú:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['cafeteria_period1']['variedad_menu'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['cafeteria_period2']['variedad_menu'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['cafeteria_differences']['variedad_menu']))
                                        @php $diff = $comparisonData['cafeteria_differences']['variedad_menu']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period1']['variedad_menu'] }}%">
                                        {{ $comparisonData['cafeteria_period1']['variedad_menu'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['variedad_menu'] }}%">
                                        {{ $comparisonData['cafeteria_period2']['variedad_menu'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Temperatura -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Temperatura:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['cafeteria_period1']['temperatura_adecuada'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['cafeteria_period2']['temperatura_adecuada'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['cafeteria_differences']['temperatura_adecuada']))
                                        @php $diff = $comparisonData['cafeteria_differences']['temperatura_adecuada']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period1']['temperatura_adecuada'] }}%">
                                        {{ $comparisonData['cafeteria_period1']['temperatura_adecuada'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['temperatura_adecuada'] }}%">
                                        {{ $comparisonData['cafeteria_period2']['temperatura_adecuada'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Limpieza -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Limpieza:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['cafeteria_period1']['limpieza_comedor'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['cafeteria_period2']['limpieza_comedor'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['cafeteria_differences']['limpieza_comedor']))
                                        @php $diff = $comparisonData['cafeteria_differences']['limpieza_comedor']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period1']['limpieza_comedor'] }}%">
                                        {{ $comparisonData['cafeteria_period1']['limpieza_comedor'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['limpieza_comedor'] }}%">
                                        {{ $comparisonData['cafeteria_period2']['limpieza_comedor'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trato del Personal -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Trato del Personal:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['cafeteria_period1']['trato_personal'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['cafeteria_period2']['trato_personal'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['cafeteria_differences']['trato_personal']))
                                        @php $diff = $comparisonData['cafeteria_differences']['trato_personal']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period1']['trato_personal'] }}%">
                                        {{ $comparisonData['cafeteria_period1']['trato_personal'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['trato_personal'] }}%">
                                        {{ $comparisonData['cafeteria_period2']['trato_personal'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No hay datos suficientes del servicio de cafetería para los períodos seleccionados.
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Análisis del Servicio de Transporte -->
    @if(isset($comparisonData['service']) && ($comparisonData['service'] === 'both' || $comparisonData['service'] === 'transport'))
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bus"></i> ANÁLISIS DEL SERVICIO DE TRANSPORTE
                @if(isset($comparisonData['dependency']) && $comparisonData['dependency'] !== 'all')
                    <span class="badge badge-light ml-2">{{ $comparisonData['dependency'] }}</span>
                @endif
            </h3>
        </div>
        <div class="card-body">
            @if($comparisonData['transport_period1']['total_usuarios'] > 0 || $comparisonData['transport_period2']['total_usuarios'] > 0)
                
                <div class="alert alert-info">
                    <p>En el caso del servicio de transporte, se conserva el mismo aliado estratégico, destacando los resultados obtenidos y el trabajo realizado.</p>
                    <p><strong>Las variables consideradas en la encuesta fueron:</strong></p>
                </div>

                <!-- Puntualidad -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Puntualidad:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['transport_period1']['puntualidad'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['transport_period2']['puntualidad'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['transport_differences']['puntualidad']))
                                        @php $diff = $comparisonData['transport_differences']['puntualidad']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['transport_period1']['puntualidad'] }}%">
                                        {{ $comparisonData['transport_period1']['puntualidad'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['transport_period2']['puntualidad'] }}%">
                                        {{ $comparisonData['transport_period2']['puntualidad'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Limpieza de los Vehículos -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Limpieza de los Vehículos:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['transport_period1']['limpieza_vehiculo'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['transport_period2']['limpieza_vehiculo'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['transport_differences']['limpieza_vehiculo']))
                                        @php $diff = $comparisonData['transport_differences']['limpieza_vehiculo']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['transport_period1']['limpieza_vehiculo'] }}%">
                                        {{ $comparisonData['transport_period1']['limpieza_vehiculo'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['transport_period2']['limpieza_vehiculo'] }}%">
                                        {{ $comparisonData['transport_period2']['limpieza_vehiculo'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trato del Personal -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Trato del Personal:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['transport_period1']['trato_personal'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['transport_period2']['trato_personal'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['transport_differences']['trato_personal']))
                                        @php $diff = $comparisonData['transport_differences']['trato_personal']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['transport_period1']['trato_personal'] }}%">
                                        {{ $comparisonData['transport_period1']['trato_personal'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['transport_period2']['trato_personal'] }}%">
                                        {{ $comparisonData['transport_period2']['trato_personal'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comunicación Oportuna y Asertiva -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="text-primary"><strong>Comunicación Oportuna y Asertiva:</strong></h5>
                            <ul class="list-unstyled ml-3">
                                <li>• <strong>Usuarios:</strong></li>
                                <li class="ml-3">
                                    {{ $comparisonData['period1'] }}: <span class="badge badge-info">{{ $comparisonData['transport_period1']['comunicacion'] }}%</span> 
                                    Vs {{ $comparisonData['period2'] }}: <span class="badge badge-success">{{ $comparisonData['transport_period2']['comunicacion'] }}%</span>
                                </li>
                                <li class="ml-3">
                                    @if(isset($comparisonData['transport_differences']['comunicacion']))
                                        @php $diff = $comparisonData['transport_differences']['comunicacion']; @endphp
                                        <span class="{{ $diff['trend_class'] }}">
                                            <strong>{{ ucfirst($diff['trend']) }} indicador: {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%</strong>
                                        </span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-2">
                                <small class="text-muted">{{ $comparisonData['period1'] }}</small>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-info" style="width: {{ $comparisonData['transport_period1']['comunicacion'] }}%">
                                        {{ $comparisonData['transport_period1']['comunicacion'] }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $comparisonData['period2'] }}</small>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $comparisonData['transport_period2']['comunicacion'] }}%">
                                        {{ $comparisonData['transport_period2']['comunicacion'] }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No hay datos suficientes del servicio de transporte para los períodos seleccionados.
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Análisis Específico de Proveedores -->
    @if(isset($comparisonData['provider_analysis']) && $comparisonData['provider_analysis'])
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exchange-alt"></i> ANÁLISIS ESPECÍFICO: SAPORE vs ALDIMARK
            </h3>
            <div class="card-tools">
                <span class="badge badge-warning">Cambio de Proveedor</span>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <h5><i class="fas fa-info-circle"></i> Impacto del Cambio de Proveedor</h5>
                <p>Análisis detallado del impacto en la satisfacción tras el cambio de Sapore a Aldimark en el servicio de cafetería.</p>
                <p><strong>Período de Transición:</strong> {{ $comparisonData['transition_period'] ?? 'Mayo - Octubre 2024' }}</p>
            </div>

            <!-- Comparación de Proveedores -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light provider-card">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-utensils"></i> SAPORE (Proveedor Anterior)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-12">
                                    <h4 class="text-danger">{{ $comparisonData['sapore_overall'] ?? '25.8' }}%</h4>
                                    <small>Satisfacción General</small>
                                </div>
                            </div>
                            
                            <h6><i class="fas fa-chart-bar"></i> Métricas Detalladas:</h6>
                            @php
                                $saporeMetrics = [
                                    'Calidad y Sabor' => ['value' => '7.54', 'families' => '31.4'],
                                    'Porciones' => ['value' => '9.4', 'families' => '38.8'],
                                    'Menú' => ['value' => '38.8', 'families' => '37.0'],
                                    'Variedad' => ['value' => '22.6', 'families' => '25.9'],
                                    'Temperatura' => ['value' => '39.6', 'families' => '44.4'],
                                    'Limpieza' => ['value' => '62.3', 'families' => '70.4']
                                ];
                            @endphp
                            
                            @foreach($saporeMetrics as $metric => $data)
                                <div class="provider-comparison decline">
                                    <div>
                                        <strong>{{ $metric }}</strong><br>
                                        <small>Personal: {{ $data['value'] }}% | Familias: {{ $data['families'] }}%</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-danger">{{ number_format(($data['value'] + $data['families']) / 2, 1) }}%</span>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="mt-3">
                                <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Áreas Críticas Identificadas:</h6>
                                <ul class="list-unstyled text-sm">
                                    <li>• <strong>Calidad y Sabor:</strong> Muy baja satisfacción (7.54%)</li>
                                    <li>• <strong>Porciones:</strong> Insuficientes según usuarios</li>
                                    <li>• <strong>Variedad:</strong> Menú limitado y repetitivo</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-light provider-card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-utensils"></i> ALDIMARK (Proveedor Actual)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-12">
                                    <h4 class="text-success">{{ $comparisonData['aldimark_overall'] ?? '58.2' }}%</h4>
                                    <small>Satisfacción General</small>
                                </div>
                            </div>
                            
                            <h6><i class="fas fa-chart-bar"></i> Métricas Detalladas:</h6>
                            @php
                                $aldimarkMetrics = [
                                    'Calidad y Sabor' => ['value' => '32.5', 'families' => '42.0'],
                                    'Porciones' => ['value' => '48.8', 'families' => '74.4'],
                                    'Menú' => ['value' => '86.0', 'families' => '42.5'],
                                    'Variedad' => ['value' => '58.9', 'families' => '62.2'],
                                    'Temperatura' => ['value' => '76.0', 'families' => '81.1'],
                                    'Limpieza' => ['value' => '97.7', 'families' => '95.6']
                                ];
                                
                                $improvements = [
                                    'Calidad y Sabor' => '+24.96%',
                                    'Porciones' => '+39.4%',
                                    'Menú' => '+47.16%',
                                    'Variedad' => '+36.3%',
                                    'Temperatura' => '+36.4%',
                                    'Limpieza' => '+35.3%'
                                ];
                            @endphp
                            
                            @foreach($aldimarkMetrics as $metric => $data)
                                <div class="provider-comparison improvement">
                                    <div>
                                        <strong>{{ $metric }}</strong><br>
                                        <small>Personal: {{ $data['value'] }}% | Familias: {{ $data['families'] }}%</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success">{{ number_format(($data['value'] + $data['families']) / 2, 1) }}%</span>
                                        <small class="text-success d-block">{{ $improvements[$metric] }}</small>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="mt-3">
                                <h6 class="text-success"><i class="fas fa-check-circle"></i> Mejoras Destacadas:</h6>
                                <ul class="list-unstyled text-sm">
                                    <li>• <strong>Menú:</strong> Mejor aceptación (+47.16%)</li>
                                    <li>• <strong>Porciones:</strong> Más adecuadas (+39.4%)</li>
                                    <li>• <strong>Variedad:</strong> Mayor diversidad (+36.3%)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análisis de Correlaciones por Grado -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-graduation-cap"></i> Correlación de Satisfacción por Grado Escolar</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @php
                                    $gradeCorrelations = [
                                        'PK-K' => ['sapore' => '28.5', 'aldimark' => '65.2', 'improvement' => '+36.7'],
                                        '1°-5°' => ['sapore' => '22.1', 'aldimark' => '58.9', 'improvement' => '+36.8'],
                                        '6°-8°' => ['sapore' => '18.3', 'aldimark' => '52.4', 'improvement' => '+34.1'],
                                        '9°-11°' => ['sapore' => '35.7', 'aldimark' => '61.8', 'improvement' => '+26.1']
                                    ];
                                @endphp
                                
                                @foreach($gradeCorrelations as $grade => $data)
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <h6 class="text-info">{{ $grade }}</h6>
                                            <div class="mb-2">
                                                <small class="text-muted">Sapore</small><br>
                                                <span class="badge badge-danger">{{ $data['sapore'] }}%</span>
                                            </div>
                                            <div class="mb-2">
                                                <small class="text-muted">Aldimark</small><br>
                                                <span class="badge badge-success">{{ $data['aldimark'] }}%</span>
                                            </div>
                                            <small class="text-success"><strong>{{ $data['improvement'] }}</strong></small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-3">
                                <h6><i class="fas fa-lightbulb"></i> Insights por Edad:</h6>
                                <ul class="list-unstyled">
                                    <li>• <strong>Primera Infancia (PK-K):</strong> Mayor mejoría en satisfacción (+36.7%)</li>
                                    <li>• <strong>Primaria (1°-5°):</strong> Consistente mejoría across all metrics</li>
                                    <li>• <strong>Bachillerato (9°-11°):</strong> Menor pero significativa mejoría (+26.1%)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Plan de Acción para Mejora Continua -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i> PLAN DE ACCIÓN PARA MEJORA CONTINUA
                @if(isset($comparisonData['dependency']) && $comparisonData['dependency'] !== 'all')
                    <span class="badge badge-light ml-2">{{ $comparisonData['dependency'] }}</span>
                @endif
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    <i class="fas fa-clipboard-check"></i> Estrategia 2025
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <h5 class="mb-2"><i class="fas fa-lightbulb"></i> Enfoque Estratégico</h5>
                <p class="mb-1">Toma de decisiones enfocadas en la mejora continua del proceso basada en los resultados del análisis comparativo.</p>
                @if(isset($comparisonData['dependency']) && $comparisonData['dependency'] !== 'all')
                    <p class="mb-0"><strong>Enfoque específico para:</strong> {{ $comparisonData['dependency'] }}</p>
                    <small class="text-muted">Las acciones propuestas están orientadas a las necesidades específicas identificadas en esta dependencia.</small>
                @endif
            </div>
            
            <div class="row">
                @if(isset($comparisonData['service']) && ($comparisonData['service'] === 'both' || $comparisonData['service'] === 'cafeteria'))
                <div class="col-md-{{ ($comparisonData['service'] === 'both') ? '6' : '12' }}">
                    <div class="card bg-light border-left-success shadow h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-utensils"></i> Servicio de Cafetería
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="action-items">
                                <div class="action-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-clipboard-list text-success"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Calidad y Procesos</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Validación de recetas estándar</li>
                                                <li>Seguimiento permanente del personal manipulador de alimentos</li>
                                                <li>Verificación de calidad de insumos</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-balance-scale text-warning"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Porciones y Equipos</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Verificación permanente de menaje para garantizar porciones adecuadas</li>
                                                <li>Verificación permanente de equipos para conservar temperaturas</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-users text-primary"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Planificación y Seguimiento</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Reuniones semanales para verificación de propuestas de menús</li>
                                                <li>Apoyo del Comité de alimentos en la estructura de menús balanceados</li>
                                                <li>Retroalimentaciones permanentes al equipo de trabajo</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-item">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-broom text-info"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Higiene y Ambiente</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Revisiones permanentes del sitio y zonas de mayor circulación</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(isset($comparisonData['service']) && ($comparisonData['service'] === 'both' || $comparisonData['service'] === 'transport'))
                <div class="col-md-{{ ($comparisonData['service'] === 'both') ? '6' : '12' }}">
                    <div class="card bg-light border-left-warning shadow h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-bus"></i> Servicio de Transporte
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="action-items">
                                <div class="action-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-handshake text-primary"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Acuerdos y Capacitación</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Elaboración y divulgación de acuerdos esenciales entre las partes</li>
                                                <li>Capacitación permanente del personal operativo</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-tools text-success"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Soporte Técnico</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Respuestas oportunas con trazabilidad y soportes técnicos</li>
                                                <li>Reporte oportuno de novedades a través de herramientas de apoyo</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-car text-warning"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Operación y Mantenimiento</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Verificación permanente del estado de los vehículos</li>
                                                <li>Registros actualizados y reales de los recorridos diarios</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="action-item">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon">
                                            <i class="fas fa-comments text-info"></i>
                                        </div>
                                        <div class="action-content">
                                            <strong>Retroalimentación</strong>
                                            <ul class="mt-1 mb-0">
                                                <li>Retroalimentación permanente del estado del servicio</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @else
    <!-- Formulario de Selección de Períodos y Filtros Avanzados -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Configuración de Análisis Comparativo Avanzado
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Tabs para diferentes tipos de análisis -->
            <ul class="nav nav-tabs" id="analysisTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab">
                        <i class="fas fa-chart-line"></i> Análisis Básico
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="provider-tab" data-toggle="tab" href="#provider" role="tab">
                        <i class="fas fa-exchange-alt"></i> Cambio de Proveedor
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="grade-tab" data-toggle="tab" href="#grade" role="tab">
                        <i class="fas fa-graduation-cap"></i> Por Grado Escolar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="route-tab" data-toggle="tab" href="#route" role="tab">
                        <i class="fas fa-route"></i> Por Ruta
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="analysisTabContent">
                <!-- Tab de Análisis Básico -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Análisis Comparativo Estándar</h5>
                        <p class="mb-0">Compare dos períodos específicos con filtros básicos de segmentación.</p>
                    </div>
                    
                    <form action="{{ route('surveys.complementary-services.transport.compare') }}" method="GET" id="basicAnalysisForm">
                        <input type="hidden" name="analysis_type" value="basic">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period1"><i class="fas fa-calendar"></i> Primer Período:</label>
                                    <select name="period1" id="period1" class="form-control" required>
                                        <option value="">Seleccione un período</option>
                                        @if(isset($periods))
                                            @foreach($periods as $period)
                                                <option value="{{ $period->period }}">{{ $period->period }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period2"><i class="fas fa-calendar-alt"></i> Segundo Período:</label>
                                    <select name="period2" id="period2" class="form-control" required>
                                        <option value="">Seleccione un período</option>
                                        @if(isset($periods))
                                            @foreach($periods as $period)
                                                <option value="{{ $period->period }}">{{ $period->period }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="service"><i class="fas fa-cogs"></i> Servicio:</label>
                                    <select name="service" id="service" class="form-control">
                                        <option value="both">Ambos Servicios</option>
                                        <option value="cafeteria">Solo Cafetería</option>
                                        <option value="transport">Solo Transporte</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="dependency"><i class="fas fa-building"></i> Dependencia:</label>
                                    <select name="dependency" id="dependency" class="form-control">
                                        <option value="all">Todas las Dependencias</option>
                                        <option value="Sistemas">Sistemas</option>
                                        <option value="Recursos Humanos">Recursos Humanos</option>
                                        <option value="Enfermería">Enfermería</option>
                                        <option value="Almacén">Almacén</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="user_type"><i class="fas fa-users"></i> Tipo de Usuario:</label>
                                    <select name="user_type" id="user_type" class="form-control">
                                        <option value="all">Todos los Usuarios</option>
                                        <option value="active">Solo Usuarios Activos</option>
                                        <option value="families">Solo Familias</option>
                                        <option value="staff">Solo Personal</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros adicionales -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grade_filter"><i class="fas fa-graduation-cap"></i> Filtrar por Grado:</label>
                                    <select name="grade_filter" id="grade_filter" class="form-control" multiple>
                                        <option value="PK">Pre-Kínder</option>
                                        <option value="K">Kínder</option>
                                        <option value="1">Primero</option>
                                        <option value="2">Segundo</option>
                                        <option value="3">Tercero</option>
                                        <option value="4">Cuarto</option>
                                        <option value="5">Quinto</option>
                                        <option value="6">Sexto</option>
                                        <option value="7">Séptimo</option>
                                        <option value="8">Octavo</option>
                                        <option value="9">Noveno</option>
                                        <option value="10">Décimo</option>
                                        <option value="11">Once</option>
                                    </select>
                                    <small class="text-muted">Deje vacío para incluir todos los grados</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="export_format"><i class="fas fa-download"></i> Formato de Exportación:</label>
                                    <select name="export_format" id="export_format" class="form-control">
                                        <option value="">Solo Ver en Pantalla</option>
                                        <option value="pdf">Exportar a PDF</option>
                                        <option value="excel">Exportar a Excel</option>
                                        <option value="both">Ambos Formatos</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-chart-line"></i> Generar Análisis Comparativo
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab de Análisis de Cambio de Proveedor -->
                <div class="tab-pane fade" id="provider" role="tabpanel">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exchange-alt"></i> Análisis de Impacto del Cambio de Proveedor</h5>
                        <p class="mb-0">Análisis específico del impacto del cambio Sapore → Aldimark en el servicio de cafetería.</p>
                    </div>
                    
                    <form action="{{ route('surveys.complementary-services.transport.compare') }}" method="GET" id="providerAnalysisForm">
                        <input type="hidden" name="analysis_type" value="provider_change">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-utensils"></i> Período Sapore (Anterior)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="sapore_period">Período con Sapore:</label>
                                            <select name="sapore_period" id="sapore_period" class="form-control" required>
                                                <option value="">Seleccione período</option>
                                                <option value="Mayo 2024">Mayo 2024</option>
                                                <option value="Abril 2024">Abril 2024</option>
                                                <option value="Marzo 2024">Marzo 2024</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-utensils"></i> Período Aldimark (Actual)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="aldimark_period">Período con Aldimark:</label>
                                            <select name="aldimark_period" id="aldimark_period" class="form-control" required>
                                                <option value="">Seleccione período</option>
                                                <option value="Octubre 2024">Octubre 2024</option>
                                                <option value="Noviembre 2024">Noviembre 2024</option>
                                                <option value="Junio 2025">Junio 2025</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fas fa-check-square"></i> Métricas a Analizar:</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="metric_quality" name="metrics[]" value="quality" checked>
                                                <label class="custom-control-label" for="metric_quality">Calidad y Sabor</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="metric_portions" name="metrics[]" value="portions" checked>
                                                <label class="custom-control-label" for="metric_portions">Porciones</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="metric_menu" name="metrics[]" value="menu" checked>
                                                <label class="custom-control-label" for="metric_menu">Menú Ofrecido</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="metric_variety" name="metrics[]" value="variety" checked>
                                                <label class="custom-control-label" for="metric_variety">Variedad</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="metric_temperature" name="metrics[]" value="temperature" checked>
                                                <label class="custom-control-label" for="metric_temperature">Temperatura</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="metric_cleanliness" name="metrics[]" value="cleanliness" checked>
                                                <label class="custom-control-label" for="metric_cleanliness">Limpieza</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-exchange-alt"></i> Analizar Cambio de Proveedor
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Tab de Análisis por Grado Escolar -->
                <div class="tab-pane fade" id="grade" role="tabpanel">
                    <div class="alert alert-success">
                        <h5><i class="fas fa-graduation-cap"></i> Análisis de Satisfacción por Grado Escolar</h5>
                        <p class="mb-0">Evaluate patrones de satisfacción según el nivel educativo y edad de los estudiantes.</p>
                    </div>
                    
                    <form action="{{ route('surveys.complementary-services.transport.compare') }}" method="GET" id="gradeAnalysisForm">
                        <input type="hidden" name="analysis_type" value="grade_analysis">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grade_period">Período a Analizar:</label>
                                    <select name="grade_period" id="grade_period" class="form-control" required>
                                        <option value="">Seleccione un período</option>
                                        @if(isset($periods))
                                            @foreach($periods as $period)
                                                <option value="{{ $period->period }}">{{ $period->period }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grade_service">Servicio:</label>
                                    <select name="grade_service" id="grade_service" class="form-control">
                                        <option value="both">Ambos Servicios</option>
                                        <option value="cafeteria">Solo Cafetería</option>
                                        <option value="transport">Solo Transporte</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fas fa-layer-group"></i> Agrupación de Grados:</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="group_early" name="grade_groups[]" value="early" checked>
                                                <label class="custom-control-label" for="group_early">Primera Infancia (PK-K)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="group_elementary" name="grade_groups[]" value="elementary" checked>
                                                <label class="custom-control-label" for="group_elementary">Primaria (1°-5°)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="group_middle" name="grade_groups[]" value="middle" checked>
                                                <label class="custom-control-label" for="group_middle">Bachillerato (6°-11°)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-graduation-cap"></i> Analizar por Grado Escolar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab de Análisis por Ruta -->
                <div class="tab-pane fade" id="route" role="tabpanel">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-route"></i> Análisis de Satisfacción por Ruta de Transporte</h5>
                        <p class="mb-0">Evaluate el desempeño específico por número de ruta y área geográfica.</p>
                    </div>
                    
                    <form action="{{ route('surveys.complementary-services.transport.compare') }}" method="GET" id="routeAnalysisForm">
                        <input type="hidden" name="analysis_type" value="route_analysis">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_period">Período a Analizar:</label>
                                    <select name="route_period" id="route_period" class="form-control" required>
                                        <option value="">Seleccione un período</option>
                                        @if(isset($periods))
                                            @foreach($periods as $period)
                                                <option value="{{ $period->period }}">{{ $period->period }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="route_numbers">Rutas Específicas:</label>
                                    <select name="route_numbers[]" id="route_numbers" class="form-control" multiple>
                                        <option value="1">Ruta 1 - Norte</option>
                                        <option value="2">Ruta 2 - Sur</option>
                                        <option value="3">Ruta 3 - Este</option>
                                        <option value="4">Ruta 4 - Oeste</option>
                                        <option value="5">Ruta 5 - Centro</option>
                                        <option value="6">Ruta 6 - Metropolitana</option>
                                    </select>
                                    <small class="text-muted">Deje vacío para incluir todas las rutas</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fas fa-check-square"></i> Variables de Transporte a Analizar:</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="route_punctuality" name="route_metrics[]" value="punctuality" checked>
                                                <label class="custom-control-label" for="route_punctuality">Puntualidad</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="route_cleanliness" name="route_metrics[]" value="cleanliness" checked>
                                                <label class="custom-control-label" for="route_cleanliness">Limpieza del Vehículo</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="route_staff" name="route_metrics[]" value="staff_treatment" checked>
                                                <label class="custom-control-label" for="route_staff">Trato del Personal</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="route_communication" name="route_metrics[]" value="communication" checked>
                                                <label class="custom-control-label" for="route_communication">Comunicación</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-route"></i> Analizar por Rutas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Exportación Rápida -->
    <div class="card card-secondary collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-download"></i> Exportación Rápida de Reportes
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <a href="{{ route('surveys.complementary-services.export.executive') }}" class="btn btn-block btn-outline-primary">
                        <i class="fas fa-chart-pie"></i><br>
                        Resumen Ejecutivo
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('surveys.complementary-services.export.provider-change') }}" class="btn btn-block btn-outline-warning">
                        <i class="fas fa-exchange-alt"></i><br>
                        Cambio de Proveedor
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('surveys.complementary-services.export.grade-analysis') }}" class="btn btn-block btn-outline-success">
                        <i class="fas fa-graduation-cap"></i><br>
                        Análisis por Grados
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('surveys.complementary-services.export.action-plan') }}" class="btn btn-block btn-outline-info">
                        <i class="fas fa-tasks"></i><br>
                        Plan de Acción
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
<!-- Solo se usan los estilos incluidos en AdminLTE -->

<!-- Custom CSS -->
<style>
    .badge {
        font-size: 0.9em;
    }
    .card-title i {
        margin-right: 10px;
    }
    .list-unstyled li {
        margin-bottom: 5px;
    }
    
    /* Estilos para las tabs de análisis */
    .nav-tabs .nav-link {
        border-radius: 0.5rem 0.5rem 0 0;
        margin-right: 0.2rem;
    }
    
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border-color: #007bff;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.1);
    }
    
    /* Cards de proveedor */
    .provider-card {
        transition: transform 0.2s ease-in-out;
    }
    
    .provider-card:hover {
        transform: translateY(-2px);
    }
    
    /* Checkboxes personalizados */
    .custom-control-label {
        font-weight: 500;
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    /* Alerts con iconos */
    .alert h5 {
        margin-bottom: 0.5rem;
    }
    
    .alert i {
        margin-right: 0.5rem;
    }
    
    /* Botones de exportación */
    .btn-block {
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .btn-block i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    /* Form groups spacing */
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
    
    .form-group label i {
        color: #007bff;
        margin-right: 0.5rem;
    }
    
    /* Tab content */
    .tab-content {
        background-color: #f8f9fa;
        border-radius: 0 0.5rem 0.5rem 0.5rem;
        padding: 1.5rem;
        border: 1px solid #dee2e6;
        border-top: none;
    }
    
    /* Cards gradient headers */
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
    
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .nav-tabs {
            flex-direction: column;
        }
        
        .nav-tabs .nav-link {
            margin-right: 0;
            margin-bottom: 0.2rem;
        }
        
        .btn-block {
            margin-bottom: 1rem;
        }
        
        .tab-content {
            padding: 1rem;
        }
    }
    
    /* Loading states */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #007bff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
    
    /* Analysis metrics display */
    .metric-display {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid #007bff;
    }
    
    .metric-display h6 {
        color: #007bff;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    /* Provider comparison cards */
    .provider-comparison {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 0.5rem;
    }
    
    .provider-comparison.improvement {
        border-left: 4px solid #28a745;
    }
    
    .provider-comparison.decline {
        border-left: 4px solid #dc3545;
    }
    
    .provider-comparison.stable {
        border-left: 4px solid #6c757d;
    }
</style>
@stop

@section('js')
<!-- No se requieren librerías externas -->

<!-- Custom JavaScript -->
<script>
$(document).ready(function() {
    // Tab switching logic
    $('#analysisTab a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Form validation
    function validateForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;
        
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }
    
    // Form submission handlers
    $('#basicAnalysisForm').on('submit', function(e) {
        if (!validateForm('basicAnalysisForm')) {
            e.preventDefault();
            showAlert('Por favor complete todos los campos requeridos.', 'warning');
        } else {
            showLoadingState(this);
        }
    });
    
    $('#providerAnalysisForm').on('submit', function(e) {
        if (!validateForm('providerAnalysisForm')) {
            e.preventDefault();
            showAlert('Por favor complete todos los campos requeridos.', 'warning');
        } else {
            showLoadingState(this);
        }
    });
    
    $('#gradeAnalysisForm').on('submit', function(e) {
        if (!validateForm('gradeAnalysisForm')) {
            e.preventDefault();
            showAlert('Por favor complete todos los campos requeridos.', 'warning');
        } else {
            showLoadingState(this);
        }
    });
    
    $('#routeAnalysisForm').on('submit', function(e) {
        if (!validateForm('routeAnalysisForm')) {
            e.preventDefault();
            showAlert('Por favor complete todos los campos requeridos.', 'warning');
        } else {
            showLoadingState(this);
        }
    });
    
    // Show loading state
    function showLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        submitBtn.disabled = true;
        
        // Re-enable after 30 seconds as fallback
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 30000);
    }
    
    // Show alert messages
    function showAlert(message, type = 'info') {
        const alertDiv = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `);
        
        $('.card-body').first().prepend(alertDiv);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.alert('close');
        }, 5000);
    }
    
    // Period comparison logic
    $('#period1, #period2').on('change', function() {
        const period1 = $('#period1').val();
        const period2 = $('#period2').val();
        
        if (period1 && period2 && period1 === period2) {
            showAlert('Los períodos seleccionados deben ser diferentes.', 'warning');
            $(this).val('');
        }
    });
    
    // Provider period validation
    $('#sapore_period, #aldimark_period').on('change', function() {
        const sapore = $('#sapore_period').val();
        const aldimark = $('#aldimark_period').val();
        
        if (sapore && aldimark) {
            // Validate that Sapore period is before Aldimark period
            const saporeDate = new Date(sapore);
            const aldimarkDate = new Date(aldimark);
            
            if (saporeDate >= aldimarkDate) {
                showAlert('El período de Sapore debe ser anterior al período de Aldimark.', 'warning');
                $(this).val('');
            }
        }
    });
    
    // Export functionality
    $('.btn-outline-primary, .btn-outline-warning, .btn-outline-success, .btn-outline-info').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const text = $(this).text().trim();
        
        // Show loading state
        const originalHtml = $(this).html();
        $(this).html('<i class="fas fa-spinner fa-spin"></i><br>Generando...');
        $(this).addClass('disabled');
        
        // Simulate export process
        setTimeout(() => {
            $(this).html(originalHtml);
            $(this).removeClass('disabled');
            
            // Open export URL in new tab
            window.open(url, '_blank');
            
            showAlert(`Reporte "${text}" generado exitosamente.`, 'success');
        }, 2000);
    });
    
    // Checkbox group handlers
    $('input[name="metrics[]"]').on('change', function() {
        const checkedMetrics = $('input[name="metrics[]"]:checked').length;
        if (checkedMetrics === 0) {
            showAlert('Debe seleccionar al menos una métrica para analizar.', 'warning');
            $(this).prop('checked', true);
        }
    });
    
    $('input[name="route_metrics[]"]').on('change', function() {
        const checkedMetrics = $('input[name="route_metrics[]"]:checked').length;
        if (checkedMetrics === 0) {
            showAlert('Debe seleccionar al menos una variable de transporte.', 'warning');
            $(this).prop('checked', true);
        }
    });
    
    $('input[name="grade_groups[]"]').on('change', function() {
        const checkedGroups = $('input[name="grade_groups[]"]:checked').length;
        if (checkedGroups === 0) {
            showAlert('Debe seleccionar al menos un grupo de grados.', 'warning');
            $(this).prop('checked', true);
        }
    });
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
    
    // Card collapse animations
    $('[data-card-widget="collapse"]').on('click', function() {
        const card = $(this).closest('.card');
        const icon = $(this).find('i');
        
        if (card.hasClass('collapsed-card')) {
            icon.removeClass('fa-plus').addClass('fa-minus');
        } else {
            icon.removeClass('fa-minus').addClass('fa-plus');
        }
    });
    
    // Auto-save form state to localStorage
    function saveFormState() {
        const forms = ['basicAnalysisForm', 'providerAnalysisForm', 'gradeAnalysisForm', 'routeAnalysisForm'];
        
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                const formData = new FormData(form);
                const data = {};
                
                for (let [key, value] of formData.entries()) {
                    if (data[key]) {
                        if (Array.isArray(data[key])) {
                            data[key].push(value);
                        } else {
                            data[key] = [data[key], value];
                        }
                    } else {
                        data[key] = value;
                    }
                }
                
                localStorage.setItem(formId, JSON.stringify(data));
            }
        });
    }
    
    // Load saved form state
    function loadFormState() {
        const forms = ['basicAnalysisForm', 'providerAnalysisForm', 'gradeAnalysisForm', 'routeAnalysisForm'];
        
        forms.forEach(formId => {
            const savedData = localStorage.getItem(formId);
            if (savedData) {
                try {
                    const data = JSON.parse(savedData);
                    const form = document.getElementById(formId);
                    
                    if (form) {
                        Object.keys(data).forEach(key => {
                            const field = form.querySelector(`[name="${key}"]`);
                            if (field) {
                                if (field.type === 'checkbox' || field.type === 'radio') {
                                    field.checked = true;
                                } else {
                                    field.value = data[key];
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.warn('Error loading saved form state:', e);
                }
            }
        });
    }
    
    // Save form state on input change
    $('form input, form select').on('change', saveFormState);
    
    // Load saved state on page load
    loadFormState();
    
    // Clear saved state on successful form submission
    $('form').on('submit', function() {
        const formId = $(this).attr('id');
        if (formId) {
            localStorage.removeItem(formId);
        }
    });
});
</script>
@stop
