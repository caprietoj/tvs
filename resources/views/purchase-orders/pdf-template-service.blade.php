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
                <td style="width: 50%;">{{ $order->provider->name ?? 'N/A' }}</td>
                <td class="bold header-section" style="width: 15%;">FECHA:</td>
                <td style="width: 15%;">{{ $order->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="bold header-section">NIT:</td>
                <td>{{ $purchaseRequest->provider_nit ?? $order->provider->nit ?? 'N/A' }}</td>
                <td class="bold header-section">TELÉFONO:</td>
                <td>{{ $purchaseRequest->provider_contact ?? $order->provider->phone ?? 'N/A' }}</td>
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
            <tr>
                <td class="bold header-section" style="width: 70%;">VALOR TOTAL DEL SERVICIO:</td>
                <td class="right bold">${{ number_format($order->total_amount, 0, ',', '.') }}</td>
            </tr>
            @if($includesIva && $ivaAmount > 0)
            <tr>
                <td class="bold header-section">SUBTOTAL (Sin IVA):</td>
                <td class="right">${{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold header-section">IVA (19%):</td>
                <td class="right">${{ number_format($ivaAmount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="bold header-section">VALOR TOTAL EN LETRAS:</td>
                <td class="center bold">{{ $purchaseRequest->service_budget_text ?? '' }}</td>
            </tr>
        </table>

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

        <!-- Firmas -->
        <div class="signature-section">
            <table>
                <tr>
                    <td class="center" style="width: 33%; border: none; height: 80px; vertical-align: bottom;">
                        <div style="border-top: 1px solid #000; margin-top: 60px;">
                            <strong>SOLICITADO POR</strong><br>
                            {{ $purchaseRequest->requester ?? 'N/A' }}<br>
                            Fecha: _____________
                        </div>
                    </td>
                    <td class="center" style="width: 34%; border: none; height: 80px; vertical-align: bottom;">
                        <div style="border-top: 1px solid #000; margin-top: 60px;">
                            <strong>APROBADO POR</strong><br>
                            {{ $order->purchaseRequest->approver->name ?? 'ADMINISTRACIÓN' }}<br>
                            Fecha: {{ $order->purchaseRequest->approval_date ? $order->purchaseRequest->approval_date->format('d/m/Y') : '_____________' }}
                        </div>
                    </td>
                    <td class="center" style="width: 33%; border: none; height: 80px; vertical-align: bottom;">
                        <div style="border-top: 1px solid #000; margin-top: 60px;">
                            <strong>RECIBIDO POR</strong><br>
                            {{ $order->provider->name ?? 'PROVEEDOR' }}<br>
                            Fecha: _____________
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Pie de página -->
        <div style="margin-top: 20px; font-size: 10px; text-align: center;">
            <strong>COLEGIO VICTORIA S.A.S.</strong><br>
            Carrera 15 # 88-64 | PBX: (601) 530-0013 | Bogotá D.C., Colombia<br>
            www.tvs.edu.co | info@tvs.edu.co
        </div>
    </div>
</body>
</html>
