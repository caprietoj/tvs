@component('mail::message')
# Sala de informática cedida

Se ha cedido el espacio de una sala de informática a otro docente.

<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Sala:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $details['space_name'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Fecha:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $details['date'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Horario:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $details['start_time'] ?? '' }} - {{ $details['end_time'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Docente original:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $details['original_teacher'] ?? 'Sin asignar' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Cedida a:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $details['new_teacher'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding: 8px;"><strong>Registrado por:</strong></td>
        <td style="padding: 8px;">{{ $details['ceded_by'] ?? 'N/A' }}</td>
    </tr>
</table>
</div>

La sala quedó asignada al nuevo docente únicamente para la fecha indicada. El bloqueo recurrente no se modificó.

@component('mail::button', ['url' => route('equipment.blocks.index')])
Ver Bloqueos
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
