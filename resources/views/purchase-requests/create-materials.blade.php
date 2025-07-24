@extends('adminlte::page')

@section('title', 'Solicitud de Materiales')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-box mr-2"></i>Solicitud de Materiales</h1>
@stop

@section('content')
<div class="container">
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
                                <option value="Pre Escolar" {{ old('section') == 'Pre Escolar' ? 'selected' : '' }}>Pre Escolar</option>
                                <option value="Primaria" {{ old('section') == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                                <option value="Bachillerato" {{ old('section') == 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                                <option value="Administración" {{ old('section') == 'Administración' ? 'selected' : '' }}>Administración</option>
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
                                                   autocomplete="off">
                                            <datalist id="products-datalist-0">
                                                @foreach($inventoryItems as $item)
                                                    <option value="{{ $item->producto }}">
                                                @endforeach
                                            </datalist>
                                            <input type="hidden" name="material_items[0][item]" value="1">
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
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        console.log('🚀 Sistema de autocompletado con datalist iniciado');
        
        // Lista de productos válidos para validación
        const validProducts = [
            @foreach($inventoryItems as $item)
                "{{ addslashes($item->producto) }}",
            @endforeach
        ];
        
        console.log('📦 Total productos válidos:', validProducts.length);
        
        let itemCounter = 1;
        
        // Función para validar selección de producto
        function validateProduct(input) {
            const value = input.val().trim();
            const isValid = validProducts.includes(value);
            
            input.removeClass('valid-selection invalid-selection');
            
            if (value === '') {
                // Campo vacío, sin validación visual
                return false;
            } else if (isValid) {
                input.addClass('valid-selection');
                console.log('✅ Producto válido:', value);
                return true;
            } else {
                input.addClass('invalid-selection');
                console.log('❌ Producto no válido:', value);
                return false;
            }
        }
        
        // Evento para validar productos en tiempo real
        $(document).on('input change blur', '.product-input', function() {
            validateProduct($(this));
        });
        
        // Función para crear datalist único
        function createUniqueDatalist(index) {
            const datalistId = `products-datalist-${index}`;
            const options = validProducts.map(product => 
                `<option value="${product}">`
            ).join('');
            
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
                               autocomplete="off">
                        ${createUniqueDatalist(newIndex)}
                        <input type="hidden" name="material_items[${newIndex}][item]" value="${itemCounter}">
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
        
        // Validación del formulario
        $('#materialsForm').submit(function(e) {
            let hasValidItems = false;
            let hasInvalidItems = false;
            
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
                        console.log('✅ Item válido:', productValue, 'Cantidad:', quantity);
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
            
            console.log('✅ Formulario válido, enviando...');
            return true;
        });
        
        // Validar el primer producto al cargar la página
        validateProduct($('.product-input').first());
    });
</script>
@stop