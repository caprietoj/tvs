@extends('adminlte::page')

@section('title', 'Crear Ciclo Escolar')

@section('content_header')
    <h1>Crear Nuevo Ciclo Escolar</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('school-cycles.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nombre del Ciclo Escolar <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required
                           placeholder="Ej: Ciclo Escolar 2025-2026">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="start_date">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Es recomendable iniciar en un lunes.</small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="cycle_length">Longitud del Ciclo <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('cycle_length') is-invalid @enderror" 
                               id="cycle_length" name="cycle_length" value="{{ old('cycle_length', 7) }}" 
                               min="1" max="30" required>
                        @error('cycle_length')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Número de días lectivos que componen el ciclo (Ej: 7 para un ciclo semanal).</small>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="active" name="active" {{ old('active') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="active">Activar Ciclo Escolar</label>
                        <small class="form-text text-muted ml-4">Al activar este ciclo, cualquier otro ciclo activo se desactivará automáticamente.</small>
                    </div>
                </div>

                <div class="card mt-4 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Generación de días de ciclo</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="generate_days" name="generate_days" {{ old('generate_days') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="generate_days">Generar días de ciclo automáticamente</label>
                            </div>
                        </div>

                        <div id="end_date_section" class="form-group" style="display: {{ old('generate_days') ? 'block' : 'none' }};">
                            <label for="end_date">Fecha de Fin para la generación</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Si no se especifica, se generarán días para un año a partir de la fecha de inicio.
                                Los fines de semana y días festivos serán excluidos automáticamente.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Ciclo Escolar
                    </button>
                    <a href="{{ route('school-cycles.index') }}" class="btn btn-secondary">
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
            // Mostrar/ocultar sección de fecha fin según se seleccione generar días
            $('#generate_days').change(function() {
                if($(this).is(':checked')) {
                    $('#end_date_section').show();
                } else {
                    $('#end_date_section').hide();
                }
            });
        });
    </script>
@stop