@extends('adminlte::page')

@section('title', 'Nuevo Empleado')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-user-plus mr-2"></i>Registrar Nuevo Empleado
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
            <!-- Formulario Individual -->
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>Registro Individual
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('empleados.store') }}" method="POST">
                        @csrf
                        
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
                                           value="{{ old('nombre_completo') }}"
                                           placeholder="Ej: Juan Pérez García"
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
                                           value="{{ old('documento') }}"
                                           placeholder="Ej: 1234567890"
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
                                           value="{{ old('email') }}"
                                           placeholder="correo@ejemplo.com">
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
                                        <option value="DOCENTE" {{ old('area') == 'DOCENTE' ? 'selected' : '' }}>DOCENTE</option>
                                        <option value="ADMINISTRATIVO" {{ old('area') == 'ADMINISTRATIVO' ? 'selected' : '' }}>ADMINISTRATIVO</option>
                                        <option value="SERV. GENS. Y MTO." {{ old('area') == 'SERV. GENS. Y MTO.' ? 'selected' : '' }}>SERV. GENS. Y MTO.</option>
                                        <option value="TRANSPORTE" {{ old('area') == 'TRANSPORTE' ? 'selected' : '' }}>TRANSPORTE</option>
                                        <option value="OTRO" {{ old('area') == 'OTRO' ? 'selected' : '' }}>OTRO</option>
                                    </select>
                                    @error('area')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sexo">
                                        <i class="fas fa-venus-mars mr-1"></i>Sexo
                                    </label>
                                    <select class="form-control @error('sexo') is-invalid @enderror" 
                                            id="sexo" 
                                            name="sexo">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                    @error('sexo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_sangre">
                                        <i class="fas fa-tint mr-1"></i>Tipo de Sangre
                                    </label>
                                    <select class="form-control @error('tipo_sangre') is-invalid @enderror" 
                                            id="tipo_sangre" 
                                            name="tipo_sangre">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="A+" {{ old('tipo_sangre') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('tipo_sangre') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('tipo_sangre') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('tipo_sangre') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('tipo_sangre') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('tipo_sangre') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('tipo_sangre') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('tipo_sangre') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('tipo_sangre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>Guardar Empleado
                                </button>
                                <a href="{{ route('empleados.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Importación Masiva -->
            <div class="card mt-3">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        <i class="fas fa-file-excel mr-2"></i>Importación Masiva desde Excel
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Instrucciones:</strong>
                        <ol class="mb-0">
                            <li>Primero seleccione el <strong>Área</strong> en el campo de abajo</li>
                            <li>Copie los datos desde Excel (seleccione y Ctrl+C)</li>
                            <li>Pegue los datos en el área de texto (Ctrl+V)</li>
                            <li>Las columnas deben estar en este orden: <strong>Nombre Completo [TAB] Documento [TAB] Email [TAB] Sexo [TAB] Tipo de Sangre</strong></li>
                            <li>Los campos <strong>Nombre Completo</strong> y <strong>Documento</strong> son obligatorios</li>
                            <li>Sexo: M (Masculino) o F (Femenino)</li>
                            <li>Tipo de Sangre: A+, A-, B+, B-, AB+, AB-, O+, O-</li>
                        </ol>
                    </div>

                    <form action="{{ route('empleados.import') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="area_masiva">
                                <i class="fas fa-building mr-1"></i>Área <span class="text-danger">*</span>
                            </label>
                            <select name="area_masiva" id="area_masiva" class="form-control @error('area_masiva') is-invalid @enderror" required>
                                <option value="">-- Seleccione el área para todos los empleados --</option>
                                <option value="DOCENTE" {{ old('area_masiva') == 'DOCENTE' ? 'selected' : '' }}>DOCENTE</option>
                                <option value="ADMINISTRATIVO" {{ old('area_masiva') == 'ADMINISTRATIVO' ? 'selected' : '' }}>ADMINISTRATIVO</option>
                                <option value="SERV. GENS. Y MTO." {{ old('area_masiva') == 'SERV. GENS. Y MTO.' ? 'selected' : '' }}>SERV. GENS. Y MTO.</option>
                                <option value="TRANSPORTE" {{ old('area_masiva') == 'TRANSPORTE' ? 'selected' : '' }}>TRANSPORTE</option>
                                <option value="OTRO" {{ old('area_masiva') == 'OTRO' ? 'selected' : '' }}>OTRO</option>
                            </select>
                            @error('area_masiva')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="datos">
                                <i class="fas fa-table mr-1"></i>Datos de Excel (pegue aquí)
                            </label>
                            <textarea class="form-control @error('datos') is-invalid @enderror" 
                                      id="datos" 
                                      name="datos" 
                                      rows="10"
                                      placeholder="Pegue aquí los datos copiados desde Excel..."
                                      required>{{ old('datos') }}</textarea>
                            @error('datos')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                Ejemplo:<br>
                                Juan Pérez García&nbsp;&nbsp;&nbsp;&nbsp;1234567890&nbsp;&nbsp;&nbsp;&nbsp;juan@email.com&nbsp;&nbsp;&nbsp;&nbsp;M&nbsp;&nbsp;&nbsp;&nbsp;O+<br>
                                María López Torres&nbsp;&nbsp;&nbsp;&nbsp;9876543210&nbsp;&nbsp;&nbsp;&nbsp;maria@email.com&nbsp;&nbsp;&nbsp;&nbsp;F&nbsp;&nbsp;&nbsp;&nbsp;A+
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload mr-1"></i>Importar Empleados
                                </button>
                            </div>
                        </div>
                    </form>
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
        .alert ol {
            margin-left: 20px;
        }
    </style>
@stop
