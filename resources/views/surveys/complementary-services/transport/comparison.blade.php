@extends('adminlte::page')

@section('title', 'Análisis Comparativo - Servicios Complementarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line text-primary"></i> Análisis Comparativo de Encuestas de Satisfacción</h1>
        <a href="{{ route('surveys.complementary-services.transport.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    @if(isset($comparisonData) && !empty($comparisonData))
    
    <!-- Resumen Ejecutivo -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Informe Ejecutivo de Análisis Comparativo
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    {{ $comparisonData['period1'] }} vs {{ $comparisonData['period2'] }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-clipboard-list"></i> Resumen Ejecutivo</h5>
                        <p class="mb-2">
                            El presente informe presenta el análisis comparativo de satisfacción de los servicios complementarios 
                            (cafetería y transporte) entre los períodos <strong>{{ $comparisonData['period1'] }}</strong> y 
                            <strong>{{ $comparisonData['period2'] }}</strong>, con el objetivo de evaluar la evolución de la 
                            calidad de los servicios y el impacto de las acciones de mejora implementadas.
                        </p>
                        <p class="mb-2">
                            <strong>Metodología:</strong> Se aplicó la misma encuesta de satisfacción en ambos períodos para 
                            garantizar la comparabilidad de los resultados. Los indicadores se miden en escala porcentual 
                            de satisfacción positiva.
                        </p>
                        <p class="mb-0">
                            <strong>Alcance de la muestra:</strong> {{ $comparisonData['responses_period1'] }} respuestas 
                            en {{ $comparisonData['period1'] }} vs {{ $comparisonData['responses_period2'] }} respuestas 
                            en {{ $comparisonData['period2'] }}.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas {{ $comparisonData['period1'] }}</span>
                            <span class="info-box-number">{{ $comparisonData['responses_period1'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas {{ $comparisonData['period2'] }}</span>
                            <span class="info-box-number">{{ $comparisonData['responses_period2'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Variación de Respuestas</span>
                            <span class="info-box-number">
                                @php
                                    $responseChange = $comparisonData['responses_period1'] > 0 
                                        ? round((($comparisonData['responses_period2'] - $comparisonData['responses_period1']) / $comparisonData['responses_period1']) * 100, 1)
                                        : 0;
                                @endphp
                                {{ $responseChange > 0 ? '+' : '' }}{{ $responseChange }}%
                            </span>
                            <div class="progress">
                                <div class="progress-bar {{ $responseChange > 0 ? 'bg-success' : ($responseChange < 0 ? 'bg-danger' : 'bg-warning') }}" 
                                     style="width: {{ abs($responseChange) > 100 ? 100 : abs($responseChange) }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $comparisonData['responses_period1'] }} → {{ $comparisonData['responses_period2'] }} respuestas
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Métricas de Variación de Satisfacción -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="info-box bg-gradient-primary">
                        <span class="info-box-icon"><i class="fas fa-utensils"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Variación Satisfacción Cafetería</span>
                            <span class="info-box-number">
                                @php
                                    $cafeteriaAvg1 = collect($comparisonData['cafeteria_period1'])->only(['calidad_sabor', 'porcion_satisfaccion', 'menu_calidad', 'variedad_menu', 'temperatura_adecuada', 'limpieza_comedor', 'trato_personal'])->avg();
                                    $cafeteriaAvg2 = collect($comparisonData['cafeteria_period2'])->only(['calidad_sabor', 'porcion_satisfaccion', 'menu_calidad', 'variedad_menu', 'temperatura_adecuada', 'limpieza_comedor', 'trato_personal'])->avg();
                                    $cafeteriaChange = $cafeteriaAvg1 > 0 ? round((($cafeteriaAvg2 - $cafeteriaAvg1) / $cafeteriaAvg1) * 100, 1) : 0;
                                @endphp
                                {{ $cafeteriaChange > 0 ? '+' : '' }}{{ $cafeteriaChange }}%
                            </span>
                            <div class="progress">
                                <div class="progress-bar {{ $cafeteriaChange > 0 ? 'bg-success' : ($cafeteriaChange < 0 ? 'bg-danger' : 'bg-warning') }}" 
                                     style="width: {{ abs($cafeteriaChange) > 100 ? 100 : abs($cafeteriaChange) }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ round($cafeteriaAvg1, 1) }}% → {{ round($cafeteriaAvg2, 1) }}% promedio
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-bus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Variación Satisfacción Transporte</span>
                            <span class="info-box-number">
                                @php
                                    $transportAvg1 = collect($comparisonData['transport_period1'])->only(['puntualidad', 'limpieza_vehiculo', 'trato_personal', 'comunicacion'])->avg();
                                    $transportAvg2 = collect($comparisonData['transport_period2'])->only(['puntualidad', 'limpieza_vehiculo', 'trato_personal', 'comunicacion'])->avg();
                                    $transportChange = $transportAvg1 > 0 ? round((($transportAvg2 - $transportAvg1) / $transportAvg1) * 100, 1) : 0;
                                @endphp
                                {{ $transportChange > 0 ? '+' : '' }}{{ $transportChange }}%
                            </span>
                            <div class="progress">
                                <div class="progress-bar {{ $transportChange > 0 ? 'bg-success' : ($transportChange < 0 ? 'bg-danger' : 'bg-warning') }}" 
                                     style="width: {{ abs($transportChange) > 100 ? 100 : abs($transportChange) }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ round($transportAvg1, 1) }}% → {{ round($transportAvg2, 1) }}% promedio
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Variación Satisfacción General</span>
                            <span class="info-box-number">
                                @php
                                    $generalAvg1 = ($cafeteriaAvg1 + $transportAvg1) / 2;
                                    $generalAvg2 = ($cafeteriaAvg2 + $transportAvg2) / 2;
                                    $generalChange = $generalAvg1 > 0 ? round((($generalAvg2 - $generalAvg1) / $generalAvg1) * 100, 1) : 0;
                                @endphp
                                {{ $generalChange > 0 ? '+' : '' }}{{ $generalChange }}%
                            </span>
                            <div class="progress">
                                <div class="progress-bar {{ $generalChange > 0 ? 'bg-success' : ($generalChange < 0 ? 'bg-danger' : 'bg-warning') }}" 
                                     style="width: {{ abs($generalChange) > 100 ? 100 : abs($generalChange) }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ round($generalAvg1, 1) }}% → {{ round($generalAvg2, 1) }}% promedio
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Ejecutivo de Variaciones -->
    <div class="card card-info mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> RESUMEN EJECUTIVO DE VARIACIONES
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="fas fa-utensils"></i> Servicios de Cafetería</h6>
                    <div class="list-group list-group-flush">
                        @php
                            $cafeteriaMetrics = [
                                'calidad_sabor' => 'Calidad y Sabor',
                                'porcion_satisfaccion' => 'Satisfacción con Porciones',
                                'menu_calidad' => 'Calidad del Menú',
                                'variedad_menu' => 'Variedad del Menú',
                                'temperatura_adecuada' => 'Temperatura Adecuada',
                                'limpieza_comedor' => 'Limpieza del Comedor',
                                'trato_personal' => 'Trato del Personal'
                            ];
                        @endphp
                        @foreach($cafeteriaMetrics as $key => $label)
                            @if(isset($comparisonData['cafeteria_differences'][$key]))
                                @php $diff = $comparisonData['cafeteria_differences'][$key]; @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $label }}</span>
                                    <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }} badge-pill">
                                        {{ $diff['percentage_change'] > 0 ? '+' : '' }}{{ $diff['percentage_change'] }}%
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-info"><i class="fas fa-bus"></i> Servicios de Transporte</h6>
                    <div class="list-group list-group-flush">
                        @php
                            $transportMetrics = [
                                'puntualidad' => 'Puntualidad',
                                'limpieza_vehiculo' => 'Limpieza del Vehículo',
                                'trato_personal' => 'Trato del Personal',
                                'comunicacion' => 'Comunicación'
                            ];
                        @endphp
                        @foreach($transportMetrics as $key => $label)
                            @if(isset($comparisonData['transport_differences'][$key]))
                                @php $diff = $comparisonData['transport_differences'][$key]; @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $label }}</span>
                                    <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }} badge-pill">
                                        {{ $diff['percentage_change'] > 0 ? '+' : '' }}{{ $diff['percentage_change'] }}%
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis del Servicio de Cafetería -->
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-utensils"></i> ANÁLISIS DEL SERVICIO DE CAFETERÍA
            </h3>
        </div>
        <div class="card-body">
            @if($comparisonData['cafeteria_period1']['total_usuarios'] > 0 || $comparisonData['cafeteria_period2']['total_usuarios'] > 0)
                
                <div class="alert alert-success mb-4">
                    <h5><i class="fas fa-info-circle"></i> Análisis General del Servicio</h5>
                    <p class="mb-2">
                        El servicio de cafetería constituye un elemento fundamental en la experiencia diaria de nuestros colaboradores, 
                        contribuyendo significativamente al bienestar y satisfacción laboral. El análisis comparativo entre los períodos 
                        {{ $comparisonData['period1'] }} y {{ $comparisonData['period2'] }} revela importantes tendencias en la 
                        percepción de calidad del servicio.
                    </p>
                    <p class="mb-0">
                        <strong>Usuarios evaluados:</strong> {{ $comparisonData['cafeteria_period1']['total_usuarios'] }} personas 
                        en {{ $comparisonData['period1'] }} vs {{ $comparisonData['cafeteria_period2']['total_usuarios'] }} personas 
                        en {{ $comparisonData['period2'] }}.
                    </p>
                </div>

                <!-- Análisis por Indicadores -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary mb-3"><i class="fas fa-chart-bar"></i> Indicadores de Satisfacción</h5>
                    </div>
                </div>

                <!-- Calidad y Sabor -->
                <div class="mb-4">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <h6 class="text-primary font-weight-bold">Calidad y Sabor de los Alimentos</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La percepción de calidad y sabor de los alimentos representa uno de los aspectos más críticos 
                                        del servicio de cafetería, impactando directamente en la satisfacción general de los usuarios.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['cafeteria_period1']['calidad_sabor'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['cafeteria_period2']['calidad_sabor'] }}%</span>
                                        @if(isset($comparisonData['cafeteria_differences']['calidad_sabor']))
                                            @php $diff = $comparisonData['cafeteria_differences']['calidad_sabor']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['cafeteria_differences']['calidad_sabor']))
                                        @php $diff = $comparisonData['cafeteria_differences']['calidad_sabor']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora significativa</strong> que refleja el impacto positivo de las acciones correctivas implementadas en la preparación y presentación de alimentos.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Disminución</strong> que requiere atención inmediata para identificar las causas y implementar acciones correctivas.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad</strong> en el indicador, manteniendo el nivel de satisfacción del período anterior.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['cafeteria_differences']['calidad_sabor']))
                                        @php $diff = $comparisonData['cafeteria_differences']['calidad_sabor']; @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $diff['trend_class'] == 'text-success' ? 'bg-success' : ($diff['trend_class'] == 'text-danger' ? 'bg-danger' : 'bg-secondary') }}" 
                                                 style="width: {{ $comparisonData['cafeteria_period2']['calidad_sabor'] }}%">
                                                {{ $comparisonData['cafeteria_period2']['calidad_sabor'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Porciones Ofrecidas -->
                <div class="mb-4">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h6 class="text-info font-weight-bold">Satisfacción con las Porciones Ofrecidas</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La adecuación de las porciones es fundamental para garantizar que los usuarios perciban 
                                        un valor justo en el servicio y se sientan satisfechos con la cantidad de alimento recibido.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['cafeteria_period1']['porcion_satisfaccion'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['cafeteria_period2']['porcion_satisfaccion'] }}%</span>
                                        @if(isset($comparisonData['cafeteria_differences']['porcion_satisfaccion']))
                                            @php $diff = $comparisonData['cafeteria_differences']['porcion_satisfaccion']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['cafeteria_differences']['porcion_satisfaccion']))
                                        @php $diff = $comparisonData['cafeteria_differences']['porcion_satisfaccion']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora en la percepción</strong> de adecuación de porciones, indicando mejor balance cantidad-precio.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Disminución</strong> que sugiere necesidad de revisar las políticas de porciones y costos.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Mantenimiento</strong> del nivel de satisfacción con las porciones servidas.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['cafeteria_differences']['porcion_satisfaccion']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: {{ $comparisonData['cafeteria_period2']['porcion_satisfaccion'] }}%">
                                                {{ $comparisonData['cafeteria_period2']['porcion_satisfaccion'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menú Ofrecido -->
                <div class="mb-4">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="text-success font-weight-bold">Calidad del Menú Ofrecido</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La estructura y calidad del menú refleja la planificación nutricional y la variedad 
                                        de opciones disponibles para satisfacer las preferencias diversas de los usuarios.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['cafeteria_period1']['menu_calidad'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['cafeteria_period2']['menu_calidad'] }}%</span>
                                        @if(isset($comparisonData['cafeteria_differences']['menu_calidad']))
                                            @php $diff = $comparisonData['cafeteria_differences']['menu_calidad']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['cafeteria_differences']['menu_calidad']))
                                        @php $diff = $comparisonData['cafeteria_differences']['menu_calidad']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Evolución positiva</strong> en la percepción de calidad del menú y su estructura nutricional.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Retroceso</strong> que requiere revisión de la planificación y variedad del menú.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Consistencia</strong> en la calidad percibida del menú ofrecido.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['cafeteria_differences']['menu_calidad']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $comparisonData['cafeteria_period2']['menu_calidad'] }}%">
                                                {{ $comparisonData['cafeteria_period2']['menu_calidad'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variedad del Menú -->
                <div class="mb-4">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <h6 class="text-warning font-weight-bold">Variedad del Menú</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La diversidad en las opciones del menú es crucial para mantener el interés de los usuarios 
                                        y satisfacer diferentes gustos y requerimientos nutricionales.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['cafeteria_period1']['variedad_menu'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['cafeteria_period2']['variedad_menu'] }}%</span>
                                        @if(isset($comparisonData['cafeteria_differences']['variedad_menu']))
                                            @php $diff = $comparisonData['cafeteria_differences']['variedad_menu']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['cafeteria_differences']['variedad_menu']))
                                        @php $diff = $comparisonData['cafeteria_differences']['variedad_menu']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Incremento en variedad</strong> que enriquece la experiencia gastronómica de los usuarios.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Reducción percibida</strong> en variedad que puede generar monotonía en el servicio.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad</strong> en la percepción de variedad del menú ofrecido.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['cafeteria_differences']['variedad_menu']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $comparisonData['cafeteria_period2']['variedad_menu'] }}%">
                                                {{ $comparisonData['cafeteria_period2']['variedad_menu'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Temperatura de los Alimentos -->
                <div class="mb-4">
                    <div class="card border-left-danger">
                        <div class="card-body">
                            <h6 class="text-danger font-weight-bold">Temperatura Adecuada de los Alimentos</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        El mantenimiento de la temperatura adecuada es fundamental para la seguridad alimentaria 
                                        y la experiencia sensorial, siendo un indicador crítico de calidad operativa.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['cafeteria_period1']['temperatura_adecuada'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['cafeteria_period2']['temperatura_adecuada'] }}%</span>
                                        @if(isset($comparisonData['cafeteria_differences']['temperatura_adecuada']))
                                            @php $diff = $comparisonData['cafeteria_differences']['temperatura_adecuada']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['cafeteria_differences']['temperatura_adecuada']))
                                        @php $diff = $comparisonData['cafeteria_differences']['temperatura_adecuada']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora en control térmico</strong> que evidencia mejor gestión de procesos operativos.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Deterioro</strong> que requiere revisión inmediata de equipos y procedimientos.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Mantenimiento</strong> del estándar de temperatura en el servicio.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['cafeteria_differences']['temperatura_adecuada']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-danger" style="width: {{ $comparisonData['cafeteria_period2']['temperatura_adecuada'] }}%">
                                                {{ $comparisonData['cafeteria_period2']['temperatura_adecuada'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Limpieza del Comedor -->
                <div class="mb-4">
                    <div class="card border-left-secondary">
                        <div class="card-body">
                            <h6 class="text-secondary font-weight-bold">Limpieza del Comedor</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        Las condiciones de limpieza del espacio físico son determinantes en la percepción 
                                        general de calidad y contribuyen significativamente a la experiencia del usuario.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['cafeteria_period1']['limpieza_comedor'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['cafeteria_period2']['limpieza_comedor'] }}%</span>
                                        @if(isset($comparisonData['cafeteria_differences']['limpieza_comedor']))
                                            @php $diff = $comparisonData['cafeteria_differences']['limpieza_comedor']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['cafeteria_differences']['limpieza_comedor']))
                                        @php $diff = $comparisonData['cafeteria_differences']['limpieza_comedor']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora en estándares</strong> de limpieza que fortalece la imagen del servicio.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Disminución preocupante</strong> que afecta la percepción general de calidad.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Consistencia</strong> en los estándares de limpieza establecidos.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['cafeteria_differences']['limpieza_comedor']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-secondary" style="width: {{ $comparisonData['cafeteria_period2']['limpieza_comedor'] }}%">
                                                {{ $comparisonData['cafeteria_period2']['limpieza_comedor'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trato del Personal -->
                <div class="mb-4">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <h6 class="text-primary font-weight-bold">Trato del Personal</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La calidad en el servicio al cliente, reflejada en el trato del personal, constituye 
                                        un diferenciador clave que impacta directamente en la satisfacción general.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['cafeteria_period1']['trato_personal'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['cafeteria_period2']['trato_personal'] }}%</span>
                                        @if(isset($comparisonData['cafeteria_differences']['trato_personal']))
                                            @php $diff = $comparisonData['cafeteria_differences']['trato_personal']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['cafeteria_differences']['trato_personal']))
                                        @php $diff = $comparisonData['cafeteria_differences']['trato_personal']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Fortalecimiento</strong> en las habilidades de servicio al cliente del equipo.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Deterioro</strong> que requiere refuerzo en capacitación y motivación del personal.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad</strong> en la calidad del servicio al cliente brindado.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['cafeteria_differences']['trato_personal']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $comparisonData['cafeteria_period2']['trato_personal'] }}%">
                                                {{ $comparisonData['cafeteria_period2']['trato_personal'] }}%
                                            </div>
                                        </div>
                                    @endif
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

    <!-- Análisis del Servicio de Transporte -->
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bus"></i> ANÁLISIS DEL SERVICIO DE TRANSPORTE
            </h3>
        </div>
        <div class="card-body">
            @if($comparisonData['transport_period1']['total_usuarios'] > 0 || $comparisonData['transport_period2']['total_usuarios'] > 0)
                
                <div class="alert alert-warning mb-4">
                    <h5><i class="fas fa-info-circle"></i> Evaluación Integral del Servicio de Transporte</h5>
                    <p class="mb-2">
                        El servicio de transporte representa un elemento estratégico en la operación organizacional, 
                        impactando directamente en la puntualidad, productividad y satisfacción de nuestros colaboradores. 
                        La evaluación comparativa entre {{ $comparisonData['period1'] }} y {{ $comparisonData['period2'] }} 
                        permite identificar tendencias críticas para la optimización continua del servicio.
                    </p>
                    <p class="mb-0">
                        <strong>Base de evaluación:</strong> {{ $comparisonData['transport_period1']['total_usuarios'] }} usuarios 
                        en {{ $comparisonData['period1'] }} versus {{ $comparisonData['transport_period2']['total_usuarios'] }} usuarios 
                        en {{ $comparisonData['period2'] }}, manteniendo nuestro aliado estratégico para garantizar la continuidad operativa.
                    </p>
                </div>

                <!-- Análisis por Dimensiones de Servicio -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-warning mb-3"><i class="fas fa-analytics"></i> Dimensiones Críticas de Evaluación del Servicio</h5>
                    </div>
                </div>

                <!-- Puntualidad del Servicio -->
                <div class="mb-4">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="text-success font-weight-bold">Puntualidad del Servicio</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La puntualidad constituye el pilar fundamental del servicio de transporte, determinando 
                                        el cumplimiento de horarios laborales y la eficiencia operativa organizacional. Su impacto 
                                        trasciende la satisfacción individual, afectando la productividad general y el ambiente laboral.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['transport_period1']['puntualidad'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['transport_period2']['puntualidad'] }}%</span>
                                        @if(isset($comparisonData['transport_differences']['puntualidad']))
                                            @php $diff = $comparisonData['transport_differences']['puntualidad']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['transport_differences']['puntualidad']))
                                        @php $diff = $comparisonData['transport_differences']['puntualidad']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora sustancial</strong> que refleja optimización en la gestión de rutas y horarios, contribuyendo positivamente a la operación.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Deterioro crítico</strong> que requiere intervención inmediata para evitar impactos en productividad organizacional.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad operativa</strong> manteniendo los estándares de puntualidad establecidos.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['transport_differences']['puntualidad']))
                                        @php $diff = $comparisonData['transport_differences']['puntualidad']; @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $diff['trend_class'] == 'text-success' ? 'bg-success' : ($diff['trend_class'] == 'text-danger' ? 'bg-danger' : 'bg-secondary') }}" 
                                                 style="width: {{ $comparisonData['transport_period2']['puntualidad'] }}%">
                                                {{ $comparisonData['transport_period2']['puntualidad'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Limpieza de los Vehículos -->
                <div class="mb-4">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h6 class="text-info font-weight-bold">Limpieza y Mantenimiento de Vehículos</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        Las condiciones de limpieza e higiene de los vehículos reflejan los estándares de calidad 
                                        del servicio y contribuyen significativamente a la imagen corporativa, la salud de los usuarios 
                                        y la percepción general del beneficio ofrecido.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['transport_period1']['limpieza_vehiculo'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['transport_period2']['limpieza_vehiculo'] }}%</span>
                                        @if(isset($comparisonData['transport_differences']['limpieza_vehiculo']))
                                            @php $diff = $comparisonData['transport_differences']['limpieza_vehiculo']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['transport_differences']['limpieza_vehiculo']))
                                        @php $diff = $comparisonData['transport_differences']['limpieza_vehiculo']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora en estándares</strong> de limpieza que fortalece la imagen profesional del servicio.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Disminución preocupante</strong> que afecta la percepción de calidad y requiere intervención del proveedor.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Mantenimiento</strong> de los estándares de limpieza establecidos para el servicio.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['transport_differences']['limpieza_vehiculo']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: {{ $comparisonData['transport_period2']['limpieza_vehiculo'] }}%">
                                                {{ $comparisonData['transport_period2']['limpieza_vehiculo'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trato del Personal -->
                <div class="mb-4">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <h6 class="text-primary font-weight-bold">Calidad en el Trato del Personal</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La calidad del servicio al cliente proporcionado por los conductores y personal operativo 
                                        constituye un elemento diferenciador que impacta directamente en la experiencia del usuario 
                                        y la percepción del profesionalismo organizacional.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['transport_period1']['trato_personal'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['transport_period2']['trato_personal'] }}%</span>
                                        @if(isset($comparisonData['transport_differences']['trato_personal']))
                                            @php $diff = $comparisonData['transport_differences']['trato_personal']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['transport_differences']['trato_personal']))
                                        @php $diff = $comparisonData['transport_differences']['trato_personal']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Fortalecimiento</strong> en habilidades de servicio al cliente y profesionalismo del personal operativo.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Deterioro</strong> que requiere capacitación y refuerzo en estándares de atención al cliente.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad</strong> en la calidad del servicio al cliente brindado por el personal.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['transport_differences']['trato_personal']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $comparisonData['transport_period2']['trato_personal'] }}%">
                                                {{ $comparisonData['transport_period2']['trato_personal'] }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comunicación Oportuna y Asertiva -->
                <div class="mb-4">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <h6 class="text-warning font-weight-bold">Comunicación Oportuna y Asertiva</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La efectividad en la comunicación determina la capacidad de respuesta ante eventualidades, 
                                        la transparencia en la información y la confianza de los usuarios en el servicio. 
                                        Este indicador mide la calidad de los canales de comunicación establecidos.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['transport_period1']['comunicacion'] }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['transport_period2']['comunicacion'] }}%</span>
                                        @if(isset($comparisonData['transport_differences']['comunicacion']))
                                            @php $diff = $comparisonData['transport_differences']['comunicacion']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['transport_differences']['comunicacion']))
                                        @php $diff = $comparisonData['transport_differences']['comunicacion']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Optimización</strong> en los procesos de comunicación que mejora la confianza y transparencia del servicio.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Debilitamiento</strong> en la comunicación que puede generar incertidumbre y afectar la credibilidad del servicio.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Consistencia</strong> en la calidad de los procesos de comunicación establecidos.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['transport_differences']['comunicacion']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $comparisonData['transport_period2']['comunicacion'] }}%">
                                                {{ $comparisonData['transport_period2']['comunicacion'] }}%
                                            </div>
                                        </div>
                                    @endif
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

    <!-- Plan de Acción para Mejora Continua -->
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i> PLAN ESTRATÉGICO DE MEJORA CONTINUA
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success mb-4">
                <h5><i class="fas fa-lightbulb"></i> Marco Estratégico para la Optimización de Servicios</h5>
                <p class="mb-2">
                    La implementación de un plan estructurado de mejora continua constituye la base fundamental para 
                    la evolución sostenible de los servicios de cafetería y transporte. Las acciones definidas responden 
                    a un enfoque preventivo y correctivo, orientado a la excelencia operativa.
                </p>
                <p class="mb-0">
                    <strong>Objetivo:</strong> Garantizar la prestación de servicios de alta calidad que superen las 
                    expectativas de nuestros colaboradores y contribuyan al fortalecimiento del clima organizacional.
                </p>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h5 class="text-success font-weight-bold mb-3">
                                <i class="fas fa-utensils"></i> ESTRATEGIAS DE MEJORA - SERVICIO DE CAFETERÍA
                            </h5>
                            
                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-clipboard-check"></i> Gestión de Calidad Alimentaria</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Validación de recetas estándar</strong> para garantizar consistencia en sabor y calidad
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Verificación rigurosa de calidad de insumos</strong> mediante protocolos de selección de proveedores
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Control permanente de equipos</strong> para conservar temperaturas óptimas de servicio
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-users"></i> Desarrollo del Talento Humano</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Capacitación continua del personal</strong> en manipulación de alimentos y servicio al cliente
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Retroalimentación sistemática</strong> para fortalecer competencias y motivación del equipo
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-chart-line"></i> Optimización Operativa</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Verificación de menaje y porciones</strong> para garantizar adecuación precio-valor
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Reuniones semanales de planificación</strong> para verificación de propuestas de menús
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Apoyo del Comité de Alimentos</strong> en la estructura de menús balanceados y nutritivos
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h6 class="text-primary"><i class="fas fa-broom"></i> Gestión de Infraestructura</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-secondary"></i> 
                                        <strong>Inspecciones permanentes</strong> del comedor y zonas de mayor circulación
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-secondary"></i> 
                                        <strong>Mantenimiento preventivo</strong> de instalaciones y equipos de servicio
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <h5 class="text-warning font-weight-bold mb-3">
                                <i class="fas fa-bus"></i> ESTRATEGIAS DE MEJORA - SERVICIO DE TRANSPORTE
                            </h5>
                            
                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-handshake"></i> Fortalecimiento de Alianzas Estratégicas</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Elaboración de acuerdos esenciales</strong> entre las partes para clarificar responsabilidades
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Evaluación continua del proveedor</strong> basada en indicadores de desempeño objetivos
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-graduation-cap"></i> Desarrollo del Personal Operativo</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Capacitación permanente</strong> en seguridad vial, servicio al cliente y comunicación asertiva
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Retroalimentación continua</strong> sobre el estado del servicio y áreas de mejora
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-tools"></i> Gestión Operativa y Tecnológica</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Verificación permanente</strong> del estado mecánico y de seguridad de los vehículos
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Registros actualizados</strong> y análisis de recorridos diarios para optimización de rutas
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Herramientas tecnológicas</strong> para reporte oportuno de novedades y trazabilidad
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h6 class="text-primary"><i class="fas fa-comments"></i> Comunicación y Respuesta</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-secondary"></i> 
                                        <strong>Respuestas oportunas</strong> con trazabilidad y soportes técnicos documentados
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-secondary"></i> 
                                        <strong>Canales de comunicación directa</strong> para atención inmediata de eventualidades
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-chart-bar"></i> <strong>Indicadores de Seguimiento y Control</strong></h6>
                        <p class="mb-2">
                            La efectividad de estas estrategias será monitoreada mediante indicadores clave de desempeño (KPI) 
                            que permitan medir el progreso y realizar ajustes oportunos en la implementación.
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Frecuencia de evaluación:</strong> Mensual para indicadores operativos, trimestral para análisis de tendencias
                            </div>
                            <div class="col-md-6">
                                <strong>Responsables:</strong> Equipo de Gestión de Servicios en coordinación con proveedores estratégicos
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Formulario de Selección de Períodos -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-search"></i> Seleccionar Períodos para Comparar
            </h3>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif
            
            <form action="{{ route('surveys.complementary-services.transport.compare') }}" method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="period1">Primer Período:</label>
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
                            <label for="period2">Segundo Período:</label>
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service">Servicio:</label>
                            <select name="service" id="service" class="form-control">
                                <option value="both">Ambos Servicios</option>
                                <option value="cafeteria">Solo Cafetería</option>
                                <option value="transport">Solo Transporte</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dependency">Dependencia:</label>
                            <select name="dependency" id="dependency" class="form-control">
                                <option value="all">Todas las Dependencias</option>
                                <option value="Sistemas">Sistemas</option>
                                <option value="Recursos Humanos">Recursos Humanos</option>
                                <option value="Enfermería">Enfermería</option>
                                <option value="Almacén">Almacén</option>
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
    </div>
    @endif
@stop

@section('css')
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
</style>
@stop
