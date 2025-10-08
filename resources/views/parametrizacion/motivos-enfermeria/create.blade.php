@extends('adminlte::page')

@section('title', 'Nuevo Motivo de Enfermería')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-plus mr-2"></i>Nuevo Motivo de Enfermería
        </h1>
        <div>
            <a href="{{ route('motivos-enfermeria.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Información del Motivo
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('motivos-enfermeria.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nombre" class="font-weight-bold">
                                        <i class="fas fa-tag mr-1"></i>Nombre del Motivo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" 
                                           name="nombre" 
                                           value="{{ old('nombre') }}" 
                                           placeholder="Ej: Enfermedad, Accidente, Dolor..."
                                           required>
                                    @error('nombre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="orden" class="font-weight-bold">
                                        <i class="fas fa-sort-numeric-up mr-1"></i>Orden <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('orden') is-invalid @enderror" 
                                           id="orden" 
                                           name="orden" 
                                           value="{{ old('orden', 1) }}" 
                                           min="1"
                                           required>
                                    @error('orden')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="descripcion" class="font-weight-bold">
                                        <i class="fas fa-align-left mr-1"></i>Descripción
                                    </label>
                                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                              id="descripcion" 
                                              name="descripcion" 
                                              rows="3"
                                              placeholder="Descripción opcional del motivo...">{{ old('descripcion') }}</textarea>
                                    @error('descripcion')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icono" class="font-weight-bold">
                                        <i class="fas fa-smile mr-1"></i>Icono/Emoji (Opcional)
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('icono') is-invalid @enderror" 
                                           id="icono" 
                                           name="icono" 
                                           value="{{ old('icono') }}" 
                                           maxlength="10"
                                           placeholder="🤒 😷 🚑 etc.">
                                    <small class="form-text text-muted">
                                        Puedes usar emojis o iconos FontAwesome
                                    </small>
                                    @error('icono')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Estado</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="activo" 
                                               name="activo" 
                                               value="1"
                                               {{ old('activo', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="activo">
                                            <i class="fas fa-eye mr-1"></i>Activo
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Solo los motivos activos aparecerán en el formulario
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('motivos-enfermeria.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-save mr-2"></i>Guardar Motivo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de iconos sugeridos -->
    <div class="row justify-content-center mt-3">
        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb mr-2"></i>Iconos Sugeridos
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Haz clic en cualquier icono para agregarlo automáticamente:</p>
                    <div class="row text-center">
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="🤒">
                                🤒<br><small>Enfermedad</small>
                            </button>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="🚑">
                                🚑<br><small>Accidente</small>
                            </button>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="😣">
                                😣<br><small>Dolor</small>
                            </button>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="😷">
                                😷<br><small>Malestar</small>
                            </button>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="🚨">
                                🚨<br><small>Emergencia</small>
                            </button>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="🤕">
                                🤕<br><small>Lesión</small>
                            </button>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="💊">
                                💊<br><small>Medicamento</small>
                            </button>
                        </div>
                        <div class="col-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-icon" data-icon="🩹">
                                🩹<br><small>Cura</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .btn-icon {
            padding: 15px;
            font-size: 24px;
            line-height: 1.2;
            min-width: 80px;
            cursor: pointer;
        }
        
        .btn-icon:hover {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }
        
        .card-outline.card-primary {
            border-color: #364E76;
        }
        
        .card-outline.card-info {
            border-color: #3498db;
        }
        
        .form-control:focus {
            border-color: #364E76;
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
        
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #364E76;
            border-color: #364E76;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Manejar clic en iconos sugeridos
            $('.btn-icon').on('click', function() {
                const icon = $(this).data('icon');
                $('#icono').val(icon);
                $(this).addClass('btn-primary').removeClass('btn-outline-primary');
                
                // Remover la clase activa de otros botones
                $('.btn-icon').not(this).removeClass('btn-primary').addClass('btn-outline-primary');
                
                // Enfocar el campo de icono
                $('#icono').focus();
            });

            // Validación del formulario
            $('form').on('submit', function(e) {
                let isValid = true;
                
                // Validar campos requeridos
                $('[required]').each(function() {
                    if ($(this).val() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor complete todos los campos requeridos.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
            
            // Limpiar validación cuando el usuario escribe
            $('[required]').on('input change', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
@stop