@extends('adminlte::page')

@section('title', 'Crear Bloqueo por Días de Semana')

@section('content_header')
    <h1>Crear Bloqueo por Días de la Semana</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('space-blocks.store-weekly') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="space_id">Espacio <span class="text-danger">*</span></label>
                            <select class="form-control @error('space_id') is-invalid @enderror" id="space_id" name="space_id" required>
                                <option value="">Seleccione un espacio</option>
                                @foreach($spaces as $space)
                                    <option value="{{ $space->id }}" {{ old('space_id') == $space->id ? 'selected' : '' }}>
                                        {{ $space->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('space_id')
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
                    <label>Seleccione los días de la semana para bloquear <span class="text-danger">*</span></label>
                    
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle"></i> Los días seleccionados se bloquearán en el horario especificado para cualquier fecha que corresponda a esos días de la semana.</p>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input weekday" id="monday" name="monday" value="1" {{ old('monday') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="monday">Lunes</label>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input weekday" id="tuesday" name="tuesday" value="1" {{ old('tuesday') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="tuesday">Martes</label>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input weekday" id="wednesday" name="wednesday" value="1" {{ old('wednesday') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="wednesday">Miércoles</label>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input weekday" id="thursday" name="thursday" value="1" {{ old('thursday') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="thursday">Jueves</label>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input weekday" id="friday" name="friday" value="1" {{ old('friday') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="friday">Viernes</label>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input weekday" id="saturday" name="saturday" value="1" {{ old('saturday') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="saturday">Sábado</label>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input weekday" id="sunday" name="sunday" value="1" {{ old('sunday') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sunday">Domingo</label>
                            </div>
                        </div>
                    </div>
                    
                    @error('weekdays')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-success">Guardar Bloqueo Semanal</button>
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
            // Validación de que al menos un día de la semana esté seleccionado
            $('form').on('submit', function(e) {
                let weekdaySelected = false;
                $('.weekday').each(function() {
                    if ($(this).is(':checked')) {
                        weekdaySelected = true;
                        return false; // Salir del bucle
                    }
                });
                
                if (!weekdaySelected) {
                    e.preventDefault();
                    alert('Debe seleccionar al menos un día de la semana.');
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