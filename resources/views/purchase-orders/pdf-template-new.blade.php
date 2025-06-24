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
                $items = $order->purchaseRequest->purchase_items ?? [];
                if (is_string($items)) {
                    $items = json_decode($items, true) ?? [];
                }
            @endphp
            @foreach($items as $item)
                <tr>
                    <td class="center">{{ $itemNumber++ }}</td>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td class="center">{{ $item['quantity'] ?? 0 }}</td>
                    <td class="right">${{ number_format(($order->subtotal ?? 0) / max(1, ($item['quantity'] ?? 1)), 0, ',', '.') }}</td>
                    <td class="right">${{ number_format(($item['quantity'] ?? 0) * (($order->subtotal ?? 0) / max(1, ($item['quantity'] ?? 1))), 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
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
                <td class="right">${{ number_format($quotation->subtotal ?? $order->subtotal ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">FECHA:</td>
                <td>{{ $order->purchaseRequest->approval_date ? $order->purchaseRequest->approval_date->format('d/m/Y') : '' }}</td>
                @if($quotation && ($quotation->includes_iva_19 || $quotation->includes_iva_5))
                    <td class="bold">
                        @if($quotation->includes_iva_19 && $quotation->includes_iva_5)
                            IVA (19% + 5%)
                        @elseif($quotation->includes_iva_19)
                            IVA (19%)
                        @else
                            IVA (5%)
                        @endif
                    </td>
                    <td class="right">${{ number_format(($quotation->iva_19_amount ?? 0) + ($quotation->iva_5_amount ?? 0), 0, ',', '.') }}</td>
                @else
                    <td class="bold">IVA</td>
                    <td class="right">${{ number_format($order->iva_amount ?? 0, 0, ',', '.') }}</td>
                @endif
            </tr>
            <tr>
                <td class="bold">PRESUPUESTO:</td>
                <td>{{ $order->purchaseRequest->budget ?? '' }}</td>
                @if($quotation && ($quotation->includes_ipoconsumo_8 || $quotation->includes_ipoconsumo_4))
                    <td class="bold">
                        @if($quotation->includes_ipoconsumo_8 && $quotation->includes_ipoconsumo_4)
                            IMPTO AL CONSUMO (8% + 4%)
                        @elseif($quotation->includes_ipoconsumo_8)
                            IMPTO AL CONSUMO (8%)
                        @else
                            IMPTO AL CONSUMO (4%)
                        @endif
                    </td>
                    <td class="right">${{ number_format(($quotation->ipoconsumo_8_amount ?? 0) + ($quotation->ipoconsumo_4_amount ?? 0), 0, ',', '.') }}</td>
                @else
                    <td class="bold">IMPTO AL CONSUMO</td>
                    <td class="right">$0</td>
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
                <td class="right bold">${{ number_format($quotation->total_amount ?? $order->total_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">FIRMA:</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- Desglose detallado de impuestos (solo si aplican) -->
        @if($quotation && ($quotation->includes_iva_19 || $quotation->includes_iva_5 || $quotation->includes_ipoconsumo_8 || $quotation->includes_ipoconsumo_4))
        <table style="margin-top: 10px;">
            <tr>
                <td colspan="4" class="center bold header-section" style="font-size: 12px; padding: 8px;">
                    DESGLOSE DE IMPUESTOS APLICABLES
                </td>
            </tr>
            @if($quotation->includes_iva_19)
            <tr>
                <td class="bold" style="width: 40%;">IVA 19%:</td>
                <td style="width: 25%;">Base: ${{ number_format($quotation->subtotal ?? 0, 0, ',', '.') }}</td>
                <td style="width: 15%;">Tasa: 19%</td>
                <td class="right" style="width: 20%;">Valor: ${{ number_format($quotation->iva_19_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($quotation->includes_iva_5)
            <tr>
                <td class="bold">IVA 5%:</td>
                <td>Base: ${{ number_format($quotation->subtotal ?? 0, 0, ',', '.') }}</td>
                <td>Tasa: 5%</td>
                <td class="right">Valor: ${{ number_format($quotation->iva_5_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($quotation->includes_ipoconsumo_8)
            <tr>
                <td class="bold">Impuesto al Consumo 8%:</td>
                <td>Base: ${{ number_format($quotation->subtotal ?? 0, 0, ',', '.') }}</td>
                <td>Tasa: 8%</td>
                <td class="right">Valor: ${{ number_format($quotation->ipoconsumo_8_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($quotation->includes_ipoconsumo_4)
            <tr>
                <td class="bold">Impuesto al Consumo 4%:</td>
                <td>Base: ${{ number_format($quotation->subtotal ?? 0, 0, ',', '.') }}</td>
                <td>Tasa: 4%</td>
                <td class="right">Valor: ${{ number_format($quotation->ipoconsumo_4_amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr style="background-color: #f0f0f0;">
                <td class="bold">TOTAL IMPUESTOS:</td>
                <td></td>
                <td></td>
                <td class="right bold">${{ number_format(($quotation->iva_19_amount ?? 0) + ($quotation->iva_5_amount ?? 0) + ($quotation->ipoconsumo_8_amount ?? 0) + ($quotation->ipoconsumo_4_amount ?? 0), 0, ',', '.') }}</td>
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
