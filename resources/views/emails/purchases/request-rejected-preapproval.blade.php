@component('mail::message')
# Solicitud Rechazada desde Pre-aprobación

Estimado(a) usuario,

Lamentamos informarle que la solicitud de compra **#{{ $request->request_number }}** ha sido **rechazada** durante el proceso de pre-aprobación.

## Detalles de la Solicitud

**Número de Solicitud:** #{{ $request->request_number }}  
**Título:** {{ $request->title }}  
**Solicitante:** {{ $request->user->name ?? 'No especificado' }}  
**Sección/Área:** {{ $request->section_area }}  
**Fecha de Solicitud:** {{ $request->created_at->format('d/m/Y H:i') }}

## Motivo del Rechazo

{{ $reason }}

---

**Rechazada por:** {{ $rejectedBy }}  
**Fecha de Rechazo:** {{ now()->format('d/m/Y H:i') }}

@component('mail::button', ['url' => $url])
Ver Detalles de la Solicitud
@endcomponent

## ¿Qué puede hacer ahora?

1. **Revisar el motivo del rechazo** detalladamente
2. **Corregir los aspectos señalados** en el motivo del rechazo
3. **Crear una nueva solicitud** con las correcciones necesarias
4. **Contactar al área de compras** si tiene dudas sobre el proceso

Si considera que el rechazo no es procedente o tiene dudas sobre los motivos, puede contactar directamente al área de compras para obtener más información.

Gracias por su comprensión.

**Departamento de Compras**  
TVS - Colegio Técnico Vicente Azuero
@endcomponent