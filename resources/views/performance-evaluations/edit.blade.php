@extends('adminlte::page')

@section('title', 'Editar Evaluación de Desempeño')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-edit text-warning"></i> Editar Evaluación de Desempeño</h1>
            <small class="text-muted">{{ $performanceEvaluation->user->name ?? 'Empleado' }}</small>
        </div>
        <a href="{{ route('performance-evaluations.show', $performanceEvaluation) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Detalle
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <form action="{{ route('performance-evaluations.update', $performanceEvaluation) }}" method="POST" id="evaluation-edit-form">
        @csrf
        @method('PUT')

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle text-warning"></i> Información General
                </h3>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h5><i class="icon fas fa-ban"></i> ¡Hay errores en el formulario!</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id" class="font-weight-bold">
                                <i class="fas fa-user text-primary"></i> Empleado Evaluado
                                <span class="text-danger">*</span>
                            </label>
                            <select name="user_id" id="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                <option value="">Seleccionar empleado...</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ (string) old('user_id', $performanceEvaluation->user_id) === (string) $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}{{ $employee->department ? ' (' . $employee->department . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="evaluator_id" class="font-weight-bold">
                                <i class="fas fa-user-tie text-info"></i> Supervisor/Evaluador
                            </label>
                            <select name="evaluator_id" id="evaluator_id" class="form-control select2 @error('evaluator_id') is-invalid @enderror">
                                <option value="">Sin supervisor asignado</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}"
                                        {{ (string) old('evaluator_id', $performanceEvaluation->evaluator_id) === (string) $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('evaluator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="evaluation_type" class="font-weight-bold">
                                <i class="fas fa-clipboard-list text-success"></i> Tipo de Evaluación
                                <span class="text-danger">*</span>
                            </label>
                            <select name="evaluation_type" id="evaluation_type" class="form-control @error('evaluation_type') is-invalid @enderror" required>
                                <option value="periodo_prueba" {{ old('evaluation_type', $performanceEvaluation->evaluation_type) === 'periodo_prueba' ? 'selected' : '' }}>
                                    Período de Prueba
                                </option>
                                <option value="periodica" {{ old('evaluation_type', $performanceEvaluation->evaluation_type) === 'periodica' ? 'selected' : '' }}>
                                    Periódica
                                </option>
                            </select>
                            @error('evaluation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="evaluation_year" class="font-weight-bold">
                                <i class="fas fa-calendar text-success"></i> Año de Elaboración
                                <span class="text-danger">*</span>
                            </label>
                            <select name="evaluation_year" id="evaluation_year" class="form-control @error('evaluation_year') is-invalid @enderror" required>
                                @foreach($availableYears as $yearOption)
                                    <option value="{{ $yearOption }}"
                                        {{ (string) old('evaluation_year', $performanceEvaluation->evaluation_year) === (string) $yearOption ? 'selected' : '' }}>
                                        {{ $yearOption }}
                                    </option>
                                @endforeach
                            </select>
                            @error('evaluation_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="evaluation_period_start" class="font-weight-bold">
                                <i class="fas fa-calendar-alt text-warning"></i> Fecha Inicio Período
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="evaluation_period_start" id="evaluation_period_start"
                                   class="form-control @error('evaluation_period_start') is-invalid @enderror"
                                   value="{{ old('evaluation_period_start', $performanceEvaluation->evaluation_period_start->format('Y-m-d')) }}" required>
                            @error('evaluation_period_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="evaluation_period_end" class="font-weight-bold">
                                <i class="fas fa-calendar-check text-danger"></i> Fecha Fin Período
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="evaluation_period_end" id="evaluation_period_end"
                                   class="form-control @error('evaluation_period_end') is-invalid @enderror"
                                   value="{{ old('evaluation_period_end', $performanceEvaluation->evaluation_period_end->format('Y-m-d')) }}" required>
                            @error('evaluation_period_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status" class="font-weight-bold">
                                <i class="fas fa-tasks text-info"></i> Estado
                                <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $performanceEvaluation->status) === 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="self_completed" {{ old('status', $performanceEvaluation->status) === 'self_completed' ? 'selected' : '' }}>Autoevaluación Completada</option>
                                <option value="supervisor_review" {{ old('status', $performanceEvaluation->status) === 'supervisor_review' ? 'selected' : '' }}>En Revisión Supervisor</option>
                                <option value="completed" {{ old('status', $performanceEvaluation->status) === 'completed' ? 'selected' : '' }}>Completada</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-info mt-md-4 mb-0">
                            <i class="fas fa-info-circle"></i>
                            Al cambiar el estado se ajustan automáticamente las fechas de finalización y los puntajes finales cuando hay información suficiente.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-comment-dots text-info"></i> Observaciones
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="self_observations" class="font-weight-bold">Observaciones del Empleado</label>
                            <textarea name="self_observations" id="self_observations" rows="5"
                                      class="form-control @error('self_observations') is-invalid @enderror"
                                      maxlength="2000">{{ old('self_observations', $performanceEvaluation->self_observations) }}</textarea>
                            @error('self_observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supervisor_observations" class="font-weight-bold">Observaciones del Supervisor</label>
                            <textarea name="supervisor_observations" id="supervisor_observations" rows="5"
                                      class="form-control @error('supervisor_observations') is-invalid @enderror"
                                      maxlength="2000">{{ old('supervisor_observations', $performanceEvaluation->supervisor_observations) }}</textarea>
                            @error('supervisor_observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('performance-evaluations.show', $performanceEvaluation) }}" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Los campos marcados con (*) son obligatorios
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
<style>
    .select2-container--bootstrap .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
    }

    .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        padding-right: 20px;
        height: auto;
        margin-top: -2px;
    }

    .form-group label.font-weight-bold {
        color: #495057;
        margin-bottom: 8px;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'Buscar y seleccionar...',
        allowClear: true
    });
});
</script>
@stop
