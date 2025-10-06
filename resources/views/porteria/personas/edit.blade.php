@extends('adminlte::page')

@section('title', 'Editar Persona - Portería')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> Editar Persona</h1>
@stop

@section('content')
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">Formulario de Edición</h3>
        </div>
        <form action="{{ route('porteria.personas.update', $persona->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="documento">Documento <span class="text-danger">*</span></label>
                            <input type="text" name="documento" id="documento" 
                                   class="form-control @error('documento') is-invalid @enderror" 
                                   value="{{ old('documento', $persona->documento) }}" required>
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
                                <option value="estudiante" {{ old('tipo_persona', $persona->tipo_persona) == 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                                <option value="empleado" {{ old('tipo_persona', $persona->tipo_persona) == 'empleado' ? 'selected' : '' }}>Empleado</option>
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
                                   value="{{ old('nombre', $persona->nombre) }}" 
                                   placeholder="Ingrese el nombre completo"
                                   required>
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Ingrese el nombre completo de la persona
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="grado">Grado/Cargo</label>
                            <input type="text" name="grado" id="grado" 
                                   class="form-control @error('grado') is-invalid @enderror" 
                                   value="{{ old('grado', $persona->grado) }}" 
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
                                <option value="1" {{ old('activo', $persona->activo) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo', $persona->activo) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" 
                              class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $persona->observaciones) }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="{{ route('porteria.personas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop
