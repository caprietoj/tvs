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
</style>
@stop
