<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($purchaseRequest->type == 'purchase')
            Nueva Solicitud de Compra
        @elseif($purchaseRequest->type == 'services')
            Nueva Solicitud de Servicio
        @elseif($purchaseRequest->isCopiesRequest())
            Nueva Solicitud de Fotocopias
        @else
            Nueva Solicitud de Materiales
        @endif
    </title>
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
        
        /* Encabezado simplificado sin imágenes */
        .header {
            background-color: #364E76;
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
        
        .badge {
            display: inline-block;
            position: absolute;
            top: 15px;
            right: 20px;
            background-color: rgba(255,255,255,0.2);
            color: white;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .content {
            padding: 25px;
        }
        
        .welcome-text {
            font-size: 16px;
            margin-bottom: 20px;
            color: #495057;
        }
        
        .icon {
            margin-right: 5px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #364E76;
            border-bottom: 2px solid #364E76;
            padding-bottom: 5px;
        }
        
        .section-title .icon {
            color: #364E76;
            font-weight: bold;
        }
        
        .info-card {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 3px solid #364E76;
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
            flex: 0 0 40%;
            font-weight: 500;
            color: #6c757d;
            font-size: 13px;
        }
        
        .info-value {
            flex: 0 0 60%;
            font-size: 14px;
        }
        
        .highlight {
            font-weight: 600;
            color: #343a40;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 20px 0;
        }
        
        .cta-container {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .cta-button {
            display: inline-block;
            background-color: #364E76;
            color: white !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        
        .cta-button:hover {
            background-color: #2c3e61;
            color: white !important;
            text-decoration: none;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
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
                align-items: flex-start;
            }
            
            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>
                @if($purchaseRequest->type == 'purchase')
                    Nueva Solicitud de Compra
                @elseif($purchaseRequest->type == 'services')
                    Nueva Solicitud de Servicio
                @elseif($purchaseRequest->isCopiesRequest())
                    Nueva Solicitud de Fotocopias
                @else
                    Nueva Solicitud de Materiales
                @endif
            </h2>
            <div class="header-subtitle">Departamento de Compras - TVS</div>
            <div class="badge">
                @if($purchaseRequest->type == 'purchase')
                    COMPRA
                @elseif($purchaseRequest->type == 'services')
                    SERVICIO
                @elseif($purchaseRequest->isCopiesRequest())
                    FOTOCOPIAS
                @else
                    MATERIALES
                @endif
            </div>
        </div>
        
        <div class="content">
            <p class="welcome-text">
                <span class="icon">📋</span> Se ha recibido una nueva solicitud de <strong>
                @if($purchaseRequest->type == 'purchase')
                    compra
                @elseif($purchaseRequest->type == 'services')
                    servicio
                @elseif($purchaseRequest->isCopiesRequest())
                    fotocopias
                @else
                    materiales
                @endif
                </strong> que requiere su atención
                @if($purchaseRequest->type == 'purchase')
                    para el proceso de cotización y gestión
                @elseif($purchaseRequest->type == 'services')
                    para la evaluación y aprobación del servicio solicitado
                @endif
                .
            </p>
            
            <div class="section">
                <h3 class="section-title">
                    <span class="icon">ℹ️</span> Detalles de la Solicitud
                </h3>
                
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">#</span> Número de Solicitud
                        </div>
                        <div class="info-value highlight">{{ $purchaseRequest->request_number ?? '#' . $purchaseRequest->id }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">📅</span> Fecha de Creación
                        </div>
                        <div class="info-value">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">⭐</span> Estado
                        </div>
                        <div class="info-value">
                            <span class="status-badge" style="background-color: 
                                @if($purchaseRequest->status == 'pending')
                                    #fff3cd; color: #856404
                                @elseif($purchaseRequest->status == 'approved')
                                    #d4edda; color: #155724
                                @elseif($purchaseRequest->status == 'rejected') 
                                    #f8d7da; color: #721c24
                                @else
                                    #e2e3e5; color: #383d41
                                @endif
                            ">
                                @if($purchaseRequest->status == 'pending')
                                    ⏳
                                @elseif($purchaseRequest->status == 'approved')
                                    ✅
                                @elseif($purchaseRequest->status == 'rejected')
                                    ❌
                                @else
                                    ℹ️
                                @endif
                                {{ ucfirst($purchaseRequest->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">👤</span> Solicitante
                        </div>
                        <div class="info-value">{{ $purchaseRequest->requester ?? ($purchaseRequest->user ? $purchaseRequest->user->name : 'N/A') }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">🏢</span> Área/Sección
                        </div>
                        <div class="info-value">{{ $purchaseRequest->section_area }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">👨‍💼</span> Creado por
                        </div>
                        <div class="info-value">{{ $purchaseRequest->user ? $purchaseRequest->user->name : 'N/A' }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">🏫</span> Departamento
                        </div>
                        <div class="info-value">
                            @if(isset($purchaseRequest->department_id) && $purchaseRequest->department_id)
                                {{ \App\Models\Department::find($purchaseRequest->department_id)->name ?? 'N/A' }}
                            @elseif(isset($purchaseRequest->department) && $purchaseRequest->department)
                                {{ $purchaseRequest->department->name }}
                            @elseif(isset($purchaseRequest->department_name))
                                {{ $purchaseRequest->department_name }}
                            @elseif($purchaseRequest->user && $purchaseRequest->user->department)
                                {{ $purchaseRequest->user->department->name }}
                            @elseif($purchaseRequest->user && $purchaseRequest->user->department_id)
                                {{ \App\Models\Department::find($purchaseRequest->user->department_id)->name ?? 'N/A' }}
                            @else
                                <span style="color:#999;">No especificado</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="cta-container">
                <p><strong>🔍 Acción Requerida</strong></p>
                <p>Revise la solicitud completa para iniciar el proceso de cotización y gestión correspondiente.</p>
                <a href="{{ url('/purchase-requests/' . $purchaseRequest->id) }}" class="cta-button">
                    Ver solicitud →
                </a>
            </div>
            
            <p style="color: #6c757d; font-size: 12px; margin-top: 20px; text-align: center;">
                <span class="icon">ℹ️</span> Esta notificación ha sido enviada automáticamente al departamento de compras para gestionar la solicitud de 
                @if($purchaseRequest->type == 'purchase')
                    compra
                @elseif($purchaseRequest->type == 'services')
                    servicio
                @elseif($purchaseRequest->isCopiesRequest())
                    fotocopias
                @else
                    materiales
                @endif.
            </p>
        </div>
        
        <div class="footer">
            <p><span class="icon">📧</span> Este es un correo automático, por favor no responda a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Colegio TVS - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
