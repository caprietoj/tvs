@extends('adminlte::page')

@section('title', 'Editar Solicitud de Materiales y/o Fotocopias')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-copy mr-2"></i>Editar Solicitud de Materiales y/o Fotocopias</h1>
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
                                    @if($purchaseRequest->copyItems && count($purchaseRequest->copyItems) > 0)
                                        @foreach($purchaseRequest->copyItems as $index => $item)
                                            <tr id="copyItem-{{ $item->item }}">
                                                <td>{{ $item->item }}</td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="copy_items[{{ $index }}][original]" value="{{ $item->original }}">
                                                    <input type="hidden" name="copy_items[{{ $index }}][item]" value="{{ $item->item }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][copies_required]" min="0" value="{{ $item->copies_required }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][double_letter_color]" min="0" value="{{ $item->double_letter_color ?? '' }}" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][black_white]" min="0" value="{{ $item->black_white ?? '' }}" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="copy_items[{{ $index }}][color]" min="0" value="{{ $item->color ?? '' }}" placeholder="0">
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
                                    @if($purchaseRequest->materialItems && count($purchaseRequest->materialItems) > 0)
                                        @foreach($purchaseRequest->materialItems as $index => $item)
                                            <tr id="materialItem-{{ $item->item }}">
                                                <td>{{ $item->item }}</td>
                                                <td>
                                                    <select class="form-control form-control-sm select2" name="material_items[{{ $index }}][article]">
                                                        <option value="">Seleccione un producto...</option>
                                                        @foreach($inventoryItems as $inventoryItem)
                                                            <option value="{{ $inventoryItem->producto }}" {{ $item->article == $inventoryItem->producto ? 'selected' : '' }}>
                                                                {{ $inventoryItem->producto }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="material_items[{{ $index }}][item]" value="{{ $item->item }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="material_items[{{ $index }}][quantity]" min="0" value="{{ $item->quantity }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="material_items[{{ $index }}][objective]" value="{{ $item->objective }}">
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
                                                <select class="form-control form-control-sm select2" name="material_items[0][article]">
                                                    <option value="">Seleccione un producto...</option>
                                                    @foreach($inventoryItems as $item)
                                                        <option value="{{ $item->producto }}">{{ $item->producto }}</option>
                                                    @endforeach
                                                </select>
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
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function() {
        // Inicializar Select2 para los selectores de productos
        $('.select2').select2({
            placeholder: 'Seleccione un producto',
            width: '100%'
        });
        
        // Variables para contadores de filas
        let copyItemCounter = {{ $purchaseRequest->copyItems && count($purchaseRequest->copyItems) > 0 ? $purchaseRequest->copyItems->max('item') : 1 }};
        let materialItemCounter = {{ $purchaseRequest->materialItems && count($purchaseRequest->materialItems) > 0 ? $purchaseRequest->materialItems->max('item') : 1 }};
        
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
            
            // Obtener opciones de productos desde el primer select
            const productOptions = $('select[name="material_items[0][article]"]').html();
            
            const newRow = `
                <tr id="materialItem-${materialItemCounter}">
                    <td>${materialItemCounter}</td>
                    <td>
                        <select class="form-control form-control-sm select2-new" name="material_items[${newIndex}][article]">
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
            $('#materialItemsBody').append(newRow);
            
            // Inicializar Select2 para el nuevo select
            $('.select2-new').select2({
                placeholder: 'Seleccione un producto',
                width: '100%'
            }).removeClass('select2-new');
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