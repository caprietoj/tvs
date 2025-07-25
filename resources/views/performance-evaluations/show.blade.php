@extends('adminlte::page')

@section('title', 'Detalle de Evaluación')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Evaluación de Desempeño - {{ $performanceEvaluation->user->name }}</h1>
        <a href="{{ route('performance-evaluations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Información General -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información General</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Empleado:</strong></td>
                            <td>{{ $performanceEvaluation->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $performanceEvaluation->user->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Evaluador:</strong></td>
                            <td>{{ $performanceEvaluation->evaluator->name ?? 'No asignado' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tipo de Evaluación:</strong></td>
                            <td>
                                @if($performanceEvaluation->evaluation_type === 'periodo_prueba')
                                    <span class="badge badge-info">Período de Prueba</span>
                                @else
                                    <span class="badge badge-primary">Periódica</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Período Evaluado:</strong></td>
                            <td>
                                {{ $performanceEvaluation->evaluation_period_start->format('d/m/Y') }} - 
                                {{ $performanceEvaluation->evaluation_period_end->format('d/m/Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>
                                @if($performanceEvaluation->status === 'draft')
                                    <span class="badge badge-secondary">Borrador</span>
                                @elseif($performanceEvaluation->status === 'self_completed')
                                    <span class="badge badge-warning">Autoevaluación Completada</span>
                                @elseif($performanceEvaluation->status === 'supervisor_review')
                                    <span class="badge badge-info">En Revisión</span>
                                @elseif($performanceEvaluation->status === 'completed')
                                    <span class="badge badge-success">Completada</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Nivel de Desempeño:</strong></td>
                            <td>
                                @if($performanceEvaluation->performance_level)
                                    @if($performanceEvaluation->performance_level === 'Superior')
                                        <span class="badge badge-success">{{ $performanceEvaluation->performance_level }}</span>
                                    @elseif($performanceEvaluation->performance_level === 'Alto')
                                        <span class="badge badge-info">{{ $performanceEvaluation->performance_level }}</span>
                                    @elseif($performanceEvaluation->performance_level === 'Básico')
                                        <span class="badge badge-warning">{{ $performanceEvaluation->performance_level }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ $performanceEvaluation->performance_level }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Puntaje Final:</strong></td>
                            <td>
                                @if($performanceEvaluation->final_average_score)
                                    <strong>{{ number_format($performanceEvaluation->final_average_score, 1) }}/100</strong>
                                @elseif($performanceEvaluation->final_self_score)
                                    {{ number_format($performanceEvaluation->final_self_score, 1) }}/100 <small>(Solo autoevaluación)</small>
                                @else
                                    <span class="text-muted">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Disponibles -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Acciones Disponibles</h3>
        </div>
        <div class="card-body">
            <div class="btn-group">
                @if($performanceEvaluation->user_id === auth()->id() && $performanceEvaluation->canSelfEvaluate())
                    <a href="{{ route('performance-evaluations.self-evaluate', $performanceEvaluation) }}" 
                       class="btn btn-success">
                        <i class="fas fa-user-edit"></i> 
                        @if($performanceEvaluation->status === 'draft')
                            Realizar Autoevaluación
                        @else
                            Editar Autoevaluación
                        @endif
                    </a>
                @endif
                  @if((auth()->user()->hasRole('admin') || 
                     (auth()->user()->can('evaluate-as-supervisor') && $performanceEvaluation->evaluator_id === auth()->id())) 
                     && $performanceEvaluation->canSupervisorEvaluate())
                    <a href="{{ route('performance-evaluations.supervisor-evaluate', $performanceEvaluation) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-user-check"></i> Evaluar como Supervisor
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Sesiones de Retroalimentación -->
    @if($performanceEvaluation->status === 'supervisor_completed')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-comments text-primary"></i> 
                Sesiones de Retroalimentación
            </h3>
        </div>
        <div class="card-body">
            @php
                $feedbackSessions = $performanceEvaluation->feedbackSessions()->latest('scheduled_datetime')->get();
            @endphp
            
            @if($feedbackSessions->count() > 0)
                <div class="row">
                    @foreach($feedbackSessions as $session)
                    <div class="col-md-6 mb-3">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-calendar"></i> 
                                            {{ $session->formatted_datetime }}
                                        </h6>
                                        @if($session->location)
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-map-marker-alt"></i> {{ $session->location }}
                                        </p>
                                        @endif
                                        @if($session->status === 'realizada')
                                            <small class="text-muted">
                                                Completada el {{ $session->completed_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </div>
                                    <span class="badge 
                                        @if($session->status === 'programada') badge-warning
                                        @elseif($session->status === 'realizada') badge-success
                                        @else badge-secondary @endif
                                    ">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('feedback-sessions.show', $session) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> No hay sesiones de retroalimentación programadas</h6>
                    <p class="mb-2">Una sesión de retroalimentación permite:</p>
                    <ul class="mb-3">
                        <li>Discutir los resultados de la evaluación</li>
                        <li>Identificar fortalezas y áreas de mejora</li>
                        <li>Establecer metas para el próximo período</li>
                        <li>Planificar el desarrollo profesional</li>
                    </ul>
                    
                    @if(auth()->id() === $performanceEvaluation->evaluator_id || auth()->user()->hasRole('admin'))
                    <a href="{{ route('feedback-sessions.create', $performanceEvaluation) }}" class="btn btn-success">
                        <i class="fas fa-calendar-plus"></i> Programar Sesión de Retroalimentación
                    </a>
                    @endif
                </div>
            @endif
            
            @if($feedbackSessions->count() > 0 && (auth()->id() === $performanceEvaluation->evaluator_id || auth()->user()->hasRole('admin')))
                <div class="text-center mt-3">
                    <a href="{{ route('feedback-sessions.create', $performanceEvaluation) }}" class="btn btn-outline-success">
                        <i class="fas fa-plus"></i> Programar Nueva Sesión
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Resumen de Puntuaciones -->
    @if($performanceEvaluation->objectives_self_score || $performanceEvaluation->objectives_supervisor_score)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen de Puntuaciones</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Autoevaluación</h5>
                    <table class="table table-bordered">
                        <tr>
                            <td>Objetivos del Cargo (30%)</td>
                            <td class="text-center">
                                {{ $performanceEvaluation->objectives_self_score ? number_format($performanceEvaluation->objectives_self_score, 1) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Competencias Organizacionales (70%)</td>
                            <td class="text-center">
                                {{ $performanceEvaluation->competencies_self_score ? number_format($performanceEvaluation->competencies_self_score, 1) : '-' }}
                            </td>
                        </tr>
                        <tr class="table-info">
                            <td><strong>Puntaje Total</strong></td>
                            <td class="text-center">
                                <strong>{{ $performanceEvaluation->final_self_score ? number_format($performanceEvaluation->final_self_score, 1) : '-' }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
                
                @if($performanceEvaluation->objectives_supervisor_score)
                <div class="col-md-6">
                    <h5>Evaluación del Supervisor</h5>
                    <table class="table table-bordered">
                        <tr>
                            <td>Objetivos del Cargo (30%)</td>
                            <td class="text-center">
                                {{ number_format($performanceEvaluation->objectives_supervisor_score, 1) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Competencias Organizacionales (70%)</td>
                            <td class="text-center">
                                {{ number_format($performanceEvaluation->competencies_supervisor_score, 1) }}
                            </td>
                        </tr>
                        <tr class="table-info">
                            <td><strong>Puntaje Total</strong></td>
                            <td class="text-center">
                                <strong>{{ number_format($performanceEvaluation->final_supervisor_score, 1) }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
                @endif
            </div>
            
            @if($performanceEvaluation->final_average_score)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>Puntaje Final Promedio: {{ number_format($performanceEvaluation->final_average_score, 1) }}/100</h4>
                        <p class="mb-0">Nivel de Desempeño: 
                            <strong>{{ $performanceEvaluation->performance_level }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Observaciones -->
    @if($performanceEvaluation->self_observations || $performanceEvaluation->supervisor_observations)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Observaciones</h3>
        </div>
        <div class="card-body">
            @if($performanceEvaluation->self_observations)
            <div class="mb-3">
                <h5>Observaciones del Empleado:</h5>
                <div class="alert alert-light">
                    {{ $performanceEvaluation->self_observations }}
                </div>
            </div>
            @endif
            
            @if($performanceEvaluation->supervisor_observations)
            <div class="mb-3">
                <h5>Observaciones del Supervisor:</h5>
                <div class="alert alert-light">
                    {{ $performanceEvaluation->supervisor_observations }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Detalles de la Evaluación -->
    @if($performanceEvaluation->objectives_section || $performanceEvaluation->organizational_competencies)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clipboard-list"></i> Detalles de la Evaluación
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="evaluation-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="objectives-tab" data-toggle="tab" href="#objectives" role="tab">
                                <i class="fas fa-target"></i> Objetivos del Cargo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="competencies-tab" data-toggle="tab" href="#competencies" role="tab">
                                <i class="fas fa-users"></i> Competencias Organizacionales
                            </a>
                        </li>
                        @if($performanceEvaluation->technical_competencies)
                        <li class="nav-item">
                            <a class="nav-link" id="technical-tab" data-toggle="tab" href="#technical" role="tab">
                                <i class="fas fa-cogs"></i> Competencias Técnicas
                            </a>
                        </li>
                        @endif
                        @if($performanceEvaluation->safety_health_section)
                        <li class="nav-item">
                            <a class="nav-link" id="safety-tab" data-toggle="tab" href="#safety" role="tab">
                                <i class="fas fa-shield-alt"></i> Seguridad y Salud
                            </a>
                        </li>
                        @endif
                    </ul>
                    
                    <div class="tab-content mt-3" id="evaluation-tabsContent">
                        <!-- Objetivos del Cargo -->
                        <div class="tab-pane fade show active" id="objectives" role="tabpanel">
                            @if($performanceEvaluation->objectives_section)
                            @foreach($objectivesQuestions as $sectionKey => $section)
                            @if(isset($performanceEvaluation->objectives_section[$sectionKey]))
                            <div class="mb-4">
                                <h5 class="text-primary">
                                    <i class="fas fa-flag"></i> {{ $section['title'] }} 
                                    <small class="text-muted">({{ $section['weight'] * 100 }}% del peso de objetivos)</small>
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="50%">Criterio</th>
                                                <th width="25%" class="text-center">Autoevaluación</th>
                                                @if($performanceEvaluation->objectives_supervisor_score)
                                                <th width="25%" class="text-center">Evaluación Supervisor</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($section['questions'] as $questionKey => $question)
                                            @if(isset($performanceEvaluation->objectives_section[$sectionKey][$questionKey]))
                                            <tr>
                                                <td>{{ $question }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-success">
                                                        {{ $performanceEvaluation->objectives_section[$sectionKey][$questionKey] }}
                                                    </span>
                                                </td>
                                                @if($performanceEvaluation->objectives_section_supervisor && isset($performanceEvaluation->objectives_section_supervisor[$sectionKey][$questionKey]))
                                                <td class="text-center">
                                                    <span class="badge badge-info">
                                                        {{ $performanceEvaluation->objectives_section_supervisor[$sectionKey][$questionKey] }}
                                                    </span>
                                                </td>
                                                @elseif($performanceEvaluation->objectives_supervisor_score)
                                                <td class="text-center">
                                                    <span class="text-muted">-</span>
                                                </td>
                                                @endif
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            @endforeach
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No se han completado los objetivos del cargo.
                            </div>
                            @endif
                        </div>
                        
                        <!-- Competencias Organizacionales -->
                        <div class="tab-pane fade" id="competencies" role="tabpanel">
                            @if($performanceEvaluation->organizational_competencies)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="50%">Competencia</th>
                                            <th width="25%" class="text-center">Autoevaluación</th>
                                            @if($performanceEvaluation->competencies_supervisor_score)
                                            <th width="25%" class="text-center">Evaluación Supervisor</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($organizationalCompetencies as $competencyKey => $competency)
                                        @if(isset($performanceEvaluation->organizational_competencies[$competencyKey]))
                                        <tr>
                                            <td>{{ $competency }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-success">
                                                    {{ $performanceEvaluation->organizational_competencies[$competencyKey] }}
                                                </span>
                                            </td>
                                            @if($performanceEvaluation->organizational_competencies_supervisor && isset($performanceEvaluation->organizational_competencies_supervisor[$competencyKey]))
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    {{ $performanceEvaluation->organizational_competencies_supervisor[$competencyKey] }}
                                                </span>
                                            </td>
                                            @elseif($performanceEvaluation->competencies_supervisor_score)
                                            <td class="text-center">
                                                <span class="text-muted">-</span>
                                            </td>
                                            @endif
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No se han completado las competencias organizacionales.
                            </div>
                            @endif
                        </div>
                        
                        <!-- Competencias Técnicas -->
                        @if($performanceEvaluation->technical_competencies)
                        <div class="tab-pane fade" id="technical" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="50%">Competencia</th>
                                            <th width="50%">Respuesta del Empleado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($technicalCompetencies as $competencyKey => $competency)
                                        @if(isset($performanceEvaluation->technical_competencies[$competencyKey]))
                                        <tr>
                                            <td><strong>{{ $competency['title'] }}</strong></td>
                                            <td>{{ $performanceEvaluation->technical_competencies[$competencyKey] ?? 'No especificado' }}</td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Seguridad y Salud en el Trabajo -->
                        @if($performanceEvaluation->safety_health_section)
                        <div class="tab-pane fade" id="safety" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="70%">Pregunta</th>
                                            <th width="30%" class="text-center">Respuesta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($safetyHealthQuestions as $questionKey => $question)
                                        @if(isset($performanceEvaluation->safety_health_section[$questionKey]))
                                        <tr>
                                            <td>{{ $question }}</td>
                                            <td class="text-center">
                                                @if($performanceEvaluation->safety_health_section[$questionKey] === 'si')
                                                    <span class="badge badge-success">Sí</span>
                                                @else
                                                    <span class="badge badge-danger">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Escala de puntuación -->
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle"></i> Escala de Puntuación:</h6>
                        <strong>1:</strong> No cumple - 
                        <strong>2:</strong> Aceptable - 
                        <strong>3:</strong> Cumple con lo establecido sin proactividad - 
                        <strong>4:</strong> Buen desempeño con características proactivas - 
                        <strong>5:</strong> Supera las expectativas de desempeño
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Fechas Importantes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial</h3>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="time-label">
                    <span class="bg-secondary">{{ $performanceEvaluation->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <i class="fas fa-plus bg-primary"></i>
                    <div class="timeline-item">
                        <div class="timeline-body">
                            Evaluación creada
                        </div>
                    </div>
                </div>
                
                @if($performanceEvaluation->self_evaluation_completed_at)
                <div class="time-label">
                    <span class="bg-success">{{ $performanceEvaluation->self_evaluation_completed_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <i class="fas fa-user-edit bg-success"></i>
                    <div class="timeline-item">
                        <div class="timeline-body">
                            Autoevaluación completada por {{ $performanceEvaluation->user->name }}
                        </div>
                    </div>
                </div>
                @endif
                
                @if($performanceEvaluation->supervisor_evaluation_completed_at)
                <div class="time-label">
                    <span class="bg-info">{{ $performanceEvaluation->supervisor_evaluation_completed_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <i class="fas fa-user-check bg-info"></i>
                    <div class="timeline-item">
                        <div class="timeline-body">
                            Evaluación del supervisor completada por {{ $performanceEvaluation->evaluator->name }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .timeline {
        position: relative;
        margin: 0 0 30px 0;
        padding: 0;
        list-style: none;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }
    
    .timeline > li {
        position: relative;
        margin-right: 10px;
        margin-bottom: 15px;
    }
    
    .timeline-item {
        background: #fff;
        border-radius: 3px;
        margin-left: 60px;
        margin-right: 15px;
        margin-top: -2px;
        padding: 0;
        position: relative;
    }
    
    .timeline-item .timeline-body {
        padding: 10px;
        font-size: 16px;
        line-height: 1.4;
    }
    
    .time-label > span {
        font-weight: 600;
        color: #fff;
        font-size: 12px;
        padding: 5px 10px;
        display: inline-block;
        border-radius: 4px;
        line-height: 1.2;
    }
    
    .timeline > div > .fa,
    .timeline > div > .fab,
    .timeline > div > .fad,
    .timeline > div > .fal,
    .timeline > div > .far,
    .timeline > div > .fas {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #666;
        background: #d2d6de;
        border-radius: 50%;
        text-align: center;
        left: 18px;
        top: 0;
    }
    
    /* Estilos para las tabs de evaluación */
    .nav-tabs .nav-link {
        border: 1px solid transparent;
        border-radius: 0.25rem 0.25rem 0 0;
        color: #495057;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
        color: #007bff;
    }
    
    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: 600;
    }
    
    .tab-content {
        border: 1px solid #dee2e6;
        border-top: none;
        padding: 1rem;
        border-radius: 0 0 0.25rem 0.25rem;
        background-color: #fff;
    }
    
    .table-bordered th {
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .table-bordered td {
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.875em;
        padding: 0.375rem 0.75rem;
    }
    
    .text-primary {
        color: #007bff !important;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Verificar si hay mensaje de evaluación completada y mostrar modal
    @if(session('evaluation_completed') && session('show_feedback_session_option'))
        setTimeout(function() {
            showFeedbackSessionModal();
        }, 1500);
    @endif
});

// Función para mostrar modal de programar sesión de retroalimentación
function showFeedbackSessionModal() {
    Swal.fire({
        title: '¡Evaluación Completada!',
        html: `
            <div class="text-left">
                <p class="mb-3">La evaluación del supervisor ha sido completada exitosamente.</p>
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb"></i> ¿Desea programar una sesión de retroalimentación?</h6>
                    <p class="mb-2">Una sesión de retroalimentación permite:</p>
                    <ul class="text-left mb-0">
                        <li>Discutir los resultados de la evaluación</li>
                        <li>Identificar fortalezas y áreas de mejora</li>
                        <li>Establecer metas para el próximo período</li>
                        <li>Planificar el desarrollo profesional</li>
                    </ul>
                </div>
                <p class="text-muted"><small>Puede programar la sesión ahora o hacerlo más tarde desde esta misma página.</small></p>
            </div>
        `,
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-calendar-plus"></i> Programar Sesión',
        cancelButtonText: 'Ahora No',
        width: '600px',
        customClass: {
            htmlContainer: 'text-left'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir a la página de programar sesión
            window.location.href = '{{ route("feedback-sessions.create", $performanceEvaluation) }}';
        }
    });
}
</script>
@stop
