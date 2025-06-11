@extends('adminlte::page')

@section('title', 'Editar Solicitud de Fotocopias')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-copy mr-2"></i>Editar Solicitud de Fotocopias</h1>
@stop

@section('content')
<div class="container">
    <div class="card card-outline" style="border-top-color: #364E76;">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Formato Orden de Fotocopias</h3>
        </div>
        
        <form action="{{ route('purchase-requests.update', $purchaseRequest->id) }}" method="POST" id="copiesForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="materials">
            
            <div class="card-body">
                <!-- Cabecera del formato -->
                <div class="text-center mb-3">
                    <h4 class="font-weight-bold" style="color: #364E76;">GESTIÓN ADMINISTRATIVA Y FINANCIERA</h4>
                    <h4 class="font-weight-bold" style="color: #364E76;">COLEGIO VICTORIA SAS</h4>
                    <div>FORMATO ORDEN DE FOTOCOPIAS A.ADM-71</div>
                </div>

                <!-- Número de solicitud -->
                <div class="alert alert-info">
                    <strong>Número de Solicitud:</strong> {{ $purchaseRequest->request_number }}<br>
                    <strong>Fecha de Solicitud:</strong> {{ $purchaseRequest->request_date->format('d/m/Y') }}<br>
                    <strong>Estado:</strong> 
                    @if($purchaseRequest->status === 'pending')
                        <span class="badge badge-warning">Pendiente</span>
                    @elseif($purchaseRequest->status === 'approved')
                        <span class="badge badge-success">Aprobada</span>
                    @elseif($purchaseRequest->status === 'rejected')
                        <span class="badge badge-danger">Rechazada</span>
                    @endif
                </div>

                <!-- Datos del usuario -->
                <table class="table table-bordered mb-4">
                    <tr>
                        <td>SOLICITANTE:</td>
                        <td>
                            <input type="text" class="form-control @error('requester') is-invalid @enderror" name="requester" value="{{ old('requester', $purchaseRequest->requester) }}">
                            @error('requester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>CÓDIGO:</td>
                        <td>
                            <input type="text" class="form-control" name="code" value="{{ old('code', $purchaseRequest->code) }}">
                        </td>
                    </tr>
                    <tr>
                        <td>GRADO:</td>
                        <td>
                            <input type="text" class="form-control" name="grade" value="{{ old('grade', $purchaseRequest->grade) }}">
                        </td>
                        <td>SECCIÓN:</td>
                        <td>
                            <select class="form-control @error('section') is-invalid @enderror" name="section">
                                <option value="">Seleccione...</option>
                                <option value="Pre Escolar" {{ old('section', $purchaseRequest->section) == 'Pre Escolar' ? 'selected' : '' }}>Pre Escolar</option>
                                <option value="Primaria" {{ old('section', $purchaseRequest->section) == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                                <option value="Bachillerato" {{ old('section', $purchaseRequest->section) == 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                                <option value="Administración" {{ old('section', $purchaseRequest->section) == 'Administración' ? 'selected' : '' }}>Administración</option>
                            </select>
                            @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>FECHA DE ENTREGA:</td>
                        <td>
                            <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" name="delivery_date" value="{{ old('delivery_date', $purchaseRequest->delivery_date->format('Y-m-d')) }}">
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>RECIBIDO A SATISFACCIÓN</td>
                        <td></td>
                    </tr>
                </table>

                <!-- Solicitud de fotocopias -->
                <div class="card my-4 card-outline" style="border-top-color: #364E76;">
                    <div class="card-header" style="background-color: #364E76; color: white;">
                        <h5 class="mb-0">SOLICITUD / FOTOCOPIAS</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="copyItemsTable">                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 10%;">N</th>
                                        <th style="width: 20%;">ORIGINAL</th>
                                        <th style="width: 20%;">COPIAS REQUERIDAS</th>
                                        <th style="width: 20%;">DOBLE CARTA COLOR</th>
                                        <th style="width: 15%;">BLANCO Y NEGRO</th>
                                        <th style="width: 10%;">COLOR</th>
                                        <th style="width: 5%;">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="copyItemsBody">
                                    @foreach($purchaseRequest->copy_items as $index => $item)
                                    <tr id="copyItem-{{ $item['item'] }}">
                                        <td>{{ $item['item'] }}</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="copy_items[{{ $index }}][original]" value="{{ $item['original'] ?? '' }}">
                                            <input type="hidden" name="copy_items[{{ $index }}][item]" value="{{ $item['item'] }}">
                                        </td>                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][copies_required]" min="0" value="{{ $item['copies_required'] ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][double_letter_color]" min="0" value="{{ $item['double_letter_color'] ?? '' }}" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][black_white]" min="0" value="{{ $item['black_white'] ?? '' }}" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][color]" min="0" value="{{ $item['color'] ?? '' }}" placeholder="0">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger delete-row" {{ count($purchaseRequest->copy_items) <= 1 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <button type="button" class="btn btn-sm" id="addCopyItem" style="background-color: #364E76; color: white;">
                                                <i class="fas fa-plus"></i> Agregar
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>                </div>

                <!-- Especificaciones -->
                <div class="card my-4 card-outline" style="border-top-color: #364E76;">
                    <div class="card-header" style="background-color: #364E76; color: white;">
                        <h5 class="mb-0"><i class="fas fa-cogs mr-2"></i>ESPECIFICACIONES</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="paper_size" class="font-weight-bold">
                                        <i class="fas fa-ruler mr-2"></i>Tamaño de Papel
                                    </label>
                                    <select class="form-control @error('paper_size') is-invalid @enderror" name="paper_size" id="paper_size">
                                        <option value="">Seleccione...</option>
                                        <option value="Carta" {{ old('paper_size', $purchaseRequest->paper_size) == 'Carta' ? 'selected' : '' }}>Carta (21.5 x 27.9 cm)</option>
                                        <option value="Oficio" {{ old('paper_size', $purchaseRequest->paper_size) == 'Oficio' ? 'selected' : '' }}>Oficio (21.5 x 35.5 cm)</option>
                                        <option value="A4" {{ old('paper_size', $purchaseRequest->paper_size) == 'A4' ? 'selected' : '' }}>A4 (21.0 x 29.7 cm)</option>
                                        <option value="A3" {{ old('paper_size', $purchaseRequest->paper_size) == 'A3' ? 'selected' : '' }}>A3 (29.7 x 42.0 cm)</option>
                                        <option value="Tabloid" {{ old('paper_size', $purchaseRequest->paper_size) == 'Tabloid' ? 'selected' : '' }}>Tabloid (27.9 x 43.2 cm)</option>
                                    </select>
                                    @error('paper_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="paper_type" class="font-weight-bold">
                                        <i class="fas fa-file-alt mr-2"></i>Tipo de Papel
                                    </label>
                                    <select class="form-control @error('paper_type') is-invalid @enderror" name="paper_type" id="paper_type">
                                        <option value="">Seleccione...</option>
                                        <option value="Bond 75g" {{ old('paper_type', $purchaseRequest->paper_type) == 'Bond 75g' ? 'selected' : '' }}>Bond 75g</option>
                                        <option value="Bond 90g" {{ old('paper_type', $purchaseRequest->paper_type) == 'Bond 90g' ? 'selected' : '' }}>Bond 90g</option>
                                        <option value="Propalcote 115g" {{ old('paper_type', $purchaseRequest->paper_type) == 'Propalcote 115g' ? 'selected' : '' }}>Propalcote 115g</option>
                                        <option value="Propalcote 150g" {{ old('paper_type', $purchaseRequest->paper_type) == 'Propalcote 150g' ? 'selected' : '' }}>Propalcote 150g</option>
                                        <option value="Cartulina" {{ old('paper_type', $purchaseRequest->paper_type) == 'Cartulina' ? 'selected' : '' }}>Cartulina</option>
                                        <option value="Opalina" {{ old('paper_type', $purchaseRequest->paper_type) == 'Opalina' ? 'selected' : '' }}>Opalina</option>
                                    </select>
                                    @error('paper_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="paper_color" class="font-weight-bold">
                                        <i class="fas fa-palette mr-2"></i>Color del Papel
                                    </label>
                                    <select class="form-control @error('paper_color') is-invalid @enderror" name="paper_color" id="paper_color">
                                        <option value="">Seleccione...</option>
                                        <option value="Blanco" {{ old('paper_color', $purchaseRequest->paper_color) == 'Blanco' ? 'selected' : '' }}>Blanco</option>
                                        <option value="Amarillo" {{ old('paper_color', $purchaseRequest->paper_color) == 'Amarillo' ? 'selected' : '' }}>Amarillo</option>
                                        <option value="Rosa" {{ old('paper_color', $purchaseRequest->paper_color) == 'Rosa' ? 'selected' : '' }}>Rosa</option>
                                        <option value="Verde" {{ old('paper_color', $purchaseRequest->paper_color) == 'Verde' ? 'selected' : '' }}>Verde</option>
                                        <option value="Azul" {{ old('paper_color', $purchaseRequest->paper_color) == 'Azul' ? 'selected' : '' }}>Azul</option>
                                        <option value="Gris" {{ old('paper_color', $purchaseRequest->paper_color) == 'Gris' ? 'selected' : '' }}>Gris</option>
                                        <option value="Otro" {{ old('paper_color', $purchaseRequest->paper_color) == 'Otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                    @error('paper_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-ring mr-2"></i>¿Requiere Anillado?
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_binding" id="binding_yes" value="1" {{ old('requires_binding', $purchaseRequest->requires_binding) == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="binding_yes">
                                            <i class="fas fa-check-circle text-success mr-1"></i>Sí
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_binding" id="binding_no" value="0" {{ old('requires_binding', $purchaseRequest->requires_binding) == '0' || old('requires_binding', $purchaseRequest->requires_binding) === null ? 'checked' : '' }}>
                                        <label class="form-check-label" for="binding_no">
                                            <i class="fas fa-times-circle text-danger mr-1"></i>No
                                        </label>
                                    </div>
                                    @error('requires_binding')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-shield-alt mr-2"></i>¿Requiere Laminado?
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_lamination" id="lamination_yes" value="1" {{ old('requires_lamination', $purchaseRequest->requires_lamination) == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lamination_yes">
                                            <i class="fas fa-check-circle text-success mr-1"></i>Sí
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_lamination" id="lamination_no" value="0" {{ old('requires_lamination', $purchaseRequest->requires_lamination) == '0' || old('requires_lamination', $purchaseRequest->requires_lamination) === null ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lamination_no">
                                            <i class="fas fa-times-circle text-danger mr-1"></i>No
                                        </label>
                                    </div>
                                    @error('requires_lamination')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-cut mr-2"></i>¿Requiere Recortes?
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_cutting" id="cutting_yes" value="1" {{ old('requires_cutting', $purchaseRequest->requires_cutting) == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="cutting_yes">
                                            <i class="fas fa-check-circle text-success mr-1"></i>Sí
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_cutting" id="cutting_no" value="0" {{ old('requires_cutting', $purchaseRequest->requires_cutting) == '0' || old('requires_cutting', $purchaseRequest->requires_cutting) === null ? 'checked' : '' }}>
                                        <label class="form-check-label" for="cutting_no">
                                            <i class="fas fa-times-circle text-danger mr-1"></i>No
                                        </label>
                                    </div>
                                    @error('requires_cutting')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campo adicional para especificar detalles cuando se requieren servicios especiales -->
                        <div class="row" id="special_details_section" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="special_details" class="font-weight-bold">
                                        <i class="fas fa-comment-alt mr-2"></i>Detalles Adicionales
                                    </label>
                                    <textarea class="form-control" name="special_details" id="special_details" rows="3" placeholder="Especifique detalles sobre el anillado, laminado o recortes requeridos...">{{ old('special_details', $purchaseRequest->special_details) }}</textarea>
                                    <small class="form-text text-muted">
                                        Indique el tipo de anillado, medidas de recorte, tipo de laminado, etc.
                                    </small>
                                    @error('special_details')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>                </div>

                <!-- Campo de adjuntar archivos originales -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-outline" style="border-top-color: #364E76;">
                            <div class="card-header" style="background-color: #364E76; color: white;">
                                <h5 class="mb-0"><i class="fas fa-paperclip mr-2"></i>Adjuntar Archivos Originales</h5>
                            </div>
                            <div class="card-body">
                                @if($purchaseRequest->attached_files && count($purchaseRequest->attached_files) > 0)
                                    <div class="alert alert-info">
                                        <i class="fas fa-file mr-2"></i>
                                        <strong>Archivos actuales:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach($purchaseRequest->attached_files as $index => $file)
                                                <li>
                                                    <i class="fas fa-file-alt mr-1"></i>
                                                    {{ $file['original_name'] ?? 'Archivo ' . ($index + 1) }}
                                                    <small class="text-muted">({{ isset($file['size']) ? number_format($file['size'] / 1024, 1) : '0' }} KB)</small>
                                                    @if(isset($file['file_path']))
                                                        <a href="{{ route('purchase-requests.download-attached-file', ['id' => $purchaseRequest->id, 'fileIndex' => $index]) }}" 
                                                           class="btn btn-sm btn-outline-primary ml-2">
                                                            <i class="fas fa-download"></i> Descargar
                                                        </a>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <div id="attachedFilesContainer">
                                    <div class="attached-file-row" data-index="0">
                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                <i class="fas fa-paperclip mr-2"></i>
                                                @if($purchaseRequest->attached_files && count($purchaseRequest->attached_files) > 0)
                                                    Reemplazar Archivos - Archivo #1 (PDF, DOC, DOCX, JPG, PNG - Máx. 10MB)
                                                @else
                                                    Archivo Original #1 (PDF, DOC, DOCX, JPG, PNG - Máx. 10MB)
                                                @endif
                                            </label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input attached-file-input" 
                                                           id="attached_files_0" name="attached_files[]" 
                                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                    <label class="custom-file-label" for="attached_files_0">Seleccionar archivo...</label>
                                                </div>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger remove-file-btn" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-sm" id="addFileBtn" style="background-color: #364E76; color: white;">
                                        <i class="fas fa-plus mr-2"></i>Agregar Otro Archivo
                                    </button>
                                </div>
                                
                                @error('attached_files')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                @error('attached_files.*')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    @if($purchaseRequest->attached_files && count($purchaseRequest->attached_files) > 0)
                                        <strong>Opcional:</strong> Seleccione archivos solo si desea reemplazar los archivos actuales.
                                    @else
                                        <strong>Opcional:</strong> Puede adjuntar los archivos originales que necesita fotocopiar.
                                    @endif
                                    Máximo 5 archivos.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn" style="background-color: #364E76; color: white;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --institutional-blue: #364E76;
    }
    
    .table th {
        background-color: var(--institutional-blue);
        color: white;
    }
    
    .delete-row:hover {
        background-color: #dc3545;
        color: white;
    }
    
    .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }
    
    .attached-file-row {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }
    
    .attached-file-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .remove-file-btn:hover {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-primary {
        background-color: var(--institutional-blue);
        border-color: var(--institutional-blue);
    }
    
    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }
</style>
@stop

@section('js')
<script>
    $(function() {
        // Variables para contadores de filas
        let copyItemCounter = {{ collect($purchaseRequest->copy_items)->max('item') ?? 0 }};
          // Función para agregar nueva fotocopia
        $('#addCopyItem').click(function() {
            copyItemCounter++;
            const newIndex = $('#copyItemsBody tr').length;
            const newRow = `
                <tr id="copyItem-${copyItemCounter}">
                    <td>${copyItemCounter}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="copy_items[${newIndex}][original]">
                        <input type="hidden" name="copy_items[${newIndex}][item]" value="${copyItemCounter}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="copy_items[${newIndex}][copies_required]" min="0">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="copy_items[${newIndex}][double_letter_color]" min="0" placeholder="0">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="copy_items[${newIndex}][black_white]" min="0" placeholder="0">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="copy_items[${newIndex}][color]" min="0" placeholder="0">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#copyItemsBody').append(newRow);
        });
        
        // Evento para eliminar fila (delegación de eventos)
        $(document).on('click', '.delete-row', function() {
            // No permitir eliminar si solo queda una fila
            const tableId = $(this).closest('table').attr('id');
            const rowCount = $(this).closest('tbody').find('tr').length;
            
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                
                // Renumerar las filas visibles
                $('#copyItemsBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            } else {
                alert('Debe mantener al menos un ítem en la tabla.');
            }
        });
        
        // Validación del formulario
        $('#copiesForm').submit(function(e) {
            let valid = false;
            let hasCopyItems = false;
            
            // Verificar si hay ítems de fotocopias
            $('input[name$="[copies_required]"]').each(function() {
                if ($(this).val() && parseInt($(this).val()) > 0) {
                    hasCopyItems = true;
                    return false; // Romper el ciclo
                }
            });
            
            valid = hasCopyItems;
            
            if (!valid) {
                e.preventDefault();
                alert('Debe ingresar al menos una solicitud de fotocopias para procesar la solicitud.');
                return false;
            }
            
            return true;
        });
          // Mostrar nombre del archivo seleccionado
        $(document).on('change', '.attached-file-input', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Seleccionar archivo...';
            $(this).next('.custom-file-label').text(fileName);
        });
        
        // Variables para manejo de archivos adjuntos
        let attachedFileCounter = 0;
        
        // Agregar nuevo campo de archivo adjunto
        $('#addFileBtn').click(function() {
            const currentFiles = $('.attached-file-row').length;
            if (currentFiles >= 5) {
                alert('Máximo 5 archivos permitidos.');
                return;
            }
            
            attachedFileCounter++;
            const newIndex = currentFiles;
            const newFileRow = `
                <div class="attached-file-row" data-index="${newIndex}">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-paperclip mr-2"></i>Archivo Original #${currentFiles + 1} (PDF, DOC, DOCX, JPG, PNG - Máx. 10MB)
                        </label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input attached-file-input" 
                                       id="attached_files_${newIndex}" name="attached_files[]" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <label class="custom-file-label" for="attached_files_${newIndex}">Seleccionar archivo...</label>
                            </div>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-danger remove-file-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#attachedFilesContainer').append(newFileRow);
            updateRemoveButtons();
        });
        
        // Eliminar campo de archivo adjunto
        $(document).on('click', '.remove-file-btn', function() {
            const rowCount = $('.attached-file-row').length;
            if (rowCount > 1) {
                $(this).closest('.attached-file-row').remove();
                updateFileLabels();
                updateRemoveButtons();
            }
        });
        
        // Función para actualizar las etiquetas de los archivos
        function updateFileLabels() {
            $('.attached-file-row').each(function(index) {
                $(this).find('label').html(`
                    <i class="fas fa-paperclip mr-2"></i>Archivo Original #${index + 1} (PDF, DOC, DOCX, JPG, PNG - Máx. 10MB)
                `);
            });
        }
        
        // Función para actualizar el estado de los botones de eliminar
        function updateRemoveButtons() {
            const rowCount = $('.attached-file-row').length;
            if (rowCount <= 1) {
                $('.remove-file-btn').prop('disabled', true);
            } else {
                $('.remove-file-btn').prop('disabled', false);
            }
        }
        
        // Función para mostrar/ocultar detalles especiales
        function toggleSpecialDetails() {
            const binding = $('input[name="requires_binding"]:checked').val();
            const lamination = $('input[name="requires_lamination"]:checked').val();
            const cutting = $('input[name="requires_cutting"]:checked').val();
            
            if (binding == '1' || lamination == '1' || cutting == '1') {
                $('#special_details_section').fadeIn();
            } else {
                $('#special_details_section').fadeOut();
                $('#special_details').val(''); // Limpiar el campo
            }
        }
        
        // Eventos para los radio buttons de servicios especiales
        $('input[name="requires_binding"], input[name="requires_lamination"], input[name="requires_cutting"]').on('change', toggleSpecialDetails);
        
        // Verificar el estado inicial
        toggleSpecialDetails();
    });
</script>
@stop
