@extends('adminlte::page')

@section('title', 'Registro de Entrada/Salida - Portería')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-door-open"></i> Registro de Entrada/Salida</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-right">
                <span class="badge badge-info p-2" style="font-size: 1.1rem;">
                    <i class="fas fa-calendar"></i> {{ now()->format('d/m/Y') }}
                </span>
                <span class="badge badge-primary p-2 ml-2" style="font-size: 1.1rem;" id="reloj">
                    <i class="fas fa-clock"></i> <span id="hora-actual"></span>
                </span>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Formulario de Escaneo --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-barcode"></i> Escanear Documento</h3>
                </div>
                <div class="card-body">
                    <form id="form-registro" autocomplete="off">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-10">
                                <label for="documento" class="form-label">
                                    <i class="fas fa-id-card"></i> Número de Documento o Escanee el Carnet
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg" 
                                    id="documento" 
                                    name="documento" 
                                    placeholder="Ingrese el número de documento o use el lector de barras..."
                                    autofocus
                                    required
                                    style="font-size: 1.3rem; height: 60px;"
                                >
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success btn-lg btn-block" style="height: 60px;">
                                    <i class="fas fa-check-circle"></i> Registrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Mensaje de Confirmación --}}
    <div class="row mb-3" id="mensaje-confirmacion" style="display: none;">
        <div class="col-12">
            <div class="alert" id="alert-mensaje" role="alert">
                <h4 class="alert-heading" id="alert-titulo"></h4>
                <p id="alert-contenido"></p>
            </div>
        </div>
    </div>

    {{-- Modal para Visitantes --}}
    <div id="modal-visitante" class="modal-visitante" style="display: none;">
        <div class="modal-visitante-overlay"></div>
        <div class="modal-visitante-content">
            <div class="modal-visitante-header">
                <h3><i class="fas fa-user-plus"></i> Registro de Visitante</h3>
                <button type="button" class="modal-visitante-close" onclick="cerrarModalVisitante()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-visitante-body">
                <form id="form-visitante">
                    <input type="hidden" id="visitante-documento">
                    <div class="form-group-modal">
                        <label for="visitante-nombre">
                            <i class="fas fa-user"></i> Nombre <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="visitante-nombre" 
                            name="nombre" 
                            class="form-control-modal"
                            placeholder="Ingrese el nombre del visitante"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group-modal">
                        <label for="visitante-apellido">
                            <i class="fas fa-user"></i> Apellido <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="visitante-apellido" 
                            name="apellido" 
                            class="form-control-modal"
                            placeholder="Ingrese el apellido del visitante"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group-modal">
                        <label for="visitante-observaciones">
                            <i class="fas fa-comment-alt"></i> Observaciones
                        </label>
                        <textarea 
                            id="visitante-observaciones" 
                            name="observaciones" 
                            class="form-control-modal"
                            placeholder="Ingrese observaciones sobre la visita (opcional)"
                            rows="3"
                            autocomplete="off"
                            style="resize: vertical;"
                        ></textarea>
                    </div>
                    <div class="modal-visitante-footer">
                        <button type="button" class="btn-modal btn-modal-cancel" onclick="cerrarModalVisitante()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn-modal btn-modal-primary">
                            <i class="fas fa-check"></i> Registrar Entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Editar Registro (Solo Admin) --}}
    @can('admin.personas')
    <div id="modal-editar" class="modal-visitante" style="display: none;">
        <div class="modal-visitante-overlay"></div>
        <div class="modal-visitante-content" style="max-width: 600px;">
            <div class="modal-visitante-header">
                <h3><i class="fas fa-edit"></i> Editar Registro</h3>
                <button type="button" class="modal-visitante-close" onclick="cerrarModalEditar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-visitante-body">
                <form id="form-editar">
                    <input type="hidden" id="editar-id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label for="editar-documento">
                                    <i class="fas fa-id-card"></i> Documento <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="editar-documento" 
                                    class="form-control-modal"
                                    required
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label for="editar-tipo">
                                    <i class="fas fa-user-tag"></i> Tipo <span class="text-danger">*</span>
                                </label>
                                <select id="editar-tipo" class="form-control-modal" required>
                                    <option value="empleado">Empleado</option>
                                    <option value="estudiante">Estudiante</option>
                                    <option value="externo">Visitante</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label for="editar-nombre">
                                    <i class="fas fa-user"></i> Nombre <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="editar-nombre" 
                                    class="form-control-modal"
                                    required
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label for="editar-apellido">
                                    <i class="fas fa-user"></i> Apellido
                                </label>
                                <input 
                                    type="text" 
                                    id="editar-apellido" 
                                    class="form-control-modal"
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label for="editar-entrada">
                                    <i class="fas fa-clock"></i> Hora Entrada <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="time" 
                                    id="editar-entrada" 
                                    class="form-control-modal"
                                    required
                                >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label for="editar-salida">
                                    <i class="fas fa-clock"></i> Hora Salida
                                </label>
                                <input 
                                    type="time" 
                                    id="editar-salida" 
                                    class="form-control-modal"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="form-group-modal">
                        <label for="editar-observaciones">
                            <i class="fas fa-comment-alt"></i> Observaciones
                        </label>
                        <textarea 
                            id="editar-observaciones" 
                            class="form-control-modal"
                            placeholder="Observaciones sobre el registro (opcional)"
                            rows="3"
                            autocomplete="off"
                            style="resize: vertical;"
                        ></textarea>
                    </div>

                    <div class="modal-visitante-footer">
                        <button type="button" class="btn-modal btn-modal-cancel" onclick="cerrarModalEditar()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn-modal btn-modal-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Tabla de Registros del Día --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Registros del Día</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" class="form-control" id="filtro-fecha" value="{{ date('Y-m-d') }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default btn-sm" id="btn-hoy" title="Volver a hoy">
                                    <i class="fas fa-calendar-day"></i> Hoy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-muted">
                                    <i class="fas fa-calendar"></i> 
                                    Mostrando registros del: <strong id="fecha-mostrada">{{ date('d/m/Y') }}</strong>
                                </h5>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="badge badge-primary badge-lg" id="total-registros" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                    0 registros
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tabla-registros" class="table table-striped table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No. Documento</th>
                                    <th>Nombre Completo</th>
                                    <th>Estatus</th>
                                    <th>Fecha de Ingreso</th>
                                    <th>Hora de Entrada</th>
                                    <th>Hora de Salida</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        :root {
            --color-institucional: #233e6c;
            --color-institucional-hover: #1a2e50;
            --color-institucional-light: rgba(35, 62, 108, 0.1);
        }
        
        #documento:focus {
            border-color: var(--color-institucional);
            box-shadow: 0 0 0 0.2rem rgba(35, 62, 108, 0.25);
        }
        
        .alert {
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #reloj {
            font-family: 'Courier New', monospace;
        }
        
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .card-header {
            background: linear-gradient(to right, var(--color-institucional), var(--color-institucional-hover)) !important;
            color: white;
        }
        
        .card-primary.card-outline {
            border-top: 3px solid var(--color-institucional);
        }
        
        .btn-success {
            background-color: var(--color-institucional) !important;
            border-color: var(--color-institucional) !important;
        }
        
        .btn-success:hover {
            background-color: var(--color-institucional-hover) !important;
            border-color: var(--color-institucional-hover) !important;
        }
        
        .badge-primary {
            background-color: var(--color-institucional) !important;
        }
        
        .badge-info {
            background-color: var(--color-institucional) !important;
        }
        
        .thead-dark {
            background-color: var(--color-institucional) !important;
            color: white;
        }
        
        .table-striped tbody tr:hover {
            background-color: var(--color-institucional-light);
        }
        
        /* Estilos del Modal de Visitante */
        .modal-visitante {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-visitante-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-visitante-content {
            position: relative;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            animation: slideUp 0.3s ease;
            z-index: 10000;
        }
        
        .modal-visitante-header {
            background: linear-gradient(to right, var(--color-institucional), var(--color-institucional-hover));
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-visitante-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }
        
        .modal-visitante-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }
        
        .modal-visitante-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .modal-visitante-body {
            padding: 25px;
        }
        
        .form-group-modal {
            margin-bottom: 20px;
        }
        
        .form-group-modal label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control-modal {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control-modal:focus {
            outline: none;
            border-color: var(--color-institucional);
            box-shadow: 0 0 0 0.2rem rgba(35, 62, 108, 0.25);
        }
        
        .modal-visitante-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
        }
        
        .btn-modal {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .btn-modal-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn-modal-cancel:hover {
            background: #5a6268;
        }
        
        .btn-modal-primary {
            background: var(--color-institucional);
            color: white;
        }
        
        .btn-modal-primary:hover {
            background: var(--color-institucional-hover);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Actualizar reloj cada segundo
    function actualizarReloj() {
        const ahora = new Date();
        const horas = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        $('#hora-actual').text(`${horas}:${minutos}:${segundos}`);
    }
    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    // Inicializar DataTable
    const tabla = $('#tabla-registros').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('porteria.registro.index') }}',
            type: 'GET',
            data: function(d) {
                d.fecha = $('#filtro-fecha').val();
            }
        },
        columns: [
            { data: 'documento', name: 'documento' },
            { data: 'nombre_completo', name: 'nombre_completo' },
            { data: 'estatus_badge', name: 'tipo_persona', orderable: false },
            { data: 'fecha_formatted', name: 'fecha' },
            { data: 'hora_entrada_formatted', name: 'hora_entrada' },
            { data: 'hora_salida_formatted', name: 'hora_salida' },
            { data: 'observaciones_formatted', name: 'observaciones', orderable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']], // Ordenar por hora de entrada descendente
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        drawCallback: function(settings) {
            const api = this.api();
            $('#total-registros').text(api.page.info().recordsTotal + ' registros');
        }
    });

    // Manejar cambio de fecha
    $('#filtro-fecha').on('change', function() {
        const fechaSeleccionada = $(this).val();
        const fecha = new Date(fechaSeleccionada + 'T00:00:00');
        const fechaFormateada = fecha.toLocaleDateString('es-ES', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric' 
        });
        $('#fecha-mostrada').text(fechaFormateada);
        tabla.ajax.reload();
    });

    // Botón para volver a hoy
    $('#btn-hoy').on('click', function() {
        const hoy = new Date().toISOString().split('T')[0];
        $('#filtro-fecha').val(hoy).trigger('change');
    });

    // Manejar el envío del formulario
    $('#form-registro').on('submit', function(e) {
        e.preventDefault();
        
        const documento = $('#documento').val().trim();
        
        if (!documento) {
            mostrarMensaje('warning', '¡Atención!', 'Por favor ingrese un número de documento.');
            $('#documento').focus();
            return;
        }

        // Deshabilitar el botón temporalmente
        const btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        // Primero verificar si es visitante
        $.ajax({
            url: '{{ route('porteria.registro.verificar') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                documento: documento
            },
            success: function(response) {
                console.log('✅ Respuesta de verificación:', response);
                console.log('🔍 es_visitante:', response.es_visitante);
                console.log('🔍 tipo:', response.tipo);
                
                if (response.es_visitante) {
                    // Es visitante, abrir modal
                    console.log('👤 VISITANTE DETECTADO - Abriendo modal');
                    abrirModalVisitante(documento);
                    btnSubmit.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Registrar');
                } else {
                    // No es visitante, registrar normalmente
                    console.log('✅ PERSONA REGISTRADA - Tipo:', response.tipo_persona || response.tipo);
                    console.log('📝 Llamando a registrarPersona() sin nombre/apellido');
                    registrarPersona(documento, null, null);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error al verificar:', status, error);
                console.error('Response:', xhr.responseJSON);
                btnSubmit.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Registrar');
                mostrarMensaje('danger', '✗ Error', 'Error al verificar el documento.');
            }
        });
    });

    // Función para registrar persona
    function registrarPersona(documento, nombre, apellido, observaciones) {
        console.log('🚀 registrarPersona() llamada con:', {
            documento: documento,
            nombre: nombre,
            apellido: apellido,
            observaciones: observaciones
        });
        
        const btnSubmit = $('#form-registro').find('button[type="submit"]');
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        // Enviar petición AJAX
        $.ajax({
            url: '{{ route('porteria.registro.store') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                documento: documento,
                nombre: nombre,
                apellido: apellido,
                observaciones: observaciones
            },
            success: function(response) {
                console.log('✅ Respuesta del registro:', response);
                
                if (response.success) {
                    const tipo = response.action === 'entrada' ? 'success' : 'info';
                    const titulo = response.action === 'entrada' ? '✓ Entrada Registrada' : '✓ Salida Registrada';
                    
                    console.log('✅ Registro exitoso:', response.action, '-', response.message);
                    mostrarMensaje(tipo, titulo, response.message);
                    
                    // Reproducir sonido de éxito
                    reproducirSonido(response.action);
                    
                    // Recargar tabla
                    tabla.ajax.reload(null, false);
                    
                    // Limpiar y enfocar el campo
                    $('#documento').val('').focus();
                } else {
                    console.error('❌ Error en respuesta:', response.message);
                    mostrarMensaje('danger', '✗ Error', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error AJAX al registrar:', status, error);
                console.error('Response:', xhr.responseJSON);
                console.error('Status Code:', xhr.status);
                
                let mensaje = 'Error al procesar el registro. Por favor intente nuevamente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                mostrarMensaje('danger', '✗ Error', mensaje);
            },
            complete: function() {
                // Rehabilitar el botón
                btnSubmit.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Registrar');
            }
        });
    }

    // Manejar envío del formulario de visitante
    $('#form-visitante').on('submit', function(e) {
        e.preventDefault();
        
        const documento = $('#visitante-documento').val();
        const nombre = $('#visitante-nombre').val().trim();
        const apellido = $('#visitante-apellido').val().trim();
        const observaciones = $('#visitante-observaciones').val().trim();
        
        if (!nombre || !apellido) {
            alert('Por favor complete todos los campos.');
            return;
        }
        
        cerrarModalVisitante();
        registrarPersona(documento, nombre, apellido, observaciones);
    });

    // Función para mostrar mensajes
    function mostrarMensaje(tipo, titulo, contenido) {
        const colores = {
            'success': 'alert-success',
            'info': 'alert-info',
            'warning': 'alert-warning',
            'danger': 'alert-danger'
        };
        
        $('#alert-mensaje').removeClass().addClass('alert ' + colores[tipo]);
        $('#alert-titulo').html('<i class="fas fa-info-circle"></i> ' + titulo);
        $('#alert-contenido').text(contenido);
        $('#mensaje-confirmacion').fadeIn().delay(5000).fadeOut();
    }

    // Función para reproducir sonido (opcional)
    function reproducirSonido(tipo) {
        // Se puede agregar un audio posteriormente
        // const audio = new Audio(tipo === 'entrada' ? '/sounds/entrada.mp3' : '/sounds/salida.mp3');
        // audio.play();
    }

    // Mantener el foco en el campo de documento
    $(document).on('click', function(e) {
        if (!$(e.target).is('input, button, a, select, textarea')) {
            $('#documento').focus();
        }
    });

    // Auto-enfocar después de cerrar mensajes
    $('#mensaje-confirmacion').on('hidden.bs.alert', function() {
        $('#documento').focus();
    });
});

// Función para abrir modal de visitante
function abrirModalVisitante(documento) {
    $('#visitante-documento').val(documento);
    $('#visitante-nombre').val('');
    $('#visitante-apellido').val('');
    $('#visitante-observaciones').val('');
    $('#modal-visitante').fadeIn(300);
    
    // Enfocar el campo de nombre
    setTimeout(function() {
        $('#visitante-nombre').focus();
    }, 300);
}

// Función para cerrar modal de visitante
function cerrarModalVisitante() {
    $('#modal-visitante').fadeOut(300);
    
    // Enfocar el campo de documento
    setTimeout(function() {
        $('#documento').focus();
    }, 300);
}

// Cerrar modal al hacer clic en el overlay
$(document).on('click', '.modal-visitante-overlay', function() {
    cerrarModalVisitante();
});

// Cerrar modal con tecla ESC
$(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#modal-visitante').is(':visible')) {
        cerrarModalVisitante();
    }
});

@can('admin.personas')
// Manejar clic en botón editar
$(document).on('click', '.btn-editar', function() {
    const id = $(this).data('id');
    console.log('✏️ Botón editar clickeado - ID:', id);
    
    // Cargar datos del registro
    $.ajax({
        url: '{{ url('porteria/registro') }}/' + id + '/edit',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                console.log('📝 Datos cargados:', response.data);
                const data = response.data;
                
                // Llenar el formulario
                $('#editar-id').val(data.id);
                $('#editar-documento').val(data.documento);
                $('#editar-nombre').val(data.nombre);
                $('#editar-apellido').val(data.apellido);
                $('#editar-tipo').val(data.tipo_persona);
                $('#editar-entrada').val(data.hora_entrada);
                $('#editar-salida').val(data.hora_salida);
                $('#editar-observaciones').val(data.observaciones);
                
                // Mostrar modal
                $('#modal-editar').fadeIn(300);
                setTimeout(function() {
                    $('#editar-documento').focus();
                }, 300);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            let mensaje = 'Error al cargar los datos del registro';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire('Error', mensaje, 'error');
        }
    });
});

// Manejar envío del formulario de edición
$('#form-editar').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#editar-id').val();
    const formData = {
        documento: $('#editar-documento').val(),
        nombre: $('#editar-nombre').val(),
        apellido: $('#editar-apellido').val(),
        tipo_persona: $('#editar-tipo').val(),
        hora_entrada: $('#editar-entrada').val(),
        hora_salida: $('#editar-salida').val(),
        observaciones: $('#editar-observaciones').val()
    };
    
    console.log('💾 Guardando cambios:', formData);
    
    $.ajax({
        url: '{{ url('porteria/registro') }}/' + id,
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            ...formData
        },
        success: function(response) {
            if (response.success) {
                cerrarModalEditar();
                Swal.fire('Actualizado', response.message, 'success');
                tabla.ajax.reload(null, false);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            let mensaje = 'Error al actualizar el registro';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire('Error', mensaje, 'error');
        }
    });
});

// Función para cerrar modal de editar
function cerrarModalEditar() {
    $('#modal-editar').fadeOut(300);
    $('#form-editar')[0].reset();
}

// Cerrar modal de editar con ESC
$(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#modal-editar').is(':visible')) {
        cerrarModalEditar();
    }
});

// Cerrar modal de editar al hacer clic en overlay
$(document).on('click', '#modal-editar .modal-visitante-overlay', function() {
    cerrarModalEditar();
});

// Manejar clic en botón eliminar
$(document).on('click', '.btn-eliminar', function() {
    const id = $(this).data('id');
    console.log('🗑️ Botón eliminar clickeado - ID:', id);
    console.log('📍 URL a llamar:', '{{ url('porteria/registro') }}/' + id);
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('✅ Usuario confirmó eliminación');
            $.ajax({
                url: '{{ url('porteria/registro') }}/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', response.message, 'success');
                        tabla.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error al eliminar el registro';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }
    });
});
@endcan
</script>
@stop
