@php
    // Determinar si se está trabajando con un servicio o una orden normal
    $isService = $order->purchaseRequest->request_type === 'service';
    
    // Obtener datos personalizados del PDF si existen
    $customData = json_decode($order->pdf_custom_data ?? '{}', true);
    $hasCustomData = !empty($customData);
    
    // Inicializar variables para cálculos
    $subtotalCalculado = 0;
    $ivaCalculado = 0;
    $ipoconsumoCalculado = 0;
    $totalCalculado = 0;
    $totalItems = 0;
    $itemNumber = 1;
    
    // Obtener la cotización seleccionada para usar precios específicos
    $selectedQuotation = $quotation ?? $order->purchaseRequest->selectedQuotation ?? null;
    
    // CORRECCIÓN CRÍTICA: Verificar si hay precios originales disponibles
    $hasOriginalPrices = $selectedQuotation && isset($selectedQuotation->original_item_prices) && is_array($selectedQuotation->original_item_prices);
    
    if ($hasOriginalPrices) {
        \Log::critical('💲 PDF-TEMPLATE-FIXED: PRECIOS ORIGINALES DISPONIBLES', [
            'order' => $order->order_number,
            'provider' => $order->provider->nombre,
            'original_prices_count' => count($selectedQuotation->original_item_prices)
        ]);
    } else {
        \Log::critical('⚠️ PDF-TEMPLATE-FIXED: NO HAY PRECIOS ORIGINALES', [
            'order' => $order->order_number,
            'provider' => $order->provider->nombre
        ]);
    }
    
    // Si hay items pasados explícitamente (p.ej. desde editPdfNew), usarlos
    if (isset($formItems) && !empty($formItems)) {
        $items = $formItems;
        \Log::critical('🔄 PDF-TEMPLATE-FIXED: USANDO ITEMS DE FORMULARIO', [
            'items_count' => count($items)
        ]);
    } 
    // Si no, usar los items regulares
    else {
        // Inicializar array de items
        $items = [];
        
        if ($isService) {
            // Para servicios, usar service_items
            $items = $order->purchaseRequest->service_items ?? [];
            if (is_string($items)) {
                $items = json_decode($items, true) ?? [];
            }
        } else {
            // Para compras, usar purchase_items
            $items = $order->purchaseRequest->purchase_items ?? [];
            if (is_string($items)) {
                $items = json_decode($items, true) ?? [];
            }
        }
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 12px;
            color: #333;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 200px;
            max-height: 80px;
        }
        .order-info {
            text-align: right;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        .section {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
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
        .footer {
            margin-top: 30px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature {
            width: 45%;
            border-top: 1px solid #333;
            padding-top: 5px;
            text-align: center;
        }
        .approval-section {
            margin-top: 20px;
        }
        .approval-box {
            border: 1px solid #333;
            padding: 10px;
            margin-bottom: 10px;
        }
        /* Agregar bordes a los td */
        td {
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo y número de orden -->
    <div class="header">
        <div>
            <img src="{{ public_path('img/logo.png') }}" alt="Logo" class="logo">
        </div>
        <div class="order-info">
            <h2>ORDEN DE COMPRA</h2>
            <p>No. {{ $order->order_number }}</p>
            <p>Fecha: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- Título central -->
    <div class="title">
        ORDEN DE COMPRA DE {{ $isService ? 'SERVICIOS' : 'BIENES' }}
    </div>

    <!-- Información del proveedor y condiciones -->
    <table>
        <tr>
            <td width="15%"><strong>Proveedor:</strong></td>
            <td width="35%">{{ $customData['provider_name'] ?? $order->provider->nombre }}</td>
            <td width="15%"><strong>Forma de pago:</strong></td>
            <td width="35%">{{ $customData['payment_method'] ?? $order->provider->payment_method ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>NIT:</strong></td>
            <td>{{ $customData['provider_nit'] ?? $order->provider->nit }}</td>
            <td><strong>Presupuesto:</strong></td>
            <td>
                @php
                    $budgetValue = $customData['budget'] ?? $order->budget_id ?? '';
                    $budgetWithParent = \App\Helpers\BudgetHelper::getBudgetWithParentSection($budgetValue);
                @endphp
                {{ $budgetWithParent ?: 'N/A' }}
            </td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $customData['provider_email'] ?? $order->provider->email }}</td>
            <td><strong>Fecha Entrega:</strong></td>
            <td>{{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') ?? 'Por confirmar' }}</td>
        </tr>
        <tr>
            <td><strong>Teléfono:</strong></td>
            <td>{{ $customData['provider_phone'] ?? $order->provider->phone }}</td>
            <td><strong>Lugar Entrega:</strong></td>
            <td>{{ $customData['delivery_address'] ?? $order->delivery_address ?? 'Por definir' }}</td>
        </tr>
    </table>

    <!-- Tabla de ítems -->
    <div class="section">
        <table>
            <tr>
                <th width="5%">#</th>
                <th width="55%">Descripción</th>
                <th width="10%">Cantidad</th>
                <th width="15%">Precio Unitario</th>
                <th width="15%">Total</th>
            </tr>
            
            @php
                // CORRECCIÓN CRÍTICA: Validar y logear los items
                \Log::critical("📦 ITEMS EN PDF FIXED", [
                    "total_items" => count($items),
                    "sample" => array_slice($items, 0, 2)
                ]);
                
                // Reset calculated totals
                $subtotalCalculado = 0;
            @endphp
            
            @foreach($items as $index => $item)
                @php
                    // Asegurar que la cantidad sea numérica
                    $cantidad = isset($item['quantity']) ? floatval($item['quantity']) : 0;
                    $descripcion = isset($item['description']) ? $item['description'] : '';
                    $totalItems += $cantidad;
                    
                    // CORRECCIÓN CRÍTICA: Determinar el precio unitario correcto
                    $precioUnitario = 0;
                    $precioOrigen = "valor por defecto";
                    
                    // Opción 1: Usar precio explícito del item
                    if (isset($item['unit_price'])) {
                        // Convertir explícitamente a float para garantizar que sea numérico
                        $precioUnitario = floatval($item['unit_price']);
                        $precioOrigen = "item['unit_price']";
                    } 
                    // Opción 2: Usar precios originales de la cotización
                    elseif ($hasOriginalPrices && isset($selectedQuotation->original_item_prices[$index])) {
                        // Convertir explícitamente a float
                        $precioUnitario = floatval($selectedQuotation->original_item_prices[$index]);
                        $precioOrigen = "selectedQuotation->original_item_prices[$index]";
                    }
                    // Opción 3: Usar precios de la base de datos
                    elseif (isset($customData['items'][$index]['unit_price'])) {
                        // Convertir explícitamente a float
                        $precioUnitario = floatval($customData['items'][$index]['unit_price']);
                        $precioOrigen = "customData['items'][$index]['unit_price']";
                    }
                    
                    // Asegurarnos que el precio unitario sea un número válido
                    if (!is_numeric($precioUnitario)) {
                        $precioUnitario = 0;
                        $precioOrigen = "corregido por no ser numérico";
                    }
                    
                    // Calcular el total y acumular el subtotal
                    $total = $cantidad * $precioUnitario;
                    $subtotalCalculado += $total;
                    
                    \Log::critical("💵 PRECIO PARA ITEM #{$index}", [
                        "descripcion" => $descripcion,
                        "precio_unitario" => $precioUnitario,
                        "origen" => $precioOrigen,
                        "cantidad" => $cantidad,
                        "total" => $total
                    ]);
                @endphp
                
                @if(!empty($descripcion))
                <tr>
                    <td class="center">{{ $itemNumber++ }}</td>
                    <td>{{ $descripcion }}</td>
                    <td class="center">{{ $cantidad }}</td>
                    <td class="right">${{ number_format($precioUnitario, 0, ',', '.') }}</td>
                    <td class="right">${{ number_format($total, 0, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach
            
            @php
                // Calcular impuestos
                // 1. Usar datos del formulario o customData si están disponibles
                try {
                    if ($hasCustomData && isset($customData['iva_rate'])) {
                        // Asegurar que la tasa de IVA sea numérica
                        $ivaRate = is_string($customData['iva_rate']) ? 
                            intval(str_replace('%', '', $customData['iva_rate'])) : 
                            intval($customData['iva_rate']);
                        
                        // Usar el monto de IVA de customData o calcularlo
                        $ivaCalculado = isset($customData['iva_amount']) ? 
                            floatval($customData['iva_amount']) : 
                            round(($subtotalCalculado * $ivaRate) / 100);
                        
                        if (isset($customData['ipoconsumo_rate'])) {
                            // Asegurar que la tasa de ipoconsumo sea numérica
                            $ipoconsumoRate = is_string($customData['ipoconsumo_rate']) ? 
                                intval(str_replace('%', '', $customData['ipoconsumo_rate'])) : 
                                intval($customData['ipoconsumo_rate']);
                            
                            // Usar el monto de ipoconsumo de customData o calcularlo
                            $ipoconsumoCalculado = isset($customData['ipoconsumo_amount']) ? 
                                floatval($customData['ipoconsumo_amount']) : 
                                round(($subtotalCalculado * $ipoconsumoRate) / 100);
                        } else {
                            $ipoconsumoCalculado = 0;
                        }
                    } else {
                        // Fallback: usar los valores de la orden original
                        $ivaCalculado = $order->iva_amount ?? 0;
                        $ivaCalculado = is_numeric($ivaCalculado) ? floatval($ivaCalculado) : 0;
                        $ipoconsumoCalculado = 0; // La orden no tiene impuesto al consumo por defecto
                    }
                    
                    // Asegurar que todos los valores sean numéricos
                    $subtotalCalculado = is_numeric($subtotalCalculado) ? $subtotalCalculado : 0;
                    $ivaCalculado = is_numeric($ivaCalculado) ? $ivaCalculado : 0;
                    $ipoconsumoCalculado = is_numeric($ipoconsumoCalculado) ? $ipoconsumoCalculado : 0;
                    
                    // Calcular total
                    $totalCalculado = $subtotalCalculado + $ivaCalculado + $ipoconsumoCalculado;
                    
                    \Log::critical("🧮 CÁLCULOS FISCALES", [
                        "subtotal" => $subtotalCalculado,
                        "iva" => $ivaCalculado,
                        "ipoconsumo" => $ipoconsumoCalculado,
                        "total" => $totalCalculado
                    ]);
                } catch (\Exception $e) {
                    \Log::error("⚠️ ERROR EN CÁLCULOS FISCALES: " . $e->getMessage());
                    // Valores seguros en caso de error
                    $subtotalCalculado = $subtotalCalculado ?: 0;
                    $ivaCalculado = 0;
                    $ipoconsumoCalculado = 0;
                    $totalCalculado = $subtotalCalculado;
                }
            @endphp
            
            <!-- Items adicionales si existen -->
            @if(isset($order->additional_items) && is_array($order->additional_items))
                @foreach($order->additional_items as $additionalItem)
                    @php
                        // Convertir valores a numéricos de manera segura
                        $addQuantity = isset($additionalItem['quantity']) ? floatval($additionalItem['quantity']) : 0;
                        $addPrice = isset($additionalItem['price']) ? floatval($additionalItem['price']) : 0;
                        $addTotal = isset($additionalItem['total']) ? floatval($additionalItem['total']) : ($addQuantity * $addPrice);
                        
                        // Acumular al subtotal
                        $subtotalCalculado += $addTotal;
                    @endphp
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $additionalItem['description'] ?? '' }}</td>
                        <td class="center">{{ $addQuantity }}</td>
                        <td class="right">${{ number_format($addPrice, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($addTotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            
            <tr>
                <td class="bold">Observaciones:</td>
                <td colspan="3">{{ $order->observations ?? $customData['observations'] ?? '' }}</td>
                <td class="right">-</td>
            </tr>
        </table>

        <!-- Sección de aprobación y totales -->
        <table>
            <tr>
                <td rowspan="3" width="60%" class="approval-section">
                    <div class="approval-box">
                        <p><strong>Solicitado por:</strong> {{ $order->purchaseRequest->user->name ?? 'N/A' }}</p>
                        <p><strong>Aprobado por:</strong> {{ $order->purchaseRequest->approver->name ?? 'Pendiente' }}</p>
                    </div>
                    <div class="bold">
                        IMPORTANTE: FAVOR ENVIAR FACTURA ELECTRÓNICA AL CORREO facturacion@virtual.net.co
                    </div>
                </td>
                <td width="20%" class="bold right">Subtotal</td>
                <td width="20%" class="right">${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold right">IVA</td>
                <td class="right">${{ number_format($ivaCalculado, 0, ',', '.') }}</td>
            </tr>
            @if($ipoconsumoCalculado > 0)
            <tr>
                <td class="bold right">Impuesto al Consumo</td>
                <td class="right">${{ number_format($ipoconsumoCalculado, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="center bold">
                    <div>______________________________</div>
                    <div>FIRMA AUTORIZADA</div>
                </td>
                <td class="bold right">TOTAL</td>
                <td class="bold right">${{ number_format($totalCalculado, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
