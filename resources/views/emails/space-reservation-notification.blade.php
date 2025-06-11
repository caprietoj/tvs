<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Reserva de Espacio</title>
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
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .info-value {
            margin-left: 10px;
        }
        .items-section {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
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
        <h1>Nueva Reserva de Espacio</h1>
        <p>Sistema de Reservas - Tecnológico de la Vanguardia Salesiana</p>
    </div>

    <div class="content">
        <p>Se ha realizado una nueva reserva de espacio en el sistema. A continuación se detallan los datos:</p>

        <div class="info-section">
            <div><span class="info-label">Usuario:</span><span class="info-value">{{ $reservation->user->name }}</span></div>
            <div><span class="info-label">Email:</span><span class="info-value">{{ $reservation->user->email }}</span></div>
        </div>

        <div class="info-section">
            <div><span class="info-label">Espacio:</span><span class="info-value">{{ $reservation->space->name }}</span></div>
            <div><span class="info-label">Ubicación:</span><span class="info-value">{{ $reservation->space->location }}</span></div>
        </div>

        <div class="info-section">
            <div><span class="info-label">Fecha:</span><span class="info-value">{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</span></div>
            <div><span class="info-label">Hora de inicio:</span><span class="info-value">{{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }}</span></div>
            <div><span class="info-label">Hora de fin:</span><span class="info-value">{{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}</span></div>
        </div>

        <div class="info-section">
            <div><span class="info-label">Propósito:</span><span class="info-value">{{ $reservation->purpose }}</span></div>
            <div><span class="info-label">Estado:</span><span class="info-value">{{ ucfirst($reservation->status) }}</span></div>
            <div><span class="info-label">Requiere bibliotecario:</span><span class="info-value">{{ $reservation->requires_librarian ? 'Sí' : 'No' }}</span></div>
        </div>        @if($reservation->items && $reservation->items->count() > 0)
        <div class="items-section">
            <h3>Implementos solicitados:</h3>
            <ul>
                @foreach($reservation->items as $item)
                <li>
                    <strong>{{ $item->item->name }}</strong> - 
                    Cantidad: {{ $item->quantity }}
                    @if($item->item->description)
                    <br><small>{{ $item->item->description }}</small>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="info-section">
            <div><span class="info-label">Fecha de creación:</span><span class="info-value">{{ $reservation->created_at->format('d/m/Y H:i') }}</span></div>
        </div>
    </div>

    <div class="footer">
        <p>Este es un correo automático del Sistema de Reservas de Espacios</p>
        <p>Tecnológico de la Vanguardia Salesiana</p>
    </div>
</body>
</html>
