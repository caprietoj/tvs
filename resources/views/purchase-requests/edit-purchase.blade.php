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
                            <option value="Preescolar y Primaria" {{ old('section_area', $purchaseRequest->section_area) == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                            <option value="Escuela Media" {{ old('section_area', $purchaseRequest->section_area) == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                            <option value="Escuela Alta / DP" {{ old('section_area', $purchaseRequest->section_area) == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                            <option value="PAI" {{ old('section_area', $purchaseRequest->section_area) == 'PAI' ? 'selected' : '' }}>PAI</option>
                            <option value="PEP" {{ old('section_area', $purchaseRequest->section_area) == 'PEP' ? 'selected' : '' }}>PEP</option>
                            <option value="Deportes" {{ old('section_area', $purchaseRequest->section_area) == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                            <option value="Psicología Institucional" {{ old('section_area', $purchaseRequest->section_area) == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                            <option value="Biblioteca" {{ old('section_area', $purchaseRequest->section_area) == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                            <option value="Dirección General" {{ old('section_area', $purchaseRequest->section_area) == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                            <option value="CAS" {{ old('section_area', $purchaseRequest->section_area) == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="Administración" {{ old('section_area', $purchaseRequest->section_area) == 'Administración' ? 'selected' : '' }}>Administración</option>
                            <option value="Tecnología Institucional" {{ old('section_area', $purchaseRequest->section_area) == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
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
                                            <input class="form-check-input" type="radio" name="is_shared" id="is_shared_no" value="no" {{ ($purchaseRequest->is_shared ?? 'no') == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_shared_no">
                                                <i class="fas fa-times text-danger mr-1"></i> No, esta compra es solo para mi sección
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_shared" id="is_shared_yes" value="yes" {{ ($purchaseRequest->is_shared ?? 'no') == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_shared_yes">
                                                <i class="fas fa-check text-success mr-1"></i> Sí, esta compra será compartida
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuración de compra compartida -->
                                <div id="sharedConfig" style="{{ ($purchaseRequest->is_shared ?? 'no') == 'yes' ? 'display: block;' : 'display: none;' }}">
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-user mr-1"></i> Mi Sección</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-1"><strong>Sección:</strong></p>
                                                    <p class="section-display" id="currentSection">{{ $purchaseRequest->section_area ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="myPercentage" name="my_percentage" min="1" max="99" value="{{ $purchaseRequest->my_percentage ?? '50' }}">
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
                                                        <option value="Preescolar y Primaria" {{ ($purchaseRequest->shared_section ?? '') == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                                                        <option value="Escuela Media" {{ ($purchaseRequest->shared_section ?? '') == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                                                        <option value="Escuela Alta / DP" {{ ($purchaseRequest->shared_section ?? '') == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                                                        <option value="PAI" {{ ($purchaseRequest->shared_section ?? '') == 'PAI' ? 'selected' : '' }}>PAI</option>
                                                        <option value="PEP" {{ ($purchaseRequest->shared_section ?? '') == 'PEP' ? 'selected' : '' }}>PEP</option>
                                                        <option value="Deportes" {{ ($purchaseRequest->shared_section ?? '') == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                                                        <option value="Psicología Institucional" {{ ($purchaseRequest->shared_section ?? '') == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                                                        <option value="Biblioteca" {{ ($purchaseRequest->shared_section ?? '') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                                                        <option value="Dirección General" {{ ($purchaseRequest->shared_section ?? '') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                                                        <option value="CAS" {{ ($purchaseRequest->shared_section ?? '') == 'CAS' ? 'selected' : '' }}>CAS</option>
                                                        <option value="Administración" {{ ($purchaseRequest->shared_section ?? '') == 'Administración' ? 'selected' : '' }}>Administración</option>
                                                        <option value="Tecnología Institucional" {{ ($purchaseRequest->shared_section ?? '') == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                                                    </select>
                                                    <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="sharedPercentage" name="shared_percentage" value="{{ $purchaseRequest->shared_percentage ?? '50' }}" readonly>
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
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addThirdSection" 
                                                style="{{ !empty($purchaseRequest->third_shared_section) ? 'display: none;' : '' }}">
                                            <i class="fas fa-plus mr-1"></i> Agregar otra sección
                                        </button>
                                    </div>

                                    <!-- Tercera sección compartida -->
                                    <div id="thirdSectionConfig" style="{{ !empty($purchaseRequest->third_shared_section) ? 'display: block;' : 'display: none;' }}" class="mt-3">
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
                                                    <select class="form-control" id="thirdSharedSection" name="third_shared_section" {{ empty($purchaseRequest->third_shared_section) ? 'disabled tabindex="-1"' : '' }}>
                                                        <option value="">Seleccione una sección...</option>
                                                        <option value="Preescolar y Primaria" {{ ($purchaseRequest->third_shared_section ?? '') == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                                                        <option value="Escuela Media" {{ ($purchaseRequest->third_shared_section ?? '') == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                                                        <option value="Escuela Alta / DP" {{ ($purchaseRequest->third_shared_section ?? '') == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                                                        <option value="PAI" {{ ($purchaseRequest->third_shared_section ?? '') == 'PAI' ? 'selected' : '' }}>PAI</option>
                                                        <option value="PEP" {{ ($purchaseRequest->third_shared_section ?? '') == 'PEP' ? 'selected' : '' }}>PEP</option>
                                                        <option value="Deportes" {{ ($purchaseRequest->third_shared_section ?? '') == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                                                        <option value="Psicología Institucional" {{ ($purchaseRequest->third_shared_section ?? '') == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                                                        <option value="Biblioteca" {{ ($purchaseRequest->third_shared_section ?? '') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                                                        <option value="Dirección General" {{ ($purchaseRequest->third_shared_section ?? '') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                                                        <option value="CAS" {{ ($purchaseRequest->third_shared_section ?? '') == 'CAS' ? 'selected' : '' }}>CAS</option>
                                                        <option value="Administración" {{ ($purchaseRequest->third_shared_section ?? '') == 'Administración' ? 'selected' : '' }}>Administración</option>
                                                        <option value="Tecnología Institucional" {{ ($purchaseRequest->third_shared_section ?? '') == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                                                    </select>
                                                    <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="thirdSharedPercentage" name="third_shared_percentage" min="1" max="97" value="{{ $purchaseRequest->third_shared_percentage ?? '0' }}" {{ empty($purchaseRequest->third_shared_section) ? 'disabled tabindex="-1"' : '' }}>
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
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Variables para controlar el número de elementos
        let purchaseItemCount = {{ old('purchase_items', $purchaseRequest->purchase_items) ? count(old('purchase_items', $purchaseRequest->purchase_items)) : 1 }};

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

        // Eliminar fila (delegación de eventos)
        $(document).on('click', '.delete-row', function() {
            $(this).closest('tr').remove();
        });

        // ========== FUNCIONALIDAD DE COMPRA COMPARTIDA ==========
        
        // Función para actualizar la sección actual cuando cambie
        $('#section_area').change(function() {
            const currentSection = $(this).find('option:selected').text();
            $('#currentSection').text(currentSection || '-');
            updateSectionFilters();
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
        $('#sharedSection, #thirdSharedSection').change(function() {
            updateSectionFilters();
        });

        // Validación del formulario para compra compartida
        $('#purchaseForm').submit(function(e) {
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
                    e.preventDefault();
                    return false;
                }
                
                // Si la tercera sección está visible, validar que esté seleccionada
                if ($('#thirdSectionConfig').is(':visible')) {
                    const thirdSection = $('#thirdSharedSection').val();
                    if (!thirdSection) {
                        alert('Por favor seleccione la tercera sección o remueva la opción de tercera sección.');
                        $('#thirdSharedSection').focus();
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
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });

        // Inicializar filtros al cargar la página
        $(document).ready(function() {
            updateSectionFilters();
            
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
            
            // Manejar errores de validación en campos ocultos
            @if($errors->has('third_shared_percentage') && old('third_shared_section', $purchaseRequest->third_shared_section))
                // Si hay error en third_shared_percentage y hay valor en third_shared_section,
                // mostrar la tercera sección automáticamente y habilitar los campos
                $('#thirdSectionConfig').show();
                $('#addThirdSection').hide();
                $('#thirdSharedSection').prop('disabled', false).removeAttr('tabindex');
                $('#thirdSharedPercentage').prop('disabled', false).removeAttr('tabindex');
            @endif
        });
    });
</script>
@stop