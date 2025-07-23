@extends('adminlte::page')

@section('title', 'Subir Encuestas - Padres de Familia')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-upload text-info"></i> Cargar Encuestas de Padres de Familia</h1>
        <a href="{{ route('surveys.parent-student.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@endsection

@section('content')
    <!-- Overlay de carga -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="text-center">
            <div class="spinner-border spinner-border-lg text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <div class="mt-3">
                <h4 class="text-primary">Procesando archivos...</h4>
                <p class="text-muted">Por favor espere mientras procesamos sus encuestas</p>
                <div class="progress mt-3" style="width: 300px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de progreso superior -->
    <div id="uploadProgress" class="row" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Progreso de Carga</h5>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                             id="progressBar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted" id="progressText">Preparando...</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Instrucciones -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Guía de Carga</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-file-excel"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Formatos Soportados</span>
                                    <span class="info-box-number text-sm">Excel (.xlsx, .xls)</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Máximo 10MB por archivo</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success"><i class="fas fa-upload"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Carga Individual</span>
                                    <span class="info-box-number text-sm">Un archivo por vez</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Configuración personalizada</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Organización</span>
                                    <span class="info-box-number text-sm">Por año y mes</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Configurable por archivo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Carga Principal -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cloud-upload-alt"></i> Cargar Archivos de Encuestas
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">Arrastra archivos aquí</span>
                    </div>
                </div>
                <form id="mainUploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-check"></i> Éxito!</h5>
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Zona de Drag & Drop -->
                        <div class="drop-zone" id="dropZone">
                            <div class="drop-zone-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Arrastra archivos aquí o haz clic para seleccionar</h4>
                                <p class="text-muted">
                                    Carga archivos Excel individualmente<br>
                                    <small>Formatos soportados: .xlsx, .xls (máx. 10MB cada uno)</small>
                                </p>
                                <button type="button" class="btn btn-primary btn-lg" id="selectFilesBtn">
                                    <i class="fas fa-folder-open"></i> Seleccionar Archivos
                                </button>
                                <input type="file" id="fileInput" multiple accept=".xlsx,.xls" style="display: none;">
                            </div>
                        </div>

                        <!-- Lista de Archivos Seleccionados -->
                        <div id="filesList" class="mt-4" style="display: none;">
                            <h5><i class="fas fa-list"></i> Archivos Seleccionados</h5>
                            <div id="filesContainer"></div>
                        </div>

                        <!-- Controles de Acción -->
                        <div class="row mt-4" id="actionControls" style="display: none;">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-warning" id="clearAllBtn">
                                    <i class="fas fa-trash-alt"></i> Limpiar Todo
                                </button>
                                <button type="button" class="btn btn-info ml-2" id="addMoreBtn">
                                    <i class="fas fa-plus"></i> Agregar Más
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-success btn-lg" id="uploadBtn">
                                    <i class="fas fa-upload"></i> Cargar Archivos
                                    <span class="badge badge-light ml-2" id="fileCount">0</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Resultados de Carga -->
    <div id="uploadResults" class="row" style="display: none;">
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-circle"></i> Resultados de la Carga</h3>
                </div>
                <div class="card-body" id="resultsContent">
                    <!-- Los resultados se cargarán aquí dinámicamente -->
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-plus"></i> Cargar Más Archivos
                    </button>
                    <a href="{{ route('surveys.parent-student.index') }}" class="btn btn-success ml-2">
                        <i class="fas fa-chart-line"></i> Ver Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Documentación y Ayuda -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-info collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle"></i> Ayuda y Formato del Archivo</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-info"><i class="fas fa-info-circle"></i> Estructura Requerida</h6>
                            <p class="text-sm">El archivo Excel debe contener las siguientes columnas en este orden:</p>
                            
                            <div class="mb-3">
                                <strong>Información General:</strong>
                                <ul class="text-sm">
                                    <li>Estudiante (Columna A)</li>
                                    <li>Fecha (Columna B)</li>
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Preguntas Cafetería:</strong>
                                <ul class="text-sm">
                                    <li>¿Usa cafetería? (Sí/No)</li>
                                    <li>Calidad y sabor</li>
                                    <li>Porción de alimentos</li>
                                    <li>Menú ofrecido</li>
                                    <li>Variedad del menú</li>
                                    <li>Temperatura de comida</li>
                                    <li>Limpieza del comedor</li>
                                    <li>Servicio de tienda</li>
                                    <li>Trato del personal</li>
                                    <li>Aspectos positivos</li>
                                    <li>Oportunidades de mejora</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Preguntas Transporte:</strong>
                                <ul class="text-sm">
                                    <li>¿Usa transporte? (Sí/No)</li>
                                    <li>Número de ruta</li>
                                    <li>Puntualidad</li>
                                    <li>Limpieza del vehículo</li>
                                    <li>Trato del personal</li>
                                    <li>Comunicación</li>
                                    <li>Aspectos positivos</li>
                                    <li>Oportunidades de mejora</li>
                                </ul>
                            </div>

                            <div class="callout callout-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Valores Esperados</h6>
                                <ul class="mb-0 text-sm">
                                    <li><strong>Satisfacción:</strong> 1-5 (numérico)</li>
                                    <li><strong>Uso de servicio:</strong> Sí, No</li>
                                    <li><strong>Comentarios:</strong> Texto libre</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-download"></i> Plantilla y Ejemplos</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-success btn-block" onclick="downloadTemplate()">
                            <i class="fas fa-download"></i> Descargar Plantilla Excel
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <a href="{{ asset('test_simple_encuesta.xlsx') }}" class="btn btn-info btn-block" download>
                            <i class="fas fa-file-excel"></i> Archivo de Prueba
                        </a>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">
                            <strong>Consejos:</strong>
                            <ul class="mt-2 text-sm">
                                <li>La primera fila debe contener los encabezados</li>
                                <li>Evita celdas vacías en campos obligatorios</li>
                                <li>Las fechas se procesan automáticamente</li>
                                <li>Los comentarios pueden contener texto libre</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .drop-zone {
        border: 2px dashed #007bff;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .drop-zone:hover {
        border-color: #0056b3;
        background: #e9ecef;
    }

    .drop-zone.dragover {
        border-color: #28a745;
        background: #d4edda;
    }

    .drop-zone-content {
        max-width: 400px;
        margin: 0 auto;
    }

    .file-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: white;
        transition: all 0.3s ease;
    }

    .file-item:hover {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .file-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .file-details {
        display: flex;
        align-items: center;
    }

    .file-icon {
        font-size: 24px;
        color: #28a745;
        margin-right: 15px;
    }

    .file-meta h6 {
        margin-bottom: 2px;
        font-weight: 600;
    }

    .file-actions button {
        margin-left: 5px;
    }

    .file-progress {
        height: 4px;
        background: #f8f9fa;
        border-radius: 2px;
        overflow: hidden;
        margin-top: 5px;
    }

    .file-progress-bar {
        height: 100%;
        background: #007bff;
        transition: width 0.3s ease;
    }

    .period-preview {
        font-weight: 600;
        text-align: center;
    }

    .slide-in-up {
        animation: slideInUp 0.3s ease-out;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-file-action {
        padding: 4px 8px;
    }

    .file-status-badge {
        font-size: 0.75em;
    }
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    let selectedFiles = [];
    const dropZone = $('#dropZone');
    const fileInput = $('#fileInput');
    const filesList = $('#filesList');
    const filesContainer = $('#filesContainer');
    const actionControls = $('#actionControls');
    const uploadBtn = $('#uploadBtn');
    const fileCount = $('#fileCount');
    const progressBar = $('#progressBar');
    const progressText = $('#progressText');
    const uploadProgress = $('#uploadProgress');
    const loadingOverlay = $('#loadingOverlay');
    const uploadResults = $('#uploadResults');
    const resultsContent = $('#resultsContent');

    // Event listeners
    $('#selectFilesBtn').on('click', function() {
        fileInput.click();
    });

    $('#addMoreBtn').on('click', function() {
        fileInput.click();
    });

    $('#clearAllBtn').on('click', function() {
        selectedFiles = [];
        updateUI();
    });

    fileInput.on('change', function() {
        const files = Array.from(this.files);
        files.forEach(file => {
            if (validateFile(file)) {
                addFileToList(file);
            }
        });
        updateUI();
        this.value = ''; // Reset input
    });

    // Drag and drop
    dropZone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    dropZone.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    dropZone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = Array.from(e.originalEvent.dataTransfer.files);
        files.forEach(file => {
            if (validateFile(file)) {
                addFileToList(file);
            }
        });
        updateUI();
    });

    // Form submission
    $('#mainUploadForm').on('submit', function(e) {
        e.preventDefault();
        
        if (selectedFiles.length === 0) {
            showNotification('Por favor selecciona al menos un archivo', 'warning');
            return;
        }

        // Validate all files have year and month
        const invalidFiles = selectedFiles.filter(f => !f.year || !f.month);
        if (invalidFiles.length > 0) {
            showNotification('Por favor configura el año y mes para todos los archivos', 'warning');
            return;
        }

        uploadFiles();
    });

    function validateFile(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream'
        ];

        if (file.size > maxSize) {
            showNotification(`El archivo "${file.name}" es demasiado grande (máx. 10MB)`, 'error');
            return false;
        }

        const fileName = file.name.toLowerCase();
        if (!fileName.endsWith('.xlsx') && !fileName.endsWith('.xls')) {
            showNotification(`El archivo "${file.name}" no es un formato Excel válido`, 'error');
            return false;
        }

        if (selectedFiles.some(f => f.file.name === file.name && f.file.size === file.size)) {
            showNotification(`El archivo "${file.name}" ya está seleccionado`, 'warning');
            return false;
        }

        return true;
    }

    function addFileToList(file) {
        const fileId = Date.now() + Math.random();
        const currentYear = new Date().getFullYear();
        const currentMonth = String(new Date().getMonth() + 1).padStart(2, '0');

        const fileObj = {
            id: fileId,
            file: file,
            year: currentYear,
            month: currentMonth,
            surveyType: 'complete',
            description: '',
            status: 'pending'
        };

        selectedFiles.push(fileObj);
        renderFileItem(fileObj);
    }

    function renderFileItem(fileObj) {
        const fileItem = $(`
            <div class="file-item slide-in-up" data-file-id="${fileObj.id}">
                <div class="file-info">
                    <div class="file-details">
                        <div class="file-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div class="file-meta">
                            <h6 class="mb-1">${fileObj.file.name}</h6>
                            <small class="text-muted">
                                ${formatFileSize(fileObj.file.size)} • 
                                <span class="file-status">Listo para cargar</span>
                            </small>
                            <div class="file-progress" style="display: none;">
                                <div class="file-progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="file-actions">
                        <button type="button" class="btn btn-danger btn-sm btn-file-action" onclick="removeFile(${fileObj.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-sm">Año:</label>
                            <select class="form-control form-control-sm" onchange="updateFileData(${fileObj.id}, 'year', this.value)">
                                ${generateYearOptions(fileObj.year)}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-sm">Mes:</label>
                            <select class="form-control form-control-sm" onchange="updateFileData(${fileObj.id}, 'month', this.value)">
                                ${generateMonthOptions(fileObj.month)}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-sm">Tipo de Encuesta:</label>
                            <select class="form-control form-control-sm" onchange="updateFileData(${fileObj.id}, 'surveyType', this.value)">
                                <option value="complete" ${fileObj.surveyType === 'complete' ? 'selected' : ''}>Completa</option>
                                <option value="cafeteria_only" ${fileObj.surveyType === 'cafeteria_only' ? 'selected' : ''}>Solo Cafetería</option>
                                <option value="transport_only" ${fileObj.surveyType === 'transport_only' ? 'selected' : ''}>Solo Transporte</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2">
                            <label class="text-sm">Período:</label>
                            <input type="text" class="form-control form-control-sm period-preview" 
                                   value="${fileObj.year && fileObj.month ? fileObj.year + '-' + fileObj.month : 'Seleccione año y mes'}" 
                                   readonly style="background-color: ${fileObj.year && fileObj.month ? '#e8f5e8' : '#fff3cd'}; color: ${fileObj.year && fileObj.month ? '#155724' : '#856404'};">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-0">
                            <label class="text-sm">Descripción (opcional):</label>
                            <input type="text" class="form-control form-control-sm" 
                                   placeholder="Ej: Encuesta primer trimestre, Evaluación proveedor..."
                                   onchange="updateFileData(${fileObj.id}, 'description', this.value)">
                        </div>
                    </div>
                </div>
            </div>
        `);

        filesContainer.append(fileItem);
    }

    function generateYearOptions(selectedYear) {
        let options = '<option value="">Seleccionar...</option>';
        const currentYear = new Date().getFullYear();
        for (let year = currentYear - 2; year <= currentYear + 1; year++) {
            options += `<option value="${year}" ${year == selectedYear ? 'selected' : ''}>${year}</option>`;
        }
        return options;
    }

    function generateMonthOptions(selectedMonth) {
        const months = [
            {value: '01', text: 'Enero'}, {value: '02', text: 'Febrero'}, {value: '03', text: 'Marzo'},
            {value: '04', text: 'Abril'}, {value: '05', text: 'Mayo'}, {value: '06', text: 'Junio'},
            {value: '07', text: 'Julio'}, {value: '08', text: 'Agosto'}, {value: '09', text: 'Septiembre'},
            {value: '10', text: 'Octubre'}, {value: '11', text: 'Noviembre'}, {value: '12', text: 'Diciembre'}
        ];

        let options = '<option value="">Seleccionar...</option>';
        months.forEach(month => {
            options += `<option value="${month.value}" ${month.value == selectedMonth ? 'selected' : ''}>${month.text}</option>`;
        });
        return options;
    }

    function updateUI() {
        if (selectedFiles.length > 0) {
            filesList.show();
            actionControls.show();
            fileCount.text(selectedFiles.length);
        } else {
            filesList.hide();
            actionControls.hide();
            filesContainer.empty();
        }
    }

    function uploadFiles() {
        loadingOverlay.show();
        uploadProgress.show();
        
        let completedFiles = 0;
        let totalFiles = selectedFiles.length;
        let allResults = [];

        selectedFiles.forEach((fileObj, index) => {
            uploadSingleFile(fileObj, index)
                .then(result => {
                    completedFiles++;
                    allResults.push(result);
                    updateGlobalProgress(completedFiles, totalFiles);
                    
                    if (completedFiles === totalFiles) {
                        showResults(allResults);
                    }
                })
                .catch(error => {
                    completedFiles++;
                    allResults.push({
                        file: fileObj.file.name,
                        success: false,
                        message: error.message
                    });
                    updateGlobalProgress(completedFiles, totalFiles);
                    
                    if (completedFiles === totalFiles) {
                        showResults(allResults);
                    }
                });
        });
    }

    function uploadSingleFile(fileObj, index) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('excel_files[]', fileObj.file);
            formData.append('survey_type', fileObj.surveyType);
            formData.append('period', `${fileObj.year}-${fileObj.month}`);
            formData.append('auto_detect_provider', '1');
            formData.append('skip_duplicates', '1');
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: '{{ route("surveys.parent-student.upload.process") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    resolve({
                        file: fileObj.file.name,
                        success: true,
                        data: response.data,
                        message: response.message
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Error al procesar el archivo';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    reject(new Error(errorMessage));
                }
            });
        });
    }

    function updateGlobalProgress(completed, total) {
        const percentage = (completed / total) * 100;
        progressBar.css('width', percentage + '%');
        progressText.text(`${completed} de ${total} archivos procesados`);
    }

    function showResults(results) {
        loadingOverlay.hide();
        uploadProgress.hide();
        
        let successCount = results.filter(r => r.success).length;
        let errorCount = results.filter(r => !r.success).length;
        
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Archivos Exitosos</span>
                            <span class="info-box-number">${successCount}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-times"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Archivos con Error</span>
                            <span class="info-box-number">${errorCount}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        results.forEach(result => {
            const statusClass = result.success ? 'success' : 'danger';
            const icon = result.success ? 'check' : 'times';
            html += `
                <div class="alert alert-${statusClass}">
                    <h6><i class="fas fa-${icon}"></i> ${result.file}</h6>
                    <p class="mb-0">${result.message}</p>
                </div>
            `;
        });

        resultsContent.html(html);
        uploadResults.show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: uploadResults.offset().top - 100
        }, 500);
    }

    function showNotification(message, type) {
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 
                          'alert-info';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `);
        
        $('.card-body').first().prepend(alert);
        
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Global functions
    window.removeFile = function(fileId) {
        selectedFiles = selectedFiles.filter(f => f.id !== fileId);
        $(`[data-file-id="${fileId}"]`).remove();
        updateUI();
    };

    window.updateFileData = function(fileId, field, value) {
        const file = selectedFiles.find(f => f.id === fileId);
        if (file) {
            file[field] = value;
            
            // Update period preview
            if (field === 'year' || field === 'month') {
                const periodInput = $(`[data-file-id="${fileId}"] .period-preview`);
                if (file.year && file.month) {
                    periodInput.val(`${file.year}-${file.month}`)
                             .css({
                                 'background-color': '#e8f5e8',
                                 'color': '#155724'
                             });
                } else {
                    periodInput.val('Seleccione año y mes')
                             .css({
                                 'background-color': '#fff3cd',
                                 'color': '#856404'
                             });
                }
            }
        }
    };

    window.downloadTemplate = function() {
        // Crear un enlace de descarga para la plantilla
        const link = document.createElement('a');
        link.href = '{{ asset("test_simple_encuesta.xlsx") }}';
        link.download = 'plantilla_encuesta_padres.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
});
</script>
@endsection
