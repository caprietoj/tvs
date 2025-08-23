@extends('adminlte::page')

@section('title', 'Editar Previsita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Editar Previsita #{{ $previsita->id }}</h1>
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

        <form action="{{ route('previsitas.update', $previsita) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="lugar" class="required">Lugar <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('lugar') is-invalid @enderror" 
                               id="lugar" 
                               name="lugar" 
                               value="{{ old('lugar', $previsita->lugar) }}" 
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
                               value="{{ old('responsable', $previsita->responsable) }}" 
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
                               value="{{ old('fecha_visita', $previsita->fecha_visita->format('Y-m-d')) }}"
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
                               value="{{ old('vencimiento', $previsita->vencimiento ? $previsita->vencimiento->format('Y-m-d') : '') }}">
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
                            <option value="1" {{ old('aprobacion_sitio', $previsita->aprobacion_sitio ? '1' : '0') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('aprobacion_sitio', $previsita->aprobacion_sitio ? '1' : '0') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                        @error('aprobacion_sitio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="novedades_visita_archivo">Novedades Visita (PDF)</label>
                        @if($previsita->novedades_visita_archivo)
                            <div class="mb-2">
                                <small class="text-info">
                                    <i class="fas fa-file-pdf"></i> 
                                    Archivo actual: 
                                    <a href="{{ route('previsitas.download', $previsita) }}" target="_blank">
                                        {{ basename($previsita->novedades_visita_archivo) }}
                                    </a>
                                </small>
                            </div>
                        @endif
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input @error('novedades_visita_archivo') is-invalid @enderror" 
                                       id="novedades_visita_archivo" 
                                       name="novedades_visita_archivo"
                                       accept=".pdf">
                                <label class="custom-file-label" for="novedades_visita_archivo">
                                    {{ $previsita->novedades_visita_archivo ? 'Cambiar archivo PDF...' : 'Seleccionar archivo PDF...' }}
                                </label>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            {{ $previsita->novedades_visita_archivo ? 'Deje vacío para mantener el archivo actual. ' : '' }}
                            Archivo PDF máximo 10MB
                        </small>
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
                                  placeholder="Ingrese observaciones y recomendaciones importantes...">{{ old('observaciones_recomendaciones', $previsita->observaciones_recomendaciones) }}</textarea>
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
                            <i class="fas fa-save"></i> Actualizar Previsita
                        </button>
                        <a href="{{ route('previsitas.show', $previsita) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                        <a href="{{ route('previsitas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card custom-card">
    <div class="card-header">
        <h3 class="card-title">Información Adicional</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Creado por:</strong> {{ $previsita->user->name ?? 'N/A' }}</p>
                <p><strong>Fecha de creación:</strong> {{ $previsita->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Última actualización:</strong> {{ $previsita->updated_at->format('d/m/Y H:i') }}</p>
                @if($previsita->vencimiento < now())
                    <p><span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Vencida</span></p>
                @elseif($previsita->vencimiento <= now()->addDays(7))
                    <p><span class="badge badge-warning"><i class="fas fa-clock"></i> Próxima a vencer</span></p>
                @endif
            </div>
        </div>
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
    
    .text-info a {
        color: #17a2b8 !important;
        text-decoration: none;
    }
    
    .text-info a:hover {
        text-decoration: underline;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Actualizar el nombre del archivo seleccionado
        $('#novedades_visita_archivo').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            var hasCurrentFile = {{ $previsita->novedades_visita_archivo ? 'true' : 'false' }};
            var defaultText = hasCurrentFile ? 'Cambiar archivo PDF...' : 'Seleccionar archivo PDF...';
            $(this).next('.custom-file-label').html(fileName || defaultText);
        });
        
        // Validar que el vencimiento sea mayor o igual a la fecha de visita
        $('#fecha_visita, #vencimiento').on('change', function() {
            var fechaVisita = $('#fecha_visita').val();
            var vencimiento = $('#vencimiento').val();
            
            if (fechaVisita && vencimiento) {
                if (new Date(vencimiento) < new Date(fechaVisita)) {
                    alert('El vencimiento debe ser igual o posterior a la fecha de visita.');
                    $('#vencimiento').val('{{ $previsita->vencimiento ? $previsita->vencimiento->format('Y-m-d') : '' }}');
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
                    var hasCurrentFile = {{ $previsita->novedades_visita_archivo ? 'true' : 'false' }};
                    var defaultText = hasCurrentFile ? 'Cambiar archivo PDF...' : 'Seleccionar archivo PDF...';
                    $(this).next('.custom-file-label').html(defaultText);
                    return;
                }
                
                // Validar tamaño (10MB = 10485760 bytes)
                if (file.size > 10485760) {
                    alert('El archivo no debe superar los 10MB.');
                    $(this).val('');
                    var hasCurrentFile = {{ $previsita->novedades_visita_archivo ? 'true' : 'false' }};
                    var defaultText = hasCurrentFile ? 'Cambiar archivo PDF...' : 'Seleccionar archivo PDF...';
                    $(this).next('.custom-file-label').html(defaultText);
                    return;
                }
            }
        });
    });
</script>
@stop