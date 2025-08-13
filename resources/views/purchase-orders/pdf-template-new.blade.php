<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra/Servicio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
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
            margin-bottom: 10px;
        }
        
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            height: 20px;
        }
        
        .header-section {
            background-color: #f0f0f0;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .center {
            text-align: center;
        }
        
        .right {
            text-align: right;
        }
        
        .no-border {
            border: none;
        }
        
        .email-notice {
            font-size: 10px;
            background-color: #ffffcc;
            padding: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }
        
        .footer-info {
            font-size: 10px;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Aviso de envío de facturas -->
        <div class="email-notice">
            El envío de las facturas se debe realizar al correo 830097105@recepciondefacturas.co para poder realizar las respectivas aceptaciones y acuse de facturas ante la DIAN.
        </div>

        <!-- Título principal -->
        <table>
            <tr>
                <td colspan="6" class="center bold" style="font-size: 14px; padding: 10px; background-color: #e0e0e0;">
                    FORMATO DE ORDEN DE COMPRA Y/O SERVICIO COLEGIO VICTORIA S.A.S
                </td>
            </tr>
        </table>

        <!-- Encabezado principal -->
        <table>
            <tr>
                <td colspan="4" class="center bold header-section">ORDEN DE COMPRA/SERVICIO</td>
                <td class="bold">FECHA</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="bold">Consecutivo COM</td>
                <td colspan="3">{{ $order->order_number }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- Información del proveedor -->
        <table>
            <tr>
                <td class="bold">SEÑORES:</td>
                <td colspan="3">{{ $order->provider->nombre ?? 'IDENTIDAD PÚBLICA DISEÑO Y MEDIOS PUBLICITARIOS SAS' }}</td>
                <td class="bold">SECCION</td>
                <td>{{ $order->purchaseRequest->section_area ?? '' }}</td>
            </tr>            <tr>
                <td class="bold">NIT.</td>
                <td colspan="3">{{ $order->provider->nit ?? '' }}</td>
                <td></td>
                <td></td>
            </tr>            <tr>
                <td class="bold">ATENCION</td>
                <td colspan="3">{{ $order->provider->persona_contacto ?? '' }}</td>
                <td class="bold">TELEFONO</td>
                <td>{{ $order->provider->telefono ?? '' }}</td>
            </tr>
            <tr>
                <td class="bold">DIRECCION:</td>
                <td colspan="3">{{ $order->provider->direccion ?? '' }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="bold">FORMA DE PAGO:</td>
                <td>{{ $order->payment_terms ?? 'Contado' }}</td>
                <td class="bold">FECHA DE ENTREGA:</td>
                <td>{{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : '' }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- Tabla de productos/servicios -->
        <table>
            <tr class="header-section">
                <td class="bold center">N</td>
                <td class="bold center">DESCRIPCION</td>
                <td class="bold center">CANTIDAD</td>
                <td class="bold center">VALOR UNITARIO</td>
                <td class="bold center">VALOR TOTAL</td>
            </tr>
            @php
                $itemNumber = 1;
                $totalItems = 0;
                $subtotalCalculado = 0;
                
                // Determinar si es servicio o compra y obtener items correspondientes
                $isService = $order->purchaseRequest->type === 'services';
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
                
                // Obtener la cotización seleccionada para usar precios específicos
                $selectedQuotation = $quotation ?? $order->purchaseRequest->selectedQuotation ?? null;
                
                // Para servicios, si hay una cotización, usar el subtotal de la cotización
                if ($isService && $selectedQuotation) {
                    $subtotalBase = $selectedQuotation->subtotal ?? 0;
                } else {
                    $subtotalBase = $order->subtotal ?? 0;
                }
            @endphp
            
            @if($isService)
                {{-- Mostrar servicios --}}
                @foreach($items as $index => $item)
                    @if(!empty($item['description']) || !empty($item['quantity']))
                    @php
                        $cantidad = $item['quantity'] ?? 1;
                        $descripcion = $item['description'] ?? '';
                        $totalItems += $cantidad;
                        
                        // Para servicios, calcular precio unitario basado en el subtotal total
                        $totalCantidadServicios = array_sum(array_filter(array_column($items, 'quantity'), function($q) { return $q > 0; }));
                        $precioUnitario = $totalCantidadServicios > 0 ? $subtotalBase / $totalCantidadServicios : $subtotalBase;
                        
                        $total = $cantidad * $precioUnitario;
                        $subtotalCalculado += $total;
                    @endphp
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $descripcion }}</td>
                        <td class="center">{{ $cantidad }}</td>
                        <td class="right">${{ number_format($precioUnitario, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
            @else
                {{-- Mostrar productos (lógica original) --}}
                @foreach($items as $index => $item)
                    @php
                        $cantidad = $item['quantity'] ?? 1;
                        $descripcion = $item['description'] ?? '';
                        
                        // Intentar obtener precio unitario de la cotización seleccionada
                        $precioUnitario = 0;
                        if ($selectedQuotation && 
                            isset($selectedQuotation->original_item_prices) && 
                            isset($selectedQuotation->original_item_prices[$index])) {
                            $precioUnitario = $selectedQuotation->original_item_prices[$index];
                        } else {
                            // Fallback: calcular precio promedio solo si no hay precios específicos
                            $totalCantidad = array_sum(array_column($items, 'quantity'));
                            $precioUnitario = $totalCantidad > 0 ? $subtotalBase / $totalCantidad : 0;
                        }
                        
                        $total = $cantidad * $precioUnitario;
                        $subtotalCalculado += $total;
                    @endphp
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $descripcion }}</td>
                        <td class="center">{{ $cantidad }}</td>
                        <td class="right">${{ number_format($precioUnitario, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            
            @php
                // Si no se calculó el subtotal en el loop anterior (ej: items adicionales)
                if ($subtotalCalculado == 0) {
                    $subtotalCalculado = $subtotalBase;
                }
                
                // Sumar items adicionales
                if($order->additional_items && is_array($order->additional_items)) {
                    foreach($order->additional_items as $additionalItem) {
                        $subtotalCalculado += $additionalItem['total'] ?? 0;
                    }
                }
                
                // Priorizar datos personalizados del PDF si existen
                $customData = null;
                if (!empty($order->pdf_custom_data)) {
                    $customData = json_decode($order->pdf_custom_data, true);
                }
                
                // Recalcular los impuestos basándose en datos personalizados o subtotal
                $ivaCalculado = 0;
                $ipoconsumoCalculado = 0;
                
                if ($customData && isset($customData['iva_amount'])) {
                    // Usar datos personalizados del editor de PDF
                    $ivaCalculado = $customData['iva_amount'];
                    $ipoconsumoCalculado = $customData['ipoconsumo_amount'] ?? 0;
                    $subtotalCalculado = $customData['subtotal'] ?? $subtotalCalculado;
                } elseif ($selectedQuotation) {
                    // Si hay IVA 19% o 5%, calcularlos sobre el subtotal
                    if ($selectedQuotation->includes_iva_19) {
                        $ivaCalculado += $subtotalCalculado * 0.19;
                    }
                    if ($selectedQuotation->includes_iva_5) {
                        $ivaCalculado += $subtotalCalculado * 0.05;
                    }
                    
                    // Si hay impuestos al consumo, calcularlos sobre el subtotal
                    if ($selectedQuotation->includes_ipoconsumo_8) {
                        $ipoconsumoCalculado += $subtotalCalculado * 0.08;
                    }
                    if ($selectedQuotation->includes_ipoconsumo_4) {
                        $ipoconsumoCalculado += $subtotalCalculado * 0.04;
                    }
                } else {
                    // Fallback: usar los valores de la orden original
                    $ivaCalculado = $order->iva_amount ?? 0;
                }
                
                // Calcular total usando datos personalizados si están disponibles
                if ($customData && isset($customData['total'])) {
                    $totalCalculado = $customData['total'];
                } else {
                    $totalCalculado = $subtotalCalculado + $ivaCalculado + $ipoconsumoCalculado;
                }
            @endphp
            
            <!-- Items adicionales si existen -->
            @if($order->additional_items && is_array($order->additional_items))
                @foreach($order->additional_items as $additionalItem)
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $additionalItem['description'] ?? '' }}</td>
                        <td class="center">{{ $additionalItem['quantity'] ?? 0 }}</td>
                        <td class="right">${{ number_format($additionalItem['price'] ?? 0, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($additionalItem['total'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            
            <tr>
                <td class="bold">Observaciones:</td>
                <td colspan="3">{{ $order->observations ?? '' }}</td>
                <td class="right">-</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3"></td>
                <td class="right">-</td>
            </tr>
        </table>

        <!-- Sección de aprobación y totales -->
        <table>
            <tr>
                <td class="bold">APROBACIÓN</td>
                <td>{{ $order->purchaseRequest->approver->name ?? '' }}</td>
                <td class="bold">SUB TOTAL</td>
                <td class="right">${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">FECHA:</td>
                <td>{{ $order->purchaseRequest->approval_date ? $order->purchaseRequest->approval_date->format('d/m/Y') : '' }}</td>
                @if(($order->includes_iva && $ivaCalculado > 0) || ($customData && isset($customData['iva_amount']) && $customData['iva_amount'] > 0))
                    <td class="bold">
                        @php
                            // Determinar el porcentaje de IVA basándose en datos personalizados o cálculo
                            if ($customData && isset($customData['iva_rate'])) {
                                $ivaPercentage = (int) str_replace('%', '', $customData['iva_rate']);
                            } else {
                                $ivaPercentage = $subtotalCalculado > 0 ? round(($ivaCalculado / $subtotalCalculado) * 100) : 0;
                            }
                        @endphp
                        @if($ivaPercentage == 5)
                            IVA (5%)
                        @elseif($ivaPercentage == 19)
                            IVA (19%)
                        @else
                            IVA ({{ $ivaPercentage }}%)
                        @endif
                    </td>
                    <td class="right">${{ number_format($ivaCalculado, 0, ',', '.') }}</td>
                @else
                    <td class="bold">IVA</td>
                    <td class="right">${{ number_format($ivaCalculado, 0, ',', '.') }}</td>
                @endif
            </tr>
            <tr>
                <td class="bold">PRESUPUESTO:</td>
                <td>
                    @php
                        $budgetValue = '';
                        if (!empty($order->pdf_custom_data)) {
                            $customData = json_decode($order->pdf_custom_data, true);
                            $budgetValue = $customData['budget'] ?? '';
                        }
                        if (empty($budgetValue)) {
                            $budgetValue = $order->purchaseRequest->budget ?? '';
                        }
                    @endphp
                    {{ $budgetValue }}
                </td>
                @if($selectedQuotation && ($selectedQuotation->includes_ipoconsumo_8 || $selectedQuotation->includes_ipoconsumo_4))
                    <td class="bold">
                        @if($selectedQuotation->includes_ipoconsumo_8 && $selectedQuotation->includes_ipoconsumo_4)
                            IMPTO AL CONSUMO (8% + 4%)
                        @elseif($selectedQuotation->includes_ipoconsumo_8)
                            IMPTO AL CONSUMO (8%)
                        @else
                            IMPTO AL CONSUMO (4%)
                        @endif
                    </td>
                    <td class="right">${{ number_format($ipoconsumoCalculado, 0, ',', '.') }}</td>
                @else
                    <td class="bold">IMPTO AL CONSUMO</td>
                    <td class="right">${{ number_format($ipoconsumoCalculado, 0, ',', '.') }}</td>
                @endif
            </tr>
            <tr>
                <td class="bold">SECCIÓN / DPTO:</td>
                <td>{{ $order->purchaseRequest->section_area ?? '' }}</td>
                <td class="bold">DESCUENTO</td>
                <td class="right">$0</td>
            </tr>
            <tr>
                <td class="bold">NOMBRE:</td>
                <td>{{ $order->purchaseRequest->requester ?? '' }}</td>
                <td class="bold">TOTAL</td>
                <td class="right bold">${{ number_format($totalCalculado, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">FIRMA:</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- Desglose detallado de impuestos (solo si aplican) -->
        @if($selectedQuotation && ($selectedQuotation->includes_iva_19 || $selectedQuotation->includes_iva_5 || $selectedQuotation->includes_ipoconsumo_8 || $selectedQuotation->includes_ipoconsumo_4))
        <table style="margin-top: 10px;">
            <tr>
                <td colspan="4" class="center bold header-section" style="font-size: 12px; padding: 8px;">
                    DESGLOSE DE IMPUESTOS APLICABLES
                </td>
            </tr>
            @if($selectedQuotation->includes_iva_19)
            <tr>
                <td class="bold" style="width: 40%;">IVA 19%:</td>
                <td style="width: 25%;">Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td style="width: 15%;">Tasa: 19%</td>
                <td class="right" style="width: 20%;">Valor: ${{ number_format($subtotalCalculado * 0.19, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($selectedQuotation->includes_iva_5)
            <tr>
                <td class="bold">IVA 5%:</td>
                <td>Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td>Tasa: 5%</td>
                <td class="right">Valor: ${{ number_format($subtotalCalculado * 0.05, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($selectedQuotation->includes_ipoconsumo_8)
            <tr>
                <td class="bold">Impuesto al Consumo 8%:</td>
                <td>Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td>Tasa: 8%</td>
                <td class="right">Valor: ${{ number_format($subtotalCalculado * 0.08, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($selectedQuotation->includes_ipoconsumo_4)
            <tr>
                <td class="bold">Impuesto al Consumo 4%:</td>
                <td>Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td>Tasa: 4%</td>
                <td class="right">Valor: ${{ number_format($subtotalCalculado * 0.04, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr style="background-color: #f0f0f0;">
                <td class="bold">TOTAL IMPUESTOS:</td>
                <td></td>
                <td></td>
                <td class="right bold">${{ number_format($ivaCalculado + $ipoconsumoCalculado, 0, ',', '.') }}</td>
            </tr>
        </table>
        @endif

        <!-- Información del colegio -->
        <div class="footer-info">
            <strong>FACTURA A FAVOR DE COLEGIO VICTORIA SAS NIT 830.097.105-2</strong><br>
            Calle 215 No. 50-60 Tel (571) 6761503/6763435<br>
            Bogotá - Colombia<br>
            Departamento de Compras email: compras@tvs.edu.co
        </div>
    </div>
</body>
</html>
