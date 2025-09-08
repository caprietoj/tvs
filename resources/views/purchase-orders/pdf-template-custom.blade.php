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
                <td class="value">Colegio Victoria Calle 215 No. 50-60</td>
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
            
            // CORRECCIÓN CRÍTICA: Usar ÚNICAMENTE los datos filtrados de customData
            $itemsToShow = [];
            
            // DEBUG: Verificar estado de customData
            Log::info('🔍 VISTA PDF: Analizando customData completo', [
                'order_id' => $order->id,
                'has_customData' => isset($customData),
                'is_array' => isset($customData) && is_array($customData),
                'has_items' => isset($customData['items']),
                'items_empty' => isset($customData['items']) ? empty($customData['items']) : 'no_items_key',
                'items_count' => isset($customData['items']) && is_array($customData['items']) ? count($customData['items']) : 'not_array',
                'customData_keys' => isset($customData) && is_array($customData) ? array_keys($customData) : 'not_available',
                'has_quotationItemSelections' => isset($quotationItemSelections)
            ]);
            
            // NUEVA LÓGICA: Priorizar quotationItemSelections para órdenes mixtas
            if (isset($quotationItemSelections) && $quotationItemSelections->count() > 0) {
                Log::info('🎯 VISTA PDF: Usando quotationItemSelections (orden mixta)', [
                    'order_id' => $order->id,
                    'selections_count' => $quotationItemSelections->count()
                ]);
                
                // Para órdenes mixtas, usar las selecciones pasadas desde el servicio
                foreach ($quotationItemSelections as $selection) {
                    $unitPrice = 0;
                    
                    // Intentar obtener el precio de diferentes fuentes
                    if (isset($selection->unit_price)) {
                        $unitPrice = $selection->unit_price;
                    } elseif ($selection->quotation && isset($selection->quotation->original_item_prices[$selection->item_index])) {
                        $unitPrice = $selection->quotation->original_item_prices[$selection->item_index];
                    } elseif ($selection->quotation && isset($selection->quotation->item_prices[$selection->item_index])) {
                        $unitPrice = $selection->quotation->item_prices[$selection->item_index];
                    }
                    
                    $itemsToShow[] = [
                        'description' => $selection->item_description ?? 'N/A',
                        'quantity' => $selection->quantity ?? 1,
                        'unit_price' => $unitPrice,
                        'total' => isset($selection->total_price) ? $selection->total_price : (($selection->quantity ?? 1) * $unitPrice),
                        'unit' => $selection->unit ?? 'Unidad',
                        'observations' => $selection->observations ?? ''
                    ];
                }
                
                Log::info('🎯 VISTA PDF: Items de quotationItemSelections procesados', [
                    'order_id' => $order->id,
                    'items_count' => count($itemsToShow)
                ]);
                
            // SEGUNDA PRIORIDAD: Si hay customData con items válidos
            } elseif (isset($customData) && is_array($customData)) {
                Log::info('🎯 VISTA PDF: Usando customData (fallback)', [
                    'order_id' => $order->id,
                    'has_items' => isset($customData['items']),
                    'items_count' => isset($customData['items']) && is_array($customData['items']) ? count($customData['items']) : 0
                ]);
                
                // Usar items de customData (pueden estar vacíos si se filtraron todos)
                if (isset($customData['items']) && is_array($customData['items'])) {
                    $itemsToShow = $customData['items'];
                    Log::info('🎯 VISTA PDF: Items regulares de customData', [
                        'count' => count($itemsToShow),
                        'order_id' => $order->id
                    ]);
                } else {
                    $itemsToShow = [];
                    Log::warning('⚠️ VISTA PDF: customData sin items válidos', [
                        'order_id' => $order->id
                    ]);
                }
                
                // Agregar items adicionales SOLO si están en customData y son válidos
                if (isset($customData['additional_items']) && is_array($customData['additional_items'])) {
                    Log::info('🎯 VISTA PDF: Procesando additional_items', [
                        'additional_count' => count($customData['additional_items']),
                        'order_id' => $order->id
                    ]);
                    
                    foreach ($customData['additional_items'] as $additionalItem) {
                        // Aplicar el MISMO filtro que en el controlador
                        $hasDescription = !empty($additionalItem['description']) && trim($additionalItem['description']) !== '';
                        $hasQuantity = isset($additionalItem['quantity']) && floatval($additionalItem['quantity']) > 0;
                        $hasPrice = isset($additionalItem['unit_price']) && floatval($additionalItem['unit_price']) > 0;
                        
                        Log::info('🔍 VISTA PDF: Evaluando additional_item', [
                            'description' => $additionalItem['description'] ?? 'missing',
                            'hasDescription' => $hasDescription,
                            'hasQuantity' => $hasQuantity,
                            'hasPrice' => $hasPrice,
                            'will_include' => $hasDescription && ($hasQuantity || $hasPrice)
                        ]);
                        
                        if ($hasDescription && ($hasQuantity || $hasPrice)) {
                            $quantity = floatval($additionalItem['quantity'] ?? 0);
                            $unitPrice = floatval($additionalItem['unit_price'] ?? 0);
                            $total = floatval($additionalItem['total'] ?? ($quantity * $unitPrice));
                            
                            $itemsToShow[] = [
                                'description' => $additionalItem['description'],
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'total' => $total,
                                'unit' => $additionalItem['unit'] ?? 'Unidad',
                                'observations' => $additionalItem['observations'] ?? '',
                                'tax_rate' => $additionalItem['tax_rate'] ?? 0
                            ];
                            
                            Log::info('✅ VISTA PDF: Additional_item agregado', [
                                'description' => $additionalItem['description'],
                                'total' => $total
                            ]);
                        }
                    }
                }
                
                Log::info('🎯 VISTA PDF: Items finales a mostrar', [
                    'total_items' => count($itemsToShow),
                    'regular_items' => isset($customData['items']) && is_array($customData['items']) ? count($customData['items']) : 0,
                    'additional_items' => isset($customData['additional_items']) ? count($customData['additional_items']) : 0,
                    'order_id' => $order->id
                ]);
                
            } else {
                // FALLBACK: Solo si NO hay customData ni quotationItemSelections
                Log::warning('⚠️ VISTA PDF: Fallback - sin customData ni quotationItemSelections', [
                    'order_id' => $order->id
                ]);
                $itemsToShow = [];
            }
            
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
            
            // Para solicitudes de servicios en fallback (solo cuando no hay customData)
            if (empty($itemsToShow) && $order->purchaseRequest && $order->purchaseRequest->type === 'services' && $order->purchaseRequest->service_items) {
                $serviceItems = is_string($order->purchaseRequest->service_items) ? 
                               json_decode($order->purchaseRequest->service_items, true) : 
                               $order->purchaseRequest->service_items;
                
                if (is_array($serviceItems)) {
                    $serviceBudget = 0;
                    if ($order->purchaseRequest->selectedQuotation) {
                        $serviceBudget = floatval($order->purchaseRequest->selectedQuotation->subtotal ?? 
                                                $order->purchaseRequest->selectedQuotation->total_amount ?? 0);
                    } else {
                        $serviceBudget = floatval($order->subtotal ?? $order->total_amount ?? 0);
                        if (!$serviceBudget && $order->total_amount && $order->iva_amount) {
                            $serviceBudget = floatval($order->total_amount) - floatval($order->iva_amount);
                        }
                    }
                    
                    $itemCount = count($serviceItems);
                    $pricePerItem = $itemCount > 0 ? $serviceBudget / $itemCount : $serviceBudget;
                    
                    foreach ($serviceItems as $index => $serviceItem) {
                        $quantity = intval($serviceItem['quantity'] ?? 1);
                        $totalPerItem = $pricePerItem;
                        $unitPrice = $quantity > 0 ? $totalPerItem / $quantity : $totalPerItem;
                        
                        $itemsToShow[] = [
                            'description' => $serviceItem['description'] ?? 'Servicio ' . ($index + 1),
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'total' => $totalPerItem,
                            'unit' => 'Servicio',
                            'observations' => ''
                        ];
                    }
                }
            }
            
            // VERIFICAR si hay impuestos individuales en los items que se van a mostrar
            if (!empty($itemsToShow)) {
                foreach ($itemsToShow as $item) {
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
            
            // Verificar si hay impuestos individuales en customData
            if (!$hasIndividualTaxes && isset($customData) && is_array($customData)) {
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
            
            // CORRECCIÓN: Usar PRIORITARIAMENTE los cálculos guardados en customData
            $calculatedSubtotal = 0;
            $calculatedIva = 0;
            $calculatedIndividualTaxes = 0;
            
            // Detectar el tipo de IVA desde la cotización seleccionada
            $ivaType = null;
            $ivaLabel = 'IVA';
            if ($order->purchaseRequest && $order->purchaseRequest->selectedQuotation) {
                $selectedQuotation = $order->purchaseRequest->selectedQuotation;
                // Prioridad 1: Usar flags habilitados
                if ($selectedQuotation->iva_19_enabled && $selectedQuotation->iva_19_amount > 0) {
                    $ivaType = '19%';
                    $ivaLabel = 'IVA (19%)';
                } elseif ($selectedQuotation->iva_5_enabled && $selectedQuotation->iva_5_amount > 0) {
                    $ivaType = '5%';
                    $ivaLabel = 'IVA (5%)';
                } else {
                    // Prioridad 2: Detectar tipo basado en montos y porcentajes
                    if ($selectedQuotation->iva_19_amount > 0) {
                        // Calcular si corresponde al 19%
                        $total = floatval($selectedQuotation->total_amount);
                        $ivaAmount = floatval($selectedQuotation->iva_19_amount);
                        if ($total > 0 && $ivaAmount > 0) {
                            $subtotalCalculated = $total - $ivaAmount;
                            $percentage = round(($ivaAmount / $subtotalCalculated) * 100);
                            if ($percentage == 19) {
                                $ivaType = '19%';
                                $ivaLabel = 'IVA (19%)';
                            } elseif ($percentage == 5) {
                                $ivaType = '5%';
                                $ivaLabel = 'IVA (5%)';
                            } else {
                                $ivaType = $percentage . '%';
                                $ivaLabel = 'IVA (' . $percentage . '%)';
                            }
                        }
                    } elseif ($selectedQuotation->iva_5_amount > 0) {
                        $ivaType = '5%';
                        $ivaLabel = 'IVA (5%)';
                    }
                }
            }
            
            // Si no se puede determinar desde la cotización, usar customData
            if (!$ivaType && isset($customData['iva_rate'])) {
                $rate = str_replace('%', '', $customData['iva_rate']);
                if (is_numeric($rate)) {
                    $ivaType = $rate . '%';
                    $ivaLabel = 'IVA (' . $rate . '%)';
                }
            }
            
            // Debug temporal - agregar logs para diagnóstico
            Log::info('🔍 DEBUG PDF - Detección de IVA', [
                'order_id' => $order->id,
                'ivaType' => $ivaType,
                'ivaLabel' => $ivaLabel,
                'has_selected_quotation' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation,
                'quotation_id' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation ? $order->purchaseRequest->selectedQuotation->id : null,
                'iva_19_enabled' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation ? $order->purchaseRequest->selectedQuotation->iva_19_enabled : null,
                'iva_19_amount' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation ? $order->purchaseRequest->selectedQuotation->iva_19_amount : null,
            ]);
            
            // Si hay customData con cálculos ya realizados, usarlos DIRECTAMENTE
            if (isset($customData) && is_array($customData)) {
                // Usar subtotal de customData si está disponible y es coherente
                if (isset($customData['subtotal']) && is_numeric($customData['subtotal']) && $customData !== null) {
                    $calculatedSubtotal = floatval($customData['subtotal']);
                    Log::info('🎯 VISTA PDF: Usando subtotal de customData (coherente)', [
                        'subtotal' => $calculatedSubtotal,
                        'order_id' => $order->id
                    ]);
                } else {
                    // Si no hay subtotal en customData, obtenerlo de la cotización seleccionada
                    if ($order->purchaseRequest && $order->purchaseRequest->selectedQuotation) {
                        $selectedQuotation = $order->purchaseRequest->selectedQuotation;
                        if ($selectedQuotation->subtotal_amount > 0) {
                            $calculatedSubtotal = floatval($selectedQuotation->subtotal_amount);
                            Log::info('🎯 VISTA PDF: Usando subtotal de cotización seleccionada', [
                                'subtotal' => $calculatedSubtotal,
                                'order_id' => $order->id
                            ]);
                        } else {
                            // Fallback: calcular subtotal desde total - IVA
                            if ($selectedQuotation->total_amount > 0 && ($selectedQuotation->iva_19_amount > 0 || $selectedQuotation->iva_5_amount > 0)) {
                                $totalIva = floatval($selectedQuotation->iva_19_amount) + floatval($selectedQuotation->iva_5_amount);
                                $calculatedSubtotal = floatval($selectedQuotation->total_amount) - $totalIva;
                                Log::info('🎯 VISTA PDF: Calculando subtotal desde total-IVA', [
                                    'subtotal' => $calculatedSubtotal,
                                    'total' => $selectedQuotation->total_amount,
                                    'iva_total' => $totalIva,
                                    'order_id' => $order->id
                                ]);
                            }
                        }
                    }
                }
                
                // Usar IVA de customData si está disponible y es coherente
                if (isset($customData['iva_amount']) && is_numeric($customData['iva_amount'])) {
                    $customIva = floatval($customData['iva_amount']);
                    $customSubtotal = floatval($customData['subtotal'] ?? 0);
                    $customTotal = floatval($customData['total'] ?? 0);
                    
                    // Validar coherencia: subtotal + iva debería ser aproximadamente igual al total
                    $expectedTotal = $customSubtotal + $customIva;
                    $totalDifference = abs($customTotal - $expectedTotal);
                    
                    Log::info('🔍 DEBUG PDF - Validando coherencia de customData', [
                        'custom_iva' => $customIva,
                        'custom_subtotal' => $customSubtotal,
                        'custom_total' => $customTotal,
                        'expected_total' => $expectedTotal,
                        'total_difference' => $totalDifference,
                        'is_coherent' => $totalDifference <= 1 // Tolerancia de $1
                    ]);
                    
                    // Solo usar customData si es coherente
                    if ($totalDifference <= 1) {
                        $calculatedIva = $customIva;
                        Log::info('🎯 VISTA PDF: Usando IVA de customData (coherente)', [
                            'iva_amount' => $calculatedIva,
                            'order_id' => $order->id
                        ]);
                    } else {
                        Log::warning('⚠️ VISTA PDF: customData incoherente, usando cotización', [
                            'custom_total' => $customTotal,
                            'expected_total' => $expectedTotal,
                            'difference' => $totalDifference,
                            'order_id' => $order->id
                        ]);
                        // Forzar usar datos de cotización
                        $customData = null;
                    }
                } else {
                    // Si no hay IVA en customData, obtenerlo de la cotización seleccionada
                    if ($order->purchaseRequest && $order->purchaseRequest->selectedQuotation) {
                        $selectedQuotation = $order->purchaseRequest->selectedQuotation;
                        Log::info('🔍 DEBUG PDF - Buscando IVA en cotización', [
                            'quotation_id' => $selectedQuotation->id,
                            'iva_19_enabled' => $selectedQuotation->iva_19_enabled,
                            'iva_19_amount' => $selectedQuotation->iva_19_amount,
                            'iva_5_enabled' => $selectedQuotation->iva_5_enabled,
                            'iva_5_amount' => $selectedQuotation->iva_5_amount,
                        ]);
                        
                        if ($selectedQuotation->iva_19_enabled && $selectedQuotation->iva_19_amount > 0) {
                            $calculatedIva = floatval($selectedQuotation->iva_19_amount);
                        } elseif ($selectedQuotation->iva_5_enabled && $selectedQuotation->iva_5_amount > 0) {
                            $calculatedIva = floatval($selectedQuotation->iva_5_amount);
                        } elseif ($selectedQuotation->iva_19_amount > 0) {
                            // Aunque no esté habilitado, si hay monto, usarlo
                            $calculatedIva = floatval($selectedQuotation->iva_19_amount);
                        } elseif ($selectedQuotation->iva_5_amount > 0) {
                            $calculatedIva = floatval($selectedQuotation->iva_5_amount);
                        }
                        
                        Log::info('🎯 VISTA PDF: IVA calculado desde cotización', [
                            'iva_amount' => $calculatedIva,
                            'iva_19_enabled' => $selectedQuotation->iva_19_enabled,
                            'iva_5_enabled' => $selectedQuotation->iva_5_enabled,
                            'order_id' => $order->id
                        ]);
                    }
                }
                
                // Usar impuestos individuales de customData si están disponibles
                if (isset($customData['individual_taxes_total']) && is_numeric($customData['individual_taxes_total'])) {
                    $calculatedIndividualTaxes = floatval($customData['individual_taxes_total']);
                    Log::info('🎯 VISTA PDF: Usando impuestos individuales de customData', [
                        'individual_taxes' => $calculatedIndividualTaxes,
                        'order_id' => $order->id
                    ]);
                }
            }
            
            // FALLBACK: Solo si NO hay customData, calcular dinámicamente
            if ($calculatedSubtotal <= 0 && !empty($itemsToShow)) {
                Log::warning('⚠️ VISTA PDF: Calculando subtotal dinámicamente como fallback', [
                    'order_id' => $order->id
                ]);
                
                foreach ($itemsToShow as $item) {
                    $itemTotal = floatval($item['total'] ?? (($item['quantity'] ?? 1) * floatval($item['unit_price'] ?? 0)));
                    $calculatedSubtotal += $itemTotal;
                    
                    // Calcular impuestos individuales por item solo si no hay customData
                    if ($calculatedIndividualTaxes <= 0) {
                        $itemTaxRate = floatval($item['tax_rate'] ?? 0);
                        if ($itemTaxRate > 0 && $itemTaxRate != 19) {
                            $itemTaxAmount = ($itemTotal * $itemTaxRate) / 100;
                            $calculatedIndividualTaxes += $itemTaxAmount;
                        }
                    }
                }
                
                // Calcular IVA solo si no hay customData
                if ($calculatedIva <= 0 && $calculatedSubtotal > 0) {
                    // Verificar si hay tasa de IVA en customData
                    if (isset($customData['iva_rate']) && is_numeric(str_replace('%', '', $customData['iva_rate']))) {
                        $ivaRate = floatval(str_replace('%', '', $customData['iva_rate']));
                        $calculatedIva = round($calculatedSubtotal * ($ivaRate / 100));
                    } else {
                        $calculatedIva = 0; // Por defecto sin IVA si no está especificado
                    }
                }
            }
            
            $calculatedTotal = round($calculatedSubtotal + $calculatedIva + $calculatedIndividualTaxes);
            
            Log::info('🎯 VISTA PDF: Totales finales calculados', [
                'order_id' => $order->id,
                'subtotal' => $calculatedSubtotal,
                'iva' => $calculatedIva,
                'individual_taxes' => $calculatedIndividualTaxes,
                'total' => $calculatedTotal,
                'source' => isset($customData['subtotal']) ? 'customData' : 'dynamic'
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

            @if(!empty($itemsToShow))
                @foreach($itemsToShow as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item['description'] ?? $item['item_description'] ?? 'N/A' }}</td>
                    <td class="center">{{ $item['quantity'] ?? $item['cantidad'] ?? 1 }}</td>
                    <td class="right">${{ number_format(round(floatval($item['unit_price'] ?? $item['precio_unitario'] ?? $item['unit_price_display'] ?? 0)), 0, ',', '.') }}</td>
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
                    <td class="right">${{ number_format(round(floatval($item['total'] ?? $item['total_price'] ?? (($item['quantity'] ?? 1) * floatval($item['unit_price'] ?? 0)))), 0, ',', '.') }}</td>
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
                <td class="value bold right">${{ number_format($calculatedSubtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">FECHA:</td>
                <td class="value">{{ isset($approvalDate) ? \Carbon\Carbon::parse($approvalDate)->format('d/m/Y') : $order->created_at->format('d/m/Y') }}</td>
                <td class="label bold">{{ $ivaLabel }}</td>
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