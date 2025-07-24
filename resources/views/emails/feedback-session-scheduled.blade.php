@extends('emails.layouts.main')

@section('title', 'Sesión de Retroalimentación Programada')

@section('content')
<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <h2 style="color: #2c3e50; margin: 0 0 15px 0;">
        <i class="fas fa-calendar-check" style="color: #28a745; margin-right: 10px;"></i>
        @if($recipientType === 'employee')
            Sesión de Retroalimentación Programada
        @else
            Confirmación: Sesión de Retroalimentación Programada
        @endif
    </h2>
</div>

@if($recipientType === 'employee')
<p>Estimado/a <strong>{{ $employee->name }}</strong>,</p>
<p>Se ha programado una <strong>sesión de retroalimentación</strong> con su supervisor como parte del proceso de evaluación de desempeño.</p>
@else
<p>Estimado/a <strong>{{ $supervisor->name }}</strong>,</p>
<p>Esta es la confirmación de la sesión de retroalimentación que ha programado para el colaborador <strong>{{ $employee->name }}</strong>.</p>
@endif

<div style="background-color: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196f3; margin: 20px 0;">
    <h3 style="color: #1976d2; margin: 0 0 15px 0;">📅 Detalles de la Sesión</h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #555; width: 140px;">Fecha:</td>
            <td style="padding: 8px 0; color: #333;">{{ $scheduledDate }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #555;">Hora:</td>
            <td style="padding: 8px 0; color: #333;">{{ $scheduledTime }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #555;">Colaborador:</td>
            <td style="padding: 8px 0; color: #333;">{{ $employee->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #555;">Supervisor:</td>
            <td style="padding: 8px 0; color: #333;">{{ $supervisor->name }}</td>
        </tr>
        @if($feedbackSession->location)
        <tr>
            <td style="padding: 8px 0; font-weight: bold; color: #555;">Ubicación:</td>
            <td style="padding: 8px 0; color: #333;">{{ $feedbackSession->location }}</td>
        </tr>
        @endif
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

<div style="margin: 30px 0 20px 0; text-align: center;">
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
