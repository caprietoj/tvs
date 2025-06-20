<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Proveedor Registrado</title>
    <style>
        /* Estilos base */
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Encabezado */
        .header {
            background-color: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        
        .header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
            letter-spacing: 0.5px;
        }
        
        .header-subtitle {
            font-size: 14px;
            margin-top: 5px;
            opacity: 0.9;
        }
        
        /* Contenido principal */
        .content {
            padding: 30px;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
            align-items: flex-start;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
            color: #495057;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #212529;
            flex: 1;
        }
        
        .section-title {
            color: #28a745;
            font-size: 16px;
            font-weight: 600;
            margin: 25px 0 15px 0;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Footer */
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        /* Responsividad */
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 4px;
            }
            
            .content {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                width: auto;
                margin-bottom: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <h2>🏢 Nuevo Proveedor Registrado</h2>
            <div class="header-subtitle">Sistema de Gestión TVS</div>
        </div>
        
        <!-- Contenido principal -->
        <div class="content">
            <p>Se ha registrado un nuevo proveedor en el sistema:</p>
            
            <!-- Información básica del proveedor -->
            <div class="info-box">
                <div class="section-title">📋 Información General</div>
                
                <div class="info-row">
                    <div class="info-label">Nombre:</div>
                    <div class="info-value"><strong>{{ $proveedor->nombre }}</strong></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">NIT:</div>
                    <div class="info-value">{{ $proveedor->nit }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value">{{ $proveedor->email }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Teléfono:</div>
                    <div class="info-value">{{ $proveedor->telefono }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Ciudad:</div>
                    <div class="info-value">{{ $proveedor->ciudad }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Dirección:</div>
                    <div class="info-value">{{ $proveedor->direccion }}</div>
                </div>
            </div>
            
            <!-- Información de contacto y servicios -->
            <div class="info-box">
                <div class="section-title">👤 Contacto y Servicios</div>
                
                <div class="info-row">
                    <div class="info-label">Contacto:</div>
                    <div class="info-value">{{ $proveedor->persona_contacto }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Servicios/Productos:</div>
                    <div class="info-value">{{ $proveedor->servicio_producto }}</div>
                </div>
                
                @if($proveedor->market_segment)
                <div class="info-row">
                    <div class="info-label">Segmento:</div>
                    <div class="info-value">{{ $proveedor->market_segment }}</div>
                </div>
                @endif
            </div>
            
            <!-- Clasificación del proveedor -->
            <div class="info-box">
                <div class="section-title">🏷️ Clasificación</div>
                
                <div class="info-row">
                    <div class="info-label">Proveedor Crítico:</div>
                    <div class="info-value">
                        @if($proveedor->proveedor_critico)
                            <span class="badge badge-warning">SÍ</span>
                        @else
                            <span class="badge badge-success">NO</span>
                        @endif
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Alto Riesgo:</div>
                    <div class="info-value">
                        @if($proveedor->alto_riesgo)
                            <span class="badge badge-danger">SÍ</span>
                        @else
                            <span class="badge badge-success">NO</span>
                        @endif
                    </div>
                </div>
                
                @if($proveedor->criterios_tecnicos)
                <div class="info-row">
                    <div class="info-label">Criterios Técnicos:</div>
                    <div class="info-value">{{ $proveedor->criterios_tecnicos }}%</div>
                </div>
                @endif
            </div>
            
            <!-- Información adicional -->
            @if($proveedor->forma_pago || $proveedor->descuento || $proveedor->cobertura)
            <div class="info-box">
                <div class="section-title">💼 Información Comercial</div>
                
                @if($proveedor->forma_pago)
                <div class="info-row">
                    <div class="info-label">Forma de Pago:</div>
                    <div class="info-value">{{ $proveedor->forma_pago }}</div>
                </div>
                @endif
                
                @if($proveedor->descuento)
                <div class="info-row">
                    <div class="info-label">Descuento:</div>
                    <div class="info-value">{{ $proveedor->descuento }}</div>
                </div>
                @endif
                
                @if($proveedor->cobertura)
                <div class="info-row">
                    <div class="info-label">Cobertura:</div>
                    <div class="info-value">{{ $proveedor->cobertura }}</div>
                </div>
                @endif
            </div>
            @endif
            
            <p style="margin-top: 25px; color: #6c757d;">
                <strong>Fecha de registro:</strong> {{ $proveedor->created_at->format('d/m/Y H:i') }}
            </p>
            
            <p style="margin-top: 20px;">
                Puede revisar toda la información del proveedor accediendo al sistema de gestión.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Sistema de Gestión TVS</strong></p>
            <p>Este es un mensaje automático, por favor no responder a este correo.</p>
            <p>{{ date('Y') }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
