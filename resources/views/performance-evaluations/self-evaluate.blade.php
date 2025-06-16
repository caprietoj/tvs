@extends('adminlte::page')

@section('title', 'Autoevaluación de Desempeño')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Autoevaluación de Desempeño</h1>
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

    <form action="{{ route('performance-evaluations.store-self-evaluation', $performanceEvaluation) }}" method="POST" id="selfEvaluationForm">
        @csrf
        
        <!-- Información de la Evaluación -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">Información de la Evaluación</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Período Evaluado:</strong><br>
                        {{ $performanceEvaluation->evaluation_period_start->format('d/m/Y') }} - 
                        {{ $performanceEvaluation->evaluation_period_end->format('d/m/Y') }}
                    </div>
                    <div class="col-md-4">
                        <strong>Tipo de Evaluación:</strong><br>
                        {{ $performanceEvaluation->evaluation_type === 'periodo_prueba' ? 'Período de Prueba' : 'Periódica' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Evaluador:</strong><br>
                        {{ $performanceEvaluation->evaluator->name ?? 'No asignado' }}
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
                            <strong>Verde:</strong> Campos para diligenciar por el evaluado (autoevaluación)
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info mb-2">
                            <strong>Azul:</strong> Campos para diligenciar por el jefe inmediato
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

        <!-- Sección I: Objetivos del Cargo (30%) -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Sección I: Objetivos del Cargo (30% del total)</h3>
            </div>
            <div class="card-body">
                @foreach($objectivesQuestions as $sectionKey => $section)
                <div class="mb-4">
                    <h5>{{ $section['title'] }} ({{ $section['weight'] * 100 }}% del peso de objetivos)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="60%">Criterio</th>
                                    <th width="40%" class="text-center">Puntuación (1-5)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($section['questions'] as $questionKey => $question)
                                <tr>
                                    <td>{{ $question }}</td>
                                    <td class="text-center">
                                        <select name="objectives_section[{{ $sectionKey }}][{{ $questionKey }}]" 
                                                class="form-control form-control-sm objectives-score" 
                                                style="background-color: #d4edda;" required>
                                            <option value="">Seleccionar...</option>
                                            @for($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" 
                                                    {{ (old("objectives_section.{$sectionKey}.{$questionKey}") ?? ($performanceEvaluation->objectives_section[$sectionKey][$questionKey] ?? '')) == $i ? 'selected' : '' }}>
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
                    <strong>1:</strong> No cumple - <strong>2:</strong> Cumple parcialmente - <strong>3:</strong> Cumple - <strong>4:</strong> Supera - <strong>5:</strong> Supera ampliamente
                </div>
            </div>
        </div>

        <!-- Sección II: Competencias Organizacionales (70%) -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Sección II: Competencias Organizacionales (70% del total)</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="60%">Competencia</th>
                                <th width="40%" class="text-center">Puntuación (1-5)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organizationalCompetencies as $competencyKey => $competency)
                            <tr>
                                <td>{{ $competency }}</td>
                                <td class="text-center">
                                    <select name="organizational_competencies[{{ $competencyKey }}]" 
                                            class="form-control form-control-sm competencies-score" 
                                            style="background-color: #d4edda;" required>
                                        <option value="">Seleccionar...</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" 
                                                {{ (old("organizational_competencies.{$competencyKey}") ?? ($performanceEvaluation->organizational_competencies[$competencyKey] ?? '')) == $i ? 'selected' : '' }}>
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

        <!-- Sección III: Competencias Técnicas -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Sección III: Competencias Técnicas</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="40%">Competencia</th>
                                <th width="60%">Nivel/Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($technicalCompetencies as $competencyKey => $competency)
                            <tr>
                                <td>{{ $competency['title'] }}</td>
                                <td>
                                    @if(isset($competency['options']))
                                        <select name="technical_competencies[{{ $competencyKey }}]" 
                                                class="form-control" style="background-color: #d4edda;">
                                            @foreach($competency['options'] as $option)
                                                <option value="{{ $option }}" 
                                                    {{ (old("technical_competencies.{$competencyKey}") ?? ($performanceEvaluation->technical_competencies[$competencyKey] ?? '')) === $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <textarea name="technical_competencies[{{ $competencyKey }}]" 
                                                  class="form-control" rows="2" 
                                                  style="background-color: #d4edda;"
                                                  placeholder="Describir formación adicional...">{{ old("technical_competencies.{$competencyKey}") ?? ($performanceEvaluation->technical_competencies[$competencyKey] ?? '') }}</textarea>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sección IV: Seguridad y Salud en el Trabajo -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Sección IV: Seguridad y Salud en el Trabajo</h3>
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
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" 
                                               name="safety_health_section[{{ $questionKey }}]" 
                                               id="{{ $questionKey }}_si" value="si"
                                               style="background-color: #d4edda;"
                                               {{ (old("safety_health_section.{$questionKey}") ?? ($performanceEvaluation->safety_health_section[$questionKey] ?? '')) === 'si' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="{{ $questionKey }}_si">Sí</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" 
                                               name="safety_health_section[{{ $questionKey }}]" 
                                               id="{{ $questionKey }}_no" value="no"
                                               style="background-color: #d4edda;"
                                               {{ (old("safety_health_section.{$questionKey}") ?? ($performanceEvaluation->safety_health_section[$questionKey] ?? '')) === 'no' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="{{ $questionKey }}_no">No</label>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sección V: Observaciones -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Sección V: Observaciones del Evaluado</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="self_observations">
                        Comentarios adicionales, logros destacados, desafíos enfrentados, sugerencias de mejora:
                    </label>
                    <textarea name="self_observations" id="self_observations" 
                              class="form-control" rows="5" 
                              style="background-color: #d4edda;"
                              placeholder="Escriba sus observaciones aquí..."
                              maxlength="2000">{{ old('self_observations') ?? $performanceEvaluation->self_observations }}</textarea>
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
                            <i class="fas fa-check"></i> Completar Autoevaluación
                        </button>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('performance-evaluations.show', $performanceEvaluation) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <strong>Importante:</strong> Una vez que complete la autoevaluación, será enviada al supervisor para su revisión y no podrá ser modificada.
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
        if (!confirm('¿Está seguro de que desea completar la autoevaluación? Una vez completada no podrá ser modificada.')) {
            e.preventDefault();
        }
    });

    // Validar que todos los campos requeridos estén completos antes de completar
    $('#selfEvaluationForm').on('submit', function(e) {
        if ($('button[name="complete_evaluation"]:focus').length > 0) {
            let incompleteFields = 0;
            
            // Verificar objetivos
            $('.objectives-score').each(function() {
                if ($(this).val() === '') {
                    incompleteFields++;
                }
            });
            
            // Verificar competencias
            $('.competencies-score').each(function() {
                if ($(this).val() === '') {
                    incompleteFields++;
                }
            });
            
            // Verificar SST
            let sstQuestions = $('input[type="radio"][name^="safety_health_section"]').length / 2;
            let sstAnswered = $('input[type="radio"][name^="safety_health_section"]:checked').length;
            
            if (sstAnswered < sstQuestions) {
                incompleteFields += (sstQuestions - sstAnswered);
            }
            
            if (incompleteFields > 0) {
                e.preventDefault();
                alert('Por favor complete todos los campos requeridos antes de finalizar la autoevaluación. Faltan ' + incompleteFields + ' campos por completar.');
                return false;
            }
        }
    });
    
    // Contador de caracteres para observaciones
    $('#self_observations').on('input', function() {
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
    .card-header.bg-success {
        border-color: #28a745;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
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
</style>
@stop
