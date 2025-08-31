<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 15px;
            background-color: white;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 12px;
        }
        
        .header-title {
            background-color: #cccccc;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
            padding: 6px;
        }
        
        .label {
            background-color: #e6e6e6;
            font-weight: bold;
            width: 120px;
            padding: 4px;
        }
        
        .value {
            padding: 4px;
        }
        
        .center {
            text-align: center;
        }
        
        .right {
            text-align: right;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .items-header {
            background-color: #cccccc;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            padding: 4px;
        }
        
        .footer-box {
            border: 2px solid #000;
            padding: 12px;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        .signature-section {
            margin-top: 20px;
            font-size: 12px;
        }
        
        .edit-notice {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(isset($showEditedNotice) && $showEditedNotice)
        <div class="edit-notice">
            <strong>⚠️ DOCUMENTO EDITADO</strong><br>
            Este PDF ha sido personalizado por un administrador el {{ isset($editedAt) ? $editedAt->format('d/m/Y H:i') : \Carbon\Carbon::now()->format('d/m/Y H:i') }}
            @if(isset($editedBy))
                por {{ $editedBy }}
            @endif
        </div>
        @endif

        <!-- Nota sobre envío de facturas -->
        <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 8px; margin-bottom: 10px; font-size: 11px; text-align: justify;">
            <strong>IMPORTANTE:</strong> El envío de las facturas se debe realizar al correo <strong>830097105@recepciondefacturas.co</strong> para poder realizar las respectivas aceptaciones y acuse de facturas ante la DIAN
        </div>

        <!-- Título principal -->
        <table>
            <tr>
                <td class="header-title">FORMATO DE ORDEN DE COMPRA Y/O SERVICIO COLEGIO VICTORIA S.A.S</td>
            </tr>
        </table>

        <!-- Información básica -->
        <table>
            <tr>
                <td class="label">ORDEN DE COMPRA/SERVICIO</td>
                <td class="value" style="width: 200px;">{{ $order->order_number }}</td>
                <td class="label">FECHA</td>
                <td class="value">{{ isset($orderDate) ? \Carbon\Carbon::parse($orderDate)->format('d/m/Y') : $order->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Consecutivo COM</td>
                <td class="value">{{ $order->order_number }}</td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
        </table>

        <!-- Información del proveedor -->
        <table>
            <tr>
                <td class="label">PROVEEDOR:</td>
                <td class="value" colspan="3">{{ isset($customProvider) ? $customProvider->nombre : ($order->provider->nombre ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td class="label">NIT/CC:</td>
                <td class="value">
                    @if(isset($customProvider) && $customProvider->nit)
                        {{ $customProvider->nit }}
                    @elseif($order->provider && $order->provider->nit)
                        {{ $order->provider->nit }}
                    @endif
                </td>
                <td class="label">DIRECCIÓN:</td>
                <td class="value">
                    @if(isset($customProvider) && $customProvider->direccion)
                        {{ $customProvider->direccion }}
                    @elseif($order->provider && $order->provider->direccion)
                        {{ $order->provider->direccion }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">TELÉFONO:</td>
                <td class="value">
                    @if(isset($customProvider) && $customProvider->telefono)
                        {{ $customProvider->telefono }}
                    @elseif($order->provider && $order->provider->telefono)
                        {{ $order->provider->telefono }}
                    @endif
                </td>
                <td class="label">CIUDAD:</td>
                <td class="value">Bogotá, D.C.</td>
            </tr>
            <tr>
                <td class="label">E-MAIL:</td>
                <td class="value">
                    @if(isset($customProvider) && $customProvider->email)
                        {{ $customProvider->email }}
                    @elseif($order->provider && $order->provider->email)
                        {{ $order->provider->email }}
                    @endif
                </td>
                <td class="label">ENTREGAR EN:</td>
                <td class="value">COLEGIO VICTORIA CALLE 32 F SUR 17G 26</td>
            </tr>
        </table>

        <!-- Información de entrega y responsable -->
        <table>
            <tr>
                <td class="label">FORMA DE PAGO:</td>
                <td class="value">{{ $paymentTerms ?? $order->payment_terms ?? 'Contado' }}</td>
                <td class="label">RESPONSABLE DE LA COMPRA:</td>
                <td class="value">{{ $order->purchaseRequest->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">FECHA ENTREGA:</td>
                <td class="value">{{ isset($deliveryDate) ? \Carbon\Carbon::parse($deliveryDate)->format('d/m/Y') : ($order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') : \Carbon\Carbon::now()->format('d/m/Y')) }}</td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
        </table>

        <!-- Información específica para órdenes mixtas -->
        @if(isset($isMixedOrder) && $isMixedOrder && isset($providerSpecificInfo) && $providerSpecificInfo)
        <table>
            <tr>
                <td class="label">TIPO DE ORDEN:</td>
                <td class="value" colspan="3">
                    <strong>ORDEN MIXTA</strong> - Selección de múltiples proveedores
                </td>
            </tr>
            <tr>
                <td class="label">DETALLE SELECCIÓN:</td>
                <td class="value" colspan="3">
                    {{ $providerSpecificInfo['this_provider_items'] }} de {{ $providerSpecificInfo['total_items'] }} items totales | 
                    Proveedores involucrados: {{ $providerSpecificInfo['total_providers'] }}
                </td>
            </tr>
        </table>
        @endif

        <!-- Información específica para órdenes compartidas -->
        @if(isset($isSharedPurchase) && $isSharedPurchase)
        <table>
            <tr>
                <td class="label">COMPRA COMPARTIDA:</td>
                <td class="value" colspan="3">
                    <strong>SÍ</strong> - Presupuesto distribuido entre secciones
                </td>
            </tr>
            @if(isset($sharedSections) && !empty($sharedSections))
            <tr>
                <td class="label">SECCIONES:</td>
                <td class="value" colspan="3">
                    {{ is_array($sharedSections) ? implode(' • ', $sharedSections) : $sharedSections }}
                </td>
            </tr>
            @endif
        </table>
        @endif

        @php
            // Determinar si se debe mostrar la columna de impuestos
            $showTaxColumn = false;
            $hasIndividualTaxes = false;
            
            // Verificar si hay impuestos individuales en customData
            if (isset($customData) && is_array($customData)) {
                // Verificar items regulares
                if (isset($customData['items']) && is_array($customData['items'])) {
                    foreach ($customData['items'] as $item) {
                        if (isset($item['tax_rate']) && is_numeric($item['tax_rate'])) {
                            $taxRate = floatval($item['tax_rate']);
                            // Mostrar columna si hay cualquier impuesto diferente de 19% (incluyendo 0%)
                            if ($taxRate != 19) {
                                $hasIndividualTaxes = true;
                                break;
                            }
                        }
                    }
                }
                
                // Verificar items adicionales si no se encontraron en regulares
                if (!$hasIndividualTaxes && isset($customData['additional_items']) && is_array($customData['additional_items'])) {
                    foreach ($customData['additional_items'] as $item) {
                        if (isset($item['tax_rate']) && is_numeric($item['tax_rate'])) {
                            $taxRate = floatval($item['tax_rate']);
                            // Mostrar columna si hay cualquier impuesto diferente de 19% (incluyendo 0%)
                            if ($taxRate != 19) {
                                $hasIndividualTaxes = true;
                                break;
                            }
                        }
                    }
                }
                
                // También verificar si hay total de impuestos individuales
                if (!$hasIndividualTaxes && isset($customData['individual_taxes_total']) && floatval($customData['individual_taxes_total']) > 0) {
                    $hasIndividualTaxes = true;
                }
                
                // También mostrar si hay breakdown con valores
                if (!$hasIndividualTaxes && isset($customData['individual_taxes_breakdown']) && is_array($customData['individual_taxes_breakdown'])) {
                    foreach ($customData['individual_taxes_breakdown'] as $rate => $amount) {
                        if (floatval($amount) > 0) {
                            $hasIndividualTaxes = true;
                            break;
                        }
                    }
                }
            }
            
            $showTaxColumn = $hasIndividualTaxes;
            
            // Calcular el total correcto incluyendo impuestos individuales
            $calculatedSubtotal = $subtotal ?? $order->subtotal ?? 0;
            $calculatedIva = $ivaAmount ?? $order->iva_amount ?? 0;
            $calculatedIndividualTaxes = 0;
            
            if (isset($customData['individual_taxes_total'])) {
                $calculatedIndividualTaxes = floatval($customData['individual_taxes_total']);
            }
            
            $calculatedTotal = $calculatedSubtotal + $calculatedIva + $calculatedIndividualTaxes;
            
            // Debug: log de la decisión
            \Log::info('Cálculo de totales en PDF:', [
                'subtotal' => $calculatedSubtotal,
                'iva' => $calculatedIva, 
                'individual_taxes' => $calculatedIndividualTaxes,
                'total_calculado' => $calculatedTotal,
                'total_original' => $totalAmount ?? $order->total_amount ?? 0,
                'showTaxColumn' => $showTaxColumn
            ]);
        @endphp

        <!-- Items -->
        <table>
            <tr>
                <td class="items-header" style="width: 60px;">ITEM</td>
                <td class="items-header">DESCRIPCIÓN</td>
                <td class="items-header" style="width: 60px;">CANT</td>
                <td class="items-header" style="width: 100px;">VALOR UNIT</td>
                @if($showTaxColumn)
                <td class="items-header" style="width: 80px;">IMPUESTO</td>
                @endif
                <td class="items-header" style="width: 100px;">VALOR TOTAL</td>
            </tr>
            
            @php
                $itemsToShow = [];
                
                // Extraer items de customData si están disponibles
                $customItems = [];
                if (isset($customData) && is_array($customData) && isset($customData['items'])) {
                    $customItems = $customData['items'];
                }
                
                $useCustomData = isset($customItems) && !empty($customItems);
                
                // Detectar si es orden mixta
                $isMixedOrder = false;
                $isSharedPurchase = false;
                $sharedSections = [];
                $providerSpecificInfo = null;
                
                // Obtener el presupuesto correcto
                $budget = null;
                if (isset($customData) && is_array($customData) && isset($customData['budget'])) {
                    $budget = $customData['budget'];
                } elseif ($order->purchaseRequest && $order->purchaseRequest->budget) {
                    $budget = $order->purchaseRequest->budget;
                }
                
                // Verificar datos personalizados para información adicional
                if (isset($customData) && is_array($customData)) {
                    $isSharedPurchase = $customData['is_shared_purchase'] ?? false;
                    $sharedSections = $customData['shared_sections'] ?? [];
                    if (is_string($sharedSections)) {
                        $sharedSections = explode(' • ', $sharedSections);
                    }
                }
                
                // Detectar orden mixta por selecciones de cotización
                if ($order->purchaseRequest && $order->purchaseRequest->quotationItemSelections()->exists()) {
                    $isMixedOrder = true;
                    $allSelections = $order->purchaseRequest->quotationItemSelections;
                    $providerSelections = $allSelections->filter(function($sel) use ($order) {
                        return $sel->quotation && $sel->quotation->provider_name === $order->provider->nombre;
                    });
                    
                    if ($providerSelections->count() > 0) {
                        $providerSpecificInfo = [
                            'total_providers' => $allSelections->pluck('quotation.provider_name')->unique()->count(),
                            'this_provider_items' => $providerSelections->count(),
                            'total_items' => $allSelections->count()
                        ];
                    }
                }
                
                if ($useCustomData) {
                    // Usar datos personalizados
                    $itemsToShow = $customItems;
                } elseif (isset($quotationItemSelections) && $quotationItemSelections->count() > 0) {
                    // Para órdenes mixtas, usar solo las selecciones del proveedor específico
                    $itemsToShow = [];
                    foreach ($quotationItemSelections as $selection) {
                        $unitPrice = 0;
                        if ($selection->quotation && isset($selection->quotation->original_item_prices[$selection->item_index])) {
                            $unitPrice = $selection->quotation->original_item_prices[$selection->item_index];
                        } elseif ($selection->quotation && isset($selection->quotation->item_prices[$selection->item_index])) {
                            $unitPrice = $selection->quotation->item_prices[$selection->item_index];
                        }
                        
                        $itemsToShow[] = [
                            'description' => $selection->item_description ?? 'N/A',
                            'quantity' => $selection->quantity ?? 1,
                            'unit_price' => $unitPrice,
                            'total' => ($selection->quantity ?? 1) * $unitPrice,
                            'unit' => $selection->unit ?? 'Unidad',
                            'observations' => $selection->observations ?? ''
                        ];
                    }
                } elseif (isset($items) && !empty($items)) {
                    // Usar items regulares
                    $itemsToShow = $items;
                } elseif ($order->purchaseRequest && $order->purchaseRequest->purchase_items) {
                    // Combinar items de la solicitud con precios de la cotización
                    $purchaseItems = $order->purchaseRequest->purchase_items;
                    $prices = [];
                    
                    if ($order->purchaseRequest->selectedQuotation) {
                        $prices = $order->purchaseRequest->selectedQuotation->original_item_prices ?? 
                                 $order->purchaseRequest->selectedQuotation->item_prices ?? [];
                    }
                    
                    $itemsToShow = [];
                    $totalItemCount = count($purchaseItems);
                    $totalPriceCount = count($prices);
                    
                    foreach ($purchaseItems as $index => $item) {
                        $unitPrice = 0;
                        
                        // Si hay exactamente la misma cantidad de precios que items
                        if ($totalPriceCount == $totalItemCount && isset($prices[$index])) {
                            $unitPrice = $prices[$index];
                        }
                        // Si hay precios disponibles, usarlos hasta que se agoten
                        elseif ($totalPriceCount > 0 && isset($prices[$index])) {
                            $unitPrice = $prices[$index];
                        }
                        
                        $quantity = $item['quantity'] ?? 1;
                        
                        $itemsToShow[] = [
                            'description' => $item['description'] ?? 'N/A',
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'total' => $quantity * $unitPrice,
                            'unit' => $item['unit'] ?? 'Unidad',
                            'observations' => $item['observations'] ?? ''
                        ];
                    }
                }
            @endphp

            @if(!empty($itemsToShow))
                @foreach($itemsToShow as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item['description'] ?? $item['item_description'] ?? 'N/A' }}</td>
                    <td class="center">{{ $item['quantity'] ?? $item['cantidad'] ?? 1 }}</td>
                    <td class="right">${{ number_format(intval($item['unit_price'] ?? $item['precio_unitario'] ?? $item['unit_price_display'] ?? 0), 0, ',', '.') }}</td>
                    @if($showTaxColumn)
                    <td class="center">
                        @php
                            $itemTaxRate = $item['tax_rate'] ?? 0;
                        @endphp
                        @if($itemTaxRate > 0)
                            {{ $itemTaxRate }}%
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    <td class="right">${{ number_format(intval($item['total'] ?? $item['total_price'] ?? (($item['quantity'] ?? 1) * intval($item['unit_price'] ?? 0))), 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="center">1</td>
                    <td>No hay items disponibles</td>
                    <td class="center">0</td>
                    <td class="right">0</td>
                    @if($showTaxColumn)
                    <td class="center">-</td>
                    @endif
                    <td class="right">$0</td>
                </tr>
            @endif
        </table>

        <!-- Observaciones y presupuesto -->
        <table>
            <tr>
                <td class="label">Observaciones:</td>
                <td class="value" colspan="3">
                    @php
                        // Combinar observaciones de diferentes fuentes
                        $observationSources = [];
                        
                        // Observaciones del PurchaseOrder
                        if (isset($observations) && $observations && $observations !== '-') {
                            $observationSources[] = $observations;
                        }
                        
                        // Observaciones del customData
                        if (isset($customData['observations']) && $customData['observations']) {
                            $observationSources[] = $customData['observations'];
                        }
                        
                        // Observaciones del PurchaseRequest
                        if (isset($purchaseRequestObservations) && $purchaseRequestObservations) {
                            $observationSources[] = $purchaseRequestObservations;
                        }
                        
                        // Combinar todas las observaciones
                        $displayObservations = !empty($observationSources) ? implode(' | ', $observationSources) : '-';
                        

                        
                        // Agregar información adicional para órdenes compartidas
                        if (isset($isSharedPurchase) && $isSharedPurchase) {
                            $sharedInfo = "Los costos serán distribuidos proporcionalmente entre las secciones involucradas.";
                            $displayObservations = ($displayObservations === '-' ? '' : $displayObservations . ' | ') . $sharedInfo;
                        }
                        
                        echo $displayObservations ?: '-';
                    @endphp
                </td>
            </tr>
            <tr>
                <td class="label">PRESUPUESTO COMPARTIDO:</td>
                <td class="value" colspan="3">
                    @if(isset($isSharedPurchase) && $isSharedPurchase && isset($sharedSections) && !empty($sharedSections))
                        @php
                            $sectionCount = is_array($sharedSections) ? count($sharedSections) : 1;
                            $percentage = $sectionCount > 0 ? round(100 / $sectionCount, 1) : 100;
                        @endphp
                        {{ is_array($sharedSections) ? implode(" ({$percentage}%) - ", $sharedSections) . " ({$percentage}%)" : $sharedSections }}
                    @else
                        {{ isset($sharedBudget) ? $sharedBudget : '' }}
                    @endif
                </td>
            </tr>
        </table>

        <!-- Aprobación y totales -->
        <table>
            <tr>
                <td class="label">APROBACIÓN</td>
                <td class="value">{{ $order->purchaseRequest->approver->name ?? 'Juliana Pérez López' }}</td>
                <td class="label bold">SUB TOTAL</td>
                <td class="value bold right">${{ number_format($subtotal ?? $order->subtotal ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">FECHA:</td>
                <td class="value">{{ isset($approvalDate) ? \Carbon\Carbon::parse($approvalDate)->format('d/m/Y') : $order->created_at->format('d/m/Y') }}</td>
                <td class="label bold">IVA (19%)</td>
                <td class="value bold right">${{ number_format($calculatedIva, 0, ',', '.') }}</td>
            </tr>
            @if($showTaxColumn && $calculatedIndividualTaxes > 0)
            <tr>
                <td class="label">PRESUPUESTO:</td>
                <td class="value">{{ $budget ?? 'N/A' }}</td>
                <td class="label bold">Imp. Individuales</td>
                <td class="value bold right">${{ number_format($calculatedIndividualTaxes, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">SOLICITUD Nº:</td>
                <td class="value">{{ $order->purchaseRequest->request_number ?? 'SC-0012' }}</td>
                <td class="label bold">Sin Imp. Consumo</td>
                <td class="value bold right">$0</td>
            </tr>
            @else
            <tr>
                <td class="label">PRESUPUESTO:</td>
                <td class="value">{{ $budget ?? 'N/A' }}</td>
                <td class="label bold">Sin Imp. Consumo</td>
                <td class="value bold right">$0</td>
            </tr>
            <tr>
                <td class="label">SOLICITUD Nº:</td>
                <td class="value">{{ $order->purchaseRequest->request_number ?? 'SC-0012' }}</td>
                <td class="label bold">TOTAL A PAGAR</td>
                <td class="value bold right">${{ number_format($calculatedTotal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($showTaxColumn && $calculatedIndividualTaxes > 0)
            <tr>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label bold">TOTAL A PAGAR</td>
                <td class="value bold right">${{ number_format($calculatedTotal, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>

        <!-- Información de facturación -->
        <div style="text-align: center; margin-top: 20px; padding: 15px; border: 2px solid #000; font-weight: bold; font-size: 12px;">
            FACTURA A FAVOR DE COLEGIO VICTORIA SAS NIT 830.097.105-2<br>
            Calle 215 No. 50-60 Tel (571) 6761503/6763435<br>
            Bogotá - Colombia<br>
            Departamento de Compras email: compras@tvs.edu.co
        </div>
    </div>
</body>
</html>