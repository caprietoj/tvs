@extends('adminlte::page')

@section('title', 'Editar PDF - Orden de Compra')

@section('content_header')
    <h1>Editar PDF - Orden de Compra {{ $order->order_number }}</h1>
@stop

@section('content')
    @php
        // Obtener datos personalizados si existen
        $customData = [];
        if (!empty($order->pdf_custom_data)) {
            $customData = json_decode($order->pdf_custom_data, true);
        }

        // Obtener la cotización seleccionada si existe
        $selectedQuotation = null;
        if ($order->purchaseRequest && $order->purchaseRequest->quotations->isNotEmpty()) {
            $selectedQuotation = $order->purchaseRequest->quotations->where('selected', 1)->first();
            if (!$selectedQuotation) {
                $selectedQuotation = $order->purchaseRequest->quotations->first();
            }
        }

        // Si no hay cotización seleccionada, usar selectedQuotation del modelo
        if (!$selectedQuotation && $order->purchaseRequest && $order->purchaseRequest->selectedQuotation) {
            $selectedQuotation = $order->purchaseRequest->selectedQuotation;
        }

        // Detectar si es una orden de selección mixta
        $isMixedSelection = false;
        $mixedSelections = collect();
        if ($order->purchaseRequest) {
            $mixedSelections = $order->purchaseRequest->quotationItemSelections()->with('quotation')->get();
            $isMixedSelection = $mixedSelections->count() > 0;
        }

        // Si es selección mixta, agrupar por proveedor para facilitar la separación
        $providerGroups = [];
        if ($isMixedSelection) {
            $providerGroups = $mixedSelections->groupBy('quotation.provider_name')->map(function ($items, $providerName) {
                return [
                    'provider_name' => $providerName,
                    'quotation_id' => $items->first()->quotation_id,
                    'items' => $items,
                    'total' => $items->sum('total_price')
                ];
            });
        }

        // Detectar órdenes con múltiples cotizaciones (potencial para separación alternativa)
        $hasMultipleQuotations = false;
        $alternativeQuotations = collect();
        if ($order->purchaseRequest && $order->purchaseRequest->quotations) {
            $quotations = $order->purchaseRequest->quotations;
            $hasMultipleQuotations = $quotations->count() > 1;
            $alternativeQuotations = $quotations->filter(function($quotation) use ($order) {
                return $quotation->provider_name !== $order->provider->nombre;
            });
        }

        // Obtener purchase_items como fallback si no hay quotationItemSelections
        $purchaseItems = [];
        if ($order->purchaseRequest && $order->purchaseRequest->purchase_items) {
            $purchaseItems = is_array($order->purchaseRequest->purchase_items) 
                ? $order->purchaseRequest->purchase_items 
                : json_decode($order->purchaseRequest->purchase_items, true);
        }

        // Si no hay purchase_items, intentar con service_items
        if (empty($purchaseItems) && $order->purchaseRequest && $order->purchaseRequest->service_items) {
            $serviceItems = is_array($order->purchaseRequest->service_items) 
                ? $order->purchaseRequest->service_items 
                : json_decode($order->purchaseRequest->service_items, true);
            
            // Convertir service_items al formato esperado
            if (is_array($serviceItems)) {
                $totalServiceItems = count($serviceItems);
                $pricePerItem = $totalServiceItems > 0 ? ($order->total_amount / $totalServiceItems) : 0;
                
                foreach ($serviceItems as $serviceItem) {
                    $quantity = $serviceItem['quantity'] ?? 1;
                    $unitPrice = $totalServiceItems > 0 ? ($order->total_amount / $totalServiceItems) : 0;
                    
                    $purchaseItems[] = [
                        'description' => $serviceItem['description'] ?? '',
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $unitPrice * $quantity
                    ];
                }
            }
        }

        // Si no hay items, intentar con material_items
        if (empty($purchaseItems) && $order->purchaseRequest && $order->purchaseRequest->material_items) {
            $materialItems = is_array($order->purchaseRequest->material_items) 
                ? $order->purchaseRequest->material_items 
                : json_decode($order->purchaseRequest->material_items, true);
            
            if (is_array($materialItems)) {
                foreach ($materialItems as $materialItem) {
                    $purchaseItems[] = [
                        'description' => $materialItem['description'] ?? $materialItem['material'] ?? '',
                        'quantity' => $materialItem['quantity'] ?? 1,
                        'unit_price' => isset($materialItem['cost']) ? $materialItem['cost'] / ($materialItem['quantity'] ?? 1) : 0,
                        'total' => $materialItem['cost'] ?? 0
                    ];
                }
            }
        }
    @endphp

    <!-- Alerta sobre selección mixta -->
    @if($isMixedSelection && count($providerGroups) > 1)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Selección Mixta Detectada:</strong> Esta orden contiene productos de {{ count($providerGroups) }} proveedores diferentes.
            Si necesita separar esta orden por proveedor, puede utilizar las herramientas disponibles más abajo.
            <br><small>Proveedores: {{ implode(', ', array_keys($providerGroups->toArray())) }}</small>
        </div>
    @elseif($hasMultipleQuotations && $alternativeQuotations->count() > 0)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Múltiples Cotizaciones Disponibles:</strong> Esta orden fue asignada a <strong>{{ $order->provider->nombre }}</strong>, pero hay {{ $alternativeQuotations->count() }} proveedor(es) alternativo(s) disponible(s).
            <br><small>Proveedores alternativos: {{ $alternativeQuotations->pluck('provider_name')->implode(', ') }}</small>
            <br><small class="text-muted">Puede crear una orden alternativa con un proveedor diferente usando las herramientas más abajo.</small>
        </div>
    @endif

    <form id="pdf-edit-form" method="POST" action="{{ route('purchase-orders.update-pdf', $order->id) }}">
        @csrf
        @method('PUT')
        
        <div class="card">
            <div class="card-body">
                <!-- Réplica exacta del PDF como HTML editable -->
                <div style="font-family: Arial, sans-serif; font-size: 12px; max-width: 800px; margin: 0 auto;">
                    
                    <!-- Aviso de envío de facturas -->
                    <div style="font-size: 10px; background-color: #ffffcc; padding: 5px; border: 1px solid #ccc; margin-bottom: 10px;">
                        El envío de las facturas se debe realizar al correo 830097105@recepciondefacturas.co para poder realizar las respectivas aceptaciones y acuse de facturas ante la DIAN.
                    </div>

                    <!-- Título principal -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr>
                            <td colspan="6" style="text-align: center; font-weight: bold; font-size: 14px; padding: 10px; background-color: #e0e0e0; border: 1px solid #000;">
                                FORMATO DE ORDEN DE COMPRA Y/O SERVICIO COLEGIO VICTORIA S.A.S
                            </td>
                        </tr>
                    </table>

                    <!-- Encabezado principal -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr>
                            <td colspan="4" style="text-align: center; font-weight: bold; background-color: #f0f0f0; border: 1px solid #000; padding: 4px;">ORDEN DE COMPRA/SERVICIO</td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">FECHA</td>
                            <td style="border: 1px solid #000; padding: 4px;">{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">Consecutivo COM</td>
                            <td colspan="3" style="border: 1px solid #000; padding: 4px;">{{ $order->order_number }}</td>
                            <td style="border: 1px solid #000; padding: 4px;"></td>
                            <td style="border: 1px solid #000; padding: 4px;"></td>
                        </tr>
                    </table>

                    <!-- Información del proveedor -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; width: 15%;">PROVEEDOR:</td>
                            <td style="border: 1px solid #000; padding: 4px; width: 35%;">
                                <select name="provider_id" id="provider-select" class="form-control form-control-sm" 
                                        style="width: 100%; border: none; background: transparent;">
                                    <option value="">Seleccionar proveedor...</option>
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
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; width: 15%;">NIT/CC:</td>
                            <td style="border: 1px solid #000; padding: 4px; width: 35%;">
                                <input type="text" name="provider_nit" id="provider-nit-input" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_nit'] ?? $order->provider->nit ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">DIRECCIÓN:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_address" id="provider-address-input" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_address'] ?? $order->provider->direccion ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">TELÉFONO:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_phone" id="provider-phone-input" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_phone'] ?? $order->provider->telefono ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">CIUDAD:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_city" id="provider-city-input" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_city'] ?? $order->provider->ciudad ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">E-MAIL:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_email" id="provider-email-input" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_email'] ?? $order->provider->email ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                        </tr>
                    </table>

                    <!-- Información de entrega -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">ENTREGAR EN:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="delivery_address" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['delivery_address'] ?? 'COLEGIO VICTORIA CALLE 32 F SUR 17G 26' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">FORMA DE PAGO:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="payment_method" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['payment_method'] ?? $order->payment_method ?? 'Contado' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">RESPONSABLE DE LA COMPRA:</td>
                            <td style="border: 1px solid #000; padding: 4px;">{{ $order->purchaseRequest->user->name ?? '' }}</td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">FECHA ENTREGA:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="date" name="delivery_date" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['delivery_date'] ?? $order->delivery_date?->format('Y-m-d') ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                        </tr>
                    </table>

                    <!-- Items de la orden -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr style="background-color: #f0f0f0;">
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 6%;">ITEM</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 42%;">DESCRIPCIÓN</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 8%;">CANT</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 14%;">VALOR UNIT</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 18%;">VALOR TOTAL</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 12%;">ACCIONES</td>
                        </tr>
                        
                        @php $itemNumber = 1; @endphp
                        
                        {{-- Mostrar items de quotationItemSelections si existen --}}
                        @if($selectedQuotation && $selectedQuotation->quotationItemSelections && $selectedQuotation->quotationItemSelections->count() > 0)
                            @foreach($selectedQuotation->quotationItemSelections as $item)
                                <tr>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">{{ $itemNumber++ }}</td>
                                    <td style="border: 1px solid #000; padding: 4px;">
                                        <textarea name="items[{{ $loop->index }}][description]" 
                                                class="form-control form-control-sm border-0" 
                                                style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 20px;"
                                                rows="2">{{ $customData['items'][$loop->index]['description'] ?? $item->item_description ?? '' }}</textarea>
                                    </td>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                        <input type="number" name="items[{{ $loop->index }}][quantity]" 
                                               class="form-control form-control-sm border-0 item-quantity" 
                                               value="{{ $customData['items'][$loop->index]['quantity'] ?? $item->quantity ?? 0 }}" 
                                               style="width: 100%; border: none; background: transparent; text-align: center;"
                                               data-index="{{ $loop->index }}">
                                    </td>
                                    <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                        <input type="number" name="items[{{ $loop->index }}][unit_price]" 
                                               class="form-control form-control-sm border-0 item-price" 
                                               value="{{ $customData['items'][$loop->index]['unit_price'] ?? $item->unit_price ?? 0 }}" 
                                               style="width: 100%; border: none; background: transparent; text-align: right;"
                                               data-index="{{ $loop->index }}">
                                    </td>
                                    <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                        <span class="item-total" data-index="{{ $loop->index }}">
                                            ${{ number_format(($customData['items'][$loop->index]['total'] ?? $item->total_price ?? 0), 0, ',', '.') }}
                                        </span>
                                        <input type="hidden" name="items[{{ $loop->index }}][total]" 
                                               class="item-total-input" 
                                               value="{{ $customData['items'][$loop->index]['total'] ?? $item->total_price ?? 0 }}"
                                               data-index="{{ $loop->index }}">
                                    </td>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                        <button type="button" 
                                                class="btn btn-outline-dark btn-sm" 
                                                onclick="removeItem({{ $loop->index }}, 'quotation')"
                                                title="Eliminar item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        {{-- Si no hay quotationItemSelections, mostrar items de customData --}}
                        @elseif(!empty($customData['items']) && is_array($customData['items']))
                            @foreach($customData['items'] as $index => $item)
                                @if(!empty($item['description']) || !empty($item['quantity']) || !empty($item['unit_price']))
                                    <tr>
                                        <td style="text-align: center; border: 1px solid #000; padding: 4px;">{{ $itemNumber++ }}</td>
                                        <td style="border: 1px solid #000; padding: 4px;">
                                            <textarea name="items[{{ $index }}][description]" 
                                                    class="form-control form-control-sm border-0" 
                                                    style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 20px;"
                                                    rows="2">{{ $item['description'] ?? '' }}</textarea>
                                        </td>
                                        <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                            <input type="number" name="items[{{ $index }}][quantity]" 
                                                   class="form-control form-control-sm border-0 item-quantity" 
                                                   value="{{ floatval($item['quantity'] ?? 0) }}" 
                                                   style="width: 100%; border: none; background: transparent; text-align: center;"
                                                   data-index="{{ $index }}">
                                        </td>
                                        <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                            <input type="number" name="items[{{ $index }}][unit_price]" 
                                                   class="form-control form-control-sm border-0 item-price" 
                                                   value="{{ floatval($item['unit_price'] ?? 0) }}" 
                                                   style="width: 100%; border: none; background: transparent; text-align: right;"
                                                   data-index="{{ $index }}">
                                        </td>
                                        <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                            <span class="item-total" data-index="{{ $index }}">
                                                ${{ number_format(floatval($item['total'] ?? (floatval($item['quantity'] ?? 0) * floatval($item['unit_price'] ?? 0))), 0, ',', '.') }}
                                            </span>
                                            <input type="hidden" name="items[{{ $index }}][total]" 
                                                   class="item-total-input" 
                                                   value="{{ floatval($item['total'] ?? (floatval($item['quantity'] ?? 0) * floatval($item['unit_price'] ?? 0))) }}"
                                                   data-index="{{ $index }}">
                                        </td>
                                        <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                            <button type="button" 
                                                    class="btn btn-outline-dark btn-sm" 
                                                    onclick="removeItem({{ $index }}, 'custom')"
                                                    title="Eliminar item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        {{-- Si no hay customData, usar purchase_items originales --}}
                        @elseif(!empty($purchaseItems) && is_array($purchaseItems))
                            @foreach($purchaseItems as $index => $item)
                                <tr>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">{{ $itemNumber++ }}</td>
                                    <td style="border: 1px solid #000; padding: 4px;">
                                        <textarea name="items[{{ $index }}][description]" 
                                                class="form-control form-control-sm border-0" 
                                                style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 20px;"
                                                rows="2">{{ $item['description'] ?? '' }}</textarea>
                                    </td>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control form-control-sm border-0 item-quantity" 
                                               value="{{ $item['quantity'] ?? 0 }}" 
                                               style="width: 100%; border: none; background: transparent; text-align: center;"
                                               data-index="{{ $index }}">
                                    </td>
                                    <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                        <input type="number" name="items[{{ $index }}][unit_price]" 
                                               class="form-control form-control-sm border-0 item-price" 
                                               value="{{ $order->total_amount && count($purchaseItems) > 0 ? round($order->total_amount / array_sum(array_column($purchaseItems, 'quantity'))) : 0 }}" 
                                               style="width: 100%; border: none; background: transparent; text-align: right;"
                                               data-index="{{ $index }}">
                                    </td>
                                    <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                        <span class="item-total" data-index="{{ $index }}">
                                            @php
                                                $totalForItem = $order->total_amount && count($purchaseItems) > 0 
                                                    ? round(($order->total_amount / array_sum(array_column($purchaseItems, 'quantity'))) * $item['quantity']) 
                                                    : 0;
                                            @endphp
                                            ${{ number_format($totalForItem, 0, ',', '.') }}
                                        </span>
                                        <input type="hidden" name="items[{{ $index }}][total]" 
                                               class="item-total-input" 
                                               value="{{ $totalForItem }}"
                                               data-index="{{ $index }}">
                                    </td>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                        <button type="button" 
                                                class="btn btn-outline-dark btn-sm" 
                                                onclick="removeItem({{ $index }}, 'purchase')"
                                                title="Eliminar item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        {{-- Si no hay items, mostrar una fila vacía para comenzar --}}
                        @else
                            <tr>
                                <td style="text-align: center; border: 1px solid #000; padding: 4px;">{{ $itemNumber++ }}</td>
                                <td style="border: 1px solid #000; padding: 4px;">
                                    <textarea name="items[0][description]" 
                                            class="form-control form-control-sm border-0" 
                                            placeholder="Descripción del item"
                                            style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 20px;"
                                            rows="2"></textarea>
                                </td>
                                <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                    <input type="number" name="items[0][quantity]" 
                                           class="form-control form-control-sm border-0 item-quantity" 
                                           value="0" 
                                           style="width: 100%; border: none; background: transparent; text-align: center;"
                                           data-index="0">
                                </td>
                                <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                    <input type="number" name="items[0][unit_price]" 
                                           class="form-control form-control-sm border-0 item-price" 
                                           value="0" 
                                           style="width: 100%; border: none; background: transparent; text-align: right;"
                                           data-index="0">
                                </td>
                                <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                    <span class="item-total" data-index="0">$0</span>
                                    <input type="hidden" name="items[0][total]" 
                                           class="item-total-input" 
                                           value="0"
                                           data-index="0">
                                </td>
                                <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                    <button type="button" 
                                            class="btn btn-outline-dark btn-sm" 
                                            onclick="removeItem(0, 'empty')"
                                            title="Eliminar item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif

                        <!-- Items adicionales editables (solo mostrar si tienen datos o para agregar nuevos) -->
                        @for($i = 0; $i < 3; $i++)
                            @php
                                $hasData = isset($customData['additional_items'][$i]) && 
                                          (!empty($customData['additional_items'][$i]['description']) || 
                                           !empty($customData['additional_items'][$i]['quantity']) || 
                                           !empty($customData['additional_items'][$i]['unit_price']));
                                $showRow = $hasData || $i == 0; // Siempre mostrar al menos la primera fila para nuevos items
                            @endphp
                            
                            @if($showRow)
                                <tr>
                                    <td style="border: 1px solid #000; padding: 4px;">{{ $itemNumber++ }}</td>
                                    <td style="border: 1px solid #000; padding: 4px;">
                                        <textarea name="additional_items[{{ $i }}][description]" 
                                                class="form-control form-control-sm border-0" 
                                                placeholder="Descripción adicional"
                                                style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 20px;"
                                                rows="2">{{ $customData['additional_items'][$i]['description'] ?? '' }}</textarea>
                                    </td>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                        <input type="number" name="additional_items[{{ $i }}][quantity]" 
                                               class="form-control form-control-sm border-0 additional-quantity" 
                                               value="{{ floatval($customData['additional_items'][$i]['quantity'] ?? 0) ?: '' }}" 
                                               style="width: 100%; border: none; background: transparent; text-align: center;"
                                               data-index="{{ $i }}">
                                    </td>
                                    <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                        <input type="number" name="additional_items[{{ $i }}][unit_price]" 
                                               class="form-control form-control-sm border-0 additional-price" 
                                               value="{{ floatval($customData['additional_items'][$i]['unit_price'] ?? 0) ?: '' }}" 
                                               style="width: 100%; border: none; background: transparent; text-align: right;"
                                               data-index="{{ $i }}">
                                    </td>
                                    <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                        <span class="additional-total" data-index="{{ $i }}">
                                            @php
                                                $additionalTotal = floatval($customData['additional_items'][$i]['total'] ?? 0);
                                            @endphp
                                            @if($additionalTotal > 0)
                                                ${{ number_format($additionalTotal, 0, ',', '.') }}
                                            @else
                                                $0
                                            @endif
                                        </span>
                                        <input type="hidden" name="additional_items[{{ $i }}][total]" 
                                               class="additional-total-input" 
                                               value="{{ $additionalTotal }}"
                                               data-index="{{ $i }}">
                                    </td>
                                    <td style="text-align: center; border: 1px solid #000; padding: 4px;">
                                        <button type="button" 
                                                class="btn btn-outline-dark btn-sm" 
                                                onclick="removeAdditionalItem({{ $i }})"
                                                title="Eliminar item adicional">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @endfor

                        <!-- Observaciones -->
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">Observaciones:</td>
                            <td colspan="3" style="border: 1px solid #000; padding: 4px;">
                                <textarea name="observations" 
                                        class="form-control form-control-sm border-0" 
                                        style="width: 100%; border: none; background: transparent; resize: vertical; min-height: 20px;"
                                        rows="3">{{ $customData['observations'] ?? $order->observations ?? '' }}</textarea>
                            </td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: right;">-</td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center;">-</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px;"></td>
                            <td colspan="3" style="border: 1px solid #000; padding: 4px;"></td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: right;">-</td>
                        </tr>
                    </table>

                    <!-- Sección de aprobación y totales -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; width: 20%;">APROBACIÓN</td>
                            <td style="border: 1px solid #000; padding: 4px; width: 30%;">{{ $order->purchaseRequest->approver->name ?? '' }}</td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; width: 20%;">SUB TOTAL</td>
                            <td style="text-align: right; border: 1px solid #000; padding: 4px; width: 30%;">
                                <span id="calculated-subtotal">${{ number_format($customData['subtotal'] ?? $order->subtotal ?? 0, 0, ',', '.') }}</span>
                                <input type="hidden" name="subtotal" id="subtotal-input" value="{{ $customData['subtotal'] ?? $order->subtotal ?? 0 }}">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">FECHA:</td>
                            <td style="border: 1px solid #000; padding: 4px;">{{ $order->purchaseRequest->approval_date ? $order->purchaseRequest->approval_date->format('d/m/Y') : '' }}</td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">
                                <select name="iva_rate" id="iva-rate" class="form-control form-control-sm border-0" style="border: none; background: transparent;">
                                    <option value="0" {{ ($customData['iva_rate'] ?? '0%') == '0%' ? 'selected' : '' }}>Sin IVA</option>
                                    <option value="5" {{ ($customData['iva_rate'] ?? '') == '5%' ? 'selected' : '' }}>IVA (5%)</option>
                                    <option value="19" {{ ($customData['iva_rate'] ?? '') == '19%' ? 'selected' : '' }}>IVA (19%)</option>
                                </select>
                            </td>
                            <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                <span id="calculated-iva">${{ number_format($customData['iva_amount'] ?? 0, 0, ',', '.') }}</span>
                                <input type="hidden" name="iva_amount" id="iva-amount-input" value="{{ $customData['iva_amount'] ?? 0 }}">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">PRESUPUESTO:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <select name="budget" class="form-control form-control-sm border-0" 
                                        style="width: 100%; border: none; background: transparent;">
                                    <option value="">Seleccionar presupuesto...</option>
                                    @foreach($budgetOptions as $option)
                                        <option value="{{ $option }}" 
                                                {{ ($customData['budget'] ?? $order->purchaseRequest->budget ?? '') == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">
                                <select name="ipoconsumo_rate" id="ipoconsumo-rate" class="form-control form-control-sm border-0" style="border: none; background: transparent;">
                                    <option value="0" {{ ($customData['ipoconsumo_rate'] ?? '0%') == '0%' ? 'selected' : '' }}>Sin Imp. Consumo</option>
                                    <option value="4" {{ ($customData['ipoconsumo_rate'] ?? '') == '4%' ? 'selected' : '' }}>Imp. Consumo (4%)</option>
                                    <option value="8" {{ ($customData['ipoconsumo_rate'] ?? '') == '8%' ? 'selected' : '' }}>Imp. Consumo (8%)</option>
                                    <option value="16" {{ ($customData['ipoconsumo_rate'] ?? '') == '16%' ? 'selected' : '' }}>Imp. Consumo (16%)</option>
                                </select>
                            </td>
                            <td style="text-align: right; border: 1px solid #000; padding: 4px;">
                                <span id="calculated-ipoconsumo">${{ number_format($customData['ipoconsumo_amount'] ?? 0, 0, ',', '.') }}</span>
                                <input type="hidden" name="ipoconsumo_amount" id="ipoconsumo-amount-input" value="{{ $customData['ipoconsumo_amount'] ?? 0 }}">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">SOLICITUD Nº:</td>
                            <td style="border: 1px solid #000; padding: 4px;">{{ $order->purchaseRequest->request_number ?? '' }}</td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; background-color: #ffff99;">TOTAL A PAGAR</td>
                            <td style="text-align: right; font-weight: bold; border: 1px solid #000; padding: 4px; background-color: #ffff99;">
                                <span id="calculated-total">${{ number_format($customData['total'] ?? $order->total_amount ?? 0, 0, ',', '.') }}</span>
                                <input type="hidden" name="total" id="total-input" value="{{ $customData['total'] ?? $order->total_amount ?? 0 }}">
                            </td>
                        </tr>
                    </table>

                    <!-- Información adicional -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; width: 50%;">
                                ESTA ORDEN DE COMPRA DEBE SER FIRMADA Y SELLADA POR EL PROVEEDOR COMO CONSTANCIA DE ACEPTACIÓN.
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; width: 50%;">
                                RECIBE CONFORME Y SATISFACCIÓN (FIRMA)
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; padding: 30px; text-align: center;">
                                <br><br>
                                <div style="border-top: 1px solid #000; margin-top: 20px; padding-top: 5px;">
                                    PROVEEDOR
                                </div>
                            </td>
                            <td style="border: 1px solid #000; padding: 30px; text-align: center;">
                                <br><br>
                                <div style="border-top: 1px solid #000; margin-top: 20px; padding-top: 5px;">
                                    COLEGIO VICTORIA
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Información de contacto -->
                    <div style="font-size: 10px; margin-top: 15px; text-align: center;">
                        Calle 32F Sur 17G-26 | PBX: 601 3601373 Ext. 3001 | Cel: 318 404 1373 | Email: compras@colegiovictoria.edu.co
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="{{ route('purchase-orders.show', $order->id) }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="{{ route('purchase-orders.pdf', $order->id) }}" class="btn btn-success btn-lg ml-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> Ver PDF
                </a>
            </div>
        </div>
    </form>

    <!-- Herramientas para Selección Mixta -->
    @if($isMixedSelection && count($providerGroups) > 1)
        <div class="card mt-4">
            <div class="card-header bg-warning">
                <h5 class="m-0">
                    <i class="fas fa-tools mr-2"></i>
                    Herramientas de Selección Mixta
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Esta orden contiene productos de múltiples proveedores.</strong><br>
                    Puede separar esta orden por proveedor para generar órdenes individuales.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Proveedor</th>
                                <th>Cantidad de Items</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providerGroups as $group)
                                <tr>
                                    <td><strong>{{ $group['provider_name'] }}</strong></td>
                                    <td>{{ $group['items']->count() }} items</td>
                                    <td>${{ number_format($group['total'], 0, ',', '.') }}</td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary"
                                                onclick="createSeparateOrder('{{ $group['provider_name'] }}', {{ $group['quotation_id'] }})">
                                            <i class="fas fa-plus"></i> Crear Orden Separada
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger ml-1"
                                                onclick="removeProviderItems('{{ $group['provider_name'] }}', {{ $group['quotation_id'] }})">
                                            <i class="fas fa-trash"></i> Remover de esta Orden
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <h6>Detalles de Items por Proveedor:</h6>
                    @foreach($providerGroups as $group)
                        <div class="card mt-2">
                            <div class="card-header">
                                <h6 class="m-0">{{ $group['provider_name'] }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group['items'] as $item)
                                                <tr>
                                                    <td>{{ $item->item_description }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                    <td>${{ number_format($item->total_price, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Herramientas para Órdenes con Múltiples Cotizaciones -->
    @if($hasMultipleQuotations && $alternativeQuotations->count() > 0 && !$isMixedSelection)
        <div class="card mt-4">
            <div class="card-header bg-info">
                <h5 class="m-0">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Herramientas de Proveedores Alternativos
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>¡Atención!</strong> Esta orden tiene proveedores alternativos disponibles que podrían ofrecer mejores precios.
                    <br><small class="text-muted">Proveedor actual: <strong>{{ $order->provider->nombre }}</strong></small>
                </div>

                <!-- Botón de Reversión -->
                <div class="mb-3 p-3 border rounded" style="background-color: #fff3cd;">
                    <h6 class="mb-2">
                        <i class="fas fa-undo text-warning mr-2"></i>
                        <strong>¿Cometió un error en la selección?</strong>
                    </h6>
                    <p class="mb-2 text-muted">
                        Si debería haber hecho una selección mixta entre múltiples proveedores, 
                        puede revertir esta orden y volver al proceso de selección.
                    </p>
                    <button type="button" 
                            class="btn btn-warning"
                            onclick="showRevertToMixedModal()">
                        <i class="fas fa-undo mr-2"></i>
                        Revertir a Selección Múltiple
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Proveedor Alternativo</th>
                                <th>Total de Cotización</th>
                                <th>Diferencia con Actual</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alternativeQuotations as $quotation)
                                @php
                                    $difference = $quotation->total_amount - $order->total_amount;
                                    $differenceClass = $difference < 0 ? 'text-success' : 'text-danger';
                                    $differenceIcon = $difference < 0 ? 'fas fa-arrow-down' : 'fas fa-arrow-up';
                                @endphp
                                <tr>
                                    <td><strong>{{ $quotation->provider_name }}</strong></td>
                                    <td>${{ number_format($quotation->total_amount, 0, ',', '.') }}</td>
                                    <td class="{{ $differenceClass }}">
                                        <i class="{{ $differenceIcon }}"></i>
                                        ${{ number_format(abs($difference), 0, ',', '.') }}
                                        {{ $difference < 0 ? '(Ahorro)' : '(Adicional)' }}
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary"
                                                onclick="createAlternativeOrder({{ $quotation->id }}, '{{ $quotation->provider_name }}', {{ $quotation->total_amount }})">
                                            <i class="fas fa-copy"></i> Crear Orden Alternativa
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-muted">
                    <small>
                        <strong>Nota:</strong> Al crear una orden alternativa, se generará una nueva orden de compra 
                        basada en la cotización seleccionada. La orden actual no se modificará.
                    </small>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para confirmación de orden alternativa -->
    <div id="alternativeOrderModal" class="custom-modal">
        <div class="custom-modal-overlay"></div>
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h3 class="custom-modal-title">Confirmar Orden Alternativa</h3>
                <button type="button" class="custom-modal-close" onclick="closeAlternativeOrderModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="custom-modal-body">
                <div class="confirmation-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <p class="confirmation-message">
                    ¿Está seguro de crear una orden alternativa con el proveedor <strong id="modal-provider-name"></strong>?
                </p>
                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Total de la cotización:</span>
                        <span class="detail-value" id="modal-total-amount"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Orden actual:</span>
                        <span class="detail-value">{{ $order->order_number }}</span>
                    </div>
                </div>
                <div class="confirmation-note">
                    <i class="fas fa-info-circle"></i>
                    <span>Esto creará una nueva orden basada en esta cotización. La orden actual no se modificará.</span>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="custom-btn custom-btn-secondary" onclick="closeAlternativeOrderModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="custom-btn custom-btn-primary" onclick="confirmAlternativeOrder()">
                    <i class="fas fa-check"></i> Crear Orden Alternativa
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para reversión a selección múltiple -->
    <div id="revertToMixedModal" class="custom-modal">
        <div class="custom-modal-overlay"></div>
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h3 class="custom-modal-title">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                    Revertir a Selección Múltiple
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeRevertToMixedModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="custom-modal-body">
                <div class="confirmation-icon">
                    <i class="fas fa-undo" style="color: #ffc107;"></i>
                </div>
                <p class="confirmation-message">
                    <strong>¿Está seguro de revertir esta orden a selección múltiple?</strong>
                </p>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-info-circle mr-1"></i> Esto significa que:</h6>
                    <ul class="mb-0">
                        <li>Se eliminará la orden actual <strong>{{ $order->order_number }}</strong></li>
                        <li>Se restaurará la solicitud de compra al estado de "selección pendiente"</li>
                        <li>Podrá hacer una nueva selección mixta entre múltiples proveedores</li>
                        <li>Esta acción NO se puede deshacer</li>
                    </ul>
                </div>

                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Solicitud de compra:</span>
                        <span class="detail-value">{{ $order->purchaseRequest->request_number ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Cotizaciones disponibles:</span>
                        <span class="detail-value">{{ $alternativeQuotations->count() + 1 }} proveedores</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Ahorro potencial estimado:</span>
                        <span class="detail-value text-success">
                            @php
                                $maxSaving = 0;
                                foreach($alternativeQuotations as $q) {
                                    $saving = $order->total_amount - $q->total_amount;
                                    if($saving > $maxSaving) $maxSaving = $saving;
                                }
                            @endphp
                            @if($maxSaving > 0)
                                Hasta ${{ number_format($maxSaving, 0, ',', '.') }}
                            @else
                                No estimado
                            @endif
                        </span>
                    </div>
                </div>

                <div class="confirmation-note">
                    <i class="fas fa-lightbulb"></i>
                    <span>
                        <strong>Recomendación:</strong> Use esta opción solo si cometió un error en la selección original. 
                        Si solo necesita una orden adicional, use "Crear Orden Alternativa" en su lugar.
                    </span>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="custom-btn custom-btn-secondary" onclick="closeRevertToMixedModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="custom-btn" style="background-color: #ffc107; border-color: #ffc107; color: #212529;" onclick="confirmRevertToMixed()">
                    <i class="fas fa-undo"></i> Sí, Revertir Orden
                </button>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Función para formatear números
    function formatNumber(num) {
        return new Intl.NumberFormat('es-CO').format(Math.round(num || 0));
    }

    // Función para calcular total de un item
    function calculateItemTotal(index, isAdditional = false) {
        const prefix = isAdditional ? 'additional' : 'item';
        const quantity = parseFloat($(`input[data-index="${index}"].${prefix}-quantity`).val()) || 0;
        const price = parseFloat($(`input[data-index="${index}"].${prefix}-price`).val()) || 0;
        const total = quantity * price;
        
        $(`.${prefix}-total[data-index="${index}"]`).text('$' + formatNumber(total));
        $(`.${prefix}-total-input[data-index="${index}"]`).val(total);
        
        return total;
    }

    // Función para calcular subtotal
    function calculateSubtotal() {
        let subtotal = 0;
        
        // Sumar items regulares (excepto los eliminados)
        $('.item-total-input').each(function() {
            const row = $(this).closest('tr');
            if (!row.hasClass('deleted-item')) {
                subtotal += parseFloat($(this).val()) || 0;
            }
        });
        
        // Sumar items adicionales (excepto los eliminados)
        $('.additional-total-input').each(function() {
            const row = $(this).closest('tr');
            if (!row.hasClass('deleted-additional-item')) {
                subtotal += parseFloat($(this).val()) || 0;
            }
        });
        
        $('#calculated-subtotal').text('$' + formatNumber(subtotal));
        $('#subtotal-input').val(subtotal);
        
        return subtotal;
    }

    // Función para calcular IVA
    function calculateIva(subtotal) {
        const ivaRate = parseFloat($('#iva-rate').val()) || 0;
        const ivaAmount = subtotal * (ivaRate / 100);
        
        $('#calculated-iva').text('$' + formatNumber(ivaAmount));
        $('#iva-amount-input').val(ivaAmount);
        
        return ivaAmount;
    }

    // Función para calcular Impuesto al Consumo
    function calculateIpoconsumo(subtotal) {
        const ipoconsumoRate = parseFloat($('#ipoconsumo-rate').val()) || 0;
        const ipoconsumoAmount = subtotal * (ipoconsumoRate / 100);
        
        $('#calculated-ipoconsumo').text('$' + formatNumber(ipoconsumoAmount));
        $('#ipoconsumo-amount-input').val(ipoconsumoAmount);
        
        return ipoconsumoAmount;
    }

    // Función para calcular total final
    function calculateTotal() {
        const subtotal = calculateSubtotal();
        const ivaAmount = calculateIva(subtotal);
        const ipoconsumoAmount = calculateIpoconsumo(subtotal);
        const total = subtotal + ivaAmount + ipoconsumoAmount;
        
        $('#calculated-total').text('$' + formatNumber(total));
        $('#total-input').val(total);
        
        return total;
    }

    // Event listeners para items regulares
    $('.item-quantity, .item-price').on('input', function() {
        const index = $(this).data('index');
        calculateItemTotal(index);
        calculateTotal();
    });

    // Event listeners para items adicionales
    $('.additional-quantity, .additional-price').on('input', function() {
        const index = $(this).data('index');
        calculateItemTotal(index, true);
        calculateTotal();
    });

    // Event listeners para cambios de impuestos
    $('#iva-rate, #ipoconsumo-rate').on('change', function() {
        calculateTotal();
    });

    // Calcular totales iniciales
    calculateTotal();

    // Autocarga de datos del proveedor
    $('#provider-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val()) {
            $('#provider-name-input').val(selectedOption.data('name'));
            $('#provider-nit-input').val(selectedOption.data('nit'));
            $('#provider-email-input').val(selectedOption.data('email'));
            $('#provider-phone-input').val(selectedOption.data('phone'));
            $('#provider-address-input').val(selectedOption.data('address'));
            $('#provider-city-input').val(selectedOption.data('city'));
        } else {
            // Limpiar campos si no se selecciona proveedor
            $('#provider-name-input, #provider-nit-input, #provider-email-input, #provider-phone-input, #provider-address-input, #provider-city-input').val('');
        }
    });

    // Funciones para selección mixta
    window.createSeparateOrder = function(providerName, quotationId) {
        if (confirm(`¿Está seguro de crear una orden separada para ${providerName}?\n\nEsto creará una nueva orden con solo los items de este proveedor.`)) {
            // Crear formulario para enviar solicitud
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("purchase-orders.separate-mixed-order", $order->id) }}'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{ csrf_token() }}'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'quotation_id',
                value: quotationId
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'provider_name',
                value: providerName
            }));
            
            $('body').append(form);
            form.submit();
        }
    };

    window.removeProviderItems = function(providerName, quotationId) {
        if (confirm(`¿Está seguro de remover todos los items de ${providerName} de esta orden?\n\nEsta acción no se puede deshacer.`)) {
            // Crear formulario para enviar solicitud
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("purchase-orders.remove-provider-items", $order->id) }}'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{ csrf_token() }}'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: '_method',
                value: 'DELETE'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'quotation_id',
                value: quotationId
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'provider_name',
                value: providerName
            }));
            
            $('body').append(form);
            form.submit();
        }
    };

    window.createAlternativeOrder = function(quotationId, providerName, totalAmount) {
        // Almacenar datos para usar en la confirmación
        window.pendingAlternativeOrder = {
            quotationId: quotationId,
            providerName: providerName,
            totalAmount: totalAmount
        };
        
        // Formatear el total
        const formattedTotal = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(totalAmount);
        
        // Actualizar contenido del modal
        document.getElementById('modal-provider-name').textContent = providerName;
        document.getElementById('modal-total-amount').textContent = formattedTotal;
        
        // Mostrar el modal
        window.showAlternativeOrderModal();
    };

    window.showAlternativeOrderModal = function() {
        const modal = document.getElementById('alternativeOrderModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevenir scroll del body
    };

    window.closeAlternativeOrderModal = function() {
        const modal = document.getElementById('alternativeOrderModal');
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restaurar scroll del body
        
        // Limpiar datos pendientes
        window.pendingAlternativeOrder = null;
    };

    window.confirmAlternativeOrder = function() {
        if (!window.pendingAlternativeOrder) {
            console.error('No hay datos de orden alternativa pendientes');
            return;
        }
        
        const { quotationId, providerName } = window.pendingAlternativeOrder;
        
        // Cerrar el modal
        window.closeAlternativeOrderModal();
        
        // Mostrar indicador de carga (opcional)
        // showLoading();
        
        // Crear formulario para enviar solicitud
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("purchase-orders.create-alternative-order", $order->id) }}'
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'quotation_id',
            value: quotationId
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: 'provider_name',
            value: providerName
        }));
        
        $('body').append(form);
        form.submit();
    };

    // Cerrar modal al hacer clic en el overlay
    $(document).on('click', '.custom-modal-overlay', function() {
        window.closeAlternativeOrderModal();
    });

    // Cerrar modal con la tecla Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#alternativeOrderModal').hasClass('show')) {
            window.closeAlternativeOrderModal();
        }
        if (e.key === 'Escape' && $('#revertToMixedModal').hasClass('show')) {
            window.closeRevertToMixedModal();
        }
    });

    // Funciones para modal de reversión
    window.showRevertToMixedModal = function() {
        const modal = document.getElementById('revertToMixedModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeRevertToMixedModal = function() {
        const modal = document.getElementById('revertToMixedModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    };

    window.confirmRevertToMixed = function() {
        // Cerrar el modal
        window.closeRevertToMixedModal();
        
        // Crear formulario para enviar solicitud de reversión
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("purchase-orders.revert-to-mixed-selection", $order->id) }}'
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: '_method',
            value: 'DELETE'
        }));
        
        $('body').append(form);
        form.submit();
    };

    // Cerrar modal de reversión al hacer clic en el overlay
    $(document).on('click', '#revertToMixedModal .custom-modal-overlay', function() {
        window.closeRevertToMixedModal();
    });

    // Funciones para eliminar items
    window.removeItem = function(index, type) {
        if (confirm('¿Está seguro de que desea eliminar este item?')) {
            // Buscar la fila correspondiente
            let row;
            switch(type) {
                case 'quotation':
                case 'custom':
                case 'purchase':
                case 'empty':
                    row = $(`input[data-index="${index}"].item-quantity`).closest('tr');
                    break;
            }
            
            if (row && row.length > 0) {
                // Marcar la fila como eliminada para envío del formulario
                row.addClass('deleted-item');
                
                // Agregar un campo oculto para marcar como eliminado
                row.append($('<input>', {
                    type: 'hidden',
                    name: `items[${index}][deleted]`,
                    value: 'true'
                }));
                
                // Limpiar los valores visibles
                row.find('textarea, input[type="text"], input[type="number"]:not([name*="[deleted]"])').val('');
                row.find('.item-total').text('$0');
                row.find('.item-total-input').val('0');
                
                // Añadir efecto visual de eliminado
                row.css({
                    'opacity': '0.5',
                    'background-color': '#f8d7da'
                });
                
                // Cambiar el botón de eliminar
                const deleteBtn = row.find('button[onclick*="removeItem"]');
                deleteBtn.removeClass('btn-outline-dark')
                        .addClass('btn-outline-secondary')
                        .attr('onclick', `restoreItem(${index}, '${type}')`)
                        .attr('title', 'Restaurar item')
                        .html('<i class="fas fa-undo"></i>');
                
                // Recalcular totales
                calculateTotal();
                
                console.log(`Item ${index} marcado como eliminado (tipo: ${type})`);
            }
        }
    };

    window.restoreItem = function(index, type) {
        // Buscar la fila correspondiente
        let row;
        switch(type) {
            case 'quotation':
            case 'custom':
            case 'purchase':
            case 'empty':
                row = $(`input[data-index="${index}"].item-quantity`).closest('tr');
                break;
        }
        
        if (row && row.length > 0) {
            // Quitar la marca de eliminado
            row.removeClass('deleted-item');
            
            // Remover el campo oculto de eliminado
            row.find(`input[name="items[${index}][deleted]"]`).remove();
            
            // Restaurar estilos visuales
            row.css({
                'opacity': '1',
                'background-color': ''
            });
            
            // Cambiar el botón de vuelta a eliminar
            const restoreBtn = row.find('button[onclick*="restoreItem"]');
            restoreBtn.removeClass('btn-outline-secondary')
                     .addClass('btn-outline-dark')
                     .attr('onclick', `removeItem(${index}, '${type}')`)
                     .attr('title', 'Eliminar item')
                     .html('<i class="fas fa-times"></i>');
            
            // Recalcular totales
            calculateTotal();
            
            console.log(`Item ${index} restaurado (tipo: ${type})`);
        }
    };

    window.removeAdditionalItem = function(index) {
        if (confirm('¿Está seguro de que desea eliminar este item adicional?')) {
            // Buscar la fila correspondiente
            const row = $(`input[data-index="${index}"].additional-quantity`).closest('tr');
            
            if (row && row.length > 0) {
                // Marcar la fila como eliminada
                row.addClass('deleted-additional-item');
                
                // Agregar un campo oculto para marcar como eliminado
                row.append($('<input>', {
                    type: 'hidden',
                    name: `additional_items[${index}][deleted]`,
                    value: 'true'
                }));
                
                // Limpiar los valores visibles
                row.find('textarea, input[type="text"], input[type="number"]:not([name*="[deleted]"])').val('');
                row.find('.additional-total').text('$0');
                row.find('.additional-total-input').val('0');
                
                // Añadir efecto visual de eliminado
                row.css({
                    'opacity': '0.5',
                    'background-color': '#f8d7da'
                });
                
                // Cambiar el botón de eliminar
                const deleteBtn = row.find('button[onclick*="removeAdditionalItem"]');
                deleteBtn.removeClass('btn-outline-dark')
                        .addClass('btn-outline-secondary')
                        .attr('onclick', `restoreAdditionalItem(${index})`)
                        .attr('title', 'Restaurar item adicional')
                        .html('<i class="fas fa-undo"></i>');
                
                // Recalcular totales
                calculateTotal();
                
                console.log(`Item adicional ${index} marcado como eliminado`);
            }
        }
    };

    window.restoreAdditionalItem = function(index) {
        // Buscar la fila correspondiente
        const row = $(`input[data-index="${index}"].additional-quantity`).closest('tr');
        
        if (row && row.length > 0) {
            // Quitar la marca de eliminado
            row.removeClass('deleted-additional-item');
            
            // Remover el campo oculto de eliminado
            row.find(`input[name="additional_items[${index}][deleted]"]`).remove();
            
            // Restaurar estilos visuales
            row.css({
                'opacity': '1',
                'background-color': ''
            });
            
            // Cambiar el botón de vuelta a eliminar
            const restoreBtn = row.find('button[onclick*="restoreAdditionalItem"]');
            restoreBtn.removeClass('btn-outline-secondary')
                     .addClass('btn-outline-dark')
                     .attr('onclick', `removeAdditionalItem(${index})`)
                     .attr('title', 'Eliminar item adicional')
                     .html('<i class="fas fa-times"></i>');
            
            // Recalcular totales
            calculateTotal();
            
            console.log(`Item adicional ${index} restaurado`);
        }
    };

    // Validación del formulario
    $('#pdf-edit-form').on('submit', function(e) {
        const total = parseFloat($('#total-input').val()) || 0;
        const subtotal = parseFloat($('#subtotal-input').val()) || 0;
        const providerName = $('input[name="provider_name"]').val().trim();
        
        console.log('Form submission attempt:', {
            total: total,
            subtotal: subtotal,
            providerName: providerName
        });
        
        if (!providerName) {
            e.preventDefault();
            alert('El nombre del proveedor es obligatorio.');
            return false;
        }
        
        // Mostrar indicador de carga
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        console.log('Form being submitted successfully');
    });
});
</script>
@stop

@section('css')
<style>
    .form-control-sm.border-0:focus {
        box-shadow: none;
        border: 1px solid #007bff !important;
    }
    
    .card {
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .btn-lg {
        padding: 10px 20px;
        font-size: 16px;
    }
    
    input[type="number"] {
        -moz-appearance: textfield;
    }
    
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    /* Estilos para botones de eliminar items */
    .btn-outline-dark.btn-sm {
        padding: 2px 6px;
        font-size: 12px;
        border-radius: 3px;
        background-color: transparent;
        border: 1px solid #000;
        color: #000;
    }
    
    .btn-outline-dark.btn-sm:hover {
        background-color: #000;
        border-color: #000;
        color: #fff;
    }
    
    .btn-outline-secondary.btn-sm {
        padding: 2px 6px;
        font-size: 12px;
        border-radius: 3px;
        background-color: transparent;
        border: 1px solid #666;
        color: #666;
    }
    
    .btn-outline-secondary.btn-sm:hover {
        background-color: #666;
        border-color: #666;
        color: #fff;
    }
    
    .btn-outline-dark.btn-sm i,
    .btn-outline-secondary.btn-sm i {
        font-size: 10px;
    }
    
    /* Estilos para items eliminados */
    .deleted-item,
    .deleted-additional-item {
        transition: all 0.3s ease;
    }
    
    .deleted-item td,
    .deleted-additional-item td {
        color: #721c24;
    }
    
    .deleted-item textarea,
    .deleted-item input,
    .deleted-additional-item textarea,
    .deleted-additional-item input {
        background-color: #f5c6cb !important;
        pointer-events: none;
    }
    
    /* Ocultar filas eliminadas */
    tr.hidden-item {
        display: none;
    }
    
    /* Estilos para modal personalizado */
    .custom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }
    
    .custom-modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .custom-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
    }
    
    .custom-modal-content {
        position: relative;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        transform: scale(0.9);
        transition: transform 0.3s ease;
    }
    
    .custom-modal.show .custom-modal-content {
        transform: scale(1);
    }
    
    .custom-modal-header {
        padding: 20px 20px 10px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .custom-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    
    .custom-modal-close {
        background: none;
        border: none;
        font-size: 18px;
        color: #666;
        cursor: pointer;
        padding: 5px;
        border-radius: 3px;
        transition: all 0.2s ease;
    }
    
    .custom-modal-close:hover {
        background-color: #f8f9fa;
        color: #333;
    }
    
    .custom-modal-body {
        padding: 20px;
        text-align: center;
    }
    
    .confirmation-icon {
        margin-bottom: 15px;
    }
    
    .confirmation-icon i {
        font-size: 48px;
        color: #007bff;
    }
    
    .confirmation-message {
        font-size: 16px;
        margin-bottom: 20px;
        color: #333;
        line-height: 1.5;
    }
    
    .order-details {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        text-align: left;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .detail-row:last-child {
        margin-bottom: 0;
    }
    
    .detail-label {
        font-weight: 500;
        color: #666;
    }
    
    .detail-value {
        font-weight: 600;
        color: #333;
    }
    
    .confirmation-note {
        background-color: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 6px;
        padding: 12px;
        display: flex;
        align-items: flex-start;
        text-align: left;
        font-size: 14px;
        color: #0066cc;
    }
    
    .confirmation-note i {
        margin-right: 8px;
        margin-top: 2px;
        flex-shrink: 0;
    }
    
    .custom-modal-footer {
        padding: 10px 20px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .custom-btn {
        padding: 10px 20px;
        border: 1px solid;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .custom-btn-secondary {
        background-color: #fff;
        border-color: #6c757d;
        color: #6c757d;
    }
    
    .custom-btn-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }
    
    .custom-btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }
    
    .custom-btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    
    /* Animación de entrada */
    @keyframes modalFadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .custom-modal.show {
        animation: modalFadeIn 0.3s ease;
    }
</style>
@stop
