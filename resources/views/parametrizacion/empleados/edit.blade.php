@extends('adminlte::page')

@section('title', 'Editar Empleado')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-user-edit mr-2"></i>Editar Empleado
        </h1>
        <div>
            <a href="{{ route('empleados.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>Datos del Empleado
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('empleados.update', $empleado->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre_completo">
                                        <i class="fas fa-user mr-1"></i>Nombre Completo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('nombre_completo') is-invalid @enderror" 
                                           id="nombre_completo" 
                                           name="nombre_completo" 
                                           value="{{ old('nombre_completo', $empleado->nombre_completo) }}"
                                           required>
                                    @error('nombre_completo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="documento">
                                        <i class="fas fa-id-card mr-1"></i>Documento <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('documento') is-invalid @enderror" 
                                           id="documento" 
                                           name="documento" 
                                           value="{{ old('documento', $empleado->documento) }}"
                                           required>
                                    @error('documento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope mr-1"></i>Email
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $empleado->email) }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area">
                                        <i class="fas fa-briefcase mr-1"></i>Área
                                    </label>
                                    <select class="form-control @error('area') is-invalid @enderror" 
                                            id="area" 
                                            name="area">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="DOCENTE" {{ old('area', $empleado->area) == 'DOCENTE' ? 'selected' : '' }}>DOCENTE</option>
                                        <option value="ADMINISTRATIVO" {{ old('area', $empleado->area) == 'ADMINISTRATIVO' ? 'selected' : '' }}>ADMINISTRATIVO</option>
                                        <option value="SERV. GENS. Y MTO." {{ old('area', $empleado->area) == 'SERV. GENS. Y MTO.' ? 'selected' : '' }}>SERV. GENS. Y MTO.</option>
                                        <option value="TRANSPORTE" {{ old('area', $empleado->area) == 'TRANSPORTE' ? 'selected' : '' }}>TRANSPORTE</option>
                                        <option value="OTRO" {{ old('area', $empleado->area) == 'OTRO' ? 'selected' : '' }}>OTRO</option>
                                    </select>
                                    @error('area')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sexo">
                                        <i class="fas fa-venus-mars mr-1"></i>Sexo
                                    </label>
                                    <select class="form-control @error('sexo') is-invalid @enderror" 
                                            id="sexo" 
                                            name="sexo">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="M" {{ old('sexo', $empleado->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('sexo', $empleado->sexo) == 'F' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                    @error('sexo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_sangre">
                                        <i class="fas fa-tint mr-1"></i>Tipo de Sangre
                                    </label>
                                    <select class="form-control @error('tipo_sangre') is-invalid @enderror" 
                                            id="tipo_sangre" 
                                            name="tipo_sangre">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="A+" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('tipo_sangre', $empleado->tipo_sangre) == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('tipo_sangre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="activo">
                                        <i class="fas fa-toggle-on mr-1"></i>Estado
                                    </label>
                                    <select class="form-control @error('activo') is-invalid @enderror" 
                                            id="activo" 
                                            name="activo">
                                        <option value="1" {{ old('activo', $empleado->activo) == 1 ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('activo', $empleado->activo) == 0 ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @error('activo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save mr-1"></i>Actualizar Empleado
                                </button>
                                <a href="{{ route('empleados.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card mt-3">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>Información del Registro
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Fecha de Creación:</strong> {{ $empleado->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Última Actualización:</strong> {{ $empleado->updated_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-group label {
            font-weight: 600;
        }
    </style>
@stop
