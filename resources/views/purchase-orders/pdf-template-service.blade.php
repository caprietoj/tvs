<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Servicio</title>
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
        
        .logo-cell {
            width: 120px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000;
        }
        
        .title-cell {
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 16px;
            border: 1px solid #000;
        }
        
        .order-number-cell {
            width: 120px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000;
        }
        
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
        }
        
        .items-table td {
            height: 25px;
            font-size: 11px;
        }
        
        .signature-section {
            margin-top: 20px;
        }
        
        .signature-box {
            width: 30%;
            height: 80px;
            border: 1px solid #000;
            display: inline-block;
            margin-right: 5%;
            vertical-align: top;
            text-align: center;
            padding-top: 60px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <table>
            <tr>
                <td class="logo-cell">
                    LOGO
                </td>
                <td class="title-cell">
                    COLEGIO VICTORIA S.A.S.<br>
                    GESTIÓN ADMINISTRATIVA Y FINANCIERA
                </td>
                <td class="order-number-cell">
                    <div class="bold">Orden N°:</div>
                    <div>{{ $order->order_number }}</div>
                </td>
            </tr>
        </table>

        <!-- Título del formato -->
        <div class="form-title">
            ORDEN DE SERVICIO
        </div>

        <!-- Información del proveedor -->
        <table style="margin-top: 10px;">
            <tr>
                <td class="bold header-section" style="width: 20%;">PROVEEDOR:</td>
                <td style="width: 50%;">{{ $order->provider->nombre ?? 'N/A' }}</td>
                <td class="bold header-section" style="width: 15%;">FECHA:</td>
                <td style="width: 15%;">{{ $order->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="bold header-section">NIT:</td>
                <td>{{ $purchaseRequest->provider_nit ?? $order->provider->nit ?? 'N/A' }}</td>
                <td class="bold header-section">TELÉFONO:</td>
                <td>{{ $purchaseRequest->provider_contact ?? $order->provider->telefono ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold header-section">EMAIL:</td>
                <td colspan="3">{{ $purchaseRequest->provider_email ?? $order->provider->email ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Información de la solicitud -->
        <table>
            <tr>
                <td class="bold header-section" style="width: 20%;">SOLICITANTE:</td>
                <td style="width: 50%;">{{ $purchaseRequest->requester ?? 'N/A' }}</td>
                <td class="bold header-section" style="width: 15%;">SECCIÓN:</td>
                <td style="width: 15%;">{{ $purchaseRequest->section_area ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="bold header-section">COORDINADOR:</td>
                <td colspan="3">{{ $purchaseRequest->coordinator ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Servicios solicitados -->
        <table class="items-table" style="margin-top: 15px;">
            <tr class="header-section">
                <td class="bold center" style="width: 8%;">ITEM</td>
                <td class="bold center" style="width: 8%;">CANT.</td>
                <td class="bold center" style="width: 60%;">DESCRIPCIÓN DEL SERVICIO</td>
                <td class="bold center" style="width: 24%;">OBSERVACIONES</td>
            </tr>
            @if(isset($purchaseRequest->service_items) && is_array($purchaseRequest->service_items))
                @foreach($purchaseRequest->service_items as $index => $item)
                    @if(!empty($item['description']))
                    <tr>
                        <td class="center">{{ $item['item'] ?? ($index + 1) }}</td>
                        <td class="center">{{ $item['quantity'] ?? 1 }}</td>
                        <td>{{ $item['description'] ?? 'N/A' }}</td>
                        <td>{{ $item['observations'] ?? '' }}</td>
                    </tr>
                    @endif
                @endforeach
            @else
                <tr>
                    <td class="center">1</td>
                    <td class="center">1</td>
                    <td>Servicio según solicitud</td>
                    <td>{{ $purchaseRequest->service_justification ?? '' }}</td>
                </tr>
            @endif
            
            <!-- Filas vacías para completar espacio -->
            @for($i = 0; $i < max(0, 5 - count($purchaseRequest->service_items ?? [])); $i++)
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
        </table>

        <!-- Justificación del servicio -->
        <table>
            <tr>
                <td class="bold header-section" style="width: 25%;">JUSTIFICACIÓN:</td>
                <td>{{ $purchaseRequest->service_justification ?? 'N/A' }}</td>
            </tr>
            @if($purchaseRequest->no_quotation_reason)
            <tr>
                <td class="bold header-section">RAZÓN SIN COTIZACIÓN:</td>
                <td>{{ $purchaseRequest->no_quotation_reason }}</td>
            </tr>
            @endif
        </table>

        <!-- Información financiera -->
        <table style="margin-top: 15px;">
            @php
                // Calcular subtotal e impuestos
                $totalFinal = $order->total_amount ?? $purchaseRequest->total_amount ?? $purchaseRequest->service_budget ?? 0;
                
                // Verificar si hay impuestos detallados en applied_taxes
                $appliedTaxes = null;
                $hasDetailedTaxes = false;
                
                if ($purchaseRequest->applied_taxes) {
                    if (is_string($purchaseRequest->applied_taxes)) {
                        $appliedTaxes = json_decode($purchaseRequest->applied_taxes, true);
                    } else {
                        $appliedTaxes = $purchaseRequest->applied_taxes;
                    }
                    $hasDetailedTaxes = !empty($appliedTaxes);
                }
                
                // Extraer impuestos específicos de applied_taxes
                $iva19Amount = 0;
                $iva5Amount = 0;
                $ipoconsumo8Amount = 0;
                $ipoconsumo4Amount = 0;
                $subtotalCalculado = 0;
                
                if ($hasDetailedTaxes && is_array($appliedTaxes)) {
                    foreach ($appliedTaxes as $tax) {
                        if (isset($tax['type']) && isset($tax['amount'])) {
                            switch ($tax['type']) {
                                case 'iva_19':
                                    $iva19Amount = $tax['amount'];
                                    break;
                                case 'iva_5':
                                    $iva5Amount = $tax['amount'];
                                    break;
                                case 'ipoconsumo_8':
                                    $ipoconsumo8Amount = $tax['amount'];
                                    break;
                                case 'ipoconsumo_4':
                                    $ipoconsumo4Amount = $tax['amount'];
                                    break;
                            }
                        }
                    }
                    
                    $totalImpuestos = $iva19Amount + $iva5Amount + $ipoconsumo8Amount + $ipoconsumo4Amount;
                    $subtotalCalculado = $totalFinal - $totalImpuestos;
                } else {
                    // Usar método tradicional con IVA básico
                    $subtotalCalculado = $subtotal ?? ($totalFinal - ($ivaAmount ?? 0));
                    $ivaTradicional = $ivaAmount ?? 0;
                }
            @endphp
            
            <!-- Mostrar subtotal solo si hay impuestos -->
            @if($hasDetailedTaxes || ($includesIva && $ivaAmount > 0))
            <tr>
                <td class="bold header-section" style="width: 70%;">SUBTOTAL (Sin Impuestos):</td>
                <td class="right">${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
            </tr>
            @endif
            
            <!-- Mostrar impuestos detallados si están configurados -->
            @if($hasDetailedTaxes)
                @if($iva19Amount > 0)
                <tr>
                    <td class="bold header-section">IVA (19%):</td>
                    <td class="right">${{ number_format($iva19Amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                
                @if($iva5Amount > 0)
                <tr>
                    <td class="bold header-section">IVA (5%):</td>
                    <td class="right">${{ number_format($iva5Amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                
                @if($ipoconsumo8Amount > 0)
                <tr>
                    <td class="bold header-section">IMPUESTO AL CONSUMO (8%):</td>
                    <td class="right">${{ number_format($ipoconsumo8Amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                
                @if($ipoconsumo4Amount > 0)
                <tr>
                    <td class="bold header-section">IMPUESTO AL CONSUMO (4%):</td>
                    <td class="right">${{ number_format($ipoconsumo4Amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                
                @if($iva19Amount > 0 || $iva5Amount > 0 || $ipoconsumo8Amount > 0 || $ipoconsumo4Amount > 0)
                <tr>
                    <td class="bold header-section">TOTAL IMPUESTOS:</td>
                    <td class="right bold">${{ number_format($iva19Amount + $iva5Amount + $ipoconsumo8Amount + $ipoconsumo4Amount, 0, ',', '.') }}</td>
                </tr>
                @endif
            @elseif($includesIva && $ivaAmount > 0)
                <!-- Usar IVA tradicional si no hay impuestos detallados -->
                <tr>
                    <td class="bold header-section">IVA (19%):</td>
                    <td class="right">${{ number_format($ivaAmount, 0, ',', '.') }}</td>
                </tr>
            @endif
            
            <tr>
                <td class="bold header-section" style="background-color: #e6f3ff;">VALOR TOTAL DEL SERVICIO:</td>
                <td class="right bold" style="background-color: #e6f3ff; font-size: 14px;">${{ number_format($totalFinal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold header-section">VALOR TOTAL DEL SERVICIO EN LETRAS:</td>
                <td class="center bold">
                    @php
                        use App\Helpers\NumberToWords;
                        echo NumberToWords::convert($totalFinal);
                    @endphp
                </td>
            </tr>
        </table>

        <!-- Desglose detallado de impuestos (solo si aplican) -->
        @if($hasDetailedTaxes && ($iva19Amount > 0 || $iva5Amount > 0 || $ipoconsumo8Amount > 0 || $ipoconsumo4Amount > 0))
        <table style="margin-top: 10px;">
            <tr>
                <td colspan="4" class="center bold header-section" style="font-size: 12px; padding: 8px;">
                    DESGLOSE DE IMPUESTOS APLICABLES
                </td>
            </tr>
            @if($iva19Amount > 0)
            <tr>
                <td class="bold" style="width: 40%;">IVA 19%:</td>
                <td style="width: 25%;">Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td style="width: 15%;">Tasa: 19%</td>
                <td class="right" style="width: 20%;">Valor: ${{ number_format($iva19Amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($iva5Amount > 0)
            <tr>
                <td class="bold">IVA 5%:</td>
                <td>Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td>Tasa: 5%</td>
                <td class="right">Valor: ${{ number_format($iva5Amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($ipoconsumo8Amount > 0)
            <tr>
                <td class="bold">Impuesto al Consumo 8%:</td>
                <td>Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td>Tasa: 8%</td>
                <td class="right">Valor: ${{ number_format($ipoconsumo8Amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($ipoconsumo4Amount > 0)
            <tr>
                <td class="bold">Impuesto al Consumo 4%:</td>
                <td>Base: ${{ number_format($subtotalCalculado, 0, ',', '.') }}</td>
                <td>Tasa: 4%</td>
                <td class="right">Valor: ${{ number_format($ipoconsumo4Amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr style="background-color: #f0f0f0;">
                <td class="bold">TOTAL IMPUESTOS:</td>
                <td></td>
                <td></td>
                <td class="right bold">${{ number_format($iva19Amount + $iva5Amount + $ipoconsumo8Amount + $ipoconsumo4Amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        @endif

        <!-- Términos y condiciones -->
        <table style="margin-top: 15px;">
            <tr>
                <td class="bold header-section" style="width: 20%;">FORMA DE PAGO:</td>
                <td>{{ $order->payment_terms ?? 'Contado' }}</td>
            </tr>
            <tr>
                <td class="bold header-section">FECHA DE ENTREGA:</td>
                <td>{{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'Por coordinar' }}</td>
            </tr>
            <tr>
                <td class="bold header-section">OBSERVACIONES:</td>
                <td>{{ $order->observations ?? $purchaseRequest->general_observations ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Información de Aprobación -->
        @if($purchaseRequest->status === 'approved' && $purchaseRequest->approver)
        <table style="margin-top: 15px;">
            <tr>
                <td class="bold header-section" style="width: 20%;">APROBADO POR:</td>
                <td>{{ $purchaseRequest->approver->name ?? 'N/A' }}</td>
            </tr>
            @if($purchaseRequest->budget)
            <tr>
                <td class="bold header-section">PRESUPUESTO ASIGNADO:</td>
                <td class="bold" style="color: #2c5282;">{{ $purchaseRequest->budget }}</td>
            </tr>
            @endif
        </table>
        @endif
    </div>
</body>
</html>
