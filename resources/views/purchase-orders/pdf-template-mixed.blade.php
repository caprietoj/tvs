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
                <td class="bold center">PROVEEDOR</td>
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
                    <td colspan="6" class="bold center">{{ $providerName }}</td>
                </tr>
                
                @foreach($mixedSelections as $selection)
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $selection->item_description }}</td>
                        <td class="center">{{ $selection->quantity }}</td>
                        <td class="right">${{ number_format($selection->unit_price, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($selection->total_price, 0, ',', '.') }}</td>
                        <td class="right">{{ $selection->quotation->delivery_time ?? 'Por definir' }}</td>
                    </tr>
                    
                    @php 
                        $providerTotal += $selection->total_price;
                        $grandTotal += $selection->total_price;
                    @endphp
                @endforeach
                
                <!-- Subtotal del proveedor -->
                <tr class="provider-section">
                    <td colspan="4" class="right bold">Subtotal:</td>
                    <td class="right bold">${{ number_format($providerTotal, 0, ',', '.') }}</td>
                    <td class="right">-</td>
                </tr>
            @else
                <!-- Vista original para múltiples proveedores (compatibilidad) -->
                @foreach($mixedSelections as $selection)
                    @if($currentProvider !== $selection->quotation->provider_name)
                        @if($currentProvider !== '')
                            <!-- Subtotal del proveedor anterior -->
                            <tr class="provider-section">
                                <td colspan="5" class="right bold">Subtotal {{ $currentProvider }}:</td>
                                <td class="right bold">${{ number_format($providerTotal, 0, ',', '.') }}</td>
                            </tr>
                            @php $providerTotal = 0; @endphp
                        @endif
                        @php $currentProvider = $selection->quotation->provider_name; @endphp
                        
                        <!-- Encabezado del nuevo proveedor -->
                        <tr class="provider-section">
                            <td colspan="6" class="bold center">{{ $selection->quotation->provider_name }}</td>
                        </tr>
                    @endif
                    
                    <tr>
                        <td class="center">{{ $itemNumber++ }}</td>
                        <td>{{ $selection->quotation->provider_name }}</td>
                        <td>{{ $selection->item_description }}</td>
                        <td class="center">{{ $selection->quantity }}</td>
                        <td class="right">${{ number_format($selection->unit_price, 0, ',', '.') }}</td>
                        <td class="right">${{ number_format($selection->total_price, 0, ',', '.') }}</td>
                    </tr>
                    
                    @php 
                        $providerTotal += $selection->total_price;
                        $grandTotal += $selection->total_price;
                    @endphp
                @endforeach
                
                <!-- Subtotal del último proveedor -->
                @if($currentProvider !== '')
                    <tr class="provider-section">
                        <td colspan="5" class="right bold">Subtotal {{ $currentProvider }}:</td>
                        <td class="right bold">${{ number_format($providerTotal, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endif
            
            <tr>
                <td class="bold">Observaciones:</td>
                <td colspan="4">{{ $order->observations ?? 'Orden de compra generada automáticamente' }}</td>
                <td class="right">-</td>
            </tr>
        </table>

        <!-- Información detallada de proveedores -->
        <table>
            <tr>
                <td colspan="7" class="center bold header-section" style="font-size: 12px; padding: 8px;">
                    INFORMACIÓN DETALLADA DE PROVEEDORES
                </td>
            </tr>
            <tr class="header-section">
                <td class="bold">PROVEEDOR</td>
                <td class="bold">NIT</td>
                <td class="bold">CONTACTO</td>
                <td class="bold">TELÉFONO</td>
                <td class="bold">EMAIL</td>
                <td class="bold">PAGO</td>
                <td class="bold">TOTAL</td>
            </tr>
            
            @php
                $providerTotals = [];
                foreach($mixedSelections as $selection) {
                    $providerName = $selection->quotation->provider_name;
                    if (!isset($providerTotals[$providerName])) {
                        $providerTotals[$providerName] = [
                            'total' => 0,
                            'quotation' => $selection->quotation
                        ];
                    }
                    $providerTotals[$providerName]['total'] += $selection->total_price;
                }
            @endphp
            
            @foreach($providerTotals as $providerName => $data)
                @php $providerData = $data['quotation']->getProviderData(); @endphp
                <tr>
                    <td>{{ $providerName }}</td>
                    <td>{{ $providerData['nit'] }}</td>
                    <td>{{ $providerData['contacto'] }}</td>
                    <td>{{ $providerData['telefono'] }}</td>
                    <td style="font-size: 10px;">{{ $providerData['email'] }}</td>
                    <td>{{ $data['quotation']->payment_method ?? 'Contado' }}</td>
                    <td class="right bold">${{ number_format($data['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>

        <!-- Sección de aprobación y totales -->
        <table>
            <tr>
                <td class="bold">APROBACIÓN</td>
                <td>{{ $order->purchaseRequest->approver->name ?? '' }}</td>
                <td class="bold">SUB TOTAL</td>
                <td class="right">${{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">FECHA:</td>
                <td>{{ $order->purchaseRequest->approval_date ? $order->purchaseRequest->approval_date->format('d/m/Y') : '' }}</td>
                <td class="bold">IVA</td>
                <td class="right">${{ number_format($order->iva_amount ?? 0, 0, ',', '.') }}</td>
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
                <td class="bold">IMPTO AL CONSUMO</td>
                <td class="right">$0</td>
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
                <td class="right bold">${{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</td>
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
