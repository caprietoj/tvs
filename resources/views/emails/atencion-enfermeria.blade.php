<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Atención en Enfermería</title>
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
        
        .alert-box {
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .alert-box strong {
            color: #0c5460;
            font-size: 16px;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-section h3 {
            color: #314569;
            font-size: 16px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            padding: 10px 15px 10px 0;
            font-weight: 600;
            color: #314569;
            width: 40%;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            padding: 10px 0;
            color: #555;
            vertical-align: top;
        }
        
        .info-row:not(:last-child) .info-label,
        .info-row:not(:last-child) .info-value {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
        }
        
        .footer-logo {
            margin-top: 15px;
            font-weight: 600;
            color: #314569;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            
            .content {
                padding: 20px;
            }
            
            .info-label, .info-value {
                display: block;
                width: 100%;
                padding: 5px 0;
            }
            
            .info-label {
                font-weight: 700;
                padding-bottom: 2px;
            }
            
            .info-value {
                padding-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h2>🏥 Reporte de Atención en Enfermería</h2>
            <div class="header-subtitle">The Victoria School</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Alerta Principal -->
            <div class="alert-box">
                <strong>📋 Notificación de Atención</strong>
                <p style="margin: 8px 0 0 0;">Se ha registrado una atención en el servicio de enfermería.</p>
            </div>

            <!-- Información del Estudiante -->
            <div class="info-section">
                <h3>👤 Información del Estudiante</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Nombre Completo:</div>
                        <div class="info-value"><strong>{{ $ingreso->estudiante }}</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Código:</div>
                        <div class="info-value">{{ $ingreso->codigo_estudiante ?? 'No registrado' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Documento:</div>
                        <div class="info-value">{{ $ingreso->documento_estudiante ?? 'No registrado' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Curso:</div>
                        <div class="info-value"><span class="badge badge-info">{{ $ingreso->curso }}</span></div>
                    </div>
                    @if($ingreso->viene_de)
                    <div class="info-row">
                        <div class="info-label">Viene de:</div>
                        <div class="info-value"><span class="badge badge-info">{{ $ingreso->viene_de }}</span></div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Detalles de la Atención -->
            <div class="info-section">
                <h3>🩺 Detalles de la Atención</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Fecha:</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Hora:</div>
                        <div class="info-value">{{ $ingreso->hora }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Motivo de Consulta:</div>
                        <div class="info-value"><strong>{{ $ingreso->motivo }}</strong></div>
                    </div>
                    @if($ingreso->descripcion_evento)
                    <div class="info-row">
                        <div class="info-label">Descripción del Evento:</div>
                        <div class="info-value">{{ $ingreso->descripcion_evento }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información de Enfermería -->
            <div class="info-section">
                <h3>💊 Información de Enfermería</h3>
                <div class="info-grid">
                    @if($ingreso->temperatura)
                    <div class="info-row">
                        <div class="info-label">Temperatura:</div>
                        <div class="info-value">{{ $ingreso->temperatura }}°C</div>
                    </div>
                    @endif
                    @if($ingreso->presion_arterial)
                    <div class="info-row">
                        <div class="info-label">Presión Arterial:</div>
                        <div class="info-value">{{ $ingreso->presion_arterial }}</div>
                    </div>
                    @endif
                    @if($ingreso->frecuencia_cardiaca)
                    <div class="info-row">
                        <div class="info-label">Frecuencia Cardíaca:</div>
                        <div class="info-value">{{ $ingreso->frecuencia_cardiaca }} lpm</div>
                    </div>
                    @endif
                    @if($ingreso->saturacion_oxigeno)
                    <div class="info-row">
                        <div class="info-label">Saturación de Oxígeno:</div>
                        <div class="info-value">{{ $ingreso->saturacion_oxigeno }}%</div>
                    </div>
                    @endif
                    @if($ingreso->procedimiento_realizado)
                    <div class="info-row">
                        <div class="info-label">Procedimiento Realizado:</div>
                        <div class="info-value">{{ $ingreso->procedimiento_realizado }}</div>
                    </div>
                    @endif
                    @if($ingreso->medicamento_suministrado)
                    <div class="info-row">
                        <div class="info-label">Medicamento Suministrado:</div>
                        <div class="info-value"><span class="badge badge-warning">{{ $ingreso->medicamento_suministrado }}</span></div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Seguimiento y Derivación -->
            <div class="info-section">
                <h3>📝 Seguimiento y Derivación</h3>
                <div class="info-grid">
                    @if($ingreso->seguimiento)
                    <div class="info-row">
                        <div class="info-label">Seguimiento:</div>
                        <div class="info-value">{{ $ingreso->seguimiento }}</div>
                    </div>
                    @endif
                    @if($ingreso->derivacion_estudiante)
                    <div class="info-row">
                        <div class="info-label">Derivación:</div>
                        <div class="info-value"><span class="badge badge-danger">{{ $ingreso->derivacion_estudiante }}</span></div>
                    </div>
                    @endif
                    @if($ingreso->reporte_direccion_educacion)
                    <div class="info-row">
                        <div class="info-label">Reporte a Dirección:</div>
                        <div class="info-value">{{ $ingreso->reporte_direccion_educacion }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Información del Registro -->
            <div class="info-section">
                <h3>ℹ️ Información del Registro</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Registrado por:</div>
                        <div class="info-value">{{ $ingreso->user->name ?? 'Usuario no disponible' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha de Registro:</div>
                        <div class="info-value">{{ $ingreso->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">Este es un correo automático generado por el Sistema de Gestión de Enfermería.</p>
            <p style="margin: 5px 0 0 0;">Por favor, no responder a este correo.</p>
            <div class="footer-logo">The Victoria School</div>
        </div>
    </div>
</body>
</html>
