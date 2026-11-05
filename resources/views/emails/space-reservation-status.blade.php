<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Reserva de Espacio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            background-color: {{ $reservation->status === 'approved' ? '#28a745' : '#dc3545' }};
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .info-value {
            margin-left: 10px;
        }
        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Actualización de Reserva de Espacio</h1>
        <p>Sistema de Reservas - The Victoria School</p>
    </div>

    <div class="content">
        <p>La reserva del espacio <strong>{{ $reservation->space->name }}</strong> cambió de estado a:</p>
        <p><span class="status">{{ $statusText }}</span></p>

        <div>
            <div><span class="info-label">Usuario:</span><span class="info-value">{{ $reservation->user->name }}</span></div>
            <div><span class="info-label">Email:</span><span class="info-value">{{ $reservation->user->email }}</span></div>
        </div>

        <div style="margin-top: 15px;">
            <div><span class="info-label">Fecha:</span><span class="info-value">{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</span></div>
            <div><span class="info-label">Hora de inicio:</span><span class="info-value">{{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }}</span></div>
            <div><span class="info-label">Hora de fin:</span><span class="info-value">{{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</span></div>
            <div><span class="info-label">Propósito:</span><span class="info-value">{{ $reservation->purpose }}</span></div>
        </div>

        @if($reservation->comments)
        <div style="margin-top: 15px;">
            <div><span class="info-label">Comentarios:</span><span class="info-value">{{ $reservation->comments }}</span></div>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>Este es un correo automático del Sistema de Reservas de Espacios</p>
        <p>The Victoria School</p>
    </div>
</body>
</html>
