@extends('adminlte::page')

@section('title', 'KPI - Enfermería')

@section('content_header')
    <!-- <h1 class="text-primary">KPI - Enfermería</h1> -->
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Formulario de Registro de KPI</h3>
            <button type="button" class="btn btn-light" id="calculatorBtn">
                <i class="fas fa-calculator"></i> Calculadora
            </button>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('kpis.enfermeria.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type" class="form-label">
                            Tipo de KPI <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="type" class="form-control select2bs4 @error('type') is-invalid @enderror" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="measurement">Medición</option>
                            <option value="informative">Informativo</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="threshold_id" class="form-label">
                            Nombre del Indicador <span class="text-danger">*</span>
                        </label>
                        <select name="threshold_id" id="threshold_id" class="form-control select2bs4 @error('threshold_id') is-invalid @enderror" required>
                            <option value="">Seleccione un Indicador</option>
                            @foreach($thresholds as $threshold)
                                <option value="{{ $threshold->id }}">
                                    {{ $threshold->kpi_name }} (Umbral: {{ $threshold->value }}%)
                                </option>
                            @endforeach
                        </select>
                        @error('threshold_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="methodology" class="form-label">
                            Metodología de Medición <span class="text-danger">*</span>
                        </label>
                        <select name="methodology" id="methodology" class="form-control select2bs4 @error('methodology') is-invalid @enderror" required>
                            <option value="">Seleccione una metodología</option>
                            <option value="formulario">Formulario</option>
                            <option value="encuesta">Encuesta</option>
                            <option value="archivo">Archivo</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('methodology')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="analysis" class="form-label">
                            Análisis <span class="text-danger">*</span>
                        </label>
                        <textarea name="analysis" id="analysis" class="form-control @error('analysis') is-invalid @enderror" 
                                  rows="3" required readonly>{{ old('analysis') }}</textarea>
                        @error('analysis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="frequency" class="form-label">
                            Frecuencia de Medición <span class="text-danger">*</span>
                        </label>
                        <select name="frequency" id="frequency" class="form-control select2bs4 @error('frequency') is-invalid @enderror" required>
                            <option value="">Seleccione una frecuencia</option>
                            <option value="Diario">Diario</option>
                            <option value="Quincenal">Quincenal</option>
                            <option value="Mensual">Mensual</option>
                            <option value="Semestral">Semestral</option>
                        </select>
                        @error('frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="measurement_date" class="form-label">
                            Fecha de Medición <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="measurement_date" id="measurement_date" 
                               class="form-control @error('measurement_date') is-invalid @enderror" 
                               value="{{ old('measurement_date') }}" required>
                        @error('measurement_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="percentage" class="form-label">
                            Porcentaje Alcanzado (%) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" max="100" name="percentage" 
                               id="percentage" class="form-control @error('percentage') is-invalid @enderror" 
                               value="{{ old('percentage') }}" required>
                        @error('percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="url" class="form-label">
                    URL de Referencia
                </label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                    </div>
                    <input type="url" name="url" id="url" 
                           class="form-control @error('url') is-invalid @enderror" 
                           value="{{ old('url') }}" placeholder="https://ejemplo.com">
                </div>
                <small class="form-text text-muted">Ingrese una URL relacionada con este KPI (opcional)</small>
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('kpis.enfermeria.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Registrar KPI
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Update the modal structure -->
<div class="modal" id="surveyDataModal" tabindex="-1" role="dialog" aria-labelledby="surveyDataModalTitle">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #364E76; color: white;">
                <h5 class="modal-title" id="surveyDataModalTitle">Calculadora de KPI</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Update tab navigation for accessibility -->
                <ul class="nav nav-tabs" id="calculatorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="survey-tab" data-toggle="tab" data-target="#survey" 
                                type="button" role="tab" aria-controls="survey" aria-selected="true">
                            <i class="fas fa-poll"></i> Encuestas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="patient-care-tab" data-toggle="tab" data-target="#patient-care" 
                                type="button" role="tab" aria-controls="patient-care" aria-selected="false">
                            <i class="fas fa-user-nurse"></i> Atención al Paciente
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="welfare-tab" data-toggle="tab" data-target="#welfare" 
                                type="button" role="tab" aria-controls="welfare" aria-selected="false">
                            <i class="fas fa-heart"></i> Actividades de Bienestar
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="medical-control-tab" data-toggle="tab" data-target="#medical-control" 
                                type="button" role="tab" aria-controls="medical-control" aria-selected="false">
                            <i class="fas fa-heartbeat"></i> Control Médico
                        </button>
                    </li>
                </ul>

                <!-- Contenido de las pestañas -->
                <div class="tab-content mt-3" id="calculatorTabContent">
                    <!-- Pestaña de Encuestas -->
                    <div class="tab-pane fade show active" id="survey" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Cálculo de Satisfacción</h6>
                            <p class="mb-1">El sistema procesa:</p>
                            <ol class="pl-3 mb-0">
                                <li>Valores de "Nivel de satisfacción general" (columna 11)</li>
                                <li>Suma de valores</li>
                                <li>Promedio sobre escala de 5 puntos</li>
                                <li>Conversión a porcentaje (×20)</li>
                            </ol>
                        </div>
                        <div class="form-group">
                            <label>Datos de la encuesta</label>
                            <textarea class="form-control" id="surveyData" rows="10" 
                                placeholder="Pegue aquí los datos de la encuesta..."></textarea>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculateSurveyBtn">
                            Calcular Satisfacción
                        </button>
                    </div>

                    <!-- Pestaña de Atención al Paciente -->
                    <div class="tab-pane fade" id="patient-care" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Cálculo de Atención</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Datos a procesar:</strong></p>
                                    <ul class="pl-3 mb-0">
                                        <li>Estudiantes: Primer input</li>
                                        <li>Trabajadores: Segundo input</li>
                                        <li>Cálculo combinado automático</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Criterios de Calidad:</strong></p>
                                    <ul class="pl-3 mb-0">
                                        <li>Seguimiento completo</li>
                                        <li>Nota escrita</li>
                                        <li>Llamada de control</li>
                                        <li>Retorno al salón</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Datos de atención - Estudiantes</label>
                                    <textarea class="form-control" id="studentCareData" rows="10" 
                                        placeholder="Pegue aquí los datos de atención a estudiantes..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Datos de atención - Trabajadores</label>
                                    <textarea class="form-control" id="staffCareData" rows="10" 
                                        placeholder="Pegue aquí los datos de atención a trabajadores..."></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculatePatientCareBtn">
                            Calcular Atención
                        </button>
                    </div>

                    <!-- Pestaña de Actividades de Bienestar -->
                    <div class="tab-pane fade" id="welfare" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Cálculo de Participación</h6>
                            <p class="mb-1">El sistema calculará:</p>
                            <ul class="pl-3 mb-0">
                                <li>Porcentaje de participación = (Participantes / Invitados) × 100</li>
                                <li>Análisis automático de la participación</li>
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Número de Participantes</label>
                                    <input type="number" class="form-control" id="participantsCount" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Número de Invitados</label>
                                    <input type="number" class="form-control" id="invitedCount" min="1">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculateWelfareBtn">
                            Calcular Participación
                        </button>
                    </div>

                    <!-- Pestaña de Control Médico -->
                    <div class="tab-pane fade" id="medical-control" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Análisis de Control Médico</h6>
                            <p class="mb-1">El sistema calculará:</p>
                            <ul class="pl-3 mb-0">
                                <li>% Medicación = (Estudiantes medicados / Total que requieren) × 100</li>
                                <li>% Actualización = (Fichas actualizadas / Total estudiantes) × 100</li>
                            </ul>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Suministro de Medicamentos</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Estudiantes que recibieron medicación</label>
                                            <input type="number" class="form-control" id="medicatedStudents" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Total estudiantes que requieren medicación</label>
                                            <input type="number" class="form-control" id="totalNeedingMedication" min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Actualización de Fichas Médicas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fichas médicas actualizadas</label>
                                            <input type="number" class="form-control" id="updatedRecords" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Total de estudiantes</label>
                                            <input type="number" class="form-control" id="totalStudents" min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary" id="calculateMedicalBtn">
                            Calcular Control Médico
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    :root {
        --primary: #364E76;
    }

    /* Aseguramos que el card-header use el color corporativo */
    .card-header {
        background-color: #364E76 !important; /* Forzamos el color con !important */
    }

    .card-header .card-title {
        color: white;
        margin-bottom: 0;
    }

    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --input-height: 50px;
        --border-radius: 8px;
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .card-header {
        background-color: var(--primary);
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .card-header .card-title {
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
    }

    /* Mejoras en los campos del formulario */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label, label {
        color: #2d3748;
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
        display: block;
    }

    /* Estilizado base para todos los inputs */
    .form-control {
        height: var(--input-height) !important;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        line-height: 1.5;
        border: 2px solid #e2e8f0;
        border-radius: var(--border-radius);
        transition: all 0.3s ease;
    }

    /* Mejoras específicas para Select2 */
    .select2-container--bootstrap4 .select2-selection {
        height: var(--input-height) !important;
        border: 2px solid #e2e8f0;
        border-radius: var(--border-radius);
        padding: 0.75rem 1rem;
        font-size: 1rem;
        line-height: 1.5;
    }

    .select2-container--bootstrap4 .select2-selection--single {
        padding-right: 2.5rem;
        background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 1rem center/16px 12px;
    }

    .select2-container--bootstrap4 .select2-selection__rendered {
        padding: 0 !important;
        line-height: 1.5 !important;
        color: #495057;
    }

    .select2-container--bootstrap4 .select2-selection__arrow {
        display: none;
    }

    .select2-container--bootstrap4 .select2-dropdown {
        border-color: var(--primary);
        border-radius: var(--border-radius);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .select2-container--bootstrap4 .select2-results__option {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary);
    }

    /* Estados de focus para inputs y Select2 */
    .form-control:focus,
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        outline: none;
    }

    /* Estilos específicos para textarea */
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    /* Estilos para inputs específicos */
    input[type="date"].form-control {
        padding: 0.6rem 1rem;
    }

    input[type="number"].form-control {
        padding-right: 1rem;
    }

    /* Estilos para mensajes de error */
    .invalid-feedback {
        color: var(--accent);
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    /* Botones mejorados */
    .btn {
        height: var(--input-height);
        padding: 0 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(54, 78, 118, 0.2);
    }

    /* Ajustes responsivos */
    @media (max-width: 768px) {
        .form-control,
        .select2-container--bootstrap4 .select2-selection {
            font-size: 16px;
        }

        .btn {
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
        }
    }

    /* Estilos mejorados para el título y encabezado */
    .text-primary {
        color: var(--primary) !important;
        font-weight: 600;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    /* Estilos para el input group */
    .input-group-text {
        background-color: #f8f9fa;
        border: 2px solid #e2e8f0;
        border-right: none;
        border-radius: var(--border-radius) 0 0 var(--border-radius);
        height: var(--input-height);
        padding: 0 1rem;
    }
    
    .input-group .form-control {
        border-left: none;
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
    }

    textarea[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    /* Add or update modal styles */
    .modal .modal-header {
        background-color: #364E76;
        color: white !important;
    }

    .modal .modal-header h5.modal-title {
        color: white;
        font-weight: 600;
    }

    .modal .modal-header .close {
        color: white;
        text-shadow: none;
        opacity: 0.8;
    }

    .modal .modal-header .close:hover {
        opacity: 1;
    }

    .nav-tabs .nav-link {
        color: #364E76;
    }

    .nav-tabs .nav-link.active {
        color: #364E76;
        font-weight: 600;
    }

    /* Add or update card styles */
    .modal .card-header.bg-light {
        background-color: #364E76 !important;
        color: white !important;
    }

    .modal .card-header.bg-light h6 {
        color: white !important;
        font-weight: 600;
        margin: 0;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Asegurar que los contenedores Select2 tengan el ancho correcto
    $('.select2-container').css('width', '100%');
    
    // Inicialización mejorada de Select2
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Seleccione una opción',
        allowClear: true,
        dropdownAutoWidth: true,
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });

    // Prevenir el zoom en móviles al enfocar inputs
    $('input, select, textarea').on('focus', function() {
        $(this).data('fontSize', $(this).css('font-size')).css('font-size', '16px');
    }).on('blur', function() {
        $(this).css('font-size', $(this).data('fontSize'));
    });

    // Validación del formulario
    $('form').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Validación en tiempo real para el porcentaje
    $('#percentage').on('input', function() {
        let value = parseFloat($(this).val());
        if (value < 0) $(this).val(0);
        if (value > 100) $(this).val(100);
    });

    // Botón calculadora
    $('#calculatorBtn').click(function() {
        $('#surveyDataModal').modal('show');
    });

    // Handler para el cálculo de encuestas
    $('#calculateSurveyBtn').click(function() {
        const data = $('#surveyData').val();
        if (!data.trim()) {
            Swal.fire('Error', 'Por favor ingrese datos de la encuesta', 'error');
            return;
        }

        try {
            const rows = data.trim().split('\n');
            let total = 0;
            let count = 0;
            let satisfactionLevels = [];
            let userTypes = {};
            let easyAccessCount = 0;
            let friendlyServiceCount = 0;
            let effectiveServiceCount = 0;

            // Process each row
            for (let i = 1; i < rows.length; i++) {
                const cols = rows[i].split('\t');
                if (cols.length >= 11) {
                    const satisfactionLevel = parseInt(cols[10]);
                    if (!isNaN(satisfactionLevel) && satisfactionLevel > 0 && satisfactionLevel <= 5) {
                        // Count user types
                        userTypes[cols[2]] = (userTypes[cols[2]] || 0) + 1;
                        
                        // Count positive responses
                        if (cols[4].toLowerCase().includes('si')) easyAccessCount++;
                        if (cols[5].toLowerCase().includes('sí')) friendlyServiceCount++;
                        if (cols[6].toLowerCase().includes('sí')) effectiveServiceCount++;
                        
                        satisfactionLevels.push(satisfactionLevel);
                        total += satisfactionLevel;
                        count++;
                    }
                }
            }

            if (count === 0) {
                Swal.fire('Error', 'No se encontraron datos válidos', 'error');
                return;
            }

            // Calculate statistics
            const mean = total / count;
            const percentage = (mean / 5 * 100).toFixed(2);
            const variance = satisfactionLevels.reduce((acc, val) => acc + Math.pow(val - mean, 2), 0) / count;
            const stdDev = Math.sqrt(variance).toFixed(2);
            
            // Calculate percentages for service aspects
            const easyAccessPercentage = (easyAccessCount / count * 100).toFixed(2);
            const friendlyServicePercentage = (friendlyServiceCount / count * 100).toFixed(2);
            const effectiveServicePercentage = (effectiveServiceCount / count * 100).toFixed(2);

            // Show detailed analysis
            Swal.fire({
                icon: 'success',
                title: 'Análisis de Experiencia del Usuario',
                html: `
                    <div class="text-left">
                        <h5 class="mb-3">Resumen General</h5>
                        <div class="progress-section mb-4">
                            <p class="mb-2"><strong>Satisfacción General:</strong> ${percentage}%</p>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: ${percentage}%" 
                                    aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">Aspectos del Servicio</h5>
                        <div class="service-metrics">
                            <p><i class="fas fa-door-open"></i> Facilidad de Acceso: ${easyAccessPercentage}%</p>
                            <p><i class="fas fa-smile"></i> Amabilidad del Servicio: ${friendlyServicePercentage}%</p>
                            <p><i class="fas fa-check-circle"></i> Efectividad: ${effectiveServicePercentage}%</p>
                        </div>

                        <h5 class="mb-3 mt-4">Distribución de Usuarios</h5>
                        <div class="user-distribution">
                            ${Object.entries(userTypes).map(([type, count]) => 
                                `<p>${type}: ${((count/satisfactionLevels.length) * 100).toFixed(1)}% (${count})</p>`
                            ).join('')}
                        </div>

                        <hr>
                        <p class="text-info">
                            <small>Basado en ${count} respuestas válidas<br>
                            Desviación estándar: ${stdDev}</small>
                        </p>
                    </div>
                `,
                customClass: {
                    popup: 'analysis-popup'
                }
            });

            // Update form
            $('#percentage').val(percentage);
            $('#analysis').val(`Análisis de satisfacción basado en ${count} encuestados. ` +
                `Satisfacción general: ${percentage}%. ` +
                `Aspectos destacados: Acceso ${easyAccessPercentage}%, ` +
                `Amabilidad ${friendlyServicePercentage}%, ` +
                `Efectividad ${effectiveServicePercentage}%.`);
            
            $('#surveyDataModal').modal('hide');

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al procesar los datos', 'error');
        }
    });

    // Handler para el cálculo de atención al paciente
    $('#calculatePatientCareBtn').click(function() {
        const studentData = $('#studentCareData').val();
        const staffData = $('#staffCareData').val();
        
        if (!studentData.trim() && !staffData.trim()) {
            Swal.fire('Error', 'Por favor ingrese al menos un tipo de datos de atención', 'error');
            return;
        }

        try {
            let stats = {
                total: 0,
                estudiantes: 0,
                trabajadores: 0,
                seguimiento: 0,
                notaEscrita: 0,
                llamada: 0,
                retornoSalon: 0,
                tiposAtencion: {},
                seguimientoCompleto: 0
            };

            // Process student data
            if (studentData.trim()) {
                const studentRows = studentData.split('\n').filter(row => row.trim());
                processRows(studentRows, stats, 'estudiante');
            }

            // Process staff data
            if (staffData.trim()) {
                const staffRows = staffData.split('\n').filter(row => row.trim());
                processRows(staffRows, stats, 'trabajador');
            }

            if (stats.total === 0) {
                Swal.fire('Error', 'No se encontraron datos válidos para procesar', 'error');
                return;
            }

            const porcentaje = (((stats.seguimiento + stats.notaEscrita + stats.llamada + stats.retornoSalon) / 
                (stats.total * 4)) * 100).toFixed(2);

            // Show analysis and update form
            Swal.fire({
                title: 'Análisis de Atención',
                html: `
                    <div class="text-left">
                        <div class="analysis-section">
                            <h6 class="mb-3">Indicador de Calidad</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-${porcentaje >= 80 ? 'success' : 'warning'}" 
                                    role="progressbar" 
                                    style="width: ${porcentaje}%" 
                                    aria-valuenow="${porcentaje}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    ${porcentaje}%
                                </div>
                            </div>
                        </div>
                        
                        <div class="analysis-section mt-3">
                            <h6>Distribución de Atenciones</h6>
                            <div class="row">
                                <div class="col-6">
                                    <p><strong>Estudiantes:</strong> ${((stats.estudiantes/stats.total)*100).toFixed(1)}%</p>
                                </div>
                                <div class="col-6">
                                    <p><strong>Trabajadores:</strong> ${((stats.trabajadores/stats.total)*100).toFixed(1)}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="analysis-section mt-3">
                            <h6>Indicadores de Seguimiento</h6>
                            <p><i class="fas fa-clipboard-check"></i> Seguimiento: ${((stats.seguimiento/stats.total)*100).toFixed(1)}%</p>
                            <p><i class="fas fa-notes-medical"></i> Nota Escrita: ${((stats.notaEscrita/stats.total)*100).toFixed(1)}%</p>
                            <p><i class="fas fa-phone"></i> Llamada: ${((stats.llamada/stats.total)*100).toFixed(1)}%</p>
                            <p><i class="fas fa-walking"></i> Retorno: ${((stats.retornoSalon/stats.total)*100).toFixed(1)}%</p>
                        </div>

                        <hr>
                        <p class="text-info">
                            <small>Total de atenciones: ${stats.total}<br>
                            Seguimiento completo: ${stats.seguimientoCompleto} casos</small>
                        </p>
                    </div>
                `,
                customClass: {
                    popup: 'analysis-popup'
                }
            });

            $('#percentage').val(porcentaje);
            $('#analysis').val(`Análisis de atenciones basado en ${stats.total} registros. ` +
                `Estudiantes: ${stats.estudiantes}, Trabajadores: ${stats.trabajadores}. ` +
                `Seguimiento completo: ${stats.seguimientoCompleto} casos.`);

            $('#surveyDataModal').modal('hide');

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al procesar los datos', 'error');
        }
    });

    // Handler para el cálculo de actividades de bienestar
    $('#calculateWelfareBtn').click(function() {
        const participants = parseInt($('#participantsCount').val()) || 0;
        const invited = parseInt($('#invitedCount').val()) || 0;
        
        if (invited === 0) {
            Swal.fire('Error', 'El número de invitados debe ser mayor a 0', 'error');
            return;
        }

        if (participants > invited) {
            Swal.fire('Error', 'El número de participantes no puede ser mayor al de invitados', 'error');
            return;
        }

        const percentage = ((participants / invited) * 100).toFixed(2);
        let status = '';
        let analysisText = '';

        // Determinar el estado y análisis basado en el porcentaje
        if (percentage >= 80) {
            status = 'Excelente participación';
            analysisText = `Alta participación con ${participants} asistentes de ${invited} invitados. ` +
                'Indica un gran interés y compromiso con la actividad.';
        } else if (percentage >= 60) {
            status = 'Buena participación';
            analysisText = `Participación satisfactoria con ${participants} asistentes de ${invited} invitados. ` +
                'Sugiere una buena acogida de la actividad.';
        } else {
            status = 'Baja participación';
            analysisText = `Participación menor a la esperada con ${participants} asistentes de ${invited} invitados. ` +
                'Se recomienda revisar estrategias de convocatoria.';
        }

        // Update form values
        $('#percentage').val(percentage);
        $('#analysis').val(`Análisis de participación: ${analysisText}`);

        // Show analysis popup with progress bar instead of circle
        Swal.fire({
            title: false,
            html: `
                <div class="kpi-header">
                    <div class="welfare-progress">
                        <div class="progress-wrapper">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                    role="progressbar" 
                                    style="width: 0%; background-color: ${percentage >= 80 ? '#28a745' : percentage >= 60 ? '#ffc107' : '#dc3545'}"
                                    aria-valuenow="0" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    <span class="progress-text">0%</span>
                                </div>
                            </div>
                        </div>
                        <div class="kpi-status mt-3">
                            <span class="badge badge-${percentage >= 80 ? 'success' : percentage >= 60 ? 'warning' : 'danger'} px-4 py-2">
                                ${status}
                            </span>
                        </div>
                    </div>
                    <div class="kpi-details mt-3">
                        <p class="mb-0">${participants} participantes de ${invited} invitados</p>
                    </div>
                `,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                popup: 'kpi-modal'
            },
            didOpen: () => {
                // Animate progress bar
                setTimeout(() => {
                    const progressBar = document.querySelector('.progress-bar');
                    const progressText = document.querySelector('.progress-text');
                    let count = 0;
                    const duration = 1500;
                    const increment = percentage / (duration / 16);
                    
                    const updateProgress = () => {
                        count = Math.min(count + increment, percentage);
                        progressBar.style.width = `${count}%`;
                        progressBar.setAttribute('aria-valuenow', count);
                        progressText.textContent = `${Math.round(count)}%`;
                        
                        if (count < percentage) {
                            requestAnimationFrame(updateProgress);
                        }
                    };
                    
                    requestAnimationFrame(updateProgress);
                }, 100);
            }
        });

        $('#surveyDataModal').modal('hide');
    });

    // Handler para el cálculo de control médico
    $('#calculateMedicalBtn').click(function() {
        const medicatedStudents = parseInt($('#medicatedStudents').val()) || 0;
        const totalNeedingMedication = parseInt($('#totalNeedingMedication').val()) || 0;
        const updatedRecords = parseInt($('#updatedRecords').val()) || 0;
        const totalStudents = parseInt($('#totalStudents').val()) || 0;
        
        if (!totalNeedingMedication || !totalStudents) {
            Swal.fire('Error', 'Por favor complete todos los campos', 'error');
            return;
        }

        if (medicatedStudents > totalNeedingMedication || updatedRecords > totalStudents) {
            Swal.fire('Error', 'Los valores no pueden ser mayores que sus totales', 'error');
            return;
        }

        const medicationPercentage = (medicatedStudents / totalNeedingMedication * 100).toFixed(2);
        const recordsPercentage = (updatedRecords / totalStudents * 100).toFixed(2);
        const averagePercentage = ((parseFloat(medicationPercentage) + parseFloat(recordsPercentage)) / 2).toFixed(2);

        let status, analysisText;
        
        if (averagePercentage >= 90) {
            status = 'Control médico óptimo';
            analysisText = `Excelente gestión médica. Suministro de medicamentos al ${medicationPercentage}% y actualización de fichas al ${recordsPercentage}%.`;
        } else if (averagePercentage >= 70) {
            status = 'Control médico adecuado';
            analysisText = `Gestión médica satisfactoria con oportunidades de mejora. Suministro: ${medicationPercentage}%, Actualización: ${recordsPercentage}%.`;
        } else {
            status = 'Requiere atención';
            analysisText = `Necesita mejoras significativas. Suministro: ${medicationPercentage}%, Actualización: ${recordsPercentage}%. Se recomienda plan de acción.`;
        }

        $('#percentage').val(averagePercentage);
        $('#analysis').val(analysisText);

        Swal.fire({
            title: status,
            html: `
                <div class="kpi-header">
                    <div class="medical-progress">
                        <div class="progress-wrapper">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                    role="progressbar" 
                                    style="width: 0%; background-color: ${averagePercentage >= 90 ? '#28a745' : averagePercentage >= 70 ? '#ffc107' : '#dc3545'}"
                                    aria-valuenow="0" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    <span class="progress-text">0%</span>
                                </div>
                            </div>
                        </div>
                        <div class="metrics-container mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <div class="metric-item">
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar" style="width: ${medicationPercentage}%; background-color: #17a2b8;"></div>
                                        </div>
                                        <small>Medicación: ${medicationPercentage}%</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-item">
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar" style="width: ${recordsPercentage}%; background-color: #6f42c1;"></div>
                                        </div>
                                        <small>Fichas: ${recordsPercentage}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="status-badge mt-3">
                        <span class="badge badge-${averagePercentage >= 90 ? 'success' : averagePercentage >= 70 ? 'warning' : 'danger'} px-4 py-2">
                            ${status}
                        </span>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Aceptar',
            didOpen: () => {
                setTimeout(() => {
                    const progressBar = document.querySelector('.progress-bar');
                    const progressText = document.querySelector('.progress-text');
                    let count = 0;
                    const duration = 1500;
                    const increment = averagePercentage / (duration / 16);
                    
                    const updateProgress = () => {
                        count = Math.min(count + increment, averagePercentage);
                        progressBar.style.width = `${count}%`;
                        progressBar.setAttribute('aria-valuenow', count);
                        progressText.textContent = `${Math.round(count)}%`;
                        
                        if (count < averagePercentage) {
                            requestAnimationFrame(updateProgress);
                        }
                    };
                    
                    requestAnimationFrame(updateProgress);
                }, 100);
            }
        });
    });
});

// Add helper function to process rows
function processRows(rows, stats, type) {
    rows.forEach(row => {
        const cols = row.split('\t');
        if (cols.length >= 6) {
            stats.total++;
            
            // Count by type
            if (type === 'estudiante') stats.estudiantes++;
            if (type === 'trabajador') stats.trabajadores++;
            
            // Count quality indicators
            if (cols.includes('SEGUIMIENTO')) stats.seguimiento++;
            if (cols.includes('NOTA ESCRITA')) stats.notaEscrita++;
            if (cols.includes('LLAMADA')) stats.llamada++;
            if (cols.includes('RETORNA AL SALON')) stats.retornoSalon++;
            
            // Count complete follow-up
            if (cols.includes('SEGUIMIENTO') && 
                cols.includes('NOTA ESCRITA') && 
                cols.includes('LLAMADA')) {
                stats.seguimientoCompleto++;
            }

            // Track attention types
            const tipoAtencion = cols[2].trim();
            stats.tiposAtencion[tipoAtencion] = (stats.tiposAtencion[tipoAtencion] || 0) + 1;
        }
    });
}

// Add styles for the analysis popup
$('head').append(`
    <style>
        .analysis-popup {
            max-width: 600px;
        }
        .progress {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            margin-top: 5px;
        }
        .progress-bar {
            background-color: #364E76;
            border-radius: 10px;
        }
        .service-metrics i {
            width: 20px;
            color: #364E76;
        }
        .user-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }
        .analysis-section i {
            width: 20px;
            color: #364E76;
        }
    </style>
`);

// Add these styles to the CSS section
$('head').append(`
    <style>
        .welfare-progress {
            padding: 1rem;
        }
        
        .progress-wrapper {
            margin: 1rem auto;
            max-width: 300px;
        }
        
        .progress {
            height: 30px;
            background-color: #f3f3f3;
            border-radius: 15px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            transition: width 1.5s ease-in-out;
        }
        
        .progress-text {
            mix-blend-mode: difference;
        }
    </style>
`);
</script>
@stop