@component('mail::message')
# Nueva Evaluación de Desempeño Asignada

Estimado/a {{ $userName }},

Se ha generado una nueva **{{ $evaluationType }}** en la Intranet Institucional.

## Detalles de la Evaluación

**Período de Evaluación:** {{ $evaluationPeriodStart }} - {{ $evaluationPeriodEnd }}  
**Tipo:** {{ $evaluationType }}  
**Estado:** Pendiente de Autoevaluación  

### ¿Qué debes hacer?

1. **Realizar tu Autoevaluación**: Ingresa al sistema y completa tu autoevaluación de desempeño
2. **Plazo**: Tienes un tiempo determinado para completar este proceso
3. **Importancia**: Esta evaluación es parte importante de tu desarrollo profesional

@component('mail::button', ['url' => $evaluationUrl])
🚀 Realizar Autoevaluación
@endcomponent

### Información Adicional

- La evaluación incluye objetivos del cargo, competencias organizacionales y técnicas
- Puedes guardar tu progreso y continuar más tarde
- Una vez completada tu autoevaluación, tu supervisor realizará la evaluación correspondiente

@component('mail::panel')
**Recordatorio:** Es importante completar tu autoevaluación dentro del plazo establecido. Si tienes dudas sobre el proceso, contacta a tu supervisor o al área de Recursos Humanos.
@endcomponent

Si tienes alguna pregunta sobre la evaluación de desempeño, no dudes en contactar al equipo de Recursos Humanos.

Saludos cordiales,<br>
{{ config('app.name') }}<br>
*Sistema de Gestión de Recursos Humanos*
@endcomponent
