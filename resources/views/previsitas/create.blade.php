@extends('adminlte::page')

@section('title', 'Nueva Previsita')

@section('content_header')
    <div class="content-header-modern">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="page-title">
                        <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                        Nueva Previsita
                    </h1>
                    <p class="page-subtitle">Complete la información para crear una nueva previsita</p>
                </div>
                <div class="col-sm-6">
                    <div class="float-sm-right">
                        <a href="{{ route('previsitas.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>¡Atención!</strong> Por favor corrija los siguientes errores:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Información de la Previsita
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('previsitas.store') }}" method="POST" enctype="multipart/form-data" id="previsita-form">
                        @csrf
                        
                        <!-- Sección 1: Información Básica -->
                        <div class="form-section">
                            <div class="section-header">
                                <h4 class="section-title">
                                    <i class="fas fa-info-circle text-primary mr-2"></i>
                                    Información Básica
                                </h4>
                                <p class="section-description">Datos principales de la previsita</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lugar" class="form-label required">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            Lugar <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-modern @error('lugar') is-invalid @enderror" 
                                               id="lugar" 
                                               name="lugar" 
                                               value="{{ old('lugar') }}" 
                                               placeholder="Ej: Museo Nacional, Parque Central..."
                                               required>
                                        @error('lugar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="responsable" class="form-label required">
                                            <i class="fas fa-user mr-1"></i>
                                            Responsable <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-modern @error('responsable') is-invalid @enderror" 
                                               id="responsable" 
                                               name="responsable" 
                                               value="{{ old('responsable') }}" 
                                               placeholder="Ej: Juan Pérez"
                                               required>
                                        @error('responsable')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Fechas -->
                        <div class="form-section">
                            <div class="section-header">
                                <h4 class="section-title">
                                    <i class="fas fa-calendar-alt text-info mr-2"></i>
                                    Fechas Importantes
                                </h4>
                                <p class="section-description">Fechas de visita y vencimientos</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_visita" class="form-label required">
                                            <i class="fas fa-calendar-check mr-1"></i>
                                            Fecha de Visita <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control form-control-modern @error('fecha_visita') is-invalid @enderror" 
                                               id="fecha_visita" 
                                               name="fecha_visita" 
                                               value="{{ old('fecha_visita') }}"
                                               required>
                                        @error('fecha_visita')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vencimiento" class="form-label">
                                            <i class="fas fa-calendar-times mr-1"></i>
                                            Vencimiento
                                        </label>
                                        <input type="date" 
                                               class="form-control form-control-modern @error('vencimiento') is-invalid @enderror" 
                                               id="vencimiento" 
                                               name="vencimiento" 
                                               value="{{ old('vencimiento') }}">
                                        @error('vencimiento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Opcional para museos y entidades gubernamentales
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Aprobación -->
                        <div class="form-section">
                            <div class="section-header">
                                <h4 class="section-title">
                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                    Estado de Aprobación
                                </h4>
                                <p class="section-description">Confirme si el sitio ha sido aprobado</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aprobacion_sitio" class="form-label required">
                                            <i class="fas fa-thumbs-up mr-1"></i>
                                            Aprobación del Sitio <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control form-control-modern @error('aprobacion_sitio') is-invalid @enderror" 
                                                id="aprobacion_sitio" 
                                                name="aprobacion_sitio" 
                                                required>
                                            <option value="">Seleccione una opción</option>
                                            <option value="1" {{ old('aprobacion_sitio') == '1' ? 'selected' : '' }}>
                                                ✅ Sí - Aprobado
                                            </option>
                                            <option value="0" {{ old('aprobacion_sitio') == '0' ? 'selected' : '' }}>
                                                ❌ No - Pendiente
                                            </option>
                                        </select>
                                        @error('aprobacion_sitio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Archivos -->
                        <div class="form-section">
                            <div class="section-header">
                                <h4 class="section-title">
                                    <i class="fas fa-paperclip text-warning mr-2"></i>
                                    Archivos de Novedades
                                </h4>
                                <p class="section-description">Suba documentos PDF e imágenes relacionadas</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="archivos_novedades" class="form-label">
                                            <i class="fas fa-cloud-upload-alt mr-1"></i>
                                            Archivos (PDF, Imágenes y Documentos Word)
                                        </label>
                                        
                                        <div class="file-upload-area" id="file-upload-area">
                                            <div class="file-upload-content">
                                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                                <h5>Arrastra archivos aquí o haz clic para seleccionar</h5>
                                                <p class="text-muted">
                                                    Formatos permitidos: PDF, JPG, PNG, GIF, BMP, WEBP, DOC, DOCX<br>
                                                    Tamaño máximo: 1GB por archivo<br>
                                                    Máximo 100 archivos por previsita
                                                </p>
                                                <input type="file" 
                                                       class="file-input @error('archivos_novedades') is-invalid @enderror" 
                                                       id="archivos_novedades" 
                                                       name="archivos_novedades[]"
                                                       accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp,.doc,.docx"
                                                       multiple
                                                       style="display: none;">
                                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('archivos_novedades').click()">
                                                    <i class="fas fa-folder-open mr-1"></i>
                                                    Seleccionar Archivos
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div id="archivos-seleccionados" class="selected-files-container mt-3"></div>
                                        
                                        @error('archivos_novedades')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('archivos_novedades.*')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 5: Observaciones -->
                        <div class="form-section">
                            <div class="section-header">
                                <h4 class="section-title">
                                    <i class="fas fa-comments text-secondary mr-2"></i>
                                    Observaciones y Recomendaciones
                                </h4>
                                <p class="section-description">Información adicional importante</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="observaciones_recomendaciones" class="form-label">
                                            <i class="fas fa-sticky-note mr-1"></i>
                                            Observaciones y Recomendaciones
                                        </label>
                                        <textarea class="form-control form-control-modern @error('observaciones_recomendaciones') is-invalid @enderror" 
                                                  id="observaciones_recomendaciones" 
                                                  name="observaciones_recomendaciones" 
                                                  rows="5" 
                                                  placeholder="Describa observaciones importantes, recomendaciones, medidas de seguridad, etc...">{{ old('observaciones_recomendaciones') }}</textarea>
                                        @error('observaciones_recomendaciones')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="action-buttons">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save mr-2"></i>
                                            Guardar Previsita
                                        </button>
                                        <a href="{{ route('previsitas.index') }}" class="btn btn-outline-secondary btn-lg">
                                            <i class="fas fa-times mr-2"></i>
                                            Cancelar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Header Moderno Sin Degradado */
    .content-header-modern {
        background: #f8f9fa;
        border-bottom: 3px solid #007bff;
        margin: -15px -15px 20px -15px;
        padding: 30px 15px;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: #343a40;
    }
    
    .page-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 0;
    }
    
    /* Card Principal */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-bottom: 1px solid #dee2e6;
        padding: 1.5rem;
    }
    
    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0;
    }
    
    /* Secciones del Formulario */
    .form-section {
        margin-bottom: 2.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 12px;
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    
    .form-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .section-header {
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 0.5rem;
    }
    
    .section-description {
        color: #6c757d;
        font-size: 0.95rem;
        margin-bottom: 0;
    }
    
    /* Formulario Moderno */
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
    }
    
    .form-control-modern {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: white;
    }
    
    .form-control-modern:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        transform: translateY(-1px);
    }
    
    .form-control-modern:hover {
        border-color: #007bff;
    }
    
    /* Área de Subida de Archivos */
    .file-upload-area {
        border: 3px dashed #007bff;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: linear-gradient(45deg, #f8f9ff, #ffffff);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .file-upload-area:hover {
        border-color: #0056b3;
        background: linear-gradient(45deg, #e3f2fd, #f8f9ff);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,123,255,0.2);
    }
    
    .file-upload-area.dragover {
        border-color: #28a745;
        background: linear-gradient(45deg, #f0fff4, #f8fff9);
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    .file-upload-content {
        pointer-events: none;
    }
    
    .file-upload-icon {
        font-size: 3rem;
        color: #007bff;
        margin-bottom: 1rem;
        display: block;
    }
    
    .file-upload-area h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    /* Archivos Seleccionados */
    .selected-files-container {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }
    
    .archivo-item {
        background: linear-gradient(45deg, #f8f9fa, #ffffff);
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .archivo-item:hover {
        transform: translateX(5px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        border-color: #007bff;
    }
    
    .archivo-item:last-child {
        margin-bottom: 0;
    }
    
    .archivo-info {
        display: flex;
        align-items: center;
    }
    
    .archivo-icon {
        font-size: 2rem;
        margin-right: 1rem;
        width: 40px;
        text-align: center;
    }
    
    .archivo-details h6 {
        margin-bottom: 0.25rem;
        font-weight: 600;
        color: #495057;
    }
    
    .archivo-size {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .remove-file {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: #dc3545;
        border: none;
        color: white;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .remove-file:hover {
        background: #c82333;
        transform: scale(1.1);
    }
    
    /* Botones de Acción */
    .form-actions {
        margin-top: 3rem;
        padding: 2rem;
        background: linear-gradient(45deg, #f8f9fa, #ffffff);
        border-radius: 12px;
        border: 1px solid #e9ecef;
    }
    
    .action-buttons {
        text-align: center;
    }
    
    .btn-lg {
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        margin: 0 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        box-shadow: 0 4px 15px rgba(0,123,255,0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,123,255,0.4);
    }
    
    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(108,117,125,0.3);
    }
    
    /* Iconos y Badges */
    .required .text-danger {
        font-weight: bold;
        margin-left: 0.25rem;
    }
    
    .form-text {
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    /* Alertas Mejoradas */
    .alert {
        border-radius: 10px;
        border: none;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .alert-danger {
        background: linear-gradient(45deg, #f8d7da, #f1c0c3);
        color: #721c24;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .content-header-modern {
            margin: -15px -15px 15px -15px;
            padding: 20px 15px;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .form-section {
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .file-upload-area {
            padding: 1.5rem;
        }
        
        .btn-lg {
            display: block;
            width: 100%;
            margin: 0.5rem 0;
        }
        
        .action-buttons .btn-lg:first-child {
            margin-top: 0;
        }
    }
    
    /* Animaciones de Entrada */
    .form-section {
        animation: slideInUp 0.6s ease-out;
    }
    
    @keyframes slideInUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    /* Estados de Validación Mejorados */
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220,53,69,0.25) !important;
    }
    
    .invalid-feedback {
        display: block !important;
        font-weight: 500;
        margin-top: 0.5rem;
    }
</style>
@section('js')
<script>
    $(document).ready(function() {
        // Variables globales
        let selectedFiles = [];
        const maxFileSize = 1073741824; // 1GB
        const allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'application/msword', // Archivos .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // Archivos .docx
        ];
        
        // Inicializar drag and drop
        initializeDragAndDrop();
        
        // Manejar cambio de archivos
        $('#archivos_novedades').on('change', function() {
            handleFileSelection(this.files);
        });
        
        // Validación de fechas
        $('#fecha_visita, #vencimiento').on('change', validateDates);
        
        // Validación en tiempo real
        $('#previsita-form input, #previsita-form select, #previsita-form textarea').on('blur', function() {
            validateField($(this));
        });
        
        // Funciones principales
        function initializeDragAndDrop() {
            const dropArea = $('#file-upload-area')[0];
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });
            
            dropArea.addEventListener('drop', handleDrop, false);
            dropArea.addEventListener('click', () => $('#archivos_novedades').click());
        }
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight() {
            $('#file-upload-area').addClass('dragover');
        }
        
        function unhighlight() {
            $('#file-upload-area').removeClass('dragover');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFileSelection(files);
        }
        
        function handleFileSelection(files) {
            const container = $('#archivos-seleccionados');
            selectedFiles = []; // Reset
            container.empty();
            
            if (files.length === 0) {
                updateUploadAreaText();
                return;
            }
            
            // Validar límite de archivos
            if (files.length > 100) {
                showFileError('No se pueden seleccionar más de 100 archivos a la vez.', 'limit');
                updateUploadAreaText();
                return;
            }
            
            // Mostrar título de archivos seleccionados
            container.append(`
                <div class="mb-3">
                    <h6 class="text-primary">
                        <i class="fas fa-paperclip mr-2"></i>
                        Archivos Seleccionados (${files.length})
                    </h6>
                </div>
            `);
            
            Array.from(files).forEach((file, index) => {
                if (validateFile(file)) {
                    selectedFiles.push(file);
                    addFileToDisplay(file, index);
                }
            });
            
            updateUploadAreaText();
        }
        
        function validateFile(file) {
            // Validar tipo
            if (!allowedTypes.includes(file.type)) {
                showFileError(`El archivo "${file.name}" no es un tipo válido.`, 'type');
                return false;
            }
            
            // Validar tamaño
            if (file.size > maxFileSize) {
                showFileError(`El archivo "${file.name}" supera el límite de 1GB.`, 'size');
                return false;
            }
            
            return true;
        }
        
        function addFileToDisplay(file, index) {
            let fileType, icon;
            
            if (file.type.startsWith('image/')) {
                fileType = 'image';
                icon = 'fas fa-image text-success';
            } else if (file.type === 'application/pdf') {
                fileType = 'pdf';
                icon = 'fas fa-file-pdf text-danger';
            } else if (file.type === 'application/msword' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                fileType = 'word';
                icon = 'fas fa-file-word text-primary';
            } else {
                fileType = 'file';
                icon = 'fas fa-file text-secondary';
            }
            
            const sizeFormatted = formatFileSize(file.size);
            
            const fileElement = $(`
                <div class="archivo-item" data-index="${index}">
                    <div class="archivo-info">
                        <div class="archivo-icon">
                            <i class="${icon}"></i>
                        </div>
                        <div class="archivo-details flex-grow-1">
                            <h6 class="mb-1">${escapeHtml(file.name)}</h6>
                            <div class="archivo-size">${sizeFormatted}</div>
                        </div>
                    </div>
                    <button type="button" class="remove-file" data-index="${index}" title="Eliminar archivo">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            
            $('#archivos-seleccionados').append(fileElement);
        }
        
        function updateUploadAreaText() {
            const uploadArea = $('#file-upload-area');
            const content = uploadArea.find('.file-upload-content');
            
            if (selectedFiles.length > 0) {
                content.find('h5').text(`${selectedFiles.length} archivo(s) seleccionado(s)`);
                content.find('p').html(`
                    <i class="fas fa-check-circle text-success mr-1"></i>
                    Haz clic para agregar más archivos o arrastra nuevos archivos aquí
                `);
                uploadArea.addClass('has-files');
            } else {
                content.find('h5').text('Arrastra archivos aquí o haz clic para seleccionar');
                content.find('p').html(`
                    Formatos permitidos: PDF, JPG, PNG, GIF, BMP, WEBP, DOC, DOCX<br>
                    Tamaño máximo: 1GB por archivo<br>
                    Máximo 100 archivos por previsita
                `);
                uploadArea.removeClass('has-files');
            }
        }
        
        // Remover archivo
        $(document).on('click', '.remove-file', function() {
            const index = $(this).data('index');
            $(this).closest('.archivo-item').fadeOut(300, function() {
                $(this).remove();
                // Reindexar elementos restantes
                updateFileIndices();
            });
        });
        
        function updateFileIndices() {
            $('.archivo-item').each(function(newIndex) {
                $(this).attr('data-index', newIndex);
                $(this).find('.remove-file').attr('data-index', newIndex);
            });
            
            // Actualizar contador
            const remainingCount = $('.archivo-item').length;
            if (remainingCount > 0) {
                $('#archivos-seleccionados h6').html(`
                    <i class="fas fa-paperclip mr-2"></i>
                    Archivos Seleccionados (${remainingCount})
                `);
            } else {
                $('#archivos-seleccionados').empty();
                $('#archivos_novedades').val('');
                updateUploadAreaText();
            }
        }
        
        function validateDates() {
            const fechaVisita = $('#fecha_visita').val();
            const vencimiento = $('#vencimiento').val();
            
            if (fechaVisita && vencimiento) {
                if (new Date(vencimiento) < new Date(fechaVisita)) {
                    showAlert('El vencimiento debe ser igual o posterior a la fecha de visita.', 'warning');
                    $('#vencimiento').val('').focus();
                }
            }
        }
        
        function validateField($field) {
            const value = $field.val().trim();
            const fieldName = $field.attr('name');
            const isRequired = $field.prop('required');
            
            // Remover clases de error previas
            $field.removeClass('is-invalid is-valid');
            
            if (isRequired && !value) {
                $field.addClass('is-invalid');
                return false;
            } else if (value) {
                $field.addClass('is-valid');
            }
            
            return true;
        }
        
        // Funciones auxiliares
        function formatFileSize(bytes) {
            if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return bytes + ' B';
            }
        }
        
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        function showFileError(message, type) {
            const alertClass = type === 'size' ? 'alert-warning' : 'alert-danger';
            const icon = type === 'size' ? 'fas fa-exclamation-triangle' : 'fas fa-times-circle';
            
            const alert = $(`
                <div class="alert ${alertClass} alert-dismissible fade show mt-2" role="alert">
                    <i class="${icon} mr-2"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
            
            $('#archivos-seleccionados').prepend(alert);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alert.fadeOut();
            }, 5000);
        }
        
        function showAlert(message, type = 'info') {
            const alertClass = `alert-${type}`;
            const iconMap = {
                'success': 'fas fa-check-circle',
                'warning': 'fas fa-exclamation-triangle',
                'danger': 'fas fa-times-circle',
                'info': 'fas fa-info-circle'
            };
            
            const alert = $(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="${iconMap[type]} mr-2"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
            
            $('.container-fluid').prepend(alert);
            
            // Auto-remove after 4 seconds
            setTimeout(() => {
                alert.fadeOut();
            }, 4000);
        }
        
        // Validación del formulario antes del envío
        $('#previsita-form').on('submit', function(e) {
            let isValid = true;
            
            // Validar campos requeridos
            $(this).find('[required]').each(function() {
                if (!validateField($(this))) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Por favor complete todos los campos requeridos.', 'danger');
                
                // Scroll al primer campo con error
                const firstError = $('.is-invalid').first();
                if (firstError.length) {
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 100
                    }, 500);
                    firstError.focus();
                }
            } else {
                // Mostrar loader en botón de envío
                const submitBtn = $(this).find('[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...').prop('disabled', true);
                
                // Restaurar después de 10 segundos por si hay error
                setTimeout(() => {
                    submitBtn.html(originalText).prop('disabled', false);
                }, 10000);
            }
        });
        
        // Animaciones de entrada
        $('.form-section').each(function(index) {
            $(this).css('animation-delay', `${index * 0.1}s`);
        });
    });
</script>
@stop