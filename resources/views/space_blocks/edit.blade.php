@extends('adminlte::page')

@section('title', 'Editar Bloqueo de Espacio')

@section('content_header')
    <h1>Editar Bloqueo de Espacio</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('space-blocks.update', $spaceBlock) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Espacio:</label>
                            <p class="form-control-static"><strong>{{ $spaceBlock->space->name }}</strong></p>
                            <input type="hidden" name="space_id" value="{{ $spaceBlock->space_id }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ciclo Escolar:</label>
                            <p class="form-control-static"><strong>{{ $spaceBlock->schoolCycle->name }}</strong></p>
                            <input type="hidden" name="school_cycle_id" value="{{ $spaceBlock->school_cycle_id }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Día del Ciclo:</label>
                    <p class="form-control-static"><strong>{{ $spaceBlock->cycle_day }}</strong></p>
                    <input type="hidden" name="cycle_day" value="{{ $spaceBlock->cycle_day }}">
                </div>
                
                <div class="form-group">
                    <label for="reason">Motivo del bloqueo</label>
                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3">{{ old('reason', $spaceBlock->reason) }}</textarea>
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
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', $spaceBlock->start_time) }}" required>
                                @error('start_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_time">Hora de fin</label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', $spaceBlock->end_time) }}" required>
                                @error('end_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <p><i class="fas fa-info-circle"></i> Nota: El espacio, ciclo escolar y día de ciclo no pueden ser modificados. Si necesita cambiar estos valores, elimine este bloqueo y cree uno nuevo.</p>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('space-blocks.index') }}" class="btn btn-secondary">Cancelar</a>
                    
                    <button type="button" class="btn btn-danger float-right" 
                            onclick="if(confirm('¿Está seguro de que desea eliminar este bloqueo?')) { 
                                document.getElementById('delete-form').submit(); 
                            }">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </form>
            
            <form id="delete-form" action="{{ route('space-blocks.destroy', $spaceBlock) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
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
            // Validación del formulario
            $('form').on('submit', function(e) {
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