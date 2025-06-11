@extends('adminlte::page')

@section('title', 'Subir Documento - Recursos Humanos')

@section('content_header')
    <h1 class="text-primary">Subir Documento - Recursos Humanos</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header" style="background-color: #364E76;">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title text-white">Subir Nuevo Documento</h3>
        </div>
    </div>
    <div class="card-body">
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

        <form action="{{ route('rrhh.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nombre del Documento <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                            </div>
                            <input type="text" name="name" id="name" 
                                class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name') }}" required 
                                placeholder="Ej: Política de Personal">
                        </div>
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ingrese un nombre descriptivo para identificar el documento.</small>
                    </div>

                    <div class="form-group">
                        <label for="document" class="form-label">
                            Archivo PDF <span class="text-danger">*</span>
                        </label>
                        <div class="document-upload-container">
                            <input type="file" class="document-upload-input @error('document') is-invalid @enderror" 
                                id="document" name="document" accept=".pdf" required>
                            <label for="document" class="document-upload-label">
                                <div class="document-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="document-upload-text">
                                    <span id="document-text">Arrastre su archivo PDF aquí o haga clic para seleccionar</span>
                                </div>
                            </label>
                            <div class="document-upload-info" id="document-info">
                                <span class="document-upload-filename" id="document-name"></span>
                                <span class="document-upload-size" id="document-size"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ml-2" id="remove-document">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @error('document')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Formato permitido: PDF. Tamaño máximo: 10MB.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Vista previa</h5>
                        </div>
                        <div class="card-body p-0 d-flex align-items-center justify-content-center" style="height: 300px">
                            <div id="preview-placeholder" class="text-center p-4">
                                <i class="fas fa-file-pdf fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Vista previa del documento</p>
                            </div>
                            <div id="preview-container" class="d-none" style="height: 100%; width: 100%;">
                                <iframe id="pdf-preview" style="height: 100%; width: 100%; border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <div class="d-flex">
                    <div class="mr-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Información de documentos</h5>
                        <p class="mb-0">Los documentos subidos serán accesibles para todo el personal autorizado del departamento de Recursos Humanos. Asegúrese de que el documento no contenga información sensible o confidencial.</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ route('rrhh.documents.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-upload mr-2"></i>Subir Documento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Cargando...</span>
                </div>
                <h4 class="modal-title">Subiendo documento</h4>
                <p>Por favor espere mientras se sube su documento...</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                        role="progressbar" style="width: 0%" id="uploadProgress"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --info: #17a2b8;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .custom-card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .card-header {
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.2rem;
    }

    .form-label {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        height: auto;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .btn {
        border-radius: 6px;
        padding: 0.6rem 1.2rem;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
    }

    .btn-primary:hover {
        background-color: #2a3d5f;
        border-color: #2a3d5f;
        transform: translateY(-1px);
    }

    /* Document upload styling */
    .document-upload-container {
        position: relative;
        width: 100%;
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .document-upload-container:hover {
        border-color: #364E76;
        background-color: #f1f5fb;
    }

    .document-upload-container.dragover {
        border-color: #28a745;
        background-color: rgba(40, 167, 69, 0.1);
    }

    .document-upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
    }

    .document-upload-label {
        display: block;
        padding: 20px;
        margin-bottom: 0;
        cursor: pointer;
    }

    .document-upload-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .document-upload-text {
        color: #495057;
        font-size: 1rem;
    }

    .document-upload-info {
        display: none;
        background-color: #e9ecef;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
        align-items: center;
        justify-content: space-between;
    }

    .document-upload-filename {
        font-weight: 600;
        color: #364E76;
        margin-right: 10px;
    }

    .document-upload-size {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .alert-info {
        background-color: #f1f5fb;
        border-left: 4px solid #17a2b8;
        color: #495057;
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const documentInput = document.getElementById('document');
        const documentText = document.getElementById('document-text');
        const documentInfo = document.getElementById('document-info');
        const documentName = document.getElementById('document-name');
        const documentSize = document.getElementById('document-size');
        const removeDocument = document.getElementById('remove-document');
        const uploadContainer = document.querySelector('.document-upload-container');
        const previewContainer = document.getElementById('preview-container');
        const previewPlaceholder = document.getElementById('preview-placeholder');
        const pdfPreview = document.getElementById('pdf-preview');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingModal = $('#loadingModal');
        const uploadProgress = document.getElementById('uploadProgress');

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, function() {
                uploadContainer.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, function() {
                uploadContainer.classList.remove('dragover');
            });
        });

        uploadContainer.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length) {
                documentInput.files = files;
                updateFileInfo(files[0]);
            }
        });

        // File selection functionality
        documentInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                updateFileInfo(this.files[0]);
            }
        });

        // Remove file functionality
        removeDocument.addEventListener('click', function() {
            documentInput.value = '';
            resetFileInfo();
        });

        // Submit form with loading modal
        uploadForm.addEventListener('submit', function() {
            if (documentInput.files.length > 0) {
                loadingModal.modal('show');
                simulateProgress();
                return true;
            }
            return false;
        });

        function updateFileInfo(file) {
            if (file.type !== 'application/pdf') {
                Swal.fire({
                    icon: 'error',
                    title: 'Tipo de archivo no válido',
                    text: 'Por favor seleccione un archivo PDF',
                    confirmButtonColor: '#364E76',
                });
                resetFileInfo();
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'El tamaño máximo permitido es 10MB',
                    confirmButtonColor: '#364E76',
                });
                resetFileInfo();
                return;
            }

            // Display file info
            documentText.textContent = 'Archivo seleccionado';
            documentInfo.style.display = 'flex';
            documentName.textContent = file.name;
            documentSize.textContent = formatFileSize(file.size);
            
            // Show PDF preview
            showPDFPreview(file);
        }

        function resetFileInfo() {
            documentText.textContent = 'Arrastre su archivo PDF aquí o haga clic para seleccionar';
            documentInfo.style.display = 'none';
            documentName.textContent = '';
            documentSize.textContent = '';
            
            // Hide preview
            previewContainer.classList.add('d-none');
            previewPlaceholder.classList.remove('d-none');
        }

        function showPDFPreview(file) {
            const fileURL = URL.createObjectURL(file);
            pdfPreview.src = fileURL;
            previewContainer.classList.remove('d-none');
            previewPlaceholder.classList.add('d-none');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function simulateProgress() {
            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 10;
                if (progress > 90) {
                    clearInterval(interval);
                    progress = 90; // Stay at 90% until actual completion
                }
                uploadProgress.style.width = progress + '%';
            }, 300);
        }
    });
</script>
@stop
