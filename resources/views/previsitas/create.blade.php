@extends('adminlte::page')

@section('title', 'Nueva Previsita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Nueva Previsita</h1>
        <a href="{{ route('previsitas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-header">
        <h3 class="card-title">Información de la Previsita</h3>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('previsitas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="lugar" class="required">Lugar <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('lugar') is-invalid @enderror" 
                               id="lugar" 
                               name="lugar" 
                               value="{{ old('lugar') }}" 
                               placeholder="Ingrese el lugar de la visita"
                               required>
                        @error('lugar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="responsable" class="required">Responsable <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('responsable') is-invalid @enderror" 
                               id="responsable" 
                               name="responsable" 
                               value="{{ old('responsable') }}" 
                               placeholder="Ingrese el nombre del responsable"
                               required>
                        @error('responsable')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_visita" class="required">Fecha de Visita <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('fecha_visita') is-invalid @enderror" 
                               id="fecha_visita" 
                               name="fecha_visita" 
                               value="{{ old('fecha_visita') }}"
                               required>
                        @error('fecha_visita')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vencimiento">Vencimiento</label>
                        <input type="date" 
                               class="form-control @error('vencimiento') is-invalid @enderror" 
                               id="vencimiento" 
                               name="vencimiento" 
                               value="{{ old('vencimiento') }}">
                        @error('vencimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Dejar vacío si no requiere vencimiento (ej: museos, entidades gubernamentales)</small>
                    </div>
                </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="aprobacion_sitio" class="required">Aprobación del Sitio <span class="text-danger">*</span></label>
                        <select class="form-control @error('aprobacion_sitio') is-invalid @enderror" 
                                id="aprobacion_sitio" 
                                name="aprobacion_sitio" 
                                required>
                            <option value="">Seleccione una opción</option>
                            <option value="1" {{ old('aprobacion_sitio') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('aprobacion_sitio') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                        @error('aprobacion_sitio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="novedades_visita_archivo">Novedades Visita (PDF)</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input @error('novedades_visita_archivo') is-invalid @enderror" 
                                       id="novedades_visita_archivo" 
                                       name="novedades_visita_archivo"
                                       accept=".pdf">
                                <label class="custom-file-label" for="novedades_visita_archivo">Seleccionar archivo PDF...</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">Archivo PDF máximo 10MB</small>
                        @error('novedades_visita_archivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="observaciones_recomendaciones">Observaciones y Recomendaciones Importantes</label>
                        <textarea class="form-control @error('observaciones_recomendaciones') is-invalid @enderror" 
                                  id="observaciones_recomendaciones" 
                                  name="observaciones_recomendaciones" 
                                  rows="4" 
                                  placeholder="Ingrese observaciones y recomendaciones importantes...">{{ old('observaciones_recomendaciones') }}</textarea>
                        @error('observaciones_recomendaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Previsita
                        </button>
                        <a href="{{ route('previsitas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .custom-card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        margin-bottom: 1rem;
    }
    
    .required {
        font-weight: 600;
    }
    
    .form-group label {
        margin-bottom: 0.5rem;
    }
    
    .text-danger {
        font-weight: bold;
    }
    
    .invalid-feedback {
        display: block;
    }
    
    .custom-file-label::after {
        content: "Examinar";
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Actualizar el nombre del archivo seleccionado
        $('#novedades_visita_archivo').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Seleccionar archivo PDF...');
        });
        
        // Validar que el vencimiento sea mayor o igual a la fecha de visita
        $('#fecha_visita, #vencimiento').on('change', function() {
            var fechaVisita = $('#fecha_visita').val();
            var vencimiento = $('#vencimiento').val();
            
            if (fechaVisita && vencimiento) {
                if (new Date(vencimiento) < new Date(fechaVisita)) {
                    alert('El vencimiento debe ser igual o posterior a la fecha de visita.');
                    $('#vencimiento').val('');
                }
            }
        });
        
        // Validar archivo PDF
        $('#novedades_visita_archivo').on('change', function() {
            var file = this.files[0];
            if (file) {
                // Validar tipo de archivo
                if (file.type !== 'application/pdf') {
                    alert('Solo se permiten archivos PDF.');
                    $(this).val('');
                    $(this).next('.custom-file-label').html('Seleccionar archivo PDF...');
                    return;
                }
                
                // Validar tamaño (10MB = 10485760 bytes)
                if (file.size > 10485760) {
                    alert('El archivo no debe superar los 10MB.');
                    $(this).val('');
                    $(this).next('.custom-file-label').html('Seleccionar archivo PDF...');
                    return;
                }
            }
        });
    });
</script>
@stop