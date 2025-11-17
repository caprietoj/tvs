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
            max-width: 650px;
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
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555;
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
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-section h3 {
            color: #314569;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            border-bottom: 2px solid #314569;
            padding-bottom: 5px;
        }
        
        .info-item {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #314569;
            flex: 0 0 40%;
            min-width: 200px;
        }
        
        .info-value {
            color: #555;
            flex: 1;
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
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: #ffffff;
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
                <strong>⚠️ Importante:</strong> Se le informa que el siguiente estudiante <strong>NO tomará el servicio de ruta en la tarde</strong>.
            </div>

            <p style="margin-bottom: 20px;">
                <strong>Motivo de la salida:</strong> 
                <span class="badge {{ $motivoSalida == 'Salida al Medico' ? 'badge-danger' : 'badge-warning' }}">
                    {{ $motivoSalida }}
                </span>
            </p>

            <!-- Información del Estudiante -->
            <div class="info-section">
                <h3>👤 Datos del Estudiante</h3>
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
                    @if($ingreso->sexo_estudiante)
                    <div class="info-item">
                        <span class="info-label">Sexo:</span>
                        <span class="info-value">{{ $ingreso->sexo_estudiante == 'M' ? 'Masculino' : 'Femenino' }}</span>
                    </div>
                    @endif
                    @if($ingreso->tipo_sangre_estudiante)
                    <div class="info-item">
                        <span class="info-label">Tipo de Sangre:</span>
                        <span class="info-value">{{ $ingreso->tipo_sangre_estudiante }}</span>
                    </div>
                    @endif
                    @if($ingreso->eps_estudiante)
                    <div class="info-item">
                        <span class="info-label">EPS:</span>
                        <span class="info-value">{{ $ingreso->eps_estudiante }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información de la Atención -->
            <div class="info-section">
                <h3>🏥 Información de Atención en Enfermería</h3>
                <div class="info-box">
                    <div class="info-item">
                        <span class="info-label">Fecha de Atención:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Hora de Atención:</span>
                        <span class="info-value">{{ $ingreso->hora }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Motivo de Consulta:</span>
                        <span class="info-value">{{ $ingreso->motivo }}</span>
                    </div>
                    @if($ingreso->descripcion_evento)
                    <div class="info-item">
                        <span class="info-label">Descripción del Evento:</span>
                        <span class="info-value">{{ $ingreso->descripcion_evento }}</span>
                    </div>
                    @endif
                    @if($ingreso->accion_enfermeria)
                    <div class="info-item">
                        <span class="info-label">Acción de Enfermería:</span>
                        <span class="info-value">{{ $ingreso->accion_enfermeria }}</span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="info-label">Derivación:</span>
                        <span class="info-value"><strong>{{ $ingreso->derivacion_estudiante }}</strong></span>
                    </div>
                    @if($ingreso->seguimiento)
                    <div class="info-item">
                        <span class="info-label">Seguimiento:</span>
                        <span class="info-value">{{ $ingreso->seguimiento }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información del Registro -->
            <div class="info-section">
                <h3>📋 Información del Registro</h3>
                <div class="info-box">
                    <div class="info-item">
                        <span class="info-label">Registrado por:</span>
                        <span class="info-value">{{ $ingreso->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha de Registro:</span>
                        <span class="info-value">{{ $ingreso->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <p style="margin-top: 25px; color: #6c757d; font-size: 14px;">
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
