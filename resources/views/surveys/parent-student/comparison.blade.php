@extends('adminlte::page')

@section('title', 'Comparar Períodos - Encuesta Padres-Estudiantes')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>
                <i class="fas fa-chart-line"></i> Comparar Períodos
            </h1>
            <p class="text-muted">Análisis Comparativo Encuesta Padres-Estudiantes</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('surveys.parent-student.index') }}">Encuesta Padres-Estudiantes</a></li>
                <li class="breadcrumb-item active">Análisis Comparativo</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(isset($comparisonData) && $comparisonData)
    <!-- Resumen Ejecutivo del Análisis Comparativo -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> RESUMEN EJECUTIVO DEL ANÁLISIS COMPARATIVO
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-primary mb-4">
                <h5><i class="fas fa-lightbulb"></i> Marco Estratégico de Evaluación Educativa</h5>
                <p class="mb-2">
                    El presente análisis comparativo constituye una herramienta fundamental para la evaluación integral 
                    de la calidad educativa entre los períodos <strong>{{ $comparisonData['period1'] }}</strong> y 
                    <strong>{{ $comparisonData['period2'] }}</strong>. La sistematización de percepciones de padres de familia 
                    y estudiantes permite identificar tendencias estratégicas para la mejora continua de los procesos formativos.
                </p>
                <p class="mb-0">
                    <strong>Alcance metodológico:</strong> Evaluación multidimensional que abarca programa académico, 
                    convivencia escolar, infraestructura institucional y bienestar estudiantil, estableciendo un diagnóstico 
                    integral de la gestión educativa y su evolución temporal.
                </p>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h5 class="text-info font-weight-bold mb-3">
                                <i class="fas fa-users"></i> Participación y Representatividad
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-center border-right">
                                        <h3 class="text-primary">{{ $comparisonData['period1_total'] ?? 0 }}</h3>
                                        <p class="text-muted mb-0">{{ $comparisonData['period1'] }}</p>
                                        <small class="text-muted">Respuestas obtenidas</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <h3 class="text-success">{{ $comparisonData['period2_total'] ?? 0 }}</h3>
                                        <p class="text-muted mb-0">{{ $comparisonData['period2'] }}</p>
                                        <small class="text-muted">Respuestas obtenidas</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                @php 
                                    $p1_total = $comparisonData['period1_total'] ?? 0;
                                    $p2_total = $comparisonData['period2_total'] ?? 0;
                                    $variacion = $p2_total - $p1_total; 
                                @endphp
                                <span class="badge {{ $variacion >= 0 ? 'badge-success' : 'badge-warning' }} badge-lg">
                                    {{ $variacion >= 0 ? '+' : '' }}{{ $variacion }} respuestas
                                    ({{ $p1_total > 0 ? round(($variacion / $p1_total) * 100, 1) : 0 }}%)
                                </span>
                                <br><small class="text-muted">Variación en participación</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h5 class="text-success font-weight-bold mb-3">
                                <i class="fas fa-chart-pie"></i> Indicadores Generales de Desempeño
                            </h5>
                            @if(isset($comparisonData['general_metrics']))
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-graduation-cap text-info"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="text-xs text-muted">Programa Académico</div>
                                            <div class="font-weight-bold text-sm">
                                                {{ number_format($comparisonData['general_metrics']['academic_program_avg'], 1) }}%
                                                @if(isset($comparisonData['general_metrics']['academic_program_trend']))
                                                    <i class="fas fa-arrow-{{ $comparisonData['general_metrics']['academic_program_trend'] == 'up' ? 'up text-success' : ($comparisonData['general_metrics']['academic_program_trend'] == 'down' ? 'down text-danger' : 'right text-muted') }}"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-users text-success"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="text-xs text-muted">Convivencia</div>
                                            <div class="font-weight-bold text-sm">
                                                {{ number_format($comparisonData['general_metrics']['coexistence_avg'], 1) }}%
                                                @if(isset($comparisonData['general_metrics']['coexistence_trend']))
                                                    <i class="fas fa-arrow-{{ $comparisonData['general_metrics']['coexistence_trend'] == 'up' ? 'up text-success' : ($comparisonData['general_metrics']['coexistence_trend'] == 'down' ? 'down text-danger' : 'right text-muted') }}"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-building text-warning"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="text-xs text-muted">Infraestructura</div>
                                            <div class="font-weight-bold text-sm">
                                                {{ number_format($comparisonData['general_metrics']['infrastructure_avg'], 1) }}%
                                                @if(isset($comparisonData['general_metrics']['infrastructure_trend']))
                                                    <i class="fas fa-arrow-{{ $comparisonData['general_metrics']['infrastructure_trend'] == 'up' ? 'up text-success' : ($comparisonData['general_metrics']['infrastructure_trend'] == 'down' ? 'down text-danger' : 'right text-muted') }}"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <i class="fas fa-hands-helping text-danger"></i>
                                        </div>
                                        <div class="flex-fill">
                                            <div class="text-xs text-muted">Bienestar Est.</div>
                                            <div class="font-weight-bold text-sm">
                                                {{ number_format($comparisonData['general_metrics']['student_welfare_avg'], 1) }}%
                                                @if(isset($comparisonData['general_metrics']['student_welfare_trend']))
                                                    <i class="fas fa-arrow-{{ $comparisonData['general_metrics']['student_welfare_trend'] == 'up' ? 'up text-success' : ($comparisonData['general_metrics']['student_welfare_trend'] == 'down' ? 'down text-danger' : 'right text-muted') }}"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                <p>Métricas en procesamiento</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis del Programa Académico -->
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-graduation-cap"></i> ANÁLISIS DEL PROGRAMA ACADÉMICO
            </h3>
        </div>
        <div class="card-body">
            @if(isset($comparisonData['academic_program_period1']) && $comparisonData['academic_program_period1']['total_responses'] > 0)
                
                <div class="alert alert-info mb-4">
                    <h5><i class="fas fa-info-circle"></i> Evaluación Integral del Programa Académico</h5>
                    <p class="mb-2">
                        La evaluación del programa académico representa un elemento fundamental en la formación integral 
                        de nuestros estudiantes, impactando directamente en la calidad educativa, el desarrollo de competencias 
                        y la satisfacción de las familias. La comparación entre {{ $comparisonData['period1'] }} y {{ $comparisonData['period2'] }} 
                        permite identificar tendencias críticas para la excelencia académica.
                    </p>
                    <p class="mb-0">
                        <strong>Base de evaluación:</strong> {{ $comparisonData['academic_program_period1']['total_responses'] }} respuestas 
                        en {{ $comparisonData['period1'] }} versus {{ $comparisonData['academic_program_period2']['total_responses'] }} respuestas 
                        en {{ $comparisonData['period2'] }}, reflejando la percepción de padres y estudiantes sobre la calidad académica.
                    </p>
                </div>

                <!-- Análisis por Dimensiones Académicas -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-info mb-3"><i class="fas fa-analytics"></i> Dimensiones Críticas de Evaluación Académica</h5>
                    </div>
                </div>

                <!-- Calidad de la Enseñanza -->
                <div class="mb-4">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="text-success font-weight-bold">Calidad de la Enseñanza</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La calidad de la enseñanza constituye el pilar fundamental del proceso educativo, 
                                        determinando la efectividad del aprendizaje y el desarrollo de competencias en los estudiantes. 
                                        Su impacto trasciende el aula, influyendo en la formación integral y el futuro académico.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['academic_program_period1']['teaching_quality'] ?? 0 }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['academic_program_period2']['teaching_quality'] ?? 0 }}%</span>
                                        @if(isset($comparisonData['academic_differences']['teaching_quality']))
                                            @php $diff = $comparisonData['academic_differences']['teaching_quality']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['academic_differences']['teaching_quality']))
                                        @php $diff = $comparisonData['academic_differences']['teaching_quality']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora sustancial</strong> que refleja fortalecimiento en metodologías pedagógicas y competencias docentes.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Deterioro crítico</strong> que requiere intervención inmediata en procesos de enseñanza-aprendizaje.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad pedagógica</strong> manteniendo los estándares de calidad establecidos.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['academic_differences']['teaching_quality']))
                                        @php $diff = $comparisonData['academic_differences']['teaching_quality']; @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $diff['trend_class'] == 'text-success' ? 'bg-success' : ($diff['trend_class'] == 'text-danger' ? 'bg-danger' : 'bg-secondary') }}" 
                                                 style="width: {{ $comparisonData['academic_program_period2']['teaching_quality'] ?? 0 }}%">
                                                {{ $comparisonData['academic_program_period2']['teaching_quality'] ?? 0 }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metodologías Pedagógicas -->
                <div class="mb-4">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h6 class="text-info font-weight-bold">Metodologías y Estrategias Pedagógicas</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        Las metodologías pedagógicas empleadas determinan la efectividad del proceso de enseñanza-aprendizaje, 
                                        la motivación estudiantil y el desarrollo de competencias cognitivas, sociales y emocionales. 
                                        Su evaluación permite optimizar las estrategias didácticas institucionales.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['academic_program_period1']['teaching_methodologies'] ?? 0 }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['academic_program_period2']['teaching_methodologies'] ?? 0 }}%</span>
                                        @if(isset($comparisonData['academic_differences']['teaching_methodologies']))
                                            @php $diff = $comparisonData['academic_differences']['teaching_methodologies']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['academic_differences']['teaching_methodologies']))
                                        @php $diff = $comparisonData['academic_differences']['teaching_methodologies']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Evolución positiva</strong> en la implementación de estrategias pedagógicas innovadoras y efectivas.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Retroceso metodológico</strong> que requiere revisión y actualización de prácticas pedagógicas.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Consistencia</strong> en la aplicación de metodologías pedagógicas establecidas.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['academic_differences']['teaching_methodologies']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: {{ $comparisonData['academic_program_period2']['teaching_methodologies'] ?? 0 }}%">
                                                {{ $comparisonData['academic_program_period2']['teaching_methodologies'] ?? 0 }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comunicación Académica -->
                <div class="mb-4">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <h6 class="text-primary font-weight-bold">Comunicación y Retroalimentación Académica</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La comunicación efectiva entre docentes, estudiantes y familias constituye un elemento fundamental 
                                        para el éxito académico. La retroalimentación oportuna y constructiva permite identificar fortalezas, 
                                        superar dificultades y potenciar el aprendizaje significativo.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['academic_program_period1']['academic_communication'] ?? 0 }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['academic_program_period2']['academic_communication'] ?? 0 }}%</span>
                                        @if(isset($comparisonData['academic_differences']['academic_communication']))
                                            @php $diff = $comparisonData['academic_differences']['academic_communication']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['academic_differences']['academic_communication']))
                                        @php $diff = $comparisonData['academic_differences']['academic_communication']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Fortalecimiento</strong> en los canales de comunicación y calidad de la retroalimentación académica.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Debilitamiento</strong> que puede afectar el seguimiento académico y la participación familiar.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad</strong> en los procesos de comunicación académica establecidos.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['academic_differences']['academic_communication']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $comparisonData['academic_program_period2']['academic_communication'] ?? 0 }}%">
                                                {{ $comparisonData['academic_program_period2']['academic_communication'] ?? 0 }}%
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
                    No hay datos suficientes del programa académico para los períodos seleccionados.
                </div>
            @endif
        </div>
    </div>

    <!-- Análisis de Convivencia Escolar -->
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> ANÁLISIS DE CONVIVENCIA ESCOLAR
            </h3>
        </div>
        <div class="card-body">
            @if(isset($comparisonData['coexistence_period1']) && $comparisonData['coexistence_period1']['total_responses'] > 0)
                
                <div class="alert alert-success mb-4">
                    <h5><i class="fas fa-info-circle"></i> Evaluación Integral de la Convivencia Escolar</h5>
                    <p class="mb-2">
                        La convivencia escolar constituye el fundamento del ambiente educativo, determinando la calidad 
                        de las relaciones interpersonales, el bienestar estudiantil y el clima institucional. 
                        La comparación entre {{ $comparisonData['period1'] }} y {{ $comparisonData['period2'] }} 
                        permite evaluar la evolución del ambiente educativo y la efectividad de las estrategias implementadas.
                    </p>
                    <p class="mb-0">
                        <strong>Base de evaluación:</strong> {{ $comparisonData['coexistence_period1']['total_responses'] }} respuestas 
                        en {{ $comparisonData['period1'] }} versus {{ $comparisonData['coexistence_period2']['total_responses'] }} respuestas 
                        en {{ $comparisonData['period2'] }}, reflejando la percepción sobre el ambiente escolar.
                    </p>
                </div>

                <!-- Clima Escolar -->
                <div class="mb-4">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="text-success font-weight-bold">Clima y Ambiente Escolar</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        El clima escolar refleja la calidad del ambiente educativo, influenciando directamente 
                                        en el bienestar estudiantil, la motivación para el aprendizaje y el desarrollo 
                                        socioemocional de la comunidad educativa.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['coexistence_period1']['school_climate'] ?? 0 }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['coexistence_period2']['school_climate'] ?? 0 }}%</span>
                                        @if(isset($comparisonData['coexistence_differences']['school_climate']))
                                            @php $diff = $comparisonData['coexistence_differences']['school_climate']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['coexistence_differences']['school_climate']))
                                        @php $diff = $comparisonData['coexistence_differences']['school_climate']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Fortalecimiento</strong> del ambiente educativo que favorece el bienestar y aprendizaje.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Deterioro</strong> que requiere intervención para mejorar el clima institucional.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad</strong> en la percepción del ambiente escolar.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['coexistence_differences']['school_climate']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $comparisonData['coexistence_period2']['school_climate'] ?? 0 }}%">
                                                {{ $comparisonData['coexistence_period2']['school_climate'] ?? 0 }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resolución de Conflictos -->
                <div class="mb-4">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h6 class="text-info font-weight-bold">Gestión y Resolución de Conflictos</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        La efectividad en la gestión de conflictos determina la calidad de la convivencia, 
                                        la confianza en los procesos institucionales y el desarrollo de habilidades 
                                        socioemocionales para la resolución pacífica de diferencias.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['coexistence_period1']['conflict_resolution'] ?? 0 }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['coexistence_period2']['conflict_resolution'] ?? 0 }}%</span>
                                        @if(isset($comparisonData['coexistence_differences']['conflict_resolution']))
                                            @php $diff = $comparisonData['coexistence_differences']['conflict_resolution']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['coexistence_differences']['conflict_resolution']))
                                        @php $diff = $comparisonData['coexistence_differences']['conflict_resolution']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora significativa</strong> en los procesos de mediación y resolución de conflictos.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Debilitamiento</strong> que requiere fortalecimiento de protocolos y capacitación.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Consistencia</strong> en la gestión de situaciones conflictivas.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['coexistence_differences']['conflict_resolution']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: {{ $comparisonData['coexistence_period2']['conflict_resolution'] ?? 0 }}%">
                                                {{ $comparisonData['coexistence_period2']['conflict_resolution'] ?? 0 }}%
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
                    No hay datos suficientes de convivencia escolar para los períodos seleccionados.
                </div>
            @endif
        </div>
    </div>

    <!-- Análisis de Infraestructura -->
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-building"></i> ANÁLISIS DE INFRAESTRUCTURA INSTITUCIONAL
            </h3>
        </div>
        <div class="card-body">
            @if(isset($comparisonData['infrastructure_period1']) && $comparisonData['infrastructure_period1']['total_responses'] > 0)
                
                <div class="alert alert-warning mb-4">
                    <h5><i class="fas fa-info-circle"></i> Evaluación Integral de la Infraestructura</h5>
                    <p class="mb-2">
                        La infraestructura institucional constituye la base física que sustenta el proceso educativo, 
                        impactando directamente en la calidad del aprendizaje, la seguridad estudiantil y la imagen 
                        institucional. La comparación entre {{ $comparisonData['period1'] }} y {{ $comparisonData['period2'] }} 
                        permite evaluar las mejoras implementadas y las necesidades de desarrollo futuro.
                    </p>
                    <p class="mb-0">
                        <strong>Base de evaluación:</strong> {{ $comparisonData['infrastructure_period1']['total_responses'] }} respuestas 
                        en {{ $comparisonData['period1'] }} versus {{ $comparisonData['infrastructure_period2']['total_responses'] }} respuestas 
                        en {{ $comparisonData['period2'] }}, reflejando la percepción sobre las condiciones físicas institucionales.
                    </p>
                </div>

                <!-- Instalaciones Académicas -->
                <div class="mb-4">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="text-success font-weight-bold">Calidad de las Instalaciones Académicas</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        Las instalaciones académicas (aulas, laboratorios, biblioteca) determinan las 
                                        condiciones óptimas para el proceso de enseñanza-aprendizaje, influenciando 
                                        en la motivación estudiantil y la efectividad pedagógica.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['infrastructure_period1']['academic_facilities'] ?? 0 }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['infrastructure_period2']['academic_facilities'] ?? 0 }}%</span>
                                        @if(isset($comparisonData['infrastructure_differences']['academic_facilities']))
                                            @php $diff = $comparisonData['infrastructure_differences']['academic_facilities']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['infrastructure_differences']['academic_facilities']))
                                        @php $diff = $comparisonData['infrastructure_differences']['academic_facilities']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Mejora sustancial</strong> en la calidad y adecuación de espacios académicos.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Deterioro</strong> que requiere inversión en mantenimiento y actualización.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Estabilidad</strong> en las condiciones de las instalaciones académicas.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['infrastructure_differences']['academic_facilities']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $comparisonData['infrastructure_period2']['academic_facilities'] ?? 0 }}%">
                                                {{ $comparisonData['infrastructure_period2']['academic_facilities'] ?? 0 }}%
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tecnología Educativa -->
                <div class="mb-4">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <h6 class="text-info font-weight-bold">Recursos Tecnológicos Educativos</h6>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        Los recursos tecnológicos constituyen herramientas fundamentales para la educación 
                                        contemporánea, potenciando metodologías innovadoras, desarrollando competencias 
                                        digitales y preparando a los estudiantes para los desafíos del siglo XXI.
                                    </p>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge badge-info mr-2">{{ $comparisonData['period1'] }}: {{ $comparisonData['infrastructure_period1']['technology_resources'] ?? 0 }}%</span>
                                        <span class="badge badge-success mr-2">{{ $comparisonData['period2'] }}: {{ $comparisonData['infrastructure_period2']['technology_resources'] ?? 0 }}%</span>
                                        @if(isset($comparisonData['infrastructure_differences']['technology_resources']))
                                            @php $diff = $comparisonData['infrastructure_differences']['technology_resources']; @endphp
                                            <span class="badge {{ $diff['trend_class'] == 'text-success' ? 'badge-success' : ($diff['trend_class'] == 'text-danger' ? 'badge-danger' : 'badge-secondary') }}">
                                                {{ $diff['difference'] > 0 ? '+' : '' }}{{ $diff['difference'] }}%
                                            </span>
                                        @endif
                                    </div>
                                    @if(isset($comparisonData['infrastructure_differences']['technology_resources']))
                                        @php $diff = $comparisonData['infrastructure_differences']['technology_resources']; @endphp
                                        <p class="mb-0 text-sm">
                                            @if($diff['trend'] == 'mejora')
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> <strong>Evolución positiva</strong> en la disponibilidad y calidad de recursos tecnológicos.
                                                </span>
                                            @elseif($diff['trend'] == 'disminucion')
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> <strong>Retroceso tecnológico</strong> que requiere actualización e inversión en equipamiento.
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> <strong>Consistencia</strong> en la dotación tecnológica institucional.
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-center">
                                    @if(isset($comparisonData['infrastructure_differences']['technology_resources']))
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: {{ $comparisonData['infrastructure_period2']['technology_resources'] ?? 0 }}%">
                                                {{ $comparisonData['infrastructure_period2']['technology_resources'] ?? 0 }}%
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
                    No hay datos suficientes de infraestructura para los períodos seleccionados.
                </div>
            @endif
        </div>
    </div>

    <!-- Plan de Acción para Mejora Continua -->
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i> PLAN ESTRATÉGICO DE MEJORA CONTINUA EDUCATIVA
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success mb-4">
                <h5><i class="fas fa-lightbulb"></i> Marco Estratégico para la Excelencia Educativa</h5>
                <p class="mb-2">
                    La implementación de un plan estructurado de mejora continua constituye la base fundamental para 
                    la evolución sostenible de la calidad educativa. Las acciones definidas responden a un enfoque 
                    preventivo y correctivo, orientado a la excelencia académica y el bienestar integral de la comunidad educativa.
                </p>
                <p class="mb-0">
                    <strong>Objetivo:</strong> Garantizar la prestación de servicios educativos de alta calidad que superen las 
                    expectativas de estudiantes y familias, contribuyendo al desarrollo integral y la formación de ciudadanos competentes.
                </p>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h5 class="text-success font-weight-bold mb-3">
                                <i class="fas fa-graduation-cap"></i> ESTRATEGIAS DE MEJORA - PROGRAMA ACADÉMICO
                            </h5>
                            
                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-chalkboard-teacher"></i> Fortalecimiento Pedagógico</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Capacitación docente continua</strong> en metodologías innovadoras y competencias digitales
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Implementación de estrategias diferenciadas</strong> para atender estilos de aprendizaje diversos
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Desarrollo de proyectos interdisciplinarios</strong> que fomenten el aprendizaje significativo
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-comments"></i> Comunicación Educativa</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Fortalecimiento de canales de comunicación</strong> entre docentes, estudiantes y familias
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Retroalimentación sistemática y oportuna</strong> sobre el progreso académico estudiantil
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-chart-line"></i> Evaluación y Seguimiento</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Sistemas de evaluación formativa</strong> que orienten el proceso de aprendizaje
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Análisis periódico de resultados</strong> para ajustes curriculares y metodológicos
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
                                <i class="fas fa-users"></i> ESTRATEGIAS DE MEJORA - CONVIVENCIA Y BIENESTAR
                            </h5>
                            
                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-handshake"></i> Fortalecimiento de la Convivencia</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Programas de habilidades socioemocionales</strong> para estudiantes y docentes
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-success"></i> 
                                        <strong>Protocolos de mediación escolar</strong> para resolución pacífica de conflictos
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-heart"></i> Bienestar Estudiantil</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Programas de apoyo psicosocial</strong> para estudiantes en situación de vulnerabilidad
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-info"></i> 
                                        <strong>Actividades extracurriculares</strong> que promuevan el desarrollo integral
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary"><i class="fas fa-tools"></i> Mejora de Infraestructura</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Plan de mantenimiento preventivo</strong> de instalaciones y equipos educativos
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-warning"></i> 
                                        <strong>Actualización tecnológica continua</strong> para apoyar procesos de enseñanza-aprendizaje
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h6 class="text-primary"><i class="fas fa-family"></i> Participación Familiar</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-secondary"></i> 
                                        <strong>Escuela de padres</strong> para fortalecer el acompañamiento familiar
                                    </li>
                                    <li class="list-group-item border-0 py-1">
                                        <i class="fas fa-arrow-right text-secondary"></i> 
                                        <strong>Canales de participación activa</strong> en la gestión educativa institucional
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
                                <strong>Frecuencia de evaluación:</strong> Bimensual para indicadores académicos, trimestral para análisis de convivencia
                            </div>
                            <div class="col-md-6">
                                <strong>Responsables:</strong> Equipo Directivo en coordinación con docentes, estudiantes y familias
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
            
            <form action="{{ route('surveys.parent-student.compare') }}" method="GET">
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
                            <label for="category">Categoría:</label>
                            <select name="category" id="category" class="form-control">
                                <option value="all">Todas las Categorías</option>
                                <option value="academic_program">Programa Académico</option>
                                <option value="coexistence">Convivencia Escolar</option>
                                <option value="infrastructure">Infraestructura</option>
                                <option value="student_welfare">Bienestar Estudiantil</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="grade">Grado:</label>
                            <select name="grade" id="grade" class="form-control">
                                <option value="all">Todos los Grados</option>
                                <option value="Preescolar">Preescolar</option>
                                <option value="Primaria">Primaria</option>
                                <option value="Secundaria">Secundaria</option>
                                <option value="Media">Media</option>
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
