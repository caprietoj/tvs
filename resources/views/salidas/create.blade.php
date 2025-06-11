@extends('adminlte::page')

@section('title', 'Nueva Salida Pedagógica')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-bus mr-2"></i>
            Nueva Salida Pedagógica
            <small class="text-muted">{{ $consecutivo }}</small>
        </h1>
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('salidas.index') }}">Salidas</a></li>
            <li class="breadcrumb-item active">Nueva Salida</li>
        </ol>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card {
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            border: none;
        }
        .card-header {
            background-color: #364E76 !important;
            color: white;
            border-radius: 8px 8px 0 0 !important;
            padding: 0.75rem 1.25rem;
        }
        .card-header .card-title {
            display: flex;
            align-items: center;
            margin: 0;
            font-size: 1.1rem;
        }
        .card-header i {
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #364E76;
            border-color: #364E76;
            padding: 8px 20px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #2B3E5F;
            border-color: #2B3E5F;
        }
        .btn-secondary {
            padding: 8px 20px;
            font-weight: 500;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #364E76;
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
        .custom-switch .custom-control-label::before {
            border-color: #364E76;
        }
        .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #364E76;
            border-color: #364E76;
        }
        label {
            font-weight: 500;
            color: #495057;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .text-danger {
            font-weight: bold;
        }
        .nav-tabs .nav-link {
            color: #364E76;
            padding: 1rem 1.5rem;
        }
        .nav-tabs .nav-link.active {
            color: #364E76;
            font-weight: bold;
            border-top: 3px solid #364E76;
        }
        .form-control:focus {
            border-color: #364E76;
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #364E76;
            border-color: #364E76;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0 1rem rgba(0,0,0,.15);
            border: none;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #364E76;
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .required-field::after {
            content: '*';
            color: red;
            margin-left: 4px;
        }
        .progress-indicator {
            position: relative;
            padding-left: 1.5rem;
        }
        .progress-indicator::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: #364E76;
        }

        /* Custom styling for tabs */
        #custom-tabs .nav-link {
            color: #FEFEFE;
            opacity: 0.8;
        }
        
        #custom-tabs .nav-link:hover {
            opacity: 1;
        }
        
        #custom-tabs .nav-link.active {
            color: #364E76;
            background-color: #FEFEFE;
            opacity: 1;
        }
        
        #custom-tabs {
            background-color: #364E76;
            border-radius: 0.5rem 0.5rem 0 0;
            border: none;
            padding: 0.5rem 1rem 0;
        }
        .required:after {
            content: ' *';
            color: red;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
        }
        .card-header {
            background-color: #364E76 !important;
            color: white;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        .nav-tabs {
            border-bottom: none;
        }
        .nav-tabs .nav-link {
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: white !important; /* Texto blanco al pasar el ratón */
            background-color: rgba(255, 255, 255, 0.2) !important; /* Fondo semitransparente */
            border-top: 3px solid white !important; /* Borde superior blanco */
        }
        
        .nav-tabs .nav-link.active {
            color: #364E76;
            background-color: white;
            border-top: 3px solid #364E76;
        }
        
        /* Aseguramos que los íconos también cambien a blanco */
        .nav-tabs .nav-link:hover i {
            color: white !important;
        }
        .form-control:focus {
            border-color: #364E76;
            box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
        }
        .progress-bar {
            background-color: #364E76;
        }
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        
        /* Cambiar el color del badge al azul institucional */
        .badge-primary {
            background-color: #364E76 !important;
        }
        
        /* Asegurar que el texto sea legible en el nuevo fondo */
        .badge {
            color: white;
            font-weight: 600;
        }

        /* Estilos para los enlaces de navegación en la barra lateral */
        .nav-link {
            color: #495057;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            padding: 0.75rem 1rem;
        }
        
        .nav-link:hover {
            background-color: rgba(54, 78, 118, 0.1);
            color: #364E76;
            border-left-color: #364E76;
        }
        
        .nav-link .float-right i.far,
        .nav-link .float-right i.fas {
            color: #364E76;
        }
    </style>
@stop

@section('content')
<div class="container-fluid">
    <form id="salidaForm" action="{{ route('salidas.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Sidebar con información y estado -->
            <div class="col-md-3">
                <!-- Información Básica -->
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="h1 mb-3">
                            <span class="badge badge-primary">{{ $consecutivo }}</span>
                        </div>
                        <p class="text-muted mb-0">Fecha de Solicitud</p>
                        <p class="h4">{{ Carbon\Carbon::now()->format('d/m/Y') }}</p>
                        <hr>
                        <div class="progress-tracker mt-4">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: 0%" id="formProgress"></div>
                            </div>
                            <small class="text-muted" id="progressText">0% completado</small>
                        </div>
                    </div>
                </div>

                <!-- Servicios Requeridos -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Servicios Requeridos</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="#transporte">
                                    <i class="fas fa-bus"></i> Transporte
                                    <span class="float-right" id="transporteCheck"><i class="far fa-circle" style="color: #364E76;"></i></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#alimentacion">
                                    <i class="fas fa-utensils"></i> Alimentación
                                    <span class="float-right" id="alimentacionCheck"><i class="far fa-circle" style="color: #364E76;"></i></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#enfermeria">
                                    <i class="fas fa-heartbeat"></i> Enfermería
                                    <span class="float-right" id="enfermeriaCheck"><i class="far fa-circle" style="color: #364E76;"></i></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#comunicaciones">
                                    <i class="fas fa-bullhorn"></i> Comunicaciones
                                    <span class="float-right" id="comunicacionesCheck"><i class="far fa-circle" style="color: #364E76;"></i></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#arl">
                                    <i class="fas fa-medkit"></i> ARL
                                    <span class="float-right" id="arlCheck"><i class="far fa-circle" style="color: #364E76;"></i></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Formulario Principal -->
            <div class="col-md-9">
                <!-- Información General -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información General</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required">Grados</label>
                                    <input type="text" name="grados" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required">Responsable</label>
                                    <select name="responsable_id" class="form-control select2" required>
                                        <option value="">Seleccione un responsable</option>
                                        @foreach($responsables as $responsable)
                                            <option value="{{ $responsable->id }}">{{ $responsable->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required">Lugar a Visitar</label>
                            <input type="text" name="lugar" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha de Salida *</label>
                                    <input type="date" name="fecha_salida" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Hora de Salida *</label>
                                    <input type="time" name="hora_salida" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha de Regreso *</label>
                                    <input type="date" name="fecha_regreso" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Hora de Regreso *</label>
                                    <input type="time" name="hora_regreso" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Visita de Inspección</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="visita_inspeccion" name="visita_inspeccion">
                                        <label class="custom-control-label" for="visita_inspeccion">Habilitado</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de visita de inspección - aparece solo cuando el switch está activado -->
                        <div class="row mt-2" id="detalles_inspeccion_container" style="display: none;">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>DETALLES VISITA DE INSPECCIÓN - FECHA - HORA - CONTACTO</label>
                                    <textarea name="detalles_inspeccion" id="detalles_inspeccion" class="form-control" rows="3" placeholder="Ingrese detalles de la visita de inspección, incluyendo fecha, hora y datos de contacto"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Servicios -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs" id="serviceTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#transporte" role="tab">
                                    <i class="fas fa-bus"></i> Transporte
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#alimentacion" role="tab">
                                    <i class="fas fa-utensils"></i> Alimentación
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#otros" role="tab">
                                    <i class="fas fa-cog"></i> Otros Servicios
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="transporte" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cantidad de Pasajeros *</label>
                                            <input type="number" name="cantidad_pasajeros" class="form-control" required min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Hora Salida Bus</label>
                                            <input type="time" name="hora_salida_bus" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Hora Regreso Bus</label>
                                            <input type="time" name="hora_regreso_bus" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Transporte Confirmado</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="transporte_confirmado" name="transporte_confirmado">
                                                <label class="custom-control-label" for="transporte_confirmado">Confirmado</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="alimentacion" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Requiere Alimentación</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="requiere_alimentacion" name="requiere_alimentacion">
                                                <label class="custom-control-label" for="requiere_alimentacion">Requerido</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cantidad Snacks</label>
                                            <input type="number" name="cantidad_snacks" class="form-control" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cantidad Almuerzos</label>
                                            <input type="number" name="cantidad_almuerzos" class="form-control" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Hora Entrega Alimentos</label>
                                            <input type="time" name="hora_entrega_alimentos" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Menú Sugerido</label>
                                            <textarea name="menu_sugerido" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Observaciones Dietéticas</label>
                                            <textarea name="observaciones_dieteticas" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="otros" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Hora Apertura Puertas</label>
                                            <input type="time" name="hora_apertura_puertas" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Requiere Enfermería</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="requiere_enfermeria" name="requiere_enfermeria">
                                                <label class="custom-control-label" for="requiere_enfermeria">Requerido</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Requiere Comunicaciones</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="requiere_comunicaciones" name="requiere_comunicaciones">
                                                <label class="custom-control-label" for="requiere_comunicaciones">Requerido</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Reportar a ARL</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="requiere_arl" name="requiere_arl">
                                                <label class="custom-control-label" for="requiere_arl">Requerido</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Guardar Salida
                            </button>
                            <a href="{{ route('salidas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
            
            // Form progress tracking
            function updateProgress() {
                const requiredFields = $('#salidaForm [required]');
                const filledFields = requiredFields.filter(function() {
                    return $(this).val() !== '';
                });
                const progress = (filledFields.length / requiredFields.length) * 100;
                
                $('#formProgress').css('width', progress + '%');
                $('#progressText').text(Math.round(progress) + '% completado');
            }

            $('#salidaForm [required]').on('change', updateProgress);
            updateProgress();

            // Service selection indicators
            function updateServiceIndicator(service, checked) {
                const icon = checked ? 'fa-check-circle' : 'fa-circle';
                const element = $(`#${service}Check i`);
                element.attr('class', checked ? 'fas ' + icon : 'far ' + icon);
                element.css('color', '#364E76'); // Mantener el color institucional
            }

            $('input[type="checkbox"]').on('change', function() {
                const service = $(this).attr('id').replace('requiere_', '');
                updateServiceIndicator(service, this.checked);
            });
            
            // ARL específicamente necesita llevarnos a la pestaña correcta
            $('#requiere_arl').on('change', function() {
                if(this.checked) {
                    // Si está marcado, activar la pestaña "Otros Servicios"
                    $('a[href="#otros"]').tab('show');
                }
            });

            // Single form submission handler
            $('#salidaForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validate required fields
                let isValid = true;
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    Swal.fire({
                        title: "Error",
                        text: "Por favor complete todos los campos requeridos",
                        icon: "error",
                        confirmButtonColor: "#364E76"
                    });
                    return false;
                }

                // Handle checkbox values before submit
                $('input[type="checkbox"]').each(function() {
                    if (!$(this).is(':checked')) {
                        $(this).after('<input type="hidden" name="' + $(this).attr('name') + '" value="0">');
                    }
                });

                // Submit form
                this.submit();
            });

            // Date validation
            $('input[type="date"]').change(function() {
                var fecha_salida = $('input[name="fecha_salida"]').val();
                var fecha_regreso = $('input[name="fecha_regreso"]').val();
                
                if (fecha_salida && fecha_regreso && fecha_regreso < fecha_salida) {
                    Swal.fire({
                        title: "Error",
                        text: "La fecha de regreso no puede ser anterior a la fecha de salida",
                        icon: "error",
                        confirmButtonText: "Entendido",
                        confirmButtonColor: "#364E76"
                    });
                    $(this).val("");
                }
            });

            // Success and error messages
            @if(session('success'))
                Swal.fire({
                    title: "Éxito",
                    text: "{!! session('success') !!}",
                    icon: "success",
                    confirmButtonColor: "#364E76"
                });
            @endif

            // Aplicar el color institucional a los badges
            $('.badge-primary').css('background-color', '#364E76');

            @if(session('error'))
                Swal.fire({
                    title: "Error",
                    text: "{!! session('error') !!}",
                    icon: "error",
                    confirmButtonColor: "#364E76"
                });
            @endif

            // Mostrar detalles de visita de inspección si el switch está activado
            $('#visita_inspeccion').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#detalles_inspeccion_container').show();
                } else {
                    $('#detalles_inspeccion_container').hide();
                }
            });
        });
    </script>
@stop
