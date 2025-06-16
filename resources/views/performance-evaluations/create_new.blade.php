@extends('adminlte::page')

@section('title', 'Nueva Evaluación de Desempeño')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-user-check text-primary"></i> Nueva Evaluación de Desempeño</h1>
            <small class="text-muted">Crear una nueva evaluación para un empleado</small>
        </div>
        <a href="{{ route('performance-evaluations.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Wizard Steps Indicator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-body p-2">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="step-item active">
                                <div class="step-number">1</div>
                                <div class="step-title">Información General</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-title">Autoevaluación</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-title">Evaluación Supervisor</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('performance-evaluations.store') }}" method="POST" id="evaluation-form">
        @csrf
        
        <!-- Información del Empleado -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user text-primary"></i> Información del Empleado
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
                                <i class="fas fa-user-tag text-primary"></i> Empleado a Evaluar 
                                <span class="text-danger">*</span>
                            </label>
                            <select name="user_id" id="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                <option value="">🔍 Buscar y seleccionar empleado...</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" 
                                            {{ old('user_id') == $employee->id ? 'selected' : '' }}
                                            data-email="{{ $employee->email }}">
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Seleccione el empleado que será evaluado
                            </small>
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
                                <option value="">🔍 Buscar y seleccionar supervisor...</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" 
                                            {{ old('evaluator_id') == $supervisor->id ? 'selected' : '' }}
                                            data-email="{{ $supervisor->email }}">
                                        {{ $supervisor->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Opcional: Asigne un supervisor para la evaluación
                            </small>
                            @error('evaluator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Employee and Evaluator Info Cards -->
                <div class="row mt-3" id="selected-info" style="display: none;">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Empleado Seleccionado</span>
                                <span class="info-box-number" id="selected-employee">-</span>
                                <small id="selected-employee-email" class="text-muted"></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info"><i class="fas fa-user-tie"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Supervisor Asignado</span>
                                <span class="info-box-number" id="selected-evaluator">No asignado</span>
                                <small id="selected-evaluator-email" class="text-muted"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración de la Evaluación -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs text-success"></i> Configuración de la Evaluación
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="evaluation_type" class="font-weight-bold">
                                <i class="fas fa-clipboard-list text-success"></i> Tipo de Evaluación 
                                <span class="text-danger">*</span>
                            </label>
                            <select name="evaluation_type" id="evaluation_type" class="form-control @error('evaluation_type') is-invalid @enderror" required>
                                <option value="">📋 Seleccionar tipo de evaluación...</option>
                                <option value="periodo_prueba" {{ old('evaluation_type') === 'periodo_prueba' ? 'selected' : '' }}>
                                    🔸 Evaluación de Período de Prueba
                                </option>
                                <option value="periodica" {{ old('evaluation_type') === 'periodica' ? 'selected' : '' }}>
                                    🔹 Evaluación Periódica
                                </option>
                            </select>
                            @error('evaluation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="evaluation_period_start" class="font-weight-bold">
                                <i class="fas fa-calendar-alt text-warning"></i> Fecha Inicio Período 
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="date" name="evaluation_period_start" id="evaluation_period_start" 
                                       class="form-control @error('evaluation_period_start') is-invalid @enderror" 
                                       value="{{ old('evaluation_period_start') }}" required>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Inicio del período a evaluar
                            </small>
                            @error('evaluation_period_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="evaluation_period_end" class="font-weight-bold">
                                <i class="fas fa-calendar-check text-danger"></i> Fecha Fin Período 
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="date" name="evaluation_period_end" id="evaluation_period_end" 
                                       class="form-control @error('evaluation_period_end') is-invalid @enderror" 
                                       value="{{ old('evaluation_period_end') }}" required>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Fin del período a evaluar
                            </small>
                            @error('evaluation_period_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Evaluation Type Info -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div id="evaluation-type-info" class="alert alert-info" style="display: none;">
                            <h5><i class="icon fas fa-info-circle"></i> <span id="info-title">Información del Tipo de Evaluación</span></h5>
                            <div id="info-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Proceso -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-route text-warning"></i> Flujo del Proceso de Evaluación
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-primary">Proceso de Evaluación</span>
                    </div>
                    
                    <div>
                        <i class="fas fa-plus bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> Paso 1</span>
                            <h3 class="timeline-header">Creación de la Evaluación</h3>
                            <div class="timeline-body">
                                Se crea la evaluación y se notifica al empleado para que inicie su autoevaluación.
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <i class="fas fa-user bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> Paso 2</span>
                            <h3 class="timeline-header">Autoevaluación del Empleado</h3>
                            <div class="timeline-body">
                                El empleado completa su autoevaluación en las diferentes secciones.
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <i class="fas fa-user-tie bg-yellow"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> Paso 3</span>
                            <h3 class="timeline-header">Evaluación del Supervisor</h3>
                            <div class="timeline-body">
                                El supervisor asignado completa la evaluación final.
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <i class="fas fa-check bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> Final</span>
                            <h3 class="timeline-header">Evaluación Completada</h3>
                            <div class="timeline-body">
                                La evaluación se marca como completada y se generan los resultados finales.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de Acción -->
        <div class="card">
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Crear Evaluación
                        </button>
                        <a href="{{ route('performance-evaluations.index') }}" class="btn btn-secondary btn-lg ml-2">
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
    .step-item {
        position: relative;
        padding: 10px;
    }
    .step-item.active .step-number {
        background: #007bff;
        color: white;
    }
    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .step-title {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
    }
    .step-item.active .step-title {
        color: #007bff;
    }
    
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
    
    .info-box {
        transition: all 0.3s ease;
    }
    
    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
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
        background: #dee2e6;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }
    
    .timeline > div {
        margin-bottom: 15px;
        position: relative;
    }
    
    .timeline > div > .timeline-item {
        margin-left: 60px;
        margin-right: 15px;
        background: #fff;
        border-radius: 5px;
        padding: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .timeline > div > .fas {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        color: white;
        position: absolute;
        left: 18px;
        top: 0;
    }
    
    .time-label > span {
        font-weight: 600;
        color: #fff;
        border-radius: 4px;
        padding: 5px 10px;
    }
    
    .timeline-header {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 5px 0;
        color: #495057;
    }
    
    .timeline-body {
        font-size: 14px;
        color: #6c757d;
        line-height: 1.4;
    }
    
    .time {
        color: #999;
        font-size: 11px;
        font-weight: 600;
    }
    
    .card-outline.card-primary {
        border-top: 3px solid #007bff;
    }
    
    .card-outline.card-success {
        border-top: 3px solid #28a745;
    }
    
    .card-outline.card-warning {
        border-top: 3px solid #ffc107;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1.125rem;
        line-height: 1.5;
        border-radius: 0.3rem;
    }
    
    .input-group-text {
        background-color: #e9ecef;
        border-color: #ced4da;
    }
    
    .alert {
        border-radius: 0.375rem;
    }
    
    .form-text {
        margin-top: 0.25rem;
        font-size: 0.875em;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 with enhanced options
    $('.select2').select2({
        theme: 'bootstrap',
        placeholder: function() {
            return $(this).find('option:first').text();
        },
        allowClear: true,
        width: '100%',
        templateResult: function(option) {
            if (!option.id) {
                return option.text;
            }
            var email = $(option.element).data('email');
            if (email) {
                return $('<div><div>' + option.text + '</div><small class="text-muted">' + email + '</small></div>');
            }
            return option.text;
        }
    });
    
    // Show selected employee and evaluator info
    function updateSelectedInfo() {
        var employeeSelect = $('#user_id');
        var evaluatorSelect = $('#evaluator_id');
        var selectedInfo = $('#selected-info');
        
        var employeeSelected = employeeSelect.val();
        var evaluatorSelected = evaluatorSelect.val();
        
        if (employeeSelected || evaluatorSelected) {
            selectedInfo.show();
            
            if (employeeSelected) {
                var employeeOption = employeeSelect.find('option:selected');
                var employeeName = employeeOption.text();
                var employeeEmail = employeeOption.data('email');
                
                $('#selected-employee').text(employeeName);
                $('#selected-employee-email').text(employeeEmail || '');
            } else {
                $('#selected-employee').text('-');
                $('#selected-employee-email').text('');
            }
            
            if (evaluatorSelected) {
                var evaluatorOption = evaluatorSelect.find('option:selected');
                var evaluatorName = evaluatorOption.text();
                var evaluatorEmail = evaluatorOption.data('email');
                
                $('#selected-evaluator').text(evaluatorName);
                $('#selected-evaluator-email').text(evaluatorEmail || '');
            } else {
                $('#selected-evaluator').text('No asignado');
                $('#selected-evaluator-email').text('');
            }
        } else {
            selectedInfo.hide();
        }
    }
    
    $('#user_id, #evaluator_id').on('change', updateSelectedInfo);
    
    // Show evaluation type information
    $('#evaluation_type').on('change', function() {
        var selectedType = $(this).val();
        var infoDiv = $('#evaluation-type-info');
        var infoTitle = $('#info-title');
        var infoContent = $('#info-content');
        
        if (selectedType) {
            infoDiv.show();
            
            if (selectedType === 'periodo_prueba') {
                infoTitle.text('Evaluación de Período de Prueba');
                infoContent.html(`
                    <ul class="mb-0">
                        <li><strong>Propósito:</strong> Evaluar el desempeño durante el período inicial de prueba del empleado</li>
                        <li><strong>Duración típica:</strong> Entre 1 y 6 meses</li>
                        <li><strong>Enfoque:</strong> Adaptación al puesto, cumplimiento de objetivos iniciales</li>
                        <li><strong>Resultado:</strong> Determina la continuidad del empleado en la organización</li>
                    </ul>
                `);
                infoDiv.removeClass('alert-info alert-warning').addClass('alert-info');
            } else if (selectedType === 'periodica') {
                infoTitle.text('Evaluación Periódica');
                infoContent.html(`
                    <ul class="mb-0">
                        <li><strong>Propósito:</strong> Evaluación regular del desempeño del empleado</li>
                        <li><strong>Frecuencia:</strong> Anual, semestral o según política de la empresa</li>
                        <li><strong>Enfoque:</strong> Desarrollo profesional, metas cumplidas, áreas de mejora</li>
                        <li><strong>Resultado:</strong> Plan de desarrollo y reconocimiento del desempeño</li>
                    </ul>
                `);
                infoDiv.removeClass('alert-info alert-warning').addClass('alert-warning');
            }
        } else {
            infoDiv.hide();
        }
    });
    
    // Enhanced date validation
    function validateDates() {
        var startDate = $('#evaluation_period_start').val();
        var endDate = $('#evaluation_period_end').val();
        var startInput = $('#evaluation_period_start');
        var endInput = $('#evaluation_period_end');
        
        // Remove any existing validation classes
        startInput.removeClass('is-invalid is-valid');
        endInput.removeClass('is-invalid is-valid');
        
        if (startDate && endDate) {
            var start = new Date(startDate);
            var end = new Date(endDate);
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (end <= start) {
                endInput.addClass('is-invalid');
                showToast('error', 'La fecha de fin debe ser posterior a la fecha de inicio');
                endInput.val('');
                return false;
            }
            
            var diffTime = Math.abs(end - start);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 365) {
                endInput.addClass('is-invalid');
                showToast('warning', 'El período de evaluación es muy largo (más de un año)');
                return false;
            }
            
            startInput.addClass('is-valid');
            endInput.addClass('is-valid');
            return true;
        }
        
        return true;
    }
    
    $('#evaluation_period_start, #evaluation_period_end').on('change', validateDates);
    
    // Form validation before submit
    $('#evaluation-form').on('submit', function(e) {
        var isValid = true;
        
        // Check required fields
        var requiredFields = ['user_id', 'evaluation_type', 'evaluation_period_start', 'evaluation_period_end'];
        
        requiredFields.forEach(function(fieldId) {
            var field = $('#' + fieldId);
            if (!field.val()) {
                field.addClass('is-invalid');
                isValid = false;
            } else {
                field.removeClass('is-invalid').addClass('is-valid');
            }
        });
        
        // Validate dates
        if (!validateDates()) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            showToast('error', 'Por favor, complete todos los campos obligatorios correctamente');
            
            // Scroll to first error
            var firstError = $('.is-invalid:first');
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
        }
    });
    
    // Toast notification function
    function showToast(type, message) {
        var alertClass = 'alert-' + (type === 'error' ? 'danger' : type);
        var icon = type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
        
        var toast = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <i class="${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('body').append(toast);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $('.alert.position-fixed').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Initialize on page load
    updateSelectedInfo();
});
</script>
@stop
