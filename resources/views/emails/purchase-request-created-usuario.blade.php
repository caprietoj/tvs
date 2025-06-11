<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Solicitud de Compra</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #364E76 0%, #4a6491 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .status-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .info-section {
            background-color: #f8f9fa;
            border-left: 4px solid #364E76;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .info-section h3 {
            color: #364E76;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
            margin-bottom: 4px;
        }
        
        .info-value {
            color: #364E76;
            flex: 1;
        }
        
        .next-steps {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .next-steps h3 {
            color: #1976d2;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .next-steps ul {
            color: #424242;
            padding-left: 20px;
        }
        
        .next-steps li {
            margin-bottom: 8px;
        }
        
        .footer {
            background-color: #364E76;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .footer p {
            margin-bottom: 8px;
            opacity: 0.9;
        }
        
        .footer small {
            opacity: 0.7;
            font-size: 12px;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            
            .content {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                min-width: auto;
                margin-bottom: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Solicitud de Compra Recibida</h1>
            <p>Tu solicitud ha sido registrada exitosamente</p>
        </div>

        <!-- Content -->
        <div class="content">
            <span class="status-badge">✓ Solicitud Registrada</span>
            
            <p style="font-size: 16px; margin-bottom: 20px; color: #495057;">
                Hola <strong>{{ $purchaseRequest->user->name }}</strong>,
            </p>
            
            <p style="margin-bottom: 20px; color: #495057;">
                Te confirmamos que hemos recibido tu solicitud de compra. A continuación puedes ver los detalles de tu solicitud:
            </p>

            <!-- Información de la Solicitud -->
            <div class="info-section">
                <h3>Detalles de tu Solicitud</h3>
                
                <div class="info-row">
                    <span class="info-label">Número de Solicitud:</span>
                    <span class="info-value"><strong>#{{ $purchaseRequest->id }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Tipo de Solicitud:</span>
                    <span class="info-value">
                        @if($purchaseRequest->type === 'purchase')
                            Compra de Productos/Servicios
                        @elseif($purchaseRequest->type === 'materials')
                            Materiales
                        @elseif($purchaseRequest->type === 'copies')
                            Fotocopias
                        @else
                            {{ ucfirst($purchaseRequest->type) }}
                        @endif
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Fecha de Solicitud:</span>
                    <span class="info-value">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Estado Actual:</span>
                    <span class="info-value">
                        @if($purchaseRequest->status === 'pending')
                            Pendiente de Revisión
                        @elseif($purchaseRequest->status === 'En Cotización')
                            En Proceso de Cotización
                        @elseif($purchaseRequest->status === 'Pre-aprobada')
                            Pre-aprobada
                        @elseif($purchaseRequest->status === 'approved')
                            Aprobada
                        @else
                            {{ $purchaseRequest->status }}
                        @endif
                    </span>
                </div>
                
                @if($purchaseRequest->description)
                <div class="info-row">
                    <span class="info-label">Descripción:</span>
                    <span class="info-value">{{ $purchaseRequest->description }}</span>
                </div>
                @endif
                
                @if($purchaseRequest->justification)
                <div class="info-row">
                    <span class="info-label">Justificación:</span>
                    <span class="info-value">{{ $purchaseRequest->justification }}</span>
                </div>
                @endif
                
                @if($purchaseRequest->estimated_cost)
                <div class="info-row">
                    <span class="info-label">Costo Estimado:</span>
                    <span class="info-value">${{ number_format($purchaseRequest->estimated_cost, 2) }}</span>
                </div>
                @endif
            </div>

            <!-- Próximos Pasos -->
            <div class="next-steps">
                <h3>¿Qué sigue ahora?</h3>
                <ul>
                    <li><strong>Revisión:</strong> El departamento de compras revisará tu solicitud</li>
                    <li><strong>Cotización:</strong> Se solicitarán cotizaciones según corresponda</li>
                    <li><strong>Aprobación:</strong> Se evaluará la solicitud para su aprobación</li>
                    <li><strong>Notificación:</strong> Te mantendremos informado sobre el estado de tu solicitud</li>
                </ul>
            </div>

            <p style="color: #495057; margin-top: 20px;">
                <strong>Nota:</strong> Recibirás notificaciones adicionales cuando el estado de tu solicitud cambie. 
                Si tienes alguna pregunta, no dudes en contactar al departamento de compras.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>TVS - Sistema de Solicitudes</strong></p>
            <small>Este es un correo automático, por favor no respondas a este mensaje.</small>
        </div>
    </div>
</body>
</html>
