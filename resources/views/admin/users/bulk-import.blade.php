@extends('adminlte::page')

@section('title', 'Importación Masiva de Usuarios')

@section('content_header')
    <h1 class="text-dark">Importación Masiva de Usuarios</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-import"></i> Importación de Usuarios
                    </h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-tool" data-toggle="modal" data-target="#helpModal">
                            <i class="fas fa-question-circle"></i> Ayuda
                        </a>
                        <a href="{{ route('users.template.download') }}" class="btn btn-tool">
                            <i class="fas fa-file-excel"></i> Descargar Plantilla
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Drop zone and input area -->
                            <div class="upload-area mb-4" id="uploadArea">
                                <div class="upload-area-inner">
                                    <div class="upload-area-content text-center">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-muted"></i>
                                        <h4>Arrastre los datos aquí o pegue desde Excel</h4>
                                        <p class="text-muted">También puede pegar directamente usando Ctrl+V</p>
                                    </div>
                                    <textarea id="user_data" name="user_data" class="form-control d-none"></textarea>
                                </div>
                            </div>

                            <!-- Preview table -->
                            <div class="preview-area d-none" id="previewArea">
                                <h5 class="mb-3">Vista Previa</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Configuration panel -->
                            <div class="config-panel">
                                <h5 class="mb-3">Configuración de Importación</h5>
                                
                                <form id="importForm" action="{{ route('users.bulk.import.process') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_data" id="hiddenUserData">
                                    
                                    <div class="form-group">
                                        <label>Roles a Asignar</label>
                                        <select name="default_roles[]" class="select2" multiple required>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}">
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">
                                            Estos roles se asignarán a todos los usuarios importados
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label>Opciones de Importación</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="sendEmails" name="send_emails" checked>
                                            <label class="custom-control-label" for="sendEmails">
                                                Enviar emails de bienvenida
                                            </label>
                                        </div>
                                    </div>

                                    <div class="import-stats mt-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Registros detectados:</span>
                                            <span id="recordCount" class="font-weight-bold">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Registros válidos:</span>
                                            <span id="validCount" class="font-weight-bold text-success">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Errores detectados:</span>
                                            <span id="errorCount" class="font-weight-bold text-danger">0</span>
                                        </div>
                                    </div>

                                    <div class="actions mt-4">
                                        <button type="submit" class="btn btn-success btn-block" id="importBtn" disabled>
                                            <i class="fas fa-upload mr-2"></i>Importar Usuarios
                                        </button>
                                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-block">
                                            <i class="fas fa-times mr-2"></i>Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guía de Importación</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Help content here -->
            </div>
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3"></div>
                <h4 class="modal-title">Procesando Importación</h4>
                <p class="mb-0">Por favor espere mientras se importan los usuarios...</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.upload-area {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    background: #f8f9fa;
    position: relative;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-area.dragover {
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.upload-area-content {
    pointer-events: none;
}

.preview-area {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.config-panel {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    position: sticky;
    top: 1rem;
}

.import-stats {
    background: #fff;
    border-radius: 6px;
    padding: 1rem;
}

.validation-error {
    background-color: #fff3f3;
}

.validation-success {
    background-color: #f0fff4;
}

.select2-container--bootstrap4 .select2-selection {
    border: 1px solid #ced4da;
    border-radius: 4px;
}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ...existing initialization code...

    const uploadArea = document.getElementById('uploadArea');
    const userDataInput = document.getElementById('user_data');
    const previewArea = document.getElementById('previewArea');
    const previewBody = document.getElementById('previewBody');
    const importBtn = document.getElementById('importBtn');
    const recordCount = document.getElementById('recordCount');
    const validCount = document.getElementById('validCount');
    const errorCount = document.getElementById('errorCount');

    function handleData(data) {
        const lines = data.trim().split('\n');
        let valid = 0;
        let errors = 0;
        
        previewBody.innerHTML = '';
        lines.forEach((line, index) => {
            const cols = line.split('\t');
            const isValid = validateLine(cols);
            
            const row = document.createElement('tr');
            row.className = isValid ? 'validation-success' : 'validation-error';
            
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${cols[0] || ''}</td>
                <td>${cols[1] || ''}</td>
                <td>
                    <span class="badge badge-${isValid ? 'success' : 'danger'}">
                        ${isValid ? 'Válido' : 'Error'}
                    </span>
                </td>
            `;
            
            previewBody.appendChild(row);
            
            if (isValid) valid++; else errors++;
        });
        
        recordCount.textContent = lines.length;
        validCount.textContent = valid;
        errorCount.textContent = errors;
        
        previewArea.classList.remove('d-none');
        importBtn.disabled = valid === 0;
        
        // Update hidden input
        document.getElementById('hiddenUserData').value = data;
    }

    function validateLine(cols) {
        if (cols.length < 2) return false;
        
        const nameValid = cols[0] && cols[0].trim().length >= 3;
        const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cols[1]);
        
        return nameValid && emailValid;
    }

    // Drag and drop handlers
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const text = e.dataTransfer.getData('text');
        if (text) {
            handleData(text);
        }
    });

    // Paste handler
    document.addEventListener('paste', (e) => {
        const text = e.clipboardData.getData('text');
        handleData(text);
    });

    // Form submission
    document.getElementById('importForm').addEventListener('submit', () => {
        $('#processingModal').modal('show');
        simulateProgress();
    });

    function simulateProgress() {
        let progress = 0;
        const bar = document.querySelector('#processingModal .progress-bar');
        
        const interval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) {
                clearInterval(interval);
                progress = 90;
            }
            bar.style.width = Math.min(progress, 90) + '%';
        }, 500);
    }
});
</script>
@stop
