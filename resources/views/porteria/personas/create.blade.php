@extends('adminlte::page')

@section('title', 'Nueva Persona - Portería')

@section('content_header')
    <h1><i class="fas fa-user-plus"></i> Registrar Nueva Persona</h1>
@stop

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Formulario de Registro</h3>
        </div>
        <form action="{{ route('porteria.personas.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="documento">Documento <span class="text-danger">*</span></label>
                            <input type="text" name="documento" id="documento" 
                                   class="form-control @error('documento') is-invalid @enderror" 
                                   value="{{ old('documento') }}" required>
                            @error('documento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo_persona">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo_persona" id="tipo_persona" 
                                    class="form-control @error('tipo_persona') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                <option value="estudiante" {{ old('tipo_persona') == 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                                <option value="empleado" {{ old('tipo_persona') == 'empleado' ? 'selected' : '' }}>Empleado</option>
                            </select>
                            @error('tipo_persona')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   value="{{ old('nombre') }}" 
                                   placeholder="Ingrese el nombre completo de la persona"
                                   required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Ingrese el nombre completo (nombres y apellidos)
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" 
                                   placeholder="correo@ejemplo.com">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" 
                                   class="form-control @error('telefono') is-invalid @enderror" 
                                   value="{{ old('telefono') }}" 
                                   placeholder="Ej: 3001234567">
                            @error('telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="grado">Grado/Cargo</label>
                            <input type="text" name="grado" id="grado" 
                                   class="form-control @error('grado') is-invalid @enderror" 
                                   value="{{ old('grado') }}" 
                                   placeholder="Ej: 5°A, Coordinador, etc.">
                            @error('grado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="activo">Estado</label>
                            <select name="activo" id="activo" class="form-control">
                                <option value="1" {{ old('activo', 1) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo') == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" 
                              class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="{{ route('porteria.personas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        :root {
            --color-institucional: #233e6c;
        }
        .card-primary:not(.card-outline) > .card-header {
            background-color: var(--color-institucional);
        }
        .btn-primary {
            background-color: var(--color-institucional) !important;
            border-color: var(--color-institucional) !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Validación del formulario
            $('form').on('submit', function(e) {
                let valid = true;
                
                // Validar campos requeridos
                if (!$('#documento').val()) {
                    valid = false;
                    alert('El documento es obligatorio');
                }
                if (!$('#nombre').val()) {
                    valid = false;
                    alert('El nombre completo es obligatorio');
                }
                if (!$('#tipo_persona').val()) {
                    valid = false;
                    alert('Debe seleccionar el tipo de persona');
                }
                
                if (!valid) {
                    e.preventDefault();
                    return false;
                }
                
                console.log('Formulario enviado correctamente');
            });
        });
    </script>
@stop
