<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión de Retroalimentación Programada - The Victoria School</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #233E6C;
            margin: 0;
            padding: 0;
            background-color: #FAFAFA;
        }
        .email-wrapper {
            width: 100%;
            background-color: #FAFAFA;
            padding: 20px 0;
        }
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(35, 62, 108, 0.15);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #233E6C 0%, #1a2d4d 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        .email-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }
        .email-header .subtitle {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #233E6C;
            margin-bottom: 25px;
            font-weight: 500;
        }
        .intro-text {
            font-size: 16px;
            color: #233E6C;
            margin-bottom: 30px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1>📅 Sesión de Retroalimentación</h1>
                <p class="subtitle">
                    @if($recipientType === 'employee')
                        Evaluación de Desempeño - The Victoria School
                    @else
                        Confirmación de Programación - The Victoria School
                    @endif
                </p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <div class="greeting">
                    @if($recipientType === 'employee')
                        Estimado/a <strong>{{ $employee->name }}</strong>,
                    @else
                        Estimado/a <strong>{{ $supervisor->name }}</strong>,
                    @endif
                </div>

                <div class="intro-text">
                    @if($recipientType === 'employee')
                        Se ha programado una <strong>sesión de retroalimentación</strong> con su supervisor como parte del proceso de evaluación de desempeño. Esta es una oportunidad valiosa para revisar sus logros y planificar su desarrollo profesional.
                    @else
                        Esta es la confirmación de la sesión de retroalimentación que ha programado para el colaborador <strong>{{ $employee->name }}</strong>. Gracias por invertir tiempo en el desarrollo de su equipo.
                    @endif
                </div>

                <!-- Detalles de la Sesión -->
                <div style="background: linear-gradient(135deg, #233E6C 0%, #1a2d4d 100%); color: #ffffff; padding: 30px; border-radius: 12px; margin: 30px 0; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.3;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; opacity: 0.3;"></div>
                    
                    <h3 style="color: #ffffff; margin: 0 0 25px 0; font-size: 22px; font-weight: 500; position: relative; z-index: 1;">
                        📅 Detalles de la Sesión
                    </h3>
                    
                    <table style="width: 100%; border-collapse: collapse; position: relative; z-index: 1;">
                        <tr>
                            <td style="padding: 12px 0; font-weight: 600; color: rgba(255,255,255,0.9); width: 150px; font-size: 15px;">📅 Fecha:</td>
                            <td style="padding: 12px 0; color: #ffffff; font-size: 16px; font-weight: 500;">{{ $scheduledDate }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0; font-weight: 600; color: rgba(255,255,255,0.9); font-size: 15px;">🕐 Hora:</td>
                            <td style="padding: 12px 0; color: #ffffff; font-size: 16px; font-weight: 500;">{{ $scheduledTime }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0; font-weight: 600; color: rgba(255,255,255,0.9); font-size: 15px;">👤 Colaborador:</td>
                            <td style="padding: 12px 0; color: #ffffff; font-size: 16px; font-weight: 500;">{{ $employee->name }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0; font-weight: 600; color: rgba(255,255,255,0.9); font-size: 15px;">👨‍💼 Supervisor:</td>
                            <td style="padding: 12px 0; color: #ffffff; font-size: 16px; font-weight: 500;">{{ $supervisor->name }}</td>
                        </tr>
                        @if($feedbackSession->location)
                        <tr>
                            <td style="padding: 12px 0; font-weight: 600; color: rgba(255,255,255,0.9); font-size: 15px;">📍 Ubicación:</td>
                            <td style="padding: 12px 0; color: #ffffff; font-size: 16px; font-weight: 500;">{{ $feedbackSession->location }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 12px 0; font-weight: 600; color: rgba(255,255,255,0.9); font-size: 15px;">📊 Período:</td>
                            <td style="padding: 12px 0; color: #ffffff; font-size: 16px; font-weight: 500;">{{ $evaluationPeriod }}</td>
                        </tr>
                    </table>
                </div>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #555;">Período de Evaluación:</td>
            <td style="padding: 8px 0; color: #333;">{{ $evaluationPeriod }}</td>
        </tr>
    </table>
</div>

@if($feedbackSession->description)
<div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;">
    <h4 style="color: #856404; margin: 0 0 10px 0;">📝 Descripción:</h4>
    <p style="margin: 0; color: #856404;">{{ $feedbackSession->description }}</p>
</div>
@endif

@if($recipientType === 'employee')
<div style="background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h3 style="color: #2e7d32; margin: 0 0 15px 0;">🎯 Propósito de la Sesión</h3>
    <p style="margin: 0 0 10px 0; color: #2e7d32;">Esta sesión de retroalimentación tiene como objetivo:</p>
    <ul style="margin: 0; padding-left: 20px; color: #2e7d32;">
        <li>Revisar los resultados de su evaluación de desempeño</li>
        <li>Discutir fortalezas y áreas de mejora identificadas</li>
        <li>Establecer metas y planes de desarrollo para el próximo período</li>
        <li>Brindar orientación y apoyo para su crecimiento profesional</li>
    </ul>
</div>

<div style="background-color: #fff8e1; padding: 15px; border-radius: 8px; border-left: 4px solid #ff9800; margin: 20px 0;">
    <h4 style="color: #e65100; margin: 0 0 10px 0;">💡 Preparación Recomendada</h4>
    <p style="margin: 0; color: #e65100;">Le sugerimos revisar su autoevaluación y reflexionar sobre sus logros y desafíos durante el período evaluado.</p>
</div>
@else
<div style="background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h3 style="color: #2e7d32; margin: 0 0 15px 0;">📋 Recordatorio para el Supervisor</h3>
    <p style="margin: 0 0 10px 0; color: #2e7d32;">Para una sesión efectiva, considere:</p>
    <ul style="margin: 0; padding-left: 20px; color: #2e7d32;">
        <li>Revisar la evaluación completa del colaborador</li>
        <li>Preparar ejemplos específicos de comportamientos observados</li>
        <li>Definir objetivos claros para el próximo período</li>
        <li>Planificar estrategias de desarrollo y crecimiento</li>
    </ul>
</div>
@endif

<div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196f3; margin: 20px 0;">
    <h3 style="color: #1976d2; margin: 0 0 15px 0;">📅 Agregar al Calendario</h3>
    <p style="margin: 0 0 10px 0; color: #1976d2;">
        <strong>📎 Archivo Adjunto:</strong> Se incluye un archivo de calendario (.ics) que puede usar para agregar automáticamente esta cita a su calendario personal.
    </p>
    <div style="background-color: #ffffff; padding: 10px; border-radius: 4px; margin: 10px 0;">
        <p style="margin: 0; color: #333; font-size: 14px;">
            <strong>Para Gmail/Google Calendar:</strong> Haga clic en el archivo adjunto y seleccione "Agregar a Google Calendar"<br>
            <strong>Para Outlook:</strong> Descargue el archivo y ábralo con Outlook<br>
            <strong>Para Apple Calendar:</strong> Haga clic en el archivo adjunto para importarlo
        </p>
    </div>
    <p style="margin: 0; color: #1976d2; font-size: 12px;">
        <em>El archivo incluye recordatorios automáticos 1 día antes y 1 hora antes del evento.</em>
    </p>
</div>

<div style="background-color: #ffebee; padding: 15px; border-radius: 8px; border-left: 4px solid #f44336; margin: 20px 0;">
    <h4 style="color: #c62828; margin: 0 0 10px 0;">⚠️ Importante</h4>
    <p style="margin: 0; color: #c62828;">
        Si necesita reprogramar esta sesión, por favor contacte 
        @if($recipientType === 'employee')
            a su supervisor <strong>{{ $supervisor->name }}</strong>
        @else
            al colaborador <strong>{{ $employee->name }}</strong>
        @endif
        con la mayor anticipación posible.
    </p>
</div>

@if($recipientType === 'employee')
<p>Esta sesión es una oportunidad valiosa para recibir retroalimentación constructiva y planificar su desarrollo profesional. ¡Esperamos que sea una experiencia enriquecedora!</p>
@else
<p>Gracias por invertir tiempo en el desarrollo de su equipo. Su liderazgo y orientación son fundamentales para el crecimiento de nuestros colaboradores.</p>
@endif

<div style="margin: 30px 0 20px 0; text-align: center;">">
    <div style="background-color: #2196f3; color: white; padding: 15px; border-radius: 8px; display: inline-block;">
        <strong>📅 {{ $scheduledDate }} a las {{ $scheduledTime }}</strong>
        @if($feedbackSession->location)
        <br><small>📍 {{ $feedbackSession->location }}</small>
        @endif
    </div>
</div>
@endsection

@section('footer')
<p style="margin: 0; color: #666; font-size: 12px;">
    Esta es una notificación automática del Sistema de Evaluación de Desempeño de The Victoria School.
    <br>Por favor no responda a este correo electrónico.
</p>
@endsection
