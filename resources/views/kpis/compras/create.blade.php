@extends('adminlte::page')

@section('title', 'Nuevo KPI - Compras')

@section('content_header')
    <!-- <h1 class="text-primary">Registrar KPI - Compras</h1> -->
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-white">Formulario de Registro de KPI</h3>
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

        <form action="{{ route('kpis.compras.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type" class="form-label">
                            Tipo de KPI <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="type" class="form-control select2bs4" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="measurement">Medición</option>
                            <option value="informative">Informativo</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="threshold_id" class="form-label">
                            Nombre del Indicador <span class="text-danger">*</span>
                        </label>
                        <select name="threshold_id" id="threshold_id" class="form-control @error('threshold_id') is-invalid @enderror" required>
                            <option value="">Seleccione un Indicador</option>
                            @foreach($thresholds as $threshold)
                                <option value="{{ $threshold->id }}" {{ old('threshold_id') == $threshold->id ? 'selected' : '' }}>
                                    {{ $threshold->kpi_name }} ({{ $threshold->value }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="methodology" class="form-label">
                    Metodología de Medición <span class="text-danger">*</span>
                </label>
                <textarea name="methodology" id="methodology" class="form-control" rows="3" required>{{ old('methodology') }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="frequency" class="form-label">
                            Frecuencia de Medición <span class="text-danger">*</span>
                        </label>
                        <select name="frequency" id="frequency" class="form-control select2bs4" required>
                            <option value="">Seleccione una frecuencia</option>
                            <option value="Diario">Diario</option>
                            <option value="Quincenal">Quincenal</option>
                            <option value="Mensual">Mensual</option>
                            <option value="Semestral">Semestral</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="measurement_date" class="form-label">
                            Fecha de Medición <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="measurement_date" id="measurement_date" 
                               class="form-control" value="{{ old('measurement_date') }}" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="percentage" class="form-label">
                            Porcentaje Alcanzado (%) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" max="100" name="percentage" 
                               id="percentage" class="form-control" value="{{ old('percentage') }}" required>
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
                            <option value="formulario" {{ old('methodology') == 'formulario' ? 'selected' : '' }}>Formulario</option>
                            <option value="encuesta" {{ old('methodology') == 'encuesta' ? 'selected' : '' }}>Encuesta</option>
                            <option value="archivo" {{ old('methodology') == 'archivo' ? 'selected' : '' }}>Archivo</option>
                            <option value="otro" {{ old('methodology') == 'otro' ? 'selected' : '' }}>Otro</option>
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
                        <a href="{{ route('kpis.compras.index') }}" class="btn btn-secondary">
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

<div class="modal fade" id="surveyDataModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #364E76; color: white;">
                <h5 class="modal-title">Calculadora de KPI</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Tabs de navegación -->
                <ul class="nav nav-tabs" id="calculatorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="survey-tab" data-toggle="tab" data-target="#survey" 
                                type="button" role="tab" aria-controls="survey" aria-selected="true">
                            <i class="fas fa-poll"></i> Encuestas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="orders-tab" data-toggle="tab" data-target="#orders" 
                                type="button" role="tab" aria-controls="orders" aria-selected="false">
                            <i class="fas fa-boxes"></i> Recursos en el almacén
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="copies-tab" data-toggle="tab" data-target="#copies" 
                                type="button" role="tab" aria-controls="copies" aria-selected="false">
                            <i class="fas fa-copy"></i> Servicio de Fotocopias
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="suppliers-tab" data-toggle="tab" data-target="#materials-delivery" 
                                type="button" role="tab" aria-controls="materials-delivery" aria-selected="false">
                            <i class="fas fa-truck-loading"></i> Entrega de Materiales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="resources-tab" data-toggle="tab" data-target="#supplier-evaluation" 
                                type="button" role="tab" aria-controls="supplier-evaluation" aria-selected="false">
                            <i class="fas fa-star"></i> Evaluacion de proveedores
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

                    <!-- Pestaña de Pedidos -->
                    <div class="tab-pane fade" id="orders" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Análisis de Inventario</h6>
                            <p class="mb-1">Pegue el contenido del archivo Excel con las siguientes columnas:</p>
                            <ul class="pl-3 mb-0">
                                <li>PRODUCTO</li>
                                <li>CANTIDADES SUGERIDAS PARA TENER EN STOCK</li>
                                <li>STOCK</li>
                                <li>CANTIDADES A COMPRAR</li>
                            </ul>
                        </div>
                        <div class="form-group">
                            <label>Datos del Inventario</label>
                            <textarea class="form-control" id="inventoryData" rows="10" 
                                placeholder="PRODUCTO&#9;CANTIDADES SUGERIDAS PARA TENER EN STOCK&#9;STOCK&#9;CANTIDADES A COMPRAR"></textarea>
                            <small class="text-muted">Copie y pegue directamente desde Excel manteniendo el formato de columnas separadas por tabulaciones.</small>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculateInventoryBtn">
                            <i class="fas fa-calculator mr-2"></i>Analizar Inventario
                        </button>
                    </div>

                    <!-- Pestaña de Proveedores -->
                    <div class="tab-pane fade" id="materials-delivery" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Análisis de Entrega de Materiales</h6>
                            <p class="mb-1">Seguimiento a la distribución y entrega de materiales escolares</p>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Total de Materiales Entregados</label>
                                    <input type="number" class="form-control" id="totalMaterials" min="0" placeholder="Cantidad total de materiales entregados">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cantidad en Colegio</label>
                                    <input type="number" class="form-control" id="materialsAtSchool" min="0" placeholder="Materiales entregados en el colegio">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cantidad en Casa</label>
                                    <input type="number" class="form-control" id="materialsAtHome" min="0" placeholder="Materiales entregados en casa">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cantidad No Entregada</label>
                                    <input type="number" class="form-control" id="materialsNotDelivered" min="0" placeholder="Materiales no entregados">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <div class="alert alert-warning mb-0" id="materialsValidation" style="display: none;">
                                La suma de los materiales por ubicación debe ser igual al total de materiales entregados.
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculateMaterialsBtn">
                            Analizar Entrega de Materiales
                        </button>
                    </div>

                    <!-- Replace Resources tab with Supplier Evaluation tab -->
                    <div class="tab-pane fade" id="supplier-evaluation" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Evaluación y Reevaluación de Proveedores</h6>
                            <p class="mb-1">Análisis de las evaluaciones de proveedores registradas en el sistema</p>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="supplierEvaluationLoading" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Cargando...</span>
                                    </div>
                                    <p class="mt-2">Cargando datos de evaluaciones de proveedores...</p>
                                </div>
                                <div id="supplierEvaluationData" style="display: none;">
                                    <div class="table-responsive mb-4">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Categoría</th>
                                                    <th>Total Proveedores</th>
                                                    <th>Calificación Promedio</th>
                                                    <th>Porcentaje</th>
                                                </tr>
                                            </thead>
                                            <tbody id="supplierEvaluationTableBody">
                                                <!-- Data will be populated dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header" style="background-color: #364E76;">
                                                    <h6 class="mb-0 text-white">Mejores Proveedores</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="topSuppliersList">
                                                        <!-- Top suppliers will be listed here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-primary" id="fetchSupplierEvaluationsBtn">
                                        <i class="fas fa-sync-alt mr-2"></i>Cargar Datos de Evaluaciones
                                    </button>
                                    <button type="button" class="btn btn-success" id="applySupplierEvaluationBtn">
                                        <i class="fas fa-check mr-2"></i>Aplicar Resultados
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Disponibilidad de Recursos -->
                    <div class="tab-pane fade" id="resources" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Disponibilidad de Recursos</h6>
                            <p class="mb-1">Medición de disponibilidad de recursos en almacén</p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total de Items Solicitados</label>
                                    <input type="number" class="form-control" id="totalItems" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Items Disponibles</label>
                                    <input type="number" class="form-control" id="availableItems" min="0">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="calculateResourcesBtn">
                            Calcular Disponibilidad
                        </button>
                    </div>

                    <!-- Servicio de Fotocopias -->
                    <div class="tab-pane fade" id="copies" role="tabpanel">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Análisis Completo del Servicio de Fotocopias</h6>
                            <p class="mb-1">Pegue el registro de fotocopias exportado desde el sistema con el siguiente formato:</p>
                            <p class="small mb-0"><code>MES | NOMBRE DOCENTE | SECCIÓN | CURSO | IMPRESIONES B/N | IMPRESIONES COLOR | DOBLE CARTA COLOR | FECHA DE ENTREGA | RECIBIDO A SATISFACCIÓN</code></p>
                            <div class="mt-2">
                                <small class="text-primary"><i class="fas fa-info-circle mr-1"></i>
                                    El análisis incluye: ranking de usuarios, distribución por secciones, cursos más activos, tendencias mensuales y eficiencia del servicio.
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Datos del Servicio de Fotocopias</label>
                            <textarea class="form-control" id="photocopiesData" rows="12" 
                                placeholder="Pegue aquí los datos exportados desde el sistema de fotocopias...
Ejemplo:
ENERO	JUAN PÉREZ	PRIMARIA A	MATEMÁTICAS	50	25	10	2024-01-15	SÍ
ENERO	MARÍA GARCÍA	SECUNDARIA B	CIENCIAS	30	40	5	2024-01-16	SÍ"></textarea>
                            <small class="text-muted">
                                <i class="fas fa-clipboard mr-1"></i>Copie los datos directamente desde Excel o use la función de exportar del sistema.
                                <br><i class="fas fa-chart-line mr-1"></i>Se generará un análisis completo con estadísticas detalladas.
                            </small>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary btn-block" id="calculatePhotocopiesBtn">
                                    <i class="fas fa-analytics mr-2"></i>Realizar Análisis Completo
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-info btn-block" id="previewPhotocopiesBtn">
                                    <i class="fas fa-eye mr-2"></i>Vista Previa de Datos
                                </button>
                            </div>
                        </div>
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
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .custom-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .card-header {
        background: #364E76 !important; /* Changed from gradient to solid color */
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.5rem;
    }

    .card-header .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .card-body {
        padding: 2rem;
    }

    .form-label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        height: auto;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.25);
    }

    .select2-container--bootstrap4 .select2-selection {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        min-height: 45px;
        padding: 0.5rem;
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(26, 72, 132, 0.25);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: #364E76; /* Changed from gradient to solid color */
        border: none;
        box-shadow: 0 2px 4px rgba(54, 78, 118, 0.25);
    }

    .btn-primary:hover {
        background: #2c3e5f; /* Slightly darker shade for hover */
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(54, 78, 118, 0.35);
    }

    .alert {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--box-shadow);
    }

    .text-danger {
        color: var(--danger) !important;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }

        .btn {
            width: 100%;
            margin: 0.5rem 0;
        }
    }

    .nav-tabs .nav-link {
        color: #364E76;
    }

    .nav-tabs .nav-link.active {
        color: #364E76;
        font-weight: 600;
    }

    .alert-info {
        border-left: 4px solid #364E76;
    }

    .metric-item i,
    .metric-label,
    .tab-content i {
        color: #364E76;
    }

    .progress-bar {
        background-color: #364E76;
    }

    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
    }

    .btn-primary:hover {
        background-color: #2a3d5f;
        border-color: #2a3d5f;
    }

    .modal-header {
        background-color: #364E76 !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Seleccione una opción'
    });

    // Remove duplicate methodology field
    if (document.querySelectorAll('[name="methodology"]').length > 1) {
        // Remove the textarea methodology field and keep only the select
        document.querySelector('textarea#methodology').closest('.form-group').remove();
    }

    $('#percentage').on('input', function() {
        let value = parseFloat($(this).val());
        if (value < 0) $(this).val(0);
        if (value > 100) $(this).val(100);
    });

    $('#calculatorBtn').click(function() {
        $('#surveyDataModal').modal('show');
    });

    $('#calculateSurveyBtn').click(function() {
        const data = $('#surveyData').val();
        if (!data.trim()) {
            Swal.fire({
                title: 'Datos Requeridos',
                text: 'Por favor ingrese los datos de la encuesta para realizar el análisis',
                icon: 'info',
                confirmButtonColor: '#364E76'
            });
            return;
        }

        try {
            const rows = data.trim().split('\n');
            let stats = {
                total: 0,
                satisfactionLevels: [],
                userTypes: {},
                calificacionServicio: { total: 0, count: 0 },
                tiemposAtencion: { total: 0, count: 0 },
                satisfaccionPersonal: { total: 0, count: 0 },
                necesidadSatisfecha: 0,
                comments: []
            };

            // Mapeo de respuestas a valores numéricos
            const satisfactionMap = {
                'muy satisfecho': 5,
                'satisfecho': 4,
                'regular': 3,
                'insatisfecho': 2,
                'muy insatisfecho': 1
            };

            const calificationMap = {
                'excelente': 5,
                'bueno': 4,
                'regular': 3,
                'malo': 2,
                'muy malo': 1
            };

            // Process each row, skip header
            for (let i = 1; i < rows.length; i++) {
                const cols = rows[i].split('\t');
                if (cols.length >= 7) { // Verificar que tenga suficientes columnas
                    stats.total++;
                    
                    // Count user types (column 2)
                    const userType = cols[1].trim();
                    stats.userTypes[userType] = (stats.userTypes[userType] || 0) + 1;
                    
                    // Calificación del servicio (column 3)
                    const servicioValue = satisfactionMap[cols[2].toLowerCase()];
                    if (servicioValue) {
                        stats.calificacionServicio.total += servicioValue;
                        stats.calificacionServicio.count++;
                    }

                    // Tiempos de atención (column 4)
                    const tiempoValue = calificationMap[cols[3].toLowerCase()];
                    if (tiempoValue) {
                        stats.tiemposAtencion.total += tiempoValue;
                        stats.tiemposAtencion.count++;
                    }

                    // Necesidad satisfecha (column 5)
                    if (cols[4].toLowerCase().includes('sí')) {
                        stats.necesidadSatisfecha++;
                    }

                    // Satisfacción con el personal (column 7)
                    const personalValue = satisfactionMap[cols[6].toLowerCase()];
                    if (personalValue) {
                        stats.satisfaccionPersonal.total += personalValue;
                        stats.satisfaccionPersonal.count++;
                    }

                    // Store comments (column 8)
                    if (cols[7] && cols[7].trim() !== 'N/A' && cols[7].trim() !== '.') {
                        stats.comments.push(cols[7].trim());
                    }
                }
            }

            if (stats.total === 0) {
                throw new Error('No se encontraron datos válidos para analizar');
            }

            // Calculate averages and percentages
            const servicioPromedio = (stats.calificacionServicio.total / stats.calificacionServicio.count) || 0;
            const tiemposPromedio = (stats.tiemposAtencion.total / stats.tiemposAtencion.count) || 0;
            const personalPromedio = (stats.satisfaccionPersonal.total / stats.satisfaccionPersonal.count) || 0;
            
            // Calculate overall percentage
            const overallAverage = (servicioPromedio + tiemposPromedio + personalPromedio) / 3;
            const percentage = ((overallAverage / 5) * 100).toFixed(2);
            
            // Generate analysis text
            let analysisText = `Análisis basado en ${stats.total} respuestas. `;
            analysisText += `Satisfacción general: ${percentage}%. `;
            analysisText += `Calificación del servicio: ${((servicioPromedio/5)*100).toFixed(1)}%, `;
            analysisText += `Tiempos de atención: ${((tiemposPromedio/5)*100).toFixed(1)}%, `;
            analysisText += `Satisfacción con el personal: ${((personalPromedio/5)*100).toFixed(1)}%. `;
            analysisText += `${((stats.necesidadSatisfecha/stats.total)*100).toFixed(1)}% indica que su necesidad fue satisfecha. `;
            
            if (stats.comments.length > 0) {
                analysisText += `Se registraron ${stats.comments.length} comentarios/sugerencias.`;
            }

            // Show results
            Swal.fire({
                title: 'Análisis de Satisfacción',
                html: `
                    <div class="analysis-container">
                        <div class="satisfaction-score mb-4">
                            <h4 class="text-center mb-3">Satisfacción General</h4>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar bg-${getColorClass(percentage)}" 
                                    role="progressbar" 
                                    style="width: ${percentage}%"
                                    aria-valuenow="${percentage}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                    ${percentage}%
                                </div>
                            </div>
                            <p class="text-muted text-center">${getPerformanceLabel(percentage)}</p>
                        </div>

                        <div class="metrics-grid mb-4">
                            <div class="metric-item">
                                <span class="metric-label">Satisfacción del Servicio</span>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: ${((servicioPromedio/5)*100).toFixed(1)}%">
                                        ${((servicioPromedio/5)*100).toFixed(1)}%
                                    </div>
                                </div>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Tiempos de Atención</span>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: ${((tiemposPromedio/5)*100).toFixed(1)}%">
                                        ${((tiemposPromedio/5)*100).toFixed(1)}%
                                    </div>
                                </div>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label">Satisfacción con Personal</span>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width: ${((personalPromedio/5)*100).toFixed(1)}%">
                                        ${((personalPromedio/5)*100).toFixed(1)}%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="user-distribution mb-4">
                            <h5>Distribución por Tipo de Usuario</h5>
                            <div class="user-types-grid">
                                ${Object.entries(stats.userTypes).map(([type, count]) => `
                                    <div class="user-type-item">
                                        <span class="type-label">${type}</span>
                                        <span class="type-count">${((count/stats.total)*100).toFixed(1)}%</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        ${stats.comments.length > 0 ? `
                            <div class="comments-summary">
                                <h5>Resumen de Comentarios</h5>
                                <p class="text-muted">
                                    Se registraron ${stats.comments.length} comentarios cualitativos que pueden proporcionar insights adicionales.
                                </p>
                            </div>
                        ` : ''}
                    </div>
                `,
                customClass: {
                    container: 'analysis-modal-container',
                    popup: 'analysis-modal-popup'
                },
                width: 600,
                confirmButtonText: 'Aplicar Resultados',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#364E76',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#percentage').val(percentage);
                    $('#analysis').val(analysisText);
                    $('#surveyDataModal').modal('hide');
                }
            });

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Error al procesar los datos de la encuesta',
                confirmButtonColor: '#364E76'
            });
        }
    });

    // Helper functions
    function getColorClass(percentage) {
        if (percentage >= 90) return 'success';
        if (percentage >= 70) return 'info';
        if (percentage >= 50) return 'warning';
        return 'danger';
    }

    function getPerformanceLabel(percentage) {
        if (percentage >= 90) return 'Excelente nivel de satisfacción';
        if (percentage >= 70) return 'Buen nivel de satisfacción';
        if (percentage >= 50) return 'Satisfacción moderada';
        return 'Requiere atención inmediata';
    }

    // Add these styles to your existing CSS
    $('head').append(`
        <style>
            .analysis-container {
                padding: 1rem;
            }
            
            .satisfaction-score {
                background: #f8f9fa;
                padding: 1.5rem;
                border-radius: 8px;
            }
            
            .metrics-grid {
                display: grid;
                gap: 1rem;
                margin: 1.5rem 0;
            }
            
            .metric-item {
                background: white;
                padding: 1rem;
                border-radius: 6px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            
            .metric-label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
                font-size: 0.9rem;
            }
            
            .user-types-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.5rem;
                margin-top: 1rem;
            }
            
            .user-type-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f8f9fa;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                font-size: 0.9rem;
            }
            
            .type-count {
                font-weight: 600;
                color: #364E76;
            }
            
            .progress {
                height: 1rem;
                border-radius: 0.5rem;
                background-color: #e9ecef;
            }
            
            .progress-bar {
                transition: width 1.5s ease-in-out;
                border-radius: 0.5rem;
            }
            
            .comments-summary {
                background: #f8f9fa;
                padding: 1rem;
                border-radius: 6px;
                margin-top: 1.5rem;
            }
        </style>
    `);

    $('#calculateInventoryBtn').click(function() {
        const data = $('#inventoryData').val();
        if (!data.trim()) {
            Swal.fire('Error', 'Por favor ingrese los datos del inventario', 'error');
            return;
        }

        try {
            const rows = data.trim().split('\n');
            const inventory = [];
            let totalItems = 0;
            let itemsToOrder = 0;
            let itemsOverstock = 0;
            let itemsOptimal = 0;

            // Skip header row
            for (let i = 1; i < rows.length; i++) {
                const columns = rows[i].split('\t').filter(col => col.trim());
                if (columns.length >= 4) {
                    const item = {
                        name: columns[0],
                        suggested: parseInt(columns[1]) || 0,
                        current: parseInt(columns[2]) || 0,
                        toPurchase: parseInt(columns[3]) || 0
                    };
                    inventory.push(item);
                    totalItems++;

                    // CORRECCIÓN: Valores negativos en "Cantidades a comprar" indican sobre stock
                    // Valores positivos indican necesidad de compra
                    if (item.toPurchase > 0) itemsToOrder++;
                    else if (item.toPurchase < 0) itemsOverstock++;
                    else itemsOptimal++;
                }
            }

            // Calculate percentages and statistics
            const orderPercentage = ((itemsToOrder / totalItems) * 100).toFixed(2);
            const overstockPercentage = ((itemsOverstock / totalItems) * 100).toFixed(2);
            const optimalPercentage = ((itemsOptimal / totalItems) * 100).toFixed(2);

            // Calculate overall inventory health score
            // Higher weightage to optimal items and penalizing both understock and overstock
            const healthScore = (((itemsOptimal * 100) + (itemsOverstock * 30)) / totalItems).toFixed(2);

            const analysisText = `Análisis de ${totalItems} productos: ${itemsToOrder} requieren compra (${orderPercentage}%), ` +
                `${itemsOptimal} en nivel óptimo (${optimalPercentage}%), ${itemsOverstock} con sobrestock (${overstockPercentage}%). ` +
                `Índice de salud del inventario: ${healthScore}%`;

            // Show results in SweetAlert2 modal
            Swal.fire({
                title: 'Análisis de Inventario',
                html: `
                    <div class="inventory-analysis">
                        <div class="mb-4">
                            <h5>Salud del Inventario: ${healthScore}%</h5>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-${healthScore >= 70 ? 'success' : 'warning'}" 
                                    style="width: ${healthScore}%">
                                    ${healthScore}%
                                </div>
                            </div>
                            <div class="calculation-details mt-3">
                                <p class="font-weight-bold">Cálculos realizados:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <th>Métrica</th>
                                            <th>Cálculo</th>
                                            <th>Resultado</th>
                                        </tr>
                                        <tr>
                                            <td>Items en nivel óptimo</td>
                                            <td>${itemsOptimal} de ${totalItems} items</td>
                                            <td>${optimalPercentage}%</td>
                                        </tr>
                                        <tr>
                                            <td>Items con sobrestock</td>
                                            <td>${itemsOverstock} de ${totalItems} items</td>
                                            <td>${overstockPercentage}%</td>
                                        </tr>
                                        <tr>
                                            <td>Items por comprar</td>
                                            <td>${itemsToOrder} de ${totalItems} items</td>
                                            <td>${orderPercentage}%</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="calculation-formula mt-2">
                                    <p class="mb-1"><strong>Fórmula de salud del inventario:</strong></p>
                                    <code>
                                        ((Items óptimos × 100) + (Items sobrestock × 30)) ÷ Total items<br>
                                        ((${itemsOptimal} × 100) + (${itemsOverstock} × 30)) ÷ ${totalItems} = ${healthScore}%
                                    </code>
                                    <p class="small text-muted mt-1">Nota: El sobrestock se pondera menos porque representa capital inmovilizado.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="inventory-metrics">
                            <div class="metric">
                                <span class="label">Productos a Comprar</span>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" style="width: ${orderPercentage}%">
                                        ${itemsToOrder} (${orderPercentage}%)
                                    </div>
                                </div>
                            </div>
                            <div class="metric">
                                <span class="label">Nivel Óptimo</span>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: ${optimalPercentage}%">
                                        ${itemsOptimal} (${optimalPercentage}%)
                                    </div>
                                </div>
                            </div>
                            <div class="metric">
                                <span class="label">Sobrestock</span>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: ${overstockPercentage}%">
                                        ${itemsOverstock} (${overstockPercentage}%)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="urgent-items">
                                    <h6>Productos Prioritarios para Compra:</h6>
                                    <ul class="list-unstyled">
                                        ${inventory
                                            .filter(item => item.toPurchase > 0)
                                            .sort((a, b) => b.toPurchase - a.toPurchase)
                                            .slice(0, 5)
                                            .map(item => `
                                                <li class="text-left">
                                                    <i class="fas fa-shopping-cart text-danger"></i>
                                                    ${item.name}: Comprar ${item.toPurchase} unidades
                                                </li>
                                            `).join('')}
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="overstock-items">
                                    <h6>Productos con Mayor Sobrestock:</h6>
                                    <ul class="list-unstyled">
                                        ${inventory
                                            .filter(item => item.toPurchase < 0)
                                            .sort((a, b) => a.toPurchase - b.toPurchase)
                                            .slice(0, 5)
                                            .map(item => `
                                                <li class="text-left">
                                                    <i class="fas fa-exclamation-circle text-warning"></i>
                                                    ${item.name}: Excedente de ${Math.abs(item.toPurchase)} unidades
                                                </li>
                                            `).join('')}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Aplicar Resultados',
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                width: 800
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#percentage').val(healthScore);
                    $('#analysis').val(analysisText);
                    $('#surveyDataModal').modal('hide');
                }
            });

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar los datos del inventario',
            });
        }
    });

    // Add these styles
    $('head').append(`
        <style>
            .inventory-analysis {
                padding: 1rem;
            }
            .inventory-metrics {
                display: grid;
                gap: 1rem;
                margin: 1rem 0;
            }
            .metric {
                background: #f8f9fa;
                padding: 0.5rem;
                border-radius: 4px;
            }
            .metric .label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
                font-size: 0.9rem;
            }
            .urgent-items {
                background: #fff3cd;
                padding: 1rem;
                border-radius: 4px;
            }
            .urgent-items li {
                margin-bottom: 0.5rem;
            }
        </style>
    `);

    // Vista previa de datos de fotocopias
    $('#previewPhotocopiesBtn').click(function() {
        const data = $('#photocopiesData').val();
        if (!data.trim()) {
            Swal.fire('Error', 'Por favor ingrese los datos de fotocopias', 'error');
            return;
        }

        try {
            const rows = data.trim().split('\n');
            const previewRows = rows.slice(0, 6); // Mostrar las primeras 6 filas
            
            let previewHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
            previewRows.forEach((row, index) => {
                const cols = row.split('\t');
                const isHeader = index === 0;
                previewHtml += isHeader ? '<thead><tr class="bg-primary text-white">' : '<tr>';
                
                cols.forEach(col => {
                    previewHtml += isHeader ? `<th style="font-size: 11px; padding: 4px;">${col}</th>` : 
                                            `<td style="font-size: 10px; padding: 3px;">${col}</td>`;
                });
                
                previewHtml += isHeader ? '</tr></thead><tbody>' : '</tr>';
            });
            previewHtml += '</tbody></table></div>';
            
            if (rows.length > 6) {
                previewHtml += `<p class="text-muted text-center"><small>... y ${rows.length - 6} filas más</small></p>`;
            }

            Swal.fire({
                title: 'Vista Previa de Datos',
                html: previewHtml,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#364E76',
                width: '900px'
            });

        } catch (error) {
            Swal.fire('Error', 'Error al procesar los datos', 'error');
        }
    });

    // Análisis completo de fotocopias
    $('#calculatePhotocopiesBtn').click(function() {
        const data = $('#photocopiesData').val();
        if (!data.trim()) {
            Swal.fire('Error', 'Por favor ingrese los datos de fotocopias', 'error');
            return;
        }

        try {
            const rows = data.trim().split('\n');
            let stats = {
                total: 0,
                blancoNegro: 0, 
                color: 0,
                dobleCarta: 0,
                satisfaccion: {
                    si: 0,
                    no: 0
                },
                docentes: {},
                secciones: {},
                cursos: {},
                meses: {}
            };

            // Procesar desde la segunda fila (ignorar encabezados)
            for (let i = 1; i < rows.length; i++) {
                const cols = rows[i].split('\t');
                if (cols.length >= 9) {
                    stats.total++;
                    
                    const mes = cols[0] || 'Sin especificar';
                    const docente = cols[1] || 'Sin especificar';
                    const seccion = cols[2] || 'Sin especificar';
                    const curso = cols[3] || 'Sin especificar';
                    const impresionBN = parseInt(cols[4]) || 0;
                    const impresionColor = parseInt(cols[5]) || 0;
                    const dobleCarta = parseInt(cols[6]) || 0;
                    const satisfecho = (cols[8] || '').toLowerCase().includes('sí') || 
                                      (cols[8] || '').toLowerCase().includes('si') ||
                                      (cols[8] || '').toLowerCase().includes('yes') ||
                                      (cols[8] || '').toLowerCase().includes('recibido') ||
                                      (!(cols[8] || '').toLowerCase().includes('pendiente') && 
                                       !(cols[8] || '').toLowerCase().includes('no') && 
                                       cols[8] && cols[8].trim() !== '');
                    
                    // Totales de impresiones
                    stats.blancoNegro += impresionBN;
                    stats.color += impresionColor;
                    stats.dobleCarta += dobleCarta;
                    
                    // Satisfacción
                    if (satisfecho) {
                        stats.satisfaccion.si++;
                    } else {
                        stats.satisfaccion.no++;
                    }
                    
                    // Contadores por categoría
                    const totalImpresionesDocente = impresionBN + impresionColor + dobleCarta;
                    
                    // Por docente
                    if (!stats.docentes[docente]) {
                        stats.docentes[docente] = { total: 0, solicitudes: 0, satisfecho: 0 };
                    }
                    stats.docentes[docente].total += totalImpresionesDocente;
                    stats.docentes[docente].solicitudes++;
                    if (satisfecho) stats.docentes[docente].satisfecho++;
                    
                    // Por sección
                    if (!stats.secciones[seccion]) {
                        stats.secciones[seccion] = { total: 0, solicitudes: 0, satisfecho: 0 };
                    }
                    stats.secciones[seccion].total += totalImpresionesDocente;
                    stats.secciones[seccion].solicitudes++;
                    if (satisfecho) stats.secciones[seccion].satisfecho++;
                    
                    // Por curso
                    if (!stats.cursos[curso]) {
                        stats.cursos[curso] = { total: 0, solicitudes: 0, satisfecho: 0 };
                    }
                    stats.cursos[curso].total += totalImpresionesDocente;
                    stats.cursos[curso].solicitudes++;
                    if (satisfecho) stats.cursos[curso].satisfecho++;
                    
                    // Por mes
                    if (!stats.meses[mes]) {
                        stats.meses[mes] = { total: 0, solicitudes: 0, satisfecho: 0 };
                    }
                    stats.meses[mes].total += totalImpresionesDocente;
                    stats.meses[mes].solicitudes++;
                    if (satisfecho) stats.meses[mes].satisfecho++;
                }
            }

            // Calcular porcentajes y ranking
            const totalImpresiones = stats.blancoNegro + stats.color + stats.dobleCarta;
            const porcentajeSatisfaccion = ((stats.satisfaccion.si / stats.total) * 100).toFixed(1);
            
            // Calcular porcentaje general del KPI (sistema más laxo)
            let porcentajeKPI = 0;
            
            // Factor 1: Satisfacción (peso 70%) - factor principal
            const factorSatisfaccion = (stats.satisfaccion.si / stats.total) * 70;
            
            // Factor 2: Cobertura de servicio (peso 20%) - basado en solicitudes atendidas vs rechazadas
            let factorCobertura = 0;
            const porcentajeAtencion = (stats.satisfaccion.si / stats.total) * 100;
            if (porcentajeAtencion >= 95) factorCobertura = 20;      // Excelente cobertura
            else if (porcentajeAtencion >= 85) factorCobertura = 18; // Muy buena cobertura
            else if (porcentajeAtencion >= 75) factorCobertura = 16; // Buena cobertura
            else if (porcentajeAtencion >= 65) factorCobertura = 14; // Cobertura aceptable
            else if (porcentajeAtencion >= 50) factorCobertura = 12; // Cobertura regular
            else factorCobertura = 10;                               // Cobertura deficiente
            
            // Factor 3: Actividad del servicio (peso 10%) - basado en el número total de solicitudes
            let factorActividad = 0;
            if (stats.total >= 50) factorActividad = 10;       // Muy activo
            else if (stats.total >= 30) factorActividad = 9;   // Activo
            else if (stats.total >= 20) factorActividad = 8;   // Moderadamente activo
            else if (stats.total >= 10) factorActividad = 7;   // Poco activo
            else if (stats.total >= 5) factorActividad = 6;    // Mínimamente activo
            else factorActividad = 5;                           // Muy poco activo
            
            // Calcular porcentaje final del KPI
            porcentajeKPI = (factorSatisfaccion + factorCobertura + factorActividad).toFixed(1);
            
            // Top performers
            const topDocentes = Object.entries(stats.docentes)
                .sort(([,a], [,b]) => b.total - a.total)
                .slice(0, 5);
                
            const topSecciones = Object.entries(stats.secciones)
                .sort(([,a], [,b]) => b.total - a.total)
                .slice(0, 5);
                
            const topCursos = Object.entries(stats.cursos)
                .sort(([,a], [,b]) => b.total - a.total)
                .slice(0, 5);

            // Distribución por tipo de impresión
            const porcBN = ((stats.blancoNegro / totalImpresiones) * 100).toFixed(1);
            const porcColor = ((stats.color / totalImpresiones) * 100).toFixed(1);
            const porcDoble = ((stats.dobleCarta / totalImpresiones) * 100).toFixed(1);

            // Generar análisis detallado
            const analysisText = `Análisis completo de ${stats.total} solicitudes de fotocopias procesadas. ` +
                `Total de impresiones: ${totalImpresiones.toLocaleString()} (B/N: ${stats.blancoNegro.toLocaleString()}, ` +
                `Color: ${stats.color.toLocaleString()}, Doble Carta: ${stats.dobleCarta.toLocaleString()}). ` +
                `Porcentaje KPI: ${porcentajeKPI}% (Satisfacción: ${porcentajeSatisfaccion}%, Cobertura: ${porcentajeAtencion.toFixed(1)}%). ` +
                `Top docente: ${topDocentes[0] ? topDocentes[0][0] : 'N/A'} con ${topDocentes[0] ? topDocentes[0][1].total.toLocaleString() : '0'} impresiones. ` +
                `Sección más activa: ${topSecciones[0] ? topSecciones[0][0] : 'N/A'} con ${topSecciones[0] ? topSecciones[0][1].total.toLocaleString() : '0'} impresiones. ` +
                `Curso más solicitado: ${topCursos[0] ? topCursos[0][0] : 'N/A'} con ${topCursos[0] ? topCursos[0][1].total.toLocaleString() : '0'} impresiones.`;

            // Mostrar resultados completos
            Swal.fire({
                title: 'Análisis Completo del Servicio de Fotocopias',
                html: `
                    <div class="analysis-container" style="max-height: 600px; overflow-y: auto;">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="satisfaction-score">
                                    <h5 class="text-center mb-3">Porcentaje KPI Calculado</h5>
                                    <div class="progress mb-2" style="height: 35px;">
                                        <div class="progress-bar bg-${getColorClass(porcentajeKPI)}" 
                                            role="progressbar" 
                                            style="width: ${porcentajeKPI}%"
                                            aria-valuenow="${porcentajeKPI}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            <strong>${porcentajeKPI}%</strong>
                                        </div>
                                    </div>
                                    <p class="text-muted text-center">${getPerformanceLabel(porcentajeKPI)}</p>
                                    <div class="small text-center">
                                        <span class="badge badge-info">Este será el porcentaje aplicado al KPI</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="satisfaction-score">
                                    <h5 class="text-center mb-3">Satisfacción del Servicio</h5>
                                    <div class="progress mb-2" style="height: 35px;">
                                        <div class="progress-bar bg-${getColorClass(porcentajeSatisfaccion)}" 
                                            role="progressbar" 
                                            style="width: ${porcentajeSatisfaccion}%"
                                            aria-valuenow="${porcentajeSatisfaccion}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            ${porcentajeSatisfaccion}%
                                        </div>
                                    </div>
                                    <p class="text-muted text-center">Basado en recepción satisfactoria</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border">
                            <h6 class="font-weight-bold mb-2"><i class="fas fa-calculator mr-2"></i>Componentes del KPI:</h6>
                            <div class="row small">
                                <div class="col-6">
                                    <span class="text-muted">• Satisfacción (70%):</span> <strong>${factorSatisfaccion.toFixed(1)}pts</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">• Cobertura (20%):</span> <strong>${factorCobertura.toFixed(1)}pts</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">• Actividad (10%):</span> <strong>${factorActividad.toFixed(1)}pts</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">• Total KPI:</span> <strong>${porcentajeKPI}%</strong>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="small text-muted">
                                <strong>Criterio de satisfacción:</strong> Se considera satisfactorio cuando no está "pendiente" o contiene "sí", "si", "recibido", etc.
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white py-2">
                                        <h6 class="mb-0"><i class="fas fa-chart-pie mr-2"></i>Distribución de Impresiones</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <span class="small">Blanco y Negro (${porcBN}%)</span>
                                            <div class="progress" style="height: 18px;">
                                                <div class="progress-bar bg-secondary" style="width: ${porcBN}%">${stats.blancoNegro.toLocaleString()}</div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <span class="small">Color (${porcColor}%)</span>
                                            <div class="progress" style="height: 18px;">
                                                <div class="progress-bar bg-success" style="width: ${porcColor}%">${stats.color.toLocaleString()}</div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <span class="small">Doble Carta (${porcDoble}%)</span>
                                            <div class="progress" style="height: 18px;">
                                                <div class="progress-bar bg-info" style="width: ${porcDoble}%">${stats.dobleCarta.toLocaleString()}</div>
                                            </div>
                                        </div>
                                        <hr>
                                        <strong>Total: ${totalImpresiones.toLocaleString()} impresiones</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white py-2">
                                        <h6 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Estadísticas Generales</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr><td>📋 Total de solicitudes:</td><td><strong>${stats.total}</strong></td></tr>
                                            <tr><td>✅ Solicitudes satisfactorias:</td><td><strong>${stats.satisfaccion.si}</strong></td></tr>
                                            <tr><td>❌ Solicitudes no satisfactorias:</td><td><strong>${stats.satisfaccion.no}</strong></td></tr>
                                            <tr><td>👥 Docentes únicos:</td><td><strong>${Object.keys(stats.docentes).length}</strong></td></tr>
                                            <tr><td>🏫 Secciones activas:</td><td><strong>${Object.keys(stats.secciones).length}</strong></td></tr>
                                            <tr><td>📚 Cursos diferentes:</td><td><strong>${Object.keys(stats.cursos).length}</strong></td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark py-2">
                                        <h6 class="mb-0"><i class="fas fa-user-graduate mr-2"></i>Top 5 Docentes</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        ${topDocentes.map(([nombre, datos], index) => `
                                            <div class="d-flex justify-content-between align-items-center py-1 ${index < 3 ? 'border-bottom' : ''}">
                                                <span class="small" title="${nombre}">
                                                    <span class="badge badge-${index === 0 ? 'warning' : index === 1 ? 'secondary' : index === 2 ? 'dark' : 'light'} mr-1">${index + 1}</span>
                                                    ${nombre.length > 15 ? nombre.substring(0, 15) + '...' : nombre}
                                                </span>
                                                <span class="small font-weight-bold">${datos.total.toLocaleString()}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white py-2">
                                        <h6 class="mb-0"><i class="fas fa-building mr-2"></i>Top 5 Secciones</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        ${topSecciones.map(([nombre, datos], index) => `
                                            <div class="d-flex justify-content-between align-items-center py-1 ${index < 3 ? 'border-bottom' : ''}">
                                                <span class="small">
                                                    <span class="badge badge-${index === 0 ? 'warning' : index === 1 ? 'secondary' : index === 2 ? 'dark' : 'light'} mr-1">${index + 1}</span>
                                                    ${nombre}
                                                </span>
                                                <span class="small font-weight-bold">${datos.total.toLocaleString()}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-danger text-white py-2">
                                        <h6 class="mb-0"><i class="fas fa-book mr-2"></i>Top 5 Cursos</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        ${topCursos.map(([nombre, datos], index) => `
                                            <div class="d-flex justify-content-between align-items-center py-1 ${index < 3 ? 'border-bottom' : ''}">
                                                <span class="small" title="${nombre}">
                                                    <span class="badge badge-${index === 0 ? 'warning' : index === 1 ? 'secondary' : index === 2 ? 'dark' : 'light'} mr-1">${index + 1}</span>
                                                    ${nombre.length > 12 ? nombre.substring(0, 12) + '...' : nombre}
                                                </span>
                                                <span class="small font-weight-bold">${datos.total.toLocaleString()}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-dark text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i>Distribución Mensual</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="row">
                                    ${Object.entries(stats.meses).map(([mes, datos]) => {
                                        const satisfaccionMes = ((datos.satisfecho / datos.solicitudes) * 100).toFixed(1);
                                        return `
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <div class="border rounded p-2">
                                                    <div class="d-flex justify-content-between">
                                                        <strong class="small">${mes}</strong>
                                                        <span class="small badge badge-${getColorClass(satisfaccionMes)}">${satisfaccionMes}%</span>
                                                    </div>
                                                    <div class="small text-muted">
                                                        ${datos.total.toLocaleString()} impresiones • ${datos.solicitudes} solicitudes
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    }).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Aplicar Resultados al KPI',
                cancelButtonText: 'Cerrar Análisis',
                confirmButtonColor: '#364E76',
                cancelButtonColor: '#6c757d',
                width: '1000px'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#percentage').val(porcentajeKPI);
                    $('#analysis').val(analysisText);
                    $('#surveyDataModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Resultados Aplicados',
                        html: `
                            <div class="text-center">
                                <p>Los resultados del análisis han sido aplicados al KPI</p>
                                <div class="alert alert-info mt-2">
                                    <strong>Porcentaje Alcanzado: ${porcentajeKPI}%</strong>
                                    <br><small class="text-muted">Basado en satisfacción, volumen, diversidad y consistencia</small>
                                </div>
                            </div>
                        `,
                        confirmButtonColor: '#364E76',
                        timer: 3000
                    });
                }
            });

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error en el Análisis',
                text: error.message || 'Error al procesar los datos de fotocopias. Verifique el formato de los datos.',
                confirmButtonColor: '#364E76'
            });
        }
    });
});
</script>
@stop