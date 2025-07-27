<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .info-box {
            width: 48%;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-box p {
            margin: 3px 0;
            line-height: 1.4;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .totals-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .totals-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        
        .totals-table .total-row {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        .observations {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .signatures {
            margin-top: 40px;
        }
        
        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            margin: 0 2.5%;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .edited-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Aviso de edición -->
    @if($editedBy)
    <div class="edited-notice">
        <strong>⚠️ DOCUMENTO EDITADO</strong><br>
        Este PDF ha sido personalizado por un administrador el {{ \Carbon\Carbon::parse($editedAt)->format('d/m/Y H:i') }}
    </div>
    @endif

    <!-- Header -->
    <div class="header">
        <h1>ORDEN DE COMPRA</h1>
        <p><strong>No. {{ $order->order_number }}</strong></p>
        <p>Fecha: {{ \Carbon\Carbon::parse($orderDate)->format('d/m/Y') }}</p>
    </div>

    <!-- Información de la empresa y proveedor -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-box">
                <h3>De: Nuestra Empresa</h3>
                <p><strong>{{ config('app.name', 'TVS') }}</strong></p>
                <p>NIT: 123456789-0</p>
                <p>Dirección: Calle Principal #123</p>
                <p>Teléfono: (123) 456-7890</p>
                <p>Email: compras@empresa.com</p>
            </div>
            
            <div class="info-box">
                <h3>Para: Proveedor</h3>
                <p><strong>{{ $customProvider->nombre }}</strong></p>
                @if($customProvider->nit)
                <p>NIT: {{ $customProvider->nit }}</p>
                @endif
                @if($customProvider->direccion)
                <p>Dirección: {{ $customProvider->direccion }}</p>
                @endif
                @if($customProvider->telefono)
                <p>Teléfono: {{ $customProvider->telefono }}</p>
                @endif
                @if($customProvider->email)
                <p>Email: {{ $customProvider->email }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Información de la orden -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-box">
                <h3>Detalles de la Orden</h3>
                <p><strong>Solicitud:</strong> {{ $purchaseRequest->request_number ?? 'N/A' }}</p>
                <p><strong>Solicitante:</strong> {{ $purchaseRequest->user->name ?? 'N/A' }}</p>
                @if($deliveryDate)
                <p><strong>Fecha de Entrega:</strong> {{ \Carbon\Carbon::parse($deliveryDate)->format('d/m/Y') }}</p>
                @endif
                @if($paymentTerms)
                <p><strong>Términos de Pago:</strong> {{ $paymentTerms }}</p>
                @endif
            </div>
            
            <div class="info-box">
                <h3>Estado del Documento</h3>
                <p><strong>Estado:</strong> 
                    @switch($order->status)
                        @case('pending')
                            Pendiente
                            @break
                        @case('approved')
                            Aprobado
                            @break
                        @case('sent_to_accounting')
                            Enviado a Contabilidad
                            @break
                        @case('paid')
                            Pagado
                            @break
                        @default
                            {{ ucfirst($order->status) }}
                    @endswitch
                </p>
                <p><strong>Creado:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                @if($order->approved_at)
                <p><strong>Aprobado:</strong> {{ $order->approved_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabla de items -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%">Descripción</th>
                <th style="width: 15%">Cantidad</th>
                <th style="width: 17.5%">Precio Unitario</th>
                <th style="width: 17.5%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customItems as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td style="text-align: center">{{ number_format($item['quantity']) }}</td>
                <td style="text-align: right">${{ number_format($item['unit_price'], 2, ',', '.') }}</td>
                <td style="text-align: right">${{ number_format($item['total_price'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td style="text-align: right">${{ number_format($subtotal, 2, ',', '.') }}</td>
            </tr>
            @if($taxAmount > 0)
            <tr>
                <td><strong>IVA (19%):</strong></td>
                <td style="text-align: right">${{ number_format($taxAmount, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>TOTAL:</strong></td>
                <td style="text-align: right"><strong>${{ number_format($totalAmount, 2, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Observaciones -->
    @if($observations)
    <div class="observations">
        <h3>Observaciones:</h3>
        <p>{{ $observations }}</p>
    </div>
    @endif

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <strong>Elaborado por</strong><br>
                {{ $purchaseRequest->user->name ?? 'N/A' }}<br>
                Solicitante
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                <strong>Aprobado por</strong><br>
                {{ $purchaseRequest->approver->name ?? 'Pendiente' }}<br>
                Aprobador
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento ha sido generado electrónicamente</p>
        @if($editedBy)
        <p style="color: #856404;"><strong>Documento personalizado - Editado por administrador</strong></p>
        @endif
        <p>Orden de Compra #{{ $order->order_number }} - Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
