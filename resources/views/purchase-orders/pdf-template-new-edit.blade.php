@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            @include('layouts.partials.alerts')

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Editar PDF de Orden de Compra #{{ $order->order_number }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('purchase-orders.show', $order->id) }}" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <a href="{{ url('storage/' . $order->file_path) }}" class="btn btn-sm btn-outline-light" target="_blank">
                            <i class="fas fa-file-pdf"></i> Ver PDF actual
                        </a>
                    </div>
                </div>

                @php
                    // Obtener datos personalizados guardados si existen
                    $customData = !empty($order->pdf_custom_data) ? 
                        (is_array($order->pdf_custom_data) ? $order->pdf_custom_data : json_decode($order->pdf_custom_data, true)) 
                        : json_decode($order->pdf_custom_data, true);
                
                    // Determinar si es servicio o compra y obtener items correspondientes
                    $isService = $order->purchaseRequest->type === 'services';
                
                    // Variables para información fiscal
                    $ivaPercent = $order->iva_percent ?? 19;
                    $includesIva = $order->includes_iva ?? false;
                
                    // Variables para selección mixta
                    $isMixedSelection = false;
                    $hasMultipleQuotations = false;
                    $alternativeQuotations = collect();
                
                    // Usar las selecciones ya filtradas enviadas desde el controlador
                    if (isset($providerSpecificSelections) && $providerSpecificSelections->count() > 0) {
                        $mixedSelections = $providerSpecificSelections;
                        $isMixedSelection = true;
                
                        // DEBUG: Registrar lo que está recibiendo la vista
                        \Log::info('🔍 VISTA PDF-TEMPLATE-NEW-EDIT recibiendo datos', [
                            'order' => $order->order_number,
                            'provider' => $order->provider->nombre,
                            'mixed_selections_count' => $providerSpecificSelections->count(),
                            'provider_selections_items' => $providerSpecificSelections->pluck('item_description')->toArray()
                        ]);
                    }
                
                    // Verificar cotizaciones alternativas
                    $purchaseRequest = $order->purchaseRequest;
                    if ($purchaseRequest->quotations()->count() > 1) {
                        $hasMultipleQuotations = true;
                
                        // Obtener cotizaciones alternativas
                        $alternativeQuotations = $purchaseRequest->quotations()
                            ->where('id', '!=', $purchaseRequest->selected_quotation_id)
                            ->where('provider_name', '!=', $order->provider->nombre)
                            ->get();
                    }
                
                    // Determinación de secciones compartidas
                    $isSharedPurchase = false;
                    $sharedSections = [];
                
                    // Usar datos de secciones compartidas si existen en datos personalizados
                    if ($customData && isset($customData['shared_sections'])) {
                        $isSharedPurchase = true;
                
                        // Si hay datos guardados como string, mantenerlos
                        $sharedSections = is_string($customData['shared_sections']) 
                            ? explode(',', $customData['shared_sections']) 
                            : $customData['shared_sections'];
                
                        // Si es array pero está como índices numéricos, convertirlo
                        if (isset($sharedSections[0]) && is_array($sharedSections[0])) {
                            $sharedSections = array_column($sharedSections, 'name');
                        }
                    }
                
                    // Analizar observaciones para detectar patrón de compra compartida
                    $observations = strtolower($purchaseRequest->observations ?? '');
                    $customObservations = strtolower($customData['observations'] ?? '');
                    $allObservations = strtolower($observations . ' ' . $customObservations);
                
                    // Buscar patrones de compra compartida en las observaciones
                    if (strpos($allObservations, 'compartida') !== false || 
                        strpos($allObservations, 'compartido') !== false ||
                        strpos($allObservations, 'shared') !== false ||
                        strpos($allObservations, 'secciones:') !== false) {
                
                        $isSharedPurchase = true;
                
                        // Extraer secciones mencionadas en las observaciones con lógica mejorada
                        $extractedSections = [];
                
                        // Buscar patrones específicos de secciones
                        if (strpos($allObservations, 'escuela alta') !== false || strpos($allObservations, 'dp') !== false) {
                            $extractedSections[] = 'Escuela Alta';
                        }
                        if (strpos($allObservations, 'escuela media') !== false || (strpos($allObservations, 'media') !== false && strpos($allObservations, 'escuela') !== false)) {
                            $extractedSections[] = 'Escuela Media';
                        }
                        if (strpos($allObservations, 'preescolar') !== false) {
                            $extractedSections[] = 'Preescolar';
                        }
                        if (strpos($allObservations, 'educación física') !== false || strpos($allObservations, 'ed fisica') !== false || strpos($allObservations, 'ef') !== false) {
                            $extractedSections[] = 'Educación Física';
                        }
                        if (strpos($allObservations, 'artes') !== false) {
                            $extractedSections[] = 'Artes';
                        }
                        if (strpos($allObservations, 'mantenimiento') !== false) {
                            $extractedSections[] = 'Mantenimiento';
                        }
                        if (strpos($allObservations, 'tecnología') !== false || strpos($allObservations, 'tecnologia') !== false) {
                            $extractedSections[] = 'Tecnología';
                        }
                        
                        // Si encontramos secciones en observaciones, usarlas
                        if (!empty($extractedSections)) {
                            $sharedSections = array_merge($sharedSections, $extractedSections);
                            $sharedSections = array_unique($sharedSections);
                        }
                    }
                
                    // Buscar en los items de compra información de secciones
                    $purchaseItems = $purchaseRequestData->purchase_items ? 
                        (is_array($purchaseRequestData->purchase_items) ? $purchaseRequestData->purchase_items : json_decode($purchaseRequestData->purchase_items, true)) : [];
                
                    // Verificar si hay múltiples secciones mencionadas en los items
                    $sectionMentionsInItems = [];
                    if (is_array($purchaseItems)) {
                        foreach ($purchaseItems as $item) {
                            $itemDesc = strtolower($item['description'] ?? '');
                            if (strpos($itemDesc, 'escuela alta') !== false || strpos($itemDesc, 'dp') !== false) {
                                $sectionMentionsInItems['Escuela Alta'] = true;
                            }
                            if (strpos($itemDesc, 'escuela media') !== false) {
                                $sectionMentionsInItems['Escuela Media'] = true;
                            }
                            if (strpos($itemDesc, 'preescolar') !== false) {
                                $sectionMentionsInItems['Preescolar'] = true;
                            }
                        }
                        
                        // Si encontramos al menos 2 secciones diferentes, es una compra compartida
                        if (count($sectionMentionsInItems) >= 2) {
                            $isSharedPurchase = true;
                            $sharedSections = array_merge($sharedSections, array_keys($sectionMentionsInItems));
                            $sharedSections = array_unique($sharedSections);
                        }
                    }
                
                    // Inicializar variables para mostrar items
                    $itemNumber = 1;
                    
                    // Obtener purchase_items como fallback si no hay quotationItemSelections
                    $purchaseItems = [];
                
                    // Si tenemos selecciones específicas del proveedor, usar esos datos
                    if (isset($providerSpecificSelections) && $providerSpecificSelections->count() > 0) {
                        // 🚨 CORRECCIÓN CRÍTICA: Si hay selecciones específicas del proveedor (selección mixta), usar esos precios reales
                        foreach ($providerSpecificSelections as $selection) {
                            // Para selecciones mixtas, usar precios reales de las cotizaciones
                            echo "<!-- DEBUG: Usando precios reales de selecciones. Count: " . $providerSpecificSelections->count() . " -->";
                            
                            $purchaseItems[] = [
                                'description' => $selection->item_description,
                                'quantity' => $selection->quantity,
                                'unit_price' => $selection->unit_price, // Usar precio real de la cotización
                                'total' => $selection->total_price // Usar total real de la cotización
                            ];
                        }
                    } elseif ($isService) {
                        // Para servicios, obtener items del servicio
                        $serviceItems = $order->purchaseRequest->service_items ?? [];
                        if (is_string($serviceItems)) {
                            $serviceItems = json_decode($serviceItems, true) ?? [];
                        }
                        
                        $totalServiceItems = array_sum(array_column($serviceItems, 'quantity'));
                        $pricePerItem = $totalServiceItems > 0 ? ($order->total_amount / $totalServiceItems) : 0;
                        
                        foreach ($serviceItems as $serviceItem) {
                            $description = $serviceItem['description'] ?? '';
                            $quantity = $serviceItem['quantity'] ?? 1;
                            $unitPrice = $totalServiceItems > 0 ? ($order->total_amount / $totalServiceItems) : 0;
                            
                            $purchaseItems[] = [
                                'description' => $description,
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'total' => $unitPrice * $quantity
                            ];
                        }
                    } else {
                        // Para productos regulares, obtener datos de cotización si está disponible
                        $quotation = $selectedQuotation ?? $order->purchaseRequest->selectedQuotation;
                        
                        if ($quotation && isset($quotation->items) && is_array($quotation->items)) {
                            foreach ($quotation->items as $index => $item) {
                                $precioUnitario = 0;
                                
                                if (isset($quotation->original_item_prices[$index])) {
                                    $precioUnitario = $quotation->original_item_prices[$index];
                                }
                                
                                $purchaseItems[] = [
                                    'description' => $item['description'] ?? '',
                                    'quantity' => $item['quantity'] ?? 1,
                                    'unit_price' => $precioUnitario,
                                    'total' => $precioUnitario * ($item['quantity'] ?? 1)
                                ];
                            }
                        } else {
                            // Fallback: Usar datos del purchase_request
                            $items = $order->purchaseRequest->purchase_items ?? [];
                            if (is_string($items)) {
                                $items = json_decode($items, true) ?? [];
                            }
                            
                            // Calcular precio promedio si no tenemos precios específicos
                            $totalItems = array_sum(array_column($items, 'quantity'));
                            $avgPrice = $totalItems > 0 ? ($order->subtotal / $totalItems) : 0;
                            
                            foreach ($items as $item) {
                                $purchaseItems[] = [
                                    'description' => $item['description'] ?? '',
                                    'quantity' => $item['quantity'] ?? 1,
                                    'unit_price' => $avgPrice,
                                    'total' => $avgPrice * ($item['quantity'] ?? 1)
                                ];
                            }
                        }
                    }
                @endphp

                <form id="pdf-edit-form" method="POST" action="{{ route('purchase-orders.update-pdf', $order->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <!-- Información del Proveedor -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider-select">Proveedor:</label>
                                    <select name="provider_id" id="provider-select" class="form-control select2">
                                        @foreach(\App\Models\Proveedor::orderBy('nombre')->get() as $provider)
                                            <option value="{{ $provider->id }}" 
                                                    data-name="{{ $provider->nombre }}"
                                                    data-nit="{{ $provider->nit }}"
                                                    data-email="{{ $provider->email }}"
                                                    data-phone="{{ $provider->telefono }}"
                                                    data-address="{{ $provider->direccion }}"
                                                    data-city="{{ $provider->ciudad }}"
                                                    {{ $order->provider_id == $provider->id ? 'selected' : '' }}>
                                                {{ $provider->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="provider_name" id="provider-name-input" 
                                           value="{{ $customData['provider_name'] ?? $order->provider->nombre ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider-nit">NIT:</label>
                                    <input type="text" class="form-control" id="provider-nit" name="provider_nit" 
                                           value="{{ $customData['provider_nit'] ?? $order->provider->nit ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider-email">Email:</label>
                                    <input type="email" class="form-control" id="provider-email" name="provider_email" 
                                           value="{{ $customData['provider_email'] ?? $order->provider->email ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider-phone">Teléfono:</label>
                                    <input type="text" class="form-control" id="provider-phone" name="provider_phone" 
                                           value="{{ $customData['provider_phone'] ?? $order->provider->telefono ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="provider-address">Dirección:</label>
                                    <input type="text" class="form-control" id="provider-address" name="provider_address" 
                                           value="{{ $customData['provider_address'] ?? $order->provider->direccion ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Información de la Orden -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment-method">Forma de Pago:</label>
                                    <select class="form-control" id="payment-method" name="payment_method">
                                        <option value="Contado" {{ ($customData['payment_method'] ?? $order->payment_terms ?? '') == 'Contado' ? 'selected' : '' }}>Contado</option>
                                        <option value="Crédito 15 días" {{ ($customData['payment_method'] ?? $order->payment_terms ?? '') == 'Crédito 15 días' ? 'selected' : '' }}>Crédito 15 días</option>
                                        <option value="Crédito 30 días" {{ ($customData['payment_method'] ?? $order->payment_terms ?? '') == 'Crédito 30 días' ? 'selected' : '' }}>Crédito 30 días</option>
                                        <option value="Crédito 45 días" {{ ($customData['payment_method'] ?? $order->payment_terms ?? '') == 'Crédito 45 días' ? 'selected' : '' }}>Crédito 45 días</option>
                                        <option value="Crédito 60 días" {{ ($customData['payment_method'] ?? $order->payment_terms ?? '') == 'Crédito 60 días' ? 'selected' : '' }}>Crédito 60 días</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="delivery-address">Dirección de Entrega:</label>
                                    <input type="text" class="form-control" id="delivery-address" name="delivery_address" 
                                           value="{{ $customData['delivery_address'] ?? 'Transversal 28 No. 39-80 Sur, Bogotá D.C.' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="budget">Presupuesto:</label>
                                    <select class="form-control" id="budget" name="budget">
                                        <option value="">Seleccione un presupuesto</option>
                                        @foreach($budgetOptions as $group)
                                            <optgroup label="{{ $group['name'] }}">
                                                @foreach($group['options'] as $option)
                                                    <option value="{{ $option }}" 
                                                            {{ ($customData['budget'] ?? '') == $option ? 'selected' : '' }}>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="includes-iva">IVA:</label>
                                    <select class="form-control" id="includes-iva" name="includes_iva">
                                        <option value="1" {{ $includesIva ? 'selected' : '' }}>Incluido</option>
                                        <option value="0" {{ !$includesIva ? 'selected' : '' }}>No incluido</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Items -->
                        <h4 class="mt-4">Productos/Servicios</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="items-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 45%;">Descripción</th>
                                        <th style="width: 10%;" class="text-center">Cantidad</th>
                                        <th style="width: 15%;" class="text-right">Precio Unitario</th>
                                        <th style="width: 15%;" class="text-right">Total</th>
                                        <th style="width: 10%;" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseItems as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $itemNumber++ }}</td>
                                            <td>
                                                <textarea name="items[{{ $index }}][description]" 
                                                          class="form-control form-control-sm" 
                                                          rows="2">{{ $item['description'] }}</textarea>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="items[{{ $index }}][quantity]" 
                                                       class="form-control form-control-sm item-quantity" 
                                                       value="{{ $item['quantity'] }}" 
                                                       data-index="{{ $index }}">
                                            </td>
                                            <td class="text-right">
                                                @php
                                                    // Intentar obtener precio unitario de la cotización seleccionada como en pdf-template-new
                                                    $precioUnitario = 0;
                                                    if ($selectedQuotation && isset($selectedQuotation->original_item_prices) && isset($selectedQuotation->original_item_prices[$index])) {
                                                        $precioUnitario = $selectedQuotation->original_item_prices[$index];
                                                        // Registrar que estamos usando el precio original
                                                        \Log::info('✅ PLANTILLA EDIT: Usando precio original de cotización', [
                                                            'item' => $item['description'],
                                                            'precio_original' => $precioUnitario
                                                        ]);
                                                    } else {
                                                        $precioUnitario = $item['unit_price'];
                                                    }
                                                @endphp
                                                <input type="text" name="items[{{ $index }}][unit_price]" 
                                                       class="form-control form-control-sm item-price" 
                                                       value="{{ $precioUnitario }}" 
                                                       data-index="{{ $index }}">
                                            </td>
                                            <td class="text-right">
                                                <span class="item-total" data-index="{{ $index }}">
                                                    ${{ number_format($precioUnitario * $item['quantity'], 0, ',', '.') }}
                                                </span>
                                                <input type="hidden" name="items[{{ $index }}][total]" 
                                                       class="item-total-input" 
                                                       value="{{ $precioUnitario * $item['quantity'] }}"
                                                       data-index="{{ $index }}">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-item"
                                                        data-index="{{ $index }}">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6">
                                            <button type="button" class="btn btn-success btn-sm" id="add-item">
                                                <i class="fas fa-plus"></i> Agregar Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Totales -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="observations">Observaciones:</label>
                                    <textarea class="form-control" id="observations" name="observations" rows="3">{{ $customData['observations'] ?? $order->purchaseRequest->observations ?? '' }}</textarea>
                                </div>
                                
                                @if($isSharedPurchase)
                                <div class="form-group">
                                    <label>Distribución de Presupuesto:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable-shared-budget" name="enable_shared_budget" 
                                               {{ !empty($customData['shared_budget_info']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable-shared-budget">
                                            Definir distribución específica del presupuesto
                                        </label>
                                    </div>
                                    <div id="shared-budget-container" class="mt-2" style="{{ !empty($customData['shared_budget_info']) ? '' : 'display: none;' }}">
                                        @foreach($sharedSections as $section)
                                            <div class="input-group mb-2">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">{{ $section }}</span>
                                                </div>
                                                <input type="text" class="form-control section-percentage" 
                                                       name="shared_budget_info[{{ $section }}]" 
                                                       value="{{ $customData['shared_budget_info'][$section] ?? '' }}"
                                                       placeholder="Ej: 50%">
                                                <input type="hidden" name="shared_sections[]" value="{{ $section }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Subtotal:</th>
                                        <td class="text-right">
                                            <span id="subtotal-display">$0</span>
                                            <input type="hidden" name="subtotal" id="subtotal-input" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>IVA ({{ $ivaPercent }}%):</th>
                                        <td class="text-right">
                                            <span id="iva-display">$0</span>
                                            <input type="hidden" name="iva_amount" id="iva-input" value="0">
                                        </td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <th>Total:</th>
                                        <td class="text-right">
                                            <span id="total-display">$0</span>
                                            <input type="hidden" name="total_amount" id="total-input" value="0">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('purchase-orders.show', $order->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Variables globales
        let ivaPercent = {{ $ivaPercent }};
        let includesIva = {{ $includesIva ? 'true' : 'false' }};
        let nextItemIndex = {{ count($purchaseItems) }};

        // Al cambiar el proveedor, actualizar campos relacionados
        $('#provider-select').change(function() {
            const selectedOption = $(this).find('option:selected');
            $('#provider-name-input').val(selectedOption.data('name'));
            $('#provider-nit').val(selectedOption.data('nit'));
            $('#provider-email').val(selectedOption.data('email'));
            $('#provider-phone').val(selectedOption.data('phone'));
            $('#provider-address').val(selectedOption.data('address'));
        });

        // Habilitar/deshabilitar distribución de presupuesto compartido
        $('#enable-shared-budget').change(function() {
            $('#shared-budget-container').toggle($(this).is(':checked'));
        });

        // Actualizar cálculos cuando cambia la opción de IVA
        $('#includes-iva').change(function() {
            includesIva = $(this).val() == '1';
            calculateTotals();
        });

        // Funciones para manejar items
        $('#add-item').click(function() {
            const newRow = `
                <tr>
                    <td class="text-center">${$('#items-table tbody tr').length + 1}</td>
                    <td>
                        <textarea name="items[${nextItemIndex}][description]" 
                                class="form-control form-control-sm" 
                                rows="2"></textarea>
                    </td>
                    <td class="text-center">
                        <input type="number" name="items[${nextItemIndex}][quantity]" 
                            class="form-control form-control-sm item-quantity" 
                            value="1" 
                            data-index="${nextItemIndex}">
                    </td>
                    <td class="text-right">
                        <input type="text" name="items[${nextItemIndex}][unit_price]" 
                            class="form-control form-control-sm item-price" 
                            value="0" 
                            data-index="${nextItemIndex}">
                    </td>
                    <td class="text-right">
                        <span class="item-total" data-index="${nextItemIndex}">$0</span>
                        <input type="hidden" name="items[${nextItemIndex}][total]" 
                            class="item-total-input" 
                            value="0"
                            data-index="${nextItemIndex}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-item"
                                data-index="${nextItemIndex}">
                            <i class="fas fa-minus"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#items-table tbody').append(newRow);
            nextItemIndex++;
            updateItemNumbers();
            bindItemEvents();
        });

        // Eliminar un item
        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            updateItemNumbers();
            calculateTotals();
        });

        // Actualizar números de fila
        function updateItemNumbers() {
            $('#items-table tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Calcular el total para un item específico
        function calculateItemTotal(index) {
            const quantity = parseFloat($('.item-quantity[data-index="' + index + '"]').val()) || 0;
            const price = parseFloat($('.item-price[data-index="' + index + '"]').val()) || 0;
            const total = quantity * price;
            
            $('.item-total[data-index="' + index + '"]').text('$' + formatNumber(total));
            $('.item-total-input[data-index="' + index + '"]').val(total);
            
            return total;
        }

        // Calcular todos los totales
        function calculateTotals() {
            let subtotal = 0;
            
            // Calcular cada item y sumar al subtotal
            $('#items-table tbody tr').each(function() {
                const index = $(this).find('.item-quantity').data('index');
                subtotal += calculateItemTotal(index);
            });
            
            // Calcular IVA y total final
            let ivaAmount = 0;
            let total = 0;
            
            if (includesIva) {
                // Si el precio incluye IVA, el subtotal es precio / (1 + iva%)
                const divisor = 1 + (ivaPercent / 100);
                const subtotalSinIva = subtotal / divisor;
                ivaAmount = subtotal - subtotalSinIva;
                total = subtotal;
            } else {
                // Si el IVA no está incluido, se suma al subtotal
                ivaAmount = subtotal * (ivaPercent / 100);
                total = subtotal + ivaAmount;
            }
            
            // Actualizar los campos y visualizaciones
            $('#subtotal-display').text('$' + formatNumber(subtotal));
            $('#subtotal-input').val(subtotal);
            
            $('#iva-display').text('$' + formatNumber(ivaAmount));
            $('#iva-input').val(ivaAmount);
            
            $('#total-display').text('$' + formatNumber(total));
            $('#total-input').val(total);
        }

        // Vincular eventos a items
        function bindItemEvents() {
            $('.item-quantity, .item-price').off('input').on('input', function() {
                const index = $(this).data('index');
                calculateItemTotal(index);
                calculateTotals();
            });
        }

        // Formatear números para visualización
        function formatNumber(num) {
            return new Intl.NumberFormat('es-CO', { 
                minimumFractionDigits: 0,
                maximumFractionDigits: 0 
            }).format(num);
        }

        // Inicializar eventos y cálculos
        bindItemEvents();
        calculateTotals();
    });
</script>
@endpush
