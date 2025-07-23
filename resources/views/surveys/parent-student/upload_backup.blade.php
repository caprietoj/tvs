@extends('adminlte::page')

@section('title', 'Subir Encuestas - Padres de Familia')

@section('content_header')
    <h1>
        <i class="fas fa-upload"></i> Subir Encuestas de Padres de Familia
        <small>Cafetería y Transporte</small>
    </h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-excel"></i> Cargar Archivos Excel
                    </h3>
                </div>
                
                <form action="{{ route('surveys.parent-student.upload.process') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="card-body">
                        <!-- Área de Drag & Drop -->
                        <div class="form-group">
                            <label for="excel_files">Archivos Excel *</label>
                            <div class="upload-area" id="upload-area">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                                    <h4>Arrastra y suelta tus archivos aquí</h4>
                                    <p>o haz clic para seleccionar archivos</p>
                                    <div class="upload-btn-wrapper">
                                        <button type="button" class="btn btn-primary btn-lg" id="select-files-btn">
                                            <i class="fas fa-plus"></i> Seleccionar Archivos
                                        </button>
                                        <input type="file" class="d-none" id="excel_files" name="excel_files[]" accept=".xlsx,.xls" multiple>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Formatos permitidos: .xlsx, .xls (máximo 10MB por archivo). Puedes seleccionar múltiples archivos.
                            </small>
                        </div>

                        <!-- Lista de archivos seleccionados -->
                        <div id="file-list" class="file-list" style="display: none;">
                            <h5><i class="fas fa-list"></i> Archivos Seleccionados:</h5>
                            <div id="selected-files"></div>
                        </div>
                        
                        <!-- Mensaje cuando no hay archivos -->
                        <div id="no-files-message" class="alert alert-light text-center">
                            <i class="fas fa-info-circle text-muted"></i>
                            <span class="text-muted">No hay archivos seleccionados. Arrastra archivos o usa el botón para seleccionar.</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="survey_type">Tipo de Encuesta *</label>
                            <select class="form-control" id="survey_type" name="survey_type" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="complete">Completa (Cafetería + Transporte)</option>
                                <option value="cafeteria_only">Solo Cafetería</option>
                                <option value="transport_only">Solo Transporte</option>
                            </select>
                            <small class="form-text text-muted">
                                Selecciona el tipo de encuesta según el contenido de tus archivos
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="period">Período *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="text" class="form-control" id="period" name="period" placeholder="Ej: 2024-2, Enero 2024, Primer Semestre 2024" required>
                            </div>
                            <small class="form-text text-muted">
                                Identificador del período de la encuesta (será detectado automáticamente de los archivos si está disponible)
                            </small>
                        </div>
                        
                        <!-- Configuración Avanzada -->
                        <div class="card mt-3">
                            <div class="card-header" data-toggle="collapse" data-target="#advanced-settings" aria-expanded="false">
                                <h6 class="mb-0">
                                    <i class="fas fa-cog"></i> Configuración Avanzada
                                    <i class="fas fa-chevron-down float-right"></i>
                                </h6>
                            </div>
                            <div class="collapse" id="advanced-settings">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="auto_detect_provider">
                                            <input type="checkbox" id="auto_detect_provider" name="auto_detect_provider" checked>
                                            Detectar proveedor automáticamente
                                        </label>
                                        <small class="form-text text-muted">
                                            El sistema intentará detectar el proveedor (Sapore, Aldimark, etc.) basándose en el contenido del archivo
                                        </small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="skip_duplicates">
                                            <input type="checkbox" id="skip_duplicates" name="skip_duplicates" checked>
                                            Omitir registros duplicados
                                        </label>
                                        <small class="form-text text-muted">
                                            Evita procesar registros que ya existen en la base de datos
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <h5><i class="icon fas fa-info"></i> Mejoras en el Procesamiento:</h5>
                            <ul class="mb-0">
                                <li><strong>Múltiples Archivos:</strong> Procesa varios archivos Excel de una vez</li>
                                <li><strong>Detección Automática:</strong> El sistema detecta automáticamente el formato y proveedor</li>
                                <li><strong>Validación Inteligente:</strong> Verifica la integridad de los datos antes de procesarlos</li>
                                <li><strong>Progreso en Tiempo Real:</strong> Visualiza el progreso de carga de cada archivo</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                            <i class="fas fa-upload"></i> Procesar Archivos
                        </button>
                        <a href="{{ route('surveys.parent-student.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        
                        <!-- Progress Bar -->
                        <div class="progress mt-3" id="upload-progress" style="display: none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </h3>
                </div>
                <div class="card-body">
                    <h5>Formato de Archivo Requerido</h5>
                    <p class="text-muted">El archivo Excel debe contener las siguientes columnas mínimas:</p>
                    
                    <div class="alert alert-secondary">
                        <strong>Encuesta Completa:</strong>
                        <ul class="mb-0 small">
                            <li>Estudiante/Identificación</li>
                            <li>Calificaciones de Cafetería (1-5)</li>
                            <li>Calificaciones de Transporte (1-5)</li>
                            <li>Comentarios (opcional)</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Solo Cafetería:</strong>
                        <ul class="mb-0 small">
                            <li>Estudiante/Identificación</li>
                            <li>Calidad de Comida</li>
                            <li>Precio</li>
                            <li>Atención al Cliente</li>
                            <li>Limpieza</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>Solo Transporte:</strong>
                        <ul class="mb-0 small">
                            <li>Estudiante/Identificación</li>
                            <li>Puntualidad</li>
                            <li>Seguridad</li>
                            <li>Comodidad</li>
                            <li>Atención al Cliente</li>
                        </ul>
                    </div>
                    
                    <hr>
                    
                    <h5>Consejos</h5>
                    <ul class="text-muted small">
                        <li>Usa Excel 2010 o superior (.xlsx)</li>
                        <li>Evita celdas combinadas</li>
                        <li>Los datos numéricos deben estar en formato número</li>
                        <li>Las fechas en formato DD/MM/YYYY</li>
                        <li>No dejes filas vacías entre datos</li>
                    </ul>
                    
                    <div class="mt-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                            <i class="fas fa-download"></i> Descargar Plantilla
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas de Archivos Recientes -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Últimas Cargas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Archivos Procesados Hoy</span>
                            <span class="info-box-number">{{ $recentUploads ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Registros Totales</span>
                            <span class="info-box-number">{{ $totalRecords ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i>
                            <strong>Tip:</strong> Puedes procesar múltiples archivos a la vez para ahorrar tiempo.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('adminlte_css')
<style>
    .upload-area {
        border: 2px dashed #007bff;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
        position: relative;
    }
    
    .upload-area:hover,
    .upload-area.dragover {
        border-color: #0056b3;
        background-color: #e3f2fd;
        transform: scale(1.02);
    }
    
    .upload-content {
        pointer-events: none;
        position: relative;
        z-index: 1;
    }
    
    .upload-btn-wrapper {
        margin-top: 20px;
        pointer-events: auto;
    }
    
    #select-files-btn {
        pointer-events: auto;
        position: relative;
        z-index: 2;
    }
    
    .file-list {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }
    
    .file-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        margin: 5px 0;
        background-color: white;
        border-radius: 5px;
        border: 1px solid #e9ecef;
    }
    
    .file-info {
        display: flex;
        align-items: center;
    }
    
    .file-icon {
        color: #28a745;
        margin-right: 10px;
    }
    
    .file-name {
        font-weight: 500;
    }
    
    .file-size {
        color: #6c757d;
        font-size: 0.875rem;
        margin-left: 10px;
    }
    
    .file-remove {
        color: #dc3545;
        cursor: pointer;
        padding: 5px;
        border-radius: 3px;
        transition: background-color 0.2s;
    }
    
    .file-remove:hover {
        background-color: #f8d7da;
    }
    
    .upload-btn-wrapper {
        margin-top: 20px;
    }
    
    .progress {
        height: 25px;
    }
    
    .progress-text {
        line-height: 25px;
        font-weight: bold;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .alert {
        border-radius: 10px;
    }
    
    #no-files-message {
        border: 1px dashed #dee2e6;
        background-color: #fafafa;
        margin-top: 15px;
        padding: 20px;
    }
    
    @media (max-width: 768px) {
        .upload-area {
            padding: 20px;
        }
        
        .file-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .file-remove {
            align-self: flex-end;
            margin-top: 10px;
        }
    }
</style>
@endsection

@section('adminlte_js')
<script>
$(document).ready(function() {
    const uploadArea = $('#upload-area');
    const fileInput = $('#excel_files');
    const selectBtn = $('#select-files-btn');
    const fileList = $('#file-list');
    const selectedFiles = $('#selected-files');
    const submitBtn = $('#submit-btn');
    const uploadForm = $('#uploadForm');
    const progressBar = $('#upload-progress');
    const progressBarFill = $('.progress-bar');
    const progressText = $('#progress-text');
    
    let filesArray = [];
    let isProcessing = false;
    
    // Drag and Drop functionality
    uploadArea.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });
    
    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Solo remover dragover si realmente salimos del área
        if (!$(e.target).closest('#upload-area').length) {
            $(this).removeClass('dragover');
        }
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        if (isProcessing) return;
        
        const files = e.originalEvent.dataTransfer.files;
        if (files && files.length > 0) {
            handleFiles(files);
        }
    });
    
    // Botón para seleccionar archivos - SIN evento en el área de upload
    selectBtn.off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileInput.trigger('click');
    });
    
    // File input change - simplificado
    fileInput.off('change').on('change', function(e) {
        if (isProcessing) return;
        
        const files = this.files;
        if (files && files.length > 0) {
            handleFiles(files);
        }
    });
    
    function handleFiles(files) {
        if (isProcessing) return;
        isProcessing = true;
        
        let hasNewFiles = false;
        
        Array.from(files).forEach(file => {
            if (isValidFile(file)) {
                // Check if file already exists
                const existingIndex = filesArray.findIndex(f => f.name === file.name && f.size === file.size);
                if (existingIndex === -1) {
                    filesArray.push(file);
                    hasNewFiles = true;
                } else {
                    showAlert('El archivo "' + file.name + '" ya está seleccionado.', 'warning');
                }
            }
        });
        
        if (hasNewFiles) {
            updateFileList();
        }
        
        // Reset processing flag
        setTimeout(() => {
            isProcessing = false;
        }, 100);
    }
    
    function isValidFile(file) {
        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream'
        ];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        // Verificar por extensión
        const fileName = file.name.toLowerCase();
        const hasValidExtension = fileName.endsWith('.xlsx') || fileName.endsWith('.xls');
        
        if (!allowedTypes.includes(file.type) && !hasValidExtension) {
            showAlert('Tipo de archivo no válido: ' + file.name + '. Solo se permiten archivos Excel (.xlsx, .xls)', 'error');
            return false;
        }
        
        if (file.size > maxSize) {
            showAlert('Archivo demasiado grande: ' + file.name + '. Máximo 10MB permitido.', 'error');
            return false;
        }
        
        return true;
    }
    
    function isValidFileForSubmit(file) {
        // Validación silenciosa para el envío del formulario
        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream'
        ];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        // Verificar por extensión
        const fileName = file.name.toLowerCase();
        const hasValidExtension = fileName.endsWith('.xlsx') || fileName.endsWith('.xls');
        
        // Verificación sin mostrar alertas
        if (!allowedTypes.includes(file.type) && !hasValidExtension) {
            return false;
        }
        
        if (file.size > maxSize) {
            return false;
        }
        
        return true;
    }
    
    function updateFileList() {
        const noFilesMessage = $('#no-files-message');
        
        if (filesArray.length > 0) {
            fileList.show();
            noFilesMessage.hide();
            selectedFiles.empty();
            
            filesArray.forEach((file, index) => {
                const fileItem = $(`
                    <div class="file-item">
                        <div class="file-info">
                            <i class="fas fa-file-excel file-icon"></i>
                            <span class="file-name">${file.name}</span>
                            <span class="file-size">(${formatFileSize(file.size)})</span>
                        </div>
                        <span class="file-remove" data-index="${index}" title="Remover archivo">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                `);
                selectedFiles.append(fileItem);
            });
            
            submitBtn.prop('disabled', false);
            updateHiddenInput();
        } else {
            fileList.hide();
            noFilesMessage.show();
            submitBtn.prop('disabled', true);
        }
    }
    
    function updateHiddenInput() {
        // NO actualizar el input original para evitar bucles
        // Solo crear inputs hidden para el envío
        $('input[name="excel_files_data[]"]').remove();
        
        // Crear un campo hidden con la información de archivos para validación
        const fileNames = filesArray.map(f => f.name).join(',');
        $('<input>').attr({
            type: 'hidden',
            name: 'file_names',
            value: fileNames
        }).appendTo(uploadForm);
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Remove file
    $(document).off('click', '.file-remove').on('click', '.file-remove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const index = parseInt($(this).data('index'));
        if (index >= 0 && index < filesArray.length) {
            filesArray.splice(index, 1);
            updateFileList();
        }
    });
    
    // Form submission
    uploadForm.off('submit').on('submit', function(e) {
        // Validación personalizada de archivos
        if (filesArray.length === 0) {
            e.preventDefault();
            showAlert('Por favor selecciona al menos un archivo Excel', 'warning');
            
            // Hacer scroll hacia el área de upload para mejor UX
            $('html, body').animate({
                scrollTop: $('#upload-area').offset().top - 100
            }, 500);
            
            return false;
        }
        
        // Validar campos requeridos
        const surveyType = $('#survey_type').val();
        const period = $('#period').val();
        
        if (!surveyType) {
            e.preventDefault();
            showAlert('Por favor selecciona el tipo de encuesta', 'warning');
            $('#survey_type').focus();
            return false;
        }
        
        if (!period.trim()) {
            e.preventDefault();
            showAlert('Por favor especifica el período de la encuesta', 'warning');
            $('#period').focus();
            return false;
        }
        
        // Validación adicional de archivos
        const invalidFiles = filesArray.filter(file => !isValidFileForSubmit(file));
        if (invalidFiles.length > 0) {
            e.preventDefault();
            showAlert(`Se encontraron ${invalidFiles.length} archivo(s) inválido(s). Por favor revisa la selección.`, 'error');
            return false;
        }
        
        // Enviar usando FormData para mayor compatibilidad
        e.preventDefault();
        sendFormWithFormData();
        
        return false;
    });
    
    function sendFormWithFormData() {
        // Show progress bar
        progressBar.show();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        
        // Crear FormData
        const formData = new FormData();
        
        // Agregar archivos
        filesArray.forEach((file, index) => {
            formData.append('excel_files[]', file);
        });
        
        // Agregar otros campos del formulario
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('survey_type', $('#survey_type').val());
        formData.append('period', $('#period').val());
        formData.append('auto_detect_provider', $('#auto_detect_provider').is(':checked') ? '1' : '0');
        formData.append('skip_duplicates', $('#skip_duplicates').is(':checked') ? '1' : '0');
        
        // Simulate progress
        simulateProgress();
        
        // Enviar con AJAX
        $.ajax({
            url: uploadForm.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = (evt.loaded / evt.total) * 100;
                        progressBarFill.css('width', percentComplete + '%');
                        progressText.text(Math.round(percentComplete) + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                progressBarFill.css('width', '100%');
                progressText.text('¡Completado!');
                
                setTimeout(() => {
                    if (response.success || typeof response === 'string') {
                        // Redirigir al index con mensaje de éxito
                        window.location.href = "{{ route('surveys.parent-student.index') }}";
                    } else {
                        showAlert(response.message || 'Error en el procesamiento', 'error');
                        resetForm();
                    }
                }, 1000);
            },
            error: function(xhr, status, error) {
                console.error('Error en la carga:', error);
                let errorMessage = 'Error al procesar los archivos';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        console.warn('No se pudo parsear la respuesta de error');
                    }
                }
                
                showAlert(errorMessage, 'error');
                resetForm();
            }
        });
    }
    
    function resetForm() {
        progressBar.hide();
        submitBtn.prop('disabled', false).html('<i class="fas fa-upload"></i> Procesar Archivos');
        progressBarFill.css('width', '0%');
        progressText.text('0%');
    }
    
    function createHiddenFileInputs() {
        // Limpiar inputs hidden previos
        $('input[name^="file_info_"]').remove();
        
        // Crear inputs hidden con información de archivos como fallback
        filesArray.forEach((file, index) => {
            const hiddenInput = $('<input>').attr({
                type: 'hidden',
                name: `file_info_${index}`,
                value: JSON.stringify({
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    lastModified: file.lastModified
                })
            });
            uploadForm.append(hiddenInput);
        });
    }
    
    function simulateProgress() {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            
            progressBarFill.css('width', progress + '%');
            progressText.text(Math.round(progress) + '%');
            
            if (progress >= 90) {
                clearInterval(interval);
                progressText.text('Finalizando...');
            }
        }, 200);
    }
    
    function showAlert(message, type) {
        // Remover alertas existentes
        $('.upload-alert').remove();
        
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 
                          'alert-info';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show upload-alert" role="alert">
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        
        $('.card-body').first().prepend(alert);
        
        // Auto-remove después de 5 segundos
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }
    
    // Initialize
    updateFileList();
    
    // Prevenir drag events globales solo si no estamos en el área de upload
    $(document).on('dragover drop', function(e) {
        if (!$(e.target).closest('#upload-area').length) {
            e.preventDefault();
        }
    });
});

// Global function for template download
function downloadTemplate() {
    try {
        const csvContent = "data:text/csv;charset=utf-8," + 
            "Estudiante,Fecha,Grado,Calidad_Comida,Precio_Comida,Atencion_Cafeteria,Limpieza_Cafeteria,Puntualidad_Transporte,Seguridad_Transporte,Comodidad_Transporte,Atencion_Transporte,Comentarios\n" +
            "Juan Perez,2024-01-15,5to,4,3,5,4,5,4,3,4,Excelente servicio\n" +
            "Maria Garcia,2024-01-15,3ro,5,4,4,5,4,5,4,5,Muy buena experiencia\n";
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "plantilla_encuesta_padres.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (e) {
        console.error('Error al descargar plantilla:', e);
        alert('Error al descargar la plantilla. Por favor intenta de nuevo.');
    }
}
</script>
@endsection
