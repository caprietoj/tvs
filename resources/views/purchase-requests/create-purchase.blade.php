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
                            <option value="Pre Escolar" {{ old('section_area') == 'Pre Escolar' ? 'selected' : '' }}>Pre Escolar</option>
                            <option value="Primaria" {{ old('section_area') == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                            <option value="Bachillerato" {{ old('section_area') == 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                            <option value="PEP" {{ old('section_area') == 'PEP' ? 'selected' : '' }}>PEP</option>
                            <option value="PAI" {{ old('section_area') == 'PAI' ? 'selected' : '' }}>PAI</option>
                            <option value="Diploma" {{ old('section_area') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                            <option value="Administración" {{ old('section_area') == 'Administración' ? 'selected' : '' }}>Administración</option>
                            <option value="Dirección General" {{ old('section_area') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                            <option value="CAS" {{ old('section_area') == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="Departamento de Apoyo" {{ old('section_area') == 'Departamento de Apoyo' ? 'selected' : '' }}>Departamento de Apoyo</option>
                            <option value="Biblioteca" {{ old('section_area') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
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
</style>
@stop

@section('js')
<script>
    $(function() {
        // Variable para contador de filas
        let purchaseItemCounter = 1;
        
        // Función para agregar nuevo artículo de compra
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
        
        // Validación del formulario
        $('#purchaseForm').submit(function(e) {
            let hasPurchaseItems = false;
            
            // Verificar si hay items de compra con descripción
            $('#purchaseItemsBody input[name*="[description]"]').each(function() {
                if ($(this).val().trim()) {
                    hasPurchaseItems = true;
                    return false;
                }
            });
            
            if (!hasPurchaseItems) {
                e.preventDefault();
                alert('Debe ingresar al menos un artículo de compra para la solicitud.');
                return false;
            }
            
            // Validar justificación (ahora siempre requerida)
            if (!$('#purchase_justification').val().trim()) {
                e.preventDefault();
                alert('Debe ingresar una justificación para la compra.');
                $('#purchase_justification').focus();
                return false;
            }

            // Validar sección/área
            if (!$('#section_area').val()) {
                e.preventDefault();
                alert('Debe seleccionar una sección y/o área.');
                $('#section_area').focus();
                return false;
            }
            
            return true;
        });

        // Inicializar botones
        updateDeleteButtons();
    });
</script>
@stop