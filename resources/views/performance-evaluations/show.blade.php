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
