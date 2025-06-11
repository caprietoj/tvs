@extends('adminlte::page')

@section('title', 'Editar Salida Pedagógica')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Salida Pedagógica {{ $salida->consecutivo }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('salidas.index') }}">Salidas</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('salidas.update', $salida) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="container-fluid">
            <!-- Información General -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="calendario_general">Calendario General</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="calendario_general" 
                                        name="calendario_general" {{ old('calendario_general', $salida->calendario_general) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="calendario_general">Mostrar en Calendario General</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grados">Grados <span class="text-danger">*</span></label>
                                <input type="text" name="grados" class="form-control @error('grados') is-invalid @enderror" 
                                    value="{{ old('grados', $salida->grados) }}" required>
                                @error('grados')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lugar">Lugar <span class="text-danger">*</span></label>
                                <input type="text" name="lugar" class="form-control @error('lugar') is-invalid @enderror" 
                                    value="{{ old('lugar', $salida->lugar) }}" required>
                                @error('lugar')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="responsable_id">Responsable <span class="text-danger">*</span></label>
                                <select name="responsable_id" class="form-control @error('responsable_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un responsable</option>
                                    @foreach($responsables as $responsable)
                                        <option value="{{ $responsable->id }}" 
                                            {{ old('responsable_id', $salida->responsable_id) == $responsable->id ? 'selected' : '' }}>
                                            {{ $responsable->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsable_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidad_pasajeros">Cantidad de Pasajeros <span class="text-danger">*</span></label>
                                <input type="number" name="cantidad_pasajeros" 
                                    class="form-control @error('cantidad_pasajeros') is-invalid @enderror" 
                                    value="{{ old('cantidad_pasajeros', $salida->cantidad_pasajeros) }}" min="1" required>
                                @error('cantidad_pasajeros')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_salida">Fecha Salida <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_salida" 
                                    class="form-control @error('fecha_salida') is-invalid @enderror" 
                                    value="{{ old('fecha_salida', $salida->fecha_salida->format('Y-m-d')) }}" required>
                                @error('fecha_salida')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="hora_salida">Hora Salida <span class="text-danger">*</span></label>
                                <input type="time" name="hora_salida" 
                                    class="form-control @error('hora_salida') is-invalid @enderror" 
                                    value="{{ old('hora_salida', $salida->fecha_salida->format('H:i')) }}" required>
                                @error('hora_salida')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_regreso">Fecha Regreso <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_regreso" 
                                    class="form-control @error('fecha_regreso') is-invalid @enderror" 
                                    value="{{ old('fecha_regreso', $salida->fecha_regreso->format('Y-m-d')) }}" required>
                                @error('fecha_regreso')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="hora_regreso">Hora Regreso <span class="text-danger">*</span></label>
                                <input type="time" name="hora_regreso" 
                                    class="form-control @error('hora_regreso') is-invalid @enderror" 
                                    value="{{ old('hora_regreso', $salida->fecha_regreso->format('H:i')) }}" required>
                                @error('hora_regreso')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inspección y Servicios -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Inspección y Servicios</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="visita_inspeccion" 
                                        name="visita_inspeccion" {{ old('visita_inspeccion', $salida->visita_inspeccion) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="visita_inspeccion">Requiere Visita de Inspección</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="detalles_inspeccion">Detalles de Inspección</label>
                                <textarea name="detalles_inspeccion" class="form-control" rows="3">{{ old('detalles_inspeccion', $salida->detalles_inspeccion) }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contacto_lugar">Contacto del Lugar</label>
                                <input type="text" name="contacto_lugar" class="form-control" 
                                    value="{{ old('contacto_lugar', $salida->contacto_lugar) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servicios Adicionales -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Servicios Adicionales</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Alimentación</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="requiere_alimentacion" 
                                        name="requiere_alimentacion" {{ old('requiere_alimentacion', $salida->requiere_alimentacion) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="requiere_alimentacion">Requiere Alimentación</label>
                                </div>
                                <div id="detalles-alimentacion" class="mt-3" style="{{ old('requiere_alimentacion', $salida->requiere_alimentacion) ? '' : 'display: none;' }}">
                                    <div class="form-group">
                                        <label>Cantidad Snacks</label>
                                        <input type="number" name="cantidad_snacks" class="form-control" 
                                            value="{{ old('cantidad_snacks', $salida->cantidad_snacks) }}" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Cantidad Almuerzos</label>
                                        <input type="number" name="cantidad_almuerzos" class="form-control" 
                                            value="{{ old('cantidad_almuerzos', $salida->cantidad_almuerzos) }}" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Menú Sugerido</label>
                                        <textarea name="menu_sugerido" class="form-control" rows="2">{{ old('menu_sugerido', $salida->menu_sugerido) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Enfermería</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="requiere_enfermeria" 
                                        name="requiere_enfermeria" {{ old('requiere_enfermeria', $salida->requiere_enfermeria) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="requiere_enfermeria">Requiere Enfermería</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Comunicaciones</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="requiere_comunicaciones" 
                                        name="requiere_comunicaciones" {{ old('requiere_comunicaciones', $salida->requiere_comunicaciones) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="requiere_comunicaciones">Requiere Comunicaciones</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Observaciones</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea name="observaciones" class="form-control" rows="3" 
                            placeholder="Observaciones generales">{{ old('observaciones', $salida->observaciones) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('salidas.show', $salida) }}" class="btn btn-secondary float-right">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <style>
        .card-header { background-color: #364E76 !important; color: white; }
        .btn-primary { background-color: #364E76; border-color: #364E76; }
        .btn-primary:hover { background-color: #2B3E5F; border-color: #2B3E5F; }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#requiere_alimentacion').change(function() {
                $('#detalles-alimentacion').toggle(this.checked);
            });
        });
    </script>
@stop
