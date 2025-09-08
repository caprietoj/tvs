@extends('adminlte::page')

@section('title', 'Editar Bloqueo de Equipo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>🔒 Editar Bloqueo de Equipo</h1>
        <div>
            <a href="{{ route('equipment.blocks.show', $equipmentBlock) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Ver
            </a>
            <a href="{{ route('equipment.blocks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                @if($equipmentBlock->is_weekday_block)
                    📅 Editar Bloqueo Semanal
                @else
                    🗓️ Editar Bloqueo por Día de Ciclo
                @endif
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('equipment.blocks.update', $equipmentBlock) }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="equipment_id">Equipo *</label>
                    <select name="equipment_id" id="equipment_id" class="form-control @error('equipment_id') is-invalid @enderror" required>
                        @foreach($equipments as $equipment)
                            <option value="{{ $equipment->id }}" {{ old('equipment_id', $equipmentBlock->equipment_id) == $equipment->id ? 'selected' : '' }}>
                                {{ $equipment->type }} - {{ $equipment->section }} ({{ $equipment->total_units }} unidades)
                            </option>
                        @endforeach
                    </select>
                    @error('equipment_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                @if(!$equipmentBlock->is_weekday_block)
                    <div class="form-group">
                        <label for="school_cycle_id">Ciclo Escolar *</label>
                        <select name="school_cycle_id" id="school_cycle_id" class="form-control @error('school_cycle_id') is-invalid @enderror" required>
                            @foreach($schoolCycles as $cycle)
                                <option value="{{ $cycle->id }}" {{ old('school_cycle_id', $equipmentBlock->school_cycle_id) == $cycle->id ? 'selected' : '' }}>
                                    {{ $cycle->name }} @if($cycle->active) (Activo) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('school_cycle_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="cycle_day">Día de Ciclo *</label>
                        <input type="number" name="cycle_day" id="cycle_day" 
                               class="form-control @error('cycle_day') is-invalid @enderror" 
                               value="{{ old('cycle_day', $equipmentBlock->cycle_day) }}" 
                               min="1" max="30" required>
                        @error('cycle_day')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                @else
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
                                           id="{{ $day }}" {{ old($day, $equipmentBlock->$day) ? 'checked' : '' }}>
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
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_time">Hora de Inicio *</label>
                            <input type="time" name="start_time" id="start_time" 
                                   class="form-control @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', \Carbon\Carbon::parse($equipmentBlock->start_time)->format('H:i')) }}" required>
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
                                   value="{{ old('end_time', \Carbon\Carbon::parse($equipmentBlock->end_time)->format('H:i')) }}" required>
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
                           value="{{ old('blocked_units', $equipmentBlock->blocked_units) }}" 
                           min="1" max="{{ $equipmentBlock->equipment->total_units }}" required>
                    <small class="text-muted">Máximo: {{ $equipmentBlock->equipment->total_units }} unidades</small>
                    @error('blocked_units')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reason">Razón del Bloqueo</label>
                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" 
                              rows="3" placeholder="Opcional: Especifique la razón del bloqueo">{{ old('reason', $equipmentBlock->reason) }}</textarea>
                    @error('reason')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Bloqueo
                    </button>
                    <a href="{{ route('equipment.blocks.show', $equipmentBlock) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Actualizar límite de unidades cuando se selecciona un equipo
    $('#equipment_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const equipmentText = selectedOption.text();
        const unitsMatch = equipmentText.match(/\((\d+) unidades\)/);
        
        if (unitsMatch) {
            const maxUnits = parseInt(unitsMatch[1]);
            $('#blocked_units').attr('max', maxUnits);
            $('#blocked_units').parent().find('.text-muted').text('Máximo: ' + maxUnits + ' unidades');
        }
    });
});
</script>
@stop
