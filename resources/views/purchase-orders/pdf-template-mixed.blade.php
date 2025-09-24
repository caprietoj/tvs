<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra/Servicio - Selección Mixta</title>
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
        
        .provider-section {
            background-color: #f9f9f9;
            font-weight: bold;
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
                <td colspan="7" class="center bold" style="font-size: 14px; padding: 10px; background-color: #e0e0e0;">
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
                <td class="bold">SECCION</td>
                <td>{{ $order->purchaseRequest->section_area ?? '' }}</td>
            </tr>
            <tr>
                <td class="bold">INFORMACIÓN DETALLADA DE PROVEEDORES</td>
                <td colspan="5">
                    <strong>{{ $customData['provider_name'] ?? $order->provider->nombre ?? '' }}</strong> - 
                    NIT: {{ $customData['provider_nit'] ?? 'N/A' }}
                </td>
            </tr>
        </table>

        <!-- Información general -->
        <table>
            <tr>
                <td class="bold">SOLICITANTE:</td>
                <td colspan="2">{{ $order->purchaseRequest->requester ?? '' }}</td>
                <td class="bold">FECHA DE ENTREGA:</td>
                <td colspan="2">{{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : '' }}</td>
            </tr>
            <tr>
                <td class="bold">FORMA DE PAGO:</td>
                <td colspan="5">{{ $order->payment_terms ?? 'Según condiciones por proveedor' }}</td>
            </tr>
        </table>

        <!-- Tabla de productos/servicios por proveedor -->
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
                $currentProvider = '';
                $providerTotal = 0;
                $grandTotal = 0;
                $isSingleProvider = $mixedSelections->pluck('quotation.provider_name')->unique()->count() === 1;
            @endphp
            
            @if($isSingleProvider)
                <!-- Vista simplificada para un solo proveedor -->
                @php $providerName = $mixedSelections->first()->quotation->provider_name; @endphp
                
                <!-- Encabezado del proveedor -->
                <tr class="provider-section">
                    <td colspan="5" class="bold center">{{ $providerName }}</td>
                </tr>
                
                @foreach($mixedSelections as $selection)
                    @php
                        // Calcular el total correcto (cantidad × precio unitario)
                        $unitPrice = floatval($selection->unit_price);
                        $quantity = floatval($selection->quantity);
                        $totalPrice = $quantity * $unitPrice;
                    @endphp
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $selection->item_description }}</td>
                        <td class="center">{{ $quantity }}</td>
                        <td class="right">${{ number_format($unitPrice, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($totalPrice, 0, ',', '.') }}</td>
                    </tr>
                    
                    @php 
                        $providerTotal += $totalPrice;
                        $grandTotal += $totalPrice;
                    @endphp
                @endforeach
                
                <!-- Subtotal del proveedor -->
                <tr class="provider-section">
                    <td colspan="4" class="right bold">Subtotal:</td>
                    <td class="right bold">${{ number_format(floatval($providerTotal), 0, ',', '.') }}</td>
                </tr>
            @else
                <!-- Vista original para múltiples proveedores (compatibilidad) -->
                @foreach($mixedSelections as $selection)
                    @if($currentProvider !== $selection->quotation->provider_name)
                        @if($currentProvider !== '')
                            <!-- Subtotal del proveedor anterior -->
                            <tr class="provider-section">
                                <td colspan="4" class="right bold">Subtotal {{ $currentProvider }}:</td>
                                <td class="right bold">${{ number_format($providerTotal, 0, ',', '.') }}</td>
                            </tr>
                            @php $providerTotal = 0; @endphp
                        @endif
                        @php $currentProvider = $selection->quotation->provider_name; @endphp
                        
                        <!-- Encabezado del nuevo proveedor -->
                        <tr class="provider-section">
                            <td colspan="5" class="bold center">{{ $selection->quotation->provider_name }}</td>
                        </tr>
                    @endif
                    
                    @php
                        // Calcular el total correcto (cantidad × precio unitario)
                        $unitPrice = floatval($selection->unit_price);
                        $quantity = floatval($selection->quantity);
                        $totalPrice = $quantity * $unitPrice;
                    @endphp
                    
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $selection->item_description }}</td>
                        <td class="center">{{ $quantity }}</td>
                        <td class="right">${{ number_format($unitPrice, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($totalPrice, 0, ',', '.') }}</td>
                    </tr>
                    
                    @php 
                        $providerTotal += $totalPrice;
                        $grandTotal += $totalPrice;
                    @endphp
                @endforeach
                
                <!-- Subtotal del último proveedor -->
                @if($currentProvider !== '')
                    <tr class="provider-section">
                        <td colspan="4" class="right bold">Subtotal {{ $currentProvider }}:</td>
                        <td class="right bold">${{ number_format(floatval($providerTotal), 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endif
            
            <tr>
                <td class="bold">Observaciones:</td>
                <td colspan="4">{{ $order->observations ?? 'Orden de compra generada automáticamente' }}</td>
            </tr>
        </table>

        <!-- Sección de aprobación y totales -->
        <table>
            <tr>
                <td class="bold">APROBACIÓN</td>
                <td>{{ $order->purchaseRequest->approver->name ?? '' }}</td>
                <td class="bold">SUB TOTAL</td>
                <td class="right">${{ number_format(floatval($order->subtotal ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">FECHA:</td>
                <td>{{ $order->purchaseRequest->approval_date ? $order->purchaseRequest->approval_date->format('d/m/Y') : '' }}</td>
                <td class="bold">IVA</td>
                <td class="right">${{ number_format(floatval($order->iva_amount ?? 0), 0, ',', '.') }}</td>
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
                        
                        // Formatear el presupuesto con su sección padre: "item padre -> rubro presupuestal"
                        $budgetWithParent = \App\Helpers\BudgetHelper::getBudgetWithParentSection($budgetValue);
                    @endphp
                    {{ $budgetWithParent }}
                </td>
                <td class="bold">IMPTO AL CONSUMO</td>
                <td class="right">$0</td>
            </tr>
            
            {{-- Fila para presupuesto compartido (editable o automático) --}}
            @php
                $customData = json_decode($order->pdf_custom_data ?? '{}', true);
                $sharedBudgetInfo = $customData['shared_budget_info'] ?? '';
                $isShared = $order->purchaseRequest->is_shared;
                $sharedSection = $order->purchaseRequest->shared_section ?? '';
                
                // Mostrar si hay información personalizada o si es compartida automáticamente
                $showSharedBudget = !empty($sharedBudgetInfo) || $isShared;
                $displayText = !empty($sharedBudgetInfo) ? $sharedBudgetInfo : $sharedSection;
            @endphp
            
            @if($showSharedBudget)
            <tr>
                <td class="bold">PRESUPUESTO COMPARTIDO:</td>
                <td>{{ $displayText }}</td>
                <td></td>
                <td></td>
            </tr>
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
                <td class="right bold">${{ number_format(floatval($order->total_amount ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">FIRMA:</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- Instrucciones de facturación -->
        <table style="margin-top: 10px;">
            <tr>
                <td colspan="4" class="center bold header-section" style="font-size: 12px; padding: 8px;">
                    INSTRUCCIONES DE FACTURACIÓN
                </td>
            </tr>
            <tr>
                <td colspan="4" style="padding: 10px; font-size: 11px;">
                    <strong>IMPORTANTE:</strong><br>
                    • Cada proveedor debe facturar únicamente los items que le corresponden según se detalla en esta orden.<br>
                    • El número de orden de compra debe aparecer en todas las facturas: <strong>{{ $order->order_number }}</strong><br>
                    • Las facturas deben enviarse a: <strong>830097105@recepciondefacturas.co</strong><br>
                    • Para consultas contactar al departamento de compras: <strong>compras@tvs.edu.co</strong>
                </td>
            </tr>
        </table>

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
