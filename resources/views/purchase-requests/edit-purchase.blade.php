@extends('adminlte::page')

@section('title', 'Editar Formato Compra de Materiales')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-edit mr-2"></i>Editar Formato Compra de Materiales</h1>
@stop

@section('content')
<div class="container">
    <div class="card card-outline" style="border-top-color: #364E76;">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Editar Solicitud de Compra</h3>
        </div>
        
        <form action="{{ route('purchase-requests.update', $purchaseRequest->id) }}" method="POST" id="purchaseForm">
            @csrf
            @method('PUT')
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
                    <input type="text" class="form-control @error('requester') is-invalid @enderror" id="requester" name="requester" value="{{ old('requester', $purchaseRequest->requester ?? auth()->user()->name) }}" readonly>
                    @error('requester')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="section_area">SECCIÓN Y/O ÁREA:</label>
                        <select class="form-control @error('section_area') is-invalid @enderror" id="section_area" name="section_area">
                            <option value="">Seleccione...</option>
                            <option value="Pre Escolar" {{ old('section_area', $purchaseRequest->section_area) == 'Pre Escolar' ? 'selected' : '' }}>Pre Escolar</option>
                            <option value="Primaria" {{ old('section_area', $purchaseRequest->section_area) == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                            <option value="Bachillerato" {{ old('section_area', $purchaseRequest->section_area) == 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                            <option value="PEP" {{ old('section_area', $purchaseRequest->section_area) == 'PEP' ? 'selected' : '' }}>PEP</option>
                            <option value="PAI" {{ old('section_area', $purchaseRequest->section_area) == 'PAI' ? 'selected' : '' }}>PAI</option>
                            <option value="Diploma" {{ old('section_area', $purchaseRequest->section_area) == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                            <option value="Administración" {{ old('section_area', $purchaseRequest->section_area) == 'Administración' ? 'selected' : '' }}>Administración</option>
                            <option value="Dirección General" {{ old('section_area', $purchaseRequest->section_area) == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                            <option value="CAS" {{ old('section_area', $purchaseRequest->section_area) == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="Departamento de Apoyo" {{ old('section_area', $purchaseRequest->section_area) == 'Departamento de Apoyo' ? 'selected' : '' }}>Departamento de Apoyo</option>
                            <option value="Biblioteca" {{ old('section_area', $purchaseRequest->section_area) == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                        </select>
                        @error('section_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="request_date">FECHA DE SOLICITUD:</label>
                        <input type="date" class="form-control" id="request_date" name="request_date" value="{{ old('request_date', $purchaseRequest->created_at->format('Y-m-d')) }}" readonly>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="reception_date">FECHA DE RECEPCIÓN:</label>
                        <input type="date" class="form-control" id="reception_date" name="reception_date" value="{{ $purchaseRequest->approval_date ? $purchaseRequest->approval_date->format('Y-m-d') : '' }}" {{ $purchaseRequest->approval_date ? 'readonly' : 'disabled' }}>
                        <small class="form-text text-muted">Completado por el departamento de compras</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="coordinator">COORDINADOR DE SECCIÓN Y/O JEFE DE ÁREA:</label>
                    <input type="text" class="form-control" id="coordinator" name="coordinator" value="{{ old('coordinator', $purchaseRequest->coordinator ?? '') }}">
                </div>

                <div class="form-group">
                    <label for="budget">RUBRO PRESUPUESTAL:</label>
                    <input type="text" class="form-control" id="budget" name="budget" value="{{ old('budget', $purchaseRequest->budget ?? '') }}">
                </div>

                <!-- Compras -->
                <div class="card my-4 card-outline" style="border-top-color: #364E76;">
                    <div class="card-header" style="background-color: #364E76; color: white;">
                        <h5 class="mb-0">COMPRAS</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="purchase_justification">JUSTIFICACIÓN DE LA COMPRA (Diligenciar este espacio en todos los casos):</label>
                            <textarea class="form-control @error('purchase_justification') is-invalid @enderror" id="purchase_justification" name="purchase_justification" rows="3">{{ old('purchase_justification', $purchaseRequest->purchase_justification) }}</textarea>
                            @error('purchase_justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    @if(old('purchase_items', $purchaseRequest->purchase_items))
                                        @foreach(old('purchase_items', $purchaseRequest->purchase_items) as $index => $item)
                                            <tr id="purchaseItem-{{ $item['item'] }}">
                                                <td>{{ $item['item'] }}</td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="purchase_items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" min="0">
                                                    <input type="hidden" name="purchase_items[{{ $index }}][item]" value="{{ $item['item'] }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="purchase_items[{{ $index }}][description]" value="{{ $item['description'] }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="purchase_items[{{ $index }}][unit]" value="{{ $item['unit'] }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="purchase_items[{{ $index }}][observations]" value="{{ $item['observations'] ?? '' }}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-row" {{ $loop->first ? 'disabled' : '' }}>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="purchaseItem-1">
                                            <td>1</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" name="purchase_items[0][quantity]" min="0">
                                                <input type="hidden" name="purchase_items[0][item]" value="1">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="purchase_items[0][description]">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="purchase_items[0][unit]">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="purchase_items[0][observations]">
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
                            <small>PARA EQUIPOS TECNOLÓGICOS SOLICITAMOS CONSULTAR LAS ESPECIFICACIONES TÉCNICAS DEFINIDAS POR EL EMC DEL COLEGIO VICTORIA.</small>
                        </div>
                    </div>
                </div>

                <!-- Servicios -->
                <div class="card my-4 card-outline" style="border-top-color: #364E76;">
                    <div class="card-header" style="background-color: #364E76; color: white;">
                        <h5 class="mb-0">SERVICIOS</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="service_justification">JUSTIFICACIÓN DEL SERVICIO:</label>
                            <textarea class="form-control" id="service_justification" name="service_justification" rows="2">{{ old('service_justification', $purchaseRequest->service_justification ?? '') }}</textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="service_budget">VALOR PRESUPUESTADO PARA ESTE SERVICIO $:</label>
                                <input type="number" step="0.01" class="form-control" id="service_budget" name="service_budget" value="{{ old('service_budget', $purchaseRequest->service_budget ?? '') }}">
                            </div>
                            <div class="form-group col-md-8">
                                <label for="service_budget_text">EN LETRAS:</label>
                                <input type="text" class="form-control" id="service_budget_text" name="service_budget_text" value="{{ old('service_budget_text', $purchaseRequest->service_budget_text ?? '') }}">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="serviceItemsTable">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 5%;">ITEM</th>
                                        <th style="width: 10%;">CANT.</th>
                                        <th style="width: 55%;">DESCRIPCIÓN DEL SERVICIO</th>
                                        <th style="width: 25%;">OBSERVACIONES</th>
                                        <th style="width: 5%;">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="serviceItemsBody">
                                    @if(old('service_items', $purchaseRequest->service_items))
                                        @foreach(old('service_items', $purchaseRequest->service_items) as $index => $item)
                                            <tr id="serviceItem-{{ $item['item'] }}">
                                                <td>{{ $item['item'] }}</td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="service_items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" min="0">
                                                    <input type="hidden" name="service_items[{{ $index }}][item]" value="{{ $item['item'] }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="service_items[{{ $index }}][description]" value="{{ $item['description'] }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="service_items[{{ $index }}][observations]" value="{{ $item['observations'] ?? '' }}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-row" {{ $loop->first ? 'disabled' : '' }}>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="serviceItem-1">
                                            <td>1</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" name="service_items[0][quantity]" min="0">
                                                <input type="hidden" name="service_items[0][item]" value="1">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="service_items[0][description]">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="service_items[0][observations]">
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
                                            <button type="button" class="btn btn-sm" id="addServiceItem" style="background-color: #364E76; color: white;">
                                                <i class="fas fa-plus"></i> Agregar Servicio
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <small>NOTA: EN CASO DE REQUERIR VARIOS SERVICIOS PUEDE AÑADIR TANTAS FILAS COMO NECESITE USANDO EL BOTÓN "AGREGAR SERVICIO"</small>
                        </div>
                    </div>
                </div>

                <!-- Firmas -->
                <div class="form-row mt-4">
                    <div class="form-group col-md-6">
                        <label>FIRMA DEL RESPONSABLE DEL PRESUPUESTO</label>
                        <div class="border-bottom border-dark" style="height: 40px;"></div>
                        <small class="form-text text-muted">Especificar nombre completo</small>
                    </div>
                    <div class="form-group col-md-6">
                        <label>FIRMA JEFE DE COMPRAS</label>
                        <div class="border-bottom border-dark" style="height: 40px;"></div>
                        <small class="form-text text-muted">Especificar nombre completo</small>
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
<style>
    :root {
        --primary-color: #364E76;
    }
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .btn-primary:hover {
        background-color: #2d4265;
        border-color: #2d4265;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Variables para controlar el número de elementos
        let purchaseItemCount = {{ old('purchase_items', $purchaseRequest->purchase_items) ? count(old('purchase_items', $purchaseRequest->purchase_items)) : 1 }};
        let serviceItemCount = {{ old('service_items', $purchaseRequest->service_items) ? count(old('service_items', $purchaseRequest->service_items)) : 1 }};

        // Función para convertir números a letras en español
        function numeroALetras(numero) {
            if (numero === 0) return 'cero';
            if (numero < 0) return 'menos ' + numeroALetras(-numero);
            
            const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
            const especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
            const decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
            const centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
            
            function convertirGrupo(n) {
                let resultado = '';
                
                if (n >= 100) {
                    if (n === 100) {
                        resultado += 'cien';
                    } else {
                        resultado += centenas[Math.floor(n / 100)];
                    }
                    n %= 100;
                    if (n > 0) resultado += ' ';
                }
                
                if (n >= 20) {
                    resultado += decenas[Math.floor(n / 10)];
                    n %= 10;
                    if (n > 0) resultado += ' y ' + unidades[n];
                } else if (n >= 10) {
                    resultado += especiales[n - 10];
                } else if (n > 0) {
                    resultado += unidades[n];
                }
                
                return resultado;
            }
            
            if (numero < 1000) {
                return convertirGrupo(numero);
            } else if (numero < 1000000) {
                const miles = Math.floor(numero / 1000);
                const resto = numero % 1000;
                let resultado = '';
                
                if (miles === 1) {
                    resultado = 'mil';
                } else {
                    resultado = convertirGrupo(miles) + ' mil';
                }
                
                if (resto > 0) {
                    resultado += ' ' + convertirGrupo(resto);
                }
                
                return resultado;
            } else {
                const millones = Math.floor(numero / 1000000);
                const resto = numero % 1000000;
                let resultado = '';
                
                if (millones === 1) {
                    resultado = 'un millón';
                } else {
                    resultado = convertirGrupo(millones) + ' millones';
                }
                
                if (resto > 0) {
                    if (resto < 1000) {
                        resultado += ' ' + convertirGrupo(resto);
                    } else {
                        const miles = Math.floor(resto / 1000);
                        const restoMiles = resto % 1000;
                        
                        if (miles > 0) {
                            if (miles === 1) {
                                resultado += ' mil';
                            } else {
                                resultado += ' ' + convertirGrupo(miles) + ' mil';
                            }
                        }
                        
                        if (restoMiles > 0) {
                            resultado += ' ' + convertirGrupo(restoMiles);
                        }
                    }
                }
                
                return resultado;
            }
        }
        
        function convertirMonedaALetras(valor) {
            if (!valor || isNaN(valor)) return '';
            
            const partes = valor.toString().split('.');
            const entero = parseInt(partes[0]);
            const decimales = partes[1] ? parseInt(partes[1].padEnd(2, '0').substring(0, 2)) : 0;
            
            let resultado = numeroALetras(entero) + ' peso';
            if (entero !== 1) resultado += 's';
            
            if (decimales > 0) {
                resultado += ' con ' + numeroALetras(decimales) + ' centavo';
                if (decimales !== 1) resultado += 's';
            }
            
            return resultado.charAt(0).toUpperCase() + resultado.slice(1);
        }
        
        // Event listener para conversión automática de números a letras
        $('#service_budget').on('input change', function() {
            const valor = parseFloat($(this).val());
            if (!isNaN(valor) && valor >= 0) {
                const valorEnLetras = convertirMonedaALetras(valor);
                $('#service_budget_text').val(valorEnLetras);
            } else {
                $('#service_budget_text').val('');
            }
        });

        // Agregar elemento de compra
        $('#addPurchaseItem').click(function() {
            purchaseItemCount++;
            const newRow = `
                <tr id="purchaseItem-${purchaseItemCount}">
                    <td>${purchaseItemCount}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="purchase_items[${purchaseItemCount - 1}][quantity]" min="0">
                        <input type="hidden" name="purchase_items[${purchaseItemCount - 1}][item]" value="${purchaseItemCount}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="purchase_items[${purchaseItemCount - 1}][description]">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="purchase_items[${purchaseItemCount - 1}][unit]">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="purchase_items[${purchaseItemCount - 1}][observations]">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#purchaseItemsBody').append(newRow);
        });

        // Agregar elemento de servicio
        $('#addServiceItem').click(function() {
            serviceItemCount++;
            const newRow = `
                <tr id="serviceItem-${serviceItemCount}">
                    <td>${serviceItemCount}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="service_items[${serviceItemCount - 1}][quantity]" min="0">
                        <input type="hidden" name="service_items[${serviceItemCount - 1}][item]" value="${serviceItemCount}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="service_items[${serviceItemCount - 1}][description]">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="service_items[${serviceItemCount - 1}][observations]">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#serviceItemsBody').append(newRow);
        });

        // Eliminar fila (delegación de eventos)
        $(document).on('click', '.delete-row', function() {
            $(this).closest('tr').remove();
        });
    });
</script>
@stop