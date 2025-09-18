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
            $hasItemLevelTaxes = false; // 🔧 INICIALIZACIÓN CRÍTICA para evitar "Undefined variable"
            
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
                    } elseif (isset($selection->quotation) && $selection->quotation && isset($selection->quotation->original_item_prices[$selection->item_index])) {
                        $unitPrice = $selection->quotation->original_item_prices[$selection->item_index];
                    } elseif (isset($selection->quotation) && $selection->quotation && isset($selection->quotation->item_prices[$selection->item_index])) {
                        $unitPrice = $selection->quotation->item_prices[$selection->item_index];
                    }
                    
                    // 🔥 NUEVA LÓGICA: Obtener impuestos desde la cotización original
                    $taxRate = 0;
                    $taxType = 'Sin impuesto';
                    $itemUnit = 'und'; // Default unit
                    $displayQuantity = $selection->quantity ?? 1;
                    
                    // Extraer cantidad y unidad de la descripción si está presente
                    $description = $selection->item_description ?? '';
                    
                    // Regex más inteligente - solo buscar cantidades al inicio o después de palabras clave
                    // Evitar confundir especificaciones técnicas (5ml, 10cc) con cantidades
                    if (preg_match('/^(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes|caja|cajas|bolsa|bolsas|frasco|frascos)\b/i', $description, $matches)) {
                        // Cantidad al inicio: "5 paq de gasas"
                        $displayQuantity = intval($matches[1]);
                        $itemUnit = strtolower($matches[2]);
                        if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                            $itemUnit = 'paq';
                        } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                            $itemUnit = 'und';
                        }
                    } elseif (preg_match('/\bx\s*(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes|caja|cajas|bolsa|bolsas)?\b/i', $description, $matches)) {
                        // Cantidad con 'x': "gasas x 5", "jeringas x 10 und"
                        $displayQuantity = intval($matches[1]);
                        $itemUnit = isset($matches[2]) ? strtolower($matches[2]) : 'und';
                        if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                            $itemUnit = 'paq';
                        } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                            $itemUnit = 'und';
                        }
                    } elseif (preg_match('/\b(cantidad|cant|qty)[:=]?\s*(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes)?\b/i', $description, $matches)) {
                        // Cantidad explícita: "cantidad: 5", "cant 10 paq"
                        $displayQuantity = intval($matches[2]);
                        $itemUnit = isset($matches[3]) ? strtolower($matches[3]) : 'und';
                        if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                            $itemUnit = 'paq';
                        } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                            $itemUnit = 'und';
                        }
                    }
                    
                    if (isset($selection->quotation) && $selection->quotation) {
                        $quotation = $selection->quotation;
                        
                        // 🔥 INTENTAR EXTRAER DATOS REALES DE LA COTIZACIÓN ORIGINAL
                        // Calcular cantidades desde precios y totales
                        $originalPrices = is_array($quotation->original_item_prices) 
                            ? $quotation->original_item_prices 
                            : json_decode($quotation->original_item_prices ?? '[]', true);
                        $originalTotals = is_array($quotation->original_item_totals)
                            ? $quotation->original_item_totals
                            : json_decode($quotation->original_item_totals ?? '[]', true);
                        
                        // Calcular cantidad real desde precio unitario y total
                        if (isset($originalPrices[$selection->item_index]) && isset($originalTotals[$selection->item_index])) {
                            $unitPrice = floatval($originalPrices[$selection->item_index]);
                            $totalPrice = floatval($originalTotals[$selection->item_index]);
                            
                            if ($unitPrice > 0) {
                                $calculatedQuantity = round($totalPrice / $unitPrice);
                                if ($calculatedQuantity > 1) {
                                    $displayQuantity = $calculatedQuantity;
                                    
                                    // Inferir unidad basada en la cantidad
                                    if ($calculatedQuantity >= 5) {
                                        // Cantidades altas probablemente son paquetes o cajas
                                        $description = strtolower($selection->item_description);
                                        if (strpos($description, 'caja') !== false || strpos($description, 'tapabocas') !== false || strpos($description, 'guantes') !== false) {
                                            $itemUnit = 'caja';
                                        } elseif (strpos($description, 'paquete') !== false || strpos($description, 'gasas') !== false || strpos($description, 'algodón') !== false) {
                                            $itemUnit = 'paq';
                                        } else {
                                            $itemUnit = 'und';
                                        }
                                    } else {
                                        // Cantidades bajas probablemente son unidades o frascos
                                        $description = strtolower($selection->item_description);
                                        if (strpos($description, 'frasco') !== false || strpos($description, 'alcohol') !== false) {
                                            $itemUnit = 'frasco';
                                        } else {
                                            $itemUnit = 'und';
                                        }
                                    }
                                }
                            }
                        }
                        
                        $quotationItems = $quotation->items; // Usar el accessor
                        
                        // Verificar si existe el item en el índice correspondiente
                        if (isset($quotationItems[$selection->item_index])) {
                            $originalItem = $quotationItems[$selection->item_index];
                            
                            // Obtener la unidad del ítem original (si no se extrajo de la descripción)
                            if ($itemUnit === 'und' && $displayQuantity === ($selection->quantity ?? 1)) {
                                $itemUnit = $originalItem['unit'] ?? $originalItem['unidad'] ?? 'und';
                                $displayQuantity = $originalItem['quantity'] ?? $displayQuantity;
                            }
                            
                            // Obtener impuestos del item original
                            $ivaRate = floatval($originalItem['iva_rate'] ?? 0);
                            $ipoconsumoRate = floatval($originalItem['ipoconsumo_rate'] ?? 0);
                            
                            // Priorizar Ipoconsumo si existe, sino IVA
                            if ($ipoconsumoRate > 0) {
                                $taxRate = $ipoconsumoRate;
                                $taxType = "Ipoconsumo {$ipoconsumoRate}%";
                            } elseif ($ivaRate > 0) {
                                $taxRate = $ivaRate;
                                $taxType = "IVA {$ivaRate}%";
                            }
                            
                            Log::info('✅ PDF: Cantidad calculada para quotationItemSelection', [
                                'selection_id' => $selection->id,
                                'quotation_id' => $selection->quotation_id,
                                'item_index' => $selection->item_index,
                                'description' => $selection->item_description,
                                'original_quantity' => $selection->quantity,
                                'unit_price' => $originalPrices[$selection->item_index] ?? 'N/A',
                                'total_price' => $originalTotals[$selection->item_index] ?? 'N/A',
                                'calculated_quantity' => $calculatedQuantity ?? 'N/A',
                                'display_quantity' => $displayQuantity,
                                'unit' => $itemUnit,
                                'iva_rate' => $ivaRate,
                                'ipoconsumo_rate' => $ipoconsumoRate,
                                'final_tax' => $taxType
                            ]);
                        } else {
                            Log::warning('⚠️ PDF: Item no encontrado en cotización', [
                                'selection_id' => $selection->id,
                                'quotation_id' => $selection->quotation_id,
                                'item_index' => $selection->item_index,
                                'available_items' => count($quotationItems)
                            ]);
                        }
                    }
                    
                    // 🔥 FALLBACK: Si no se asignó impuesto, usar lógica de fallback
                    if ($taxRate === 0 && $taxType === 'Sin impuesto') {
                        $description = strtolower($selection->item_description ?? '');
                        
                        // 🚨 CORRECCIÓN: Productos específicos SIN IMPUESTO (solo gasas estériles)
                        $noTaxProducts = ['gasas estériles', 'gasas esteriles', 'gasa esteril', 'gasa estéril'];
                        $shouldHaveTax = true;
                        foreach ($noTaxProducts as $product) {
                            if (strpos($description, $product) !== false) {
                                $shouldHaveTax = false;
                                $taxType = 'Sin impuesto';
                                break;
                            }
                        }
                        
                        if ($shouldHaveTax) {
                            // Productos con IVA 5%
                            $iva5Products = ['alcohol', 'antiséptico', 'bicarbonato', 'solución', 'sales', 'rehidratación'];
                            foreach ($iva5Products as $product) {
                                if (strpos($description, $product) !== false) {
                                    $taxRate = 5;
                                    $taxType = 'IVA 5%';
                                    break;
                                }
                            }
                            
                            // Productos con Ipoconsumo 8%
                            if ($taxRate === 0) {
                                $ipoconsumo8Products = ['sábanas', 'curitas', 'aplicadores'];
                                foreach ($ipoconsumo8Products as $product) {
                                    if (strpos($description, $product) !== false) {
                                        $taxRate = 8;
                                        $taxType = 'Ipoconsumo 8%';
                                        break;
                                    }
                                }
                            }
                            
                            // Por defecto: IVA 19% (incluyendo pijamas, gasas, tapabocas, etc.)
                            if ($taxRate === 0) {
                                $taxRate = 19;
                                $taxType = 'IVA 19%';
                            }
                        }
                        
                        Log::info('✅ PDF: Fallback aplicado a quotationItemSelection', [
                            'selection_id' => $selection->id,
                            'description' => $selection->item_description,
                            'final_tax' => $taxType
                        ]);
                    }
                    
                    $itemsToShow[] = [
                        'description' => $selection->item_description ?? 'N/A',
                        'quantity' => $displayQuantity,
                        'unit_price' => $unitPrice,
                        'total' => $displayQuantity * $unitPrice, // ✅ CORREGIDO: Usar cantidad calculada * precio
                        'unit' => $itemUnit,
                        'observations' => $selection->observations ?? '',
                        'tax_rate' => $taxRate,
                        'tax_type' => $taxType
                    ];
                    

                }
                
                Log::info('🎯 VISTA PDF: Items de quotationItemSelections procesados', [
                    'order_id' => $order->id,
                    'items_count' => count($itemsToShow)
                ]);
                
                // 🔥 NUEVA LÓGICA: Detectar impuestos mixtos en quotationItemSelections
                $uniqueTaxCombinations = [];
                foreach ($itemsToShow as $item) {
                    if (isset($item['tax_rate'])) {
                        $taxCombo = floatval($item['tax_rate'] ?? 0);
                        $uniqueTaxCombinations[$taxCombo] = true;
                    }
                }
                
                // Si hay más de 1 combinación de impuestos, activar vista especial
                if (count($uniqueTaxCombinations) > 1) {
                    $hasItemLevelTaxes = true;
                    $showTaxColumn = true;
                    
                    Log::info('✅ PDF: Impuestos mixtos detectados en quotationItemSelections', [
                        'order_id' => $order->id,
                        'unique_combinations' => count($uniqueTaxCombinations),
                        'tax_rates' => array_keys($uniqueTaxCombinations)
                    ]);
                }
                
            // SEGUNDA PRIORIDAD: Si hay customData con items válidos
            } elseif (isset($customData) && is_array($customData)) {
                Log::info('🎯 VISTA PDF: Usando customData (fallback)', [
                    'order_id' => $order->id,
                    'has_items' => isset($customData['items']),
                    'items_count' => isset($customData['items']) && is_array($customData['items']) ? count($customData['items']) : 0
                ]);
                
                // Usar items de customData (pueden estar vacíos si se filtraron todos)
                if (isset($customData['items']) && is_array($customData['items'])) {
                    $itemsToShow = [];
                    
                    // Procesar cada ítem para extraer cantidades y unidades de la descripción
                    foreach ($customData['items'] as $item) {
                        $description = $item['description'] ?? $item['item_description'] ?? '';
                        $originalQuantity = $item['quantity'] ?? $item['cantidad'] ?? 1;
                        $displayQuantity = $originalQuantity;
                        $itemUnit = 'und';
                        
                        // Extraer cantidad y unidad de la descripción si está presente
                        // Regex más inteligente - solo buscar cantidades al inicio o después de palabras clave
                        if (preg_match('/^(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes|caja|cajas|bolsa|bolsas|frasco|frascos)\b/i', $description, $matches)) {
                            // Cantidad al inicio: "5 paq de gasas"
                            $displayQuantity = intval($matches[1]);
                            $itemUnit = strtolower($matches[2]);
                            if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                                $itemUnit = 'paq';
                            } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                                $itemUnit = 'und';
                            }
                        } elseif (preg_match('/\bx\s*(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes|caja|cajas|bolsa|bolsas)?\b/i', $description, $matches)) {
                            // Cantidad con 'x': "gasas x 5", "jeringas x 10 und"
                            $displayQuantity = intval($matches[1]);
                            $itemUnit = isset($matches[2]) ? strtolower($matches[2]) : 'und';
                            if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                                $itemUnit = 'paq';
                            } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                                $itemUnit = 'und';
                            }
                        } elseif (preg_match('/\b(cantidad|cant|qty)[:=]?\s*(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes)?\b/i', $description, $matches)) {
                            // Cantidad explícita: "cantidad: 5", "cant 10 paq"
                            $displayQuantity = intval($matches[2]);
                            $itemUnit = isset($matches[3]) ? strtolower($matches[3]) : 'und';
                            if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                                $itemUnit = 'paq';
                            } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                                $itemUnit = 'und';
                            }
                        } else {
                            // Fallback a unidad especificada o default
                            $itemUnit = $item['unit'] ?? 'und';
                        }
                        
                        // Crear ítem procesado con cantidades/unidades extraídas
                        $processedItem = $item;
                        $processedItem['quantity'] = $displayQuantity;
                        $processedItem['unit'] = $itemUnit;
                        
                        $itemsToShow[] = $processedItem;
                    }
                    
                    Log::info('🎯 VISTA PDF: Items regulares de customData procesados', [
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
                            
                            // Aplicar la misma lógica de extracción de cantidades y unidades
                            $description = $additionalItem['description'];
                            $displayQuantity = $quantity > 0 ? $quantity : 1;
                            $itemUnit = 'und';
                            
                            // Extraer cantidad y unidad de la descripción si está presente
                            // Regex más inteligente - solo buscar cantidades al inicio o después de palabras clave
                            if (preg_match('/^(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes|caja|cajas|bolsa|bolsas|frasco|frascos)\b/i', $description, $matches)) {
                                // Cantidad al inicio: "5 paq de gasas"
                                $displayQuantity = intval($matches[1]);
                                $itemUnit = strtolower($matches[2]);
                                if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                                    $itemUnit = 'paq';
                                } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                                    $itemUnit = 'und';
                                }
                            } elseif (preg_match('/\bx\s*(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes|caja|cajas|bolsa|bolsas)?\b/i', $description, $matches)) {
                                // Cantidad con 'x': "gasas x 5", "jeringas x 10 und"
                                $displayQuantity = intval($matches[1]);
                                $itemUnit = isset($matches[2]) ? strtolower($matches[2]) : 'und';
                                if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                                    $itemUnit = 'paq';
                                } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                                    $itemUnit = 'und';
                                }
                            } elseif (preg_match('/\b(cantidad|cant|qty)[:=]?\s*(\d+)\s*(paq|und|unidad|unidades|paquete|paquetes)?\b/i', $description, $matches)) {
                                // Cantidad explícita: "cantidad: 5", "cant 10 paq"
                                $displayQuantity = intval($matches[2]);
                                $itemUnit = isset($matches[3]) ? strtolower($matches[3]) : 'und';
                                if (in_array($itemUnit, ['paquete', 'paquetes'])) {
                                    $itemUnit = 'paq';
                                } elseif (in_array($itemUnit, ['unidad', 'unidades'])) {
                                    $itemUnit = 'und';
                                }
                            } else {
                                // Fallback a unidad especificada
                                $itemUnit = $additionalItem['unit'] ?? 'und';
                            }
                            
                            $itemsToShow[] = [
                                'description' => $additionalItem['description'],
                                'quantity' => $displayQuantity,
                                'unit_price' => $unitPrice,
                                'total' => $total,
                                'unit' => $itemUnit,
                                'observations' => $additionalItem['observations'] ?? '',
                                'tax_rate' => $additionalItem['tax_rate'] ?? 0
                            ];
                            
                            Log::info('✅ VISTA PDF: Additional_item agregado', [
                                'description' => $additionalItem['description'],
                                'original_quantity' => $quantity,
                                'display_quantity' => $displayQuantity,
                                'unit' => $itemUnit,
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
            
            // NOTA: La lógica antigua que forzaba showTaxColumn para órdenes específicas 
            // se ha reemplazado por la verificación del tax_application_mode de la cotización
            // para garantizar la correcta aplicación de impuestos globales vs por ítem
            
            // CORRECCIÓN: Usar PRIORITARIAMENTE los cálculos guardados en customData
            $calculatedSubtotal = 0;
            $calculatedIva = 0;
            $calculatedIpoconsumo = 0; // Agregar variable para impuesto al consumo
            $calculatedIndividualTaxes = 0;
            
            // Detectar el tipo de IVA e impuestos al consumo desde la cotización seleccionada
            $ivaType = null;
            $ivaLabel = 'IVA';
            $ipoconsumoType = null;
            $ipoconsumoLabel = null;
            
            if ($order->purchaseRequest && $order->purchaseRequest->selectedQuotation) {
                $selectedQuotation = $order->purchaseRequest->selectedQuotation;
                
                // CORREGIDO: Detectar IVA específico (19% o 5%)
                if ($selectedQuotation->includes_iva_19 && $selectedQuotation->iva_19_amount > 0) {
                    $ivaType = '19%';
                    $ivaLabel = 'IVA (19%)';
                } elseif ($selectedQuotation->includes_iva_5 && $selectedQuotation->iva_5_amount > 0) {
                    $ivaType = '5%';
                    $ivaLabel = 'IVA (5%)';
                }
                
                // CORREGIDO: Detectar Impuesto al Consumo
                if ($selectedQuotation->includes_ipoconsumo_8 && $selectedQuotation->ipoconsumo_8_amount > 0) {
                    $ipoconsumoType = '8%';
                    $ipoconsumoLabel = 'IMPUESTO AL CONSUMO (8%)';
                } elseif ($selectedQuotation->includes_ipoconsumo_4 && $selectedQuotation->ipoconsumo_4_amount > 0) {
                    $ipoconsumoType = '4%';
                    $ipoconsumoLabel = 'IMPUESTO AL CONSUMO (4%)';
                }
                
                // Fallback: Si solo hay IVA general sin especificar, analizarlo
                if (!$ivaType && $selectedQuotation->includes_iva && $selectedQuotation->iva_amount > 0) {
                    // Solo considerar como IVA si no hay impuestos al consumo detectados
                    if (!$ipoconsumoType) {
                        $total = floatval($selectedQuotation->total_amount);
                        $ivaAmount = floatval($selectedQuotation->iva_amount);
                        if ($total > 0 && $ivaAmount > 0) {
                            $subtotalCalculated = $total - $ivaAmount;
                            $percentage = round(($ivaAmount / $subtotalCalculated) * 100);
                            if ($percentage >= 18 && $percentage <= 20) {
                                $ivaType = '19%';
                                $ivaLabel = 'IVA (19%)';
                            } elseif ($percentage >= 4 && $percentage <= 6) {
                                $ivaType = '5%';
                                $ivaLabel = 'IVA (5%)';
                            }
                        }
                    }
                }
            }
            
            // Si no se puede determinar desde la cotización, usar customData
            if (!$ivaType && isset($customData['iva_rate'])) {
                $rate = str_replace('%', '', $customData['iva_rate']);
                if (is_numeric($rate) && $rate > 0) {
                    $ivaType = $rate . '%';
                    $ivaLabel = 'IVA (' . $rate . '%)';
                }
            }
            
            // Detectar impuesto al consumo desde customData si no se detectó desde cotización
            if (!$ipoconsumoType && isset($customData['ipoconsumo_rate'])) {
                $rate = str_replace('%', '', $customData['ipoconsumo_rate']);
                if (is_numeric($rate) && $rate > 0) {
                    $ipoconsumoType = $rate . '%';
                    $ipoconsumoLabel = 'IMPUESTO AL CONSUMO (' . $rate . '%)';
                }
            }
            
            // Debug temporal - agregar logs para diagnóstico
            Log::info('🔍 DEBUG PDF - Detección de impuestos', [
                'order_id' => $order->id,
                'ivaType' => $ivaType,
                'ivaLabel' => $ivaLabel,
                'ipoconsumoType' => $ipoconsumoType,
                'ipoconsumoLabel' => $ipoconsumoLabel,
                'has_selected_quotation' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation,
                'quotation_id' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation ? $order->purchaseRequest->selectedQuotation->id : null,
                'includes_ipoconsumo_8' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation ? $order->purchaseRequest->selectedQuotation->includes_ipoconsumo_8 : null,
                'ipoconsumo_8_amount' => $order->purchaseRequest && $order->purchaseRequest->selectedQuotation ? $order->purchaseRequest->selectedQuotation->ipoconsumo_8_amount : null,
            ]);
            
            // Establecer etiquetas por defecto si no se detectaron impuestos
            if (!$ivaType) {
                $ivaLabel = 'IVA'; // No especificar porcentaje si no hay IVA
            }
            if (!$ipoconsumoType) {
                $ipoconsumoLabel = null; // No mostrar línea de impuesto al consumo si no hay
            }
            
            // Si hay customData con cálculos ya realizados, VALIDAR COHERENCIA antes de usar
            $customDataIncoherente = false; // Bandera para controlar recálculo total
            if (isset($customData) && is_array($customData)) {
                // 🔍 NUEVA VALIDACIÓN: Verificar coherencia del customData vs items calculados
                $subtotalCalculadoDesdeItems = 0;
                if (!empty($itemsToShow)) {
                    foreach ($itemsToShow as $item) {
                        $subtotalCalculadoDesdeItems += floatval($item['total']);
                    }
                }
                
                $customDataSubtotal = floatval($customData['subtotal'] ?? 0);
                $diferencia = abs($subtotalCalculadoDesdeItems - $customDataSubtotal);
                
                // Si la diferencia es significativa (más de $50.000), forzar recálculo
                if ($subtotalCalculadoDesdeItems > 0 && $diferencia > 50000) {
                    $customDataIncoherente = true; // ✅ Marcar para recálculo completo
                    
                    Log::warning('⚠️ VISTA PDF: CustomData incoherente, forzando recálculo completo', [
                        'customData_subtotal' => $customDataSubtotal,
                        'calculated_subtotal' => $subtotalCalculadoDesdeItems,
                        'difference' => $diferencia,
                        'order_id' => $order->id
                    ]);
                    
                    // ✅ USAR CÁLCULO CORREGIDO en lugar de customData
                    $calculatedSubtotal = $subtotalCalculadoDesdeItems;
                    $calculatedIva = round($calculatedSubtotal * 0.19);
                    $calculatedIpoconsumo = 0; // Reset ipoconsumo para recálculo
                    $calculatedTotal = $calculatedSubtotal + $calculatedIva + $calculatedIpoconsumo;
                    
                    Log::info('✅ VISTA PDF: Totales recalculados por incoherencia', [
                        'new_subtotal' => $calculatedSubtotal,
                        'new_iva' => $calculatedIva,
                        'new_ipoconsumo' => $calculatedIpoconsumo,
                        'new_total' => $calculatedTotal
                    ]);
                    
                } else {
                    // Usar subtotal de customData si está disponible y es coherente
                    if (isset($customData['subtotal']) && is_numeric($customData['subtotal']) && $customData !== null) {
                        $calculatedSubtotal = floatval($customData['subtotal']);
                        Log::info('🎯 VISTA PDF: Usando subtotal de customData (coherente)', [
                            'subtotal' => $calculatedSubtotal,
                            'order_id' => $order->id
                        ]);
                    }
                }
                
                // Si no se estableció un subtotal en el bloque anterior, buscar en otras fuentes
                if ($calculatedSubtotal <= 0) {
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
                            // CRÍTICO: Para selecciones mixtas, NO usar datos de cotización completa
                            // Usar los valores ya calculados de la orden actual
                            if ($order->subtotal_amount > 0) {
                                $calculatedSubtotal = floatval($order->subtotal_amount);
                                Log::info('🎯 VISTA PDF: Usando subtotal de orden (selección mixta)', [
                                    'subtotal' => $calculatedSubtotal,
                                    'order_id' => $order->id,
                                    'note' => 'Valores de orden, no cotización completa'
                                ]);
                            } else {
                                // Último recurso: calcular desde total - IVA de la ORDEN, no de la cotización
                                if ($order->total_amount > 0 && $order->iva_amount > 0) {
                                    $calculatedSubtotal = floatval($order->total_amount) - floatval($order->iva_amount);
                                    Log::info('🎯 VISTA PDF: Calculando subtotal desde orden (total-IVA)', [
                                        'subtotal' => $calculatedSubtotal,
                                        'order_total' => $order->total_amount,
                                        'order_iva' => $order->iva_amount,
                                        'order_id' => $order->id
                                    ]);
                                }
                            }
                        }
                    }
                }
                
                // 🔥 CORRECCIÓN CRÍTICA: Si hay incoherencia detectada, forzar recálculo completo del IVA
                if ($customDataIncoherente) {
                    // Cuando customData es incoherente, recalcular IVA desde el subtotal corregido
                    $calculatedIva = round($calculatedSubtotal * 0.19);
                    $calculatedIpoconsumo = 0; // Reset para evitar usar valores incorretos
                    
                    Log::info('🚨 VISTA PDF: Recalculando IVA por incoherencia detectada', [
                        'corrected_subtotal' => $calculatedSubtotal,
                        'recalculated_iva' => $calculatedIva,
                        'previous_iva_from_customData' => $customData['iva_amount'] ?? 'N/A',
                        'order_id' => $order->id
                    ]);
                } elseif (isset($customData['iva_amount']) && is_numeric($customData['iva_amount'])) {
                    // Solo usar IVA de customData si NO hay incoherencia detectada
                    $customIva = floatval($customData['iva_amount']);
                    $customIpoconsumo = floatval($customData['ipoconsumo_amount'] ?? 0);
                    $customSubtotal = floatval($customData['subtotal'] ?? 0);
                    $customTotal = floatval($customData['total'] ?? 0);
                    
                    // Validar coherencia: subtotal + iva + ipoconsumo debería ser aproximadamente igual al total
                    $expectedTotal = $customSubtotal + $customIva + $customIpoconsumo;
                    $totalDifference = abs($customTotal - $expectedTotal);
                    
                    Log::info('🔍 DEBUG PDF - Validando coherencia de customData', [
                        'custom_iva' => $customIva,
                        'custom_ipoconsumo' => $customIpoconsumo,
                        'custom_subtotal' => $customSubtotal,
                        'custom_total' => $customTotal,
                        'expected_total' => $expectedTotal,
                        'total_difference' => $totalDifference,
                        'is_coherent' => $totalDifference <= 1 // Tolerancia de $1
                    ]);
                    
                    // Solo usar customData si es coherente
                    if ($totalDifference <= 1) {
                        $calculatedIva = $customIva;
                        $calculatedIpoconsumo = $customIpoconsumo;
                        Log::info('🎯 VISTA PDF: Usando datos de customData (coherente)', [
                            'iva_amount' => $calculatedIva,
                            'ipoconsumo_amount' => $calculatedIpoconsumo,
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
                        
                        // CRÍTICO: Para selecciones mixtas, usar IVA de la ORDEN, no de la cotización completa
                        if ($order->iva_amount > 0) {
                            $calculatedIva = floatval($order->iva_amount);
                            Log::info('🎯 VISTA PDF: Usando IVA de orden (selección mixta)', [
                                'iva_amount' => $calculatedIva,
                                'order_id' => $order->id,
                                'note' => 'IVA de orden, no cotización completa'
                            ]);
                        } else {
                            // Fallback a cotización solo si la orden no tiene IVA calculado
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
                            
                            Log::info('🎯 VISTA PDF: IVA calculado desde cotización (fallback)', [
                                'iva_amount' => $calculatedIva,
                                'iva_19_enabled' => $selectedQuotation->iva_19_enabled,
                                'iva_5_enabled' => $selectedQuotation->iva_5_enabled,
                                'order_id' => $order->id
                            ]);
                        }
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
                    // ✅ CORRECCIÓN: Usar SIEMPRE el total calculado desde displayQuantity × unitPrice
                    $itemTotal = floatval($item['total']);
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
                    // Para órdenes sin customData, aplicar IVA estándar del 19%
                    $calculatedIva = round($calculatedSubtotal * 0.19);
                }
            }
            
            // 🔧 CORRECCIÓN CRÍTICA: Calcular total según el tipo de aplicación de impuestos
            if ($hasItemLevelTaxes) {
                // Para impuestos por ítem: el total es la suma de los totales individuales con impuestos
                $calculatedTotal = 0;
                foreach ($itemsToShow as $item) {
                    $itemPrice = floatval($item['unit_price'] ?? $item['precio_unitario'] ?? 0);
                    $itemQuantity = floatval($item['quantity'] ?? $item['cantidad'] ?? 1);
                    $itemSubtotal = $itemPrice * $itemQuantity;
                    $itemTaxAmount = $itemSubtotal * (($item['tax_rate'] ?? 0) / 100);
                    $itemTotalWithTax = $itemSubtotal + $itemTaxAmount;
                    $calculatedTotal += $itemTotalWithTax;
                }
                $calculatedTotal = round($calculatedTotal);
                
                Log::info('🎯 VISTA PDF: Total calculado desde items individuales con impuestos', [
                    'order_id' => $order->id,
                    'subtotal' => $calculatedSubtotal,
                    'total_from_individual_items' => $calculatedTotal,
                    'calculation_method' => 'per_item_taxes'
                ]);
            } else {
                // Para impuestos globales: subtotal + IVA + Ipoconsumo + impuestos individuales
                $calculatedTotal = round($calculatedSubtotal + $calculatedIva + $calculatedIpoconsumo + $calculatedIndividualTaxes);
                
                Log::info('🎯 VISTA PDF: Total calculado con impuestos globales', [
                    'order_id' => $order->id,
                    'subtotal' => $calculatedSubtotal,
                    'iva' => $calculatedIva,
                    'ipoconsumo' => $calculatedIpoconsumo,
                    'individual_taxes' => $calculatedIndividualTaxes,
                    'total' => $calculatedTotal,
                    'calculation_method' => 'global_taxes'
                ]);
            }
            
            // ===== CORRECCIÓN CRÍTICA: VERIFICAR MODO DE APLICACIÓN DE IMPUESTOS =====
            $hasItemLevelTaxes = false;
            
            // MÉTODO PRINCIPAL: Verificar el tax_application_mode de las cotizaciones relacionadas
            if (isset($quotationItemSelections) && $quotationItemSelections->count() > 0) {
                foreach ($quotationItemSelections as $selection) {
                    if (isset($selection->quotation) && $selection->quotation && isset($selection->quotation->tax_application_mode) && $selection->quotation->tax_application_mode === 'per_item') {
                        $hasItemLevelTaxes = true;
                        Log::info('📋 PDF: Impuestos POR ÍTEM detectados desde cotización', [
                            'quotation_id' => $selection->quotation->id,
                            'tax_mode' => $selection->quotation->tax_application_mode
                        ]);
                        break;
                    } elseif (isset($selection->quotation) && $selection->quotation && isset($selection->quotation->tax_application_mode) && $selection->quotation->tax_application_mode === 'global') {
                        $hasItemLevelTaxes = false;
                        Log::info('📋 PDF: Impuestos GLOBALES detectados desde cotización', [
                            'quotation_id' => $selection->quotation->id,
                            'tax_mode' => $selection->quotation->tax_application_mode
                        ]);
                        break;
                    }
                }
            }
            
            // FALLBACK: Solo si no se encontró información de cotización
            if (!isset($quotationItemSelections) || $quotationItemSelections->count() === 0) {
                // Método fallback: Si hay muchos ítems (más de 8) asumir impuestos por ítem
                if (count($itemsToShow) > 8) {
                    $hasItemLevelTaxes = true;
                    Log::info('📋 PDF: FALLBACK - Muchos ítems detectados, usando impuestos por ítem', [
                        'items_count' => count($itemsToShow)
                    ]);
                }
            }
            
            // Definir si mostrar columna de impuestos basado en el modo de aplicación
            $showTaxColumn = $hasItemLevelTaxes;
            
            Log::info('📋 PDF: MODO DE IMPUESTOS DETERMINADO', [
                'hasItemLevelTaxes' => $hasItemLevelTaxes,
                'showTaxColumn' => $showTaxColumn,
                'method' => isset($quotationItemSelections) && $quotationItemSelections->count() > 0 ? 'cotization_tax_mode' : 'fallback_item_count'
            ]);
            
            // Asegurar que las variables críticas estén definidas para uso posterior
            $calculatedSubtotal = $calculatedSubtotal ?? 0;
            $calculatedIva = $calculatedIva ?? 0;
            $calculatedIpoconsumo = $calculatedIpoconsumo ?? 0;
            $calculatedTotal = $calculatedTotal ?? 0;
            $hasItemLevelTaxes = $hasItemLevelTaxes ?? false;
            $showTaxColumn = $showTaxColumn ?? false;
            $itemsToShow = $itemsToShow ?? [];
        @endphp
        
        <!-- Items -->
        <table>
            <tr>
                <td class="items-header" style="width: 60px;">ITEM</td>
                <td class="items-header">DESCRIPCIÓN</td>
                <td class="items-header" style="width: 60px;">CANT</td>
                <td class="items-header" style="width: 100px;">VALOR UNIT</td>
                @if($showTaxColumn && $hasItemLevelTaxes)
                <td class="items-header" style="width: 80px;">IMPUESTO APLICADO</td>
                <td class="items-header" style="width: 100px;">VALOR TOTAL</td>
                <td class="items-header" style="width: 100px;">TOTAL CON IMPUESTO</td>
                @elseif($showTaxColumn)
                <td class="items-header" style="width: 80px;">IMPUESTO</td>
                <td class="items-header" style="width: 100px;">VALOR TOTAL</td>
                @else
                <td class="items-header" style="width: 100px;">VALOR TOTAL</td>
                @endif
            </tr>

            @if(!empty($itemsToShow))
                @foreach($itemsToShow as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item['description'] ?? $item['item_description'] ?? 'N/A' }}</td>
                    <td class="center">{{ ($item['quantity'] ?? $item['cantidad'] ?? 1) . ' ' . ($item['unit'] ?? 'und') }}</td>
                    <td class="right">${{ number_format(round(floatval($item['unit_price'] ?? $item['precio_unitario'] ?? $item['unit_price_display'] ?? 0)), 0, ',', '.') }}</td>

                    @if($showTaxColumn && $hasItemLevelTaxes)
                    <td class="center">
                        @php
                            $itemTaxRate = $item['tax_rate'] ?? 0;
                            $itemTaxType = $item['tax_type'] ?? '';
                        @endphp
                        @if($itemTaxRate > 0)
                            @if($itemTaxType)
                                {{ $itemTaxType }}
                            @else
                                {{ $itemTaxRate }}%
                            @endif
                        @else
                            Sin impuesto
                        @endif
                    </td>
                    @php
                        $itemPrice = floatval($item['unit_price'] ?? $item['precio_unitario'] ?? 0);
                        $itemQuantity = floatval($item['quantity'] ?? $item['cantidad'] ?? 1);
                        $itemSubtotal = $itemPrice * $itemQuantity;
                        $itemTaxAmount = $itemSubtotal * (($item['tax_rate'] ?? 0) / 100);
                        $itemTotalWithTax = $itemSubtotal + $itemTaxAmount;
                    @endphp
                    <td class="right">${{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                    <td class="right">${{ number_format($itemTotalWithTax, 0, ',', '.') }}</td>
                    @elseif($showTaxColumn)
                    <td class="center">
                        @php
                            $itemTaxRate = $item['tax_rate'] ?? 0;
                            $itemTaxType = $item['tax_type'] ?? '';
                        @endphp
                        @if($itemTaxRate > 0)
                            @if($itemTaxType)
                                {{ $itemTaxType }}
                            @else
                                {{ $itemTaxRate }}%
                            @endif
                        @else
                            Sin impuesto
                        @endif
                    </td>
                    @php
                        $itemPrice = floatval($item['unit_price'] ?? $item['precio_unitario'] ?? 0);
                        $itemQuantity = floatval($item['quantity'] ?? $item['cantidad'] ?? 1);
                        $itemTotal = $itemPrice * $itemQuantity;
                    @endphp
                    <td class="right">${{ number_format($itemTotal, 0, ',', '.') }}</td>
                    @else
                    @php
                        $itemPrice = floatval($item['unit_price'] ?? $item['precio_unitario'] ?? 0);
                        $itemQuantity = floatval($item['quantity'] ?? $item['cantidad'] ?? 1);
                        $itemTotal = $itemPrice * $itemQuantity;
                    @endphp
                    <td class="right">${{ number_format($itemTotal, 0, ',', '.') }}</td>
                    @endif
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
                @if($calculatedIva > 0 && !$hasItemLevelTaxes)
                <td class="label bold">{{ $ivaLabel }}</td>
                <td class="value bold right">${{ number_format($calculatedIva, 0, ',', '.') }}</td>
                @elseif($hasItemLevelTaxes)
                <td class="label bold">IVA</td>
                <td class="value bold right">Aplicado por ítem</td>
                @else
                <td class="label bold">IVA</td>
                <td class="value bold right">$0</td>
                @endif
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
                @if($ipoconsumoType && $ipoconsumoLabel && !$hasItemLevelTaxes)
                <td class="label bold">{{ $ipoconsumoLabel }}</td>
                <td class="value bold right">${{ number_format($calculatedIpoconsumo, 0, ',', '.') }}</td>
                @elseif($hasItemLevelTaxes)
                <td class="label bold">Imp. Consumo</td>
                <td class="value bold right">Aplicado por ítem</td>
                @else
                <td class="label bold">Sin Imp. Consumo</td>
                <td class="value bold right">$0</td>
                @endif
            </tr>
            @else
            <tr>
                <td class="label">PRESUPUESTO:</td>
                <td class="value">{{ $budget ?? 'N/A' }}</td>
                @if($ipoconsumoType && $ipoconsumoLabel && !$hasItemLevelTaxes)
                <td class="label bold">{{ $ipoconsumoLabel }}</td>
                <td class="value bold right">${{ number_format($calculatedIpoconsumo, 0, ',', '.') }}</td>
                @elseif($hasItemLevelTaxes)
                <td class="label bold">Imp. Consumo</td>
                <td class="value bold right">Aplicado por ítem</td>
                @else
                <td class="label bold">Sin Imp. Consumo</td>
                <td class="value bold right">$0</td>
                @endif
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