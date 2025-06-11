<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud Creada</title>
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
            background-color: #3b5998;
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
            top: 20px;
            right: 20px;
            background-color: 
            @if ($purchaseRequest->type == 'purchase')
                #e74c3c
            @elseif($purchaseRequest->isCopiesRequest())
                #3498db
            @else
                #2ecc71
            @endif;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Contenido */
        .content {
            padding: 25px;
            background-color: #ffffff;
        }
        
        .welcome-text {
            font-size: 15px;
            margin-bottom: 20px;
            color: #555;
            border-left: 3px solid #3b5998;
            padding-left: 12px;
            line-height: 1.6;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            color: #3b5998;
            font-size: 16px;
            margin: 20px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .icon {
            font-family: Arial, sans-serif;
            margin-right: 8px;
            font-weight: bold;
        }
        
        .info-card {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 3px solid #3b5998;
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
        }
        
        .cta-button {
            display: inline-block;
            background-color: #3b5998;
            color: #ffffff; /* Cambiado a blanco explícitamente */
            text-decoration: none;
            text-align: center;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Pie de página */
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
        
        /* Responsive */
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            
            .content {
                padding: 20px 15px;
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
            <h2>Nueva Solicitud Creada</h2>
            <div class="header-subtitle">Sistema de Gestión de Solicitudes TVS</div>
            <div class="badge">
                @if($purchaseRequest->type == 'purchase')
                    COMPRA
                @elseif($purchaseRequest->isCopiesRequest())
                    FOTOCOPIAS
                @else
                    MATERIALES
                @endif
            </div>
        </div>
        
        <div class="content">
            <p class="welcome-text">
                <span class="icon">&#9888;</span> Se ha creado una nueva solicitud de <strong>
                @if($purchaseRequest->type == 'purchase')
                    compra
                @elseif($purchaseRequest->isCopiesRequest())
                    fotocopias
                @else
                    materiales
                @endif
                </strong>. A continuación encontrará los detalles de la misma.
            </p>
            
            <div class="section">
                <h3 class="section-title">
                    <span class="icon">&#8505;</span> Detalles de la Solicitud
                </h3>
                
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">#</span> ID de Solicitud
                        </div>
                        <div class="info-value highlight">#{{ $purchaseRequest->id }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">&#128197;</span> Fecha de Creación
                        </div>
                        <div class="info-value">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">&#9733;</span> Estado
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
                                    &#9203;
                                @elseif($purchaseRequest->status == 'approved')
                                    &#10004;
                                @elseif($purchaseRequest->status == 'rejected')
                                    &#10008;
                                @else
                                    &#8505;
                                @endif
                                {{ ucfirst($purchaseRequest->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">&#128100;</span> Creado por
                        </div>
                        <div class="info-value">{{ $purchaseRequest->user->name }}</div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">
                            <span class="icon">&#127970;</span> Departamento
                        </div>
                        <div class="info-value">
                            @if(isset($purchaseRequest->department_id) && $purchaseRequest->department_id)
                                {{ \App\Models\Department::find($purchaseRequest->department_id)->name ?? 'N/A' }}
                            @elseif(isset($purchaseRequest->department) && $purchaseRequest->department)
                                {{ $purchaseRequest->department->name }}
                            @elseif(isset($purchaseRequest->department_name))
                                {{ $purchaseRequest->department_name }}
                            @elseif(isset($purchaseRequest->user) && isset($purchaseRequest->user->department))
                                {{ $purchaseRequest->user->department->name }}
                            @elseif(isset($purchaseRequest->user) && isset($purchaseRequest->user->department_id))
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
                <p>Para ver todos los detalles o realizar acciones sobre esta solicitud, acceda al sistema:</p>
                <a href="{{ url('/purchase-requests/' . $purchaseRequest->id) }}" class="cta-button" style=color:white !important;>
                    Ver 
                    @if($purchaseRequest->type == 'purchase')
                        Solicitud de Compra
                    @elseif($purchaseRequest->isCopiesRequest())
                        Solicitud de Fotocopias
                    @else
                        Solicitud de Materiales
                    @endif
                     Completa &#8594;
                </a>
            </div>
            
            <p style="color: #6c757d; font-size: 12px; margin-top: 20px; text-align: center;">
                <span class="icon">&#8505;</span> Esta notificación ha sido enviada automáticamente como parte del proceso de seguimiento de 
                @if($purchaseRequest->type == 'purchase')
                    solicitudes de compra
                @elseif($purchaseRequest->isCopiesRequest())
                    solicitudes de fotocopias
                @else
                    solicitudes de materiales
                @endif 
                del sistema de intranet de TVS.
            </p>
        </div>
        
        <div class="footer">
            <p><span class="icon">&#9993;</span> Este es un correo automático, por favor no responda a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Colegio TVS - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>