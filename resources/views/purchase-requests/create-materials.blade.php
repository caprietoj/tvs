@extends('adminlte::page')

@section('title', 'Solicitud de Materiales')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-box mr-2"></i>Solicitud de Materiales</h1>
@stop

@section('content')
<div class="container">
    <!-- Modal para productos sin stock -->
    <div class="modal fade" id="outOfStockModal" tabindex="-1" role="dialog" aria-labelledby="outOfStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #dc3545; color: white;">
                    <h5 class="modal-title" id="outOfStockModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Productos sin Stock Disponible
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>¡Atención!</strong> Los siguientes productos no tienen stock disponible:
                    </div>
                    <ul id="outOfStockList" class="list-group mb-3">
                        <!-- Se llenará dinámicamente -->
                    </ul>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Recomendación:</strong> Para obtener estos productos, deberá crear una Solicitud de Compra siguiendo el procedimiento establecido.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <a href="{{ route('purchase-requests.create-purchase') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-cart mr-2"></i>Crear Solicitud de Compra
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de advertencia de stock -->
    <div class="modal fade" id="stockWarningModal" tabindex="-1" role="dialog" aria-labelledby="stockWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #ffc107; color: #212529;">
                    <h5 class="modal-title" id="stockWarningModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Advertencia de Stock
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>¡Atención!</strong> Los siguientes productos exceden el stock disponible:
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad Solicitada</th>
                                    <th>Stock Disponible</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="stockWarningTableBody">
                                <!-- Se llenará dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>¿Qué sucederá?</strong> Si continúa, se procesarán únicamente los productos con stock suficiente. Los productos sin stock se excluirán y recibirá una notificación por correo.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar y Revisar
                    </button>
                    <button type="button" class="btn btn-warning" id="continueWithWarnings">
                        <i class="fas fa-check mr-2"></i>Continuar de Todos Modos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline" style="border-top-color: #364E76;">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Formato Orden de Materiales</h3>
        </div>
        
        <form action="{{ route('purchase-requests.store') }}" method="POST" id="materialsForm">
            @csrf
            <input type="hidden" name="type" value="materials">
            
            <div class="card-body">
                <!-- Cabecera del formato -->
                <div class="text-center mb-3">
                    <h4 class="font-weight-bold" style="color: #364E76;">GESTIÓN ADMINISTRATIVA Y FINANCIERA</h4>
                    <h4 class="font-weight-bold" style="color: #364E76;">COLEGIO VICTORIA SAS</h4>
                    <div>FORMATO ORDEN DE MATERIALES A.ADM-71</div>
                </div>

                <!-- Datos del usuario -->
                <table class="table table-bordered mb-4">
                    <tr>
                        <td style="width: 20%;">FECHA DE SOLICITUD:</td>
                        <td style="width: 30%;">
                            <input type="date" class="form-control" name="request_date" value="{{ date('Y-m-d') }}" readonly>
                        </td>
                        <td style="width: 20%;">DATOS DEL USUARIO</td>
                        <td style="width: 30%;"></td>
                    </tr>
                    <tr>
                        <td>SOLICITANTE:</td>
                        <td>
                            <input type="text" class="form-control @error('requester') is-invalid @enderror" name="requester" value="{{ $user->name }}" readonly>
                            @error('requester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>CÓDIGO:</td>
                        <td>
                            <input type="text" class="form-control" name="code" value="{{ old('code') }}">
                        </td>
                    </tr>
                    <tr>
                        <td>GRADO:</td>
                        <td>
                            <input type="text" class="form-control" name="grade" value="{{ old('grade') }}">
                        </td>
                        <td>SECCIÓN:</td>
                        <td>
                            <select class="form-control @error('section') is-invalid @enderror" name="section">
                                <option value="">Seleccione...</option>
                                <option value="Preescolar y Primaria" {{ old('section') == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                                <option value="Escuela Media" {{ old('section') == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                                <option value="Escuela Alta / DP" {{ old('section') == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                                <option value="PAI" {{ old('section') == 'PAI' ? 'selected' : '' }}>PAI</option>
                                <option value="PEP" {{ old('section') == 'PEP' ? 'selected' : '' }}>PEP</option>
                                <option value="Deportes" {{ old('section') == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                                <option value="Psicología Institucional" {{ old('section') == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                                <option value="Biblioteca" {{ old('section') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                                <option value="Dirección General" {{ old('section') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                                <option value="CAS" {{ old('section') == 'CAS' ? 'selected' : '' }}>CAS</option>
                                <option value="Administración" {{ old('section') == 'Administración' ? 'selected' : '' }}>Administración</option>
                                <option value="Tecnología Institucional" {{ old('section') == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                            </select>
                            @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>FECHA DE ENTREGA:</td>
                        <td>
                            <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" name="delivery_date" value="{{ old('delivery_date') }}">
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>RECIBIDO A SATISFACCIÓN</td>
                        <td></td>
                    </tr>
                </table>

                <!-- Materiales y papelería -->
                <div class="card my-4 card-outline" style="border-top-color: #364E76;">
                    <div class="card-header" style="background-color: #364E76; color: white;">
                        <h5 class="mb-0">MATERIALES Y PAPELERÍA</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="materialItemsTable">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 10%;">N</th>
                                        <th style="width: 55%;">ARTÍCULOS</th>
                                        <th style="width: 15%;">CANTIDAD</th>
                                        <th style="width: 15%;">OBJETIVO</th>
                                        <th style="width: 5%;">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="materialItemsBody">
                                    <tr id="materialItem-1">
                                        <td>1</td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control form-control-sm product-input" 
                                                   name="material_items[0][article]"
                                                   list="products-datalist-0"
                                                   placeholder="Escriba para buscar producto..."
                                                   autocomplete="off"
                                                   data-stock-info="">
                                            <datalist id="products-datalist-0">
                                                @foreach($inventoryItems as $item)
                                                    <option value="{{ $item->producto }}" data-stock="{{ $item->stock }}">
                                                        {{ $item->producto }} (Stock: {{ $item->stock }})
                                                    </option>
                                                @endforeach
                                            </datalist>
                                            <input type="hidden" name="material_items[0][item]" value="1">
                                            <small class="text-muted stock-info" style="display: none;">
                                                <i class="fas fa-box mr-1"></i>Stock disponible: <span class="stock-amount">0</span>
                                            </small>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="material_items[0][quantity]" min="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="material_items[0][objective]">
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
                                        <td colspan="5" class="text-center">
                                            <button type="button" class="btn btn-sm" id="addMaterialItem" style="background-color: #364E76; color: white;">
                                                <i class="fas fa-plus"></i> Agregar
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <small>NOTA: Puede añadir todos los materiales que necesite utilizando el botón "Agregar"</small>
                        </div>
                    </div>
                </div>

                <!-- Firmas -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="border p-2 text-center" style="height: 60px;">
                            FIRMA AUTORIZACIÓN PRESUPUESTO O SECCIÓN
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border p-2 text-center" style="height: 60px;">
                            FIRMA AUTORIZACIÓN PRESUPUESTO O SECCIÓN
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn" style="background-color: #364E76; color: white;">
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
    
    .delete-row:hover {
        background-color: #dc3545;
        color: white;
    }
    
    .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }
    
    .btn-primary {
        background-color: var(--institutional-blue);
        border-color: var(--institutional-blue);
    }
    
    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }
    
    /* Estilos para el sistema de datalist */
    .product-input {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    
    .product-input:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        outline: none;
    }
    
    .product-input.valid-selection {
        background-color: #d4edda;
        border-color: #28a745;
    }
    
    .product-input.invalid-selection {
        background-color: #f8d7da;
        border-color: #dc3545;
    }
    
    /* Estilos para información de stock */
    .stock-info {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .stock-info .fas {
        font-size: 0.7rem;
    }
    
    .stock-info .badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }
    
    /* Estilos para validación de cantidad */
    .border-warning {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.1rem rgba(255, 193, 7, 0.25);
    }
    
    .border-danger {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.25);
    }
    
    /* Mejorar visualización del datalist */
    .product-input {
        position: relative;
    }
    
    /* Estilos para tooltips de cantidad */
    input[title] {
        cursor: help;
    }
    
    /* Estilos para modales */
    .modal-header {
        border-bottom: 1px solid #dee2e6;
    }
    
    .modal-footer {
        border-top: 1px solid #dee2e6;
    }
    
    /* Estilos para tabla de advertencias */
    .table-responsive {
        border-radius: 0.25rem;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .table td {
        font-size: 0.875rem;
        vertical-align: middle;
    }
    
    /* Mejoras visuales para filas de advertencia */
    .table-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .table-danger {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        console.log('🚀 Sistema de autocompletado con datalist iniciado');
        
        // Mostrar modal si hay productos sin stock (desde sesión de error)
        @if(session('out_of_stock_error'))
            const outOfStockItems = @json(session('out_of_stock_error'));
            showOutOfStockModal(outOfStockItems);
        @endif
        
        // Lista de productos válidos para validación con información de stock
        const validProducts = [
            @foreach($inventoryItems as $item)
                "{{ addslashes($item->producto) }}",
            @endforeach
        ];
        
        // Mapa de productos con su stock para referencia rápida
        const productStockMap = {
            @foreach($inventoryItems as $item)
                "{{ addslashes($item->producto) }}": {{ $item->stock }},
            @endforeach
        };
        
        console.log('📦 Total productos válidos:', validProducts.length);
        
        let itemCounter = 1;
        
        // Función para mostrar modal de productos sin stock
        function showOutOfStockModal(outOfStockItems) {
            const listElement = $('#outOfStockList');
            listElement.empty();
            
            outOfStockItems.forEach(function(item) {
                listElement.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>${item}</strong></span>
                        <span class="badge badge-danger badge-pill">Sin Stock</span>
                    </li>
                `);
            });
            
            $('#outOfStockModal').modal('show');
        }
        
        // Función para mostrar modal de advertencias de stock
        function showStockWarningModal(stockWarningMessages) {
            const tableBody = $('#stockWarningTableBody');
            tableBody.empty();
            
            stockWarningMessages.forEach(function(item) {
                const parts = item.split(': solicitando ');
                const product = parts[0];
                const quantityPart = parts[1];
                const quantities = quantityPart.split(', disponible ');
                const requested = quantities[0];
                const available = quantities[1];
                
                let statusBadge = '';
                let statusClass = '';
                
                if (parseInt(available) === 0) {
                    statusBadge = '<span class="badge badge-danger">Sin Stock</span>';
                    statusClass = 'table-danger';
                } else {
                    statusBadge = '<span class="badge badge-warning">Stock Insuficiente</span>';
                    statusClass = 'table-warning';
                }
                
                tableBody.append(`
                    <tr class="${statusClass}">
                        <td><strong>${product}</strong></td>
                        <td class="text-center">${requested}</td>
                        <td class="text-center">${available}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `);
            });
            
            $('#stockWarningModal').modal('show');
        }
        
        // Función para validar selección de producto y mostrar stock
        function validateProduct(input) {
            const value = input.val().trim();
            const isValid = validProducts.includes(value);
            const stockInfo = input.siblings('.stock-info');
            const stockAmount = stockInfo.find('.stock-amount');
            
            input.removeClass('valid-selection invalid-selection');
            
            if (value === '') {
                // Campo vacío, ocultar información de stock
                stockInfo.hide();
                return false;
            } else if (isValid) {
                input.addClass('valid-selection');
                
                // Mostrar información de stock
                const stock = productStockMap[value] || 0;
                stockAmount.text(stock);
                stockInfo.show();
                
                // Cambiar color según el stock
                if (stock === 0) {
                    stockInfo.removeClass('text-muted text-warning').addClass('text-danger');
                    stockAmount.html('<strong>' + stock + '</strong> <span class="badge badge-danger badge-sm">Sin Stock</span>');
                } else if (stock <= 5) {
                    stockInfo.removeClass('text-muted text-danger').addClass('text-warning');
                    stockAmount.html('<strong>' + stock + '</strong> <span class="badge badge-warning badge-sm">Stock Bajo</span>');
                } else {
                    stockInfo.removeClass('text-danger text-warning').addClass('text-success');
                    stockAmount.html('<strong>' + stock + '</strong> <span class="badge badge-success badge-sm">Disponible</span>');
                }
                
                console.log('✅ Producto válido:', value, 'Stock:', stock);
                return true;
            } else {
                input.addClass('invalid-selection');
                stockInfo.hide();
                console.log('❌ Producto no válido:', value);
                return false;
            }
        }
        
        // Evento para validar productos en tiempo real
        $(document).on('input change blur', '.product-input', function() {
            validateProduct($(this));
        });
        
        // Validación de cantidad en tiempo real
        $(document).on('input change', 'input[name*="[quantity]"]', function() {
            const quantityInput = $(this);
            const productInput = quantityInput.closest('tr').find('.product-input');
            const productValue = productInput.val().trim();
            const quantity = parseInt(quantityInput.val()) || 0;
            
            if (productValue && validProducts.includes(productValue)) {
                const availableStock = productStockMap[productValue] || 0;
                
                // Remover clases anteriores
                quantityInput.removeClass('border-warning border-danger');
                
                if (quantity > availableStock) {
                    quantityInput.addClass('border-danger');
                    quantityInput.attr('title', `Cantidad excede el stock disponible (${availableStock})`);
                } else if (quantity === availableStock && quantity > 0) {
                    quantityInput.addClass('border-warning');
                    quantityInput.attr('title', `Utilizará todo el stock disponible (${availableStock})`);
                } else {
                    quantityInput.removeAttr('title');
                }
            }
        });
        
        // Función para crear datalist único con información de stock
        function createUniqueDatalist(index) {
            const datalistId = `products-datalist-${index}`;
            const options = [
                @foreach($inventoryItems as $item)
                    '<option value="{{ addslashes($item->producto) }}" data-stock="{{ $item->stock }}">{{ addslashes($item->producto) }} (Stock: {{ $item->stock }})</option>',
                @endforeach
            ].join('');
            
            return `<datalist id="${datalistId}">${options}</datalist>`;
        }
        
        // Función para agregar nuevo material
        $('#addMaterialItem').click(function() {
            itemCounter++;
            const newIndex = $('#materialItemsBody tr').length;
            const datalistId = `products-datalist-${newIndex}`;
            
            console.log('➕ Agregando material #' + itemCounter);
            
            const newRow = `
                <tr id="materialItem-${itemCounter}">
                    <td>${itemCounter}</td>
                    <td>
                        <input type="text" 
                               class="form-control form-control-sm product-input" 
                               name="material_items[${newIndex}][article]"
                               list="${datalistId}"
                               placeholder="Escriba para buscar producto..."
                               autocomplete="off"
                               data-stock-info="">
                        ${createUniqueDatalist(newIndex)}
                        <input type="hidden" name="material_items[${newIndex}][item]" value="${itemCounter}">
                        <small class="text-muted stock-info" style="display: none;">
                            <i class="fas fa-box mr-1"></i>Stock disponible: <span class="stock-amount">0</span>
                        </small>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="material_items[${newIndex}][quantity]" min="0">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="material_items[${newIndex}][objective]">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#materialItemsBody').append(newRow);
            console.log('✅ Material #' + itemCounter + ' agregado con datalist');
        });
        
        // Eliminar fila
        $(document).on('click', '.delete-row', function() {
            const rowCount = $('#materialItemsBody tr').length;
            
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                
                // Renumerar filas
                $('#materialItemsBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
                
                console.log('🗑️ Fila eliminada');
            } else {
                alert('Debe mantener al menos un ítem en la tabla.');
            }
        });
        
        // Variable para controlar si se debe enviar el formulario
        let allowFormSubmission = false;
        
        // Validación del formulario con verificación de stock
        $('#materialsForm').submit(function(e) {
            // Si ya se autorizó el envío, permitir continuar
            if (allowFormSubmission) {
                console.log('✅ Formulario autorizado, enviando...');
                return true;
            }
            
            let hasValidItems = false;
            let hasInvalidItems = false;
            let hasStockWarnings = false;
            let stockWarningMessages = [];
            
            $('.product-input').each(function() {
                const productValue = $(this).val().trim();
                const quantityInput = $(this).closest('tr').find('input[name*="[quantity]"]');
                const quantity = parseInt(quantityInput.val()) || 0;
                
                if (productValue !== '') {
                    const isValidProduct = validProducts.includes(productValue);
                    
                    if (!isValidProduct) {
                        hasInvalidItems = true;
                        $(this).addClass('invalid-selection');
                        console.log('❌ Producto inválido:', productValue);
                    } else if (quantity > 0) {
                        hasValidItems = true;
                        
                        // Verificar stock
                        const availableStock = productStockMap[productValue] || 0;
                        if (quantity > availableStock) {
                            hasStockWarnings = true;
                            stockWarningMessages.push(`${productValue}: solicitando ${quantity}, disponible ${availableStock}`);
                        }
                        
                        console.log('✅ Item válido:', productValue, 'Cantidad:', quantity, 'Stock:', availableStock);
                    }
                }
            });
            
            if (hasInvalidItems) {
                e.preventDefault();
                alert('Algunos productos seleccionados no son válidos. Por favor, elija productos de la lista de sugerencias.');
                console.log('❌ Hay productos inválidos');
                return false;
            }
            
            if (!hasValidItems) {
                e.preventDefault();
                alert('Debe seleccionar al menos un producto válido y especificar una cantidad mayor a 0.');
                console.log('❌ No hay items válidos');
                return false;
            }
            
            // Mostrar modal de advertencias si hay problemas de stock
            if (hasStockWarnings) {
                e.preventDefault();
                showStockWarningModal(stockWarningMessages);
                console.log('⚠️ Mostrando modal de advertencias de stock');
                return false;
            }
            
            console.log('✅ Formulario válido, enviando...');
            return true;
        });
        
        // Manejar click del botón "Continuar de Todos Modos"
        $('#continueWithWarnings').on('click', function() {
            allowFormSubmission = true;
            $('#stockWarningModal').modal('hide');
            
            // Esperar a que se cierre el modal y luego enviar el formulario
            $('#stockWarningModal').on('hidden.bs.modal', function() {
                console.log('🚀 Enviando formulario con advertencias aceptadas');
                $('#materialsForm').submit();
            });
        });
        
        // Validar el primer producto al cargar la página
        validateProduct($('.product-input').first());
    });
</script>
@stop