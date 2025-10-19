@extends('adminlte::page')

@section('title', 'Formato Compra de Materiales')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-shopping-cart mr-2"></i>Formato Compra de Materiales</h1>
@stop

@section('content')
<div class="container">
    <div class="card card-outline" style="border-top-color: #364E76;">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Solicitud de Compra</h3>
        </div>
        
        <form action="{{ route('purchase-requests.store') }}" method="POST" id="purchaseForm">
            @csrf
            <input type="hidden" name="type" value="purchase">
            
            <div class="card-body">
                <!-- Cabecera del formato -->
                <div class="text-center mb-3">
                    <h4 class="font-weight-bold" style="color: #364E76;">GESTIÓN ADMINISTRATIVA Y FINANCIERA</h4>
                    <h4 class="font-weight-bold" style="color: #364E76;">COLEGIO VICTORIA SAS</h4>
                    <div>FORMATO COMPRA DE MATERIALES</div>
                </div>

                <!-- Datos generales -->
                <div class="form-group">
                    <label for="requester">DOCENTE Y/O SOLICITANTE:</label>
                    <input type="text" class="form-control @error('requester') is-invalid @enderror" id="requester" name="requester" value="{{ old('requester', auth()->user()->name) }}" readonly>
                    @error('requester')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="section_area">SECCIÓN Y/O ÁREA:</label>
                        <select class="form-control @error('section_area') is-invalid @enderror" id="section_area" name="section_area">
                            <option value="">Seleccione...</option>
                            <option value="Preescolar y Primaria" {{ old('section_area') == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                            <option value="Escuela Media" {{ old('section_area') == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                            <option value="Escuela Alta / DP" {{ old('section_area') == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                            <option value="PAI" {{ old('section_area') == 'PAI' ? 'selected' : '' }}>PAI</option>
                            <option value="PEP" {{ old('section_area') == 'PEP' ? 'selected' : '' }}>PEP</option>
                            <option value="Deportes" {{ old('section_area') == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                            <option value="Psicología Institucional" {{ old('section_area') == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                            <option value="Biblioteca" {{ old('section_area') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                            <option value="Dirección General" {{ old('section_area') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                            <option value="CAS" {{ old('section_area') == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="Administración" {{ old('section_area') == 'Administración' ? 'selected' : '' }}>Administración</option>
                            <option value="Tecnología Institucional" {{ old('section_area') == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                        </select>
                        @error('section_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="request_date">FECHA DE SOLICITUD:</label>
                        <input type="date" class="form-control" id="request_date" name="request_date" value="{{ old('request_date', date('Y-m-d')) }}" readonly>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="reception_date">FECHA DE RECEPCIÓN:</label>
                        <input type="date" class="form-control" id="reception_date" name="reception_date" disabled>
                        <small class="form-text text-muted">Será completado por el departamento de compras</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="coordinator">COORDINADOR DE SECCIÓN Y/O JEFE DE ÁREA:</label>
                    <input type="text" class="form-control" id="coordinator" name="coordinator" value="{{ old('coordinator') }}">
                </div>

                <!-- Compras -->
                <div class="card my-4 card-outline" style="border-top-color: #364E76;">
                    <div class="card-header" style="background-color: #364E76; color: white;">
                        <h5 class="mb-0">COMPRAS</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="purchase_justification">JUSTIFICACIÓN DE LA COMPRA <span class="text-danger">*</span>:</label>
                            <textarea class="form-control @error('purchase_justification') is-invalid @enderror" id="purchase_justification" name="purchase_justification" rows="3" placeholder="Describa la justificación para la compra de los artículos solicitados" required>{{ old('purchase_justification') }}</textarea>
                            @error('purchase_justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Este campo es obligatorio para solicitudes de compra de materiales.
                                Para solicitudes de servicios, utilice el formulario específico de servicios.
                            </small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="purchaseItemsTable">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 5%;">ITEM</th>
                                        <th style="width: 10%;">CANT.</th>
                                        <th style="width: 35%;">DESCRIPCIÓN DEL ARTÍCULO</th>
                                        <th style="width: 25%;">UNIDAD DE MEDIDA/PRESENTACIÓN</th>
                                        <th style="width: 20%;">OBSERVACIONES</th>
                                        <th style="width: 5%;">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="purchaseItemsBody">
                                    <tr id="purchaseItem-1">
                                        <td>1</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="purchase_items[0][quantity]" min="1" placeholder="Ej: 5" required>
                                            <input type="hidden" name="purchase_items[0][item]" value="1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="purchase_items[0][description]" placeholder="Descripción detallada del artículo" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="purchase_items[0][unit]" placeholder="Ej: Unidad, caja, metro">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="purchase_items[0][observations]" placeholder="Observaciones adicionales">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger delete-row" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <button type="button" class="btn btn-sm" id="addPurchaseItem" style="background-color: #364E76; color: white;">
                                                <i class="fas fa-plus"></i> Agregar Artículo
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Configuración de Compra Compartida -->
                        <div class="card mt-3" style="border-left: 4px solid #364E76;">
                            <div class="card-header" style="background-color: #f8f9fa;">
                                <h6 class="mb-0"><i class="fas fa-share-alt mr-2"></i>Configuración de Compra Compartida</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="font-weight-bold">¿Esta compra será compartida con otra sección?</label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_shared" id="is_shared_no" value="no" checked>
                                            <label class="form-check-label" for="is_shared_no">
                                                <i class="fas fa-times text-danger mr-1"></i> No, esta compra es solo para mi sección
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_shared" id="is_shared_yes" value="yes">
                                            <label class="form-check-label" for="is_shared_yes">
                                                <i class="fas fa-check text-success mr-1"></i> Sí, esta compra será compartida
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuración de compra compartida -->
                                <div id="sharedConfig" style="display: none;">
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-user mr-1"></i> Mi Sección</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-1"><strong>Sección:</strong></p>
                                                    <p class="section-display" id="currentSection">-</p>
                                                    <p class="mb-1"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="myPercentage" name="my_percentage" min="1" max="99" value="50">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-success text-white">
                                                    <h6 class="mb-0"><i class="fas fa-users mr-1"></i> Sección Compartida</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-1"><strong>Seleccionar sección:</strong></p>
                                                    <select class="form-control" id="sharedSection" name="shared_section">
                                                        <option value="">Seleccione una sección...</option>
                                                        <option value="Preescolar y Primaria">Preescolar y Primaria</option>
                                                        <option value="Escuela Media">Escuela Media</option>
                                                        <option value="Escuela Alta / DP">Escuela Alta / DP</option>
                                                        <option value="PAI">PAI</option>
                                                        <option value="PEP">PEP</option>
                                                        <option value="Deportes">Deportes</option>
                                                        <option value="Psicología Institucional">Psicología Institucional</option>
                                                        <option value="Biblioteca">Biblioteca</option>
                                                        <option value="Dirección General">Dirección General</option>
                                                        <option value="CAS">CAS</option>
                                                        <option value="Administración">Administración</option>
                                                        <option value="Tecnología Institucional">Tecnología Institucional</option>
                                                    </select>
                                                    <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="sharedPercentage" name="shared_percentage" readonly>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón para agregar tercera sección -->
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addThirdSection">
                                            <i class="fas fa-plus mr-1"></i> Agregar otra sección
                                        </button>
                                    </div>

                                    <!-- Tercera sección compartida (oculta por defecto) -->
                                    <div id="thirdSectionConfig" style="display: none;" class="mt-3">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header bg-warning text-dark">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-users mr-1"></i> Tercera Sección Compartida
                                                        <button type="button" class="btn btn-sm btn-outline-danger float-right" id="removeThirdSection">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-1"><strong>Seleccionar sección:</strong></p>
                                                    <select class="form-control" id="thirdSharedSection" name="third_shared_section" disabled tabindex="-1">
                                                        <option value="">Seleccione una sección...</option>
                                                        <option value="Preescolar y Primaria">Preescolar y Primaria</option>
                                                        <option value="Escuela Media">Escuela Media</option>
                                                        <option value="Escuela Alta / DP">Escuela Alta / DP</option>
                                                        <option value="PAI">PAI</option>
                                                        <option value="PEP">PEP</option>
                                                        <option value="Deportes">Deportes</option>
                                                        <option value="Psicología Institucional">Psicología Institucional</option>
                                                        <option value="Biblioteca">Biblioteca</option>
                                                        <option value="Dirección General">Dirección General</option>
                                                        <option value="CAS">CAS</option>
                                                        <option value="Administración">Administración</option>
                                                        <option value="Tecnología Institucional">Tecnología Institucional</option>
                                                    </select>
                                                    <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="thirdSharedPercentage" name="third_shared_percentage" min="1" max="97" value="0" disabled tabindex="-1">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning mt-3">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        <strong>Importante:</strong> El total entre todas las secciones debe ser 100%. 
                                        Los porcentajes se ajustarán automáticamente cuando agregue más secciones.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Información importante:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Para equipos tecnológicos solicitamos consultar las especificaciones técnicas definidas por el EMC del Colegio Victoria.</li>
                                <li><strong>Para solicitudes de servicios</strong>, utilice el <a href="{{ route('purchase-requests.create-services') }}" class="alert-link">formulario específico de servicios</a>.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <table class="table table-bordered mt-4">
                    <tr>
                        <td style="width: 25%;">Estado del documento<br><small>Documento aprobado.</small></td>
                        <td style="width: 25%;">Instancia aprobatoria<br><small>Vicerrectoría administrativa</small></td>
                        <td style="width: 25%;">Fecha de control de cambios<br><small>Agosto 2024.</small></td>
                        <td style="width: 25%;">Versión del documento<br><small>V1.</small></td>
                    </tr>
                </table>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary" style="background-color: #364E76; border-color: #364E76;">
                    <i class="fas fa-save"></i> Guardar Solicitud
                </button>
                <a href="{{ route('purchase-requests.create') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para formularios grandes -->
<div class="modal fade" id="largeFormModal" tabindex="-1" role="dialog" aria-labelledby="largeFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="largeFormModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Formulario con muchos elementos
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                </div>
                <p class="text-center">
                    Se detectó un formulario con <strong><span id="itemCountDisplay"></span> elementos</strong>.
                </p>
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-6">
                            <small><strong>Elementos:</strong> <span id="itemCountDisplay2"></span></small>
                        </div>
                        <div class="col-6">
                            <small><strong>Tiempo estimado:</strong> 5-10 segundos</small>
                        </div>
                    </div>
                </div>
                <p class="text-center text-muted">
                    <small>El procesamiento puede tomar unos momentos adicionales. ¿Desea continuar?</small>
                </p>
                <div class="progress mb-3" style="display: none;" id="processingProgress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelLargeForm">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="confirmLargeForm">
                    <i class="fas fa-check mr-1"></i>
                    Continuar
                </button>
            </div>
        </div>
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
    
    .btn-primary {
        background-color: var(--institutional-blue);
        border-color: var(--institutional-blue);
    }
    
    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }
    
    .card-header {
        padding: 0.75rem 1.25rem;
    }
    
    .delete-row:hover {
        background-color: #dc3545;
        color: white;
    }
    
    .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }
    
    /* Prevenir focus en campos disabled */
    input:disabled, select:disabled {
        pointer-events: none !important;
        outline: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
    }
    
    input:disabled:focus, select:disabled:focus {
        outline: none !important;
        box-shadow: none !important;
        border-color: #ced4da !important;
    }
    
    .percentage-input {
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
    }
    
    .section-display {
        font-size: 1rem;
        font-weight: 500;
        color: #364E76;
        background-color: #f8f9fa;
        padding: 0.5rem;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
    }

    .form-check-label {
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }
    
    .form-check-label:hover {
        background-color: #f8f9fa;
    }
    
    /* Estilos para el modal de formulario grande */
    #largeFormModal .modal-header {
        border-bottom: 2px solid #ffc107;
    }
    
    #largeFormModal .modal-body {
        padding: 2rem;
    }
    
    #largeFormModal .fa-clock {
        color: #ffc107;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    #largeFormModal .progress {
        height: 8px;
        border-radius: 4px;
        background-color: #f8f9fa;
    }
    
    #largeFormModal .progress-bar {
        background: linear-gradient(45deg, #ffc107, #fd7e14);
    }
    
    #largeFormModal .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
        font-weight: 600;
    }
    
    #largeFormModal .btn-warning:hover {
        background-color: #e0a800;
        border-color: #d39e00;
        color: #212529;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        console.log('=== FORMULARIO DE COMPRA INICIALIZADO ===');
        console.log('Form action:', $('#purchaseForm').attr('action'));
        console.log('CSRF token present:', $('input[name="_token"]').length > 0);
        
        let purchaseItemCounter = 1;        // Función para agregar nuevo artículo de compra
        $('#addPurchaseItem').click(function() {
            purchaseItemCounter++;
            const newIndex = $('#purchaseItemsBody tr').length;
            const newRow = `
                <tr id="purchaseItem-${purchaseItemCounter}">
                    <td>${purchaseItemCounter}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="purchase_items[${newIndex}][quantity]" min="1" required>
                        <input type="hidden" name="purchase_items[${newIndex}][item]" value="${purchaseItemCounter}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="purchase_items[${newIndex}][description]" placeholder="Descripción del artículo" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="purchase_items[${newIndex}][unit]" placeholder="Ej: Unidad, caja, metro">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="purchase_items[${newIndex}][observations]" placeholder="Observaciones adicionales">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#purchaseItemsBody').append(newRow);
            updateDeleteButtons();
        });
        
        // Evento para eliminar fila (delegación de eventos)
        $(document).on('click', '.delete-row', function() {
            const rowCount = $('#purchaseItemsBody tr').length;
            
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                updateDeleteButtons();
                updateItemNumbers();
            } else {
                alert('Debe mantener al menos un artículo en la solicitud.');
            }
        });

        // Función para actualizar los botones de eliminar
        function updateDeleteButtons() {
            const rows = $('#purchaseItemsBody tr');
            rows.each(function(index) {
                const deleteBtn = $(this).find('.delete-row');
                if (rows.length === 1) {
                    deleteBtn.prop('disabled', true);
                } else {
                    deleteBtn.prop('disabled', false);
                }
            });
        }

        // Función para actualizar numeración de items
        function updateItemNumbers() {
            $('#purchaseItemsBody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
                $(this).attr('id', 'purchaseItem-' + (index + 1));
                
                // Actualizar nombres de los inputs
                $(this).find('input[name*="[quantity]"]').attr('name', `purchase_items[${index}][quantity]`);
                $(this).find('input[name*="[item]"]').attr('name', `purchase_items[${index}][item]`).val(index + 1);
                $(this).find('input[name*="[description]"]').attr('name', `purchase_items[${index}][description]`);
                $(this).find('input[name*="[unit]"]').attr('name', `purchase_items[${index}][unit]`);
                $(this).find('input[name*="[observations]"]').attr('name', `purchase_items[${index}][observations]`);
            });
            
            purchaseItemCounter = $('#purchaseItemsBody tr').length;
        }
        
        // Variables para manejar el formulario
        let formIsValidated = false;
        let formIsSubmitting = false;
        
        // Función para convertir purchase_items a JSON antes de enviar
        function convertPurchaseItemsToJson() {
            const purchaseItems = [];
            
            $('#purchaseItemsBody tr').each(function(index) {
                const item = {
                    item: $(this).find('input[name*="[item]"]').val(),
                    quantity: $(this).find('input[name*="[quantity]"]').val(),
                    description: $(this).find('input[name*="[description]"]').val(),
                    unit: $(this).find('input[name*="[unit]"]').val(),
                    observations: $(this).find('input[name*="[observations]"]').val()
                };
                purchaseItems.push(item);
            });
            
            // Remover todos los campos purchase_items del formulario
            $('input[name*="purchase_items"]').remove();
            
            // Agregar el JSON como un campo oculto
            $('<input>').attr({
                type: 'hidden',
                name: 'purchase_items_json',
                value: JSON.stringify(purchaseItems)
            }).appendTo('#purchaseForm');
            
            console.log('✅ Items convertidos a JSON:', purchaseItems.length, 'items');
            
            return purchaseItems.length;
        }
        
        // Prevenir envíos múltiples
        $('#purchaseForm').on('submit', function(e) {
            if (formIsSubmitting) {
                console.log('❌ Form is already submitting, preventing duplicate submission');
                e.preventDefault();
                return false;
            }
            
            console.log('🚀 Form submission initiated');
            formIsSubmitting = true;
            
            // Deshabilitar botón de envío
            $('button[type="submit"]').prop('disabled', true).text('Guardando...');
        });
        
        // Validación del formulario
        $('#purchaseForm').submit(function(e) {
            console.log('=== FORMULARIO ENVIADO ===');
            console.log('Event target:', e.target);
            console.log('Form action:', $(this).attr('action'));
            console.log('Form method:', $(this).attr('method'));
            
            let hasPurchaseItems = false;
            
            // Verificar si hay items de compra con descripción
            $('#purchaseItemsBody input[name*="[description]"]').each(function() {
                if ($(this).val().trim()) {
                    hasPurchaseItems = true;
                    return false;
                }
            });
            
            console.log('Has purchase items:', hasPurchaseItems);
            
            if (!hasPurchaseItems) {
                alert('Debe ingresar al menos un artículo de compra para la solicitud.');
                console.log('❌ Validation failed: no purchase items');
                formIsSubmitting = false;
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                e.preventDefault();
                return false;
            }
            
            // Validar justificación (ahora siempre requerida)
            const justification = $('#purchase_justification').val().trim();
            console.log('Justification:', justification);
            
            if (!justification) {
                alert('Debe ingresar una justificación para la compra.');
                $('#purchase_justification').focus();
                console.log('❌ Validation failed: no justification');
                formIsSubmitting = false;
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                e.preventDefault();
                return false;
            }

            // Validar sección/área
            const sectionArea = $('#section_area').val();
            console.log('Section area:', sectionArea);
            
            if (!sectionArea) {
                alert('Debe seleccionar una sección y/o área.');
                $('#section_area').focus();
                console.log('❌ Validation failed: no section area');
                formIsSubmitting = false;
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                e.preventDefault();
                return false;
            }

            console.log('✅ All validations passed, submitting form...');
            
            // DIAGNÓSTICO ESPECIAL PARA FORMULARIOS GRANDES
            const itemCount = $('input[name*="[description]"]').length;
            const totalInputs = $('input, select, textarea').length;
            
            console.log('🔍 DIAGNÓSTICO DE ENVÍO:');
            console.log('- Cantidad de ítems:', itemCount);
            console.log('- Total de inputs:', totalInputs);
            
            // SOLUCIÓN: Siempre convertir a JSON para evitar problemas con max_input_vars
            console.log('🔄 Convirtiendo items a JSON...');
            convertPurchaseItemsToJson();
            
            // Si tiene más de 20 ítems, mostrar modal de advertencia
            if (itemCount > 20) {
                console.log('⚠️ FORMULARIO GRANDE DETECTADO - ' + itemCount + ' ítems');
                
                // Mostrar modal en lugar de alert
                $('#itemCountDisplay').text(itemCount);
                $('#itemCountDisplay2').text(itemCount);
                $('#largeFormModal').modal('show');
                
                // Prevenir envío hasta que el usuario confirme
                console.log('❌ Mostrando modal de confirmación para formulario grande');
                formIsSubmitting = false;
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                e.preventDefault();
                return false;
            }
            
            console.log('📤 Enviando formulario al servidor...');
            
            // Validar configuración de compra compartida se maneja más abajo en el nuevo código
            
            // Si todas las validaciones pasaron, permitir el envío
            return true;
        });

        // Función para actualizar la sección actual cuando cambie
        $('#section_area').change(function() {
            const currentSection = $(this).find('option:selected').text();
            $('#currentSection').text(currentSection || '-');
            
            // Filtrar la sección actual del dropdown de sección compartida
            $('#sharedSection option').show();
            $('#sharedSection option').each(function() {
                if ($(this).val() === $('#section_area').val()) {
                    $(this).hide();
                }
            });
            
            // Reset de sección compartida si está seleccionada la misma
            if ($('#sharedSection').val() === $('#section_area').val()) {
                $('#sharedSection').val('');
            }
        });

        // Manejar cambio en la pregunta inicial
        $('input[name="is_shared"]').change(function() {
            if ($(this).val() === 'yes') {
                $('#sharedConfig').slideDown();
            } else {
                $('#sharedConfig').slideUp();
                // Limpiar también los campos de tercera sección cuando se deshabilita la compra compartida
                $('#thirdSectionConfig').hide();
                $('#addThirdSection').show();
                
                // Deshabilitar y limpiar los campos de la tercera sección
                $('#thirdSharedSection').prop('disabled', true).attr('tabindex', '-1').val('');
                $('#thirdSharedPercentage').prop('disabled', true).attr('tabindex', '-1').val(0);
            }
        });

        // Calcular porcentaje automáticamente para dos secciones
        $('#myPercentage').on('input', function() {
            calculatePercentages();
        });

        // Manejar cambios en el porcentaje de la tercera sección
        $('#thirdSharedPercentage').on('input', function() {
            calculatePercentages();
        });

        // Función para calcular porcentajes automáticamente
        function calculatePercentages() {
            const hasThirdSection = $('#thirdSectionConfig').is(':visible');
            const myPercentage = parseInt($('#myPercentage').val()) || 0;
            
            if (hasThirdSection) {
                const thirdPercentage = parseInt($('#thirdSharedPercentage').val()) || 0;
                const sharedPercentage = 100 - myPercentage - thirdPercentage;
                
                // Validar que los porcentajes sean válidos
                if (myPercentage + thirdPercentage > 99) {
                    // Ajustar automáticamente
                    const newThirdPercentage = 99 - myPercentage;
                    $('#thirdSharedPercentage').val(Math.max(1, newThirdPercentage));
                    $('#sharedPercentage').val(1);
                } else if (sharedPercentage < 1) {
                    $('#sharedPercentage').val(1);
                } else {
                    $('#sharedPercentage').val(sharedPercentage);
                }
            } else {
                // Solo dos secciones
                const sharedPercentage = 100 - myPercentage;
                
                if (myPercentage < 1) {
                    $('#myPercentage').val(1);
                    $('#sharedPercentage').val(99);
                } else if (myPercentage > 99) {
                    $('#myPercentage').val(99);
                    $('#sharedPercentage').val(1);
                } else {
                    $('#sharedPercentage').val(sharedPercentage);
                }
            }
        }

        // Manejar agregar tercera sección
        $('#addThirdSection').click(function() {
            $('#thirdSectionConfig').slideDown();
            $(this).hide();
            
            // Habilitar los campos de la tercera sección
            $('#thirdSharedSection').prop('disabled', false).removeAttr('tabindex');
            $('#thirdSharedPercentage').prop('disabled', false).removeAttr('tabindex');
            
            // Redistribuir porcentajes equitativamente
            const newPercentage = Math.floor(100 / 3);
            $('#myPercentage').val(newPercentage);
            $('#sharedPercentage').val(newPercentage);
            $('#thirdSharedPercentage').val(100 - (newPercentage * 2));
            
            updateSectionFilters();
        });

        // Manejar remover tercera sección
        $('#removeThirdSection').click(function() {
            $('#thirdSectionConfig').slideUp();
            $('#addThirdSection').show();
            
            // Deshabilitar y limpiar los campos de la tercera sección
            $('#thirdSharedSection').prop('disabled', true).attr('tabindex', '-1');
            $('#thirdSharedPercentage').prop('disabled', true).attr('tabindex', '-1');
            
            // Resetear a dos secciones (50-50)
            $('#myPercentage').val(50);
            $('#sharedPercentage').val(50);
            $('#thirdSharedPercentage').val(0);
            $('#thirdSharedSection').val('');
            
            updateSectionFilters();
        });

        // Función para actualizar filtros de secciones
        function updateSectionFilters() {
            const currentSection = $('#section_area').val();
            const sharedSection = $('#sharedSection').val();
            const thirdSection = $('#thirdSharedSection').val();
            
            // Filtrar opciones para evitar duplicados
            $('#sharedSection option, #thirdSharedSection option').show();
            
            $('#sharedSection option').each(function() {
                if ($(this).val() === currentSection || $(this).val() === thirdSection) {
                    $(this).hide();
                }
            });
            
            $('#thirdSharedSection option').each(function() {
                if ($(this).val() === currentSection || $(this).val() === sharedSection) {
                    $(this).hide();
                }
            });
        }

        // Manejar cambios en las secciones para filtrar duplicados
        $('#section_area, #sharedSection, #thirdSharedSection').change(function() {
            updateSectionFilters();
            
            if ($(this).is('#section_area')) {
                const currentSection = $(this).find('option:selected').text();
                $('#currentSection').text(currentSection || '-');
            }
        });

        // Actualizar validación del formulario
        $('#purchaseForm').submit(function(e) {
            console.log('=== SEGUNDO MANEJADOR DE SUBMIT ===');
            
            // Antes de validar, asegurar que los campos estén visibles y habilitados si tienen datos
            const hasThirdSectionData = $('#thirdSharedSection').val() || 
                                       parseInt($('#thirdSharedPercentage').val()) > 0;
            
            if (hasThirdSectionData && $('#thirdSectionConfig').is(':hidden')) {
                $('#thirdSectionConfig').show();
                $('#addThirdSection').hide();
                $('#thirdSharedSection').prop('disabled', false).removeAttr('tabindex');
                $('#thirdSharedPercentage').prop('disabled', false).removeAttr('tabindex');
            }
            
            // Antes del envío, habilitar temporalmente todos los campos para que se envíen sus valores
            const wasDisabled = {
                section: $('#thirdSharedSection').prop('disabled'),
                percentage: $('#thirdSharedPercentage').prop('disabled')
            };
            
            if (hasThirdSectionData) {
                $('#thirdSharedSection').prop('disabled', false);
                $('#thirdSharedPercentage').prop('disabled', false);
            }
            
            // Validar configuración de compra compartida
            const isShared = $('input[name="is_shared"]:checked').val();
            if (isShared === 'yes') {
                const sharedSection = $('#sharedSection').val();
                if (!sharedSection) {
                    alert('Por favor seleccione la segunda sección para compartir esta compra.');
                    $('#sharedSection').focus();
                    console.log('❌ Shared purchase validation failed: no shared section');
                    formIsSubmitting = false;
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                    e.preventDefault();
                    return false;
                }
                
                // Si la tercera sección está visible, validar que esté seleccionada
                if ($('#thirdSectionConfig').is(':visible')) {
                    const thirdSection = $('#thirdSharedSection').val();
                    if (!thirdSection) {
                        alert('Por favor seleccione la tercera sección o remueva la opción de tercera sección.');
                        $('#thirdSharedSection').focus();
                        console.log('❌ Third section validation failed');
                        formIsSubmitting = false;
                        $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                        e.preventDefault();
                        return false;
                    }
                }
                
                // Validar que los porcentajes sumen 100%
                const myPercentage = parseInt($('#myPercentage').val()) || 0;
                const sharedPercentage = parseInt($('#sharedPercentage').val()) || 0;
                const thirdPercentage = parseInt($('#thirdSharedPercentage').val()) || 0;
                const total = myPercentage + sharedPercentage + thirdPercentage;
                
                if (total !== 100) {
                    alert(`Los porcentajes deben sumar exactamente 100%. Actualmente suman ${total}%.`);
                    console.log('❌ Percentage validation failed:', total);
                    formIsSubmitting = false;
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                    e.preventDefault();
                    return false;
                }
            }
            
            console.log('✅ All shared purchase validations passed');
            return true;
        });

        // Inicializar botones
        updateDeleteButtons();
        
        // Inicializar la sección actual en el display
        const initialSection = $('#section_area option:selected').text();
        $('#currentSection').text(initialSection || '-');
        
        // Establecer valores iniciales para compra compartida
        $('#sharedPercentage').val(50);
        updateSectionFilters();
        
        // Manejar errores de validación en campos ocultos
        @if($errors->has('third_shared_percentage') && old('third_shared_section'))
            // Si hay error en third_shared_percentage y hay valor en third_shared_section,
            // mostrar la tercera sección automáticamente y habilitar los campos
            $('#thirdSectionConfig').show();
            $('#addThirdSection').hide();
            $('#thirdSharedSection').prop('disabled', false).removeAttr('tabindex');
            $('#thirdSharedPercentage').prop('disabled', false).removeAttr('tabindex');
        @endif
        
        // Evitar focus automático en campos ocultos
        $(document).ready(function() {
            // Event handlers para el modal de formulario grande
            $('#confirmLargeForm').click(function() {
                console.log('✅ Usuario confirmó el envío del formulario grande');
                
                // Cambiar el contenido del modal para mostrar progreso
                $('#largeFormModal .modal-title').html('<i class="fas fa-spinner fa-spin mr-2"></i>Procesando solicitud...');
                $('#largeFormModal .modal-body').html(`
                    <div class="text-center">
                        <div class="spinner-border text-warning mb-3" role="status">
                            <span class="sr-only">Procesando...</span>
                        </div>
                        <p>Enviando solicitud al servidor...</p>
                        <p class="text-muted"><small>Por favor no cierre esta ventana.</small></p>
                    </div>
                `);
                $('#largeFormModal .modal-footer').hide();
                
                // SOLUCIÓN MEJORADA: Enviar usando AJAX para manejar mejor los formularios grandes
                setTimeout(function() {
                    const form = $('#purchaseForm')[0];
                    const formData = new FormData(form);
                    
                    console.log('📤 Enviando formulario grande via AJAX...');
                    console.log('- Total de campos FormData:', Array.from(formData.entries()).length);
                    
                    // Verificar si ya está en formato JSON
                    if (!formData.has('purchase_items_json')) {
                        console.log('⚠️ Items aún no convertidos a JSON, convirtiendo ahora...');
                        
                        // SOLUCIÓN: Convertir purchase_items a JSON para evitar max_input_vars
                        const purchaseItems = [];
                        
                        // Extraer los items del FormData
                        $('#purchaseItemsBody tr').each(function(index) {
                            const item = {
                                item: $(this).find('input[name*="[item]"]').val(),
                                quantity: $(this).find('input[name*="[quantity]"]').val(),
                                description: $(this).find('input[name*="[description]"]').val(),
                                unit: $(this).find('input[name*="[unit]"]').val(),
                                observations: $(this).find('input[name*="[observations]"]').val()
                            };
                            purchaseItems.push(item);
                        });
                        
                        // Eliminar todos los campos purchase_items del FormData
                        const keysToDelete = [];
                        for (let key of formData.keys()) {
                            if (key.includes('purchase_items')) {
                                keysToDelete.push(key);
                            }
                        }
                        keysToDelete.forEach(key => formData.delete(key));
                        
                        // Agregar los items como JSON
                        formData.append('purchase_items_json', JSON.stringify(purchaseItems));
                        
                        console.log('✅ Items convertidos a JSON:', purchaseItems.length, 'items');
                    } else {
                        console.log('✅ Items ya están en formato JSON');
                    }
                    
                    console.log('- Nuevo total de campos FormData:', Array.from(formData.entries()).length);
                    
                    $.ajax({
                        url: $(form).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        timeout: 60000, // 60 segundos timeout
                        success: function(response, status, xhr) {
                            console.log('✅ Formulario enviado exitosamente');
                            $('#largeFormModal').modal('hide');
                            
                            // Si la respuesta es una redirección HTML, navegar manualmente
                            if (xhr.getResponseHeader('content-type')?.includes('text/html')) {
                                window.location.href = '/purchase-requests';
                            } else if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                // Fallback - recargar la página con success message
                                window.location.href = '/purchase-requests?success=Solicitud creada exitosamente';
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('❌ Error al enviar formulario:', error);
                            console.error('Status:', status);
                            console.error('Response:', xhr.responseText);
                            
                            $('#largeFormModal').modal('hide');
                            
                            // Mostrar error específico si está disponible
                            let errorMessage = 'Error al crear la solicitud. Por favor, inténtelo nuevamente.';
                            
                            if (xhr.status === 422) {
                                errorMessage = 'Hay errores en el formulario. Por favor, revise los datos ingresados.';
                            } else if (xhr.status === 419) {
                                errorMessage = 'Su sesión ha expirado. La página se recargará automáticamente.';
                                setTimeout(() => window.location.reload(), 2000);
                            }
                            
                            alert(errorMessage);
                            
                            // Restaurar el botón de envío
                            formIsSubmitting = false;
                            $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
                        }
                    });
                }, 1000);
            });
            
            $('#cancelLargeForm').click(function() {
                console.log('❌ Usuario canceló el envío del formulario grande');
                $('#largeFormModal').modal('hide');
                formIsSubmitting = false;
                $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Solicitud');
            });
            
            // Interceptar eventos de focus en campos disabled
            $(document).on('focus', 'input:disabled, select:disabled', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).blur();
                return false;
            });
            
            // Verificar si hay errores de validación al cargar la página
            if ($('.alert-danger').length > 0) {
                // Si third_shared_percentage tiene error pero el campo está oculto, mostrar la sección
                if ($('input[name="third_shared_percentage"]').closest('#thirdSectionConfig').is(':hidden')) {
                    var hasThirdSectionData = $('select[name="third_shared_section"]').val() || 
                                            $('input[name="third_shared_percentage"]').val() > 0;
                    if (hasThirdSectionData) {
                        $('#thirdSectionConfig').show();
                        $('#addThirdSection').hide();
                        $('#thirdSharedSection').prop('disabled', false).removeAttr('tabindex');
                        $('#thirdSharedPercentage').prop('disabled', false).removeAttr('tabindex');
                    }
                }
            }
        });
    });
</script>
@stop