@extends('adminlte::page')

@section('title', 'Crear Bloqueo de Espacio')

@section('content_header')
    <h1>Crear Bloqueo de Espacio</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('space-blocks.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="space_id">Espacio <span class="text-danger">*</span></label>
                            <select class="form-control @error('space_id') is-invalid @enderror" id="space_id" name="space_id" required>
                                <option value="">Seleccione un espacio</option>
                                @foreach($spaces as $space)
                                    <option value="{{ $space->id }}" {{ old('space_id') == $space->id || (request()->has('space_id') && request('space_id') == $space->id) ? 'selected' : '' }}>
                                        {{ $space->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('space_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="school_cycle_id">Ciclo Escolar <span class="text-danger">*</span></label>
                            <select class="form-control @error('school_cycle_id') is-invalid @enderror" id="school_cycle_id" name="school_cycle_id" required>
                                <option value="">Seleccione un ciclo escolar</option>
                                @foreach($schoolCycles as $cycle)
                                    <option value="{{ $cycle->id }}" {{ old('school_cycle_id') == $cycle->id ? 'selected' : '' }}
                                        data-length="{{ $cycle->cycle_length }}">
                                        {{ $cycle->name }} {{ $cycle->active ? '(Activo)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_cycle_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reason">Motivo del bloqueo</label>
                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="2">{{ old('reason') }}</textarea>
                    @error('reason')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Horario de bloqueo <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_time">Hora de inicio</label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_time">Hora de fin</label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Seleccione los días del ciclo a bloquear <span class="text-danger">*</span></label>
                    
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle"></i> Los días seleccionados se bloquearán para todas las fechas que correspondan a ese día de ciclo en el horario especificado.</p>
                    </div>
                    
                    <div id="cycle-days-container" class="mt-3">
                        <div class="text-center">
                            <p>Seleccione un ciclo escolar para ver los días disponibles.</p>
                        </div>
                    </div>
                    
                    @error('cycle_days')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('space-blocks.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Cuando se cambia el ciclo escolar, generar los checkboxes para seleccionar los días
            $('#school_cycle_id').on('change', function() {
                let cycleId = $(this).val();
                if (!cycleId) {
                    $('#cycle-days-container').html('<div class="text-center"><p>Seleccione un ciclo escolar para ver los días disponibles.</p></div>');
                    return;
                }
                
                let cycleLength = $('option:selected', this).data('length');
                let html = '<div class="row">';
                
                for (let i = 1; i <= cycleLength; i++) {
                    html += `
                    <div class="col-md-2 col-sm-3 col-4 mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="cycle_day_${i}" name="cycle_days[]" value="${i}">
                            <label class="custom-control-label" for="cycle_day_${i}">Día ${i}</label>
                        </div>
                    </div>
                    `;
                }
                html += '</div>';
                
                $('#cycle-days-container').html(html);
                
                // Si hay un espacio seleccionado, verificar los bloqueos existentes
                let spaceId = $('#space_id').val();
                if (spaceId) {
                    checkExistingBlocks(spaceId, cycleId);
                }
            });
            
            // Cuando se cambia el espacio, verificar bloqueos existentes si hay un ciclo seleccionado
            $('#space_id').on('change', function() {
                let spaceId = $(this).val();
                let cycleId = $('#school_cycle_id').val();
                
                if (spaceId && cycleId) {
                    checkExistingBlocks(spaceId, cycleId);
                }
            });
            
            // Función para verificar bloqueos existentes
            function checkExistingBlocks(spaceId, cycleId) {
                $.ajax({
                    url: `/space-blocks/space/${spaceId}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.cycle_id == cycleId) {
                            // Marcar los checkboxes de los días ya bloqueados
                            response.blocked_days.forEach(function(day) {
                                $(`#cycle_day_${day}`).prop('checked', true);
                            });
                        }
                    }
                });
            }
            
            // Ejecutar el cambio inicial si ya hay un ciclo seleccionado
            if ($('#school_cycle_id').val()) {
                $('#school_cycle_id').trigger('change');
            }
            
            // Validación del formulario
            $('form').on('submit', function(e) {
                // Validar que al menos un día del ciclo esté seleccionado
                let cycleDaySelected = false;
                $('input[name="cycle_days[]"]').each(function() {
                    if ($(this).is(':checked')) {
                        cycleDaySelected = true;
                        return false; // Salir del bucle
                    }
                });
                
                if (!cycleDaySelected) {
                    e.preventDefault();
                    alert('Debe seleccionar al menos un día del ciclo.');
                    return false;
                }
                
                // Validar que la hora de fin sea posterior a la hora de inicio
                let startTime = $('#start_time').val();
                let endTime = $('#end_time').val();
                
                if (startTime && endTime && startTime >= endTime) {
                    e.preventDefault();
                    alert('La hora de fin debe ser posterior a la hora de inicio.');
                    return false;
                }
            });
        });
    </script>
@stop