@extends('adminlte::page')

@section('title', 'Crear Bloqueo de Equipo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>🔒 Crear Bloqueo de Equipo</h1>
        <a href="{{ route('equipment.blocks.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🗓️ Bloqueo por Días de Ciclo</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('equipment.blocks.store') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="equipment_id">Equipo *</label>
                            <select name="equipment_id" id="equipment_id" class="form-control @error('equipment_id') is-invalid @enderror" required>
                                <option value="">Seleccione un equipo</option>
                                @foreach($equipments as $equipment)
                                    <option value="{{ $equipment->id }}" {{ old('equipment_id') == $equipment->id ? 'selected' : '' }}>
                                        {{ $equipment->type }} - {{ $equipment->section }} ({{ $equipment->total_units }} unidades)
                                    </option>
                                @endforeach
                            </select>
                            @error('equipment_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="school_cycle_id">Ciclo Escolar *</label>
                            <select name="school_cycle_id" id="school_cycle_id" class="form-control @error('school_cycle_id') is-invalid @enderror" required>
                                @foreach($schoolCycles as $cycle)
                                    <option value="{{ $cycle->id }}" {{ (old('school_cycle_id', $activeSchoolCycle?->id) == $cycle->id) ? 'selected' : '' }}>
                                        {{ $cycle->name }} @if($cycle->active) (Activo) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('school_cycle_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Días de Ciclo a Bloquear *</label>
                            <div id="cycle-days-container" class="border p-3 rounded">
                                <p class="text-muted">Seleccione un ciclo escolar para ver los días disponibles</p>
                            </div>
                            @error('cycle_days')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">Hora de Inicio *</label>
                                    <input type="time" name="start_time" id="start_time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time', '08:00') }}" required>
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">Hora de Fin *</label>
                                    <input type="time" name="end_time" id="end_time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time', '17:00') }}" required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="blocked_units">Unidades a Bloquear *</label>
                            <input type="number" name="blocked_units" id="blocked_units" 
                                   class="form-control @error('blocked_units') is-invalid @enderror" 
                                   value="{{ old('blocked_units', 1) }}" min="1" required>
                            @error('blocked_units')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reason">Razón del Bloqueo</label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" 
                                      rows="3" placeholder="Opcional: Especifique la razón del bloqueo">{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Bloqueo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📅 Bloqueo Semanal</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('equipment.blocks.store-weekly') }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="equipment_id_weekly">Equipo *</label>
                            <select name="equipment_id" id="equipment_id_weekly" class="form-control @error('equipment_id') is-invalid @enderror" required>
                                <option value="">Seleccione un equipo</option>
                                @foreach($equipments as $equipment)
                                    <option value="{{ $equipment->id }}" {{ old('equipment_id') == $equipment->id ? 'selected' : '' }}>
                                        {{ $equipment->type }} - {{ $equipment->section }} ({{ $equipment->total_units }} unidades)
                                    </option>
                                @endforeach
                            </select>
                            @error('equipment_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Días de la Semana *</label>
                            <div class="border p-3 rounded">
                                @php
                                    $weekdays = [
                                        'monday' => 'Lunes',
                                        'tuesday' => 'Martes',
                                        'wednesday' => 'Miércoles',
                                        'thursday' => 'Jueves',
                                        'friday' => 'Viernes',
                                        'saturday' => 'Sábado',
                                        'sunday' => 'Domingo'
                                    ];
                                @endphp
                                @foreach($weekdays as $day => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="{{ $day }}" 
                                               id="{{ $day }}" {{ old($day) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $day }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('weekdays')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time_weekly">Hora de Inicio *</label>
                                    <input type="time" name="start_time" id="start_time_weekly" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time', '08:00') }}" required>
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time_weekly">Hora de Fin *</label>
                                    <input type="time" name="end_time" id="end_time_weekly" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time', '17:00') }}" required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="blocked_units_weekly">Unidades a Bloquear *</label>
                            <input type="number" name="blocked_units" id="blocked_units_weekly" 
                                   class="form-control @error('blocked_units') is-invalid @enderror" 
                                   value="{{ old('blocked_units', 1) }}" min="1" required>
                            @error('blocked_units')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reason_weekly">Razón del Bloqueo</label>
                            <textarea name="reason" id="reason_weekly" class="form-control @error('reason') is-invalid @enderror" 
                                      rows="3" placeholder="Opcional: Especifique la razón del bloqueo">{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Crear Bloqueo Semanal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Manejar cambio de ciclo escolar para mostrar días disponibles
    $('#school_cycle_id').on('change', function() {
        const cycleId = $(this).val();
        const container = $('#cycle-days-container');
        
        if (!cycleId) {
            container.html('<p class="text-muted">Seleccione un ciclo escolar para ver los días disponibles</p>');
            return;
        }
        
        // Mostrar loading
        container.html('<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando días de ciclo...</p>');
        
        // Obtener los días de ciclo del ciclo seleccionado vía AJAX
        $.get('{{ route("equipment.blocks.cycle-days") }}', {
            school_cycle_id: cycleId
        })
        .done(function(response) {
            if (response.cycle_days && response.cycle_days.length > 0) {
                let html = '<div class="row">';
                
                response.cycle_days.forEach(function(day, index) {
                    html += `
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cycle_days[]" value="${day}" id="cycle_day_${day}">
                                <label class="form-check-label" for="cycle_day_${day}">
                                    Día ${day}
                                </label>
                            </div>
                        </div>
                    `;
                    if ((index + 1) % 4 === 0) {
                        html += '</div><div class="row">';
                    }
                });
                html += '</div>';
                
                container.html(html);
            } else {
                container.html('<p class="text-warning">No hay días de ciclo configurados para este ciclo escolar</p>');
            }
        })
        .fail(function() {
            container.html('<p class="text-danger">Error al cargar los días de ciclo</p>');
        });
    });

    // Trigger inicial si ya hay un ciclo seleccionado
    if ($('#school_cycle_id').val()) {
        $('#school_cycle_id').trigger('change');
    }

    // Actualizar límite de unidades cuando se selecciona un equipo
    $('#equipment_id, #equipment_id_weekly').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const equipmentText = selectedOption.text();
        const unitsMatch = equipmentText.match(/\((\d+) unidades\)/);
        
        if (unitsMatch) {
            const maxUnits = parseInt(unitsMatch[1]);
            const blockedUnitsInput = $(this).attr('id') === 'equipment_id' ? '#blocked_units' : '#blocked_units_weekly';
            $(blockedUnitsInput).attr('max', maxUnits);
            
            // Mostrar información de unidades disponibles
            const info = `<small class="text-muted">Máximo: ${maxUnits} unidades</small>`;
            $(blockedUnitsInput).parent().find('.text-muted').remove();
            $(blockedUnitsInput).after(info);
        }
    });
});
</script>
@stop
