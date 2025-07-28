<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión de Retroalimentación - The Victoria School</title>
    
    <!-- Microdatos estructurados para Gmail Events -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "Event",
        "name": "Sesión de Retroalimentación - {{ $employee->name }}",
        "startDate": "{{ $feedbackSession->scheduled_datetime->toISOString() }}",
        "endDate": "{{ $feedbackSession->scheduled_datetime->copy()->addMinutes(60)->toISOString() }}",
        "location": {
            "@type": "Place",
            "name": "{{ $feedbackSession->location ?: 'Por definir' }}"
        },
        "description": "Sesión de retroalimentación programada",
        "organizer": {
            "@type": "Person",
            "name": "{{ $supervisor->name }}",
            "email": "{{ $supervisor->email }}"
        },
        "attendee": [
            {
                "@type": "Person",
                "name": "{{ $employee->name }}",
                "email": "{{ $employee->email }}"
            },
            {
                "@type": "Person",
                "name": "{{ $supervisor->name }}",
                "email": "{{ $supervisor->email }}"
            }
        ]
    }
    </script>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #233E6C;
            margin: 0;
            padding: 0;
            background-color: #FAFAFA;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(35, 62, 108, 0.1);
            overflow: hidden;
        }
        .email-header {
            background-color: #233E6C;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 25px;
            color: #233E6C;
        }
        .message-content {
            font-size: 15px;
            line-height: 1.6;
            color: #4a5568;
            margin-bottom: 30px;
        }
        .event-details {
            background-color: #f8fafc;
            border-left: 4px solid #233E6C;
            padding: 25px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .event-details h3 {
            margin: 0 0 20px 0;
            color: #233E6C;
            font-size: 18px;
            font-weight: 600;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #233E6C;
            flex-shrink: 0;
            width: 120px;
        }
        .detail-value {
            color: #4a5568;
            text-align: right;
            flex-grow: 1;
        }
        .instructions {
            background-color: #eff6ff;
            padding: 20px;
            border-radius: 6px;
            margin: 25px 0;
            border: 1px solid #bfdbfe;
        }
        .instructions h4 {
            margin: 0 0 10px 0;
            color: #233E6C;
            font-size: 16px;
        }
        .instructions p {
            margin: 0;
            color: #4a5568;
            font-size: 14px;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer-text {
            color: #718096;
            font-size: 13px;
            margin: 0;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 0;
            }
            .email-header, .email-body, .email-footer {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>📅 Sesión de Retroalimentación</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ $recipientType === 'employee' ? 'Programación de Sesión' : 'Confirmación de Programación' }} - The Victoria School</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <!-- Greeting -->
            <div class="greeting">
                Estimado/a <strong>{{ $recipientType === 'employee' ? $employee->name : $supervisor->name }}</strong>,
            </div>

            <!-- Message Content -->
            <div class="message-content">
                @if($recipientType === 'employee')
                    <p>Se ha programado una sesión de retroalimentación con su jefe inmediato como parte del proceso de evaluación de desempeño. Esta reunión es una oportunidad valiosa para revisar sus logros, identificar áreas de crecimiento y establecer objetivos para el futuro.</p>
                @else
                    <p>Esta es la confirmación de la sesión de retroalimentación que ha programado para el colaborador <strong>{{ $employee->name }}</strong>. Gracias por invertir tiempo en el desarrollo de su equipo.</p>
                @endif
            </div>

            <!-- Event Details -->
            <div class="event-details">
                <h3>📅 Evento Programado</h3>
                
                <div class="detail-row">
                    <div class="detail-label">Título:</div>
                    <div class="detail-value">Sesión de Retroalimentación - {{ $employee->name }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Fecha:</div>
                    <div class="detail-value">{{ $feedbackSession->scheduled_datetime->locale('es')->isoFormat('dddd, MMMM D, YYYY') }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Hora:</div>
                    <div class="detail-value">{{ $feedbackSession->scheduled_datetime->format('g:i A') }} - {{ $feedbackSession->scheduled_datetime->copy()->addMinutes(60)->format('g:i A') }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Ubicación:</div>
                    <div class="detail-value">{{ $feedbackSession->location ?: 'Por definir' }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">{{ $recipientType === 'employee' ? 'Jefe Inmediato:' : 'Colaborador:' }}</div>
                    <div class="detail-value">{{ $recipientType === 'employee' ? $supervisor->name : $employee->name }} ({{ $recipientType === 'employee' ? $supervisor->email : $employee->email }})</div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h4>📎 Archivo de Calendario</h4>
                <p>Se ha adjuntado un archivo de calendario (.ics) que puede agregar directamente a su calendario personal para recibir recordatorios automáticos de esta importante reunión.</p>
            </div>

            @if($recipientType === 'employee')
            <!-- Preparation Tips -->
            <div class="instructions">
                <h4>💡 Preparación para la Sesión</h4>
                <p>Se recomienda revisar su autoevaluación y los objetivos establecidos anteriormente. Esta sesión es una oportunidad para el diálogo constructivo sobre su desarrollo profesional.</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p class="footer-text">
                Sistema de Evaluación de Desempeño<br>
                The Victoria School - Desarrollo del Talento Humano
            </p>
        </div>
    </div>
</body>
</html>
