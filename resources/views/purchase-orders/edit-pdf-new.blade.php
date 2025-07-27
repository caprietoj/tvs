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
                                <input type="text" name="provider_name" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_name'] ?? $order->provider->name ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px; width: 15%;">NIT/CC:</td>
                            <td style="border: 1px solid #000; padding: 4px; width: 35%;">
                                <input type="text" name="provider_nit" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_nit'] ?? $order->provider->nit ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">DIRECCIÓN:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_address" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_address'] ?? $order->provider->address ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">TELÉFONO:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_phone" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_phone'] ?? $order->provider->phone ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">CIUDAD:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_city" class="form-control form-control-sm border-0" 
                                       value="{{ $customData['provider_city'] ?? $order->provider->city ?? '' }}" 
                                       style="width: 100%; border: none; background: transparent;">
                            </td>
                            <td style="font-weight: bold; border: 1px solid #000; padding: 4px;">E-MAIL:</td>
                            <td style="border: 1px solid #000; padding: 4px;">
                                <input type="text" name="provider_email" class="form-control form-control-sm border-0" 
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
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 8%;">ITEM</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 47%;">DESCRIPCIÓN</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 10%;">CANT</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 15%;">VALOR UNIT</td>
                            <td style="font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; width: 20%;">VALOR TOTAL</td>
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
                            <td style="border: 1px solid #000; padding: 4px;">{{ $order->purchaseRequest->budget ?? '' }}</td>
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
        
        // Sumar items regulares
        $('.item-total-input').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        
        // Sumar items adicionales
        $('.additional-total-input').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
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
</style>
@stop
