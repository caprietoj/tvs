<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #364E76;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            position: relative;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .logo {
            width: 120px;
            margin-bottom: 15px;
        }
        .content {
            padding: 30px;
        }
        .section {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #364E76;
        }
        .section-title {
            color: #364E76;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 10px;
        }
        .info-row {
            margin-bottom: 12px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            display: flex;
        }
        .info-label {
            font-weight: bold;
            color: #364E76;
            width: 140px;
            flex-shrink: 0;
        }
        .info-value {
            flex-grow: 1;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #364E76;
            color: #FFFFFF !important;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #2B3E5F;
            color: #FFFFFF !important;
            text-decoration: none;
        }
        .area-specific {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            background-color: #e9ecef;
            color: #364E76;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- <img src="" alt="Logo" class="logo"> -->
            <h2>Nueva Salida Pedagógica</h2>
            <p>{{ $salida->consecutivo }} - {{ $salida->grados }}</p>
            <div class="status-badge">Estado: {{ $salida->estado }}</div>
        </div>

        <div class="content">
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> Información General
                </div>
                <div class="info-row">
                    <span class="info-label">Grados:</span>
                    <span class="info-value">{{ $salida->grados }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lugar:</span>
                    <span class="info-value">{{ $salida->lugar }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Responsable:</span>
                    <span class="info-value">{{ $salida->responsable->name }}</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">
                    <i class="fas fa-clock"></i> Fechas y Horarios
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Salida:</span>
                    <span class="info-value">{{ $salida->fecha_salida->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Regreso:</span>
                    <span class="info-value">{{ $salida->fecha_regreso->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Participantes:</span>
                    <span class="info-value">{{ $salida->cantidad_pasajeros }} personas</span>
                </div>
            </div>

            @if($tipoDestinatario === 'transporte')
            <div class="area-specific">
                <div class="section-title">
                    <i class="fas fa-bus"></i> Detalles de Transporte
                </div>
                <div class="info-row">
                    <span class="info-label">Hora Salida Bus:</span>
                    <span class="info-value">{{ $salida->hora_salida_bus }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Hora Regreso Bus:</span>
                    <span class="info-value">{{ $salida->hora_regreso_bus }}</span>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('salidas.confirmar-area', ['id' => $salida->id, 'area' => 'transporte', 'token' => $token]) }}" 
                       class="button">
                        Confirmar Participación
                    </a>
                </div>
            </div>
            @endif

            @if($tipoDestinatario === 'alimentacion' && $salida->requiere_alimentacion)
            <div class="area-specific">
                <div class="section-title">
                    <i class="fas fa-utensils"></i> Detalles de Alimentación
                </div>
                <div class="info-row">
                    <span class="info-label">Cantidad Snacks:</span>
                    <span class="info-value">{{ $salida->cantidad_snacks }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cantidad Almuerzos:</span>
                    <span class="info-value">{{ $salida->cantidad_almuerzos }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Menú Sugerido:</span>
                    <span class="info-value">{{ $salida->menu_sugerido }}</span>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('salidas.confirmar-area', ['id' => $salida->id, 'area' => 'alimentacion', 'token' => $token]) }}" 
                       class="button">
                        Confirmar Participación
                    </a>
                </div>
            </div>
            @endif

            @if($tipoDestinatario === 'enfermeria')
            <div class="area-specific">
                <div class="section-title">
                    <i class="fas fa-heartbeat"></i> Detalles de Enfermería
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('salidas.confirmar-area', ['id' => $salida->id, 'area' => 'enfermeria', 'token' => $token]) }}" 
                       class="button">
                        Confirmar Participación
                    </a>
                </div>
            </div>
            @endif

            @if($tipoDestinatario === 'comunicaciones')
            <div class="area-specific">
                <div class="section-title">
                    <i class="fas fa-bullhorn"></i> Detalles de Comunicaciones
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('salidas.confirmar-area', ['id' => $salida->id, 'area' => 'comunicaciones', 'token' => $token]) }}" 
                       class="button">
                        Confirmar Participación
                    </a>
                </div>
            </div>
            @endif

            @if($salida->observaciones)
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-clipboard-list"></i> Observaciones
                </div>
                <p>{{ $salida->observaciones }}</p>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>Este es un correo automático del sistema de Salidas Pedagógicas</p>
            <p>The Victoria School &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
