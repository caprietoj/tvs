@extends('adminlte::page')

@section('title', 'Nuevo Estudiante - Parametrización')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-user-plus mr-2"></i>Nuevo Estudiante
        </h1>
        <div>
            <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <form action="{{ route('estudiantes.store') }}" method="POST" class="estudiante-form">
                @csrf
                
                <!-- Card 1: Información Personal -->
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user mr-2"></i>Información Personal
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre" class="font-weight-bold">
                                        <i class="fas fa-user mr-1"></i>Nombre <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('nombre') is-invalid @enderror" 
                                           id="nombre" 
                                           name="nombre" 
                                           value="{{ old('nombre') }}" 
                                           placeholder="Nombre del estudiante"
                                           required>
                                    @error('nombre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apellido_1" class="font-weight-bold">
                                        <i class="fas fa-user mr-1"></i>Primer Apellido <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('apellido_1') is-invalid @enderror" 
                                           id="apellido_1" 
                                           name="apellido_1" 
                                           value="{{ old('apellido_1') }}" 
                                           placeholder="Primer apellido"
                                           required>
                                    @error('apellido_1')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apellido_2" class="font-weight-bold">
                                        <i class="fas fa-user mr-1"></i>Segundo Apellido
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('apellido_2') is-invalid @enderror" 
                                           id="apellido_2" 
                                           name="apellido_2" 
                                           value="{{ old('apellido_2') }}" 
                                           placeholder="Segundo apellido (opcional)">
                                    @error('apellido_2')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Información Académica -->
                <div class="card card-outline card-info mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-graduation-cap mr-2"></i>Información Académica
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="curso" class="font-weight-bold">
                                        <i class="fas fa-school mr-1"></i>Curso <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-lg @error('curso') is-invalid @enderror" 
                                            id="curso" 
                                            name="curso" 
                                            required>
                                        <option value="">-- Seleccionar curso --</option>
                                        <option value="Preescolar" {{ old('curso') == 'Preescolar' ? 'selected' : '' }}>Preescolar</option>
                                        <option value="1°" {{ old('curso') == '1°' ? 'selected' : '' }}>Primero (1°)</option>
                                        <option value="2°" {{ old('curso') == '2°' ? 'selected' : '' }}>Segundo (2°)</option>
                                        <option value="3°" {{ old('curso') == '3°' ? 'selected' : '' }}>Tercero (3°)</option>
                                        <option value="4°" {{ old('curso') == '4°' ? 'selected' : '' }}>Cuarto (4°)</option>
                                        <option value="5°" {{ old('curso') == '5°' ? 'selected' : '' }}>Quinto (5°)</option>
                                        <option value="6°" {{ old('curso') == '6°' ? 'selected' : '' }}>Sexto (6°)</option>
                                        <option value="7°" {{ old('curso') == '7°' ? 'selected' : '' }}>Séptimo (7°)</option>
                                        <option value="8°" {{ old('curso') == '8°' ? 'selected' : '' }}>Octavo (8°)</option>
                                        <option value="9°" {{ old('curso') == '9°' ? 'selected' : '' }}>Noveno (9°)</option>
                                        <option value="10°" {{ old('curso') == '10°' ? 'selected' : '' }}>Décimo (10°)</option>
                                        <option value="11°" {{ old('curso') == '11°' ? 'selected' : '' }}>Once (11°)</option>
                                    </select>
                                    @error('curso')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo" class="font-weight-bold">
                                        <i class="fas fa-id-badge mr-1"></i>Código <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('codigo') is-invalid @enderror" 
                                           id="codigo" 
                                           name="codigo" 
                                           value="{{ old('codigo') }}" 
                                           placeholder="Código del estudiante"
                                           required>
                                    @error('codigo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="documento" class="font-weight-bold">
                                        <i class="fas fa-id-card mr-1"></i>Documento <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('documento') is-invalid @enderror" 
                                           id="documento" 
                                           name="documento" 
                                           value="{{ old('documento') }}" 
                                           placeholder="Número de documento"
                                           required>
                                    @error('documento')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Información Médica -->
                <div class="card card-outline card-success mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-heartbeat mr-2"></i>Información Médica
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="eps" class="font-weight-bold">
                                        <i class="fas fa-hospital mr-1"></i>EPS
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('eps') is-invalid @enderror" 
                                           id="eps" 
                                           name="eps" 
                                           value="{{ old('eps') }}" 
                                           placeholder="Entidad Promotora de Salud">
                                    @error('eps')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sexo" class="font-weight-bold">
                                        <i class="fas fa-venus-mars mr-1"></i>Sexo <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control form-control-lg @error('sexo') is-invalid @enderror" 
                                            id="sexo" 
                                            name="sexo" 
                                            required>
                                        <option value="">-- Seleccionar sexo --</option>
                                        <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                    @error('sexo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tipo_sangre" class="font-weight-bold">
                                        <i class="fas fa-tint mr-1"></i>Tipo de Sangre
                                    </label>
                                    <select class="form-control form-control-lg @error('tipo_sangre') is-invalid @enderror" 
                                            id="tipo_sangre" 
                                            name="tipo_sangre">
                                        <option value="">-- Seleccionar tipo --</option>
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
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('estudiantes.index') }}" 
                                   class="btn btn-secondary btn-block">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-save mr-2"></i>Guardar Estudiante
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Colores institucionales TVS */
        :root {
            --tvs-primary: #364E76;
            --tvs-primary-light: #4a6491;
            --tvs-primary-dark: #2c3e61;
            --tvs-secondary: #5a6c8a;
            --tvs-accent: #3498db;
            --tvs-success: #27ae60;
            --tvs-warning: #f39c12;
            --tvs-danger: #e74c3c;
            --tvs-info: #3498db;
            --tvs-light: #ecf0f1;
            --tvs-white: #ffffff;
        }

        /* Estilos generales del formulario */
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(54, 78, 118, 0.15);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
            border: 1px solid rgba(54, 78, 118, 0.1);
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(54, 78, 118, 0.25);
        }
        
        .card-body {
            padding: 25px;
            background: linear-gradient(to bottom, var(--tvs-white) 0%, rgba(54, 78, 118, 0.01) 100%);
        }
        
        .card-header {
            border-radius: 12px 12px 0 0 !important;
            color: var(--tvs-white) !important;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 18px 25px;
        }
        
        /* Headers específicos por sección */
        .card-outline.card-primary .card-header {
            background: linear-gradient(135deg, var(--tvs-primary) 0%, var(--tvs-primary-light) 100%);
            box-shadow: 0 2px 10px rgba(54, 78, 118, 0.3);
        }
        
        .card-outline.card-info .card-header {
            background: linear-gradient(135deg, var(--tvs-accent) 0%, var(--tvs-info) 100%);
            box-shadow: 0 2px 10px rgba(52, 152, 219, 0.3);
        }
        
        .card-outline.card-success .card-header {
            background: linear-gradient(135deg, var(--tvs-success) 0%, #2ecc71 100%);
            box-shadow: 0 2px 10px rgba(39, 174, 96, 0.3);
        }
        
        /* Estilos de los campos */
        .form-control {
            border-radius: 8px;
            border: 2px solid rgba(54, 78, 118, 0.2);
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 14px;
            background-color: var(--tvs-white);
        }
        
        .form-control:focus {
            border-color: var(--tvs-primary);
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
            transform: scale(1.02);
            background-color: rgba(54, 78, 118, 0.02);
        }
        
        .form-control-lg {
            padding: 15px 20px;
            font-size: 16px;
            font-weight: 500;
            border: 2px solid rgba(54, 78, 118, 0.25);
        }
        
        /* Estilos para selects */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23364E76' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 15px center;
            background-repeat: no-repeat;
            background-size: 20px 16px;
            padding-right: 50px;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        /* Etiquetas */
        .font-weight-bold {
            color: var(--tvs-primary-dark);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .font-weight-bold i {
            color: var(--tvs-primary);
            margin-right: 8px;
            font-size: 16px;
        }
        
        /* Botones */
        .btn {
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            font-size: 14px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--tvs-success) 0%, #2ecc71 100%);
            color: var(--tvs-white);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
            color: var(--tvs-white);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--tvs-secondary) 0%, var(--tvs-primary) 100%);
            color: var(--tvs-white);
            box-shadow: 0 4px 15px rgba(90, 108, 138, 0.3);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, var(--tvs-primary-dark) 0%, var(--tvs-secondary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(90, 108, 138, 0.4);
            color: var(--tvs-white);
        }

        /* Campos requeridos */
        .text-danger {
            color: var(--tvs-danger) !important;
            font-weight: bold;
        }

        /* Espaciado */
        .form-group {
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
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