<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra {{ $order->order_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #2d3748;
            background: #fff;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: #fff;
        }
        
        /* Header limpio y profesional */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
        }
        
        .company-info h1 {
            font-size: 24px;
            font-weight: 300;
            color: #1a202c;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .company-details {
            font-size: 10px;
            color: #718096;
            line-height: 1.4;
        }
        
        .order-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
            text-align: right;
            margin: 0;
        }
        
        .order-number {
            font-size: 14px;
            color: #4a5568;
            text-align: right;
            margin-top: 4px;
        }
        
        /* Información principal */
        .main-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-section h3 {
            font-size: 12px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 6px;
        }
        
        .info-label {
            min-width: 100px;
            font-weight: 500;
            color: #4a5568;
            font-size: 10px;
        }
        
        .info-value {
            color: #2d3748;
            font-size: 10px;
        }
        
        /* Tabla de productos minimalista */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .products-table th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            font-weight: 600;
            padding: 15px 12px;
            text-align: left;
            font-size: 10px;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .products-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10px;
            color: #374151;
        }
        
        .products-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .products-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Totales elegantes */
        .totals-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        .totals-box {
            width: 320px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 18px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .total-row:last-child {
            border-bottom: none;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            font-weight: 700;
            color: #0f172a;
        }
        
        .total-label {
            color: #4a5568;
            font-size: 10px;
        }
        
        .total-value {
            color: #2d3748;
            font-weight: 500;
            font-size: 10px;
        }
        
        /* Footer minimalista */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #718096;
            font-size: 9px;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 50px;
            margin: 50px 0 30px 0;
            padding: 20px 0;
        }
        
        .signature {
            text-align: center;
            padding: 20px 10px;
        }
        
        .signature-line {
            border-top: 2px solid #374151;
            margin-bottom: 10px;
            width: 150px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .signature-title {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 3px;
        }
        
        .signature-subtitle {
            font-size: 9px;
            color: #718096;
            margin-top: 2px;
        }
        
        /* Utilities */
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .font-bold { font-weight: 600; }
        .text-gray { color: #718096; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header limpio -->
        <div class="header">
            <div class="company-info">
                <h1>The Victoria School</h1>
                <div class="company-details">
                    NIT: 830.097.105-2<br>
                    Carrera 15 #123-45, Bogotá D.C.<br>
                    Tel: (601) 234-5678<br>
                    compras@tvs.edu.co
                </div>
            </div>
            <div>
                <div class="order-title">ORDEN DE COMPRA</div>
                <div class="order-number">No. {{ $order->order_number }}</div>
            </div>
        </div>

        <!-- Información principal -->
        <div class="main-info">
            <div class="info-section">
                <h3>Información de la Orden</h3>
                <div class="info-item">
                    <span class="info-label">Fecha Emisión:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha Entrega:</span>
                    <span class="info-value">{{ $order->delivery_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Solicitud:</span>
                    <span class="info-value">{{ $order->purchaseRequest->request_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Forma de Pago:</span>
                    <span class="info-value">{{ $order->payment_terms ?? 'N/A' }}</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Proveedor</h3>
                <div class="info-item">
                    <span class="info-label">Razón Social:</span>
                    <span class="info-value font-bold">{{ $order->provider->nombre }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">NIT:</span>
                    <span class="info-value">{{ $order->provider->nit ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $order->provider->telefono ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $order->provider->email ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">{{ $order->provider->direccion ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 8%">Item</th>
                    <th style="width: 50%">Descripción</th>
                    <th style="width: 10%">Cant.</th>
                    <th style="width: 8%">Unidad</th>
                    <th style="width: 12%">Precio Unit.</th>
                    <th style="width: 12%">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $itemNumber = 1; @endphp
                
                <!-- Productos de la solicitud original -->
                @if($order->purchaseRequest && $order->purchaseRequest->purchase_items)
                    @foreach($order->purchaseRequest->purchase_items as $item)
                        <tr>
                            <td class="text-center">{{ $itemNumber++ }}</td>
                            <td>{{ $item['description'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="text-center">{{ $item['unit'] ?? 'N/A' }}</td>
                            <td class="text-right">${{ number_format(($order->total_amount / array_sum(array_column($order->purchaseRequest->purchase_items, 'quantity'))) ?? 0, 2, ',', '.') }}</td>
                            <td class="text-right">${{ number_format((($order->total_amount / array_sum(array_column($order->purchaseRequest->purchase_items, 'quantity'))) * ($item['quantity'] ?? 0)) ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
                
                <!-- Productos adicionales -->
                @if($order->additional_items && is_array($order->additional_items))
                    @foreach($order->additional_items as $item)
                        <tr>
                            <td class="text-center">{{ $itemNumber++ }}</td>
                            <td>{{ $item['description'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="text-center">{{ $item['unit'] ?? 'N/A' }}</td>
                            <td class="text-right">${{ number_format($item['price'] ?? 0, 2, ',', '.') }}</td>
                            <td class="text-right">${{ number_format(($item['quantity'] * $item['price']) ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
                
                @if($itemNumber == 1)
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #718096;">
                            No hay productos registrados para esta orden.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totals-container">
            <div class="totals-box">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span class="total-value">${{ number_format($order->total_amount * 0.84, 2, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">IVA (19%):</span>
                    <span class="total-value">${{ number_format($order->total_amount * 0.16, 2, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label font-bold">TOTAL:</span>
                    <span class="total-value font-bold">${{ number_format($order->total_amount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        @if($order->observations)
        <div style="margin: 30px 0; padding: 15px; border: 1px solid #e2e8f0; border-radius: 4px; background: #f9fafb;">
            <div style="font-weight: 600; margin-bottom: 8px; color: #4a5568; font-size: 11px;">OBSERVACIONES:</div>
            <div style="font-size: 10px; color: #2d3748; line-height: 1.5;">{{ $order->observations }}</div>
        </div>
        @endif

        <!-- Firmas -->
        <div class="signatures">
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">Elaborado por</div>
                <div class="signature-subtitle">Área de Compras</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">Aprobado por</div>
                <div class="signature-subtitle">Dirección Administrativa</div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-title">Recibido por</div>
                <div class="signature-subtitle">Proveedor</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="mb-2">
                <strong>The Victoria School</strong> | NIT: 830.097.105-2 | compras@tvs.edu.co | (601) 234-5678
            </div>
            <div class="mb-1">
                Este documento constituye una orden de compra oficial. Conservar para efectos contables y de auditoría.
            </div>
            <div style="font-size: 8px;">
                Documento generado el {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        
        .table th {
            background-color: #f5f5f5;
        }
        
        .total-row {
            font-weight: bold;
        }
        
        .info-block {
            margin-bottom: 15px;
        }
        
        .label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }
        
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            margin: 50px auto 10px;
            text-align: center;
        }
        
        .signature-name {
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td width="50%">
                    <img src="{{ public_path('img/the_victoria.png') }}" alt="Logo" class="logo">
                </td>
                <td width="50%" style="text-align: right;">
                    <div class="order-title">ORDEN DE COMPRA</div>
                    <div class="order-number">N° {{ $order->order_number }}</div>
                    <div>Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL PROVEEDOR</div>
        <table width="100%">
            <tr>
                <td width="50%">
                    <div class="info-block">
                        <span class="label">Proveedor:</span> {{ $order->provider->nombre }}
                    </div>
                    <div class="info-block">
                        <span class="label">NIT:</span> {{ $order->provider->nit }}
                    </div>
                    <div class="info-block">
                        <span class="label">Contacto:</span> {{ $order->provider->contacto }}
                    </div>
                </td>
                <td width="50%">
                    <div class="info-block">
                        <span class="label">Dirección:</span> {{ $order->provider->direccion }}
                    </div>
                    <div class="info-block">
                        <span class="label">Teléfono:</span> {{ $order->provider->telefono }}
                    </div>
                    <div class="info-block">
                        <span class="label">Email:</span> {{ $order->provider->email }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">DATOS DE LA ORDEN</div>
        <table width="100%">
            <tr>
                <td width="50%">
                    <div class="info-block">
                        <span class="label">Solicitud N°:</span> {{ $order->purchaseRequest->request_number }}
                    </div>
                    <div class="info-block">
                        <span class="label">Solicitante:</span> {{ $order->purchaseRequest->requester }}
                    </div>
                    <div class="info-block">
                        <span class="label">Área/Sección:</span> {{ $order->purchaseRequest->section_area }}
                    </div>
                </td>
                <td width="50%">
                    <div class="info-block">
                        <span class="label">Fecha entrega:</span> {{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}
                    </div>
                    <div class="info-block">
                        <span class="label">Términos de pago:</span> {{ $order->payment_terms }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">DETALLE DE LA ORDEN</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">N°</th>
                    <th width="45%">Descripción</th>
                    <th width="10%">Cantidad</th>
                    <th width="10%">Unidad</th>
                    <th width="15%">Precio Unit.</th>
                    <th width="15%">Total</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($items) && count($items) > 0)
                    @foreach($items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['description'] ?? 'N/A' }}</td>
                            <td>{{ $item['quantity'] ?? '1' }}</td>
                            <td>{{ $item['unit'] ?? 'Unidad' }}</td>
                            <td style="text-align: right;">
                                @if(isset($item['price']))
                                    ${{ number_format($item['price'], 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if(isset($item['price']) && isset($item['quantity']))
                                    ${{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
                
                @if(isset($additionalItems) && count($additionalItems) > 0)
                    @if(isset($items) && count($items) > 0)
                        <tr>
                            <td colspan="6" style="background-color: #f9f9f9; font-weight: bold; text-align: center;">Items Adicionales</td>
                        </tr>
                    @endif
                    @foreach($additionalItems as $index => $item)
                        <tr>
                            <td>{{ (isset($items) ? count($items) : 0) + $index + 1 }}</td>
                            <td>{{ $item['description'] ?? 'N/A' }}</td>
                            <td>{{ $item['quantity'] ?? '1' }}</td>
                            <td>{{ $item['unit'] ?? 'Unidad' }}</td>
                            <td style="text-align: right;">${{ number_format($item['price'] ?? 0, 2, ',', '.') }}</td>
                            <td style="text-align: right;">${{ number_format($item['total'] ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" rowspan="{{ $includesIva ? 3 : 2 }}" style="border-right: none; vertical-align: top;">
                        <strong>Observaciones:</strong><br>
                        {{ $order->observations ?? 'Sin observaciones.' }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">Subtotal:</td>
                    <td style="text-align: right;">${{ number_format($subtotal ?? $order->total_amount, 2, ',', '.') }}</td>
                </tr>
                @if($includesIva)
                <tr>
                    <td style="text-align: right; font-weight: bold;">IVA (19%):</td>
                    <td style="text-align: right;">${{ number_format($ivaAmount ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td style="text-align: right;">${{ number_format($order->total_amount, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="33%" style="text-align: center;">
                    <div class="signature-line"></div>
                    <div class="signature-name">Elaborado por</div>
                </td>
                <td width="33%" style="text-align: center;">
                    <div class="signature-line"></div>
                    <div class="signature-name">Revisado por</div>
                </td>
                <td width="33%" style="text-align: center;">
                    <div class="signature-line"></div>
                    <div class="signature-name">Autorizado por</div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <p>Esta orden de compra fue generada automáticamente por el sistema el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>{{ config('app.name') }} - Todos los derechos reservados {{ date('Y') }}</p>
    </div>
</body>
</html>