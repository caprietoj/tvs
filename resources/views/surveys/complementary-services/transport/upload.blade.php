@extends('adminlte::page')

@section('title', 'Cargar Encuestas - Servicios Complementarios')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-upload text-info"></i> Cargar Encuestas de Servicios Complementarios</h1>
        <a href="{{ route('surveys.complementary-services.transport.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
@stop

@section('content')
    <!-- Overlay de carga -->
    <div class="loading-overlay" id="loadingOverlay">
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

    <!-- Instrucciones Mejoradas -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Guía de Carga Rápida</h3>
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
                                    <span class="info-box-text">Carga Múltiple</span>
                                    <span class="info-box-number text-sm">Hasta 5 archivos</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Arrastra y suelta archivos</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Organización</span>
                                    <span class="info-box-number text-sm">Por período</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Año - Mes automático</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Carga Principal Mejorado -->
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

                        <!-- Zona de Drag & Drop Mejorada -->
                        <div class="drop-zone" id="dropZone">
                            <div class="drop-zone-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Arrastra archivos aquí o haz clic para seleccionar</h4>
                                <p class="text-muted">
                                    Puedes cargar hasta 5 archivos Excel simultáneamente<br>
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
                    <a href="{{ route('surveys.complementary-services.transport.index') }}" class="btn btn-success ml-2">
                        <i class="fas fa-chart-line"></i> Ver Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Documentación y Plantilla Mejorada -->
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
                                    <li>Timestamp (fecha y hora)</li>
                                    <li>Dependencia</li>
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
                                    <li><strong>Satisfacción:</strong> Excelente, Bueno, Regular, Malo</li>
                                    <li><strong>Conformidad:</strong> Sí, No, Algunas veces</li>
                                    <li><strong>Limpieza:</strong> Limpio y ordenado, Necesita mejoras</li>
                                    <li><strong>Uso de servicios:</strong> "Si." (con punto)</li>
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
                        <button type="button" class="btn btn-info btn-block" onclick="showSampleData()">
                            <i class="fas fa-eye"></i> Ver Datos de Ejemplo
                        </button>
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

            <!-- Estadísticas de archivos cargados -->
            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Últimas Cargas</h3>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="row">
                            <div class="col-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-success">
                                        <i class="fas fa-caret-up"></i> 0%
                                    </span>
                                    <h5 class="description-header">0</h5>
                                    <span class="description-text">ARCHIVOS HOY</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <span class="description-percentage text-info">
                                        <i class="fas fa-caret-up"></i> 0%
                                    </span>
                                    <h5 class="description-header">0</h5>
                                    <span class="description-text">ESTE MES</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    /* Estilos mejorados para mejor UX */
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.1);
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .btn {
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    /* Zona de Drag & Drop */
    .drop-zone {
        border: 3px dashed #dee2e6;
        border-radius: 15px;
        padding: 60px 20px;
        text-align: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .drop-zone::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(0,123,255,0.1) 0%, rgba(40,167,69,0.1) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .drop-zone:hover::before {
        opacity: 1;
    }
    
    .drop-zone:hover {
        border-color: #007bff;
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0,123,255,0.2);
    }
    
    .drop-zone.drag-over {
        border-color: #28a745;
        background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
        transform: scale(1.02);
    }
    
    .drop-zone-content {
        position: relative;
        z-index: 1;
    }
    
    /* Lista de archivos */
    .file-item {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .file-item:hover {
        border-color: #007bff;
        box-shadow: 0 4px 20px rgba(0,123,255,0.15);
        transform: translateY(-2px);
    }
    
    .file-item.processing {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-color: #ffc107;
    }
    
    .file-item.success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-color: #28a745;
    }
    
    .file-item.error {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border-color: #dc3545;
    }
    
    .file-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .file-details {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .file-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    
    .file-meta {
        flex: 1;
    }
    
    .file-progress {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 10px;
    }
    
    .file-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
        border-radius: 4px;
        transition: width 0.3s ease;
    }
    
    /* Overlay de carga */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }
    
    .loading-overlay.show {
        display: flex;
    }
    
    /* Animaciones */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .slide-in-up {
        animation: slideInUp 0.5s ease-out;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .drop-zone {
            padding: 40px 15px;
        }
        
        .file-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .file-info {
            flex-direction: column;
            align-items: flex-start;
        }
    }
    
    /* Info boxes mejoradas */
    .info-box {
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .info-box:hover {
        transform: translateY(-3px);
    }
    
    /* Botones de estado */
    .btn-file-action {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 15px;
    }
    
    /* Badges mejorados */
    .badge {
        border-radius: 12px;
        padding: 6px 12px;
        font-weight: 500;
    }
    
    /* Formularios */
    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
</style>
@stop

@section('js')
<script>
// Variables globales - declaradas fuera del DOMContentLoaded para acceso global
let selectedFiles = [];
let isUploading = false;

// Variables de elementos del DOM - se inicializarán cuando el DOM esté listo
let dropZone, fileInput, selectFilesBtn, filesList, filesContainer, actionControls, uploadBtn, fileCount, clearAllBtn, addMoreBtn, loadingOverlay, uploadProgress, uploadResults;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar elementos del DOM
    dropZone = document.getElementById('dropZone');
    fileInput = document.getElementById('fileInput');
    selectFilesBtn = document.getElementById('selectFilesBtn');
    filesList = document.getElementById('filesList');
    filesContainer = document.getElementById('filesContainer');
    actionControls = document.getElementById('actionControls');
    uploadBtn = document.getElementById('uploadBtn');
    fileCount = document.getElementById('fileCount');
    clearAllBtn = document.getElementById('clearAllBtn');
    addMoreBtn = document.getElementById('addMoreBtn');
    loadingOverlay = document.getElementById('loadingOverlay');
    uploadProgress = document.getElementById('uploadProgress');
    uploadResults = document.getElementById('uploadResults');
    
    // Configurar eventos de drag & drop
    setupDragAndDrop();
    
    // Configurar eventos de botones
    selectFilesBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileSelection);
    uploadBtn.addEventListener('click', handleUpload);
    clearAllBtn.addEventListener('click', clearAllFiles);
    addMoreBtn.addEventListener('click', () => fileInput.click());
    
    function setupDragAndDrop() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        dropZone.addEventListener('drop', handleDrop, false);
    }
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        dropZone.classList.add('drag-over');
    }
    
    function unhighlight() {
        dropZone.classList.remove('drag-over');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    function handleFileSelection(e) {
        handleFiles(e.target.files);
    }
    
    function handleFiles(files) {
        if (files.length === 0) return;
        
        // Verificar límite de archivos
        if (selectedFiles.length + files.length > 5) {
            showNotification('Solo puedes cargar hasta 5 archivos a la vez', 'warning');
            return;
        }
        
        for (let file of files) {
            if (validateFile(file)) {
                addFileToList(file);
            }
        }
        
        updateUI();
    }
    
    function validateFile(file) {
        // Verificar tipo de archivo
        const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                             'application/vnd.ms-excel'];
        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
            showNotification(`El archivo "${file.name}" no es un formato Excel válido`, 'error');
            return false;
        }
        
        // Verificar tamaño
        if (file.size > 10 * 1024 * 1024) {
            showNotification(`El archivo "${file.name}" es demasiado grande (máx. 10MB)`, 'error');
            return false;
        }
        
        // Verificar duplicados
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
            description: '',
            status: 'pending'
        };
        
        selectedFiles.push(fileObj);
        renderFileItem(fileObj);
    }
    
    function renderFileItem(fileObj) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item slide-in-up';
        fileItem.setAttribute('data-file-id', fileObj.id);
        
        fileItem.innerHTML = `
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
                <div class="col-md-4">
                    <div class="form-group mb-2">
                        <label class="text-sm">Período:</label>
                        <input type="text" class="form-control form-control-sm period-preview" 
                               value="${fileObj.year && fileObj.month ? fileObj.year + '-' + fileObj.month : 'Seleccione año y mes'}" 
                               readonly style="background-color: ${fileObj.year && fileObj.month ? '#e8f5e8' : '#fff3cd'}; color: ${fileObj.year && fileObj.month ? '#155724' : '#856404'};">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="text-sm">Estado:</label>
                        <span class="badge badge-secondary file-status-badge">Pendiente</span>
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
        `;
        
        filesContainer.appendChild(fileItem);
    }
    
    function generateYearOptions(selectedYear) {
        let options = '<option value="">Seleccionar año</option>';
        const currentYear = new Date().getFullYear();
        for (let year = currentYear; year >= 2020; year--) {
            // Asegurar que la comparación sea number to number
            const selected = year === Number(selectedYear) ? 'selected' : '';
            options += `<option value="${year}" ${selected}>${year}</option>`;
        }
        return options;
    }
    
    function generateMonthOptions(selectedMonth) {
        const months = [
            { value: '01', name: 'Enero' },
            { value: '02', name: 'Febrero' },
            { value: '03', name: 'Marzo' },
            { value: '04', name: 'Abril' },
            { value: '05', name: 'Mayo' },
            { value: '06', name: 'Junio' },
            { value: '07', name: 'Julio' },
            { value: '08', name: 'Agosto' },
            { value: '09', name: 'Septiembre' },
            { value: '10', name: 'Octubre' },
            { value: '11', name: 'Noviembre' },
            { value: '12', name: 'Diciembre' }
        ];
        
        let options = '<option value="">Seleccionar mes</option>';
        months.forEach(month => {
            // Asegurar que la comparación sea string to string
            const selected = month.value === String(selectedMonth) ? 'selected' : '';
            options += `<option value="${month.value}" ${selected}>${month.name}</option>`;
        });
        return options;
    }
    
    async function handleUpload() {
        if (selectedFiles.length === 0 || isUploading) return;
        
        // Validar que todos los archivos tengan año y mes
        const invalidFiles = selectedFiles.filter(f => !f.year || !f.month);
        if (invalidFiles.length > 0) {
            showNotification('Por favor, selecciona año y mes para todos los archivos', 'error');
            return;
        }
        
        isUploading = true;
        loadingOverlay.classList.add('show');
        uploadProgress.style.display = 'block';
        
        try {
            // Procesar archivos uno por uno
            for (let i = 0; i < selectedFiles.length; i++) {
                const fileObj = selectedFiles[i];
                await uploadSingleFile(fileObj, i + 1, selectedFiles.length);
            }
            
            // Mostrar resultados
            showUploadResults();
            
        } catch (error) {
            console.error('Error durante la carga:', error);
            showNotification('Error durante la carga de archivos', 'error');
        } finally {
            isUploading = false;
            loadingOverlay.classList.remove('show');
            uploadProgress.style.display = 'none';
        }
    }
    
    async function uploadSingleFile(fileObj, current, total) {
        updateFileStatus(fileObj.id, 'processing', 'Procesando...');
        updateProgress(current, total, `Procesando archivo ${current} de ${total}: ${fileObj.file.name}`);
        
        const formData = new FormData();
        formData.append('survey_file', fileObj.file);
        formData.append('year', fileObj.year);
        formData.append('month', fileObj.month);
        formData.append('description', fileObj.description);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        try {
            const response = await fetch('{{ route("surveys.complementary-services.transport.process-upload") }}', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                updateFileStatus(fileObj.id, 'success', 'Cargado exitosamente');
                fileObj.result = result;
            } else {
                throw new Error(result.message || 'Error en el servidor');
            }
            
        } catch (error) {
            updateFileStatus(fileObj.id, 'error', error.message);
            fileObj.error = error.message;
        }
    }
    
    function updateFileStatus(fileId, status, message) {
        const fileItem = document.querySelector(`[data-file-id="${fileId}"]`);
        if (!fileItem) return;
        
        const statusElement = fileItem.querySelector('.file-status');
        const badgeElement = fileItem.querySelector('.file-status-badge');
        const progressBar = fileItem.querySelector('.file-progress');
        
        // Actualizar clases del elemento
        fileItem.className = `file-item ${status}`;
        
        // Actualizar texto de estado
        statusElement.textContent = message;
        
        // Actualizar badge
        badgeElement.className = `badge file-status-badge ${getBadgeClass(status)}`;
        badgeElement.textContent = getStatusText(status);
        
        // Mostrar/ocultar barra de progreso
        if (status === 'processing') {
            progressBar.style.display = 'block';
            animateProgress(progressBar.querySelector('.file-progress-bar'));
        } else {
            progressBar.style.display = 'none';
        }
    }
    
    function getBadgeClass(status) {
        const classes = {
            'pending': 'badge-secondary',
            'processing': 'badge-warning',
            'success': 'badge-success',
            'error': 'badge-danger'
        };
        return classes[status] || 'badge-secondary';
    }
    
    function getStatusText(status) {
        const texts = {
            'pending': 'Pendiente',
            'processing': 'Procesando',
            'success': 'Éxito',
            'error': 'Error'
        };
        return texts[status] || 'Desconocido';
    }
    
    function animateProgress(progressBar) {
        let width = 0;
        const interval = setInterval(() => {
            width += Math.random() * 10;
            if (width >= 90) {
                width = 90;
                clearInterval(interval);
            }
            progressBar.style.width = width + '%';
        }, 200);
    }
    
    function updateProgress(current, total, text) {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        
        const percentage = (current / total) * 100;
        progressBar.style.width = percentage + '%';
        progressText.textContent = text;
    }
    
    function showUploadResults() {
        const successCount = selectedFiles.filter(f => f.result).length;
        const errorCount = selectedFiles.filter(f => f.error).length;
        
        let resultsHTML = '<div class="row">';
        
        // Resumen general
        resultsHTML += `
            <div class="col-md-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Archivos Cargados</span>
                        <span class="info-box-number">${successCount}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box bg-light">
                    <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Errores</span>
                        <span class="info-box-number">${errorCount}</span>
                    </div>
                </div>
            </div>
        `;
        
        resultsHTML += '</div>';
        
        // Detalles por archivo
        resultsHTML += '<div class="mt-3"><h6>Detalles por archivo:</h6>';
        selectedFiles.forEach(fileObj => {
            const status = fileObj.result ? 'success' : 'error';
            const icon = fileObj.result ? 'fa-check text-success' : 'fa-times text-danger';
            const message = fileObj.result ? fileObj.result.message : fileObj.error;
            
            resultsHTML += `
                <div class="alert alert-${status === 'success' ? 'success' : 'danger'} alert-dismissible">
                    <h6><i class="fas ${icon}"></i> ${fileObj.file.name}</h6>
                    <p class="mb-0">${message}</p>
                    ${fileObj.result ? `<small>Período: ${fileObj.year}-${fileObj.month}</small>` : ''}
                </div>
            `;
        });
        resultsHTML += '</div>';
        
        document.getElementById('resultsContent').innerHTML = resultsHTML;
        uploadResults.style.display = 'block';
        uploadResults.scrollIntoView({ behavior: 'smooth' });
    }
});

// Funciones utilitarias globales
function updateUI() {
    const hasFiles = selectedFiles.length > 0;
    
    filesList.style.display = hasFiles ? 'block' : 'none';
    actionControls.style.display = hasFiles ? 'block' : 'none';
    fileCount.textContent = selectedFiles.length;
    
    // Actualizar estado del botón de carga
    uploadBtn.disabled = selectedFiles.length === 0 || isUploading;
}

function clearAllFiles() {
    selectedFiles = [];
    filesContainer.innerHTML = '';
    updateUI();
    uploadResults.style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showNotification(message, type = 'info') {
    // Crear notificación Toast mejorada
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="${icons[type] || icons.info}"></i> ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

// Funciones globales para los eventos inline
function removeFile(fileId) {
    selectedFiles = selectedFiles.filter(f => f.id !== fileId);
    document.querySelector(`[data-file-id="${fileId}"]`).remove();
    
    // Actualizar UI
    const hasFiles = selectedFiles.length > 0;
    document.getElementById('filesList').style.display = hasFiles ? 'block' : 'none';
    document.getElementById('actionControls').style.display = hasFiles ? 'block' : 'none';
    document.getElementById('fileCount').textContent = selectedFiles.length;
}

function updateFileData(fileId, field, value) {
    const fileObj = selectedFiles.find(f => f.id === fileId);
    if (fileObj) {
        fileObj[field] = value;
        
        // Actualizar preview del período si cambió año o mes
        if (field === 'year' || field === 'month') {
            const fileItem = document.querySelector(`[data-file-id="${fileId}"]`);
            const periodPreview = fileItem.querySelector('.period-preview');
            
            // Solo mostrar período si ambos valores están presentes
            if (fileObj.year && fileObj.month) {
                periodPreview.value = `${fileObj.year}-${fileObj.month}`;
                periodPreview.style.backgroundColor = '#e8f5e8';
                periodPreview.style.color = '#155724';
            } else {
                periodPreview.value = 'Seleccione año y mes';
                periodPreview.style.backgroundColor = '#fff3cd';
                periodPreview.style.color = '#856404';
            }
        }
    }
}

function downloadTemplate() {
    showNotification('Generando plantilla de Excel...', 'info');
    // Aquí se podría implementar la descarga real de la plantilla
    setTimeout(() => {
        showNotification('Funcionalidad de plantilla en desarrollo. Consulte la documentación para el formato correcto.', 'warning');
    }, 1000);
}

function showSampleData() {
    const modal = `
        <div class="modal fade" id="sampleDataModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-table"></i> Ejemplo de Datos</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>timestamp</th>
                                        <th>dependencia</th>
                                        <th>usa_cafeteria</th>
                                        <th>calidad_sabor</th>
                                        <th>usa_transporte</th>
                                        <th>puntualidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2024-07-15 10:30:00</td>
                                        <td>Recursos Humanos</td>
                                        <td>Si.</td>
                                        <td>Excelente</td>
                                        <td>Si.</td>
                                        <td>Sí</td>
                                    </tr>
                                    <tr>
                                        <td>2024-07-15 11:45:00</td>
                                        <td>Sistemas</td>
                                        <td>Si.</td>
                                        <td>Bueno</td>
                                        <td>No.</td>
                                        <td>-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> Esta es solo una muestra. El archivo completo debe incluir todas las columnas especificadas en la documentación.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Agregar modal al DOM si no existe
    if (!document.getElementById('sampleDataModal')) {
        document.body.insertAdjacentHTML('beforeend', modal);
    }
    
    // Mostrar modal
    $('#sampleDataModal').modal('show');
}
</script>
@stop
