@component('mail::message')
# Nueva Solicitud de Préstamo de Equipo

Se ha recibido una nueva solicitud de préstamo con los siguientes detalles:

<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Solicitante:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $loan->user->name }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Sección:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ ucfirst($loan->section) }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Grado:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $loan->grade }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Equipo:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ ucfirst($loan->equipment->type) }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Cantidad:</strong></td>
        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $loan->units_requested }} unidad(es)</td>
    </tr>
</table>
</div>

## ⏰ Fecha y Horario

<div style="background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>Fecha:</strong> {{ $loan->loan_date->isoFormat('dddd D [de] MMMM [de] YYYY') }}<br>
    <strong>Horario:</strong> {{ \Carbon\Carbon::parse($loan->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($loan->end_time)->format('H:i') }}
</div>

@php
$activeLoans = \App\Models\EquipmentLoan::where('equipment_id', $loan->equipment_id)
    ->where('loan_date', $loan->loan_date)
    ->where('status', '!=', 'returned')
    ->where('id', '!=', $loan->id)
    ->sum('units_requested');

$availableUnits = $loan->equipment->available_units - $activeLoans;
@endphp

{{-- @if($availableUnits < $loan->units_requested)
<div style="background-color: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; margin-bottom: 20px;">
    <strong>⚠️ Importante:</strong> Esta solicitud requiere revisión especial ya que la cantidad solicitada ({{ $loan->units_requested }}) supera las unidades actualmente disponibles ({{ $availableUnits }}).
</div>
@endif --}}

@php
$otherLoans = \App\Models\EquipmentLoan::where('equipment_id', $loan->equipment_id)
    ->where('loan_date', $loan->loan_date)
    ->where('id', '!=', $loan->id)
    ->where('status', '!=', 'returned')
    ->get();
@endphp

@if($otherLoans->count() > 0)
## 📅 Otros Préstamos para la Misma Fecha

<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    @foreach($otherLoans as $otherLoan)
    - {{ $otherLoan->units_requested }} unidad(es) / {{ Carbon\Carbon::parse($otherLoan->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($otherLoan->end_time)->format('H:i') }}<br>
    @endforeach
</div>
@endif

@component('mail::button', ['url' => route('equipment.loans')])
Ver Solicitud
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
