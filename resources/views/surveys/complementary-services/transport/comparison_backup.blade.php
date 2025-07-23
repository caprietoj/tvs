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
                <i class="fas fa-analytics"></i> Resumen Ejecutivo de Comparación
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    {{ $comparisonData['period1'] }} vs {{ $comparisonData['period2'] }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Metodología de Evaluación</h5>
                <p>Las variables consideradas fueron las mismas evaluadas en el período anterior para medir el impacto de las acciones de mejora proyectadas.</p>
                <p><strong>Comparativo {{ $comparisonData['period1'] }} vs {{ $comparisonData['period2'] }}:</strong></p>
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
                        </div>
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
                
                <!-- Calidad y Sabor -->
                <div class="mb-4">
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

                <!-- Porciones Ofrecidas -->
                <div class="mb-4">
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

                <!-- Menú Ofrecido -->
                <div class="mb-4">
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

                <!-- Variedad del Menú -->
                <div class="mb-4">
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

                <!-- Temperatura -->
                <div class="mb-4">
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

                <!-- Limpieza -->
                <div class="mb-4">
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

                <!-- Trato del Personal -->
                <div class="mb-4">
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
                
                <div class="alert alert-info">
                    <p>En el caso del servicio de transporte, se conserva el mismo aliado estratégico, destacando los resultados obtenidos y el trabajo realizado.</p>
                    <p><strong>Las variables consideradas en la encuesta fueron:</strong></p>
                </div>

                <!-- Puntualidad -->
                <div class="mb-4">
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

                <!-- Limpieza de los Vehículos -->
                <div class="mb-4">
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

                <!-- Trato del Personal -->
                <div class="mb-4">
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

                <!-- Comunicación Oportuna y Asertiva -->
                <div class="mb-4">
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

            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No hay datos suficientes del servicio de transporte para los períodos seleccionados.
                </div>
            @endif
        </div>
    </div>

    <!-- Plan de Acción para Mejora Continua -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i> PLAN DE ACCIÓN PARA MEJORA CONTINUA
            </h3>
        </div>
        <div class="card-body">
            <h5 class="text-primary"><strong>Toma de Decisiones Enfocadas en la Mejora Continua del Proceso</strong></h5>
            
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Servicio de Cafetería:</strong></h6>
                    <ul>
                        <li>• Validación de recetas estándar</li>
                        <li>• Seguimiento permanente del personal manipulador de alimentos</li>
                        <li>• Verificación de calidad de insumos</li>
                        <li>• Verificación permanente de menaje para garantizar porciones adecuadas</li>
                        <li>• Reuniones semanales para verificación de propuestas de menús</li>
                        <li>• Apoyo del Comité de alimentos en la estructura de menús balanceados</li>
                        <li>• Verificación permanente de equipos para conservar temperaturas</li>
                        <li>• Revisiones permanentes del sitio y zonas de mayor circulación</li>
                        <li>• Retroalimentaciones permanentes al equipo de trabajo</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><strong>Servicio de Transporte:</strong></h6>
                    <ul>
                        <li>• Elaboración y divulgación de acuerdos esenciales entre las partes</li>
                        <li>• Capacitación permanente del personal operativo</li>
                        <li>• Respuestas oportunas con trazabilidad y soportes técnicos</li>
                        <li>• Reporte oportuno de novedades a través de herramientas de apoyo</li>
                        <li>• Verificación permanente del estado de los vehículos</li>
                        <li>• Registros actualizados y reales de los recorridos diarios</li>
                        <li>• Retroalimentación permanente del estado del servicio</li>
                    </ul>
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
