@extends('adminlte::page')

@section('title', 'Agregar cotización')

@section('content_header')
    <h1>Agregar Cotización - Solicitud {{ $purchaseRequest->request_number }}</h1>
@stop

@section('content')
    <!-- Información de la solicitud -->
    <div class="info-card">
        <div class="info-header">
            <h3>Información de la solicitud</h3>
        </div>
        <div class="info-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Número:</div>
                    <div class="info-value">{{ $purchaseRequest->request_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Solicitante:</div>
                    <div class="info-value">{{ $purchaseRequest->requester }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Sección/Área:</div>
                    <div class="info-value">{{ $purchaseRequest->section_area }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha:</div>
                    <div class="info-value">{{ $purchaseRequest->request_date instanceof \DateTime ? $purchaseRequest->request_date->format('d/m/Y') : 'No establecida' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Estado:</div>
                    <div class="info-value">{{ $purchaseRequest->status }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Cotizaciones:</div>
                    <div class="info-value">{{ $purchaseRequest->getQuotationProgress() }}</div>
                </div>
            </div>
            <div class="justification-section">
                <div class="info-label">Justificación:</div>
                <div class="info-value">{{ $purchaseRequest->purchase_justification }}</div>
            </div>
        </div>
    </div>

    <!-- Formulario de cotización -->
    <div class="form-card">
        <div class="form-header">
            <h3>Agregar nueva cotización</h3>
        </div>
        <div class="form-body">
            @if (session('error'))
                <div class="alert error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('quotations.store', $purchaseRequest) }}" method="POST" enctype="multipart/form-data" id="quotationForm">
                @csrf
                
                <!-- Información del Proveedor -->
                <div class="form-section">
                    <h4>Información del Proveedor</h4>
                    <div class="form-group">
                        <label for="provider_name">Nombre del proveedor *</label>
                        <select name="provider_name" id="provider_name" required>
                            <option value="">-- Seleccione un proveedor --</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->nombre }}" {{ old('provider_name') == $proveedor->nombre ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }} {{ $proveedor->nit ? '- ' . $proveedor->nit : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('provider_name'))
                            <div class="error-message">{{ $errors->first('provider_name') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Impuestos Aplicables -->
                <div class="form-section">
                    <h4>Configuración de Impuestos</h4>
                    
                    <div class="alert info">
                        <strong>Nota:</strong> Puede aplicar impuestos de forma global (a todos los items) o individual (por cada item). 
                        Los impuestos globales e individuales son mutuamente excluyentes - si marca un impuesto global, se desmarcarán los individuales del mismo tipo y viceversa.
                    </div>
                    
                    <div class="tax-configuration-section">
                        <h5>Aplicación Global de Impuestos</h5>
                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="includes_iva_19" id="includes_iva_19" value="1" {{ old('includes_iva_19') ? 'checked' : '' }}>
                                    Aplicar IVA (19%)
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="includes_iva_5" id="includes_iva_5" value="1" {{ old('includes_iva_5') ? 'checked' : '' }}>
                                    Aplicar IVA (5%)
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="includes_ipoconsumo_8" id="includes_ipoconsumo_8" value="1" {{ old('includes_ipoconsumo_8') ? 'checked' : '' }}>
                                    Aplicar Ipoconsumo (8%)
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="includes_ipoconsumo_4" id="includes_ipoconsumo_4" value="1" {{ old('includes_ipoconsumo_4') ? 'checked' : '' }}>
                                    Aplicar Ipoconsumo (4%)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Campos ocultos para compatibilidad -->
                    <input type="hidden" name="includes_iva" id="includes_iva_hidden" value="0">
                    <input type="hidden" name="iva_amount" id="iva_amount_hidden" value="0">
                    <input type="hidden" name="subtotal" id="subtotal_hidden" value="0">
                    <input type="hidden" name="total_amount" id="total_amount_hidden" value="0">
                    
                    <!-- Campos de impuestos calculados (ocultos pero necesarios para JavaScript) -->
                    <input type="hidden" name="iva_19_amount" id="iva_19_amount" value="{{ old('iva_19_amount', '0') }}" step="0.01" min="0">
                    <input type="hidden" name="iva_5_amount" id="iva_5_amount" value="{{ old('iva_5_amount', '0') }}" step="0.01" min="0">
                    <input type="hidden" name="ipoconsumo_8_amount" id="ipoconsumo_8_amount" value="{{ old('ipoconsumo_8_amount', '0') }}" step="0.01" min="0">
                    <input type="hidden" name="ipoconsumo_4_amount" id="ipoconsumo_4_amount" value="{{ old('ipoconsumo_4_amount', '0') }}" step="0.01" min="0">
                </div>

                <!-- Items de la solicitud -->
                <div class="form-section">
                    <h4>Items de la Solicitud</h4>
                    
                    @if($purchaseRequest->type === 'purchase' && is_array($purchaseRequest->purchase_items) && count(array_filter($purchaseRequest->purchase_items, function($item) { return !empty($item['description']) && !empty($item['quantity']); })) > 0)
                        <div class="items-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Descripción del Artículo</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Total</th>
                                        <th>Impuestos por Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseRequest->purchase_items as $index => $item)
                                        @if(!empty($item['description']) && !empty($item['quantity']))
                                            <tr>
                                                <td>{{ $item['description'] ?? 'Sin descripción' }}</td>
                                                <td class="text-center">{{ $item['quantity'] ?? '0' }}</td>
                                                <td>
                                                    <input type="number" 
                                                           name="item_prices[{{ $index }}]" 
                                                           class="item-price" 
                                                           data-quantity="{{ $item['quantity'] ?? '0' }}"
                                                           data-index="{{ $index }}"
                                                           step="0.01" 
                                                           min="0" 
                                                           placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           name="item_totals[{{ $index }}]" 
                                                           class="item-total" 
                                                           step="0.01" 
                                                           min="0" 
                                                           readonly>
                                                </td>
                                                <td>
                                                    <div class="tax-checkboxes">
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_iva_19[{{ $index }}]" class="item-tax" data-tax="19" data-index="{{ $index }}">
                                                            IVA 19%
                                                        </label>
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_iva_5[{{ $index }}]" class="item-tax" data-tax="5" data-index="{{ $index }}">
                                                            IVA 5%
                                                        </label>
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_ipoconsumo_8[{{ $index }}]" class="item-tax" data-tax="8" data-index="{{ $index }}">
                                                            Ipoc. 8%
                                                        </label>
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_ipoconsumo_4[{{ $index }}]" class="item-tax" data-tax="4" data-index="{{ $index }}">
                                                            Ipoc. 4%
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($purchaseRequest->type === 'services' && $purchaseRequest->service_type === 'regular' && is_array($purchaseRequest->service_items) && count(array_filter($purchaseRequest->service_items, function($item) { return !empty($item['description']) && !empty($item['quantity']); })) > 0)
                        <div class="items-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Descripción del Servicio</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Total</th>
                                        <th>Impuestos por Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseRequest->service_items as $index => $item)
                                        @if(!empty($item['description']) && !empty($item['quantity']))
                                            <tr>
                                                <td>{{ $item['description'] ?? 'Sin descripción' }}</td>
                                                <td class="text-center">{{ $item['quantity'] ?? '0' }}</td>
                                                <td>
                                                    <input type="number" 
                                                           name="item_prices[{{ $index }}]" 
                                                           class="item-price" 
                                                           data-quantity="{{ $item['quantity'] ?? '0' }}"
                                                           data-index="{{ $index }}"
                                                           step="0.01" 
                                                           min="0" 
                                                           placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           name="item_totals[{{ $index }}]" 
                                                           class="item-total" 
                                                           step="0.01" 
                                                           min="0" 
                                                           readonly>
                                                </td>
                                                <td>
                                                    <div class="tax-checkboxes">
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_iva_19[{{ $index }}]" class="item-tax" data-tax="19" data-index="{{ $index }}">
                                                            IVA 19%
                                                        </label>
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_iva_5[{{ $index }}]" class="item-tax" data-tax="5" data-index="{{ $index }}">
                                                            IVA 5%
                                                        </label>
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_ipoconsumo_8[{{ $index }}]" class="item-tax" data-tax="8" data-index="{{ $index }}">
                                                            Ipoc. 8%
                                                        </label>
                                                        <label class="tax-label">
                                                            <input type="checkbox" name="item_ipoconsumo_4[{{ $index }}]" class="item-tax" data-tax="4" data-index="{{ $index }}">
                                                            Ipoc. 4%
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($purchaseRequest->type === 'services' && $purchaseRequest->service_type === 'general')
                        <div class="alert info">
                            <strong>Servicio General:</strong> {{ $purchaseRequest->general_service_description ?? 'Sin descripción específica' }}
                        </div>
                        <div class="form-group">
                            <label for="general_service_price">Precio del Servicio</label>
                            <input type="number" 
                                   name="general_service_price" 
                                   id="general_service_price"
                                   class="item-price" 
                                   data-quantity="1"
                                   data-index="general"
                                   step="0.01" 
                                   min="0" 
                                   placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Impuestos aplicables al servicio:</label>
                            <div class="tax-checkboxes">
                                <label class="tax-label">
                                    <input type="checkbox" name="item_iva_19[general]" class="item-tax" data-tax="19" data-index="general">
                                    IVA 19%
                                </label>
                                <label class="tax-label">
                                    <input type="checkbox" name="item_iva_5[general]" class="item-tax" data-tax="5" data-index="general">
                                    IVA 5%
                                </label>
                                <label class="tax-label">
                                    <input type="checkbox" name="item_ipoconsumo_8[general]" class="item-tax" data-tax="8" data-index="general">
                                    Ipoconsumo 8%
                                </label>
                                <label class="tax-label">
                                    <input type="checkbox" name="item_ipoconsumo_4[general]" class="item-tax" data-tax="4" data-index="general">
                                    Ipoconsumo 4%
                                </label>
                            </div>
                        </div>
                    @else
                        <div class="alert warning">
                            No se encontraron items específicos en la solicitud original.
                        </div>
                    @endif
                    
                    <!-- Resumen de totales -->
                    <div class="totals-summary">
                        <h5>Resumen de Totales</h5>
                        <table class="totals-table">
                            <tbody>
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-right">$<span id="subtotal-display">0.00</span></td>
                                </tr>
                                <tr id="iva-19-row" style="display: none;">
                                    <td>IVA (19%):</td>
                                    <td class="text-right">$<span id="iva-19-display">0.00</span></td>
                                </tr>
                                <tr id="iva-5-row" style="display: none;">
                                    <td>IVA (5%):</td>
                                    <td class="text-right">$<span id="iva-5-display">0.00</span></td>
                                </tr>
                                <tr id="ipoconsumo-8-row" style="display: none;">
                                    <td>Ipoconsumo (8%):</td>
                                    <td class="text-right">$<span id="ipoconsumo-8-display">0.00</span></td>
                                </tr>
                                <tr id="ipoconsumo-4-row" style="display: none;">
                                    <td>Ipoconsumo (4%):</td>
                                    <td class="text-right">$<span id="ipoconsumo-4-display">0.00</span></td>
                                </tr>
                                <tr class="total-row">
                                    <td><strong>Total General:</strong></td>
                                    <td class="text-right"><strong>$<span id="total-general-display">0.00</span></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Información comercial -->
                <div class="form-section">
                    <h4>Información Comercial</h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="delivery_time">Tiempo de entrega</label>
                            <input type="text" name="delivery_time" id="delivery_time" value="{{ old('delivery_time') }}" placeholder="Ej: 15 días hábiles">
                            @if ($errors->has('delivery_time'))
                                <div class="error-message">{{ $errors->first('delivery_time') }}</div>
                            @endif
                            <small>Especifique el tiempo estimado de entrega</small>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Forma de pago</label>
                            <select name="payment_method" id="payment_method">
                                <option value="">-- Seleccione --</option>
                                <option value="Contado" {{ old('payment_method') == 'Contado' ? 'selected' : '' }}>Contado</option>
                                <option value="Crédito a 30 días" {{ old('payment_method') == 'Crédito a 30 días' ? 'selected' : '' }}>Crédito a 30 días</option>
                                <option value="Crédito a 60 días" {{ old('payment_method') == 'Crédito a 60 días' ? 'selected' : '' }}>Crédito a 60 días</option>
                                <option value="Anticipado" {{ old('payment_method') == 'Anticipado' ? 'selected' : '' }}>Pago anticipado</option>
                                <option value="Otro" {{ old('payment_method') == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @if ($errors->has('payment_method'))
                                <div class="error-message">{{ $errors->first('payment_method') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="validity">Validez de la oferta</label>
                            <input type="text" name="validity" id="validity" value="{{ old('validity') }}" placeholder="Ej: 30 días">
                            @if ($errors->has('validity'))
                                <div class="error-message">{{ $errors->first('validity') }}</div>
                            @endif
                            <small>Período de validez de la cotización</small>
                        </div>
                        <div class="form-group">
                            <label for="warranty">Garantía</label>
                            <input type="text" name="warranty" id="warranty" value="{{ old('warranty') }}" placeholder="Ej: 3 meses">
                            @if ($errors->has('warranty'))
                                <div class="error-message">{{ $errors->first('warranty') }}</div>
                            @endif
                            <small>Período de garantía ofrecido</small>
                        </div>
                    </div>
                </div>

                <!-- Archivo -->
                <div class="form-section">
                    <h4>Archivo de Cotización</h4>
                    
                    <div class="form-group">
                        <label for="quotation_file">Archivo de cotización (PDF) *</label>
                        <input type="file" name="quotation_file" id="quotation_file" accept="application/pdf" required>
                        @if ($errors->has('quotation_file'))
                            <div class="error-message">{{ $errors->first('quotation_file') }}</div>
                        @endif
                        <small>Solo se permiten archivos PDF (máx. 5MB)</small>
                    </div>
                </div>

                <!-- Botones -->
                <div class="form-actions">
                    <button type="submit" class="btn primary" id="submitBtn">Guardar cotización</button>
                    <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="btn secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de notificación -->
    <div id="notificationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Enviando notificación</h5>
            </div>
            <div class="modal-body">
                <div class="spinner"></div>
                <h5>Procesando cotización</h5>
                <p>Se está enviando la notificación al coordinador del área para pre-aprobación.</p>
                <p>Esto puede tomar unos momentos, por favor espere...</p>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variable para verificar si es la tercera cotización
        const isThirdQuotation = {{ $purchaseRequest->quotations->count() == 2 ? 'true' : 'false' }};

        // Envío del formulario
        document.getElementById('quotationForm').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                return true;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';
            
            if (isThirdQuotation) {
                document.getElementById('notificationModal').style.display = 'flex';
            }
            
            return true;
        });
        
        // Habilitar botón si hay errores
        if (document.querySelector('.error-message')) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Guardar cotización';
        }

        // ============ Funciones de cálculos ============
        
        function calculateTotals() {
            let totalSubtotal = 0;
            let totalIva19 = 0;
            let totalIva5 = 0;
            let totalIpoconsumo8 = 0;
            let totalIpoconsumo4 = 0;
            
            // Calcular total de cada item y sus impuestos
            document.querySelectorAll('.item-price').forEach(function(priceInput) {
                const quantity = parseFloat(priceInput.getAttribute('data-quantity')) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const itemTotal = quantity * price;
                const index = priceInput.getAttribute('data-index');
                
                // Encontrar el campo total correspondiente
                const row = priceInput.closest('tr') || priceInput.closest('.form-group');
                const totalInput = row.querySelector('.item-total') || document.getElementById('general_service_total');
                
                if (totalInput) {
                    totalInput.value = itemTotal.toFixed(2);
                }
                
                totalSubtotal += itemTotal;
                
                // Calcular impuestos por item
                const iva19Checkbox = document.querySelector(`input[name="item_iva_19[${index}]"]`);
                const iva5Checkbox = document.querySelector(`input[name="item_iva_5[${index}]"]`);
                const ipoconsumo8Checkbox = document.querySelector(`input[name="item_ipoconsumo_8[${index}]"]`);
                const ipoconsumo4Checkbox = document.querySelector(`input[name="item_ipoconsumo_4[${index}]"]`);
                
                if (iva19Checkbox && iva19Checkbox.checked) {
                    totalIva19 += itemTotal * 0.19;
                }
                if (iva5Checkbox && iva5Checkbox.checked) {
                    totalIva5 += itemTotal * 0.05;
                }
                if (ipoconsumo8Checkbox && ipoconsumo8Checkbox.checked) {
                    totalIpoconsumo8 += itemTotal * 0.08;
                }
                if (ipoconsumo4Checkbox && ipoconsumo4Checkbox.checked) {
                    totalIpoconsumo4 += itemTotal * 0.04;
                }
            });
            
            // También calcular impuestos globales si están marcados
            const includesIva19 = document.getElementById('includes_iva_19').checked;
            const includesIva5 = document.getElementById('includes_iva_5').checked;
            const includesIpoconsumo8 = document.getElementById('includes_ipoconsumo_8').checked;
            const includesIpoconsumo4 = document.getElementById('includes_ipoconsumo_4').checked;
            
            if (includesIva19) {
                totalIva19 = totalSubtotal * 0.19;
            }
            if (includesIva5) {
                totalIva5 = totalSubtotal * 0.05;
            }
            if (includesIpoconsumo8) {
                totalIpoconsumo8 = totalSubtotal * 0.08;
            }
            if (includesIpoconsumo4) {
                totalIpoconsumo4 = totalSubtotal * 0.04;
            }
            
            const totalTaxes = totalIva19 + totalIva5 + totalIpoconsumo8 + totalIpoconsumo4;
            const totalGeneral = totalSubtotal + totalTaxes;
            
            // Actualizar campos de impuestos
            document.getElementById('iva_19_amount').value = totalIva19.toFixed(2);
            document.getElementById('iva_5_amount').value = totalIva5.toFixed(2);
            document.getElementById('ipoconsumo_8_amount').value = totalIpoconsumo8.toFixed(2);
            document.getElementById('ipoconsumo_4_amount').value = totalIpoconsumo4.toFixed(2);
            
            // Campos ocultos para compatibilidad
            document.getElementById('includes_iva_hidden').value = (includesIva19 || totalIva19 > 0) ? '1' : '0';
            document.getElementById('iva_amount_hidden').value = totalIva19.toFixed(2);
            document.getElementById('subtotal_hidden').value = totalSubtotal.toFixed(2);
            document.getElementById('total_amount_hidden').value = totalGeneral.toFixed(2);
            
            // Determinar el modo de aplicación de impuestos
            const hasItemTaxes = document.querySelectorAll('.item-tax:checked').length > 0;
            const hasGlobalTaxes = includesIva19 || includesIva5 || includesIpoconsumo8 || includesIpoconsumo4;
            
            // Agregar campo oculto para el modo de impuestos
            let taxModeInput = document.getElementById('tax_application_mode_hidden');
            if (!taxModeInput) {
                taxModeInput = document.createElement('input');
                taxModeInput.type = 'hidden';
                taxModeInput.name = 'tax_application_mode';
                taxModeInput.id = 'tax_application_mode_hidden';
                document.getElementById('quotationForm').appendChild(taxModeInput);
            }
            taxModeInput.value = hasItemTaxes && !hasGlobalTaxes ? 'per_item' : 'global';
            
            // Actualizar resumen
            document.getElementById('subtotal-display').textContent = totalSubtotal.toFixed(2);
            document.getElementById('iva-19-display').textContent = totalIva19.toFixed(2);
            document.getElementById('iva-5-display').textContent = totalIva5.toFixed(2);
            document.getElementById('ipoconsumo-8-display').textContent = totalIpoconsumo8.toFixed(2);
            document.getElementById('ipoconsumo-4-display').textContent = totalIpoconsumo4.toFixed(2);
            document.getElementById('total-general-display').textContent = totalGeneral.toFixed(2);
            
            // Mostrar/ocultar filas de impuestos
            document.getElementById('iva-19-row').style.display = totalIva19 > 0 ? 'table-row' : 'none';
            document.getElementById('iva-5-row').style.display = totalIva5 > 0 ? 'table-row' : 'none';
            document.getElementById('ipoconsumo-8-row').style.display = totalIpoconsumo8 > 0 ? 'table-row' : 'none';
            document.getElementById('ipoconsumo-4-row').style.display = totalIpoconsumo4 > 0 ? 'table-row' : 'none';
        }
        
        // Event listeners para cálculos
        document.querySelectorAll('.item-price, .item-tax, #includes_iva_19, #includes_iva_5, #includes_ipoconsumo_8, #includes_ipoconsumo_4').forEach(function(element) {
            element.addEventListener('change', calculateTotals);
            element.addEventListener('input', calculateTotals);
        });
        
        // Sincronizar checkboxes globales con individuales
        document.getElementById('includes_iva_19').addEventListener('change', function() {
            if (this.checked) {
                document.querySelectorAll('.item-tax[data-tax="19"]').forEach(cb => cb.checked = false);
            }
        });
        
        document.getElementById('includes_iva_5').addEventListener('change', function() {
            if (this.checked) {
                document.querySelectorAll('.item-tax[data-tax="5"]').forEach(cb => cb.checked = false);
            }
        });
        
        document.getElementById('includes_ipoconsumo_8').addEventListener('change', function() {
            if (this.checked) {
                document.querySelectorAll('.item-tax[data-tax="8"]').forEach(cb => cb.checked = false);
            }
        });
        
        document.getElementById('includes_ipoconsumo_4').addEventListener('change', function() {
            if (this.checked) {
                document.querySelectorAll('.item-tax[data-tax="4"]').forEach(cb => cb.checked = false);
            }
        });
        
        // Desmarcar global si se marca individual
        document.querySelectorAll('.item-tax').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    const taxType = this.getAttribute('data-tax');
                    if (taxType === '19') document.getElementById('includes_iva_19').checked = false;
                    if (taxType === '5') document.getElementById('includes_iva_5').checked = false;
                    if (taxType === '8') document.getElementById('includes_ipoconsumo_8').checked = false;
                    if (taxType === '4') document.getElementById('includes_ipoconsumo_4').checked = false;
                }
            });
        });
        
        // Calcular totales al cargar
        calculateTotals();
    });
</script>
@stop

@section('css')
<style>
    /* Reset y estilos básicos */
    * {
        box-sizing: border-box;
    }
    
    .info-card, .form-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .info-header, .form-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
        border-radius: 8px 8px 0 0;
    }
    
    .info-header h3, .form-header h3 {
        margin: 0;
        color: #333;
        font-size: 18px;
        font-weight: 600;
    }
    
    .info-body, .form-body {
        padding: 20px;
    }
    
    /* Grid de información */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .info-item {
        padding: 12px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: #f8f9fa;
    }
    
    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 4px;
    }
    
    .info-value {
        font-size: 14px;
        font-weight: 500;
        color: #495057;
    }
    
    .justification-section {
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }
    
    /* Secciones del formulario */
    .form-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .form-section:last-of-type {
        border-bottom: none;
    }
    
    .form-section h4 {
        color: #333;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
    }
    
    /* Grupos de formulario */
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }
    
    input[type="text"],
    input[type="number"],
    input[type="file"],
    select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
        line-height: 1.5;
    }
    
    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    input[readonly] {
        background-color: #e9ecef;
        opacity: 1;
    }
    
    small {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #6c757d;
    }
    
    /* Configuración de impuestos */
    .tax-configuration-section {
        margin: 20px 0;
        padding: 15px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
    }
    
    .tax-configuration-section h5 {
        margin: 0 0 15px 0;
        color: #495057;
        font-size: 14px;
        font-weight: 600;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .tax-configuration-section .form-row {
        margin-top: 10px;
    }
    
    /* Checkboxes */
    input[type="checkbox"] {
        margin-right: 8px;
    }
    
    /* Impuestos por item */
    .tax-checkboxes {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .tax-label {
        display: flex;
        align-items: center;
        font-size: 11px;
        font-weight: normal;
        margin-bottom: 2px;
        white-space: nowrap;
    }
    
    .tax-label input[type="checkbox"] {
        margin-right: 4px;
        transform: scale(0.8);
    }
    
    /* Tabla de items - Estilo Excel simple */
    .items-table {
        margin: 20px 0;
        overflow-x: auto;
        border: 1px solid #000;
        background: white;
    }
    
    .items-table table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        border: none;
    }
    
    .items-table th {
        background: #f2f2f2;
        color: #000;
        font-weight: 600;
        padding: 8px;
        text-align: center;
        border: 1px solid #000;
        font-size: 11px;
    }
    
    .items-table td {
        padding: 8px 10px;
        border: 1px solid #000;
        background: white;
        vertical-align: middle;
        font-size: 13px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.4;
    }
    
    
    /* Inputs en la tabla - simple */
    .items-table input[type="number"] {
        width: 100%;
        border: 1px solid #000;
        background: white;
        padding: 5px 8px;
        font-size: 13px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-align: right;
        line-height: 1.3;
    }
    
    .items-table input[type="number"]:focus {
        border: 2px solid #000;
        outline: none;
    }
    
    .items-table input[readonly] {
        background: #f0f0f0;
        color: #000;
        border: 1px solid #000;
    }
    
    /* Checkboxes simples */
    .tax-checkboxes {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 2px;
    }
    
    .tax-label {
        display: flex;
        align-items: center;
        font-size: 11px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin-bottom: 1px;
        white-space: nowrap;
        padding: 2px 3px;
        line-height: 1.3;
    }
    
    .tax-label input[type="checkbox"] {
        margin-right: 4px;
    }
    
    .tax-label input[type="checkbox"]:checked + span {
        font-weight: 600;
    }
    
    /* Quitar colores especiales de celdas */
    .items-table td:first-child {
        font-weight: normal;
        color: #000;
        border: 1px solid #000;
        background: white;
        font-size: 13px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.4;
    }
    
    .items-table td:nth-child(2) {
        text-align: center;
        font-weight: 500;
        color: #000;
        background: white;
        font-size: 13px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.4;
    }
    
    .text-center {
        text-align: center;
    }
    
    .text-right {
        text-align: right;
    }
    
    /* Resumen de totales - Estilo Excel simple */
    .totals-summary {
        margin-top: 20px;
        padding: 0;
        background: white;
        border: 1px solid #000;
    }
    
    .totals-summary h5 {
        margin: 0;
        padding: 8px 12px;
        background: #f2f2f2;
        color: #000;
        font-weight: 600;
        font-size: 12px;
        border-bottom: 1px solid #000;
    }
    
    .totals-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    
    .totals-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #000;
        font-size: 13px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: white;
        line-height: 1.4;
    }
    
    .totals-table td:first-child {
        font-weight: 500;
        color: #000;
        background: white;
    }
    
    .totals-table td:last-child {
        text-align: right;
        font-weight: 600;
        color: #000;
        font-size: 13px;
    }
    
    .total-row td {
        background: #f2f2f2 !important;
        color: #000 !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        padding: 8px 12px !important;
        border-bottom: 1px solid #000 !important;
    }
    
    .total-row td:first-child {
        border-left: none !important;
    }
    
    /* Estilos generales para otras tablas */
    table:not(.items-table table):not(.totals-table) {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #ddd;
    }
    
    table:not(.items-table table):not(.totals-table) th, 
    table:not(.items-table table):not(.totals-table) td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    table:not(.items-table table):not(.totals-table) th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    
    /* Alertas */
    .alert {
        padding: 12px 15px;
        margin-bottom: 15px;
        border: 1px solid transparent;
        border-radius: 4px;
    }
    
    .alert.error {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    
    .alert.info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }
    
    .alert.warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeeba;
    }
    
    .alert ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
    }
    
    /* Botones */
    .btn {
        display: inline-block;
        padding: 10px 20px;
        margin: 5px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn.primary {
        background-color: #007bff;
        color: white;
    }
    
    .btn.primary:hover {
        background-color: #0056b3;
    }
    
    .btn.secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .btn.secondary:hover {
        background-color: #5a6268;
    }
    
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .form-actions {
        text-align: center;
        padding-top: 20px;
        margin-top: 20px;
        border-top: 1px solid #e9ecef;
    }
    
    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        margin: 20px;
    }
    
    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .modal-header h5 {
        margin: 0;
        color: #333;
    }
    
    .modal-body {
        padding: 20px;
        text-align: center;
    }
    
    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .info-grid,
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-actions .btn {
            display: block;
            width: 100%;
            margin: 5px 0;
        }
        
        table {
            font-size: 12px;
        }
        
        th, td {
            padding: 8px;
        }
    }
</style>
@stop
