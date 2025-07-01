@extends('adminlte::page')

@section('title', 'Evaluación del Supervisor')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Evaluación del Supervisor - {{ $performanceEvaluation->user->name }}</h1>
        <a href="{{ route('performance-evaluations.show', $performanceEvaluation) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('performance-evaluations.store-supervisor-evaluation', $performanceEvaluation) }}" method="POST" id="supervisorEvaluationForm">
        @csrf
        
        <!-- Información de la Evaluación -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Información de la Evaluación</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Empleado:</strong><br>
                        {{ $performanceEvaluation->user->name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Período Evaluado:</strong><br>
                        {{ $performanceEvaluation->evaluation_period_start->format('d/m/Y') }} - 
                        {{ $performanceEvaluation->evaluation_period_end->format('d/m/Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Tipo de Evaluación:</strong><br>
                        {{ $performanceEvaluation->evaluation_type === 'periodo_prueba' ? 'Período de Prueba' : 'Periódica' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Estado Autoevaluación:</strong><br>
                        @if($performanceEvaluation->final_self_score)
                            <span class="badge badge-success">Completada ({{ number_format($performanceEvaluation->final_self_score, 1) }})</span>
                        @else
                            <span class="badge badge-warning">Pendiente</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Leyenda del Sistema de Colores -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sistema de Colores</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="alert alert-success mb-2">
                            <strong>Verde:</strong> Campos completados por el evaluado
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info mb-2">
                            <strong>Azul:</strong> Campos para diligenciar por el supervisor
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-secondary mb-2">
                            <strong>Gris:</strong> Celdas programadas (no diligenciar)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Autoevaluación -->
        @if($performanceEvaluation->objectives_section)
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Resumen de Autoevaluación del Empleado</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Puntajes por Sección</h5>
                        <table class="table table-bordered table-sm">
                            <tr>
                                <td>Objetivos del Cargo</td>
                                <td class="text-center">{{ number_format($performanceEvaluation->objectives_self_score, 1) }}</td>
                            </tr>
                            <tr>
                                <td>Competencias Organizacionales</td>
                                <td class="text-center">{{ number_format($performanceEvaluation->competencies_self_score, 1) }}</td>
                            </tr>
                            <tr class="table-info">
                                <td><strong>Puntaje Total</strong></td>
                                <td class="text-center"><strong>{{ number_format($performanceEvaluation->final_self_score, 1) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if($performanceEvaluation->self_observations)
                        <h5>Observaciones del Empleado</h5>
                        <div class="alert alert-light">
                            {{ $performanceEvaluation->self_observations }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Sección I: Objetivos del Cargo (30%) - Evaluación del Supervisor -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Sección I: Objetivos del Cargo (30% del total) - Evaluación del Supervisor</h3>
            </div>
            <div class="card-body">
                @foreach($objectivesQuestions as $sectionKey => $section)
                <div class="mb-4">
                    <h5>{{ $section['title'] }} ({{ $section['weight'] * 100 }}% del peso de objetivos)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="40%">Criterio</th>
                                    <th width="20%" class="text-center">Autoevaluación</th>
                                    <th width="40%" class="text-center">Evaluación Supervisor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($section['questions'] as $questionKey => $question)
                                <tr>
                                    <td>{{ $question }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-success">
                                            {{ $performanceEvaluation->objectives_section[$sectionKey][$questionKey] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <select name="objectives_section_supervisor[{{ $sectionKey }}][{{ $questionKey }}]" 
                                                class="form-control form-control-sm objectives-score-supervisor" 
                                                style="background-color: #cce7ff;" required>
                                            <option value="">Seleccionar...</option>
                                            @for($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" 
                                                    {{ old("objectives_section_supervisor.{$sectionKey}.{$questionKey}") == $i ? 'selected' : '' }}>
                                                    {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach

                <div class="alert alert-info">
                    <strong>Escala de Puntuación:</strong><br>
                    <strong>1:</strong> No cumple - <strong>2:</strong> Aceptable - <strong>3:</strong> Cumple con lo establecido sin proactividad - <strong>4:</strong> Buen desempeño con caracteristicas proactivas - <strong>5:</strong> Supera las expectativas de desempeño
                </div>
            </div>
        </div>

        <!-- Sección II: Competencias Organizacionales (70%) - Evaluación del Supervisor -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Sección II: Competencias Organizacionales (70% del total) - Evaluación del Supervisor</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="40%">Competencia</th>
                                <th width="20%" class="text-center">Autoevaluación</th>
                                <th width="40%" class="text-center">Evaluación Supervisor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organizationalCompetencies as $competencyKey => $competency)
                            <tr>
                                <td>{{ $competency }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success">
                                        {{ $performanceEvaluation->organizational_competencies[$competencyKey] ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <select name="organizational_competencies_supervisor[{{ $competencyKey }}]" 
                                            class="form-control form-control-sm competencies-score-supervisor" 
                                            style="background-color: #cce7ff;" required>
                                        <option value="">Seleccionar...</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" 
                                                {{ old("organizational_competencies_supervisor.{$competencyKey}") == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sección III: Competencias Técnicas - Solo Lectura -->
        @if($performanceEvaluation->technical_competencies)
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">Sección III: Competencias Técnicas (Información del Empleado)</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="40%">Competencia</th>
                                <th width="60%">Respuesta del Empleado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($technicalCompetencies as $competencyKey => $competency)
                            <tr>
                                <td>{{ $competency['title'] }}</td>
                                <td>{{ $performanceEvaluation->technical_competencies[$competencyKey] ?? 'No especificado' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Sección IV: Seguridad y Salud en el Trabajo - Solo Lectura -->
        @if($performanceEvaluation->safety_health_section)
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">Sección IV: Seguridad y Salud en el Trabajo (Respuestas del Empleado)</h3>
            </div>
            <div class="card-body">
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
                            <tr>
                                <td>{{ $question }}</td>
                                <td class="text-center">
                                    @if(isset($performanceEvaluation->safety_health_section[$questionKey]))
                                        @if($performanceEvaluation->safety_health_section[$questionKey] === 'si')
                                            <span class="badge badge-success">Sí</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Sección V: Observaciones del Supervisor -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Sección V: Observaciones del Supervisor</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="supervisor_observations">
                        Comentarios del supervisor sobre el desempeño del empleado, fortalezas identificadas, áreas de mejora, recomendaciones:
                    </label>
                    <textarea name="supervisor_observations" id="supervisor_observations" 
                              class="form-control" rows="5" 
                              style="background-color: #cce7ff;"
                              placeholder="Escriba sus observaciones aquí..."
                              maxlength="2000">{{ old('supervisor_observations') }}</textarea>
                    <small class="form-text text-muted">Máximo 2000 caracteres</small>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" name="action" value="save_draft" class="btn btn-warning btn-lg">
                            <i class="fas fa-save"></i> Guardar como Borrador
                        </button>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" name="complete_evaluation" value="1" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Completar Evaluación
                        </button>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('performance-evaluations.show', $performanceEvaluation) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <strong>Importante:</strong> Una vez que complete la evaluación, se calculará el puntaje final promedio entre la autoevaluación y la evaluación del supervisor.
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Confirmación antes de completar la evaluación
    $('button[name="complete_evaluation"]').on('click', function(e) {
        if (!confirm('¿Está seguro de que desea completar la evaluación del supervisor? Una vez completada no podrá ser modificada.')) {
            e.preventDefault();
        }
    });

    // Validar que todos los campos requeridos estén completos antes de completar
    $('#supervisorEvaluationForm').on('submit', function(e) {
        if ($('button[name="complete_evaluation"]:focus').length > 0) {
            let incompleteFields = 0;
            
            // Verificar objetivos del supervisor
            $('.objectives-score-supervisor').each(function() {
                if ($(this).val() === '') {
                    incompleteFields++;
                }
            });
            
            // Verificar competencias del supervisor
            $('.competencies-score-supervisor').each(function() {
                if ($(this).val() === '') {
                    incompleteFields++;
                }
            });
            
            if (incompleteFields > 0) {
                e.preventDefault();
                alert('Por favor complete todos los campos requeridos antes de finalizar la evaluación. Faltan ' + incompleteFields + ' campos por completar.');
                return false;
            }
        }
    });
    
    // Contador de caracteres para observaciones
    $('#supervisor_observations').on('input', function() {
        let length = $(this).val().length;
        let max = 2000;
        let remaining = max - length;
        
        if (remaining < 0) {
            $(this).val($(this).val().substring(0, max));
            remaining = 0;
        }
        
        $(this).next().text('Caracteres restantes: ' + remaining);
    });
});
</script>
@stop

@section('css')
<style>
    .card-header.bg-info {
        border-color: #17a2b8;
    }
    
    .card-header.bg-primary {
        border-color: #007bff;
    }
    
    .card-header.bg-secondary {
        border-color: #6c757d;
    }
    
    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }
    
    .table th {
        border-top: 1px solid #dee2e6;
    }
    
    .alert {
        border-radius: 0.375rem;
    }
    
    .btn-lg {
        font-size: 1.1rem;
        padding: 0.75rem 1.5rem;
    }
    
    .badge {
        font-size: 0.9em;
    }
</style>
@stop
