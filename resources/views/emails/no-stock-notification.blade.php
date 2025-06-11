<!DOCTYPE html>
<html>
<head>
    <title>Productos sin stock disponible</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #364E76;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .note {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #364E76;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Notificación de Productos sin Stock</h1>
        </div>
        
        <div class="content">
            <p>Estimado/a {{ $userName }},</p>
            
            <p>Le informamos que algunos productos solicitados en su reciente <strong>Solicitud de Materiales y Papelería</strong> no se encuentran disponibles en nuestro inventario:</p>
            
            <div class="alert">
                <p><strong>Productos sin stock disponible:</strong></p>
                <table>
                    <thead>
                        <tr>
                            <th>Artículo</th>
                            <th>Cantidad solicitada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($outOfStockItems as $item)
                        <tr>
                            <td>{{ $item['article'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="note">
                <p><strong>IMPORTANTE:</strong> Para obtener estos productos, deberá crear una <strong>Solicitud de Compra</strong> siguiendo el procedimiento establecido.</p>
            </div>
            
            <p>Si necesita más información o tiene alguna consulta, no dude en ponerse en contacto con el departamento de compras.</p>
            
            <a href="{{ route('purchase-requests.create-purchase') }}" class="btn" style="color: white !important">Crear Solicitud de Compra</a>
        </div>
        
        <div class="footer">
            <p>Este es un mensaje automático del sistema de gestión de solicitudes. Por favor no responda a este correo.</p>
            <p>© {{ date('Y') }} Colegio Victoria SAS.</p>
        </div>
    </div>
</body>
</html>