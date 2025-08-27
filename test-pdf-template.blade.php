<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantilla de Prueba para Precios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .price {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PRUEBA DE CORRECCIÓN DE PRECIOS</h1>
        <p>Esta es una plantilla para verificar la corrección de precios en el PDF</p>
    </div>

    <div>
        <h2>Información de Orden:</h2>
        <p><strong>Número de Orden:</strong> {{ $order->order_number }}</p>
        <p><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y') }}</p>
        <p><strong>Proveedor:</strong> {{ $order->provider->nombre }}</p>
    </div>

    <h2>Detalles de Productos:</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $customData = json_decode($order->pdf_custom_data, true) ?? [];
                $items = $customData['items'] ?? [];
            @endphp

            @if(!empty($items))
                @foreach($items as $index => $item)
                    @php
                        // Asegurar que los precios sean numéricos
                        $unitPrice = is_numeric($item['unit_price']) ? $item['unit_price'] : 0;
                        $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
                        $total = is_numeric($item['total']) ? $item['total'] : ($unitPrice * $quantity);
                        
                        // Agregar debugging info
                        $debug = "Tipo: " . gettype($item['unit_price']) . ", Valor: " . $item['unit_price'];
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['description'] ?? 'Sin descripción' }}</td>
                        <td>{{ $quantity }}</td>
                        <td class="price">{{ number_format($unitPrice, 0, ',', '.') }}</td>
                        <td class="price">{{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" style="background-color: #f9f9f9; font-size: 10px;">DEBUG: {{ $debug }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5">No hay elementos disponibles</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"></td>
                <td><strong>Subtotal:</strong></td>
                <td class="price">{{ number_format($customData['subtotal'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td><strong>IVA:</strong></td>
                <td class="price">{{ number_format($customData['iva_amount'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td><strong>Total:</strong></td>
                <td class="price">{{ number_format($customData['total'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px;">
        <h2>Información de Depuración:</h2>
        <pre>{{ print_r(json_decode($order->pdf_custom_data, true), true) }}</pre>
    </div>
</body>
</html>
