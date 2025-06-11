@extends('adminlte::page')

@section('title', 'Agregar Proveedor')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Agregar Proveedor</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('proveedores.index') }}">Proveedores</a></li>
                    <li class="breadcrumb-item active">Crear</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Información del Proveedor</h3>
                    </div>
                    <form action="{{ route('proveedores.store') }}" method="POST" enctype="multipart/form-data" class="form-horizontal">
                        @csrf
                        <div class="card-body">
                            <!-- Información Básica -->
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Información Básica</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nombre">Nombre/Razón Social <span class="text-danger">*</span></label>
                                                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                                                    value="{{ old('nombre') }}" placeholder="Ingrese el nombre o razón social" required>
                                                @error('nombre')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nit">NIT <span class="text-danger">*</span></label>
                                                <input type="text" name="nit" class="form-control @error('nit') is-invalid @enderror" 
                                                    value="{{ old('nit') }}" placeholder="Ingrese el NIT" required>
                                                @error('nit')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de Contacto -->
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Información de Contacto</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="direccion">Dirección <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                    </div>
                                                    <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" 
                                                        value="{{ old('direccion') }}" placeholder="Ingrese la dirección" required>
                                                </div>
                                                @error('direccion')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ciudad">Ciudad <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-city"></i></span>
                                                    </div>
                                                    <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror" 
                                                        value="{{ old('ciudad') }}" placeholder="Ingrese la ciudad" required>
                                                </div>
                                                @error('ciudad')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                    </div>
                                                    <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                                                        value="{{ old('telefono') }}" placeholder="Ingrese el teléfono" required>
                                                </div>
                                                @error('telefono')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="email">Email <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                    </div>
                                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                                        value="{{ old('email') }}" placeholder="Ingrese el email" required>
                                                </div>
                                                @error('email')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="persona_contacto">Persona de Contacto <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                    </div>
                                                    <input type="text" name="persona_contacto" class="form-control @error('persona_contacto') is-invalid @enderror" 
                                                        value="{{ old('persona_contacto') }}" placeholder="Ingrese la persona de contacto" required>
                                                </div>
                                                @error('persona_contacto')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Clasificación del Proveedor -->
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Clasificación del Proveedor</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="market_segment" class="form-label">Segmento de mercado <span class="text-danger">*</span></label>
                                                <select name="market_segment" id="market_segment" class="form-control @error('market_segment') is-invalid @enderror" required>
                                                    <option value="">Seleccione un segmento</option>
                                                    <option value="Papelería y útiles de oficina" {{ old('market_segment') == 'Papelería y útiles de oficina' ? 'selected' : '' }}>Papelería y útiles de oficina</option>
                                                    <option value="Aseo y limpieza" {{ old('market_segment') == 'Aseo y limpieza' ? 'selected' : '' }}>Aseo y limpieza</option>
                                                    <option value="Tecnología y equipos de cómputo" {{ old('market_segment') == 'Tecnología y equipos de cómputo' ? 'selected' : '' }}>Tecnología y equipos de cómputo</option>
                                                    <option value="Alimentos y cafetería" {{ old('market_segment') == 'Alimentos y cafetería' ? 'selected' : '' }}>Alimentos y cafetería</option>
                                                    <option value="Materiales de construcción" {{ old('market_segment') == 'Materiales de construcción' ? 'selected' : '' }}>Materiales de construcción</option>
                                                    <option value="Publicidad e impresión" {{ old('market_segment') == 'Publicidad e impresión' ? 'selected' : '' }}>Publicidad e impresión</option>
                                                    <option value="Otro" {{ old('market_segment') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                                </select>
                                                @error('market_segment')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="servicio_producto">Servicio/Producto Ofrecido <span class="text-danger">*</span></label>
                                                <textarea name="servicio_producto" class="form-control @error('servicio_producto') is-invalid @enderror" 
                                                    rows="3" placeholder="Describa los servicios o productos ofrecidos" required>{{ old('servicio_producto') }}</textarea>
                                                @error('servicio_producto')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Alto Riesgo <span class="text-danger">*</span></label>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="riesgo_no" name="alto_riesgo" value="0" {{ old('alto_riesgo') === '0' ? 'checked' : '' }}>
                                                    <label for="riesgo_no" class="custom-control-label">No</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="riesgo_si" name="alto_riesgo" value="1" {{ old('alto_riesgo') === '1' ? 'checked' : '' }}>
                                                    <label for="riesgo_si" class="custom-control-label">Sí</label>
                                                </div>
                                                @error('alto_riesgo')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Proveedor Crítico <span class="text-danger">*</span></label>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="critico_no" name="proveedor_critico" value="0" {{ old('proveedor_critico') === '0' ? 'checked' : '' }}>
                                                    <label for="critico_no" class="custom-control-label">No</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="critico_si" name="proveedor_critico" value="1" {{ old('proveedor_critico') === '1' ? 'checked' : '' }}>
                                                    <label for="critico_si" class="custom-control-label">Sí</label>
                                                </div>
                                                @error('proveedor_critico')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Criterios de Selección -->
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Criterios de Selección</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Forma de Pago <span class="text-danger">*</span></label>
                                                <select name="forma_pago" class="form-control @error('forma_pago') is-invalid @enderror" required>
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="0-30">0-30 días (20 pts)</option>
                                                    <option value="31-60">31-60 días (50 pts)</option>
                                                    <option value="61-90">61-90 días (100 pts)</option>
                                                </select>
                                                @error('forma_pago')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Descuento Ofrecido</label>
                                                <select name="descuento" class="form-control">
                                                    <option value="0">0%</option>
                                                    <option value="5">5%</option>
                                                    <option value="10">10%</option>
                                                    <option value="12">12%</option>
                                                    <option value="15">15%</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Cobertura (# ciudades)</label>
                                                <select name="cobertura" class="form-control">
                                                    <option value="1">1 Ciudad</option>
                                                    <option value="2">2-3 Ciudades</option>
                                                    <option value="4">4 o más Ciudades</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Referencias Comerciales</label>
                                                <select name="referencias_comerciales" class="form-control">
                                                    <option value="1">1 Concepto positivo</option>
                                                    <option value="2">2 Conceptos positivos</option>
                                                    <option value="3">3 Conceptos positivos</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nivel de Precios</label>
                                                <select name="nivel_precios" class="form-control">
                                                    <option value="alto">Precios altos</option>
                                                    <option value="promedio">Promedio del mercado</option>
                                                    <option value="bajo">Precios bajos</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Valores Agregados</label>
                                                <textarea name="valores_agregados" class="form-control" rows="2" 
                                                        placeholder="Describa los valores agregados ofrecidos"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Criterios Técnicos del Área (20%) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-clipboard-check"></i></span>
                                                    </div>
                                                    <input type="number" name="criterios_tecnicos" 
                                                           class="form-control @error('criterios_tecnicos') is-invalid @enderror"
                                                           value="{{ old('criterios_tecnicos', 60) }}" 
                                                           min="0" max="100" required
                                                           placeholder="Ingrese el puntaje de evaluación técnica (0-100)">
                                                </div>
                                                <small class="form-text text-muted">
                                                    Evaluación técnica del área responsable. Ingrese un valor entre 0 y 100 puntos.
                                                </small>
                                                @error('criterios_tecnicos')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="alert alert-info">
                                        <h5><i class="icon fas fa-info"></i> Información sobre la Evaluación</h5>
                                        <p class="mb-2">El puntaje total se calcula con los siguientes criterios y pesos:</p>
                                        <ul class="mb-2">
                                            <li><strong>Forma de Pago (20%):</strong> 0-30 días (20 pts), 31-60 días (50 pts), 61-90 días (100 pts)</li>
                                            <li><strong>Referencias Comerciales (20%):</strong> 1 concepto (30 pts), 2 conceptos (60 pts), 3 conceptos (100 pts)</li>
                                            <li><strong>Descuento (10%):</strong> 0% (0 pts), 5% (25 pts), 10% (50 pts), 12% (75 pts), 15% (100 pts)</li>
                                            <li><strong>Cobertura (10%):</strong> 1 ciudad (50 pts), 2-3 ciudades (70 pts), 4+ ciudades (100 pts)</li>
                                            <li><strong>Valores Agregados (10%):</strong> Sin valores (0 pts), Con valores (80 pts)</li>
                                            <li><strong>Nivel de Precios (10%):</strong> Altos (0 pts), Promedio (50 pts), Bajos (100 pts)</li>
                                            <li><strong>Criterios Técnicos del Área (20%):</strong> Evaluación técnica (0-100 pts)</li>
                                        </ul>
                                        <p class="mb-0"><strong>Nota:</strong> Para ser seleccionado, el proveedor debe obtener un puntaje total igual o superior a 60 puntos.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Documentación -->
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Documentación</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h3 class="card-title">Documentos Legales</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-file-pdf text-danger mr-1"></i>
                                                            Cámara de Comercio
                                                        </label>
                                                        <div class="custom-file">
                                                            <input type="file" name="camara_comercio" class="custom-file-input" accept=".pdf">
                                                            <label class="custom-file-label">Seleccionar archivo</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-file-pdf text-danger mr-1"></i>
                                                            RUT
                                                        </label>
                                                        <div class="custom-file">
                                                            <input type="file" name="rut" class="custom-file-input" accept=".pdf">
                                                            <label class="custom-file-label">Seleccionar archivo</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-id-card text-info mr-1"></i>
                                                            Cédula Representante
                                                        </label>
                                                        <div class="custom-file">
                                                            <input type="file" name="cedula_representante" class="custom-file-input" accept=".pdf,.jpg,.jpeg,.png">
                                                            <label class="custom-file-label">Seleccionar archivo</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h3 class="card-title">Documentos Financieros y Seguridad</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-university text-success mr-1"></i>
                                                            Certificación Bancaria
                                                        </label>
                                                        <div class="custom-file">
                                                            <input type="file" name="certificacion_bancaria" class="custom-file-input" accept=".pdf">
                                                            <label class="custom-file-label">Seleccionar archivo</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>
                                                            <i class="fas fa-shield-alt text-warning mr-1"></i>
                                                            Seguridad Social
                                                        </label>
                                                        <div class="custom-file">
                                                            <input type="file" name="seguridad_social" class="custom-file-input" accept=".pdf">
                                                            <label class="custom-file-label">Seleccionar archivo</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h3 class="card-title">Documentos de Seguridad Industrial</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>
                                                                    <i class="fas fa-hard-hat text-warning mr-1"></i>
                                                                    Certificación Alturas
                                                                </label>
                                                                <div class="custom-file">
                                                                    <input type="file" name="certificacion_alturas" class="custom-file-input" accept=".pdf">
                                                                    <label class="custom-file-label">Seleccionar archivo</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>
                                                                    <i class="fas fa-exclamation-triangle text-danger mr-1"></i>
                                                                    Matriz de Peligros
                                                                </label>
                                                                <div class="custom-file">
                                                                    <input type="file" name="matriz_peligros" class="custom-file-input" accept=".pdf">
                                                                    <label class="custom-file-label">Seleccionar archivo</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>
                                                                    <i class="fas fa-user-shield text-info mr-1"></i>
                                                                    Matriz EPP
                                                                </label>
                                                                <div class="custom-file">
                                                                    <input type="file" name="matriz_epp" class="custom-file-input" accept=".pdf">
                                                                    <label class="custom-file-label">Seleccionar archivo</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar
                            </button>
                            <a href="{{ route('proveedores.index') }}" class="btn btn-secondary float-right">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-primary:not(.card-outline) > .card-header {
            background-color: #364E76;
        }
        
        .card-info:not(.card-outline) > .card-header {
            background-color: #364E76;
        }
        
        .btn-primary {
            background-color: #364E76;
            border-color: #364E76;
        }
        
        .btn-primary:hover {
            background-color: #2b3e5f;
            border-color: #2b3e5f;
        }
        
        .card {
            background-color: #FEFEFE;
        }
        
        .content-wrapper {
            background-color: #f4f6f9;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 400;
            margin: 0;
            color: #FEFEFE;
        }
        
        .form-group label {
            font-weight: 500;
            color: #364E76;
        }
        
        .custom-radio {
            margin-bottom: 0.5rem;
        }
        
        .text-danger {
            font-weight: bold;
        }
        
        .input-group-text {
            background-color: #364E76;
            color: #FEFEFE;
            border-color: #364E76;
        }
        
        .breadcrumb-item a {
            color: #364E76;
        }
        
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #364E76;
            border-color: #364E76;
        }
        
        .page-item.active .page-link {
            background-color: #364E76;
            border-color: #364E76;
        }
        
        .alert-info {
            background-color: rgba(54, 78, 118, 0.1);
            border-color: #364E76;
            color: #364E76;
        }
        
        .alert-info .icon {
            color: #364E76;
        }
        
        .alert-info ul {
            padding-left: 20px;
        }
        
        .alert-info ul li {
            margin-bottom: 5px;
        }
    </style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bs-custom-file-input/1.3.4/bs-custom-file-input.min.js"></script>
<script>
    $(document).ready(function () {
        bsCustomFileInput.init();
    });
</script>
@stop
