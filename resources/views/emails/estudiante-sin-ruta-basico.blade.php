<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiante no tomará servicio de ruta</title>
    <style>
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            line-height: 1.6;
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
        
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: #FEFEFE;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 22px;
            letter-spacing: 0.5px;
        }
        
        .header-subtitle {
            font-size: 14px;
            margin-top: 8px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
            background-color: #ffffff;
        }
        
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .alert-box strong {
            color: #856404;
            font-size: 16px;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #314569;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-item {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #314569;
            flex: 0 0 40%;
            min-width: 180px;
        }
        
        .info-value {
            color: #555;
            flex: 1;
            font-size: 15px;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin: 10px 0;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: #ffffff;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h2>🚌 Notificación de Servicio de Ruta</h2>
            <div class="header-subtitle">Estudiante no tomará ruta en la tarde</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="alert-box">
                <strong>⚠️ Importante:</strong> El siguiente estudiante <strong>NO tomará el servicio de ruta en la tarde</strong>.
            </div>

            <p style="margin-bottom: 20px;">
                <strong>Motivo de la salida:</strong> 
                <span class="badge {{ $motivoSalida == 'Salida al medico' ? 'badge-danger' : 'badge-warning' }}">
                    {{ $motivoSalida }}
                </span>
            </p>

            <!-- Información del Estudiante -->
            <div class="info-box">
                <div class="info-item">
                    <span class="info-label">Nombre Completo:</span>
                    <span class="info-value"><strong>{{ $ingreso->estudiante }} {{ $ingreso->apellidos_estudiante }}</strong></span>
                </div>
                @if($ingreso->codigo_estudiante)
                <div class="info-item">
                    <span class="info-label">Código Estudiante:</span>
                    <span class="info-value">{{ $ingreso->codigo_estudiante }}</span>
                </div>
                @endif
                @if($ingreso->documento_estudiante)
                <div class="info-item">
                    <span class="info-label">Documento:</span>
                    <span class="info-value">{{ $ingreso->documento_estudiante }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Curso:</span>
                    <span class="info-value">{{ $ingreso->curso }}</span>
                </div>
            </div>

            <p style="margin-top: 25px; color: #6c757d; font-size: 14px; text-align: center;">
                Por favor, tenga en cuenta esta información para la gestión del servicio de transporte escolar.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Sistema de Gestión TVS</strong></p>
            <p>Este es un correo automático generado por el módulo de Enfermería</p>
            <p>© {{ date('Y') }} TVS - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
