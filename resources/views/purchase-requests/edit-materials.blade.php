@extends('adminlte::page')

@section('title', 'Editar Solicitud de Materiales y/o Fotocopias')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-copy mr-2"></i>Editar Solicitud de Materiales y/o Fotocopias</h1>
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
        console.log('🚀 Sistema de autocompletado con datalist iniciado (Edición)');
        
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
        
        let itemCounter = {{ count($purchaseRequest->material_items ?? []) }};
        
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
        
        // Validar productos existentes al cargar la página
        $('.product-input').each(function() {
            validateProduct($(this));
        });
    });
</script>
@stop

@section('css')
<style>
    /* Estilos para validación de productos */
    .product-input.valid-selection {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    }
    
    .product-input.invalid-selection {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    .stock-info {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    
    .stock-info.text-danger {
        color: #dc3545 !important;
    }
    
    .stock-info.text-warning {
        color: #ffc107 !important;
    }
    
    .stock-info.text-success {
        color: #28a745 !important;
    }
    
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

@section('content')
<div class="container">
    <div class="card card-outline" style="border-top-color: #364E76;">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Formato Orden de Materiales y/o Fotocopias</h3>
        </div>
        
        <form action="{{ route('purchase-requests.update', $purchaseRequest->id) }}" method="POST" id="materialsForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="materials">
            
            <div class="card-body">
                <!-- Cabecera del formato -->
                <div class="text-center mb-3">
                    <h4 class="font-weight-bold" style="color: #364E76;">GESTIÓN ADMINISTRATIVA Y FINANCIERA</h4>
                    <h4 class="font-weight-bold" style="color: #364E76;">COLEGIO VICTORIA SAS</h4>
                    <div>FORMATO ORDEN DE MATERIALES Y/O FOTOCOPIAS A.ADM-71</div>
                </div>

                <!-- Datos del usuario -->
                <table class="table table-bordered mb-4">
                    <tr>
                        <td style="width: 20%;">FECHA DE SOLICITUD:</td>
                        <td style="width: 30%;">
                            <input type="date" class="form-control" name="request_date" value="{{ $purchaseRequest->request_date }}" readonly>
                        </td>
                        <td style="width: 20%;">DATOS DEL USUARIO</td>
                        <td style="width: 30%;"></td>
                    </tr>
                    <tr>
                        <td>SOLICITANTE:</td>
                        <td>
                            <input type="text" class="form-control @error('requester') is-invalid @enderror" name="requester" value="{{ $purchaseRequest->requester }}" readonly>
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
                                <option value="Preescolar y Primaria" {{ old('section', $purchaseRequest->section) == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                                <option value="Escuela Media" {{ old('section', $purchaseRequest->section) == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                                <option value="Escuela Alta / DP" {{ old('section', $purchaseRequest->section) == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                                <option value="PAI" {{ old('section', $purchaseRequest->section) == 'PAI' ? 'selected' : '' }}>PAI</option>
                                <option value="PEP" {{ old('section', $purchaseRequest->section) == 'PEP' ? 'selected' : '' }}>PEP</option>
                                <option value="Deportes" {{ old('section', $purchaseRequest->section) == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                                <option value="Psicología Institucional" {{ old('section', $purchaseRequest->section) == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                                <option value="Biblioteca" {{ old('section', $purchaseRequest->section) == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                                <option value="Dirección General" {{ old('section', $purchaseRequest->section) == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                                <option value="CAS" {{ old('section', $purchaseRequest->section) == 'CAS' ? 'selected' : '' }}>CAS</option>
                                <option value="Administración" {{ old('section', $purchaseRequest->section) == 'Administración' ? 'selected' : '' }}>Administración</option>
                                <option value="Tecnología Institucional" {{ old('section', $purchaseRequest->section) == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                            </select>
                            @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>FECHA DE ENTREGA:</td>
                        <td>
                            <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" name="delivery_date" value="{{ old('delivery_date', $purchaseRequest->delivery_date) }}">
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
                            <table class="table table-bordered" id="copyItemsTable">
                                <thead style="background-color: #f8f9fa;">
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
                                    @if($purchaseRequest->copy_items && count($purchaseRequest->copy_items) > 0)
                                        @foreach($purchaseRequest->copy_items as $index => $item)
                                            <tr id="copyItem-{{ $item['item'] }}">
                                                <td>{{ $item['item'] }}</td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="copy_items[{{ $index }}][original]" value="{{ $item['original'] }}">
                                                    <input type="hidden" name="copy_items[{{ $index }}][item]" value="{{ $item['item'] }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][copies_required]" min="0" value="{{ $item['copies_required'] }}">
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
                                                    <button type="button" class="btn btn-sm btn-danger delete-row" {{ $index == 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="copyItem-1">
                                            <td>1</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="copy_items[0][original]">
                                                <input type="hidden" name="copy_items[0][item]" value="1">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" name="copy_items[0][copies_required]" min="0">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" name="copy_items[0][total_copies]" min="0" disabled>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="black_white_0" name="copy_items[0][black_white]" value="1">
                                                    <label class="custom-control-label" for="black_white_0"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="color_0" name="copy_items[0][color]" value="1">
                                                    <label class="custom-control-label" for="color_0"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger delete-row" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
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
                    </div>
                </div>

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
                                    @if($purchaseRequest->material_items && count($purchaseRequest->material_items) > 0)
                                        @foreach($purchaseRequest->material_items as $index => $item)
                                            <tr id="materialItem-{{ $item['item'] }}">
                                                <td>{{ $item['item'] }}</td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control form-control-sm product-input" 
                                                           name="material_items[{{ $index }}][article]"
                                                           list="products-datalist-{{ $index }}"
                                                           value="{{ $item['article'] }}"
                                                           placeholder="Escriba para buscar producto..."
                                                           autocomplete="off"
                                                           data-stock-info="">
                                                    <datalist id="products-datalist-{{ $index }}">
                                                        @foreach($inventoryItems as $inventoryItem)
                                                            <option value="{{ $inventoryItem->producto }}" data-stock="{{ $inventoryItem->stock }}">
                                                                {{ $inventoryItem->producto }} (Stock: {{ $inventoryItem->stock }})
                                                            </option>
                                                        @endforeach
                                                    </datalist>
                                                    <input type="hidden" name="material_items[{{ $index }}][item]" value="{{ $item['item'] }}">
                                                    <small class="text-muted stock-info" style="display: none;">
                                                        <i class="fas fa-box mr-1"></i>Stock disponible: <span class="stock-amount">0</span>
                                                    </small>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="material_items[{{ $index }}][quantity]" min="0" value="{{ $item['quantity'] }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="material_items[{{ $index }}][objective]" value="{{ $item['objective'] }}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-row" {{ $index == 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
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
                                    @endif
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
                    <i class="fas fa-save"></i> Actualizar Solicitud
                </button>
                <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    
    .select2-container--default .select2-selection--single {
        height: 31px;
        font-size: 0.875rem;
        padding: 0.25rem;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 31px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 31px;
        padding-left: 8px;
        color: #495057;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--institutional-blue);
    }
    
    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    
    .select2-container {
        width: 100% !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Función para inicializar Select2 en un elemento específico
        function initializeSelect2(element) {
            if (!$(element).hasClass('select2-hidden-accessible')) {
                $(element).select2({
                    placeholder: 'Seleccione un producto',
                    width: '100%',
                    allowClear: true
                });
                console.log('Select2 inicializado para:', $(element).attr('name'));
                // Forzar actualización de la vista
                $(element).trigger('change.select2');
            }
        }
        
        // Inicializar Select2 para elementos existentes
        $('.material-select').each(function() {
            initializeSelect2(this);
        });
        
        // Manejar eventos de selección usando delegación
        $(document).on('select2:select', '.material-select', function (e) {
            var data = e.params.data;
            console.log('Producto seleccionado:', data.text, 'Valor:', data.id);
            
            const selectElement = $(this);
            
            // Asegurar que el valor se mantiene
            selectElement.val(data.id).trigger('change');
            
            // Verificar y corregir la visualización después de un momento
            setTimeout(function() {
                const container = selectElement.next('.select2-container');
                if (container.length) {
                    const rendered = container.find('.select2-selection__rendered');
                    const currentText = rendered.text().trim();
                    
                    console.log('Verificando display:', {
                        selected: data.text,
                        displayed: currentText,
                        selectName: selectElement.attr('name')
                    });
                    
                    // Si el texto no coincide con lo seleccionado, corregirlo
                    if (currentText === '' || currentText === 'Seleccione un producto' || currentText !== data.text) {
                        console.log('🔧 Corrigiendo display visual para:', selectElement.attr('name'));
                        rendered.text(data.text);
                        rendered.attr('title', data.text);
                        
                        // Verificar que se aplicó el cambio
                        setTimeout(function() {
                            const newText = rendered.text();
                            console.log('✅ Display corregido a:', newText);
                        }, 10);
                    } else {
                        console.log('✅ Display correcto:', currentText);
                    }
                }
            }, 50);
            
            console.log('Valor actualizado visualmente para:', selectElement.attr('name'));
        });
        
        // Función para verificar y corregir el estado visual de Select2
        function refreshSelect2Display(selectElement) {
            if (selectElement.hasClass('select2-hidden-accessible')) {
                const currentValue = selectElement.val();
                if (currentValue && currentValue !== '') {
                    // Múltiples métodos para forzar la actualización visual
                    selectElement.trigger('change.select2');
                    selectElement.select2('trigger', 'change');
                    
                    // Verificar el texto mostrado
                    const displayText = selectElement.find('option:selected').text();
                    console.log('Display actualizado para:', selectElement.attr('name'), 'Valor:', currentValue, 'Texto:', displayText);
                    
                    // Si el texto no se muestra, forzar recreación
                    const container = selectElement.next('.select2-container');
                    if (container.length) {
                        const rendered = container.find('.select2-selection__rendered');
                        if (rendered.text() === '' || rendered.text() === 'Seleccione un producto') {
                            console.log('Forzando actualización visual...');
                            rendered.text(displayText);
                        }
                    }
                }
            }
        }
        
        // Variables para contadores de filas
        let copyItemCounter = {{ $purchaseRequest->copy_items && count($purchaseRequest->copy_items) > 0 ? collect($purchaseRequest->copy_items)->max('item') : 1 }};
        let materialItemCounter = {{ $purchaseRequest->material_items && count($purchaseRequest->material_items) > 0 ? collect($purchaseRequest->material_items)->max('item') : 1 }};
        
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
        
        // Función para agregar nuevo material
        $('#addMaterialItem').click(function() {
            materialItemCounter++;
            const newIndex = $('#materialItemsBody tr').length;
            
            // Obtener opciones de productos desde el primer select original
            const firstSelect = $('select[name="material_items[0][article]"]');
            const productOptions = firstSelect.html();
            
            const newRow = `
                <tr id="materialItem-${materialItemCounter}">
                    <td>${materialItemCounter}</td>
                    <td>
                        <select class="form-control form-control-sm material-select" name="material_items[${newIndex}][article]">
                            ${productOptions}
                        </select>
                        <input type="hidden" name="material_items[${newIndex}][item]" value="${materialItemCounter}">
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
            
            // Agregar la fila al DOM
            $('#materialItemsBody').append(newRow);
            
            // Buscar el nuevo select que acabamos de crear e inicializar Select2
            const newSelect = $(`#materialItem-${materialItemCounter} select.material-select`);
            console.log('Nuevo select creado:', newSelect.length, 'Selector:', `#materialItem-${materialItemCounter} select.material-select`);
            
            // Destruir cualquier instancia previa de Select2 y reinicializar
            if (newSelect.hasClass('select2-hidden-accessible')) {
                newSelect.select2('destroy');
            }
            
            // Inicializar Select2 en el nuevo elemento
            newSelect.select2({
                placeholder: 'Seleccione un producto',
                width: '100%',
                allowClear: true
            });
            
            // Forzar actualización del display
            newSelect.trigger('change.select2');
            
            console.log('Select2 inicializado para nuevo item:', newSelect.attr('name'));
            
            // Verificar que el select está correctamente inicializado
            if (newSelect.hasClass('select2-hidden-accessible')) {
                console.log('✓ Select2 correctamente inicializado y accesible');
                
                // Verificar y actualizar el display después de un breve momento
                setTimeout(function() {
                    refreshSelect2Display(newSelect);
                }, 50);
            } else {
                console.log('✗ Error: Select2 no se inicializó correctamente');
            }
        });
        
        // Evento para eliminar fila (delegación de eventos)
        $(document).on('click', '.delete-row', function() {
            // No permitir eliminar si solo queda una fila
            const tableId = $(this).closest('table').attr('id');
            const rowCount = $(this).closest('tbody').find('tr').length;
            
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                
                // Renumerar las filas visibles
                if (tableId === 'copyItemsTable') {
                    $('#copyItemsBody tr').each(function(index) {
                        $(this).find('td:first').text(index + 1);
                    });
                } else if (tableId === 'materialItemsTable') {
                    $('#materialItemsBody tr').each(function(index) {
                        $(this).find('td:first').text(index + 1);
                    });
                }
            } else {
                alert('Debe mantener al menos un ítem en la tabla.');
            }
        });
        
        // Validación del formulario
        $('#materialsForm').submit(function(e) {
            let valid = false;
            let hasCopyItems = false;
            let hasMaterialItems = false;
            
            // Verificar si hay ítems de fotocopias
            $('input[name$="[copies_required]"]').each(function() {
                if ($(this).val() && parseInt($(this).val()) > 0) {
                    hasCopyItems = true;
                    return false; // Romper el ciclo
                }
            });
            
            // Verificar si hay ítems de materiales
            $('input[name$="[quantity]"]').each(function() {
                if ($(this).val() && parseInt($(this).val()) > 0) {
                    hasMaterialItems = true;
                    return false; // Romper el ciclo
                }
            });
            
            valid = hasCopyItems || hasMaterialItems;
            
            if (!valid) {
                e.preventDefault();
                alert('Debe ingresar al menos un ítem de fotocopias o materiales.');
                return false;
            }
            
            return true;
        });
    });
</script>
@stop