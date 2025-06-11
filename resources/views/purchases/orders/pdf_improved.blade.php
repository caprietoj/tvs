<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background-color: #fff;
            margin: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .dian-info {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 10px;
            text-align: center;
        }
        
        .dian-info .title {
            font-weight: bold;
            color: #0c5460;
            margin-bottom: 5px;
        }
        
        .order-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 20px;
        }
        
        .order-info, .client-info {
            flex: 1;
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            border-left: 4px solid #2c3e50;
        }
        
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 2px;
        }
        
        .info-row {
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        
        .info-label {
            font-weight: bold;
            width: 40%;
        }
        
        .info-value {
            width: 60%;
            text-align: right;
        }
        
        .terms-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .terms-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .terms-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .table-container {
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #34495e;
        }
        
        td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        
        td.text-left {
            text-align: left;
        }
        
        td.text-right {
            text-align: right;
        }
        
        .item-row:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .total-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 2px 0;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .total-value {
            font-weight: bold;
            text-align: right;
        }
        
        .grand-total {
            border-top: 2px solid #2c3e50;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 12px;
            color: #2c3e50;
        }
        
        .observations {
            background-color: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .observations-title {
            font-weight: bold;
            color: #155724;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .approval-section {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }
        
        .approval-box {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px 10px;
            text-align: center;
            min-height: 80px;
        }
        
        .approval-title {
            font-weight: bold;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 20px;
            padding-top: 5px;
            font-size: 9px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }        
        @media print {
            body { margin: 0; }
            .container { box-shadow: none; margin: 0; padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Orden de Compra</h1>
        </div>
        
        <!-- DIAN Information -->
        <div class="dian-info">
            <div class="title">INFORMACIÓN PARA PRESENTACIÓN DE FACTURAS ANTE LA DIAN</div>
            <div>Razón Social: THE VICTORIA SCHOOL | NIT: 830.097.105-2 | Régimen Común</div>
            <div>Dirección: Carrera 15 #123-45, Bogotá D.C., Colombia | Teléfono: (601) 234-5678</div>
            <div>Email: facturacion@tvs.edu.co | Responsabilidad Fiscal: R-99-PN</div>
        </div>
        
        <!-- Order Details -->
        <div class="order-details">
            <div class="order-info">
                <div class="section-title">Información de la Orden</div>
                <div class="info-row">
                    <span class="info-label">No. Orden:</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha Entrega:</span>
                    <span class="info-value">{{ $order->delivery_date->format('d/m/Y') }}</span>
                </div>
                @if($order->purchaseRequest)
                <div class="info-row">
                    <span class="info-label">Sol. Compra:</span>
                    <span class="info-value">{{ $order->purchaseRequest->request_number }}</span>
                </div>
                @endif
            </div>
            
            <div class="client-info">
                <div class="section-title">Información del Proveedor</div>
                <div class="info-row">
                    <span class="info-label">Proveedor:</span>
                    <span class="info-value">{{ $order->provider->nombre }}</span>
                </div>
                @if($order->provider->nit)
                <div class="info-row">
                    <span class="info-label">NIT/CC:</span>
                    <span class="info-value">{{ $order->provider->nit }}</span>
                </div>
                @endif
                @if($order->provider->direccion)
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">{{ $order->provider->direccion }}</span>
                </div>
                @endif
                @if($order->provider->telefono)
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $order->provider->telefono }}</span>
                </div>
                @endif
                @if($order->provider->email)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $order->provider->email }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Terms and Conditions -->
        <div class="terms-section">
            <div class="terms-title">Términos de Pago y Entrega</div>
            <div class="terms-grid">
                <div>
                    <strong>Forma de Pago:</strong> Transferencia electrónica a 30 días
                </div>
                <div>
                    <strong>Lugar de Entrega:</strong> The Victoria School
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%">CANT.</th>
                        <th style="width: 8%">UNIDAD</th>
                        <th style="width: 44%">DESCRIPCIÓN</th>
                        <th style="width: 15%">VALOR UNIT.</th>
                        <th style="width: 10%">DESC. %</th>
                        <th style="width: 15%">VALOR TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $subtotal = 0;
                        $items = [];
                        
                        // Obtener items de la solicitud original
                        if($order->purchaseRequest && $order->purchaseRequest->purchase_items) {
                            foreach($order->purchaseRequest->purchase_items as $item) {
                                $cantidad = $item['quantity'] ?? 1;
                                $precioUnitario = $order->total_amount / array_sum(array_column($order->purchaseRequest->purchase_items, 'quantity'));
                                $total = $cantidad * $precioUnitario;
                                $items[] = [
                                    'cantidad' => $cantidad,
                                    'unidad' => 'UN',
                                    'descripcion' => $item['description'] ?? 'N/A',
                                    'precio_unitario' => $precioUnitario,
                                    'descuento' => 0,
                                    'total' => $total
                                ];
                                $subtotal += $total;
                            }
                        }
                        
                        // Si no hay items, crear uno genérico
                        if(empty($items)) {
                            $items[] = [
                                'cantidad' => 1,
                                'unidad' => 'UN',
                                'descripcion' => 'Producto/Servicio según solicitud ' . ($order->purchaseRequest->request_number ?? ''),
                                'precio_unitario' => $order->total_amount,
                                'descuento' => 0,
                                'total' => $order->total_amount
                            ];
                            $subtotal = $order->total_amount;
                        }
                        
                        $iva = $subtotal * 0.19; // 19% IVA
                        $totalGeneral = $subtotal + $iva;
                    @endphp
                    
                    @foreach($items as $item)
                    <tr class="item-row">
                        <td>{{ number_format($item['cantidad'], 0) }}</td>
                        <td>{{ $item['unidad'] }}</td>
                        <td class="text-left">{{ $item['descripcion'] }}</td>
                        <td class="text-right">${{ number_format($item['precio_unitario'], 0, ',', '.') }}</td>
                        <td>{{ $item['descuento'] }}%</td>
                        <td class="text-right">${{ number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Observations -->
        @if($order->observations)
        <div class="observations">
            <div class="observations-title">Observaciones Especiales:</div>
            {{ $order->observations }}
        </div>
        @else
        <div class="observations">
            <div class="observations-title">Observaciones Especiales:</div>
            • Entregar dentro de los próximos 7 días hábiles según fecha especificada<br>
            • Incluir factura electrónica con el envío<br>
            • Notificar 24 horas antes de la entrega<br>
            • Cumplir con todas las especificaciones técnicas solicitadas
        </div>
        @endif
        
        <!-- Approval Section -->
        <div class="approval-section">
            <div class="approval-box">
                <div class="approval-title">SOLICITADO POR:</div>
                <div class="signature-line">
                    @if($order->purchaseRequest && $order->purchaseRequest->user)
                        {{ $order->purchaseRequest->user->name }}
                    @else
                        DEPARTAMENTO DE COMPRAS
                    @endif
                </div>
            </div>
            
            <div class="approval-box">
                <div class="approval-title">AUTORIZADO POR:</div>
                <div class="signature-line">
                    @if($order->approved_by_user)
                        {{ $order->approved_by_user->name }}
                    @else
                        JEFE DE COMPRAS
                    @endif
                </div>
            </div>
            
            <div class="approval-box">
                <div class="approval-title">RECIBIDO POR:</div>
                <div class="signature-line">PROVEEDOR</div>
            </div>
        </div>
        
        <!-- Totals Section -->
        <div class="total-section">
            <div class="total-row">
                <span class="total-label">SUBTOTAL:</span>
                <span class="total-value">${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">IVA (19%):</span>
                <span class="total-value">${{ number_format($iva, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">DESCUENTOS:</span>
                <span class="total-value">$0</span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label">TOTAL GENERAL:</span>
                <span class="total-value">${{ number_format($totalGeneral, 0, ',', '.') }}</span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>THE VICTORIA SCHOOL</strong> - Sistema de Gestión de Compras</p>
            <p>Este documento es generado automáticamente por el sistema. Para consultas contactar al departamento de compras.</p>
            <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
