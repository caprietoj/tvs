@extends('adminlte::page')

@section('title', 'Salidas Pedagógicas')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-route mr-2"></i>Salidas Pedagógicas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Salidas</li>
                </ol>
            </div>
        </div>
        @if(!auth()->user()->hasRole('profesor'))
        <div class="row mb-2">
            <div class="col-sm-12">
                <a href="{{ route('salidas.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus mr-2"></i> Nueva Salida Pedagógica
                </a>
            </div>
        </div>
        @endif
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Estadísticas Rápidas -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $salidas->count() }}</h3>
                        <p>Total Salidas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-route"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $salidas->where('estado', 'Realizada')->count() }}</h3>
                        <p>Realizadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $salidas->where('estado', 'Programada')->count() }}</h3>
                        <p>Programadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $salidas->where('estado', 'Cancelada')->count() }}</h3>
                        <p>Canceladas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="filtro-estado">Estado:</label>
                        <select id="filtro-estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="Programada">Programada</option>
                            <option value="Realizada">Realizada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro-mes">Mes:</label>
                        <select id="filtro-mes" class="form-control">
                            <option value="">Todos</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro-grado">Grado:</label>
                        <input type="text" id="filtro-grado" class="form-control" placeholder="Buscar por grado...">
                    </div>
                    <div class="col-md-3">
                        <label for="filtro-lugar">Lugar:</label>
                        <input type="text" id="filtro-lugar" class="form-control" placeholder="Buscar por lugar...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Principal -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Listado de Salidas Pedagógicas</h3>
                <div class="card-tools">
                    <span class="badge badge-primary">{{ $salidas->count() }} registros</span>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table id="salidas-table" class="table table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 80px;">Consecutivo</th>
                            <th style="width: 100px;">Fecha Solicitud</th>
                            <th style="width: 120px;">Grados</th>
                            <th>Lugar</th>
                            <th style="width: 150px;">Responsable</th>
                            <th style="width: 140px;">Fecha Salida</th>
                            <th style="width: 120px;">Progreso</th>
                            <th style="width: 100px;">Estado</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salidas as $salida)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $salida->consecutivo }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $salida->fecha_solicitud->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-info badge-lg">{{ $salida->grados }}</span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ Str::limit($salida->lugar, 30) }}</strong>
                                        @if(strlen($salida->lugar) > 30)
                                            <i class="fas fa-info-circle text-muted" title="{{ $salida->lugar }}"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-users mr-1"></i>{{ $salida->cantidad_pasajeros }} pasajeros
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-circle text-primary mr-2"></i>
                                        <span>{{ $salida->responsable->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $salida->fecha_salida->format('d/m/Y') }}</strong>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>{{ $salida->fecha_salida->format('H:i') }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $servicios = [
                                            'transporte' => $salida->transporte_confirmado,
                                            'alimentacion' => $salida->requiere_alimentacion ? $salida->alimentacion_confirmada : true,
                                            'enfermeria' => $salida->requiere_enfermeria ? $salida->enfermeria_confirmada : true,
                                            'accesos' => $salida->accesos_confirmados,
                                            'comunicaciones' => $salida->requiere_comunicaciones ? $salida->comunicaciones_confirmada : true,
                                            'arl' => $salida->requiere_arl ? $salida->arl_confirmado : true
                                        ];
                                        $confirmados = collect($servicios)->filter()->count();
                                        $total = count($servicios);
                                        $porcentaje = ($confirmados / $total) * 100;
                                    @endphp
                                    
                                    <div class="progress mb-1" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $porcentaje == 100 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ $porcentaje }}%;" 
                                             aria-valuenow="{{ $porcentaje }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ round($porcentaje) }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $confirmados }}/{{ $total }} servicios</small>
                                </td>
                                <td>
                                    @if($salida->estado === 'Programada')
                                        <span class="badge badge-primary badge-lg">
                                            <i class="fas fa-calendar-check mr-1"></i>{{ $salida->estado }}
                                        </span>
                                    @elseif($salida->estado === 'Realizada')
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-check-circle mr-1"></i>{{ $salida->estado }}
                                        </span>
                                    @else
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-times-circle mr-1"></i>{{ $salida->estado }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group-vertical" role="group">
                                        <a href="{{ route('salidas.show', $salida) }}" 
                                           class="btn btn-info btn-sm mb-1" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                        @if(auth()->user()->email === 'jefesistemas@tvs.edu.co')
                                            <a href="{{ route('salidas.informe', $salida) }}" target="_blank"
                                               class="btn btn-secondary btn-sm mb-1" 
                                               title="Descargar informe detallado (solo administración)">
                                                <i class="fas fa-file-pdf mr-1"></i>Informe
                                            </a>
                                        @endif
                                        @if(!auth()->user()->hasRole('profesor'))
                                            <a href="{{ route('salidas.edit', $salida) }}" 
                                               class="btn btn-warning btn-sm mb-1" 
                                               title="Editar">
                                                <i class="fas fa-edit mr-1"></i>Editar
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    onclick="confirmarEliminacion('{{ $salida->id }}')"
                                                    title="Eliminar">
                                                <i class="fas fa-trash mr-1"></i>Eliminar
                                            </button>
                                            <form id="form-eliminar-{{ $salida->id }}" 
                                                action="{{ route('salidas.destroy', $salida) }}" 
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <style>
        .content-header h1 {
            color: #364E76;
            font-weight: bold;
        }
        
        .card-header { 
            background: linear-gradient(135deg, #364E76 0%, #2B3E5F 100%) !important; 
            color: white; 
            border: none;
        }
        
        .card-outline.card-primary {
            border-top: 3px solid #364E76;
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, #364E76 0%, #2B3E5F 100%); 
            border-color: #364E76; 
            box-shadow: 0 2px 4px rgba(54, 78, 118, 0.3);
        }
        
        .btn-primary:hover { 
            background: linear-gradient(135deg, #2B3E5F 0%, #1F2D47 100%); 
            border-color: #2B3E5F; 
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(54, 78, 118, 0.4);
        }
        
        .small-box {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .small-box:hover {
            transform: translateY(-2px);
        }
        
        .table th {
            background: linear-gradient(135deg, #364E76 0%, #2B3E5F 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(54, 78, 118, 0.1);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        .badge-lg {
            font-size: 0.9em;
            padding: 0.5em 0.7em;
        }
        
        .progress {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            transition: width 0.6s ease;
        }
        
        .dt-buttons {
            margin-bottom: 15px;
        }
        
        .dt-buttons .btn {
            background: linear-gradient(135deg, #364E76 0%, #2B3E5F 100%);
            border-color: #364E76;
            border-radius: 20px;
            padding: 8px 16px;
            margin-right: 5px;
        }
        
        .dt-buttons .btn:hover {
            background: linear-gradient(135deg, #2B3E5F 0%, #1F2D47 100%);
            border-color: #2B3E5F;
            transform: translateY(-1px);
        }
        
        .btn-group-vertical .btn {
            border-radius: 4px;
            margin-bottom: 2px;
        }
        
        .btn-group-vertical .btn:last-child {
            margin-bottom: 0;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .filter-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .custom-tooltip {
            position: relative;
            cursor: help;
        }
        
        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .btn-group-vertical .btn {
                font-size: 0.8em;
                padding: 4px 8px;
            }
            
            .small-box .inner h3 {
                font-size: 1.5em;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configuración de botones
            var buttonsConfig = [];
            
            @if(!auth()->user()->hasRole('profesor'))
            buttonsConfig.push({
                extend: 'excel',
                text: '<i class="fas fa-file-excel mr-1"></i> Exportar a Excel',
                className: 'btn btn-sm btn-success',
                title: 'Salidas Pedagógicas - ' + new Date().toLocaleDateString(),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7] // Exclude actions column
                }
            });
            
            buttonsConfig.push({
                text: '<i class="fas fa-sync-alt mr-1"></i> Actualizar',
                className: 'btn btn-sm btn-info',
                action: function(e, dt, node, config) {
                    location.reload();
                }
            });
            @endif

            // Inicializar DataTable
            var table = $('#salidas-table').DataTable({
                language: {
                    url: '{{ asset("js/dataTables.spanish.js") }}'
                },
                order: [[0, 'desc']],
                dom: 'Bfrtip',
                buttons: buttonsConfig,
                pageLength: 25,
                responsive: true,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: -1 },
                    { responsivePriority: 3, targets: [3, 7] }
                ]
            });

            // Filtros personalizados
            $('#filtro-estado').on('change', function() {
                var valor = this.value;
                table.column(7).search(valor).draw();
            });

            $('#filtro-mes').on('change', function() {
                var valor = this.value;
                if (valor) {
                    table.column(5).search('/' + valor + '/').draw();
                } else {
                    table.column(5).search('').draw();
                }
            });

            $('#filtro-grado').on('keyup', function() {
                table.column(2).search(this.value).draw();
            });

            $('#filtro-lugar').on('keyup', function() {
                table.column(3).search(this.value).draw();
            });

            // Función para limpiar filtros
            function limpiarFiltros() {
                $('#filtro-estado').val('');
                $('#filtro-mes').val('');
                $('#filtro-grado').val('');
                $('#filtro-lugar').val('');
                table.search('').columns().search('').draw();
            }

            // Agregar botón para limpiar filtros
            $('.card-tools:first').prepend(`
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="limpiarFiltros()">
                    <i class="fas fa-eraser mr-1"></i>Limpiar Filtros
                </button>
            `);

            // Hacer la función global
            window.limpiarFiltros = limpiarFiltros;

            // Tooltips para elementos con títulos
            $('[title]').tooltip();

            // Animación al cargar
            $('.small-box').each(function(index) {
                $(this).delay(index * 100).animate({
                    opacity: 1,
                    transform: 'translateY(0)'
                }, 500);
            });

            // Contador de registros filtrados
            table.on('draw', function() {
                var info = table.page.info();
                var total = info.recordsTotal;
                var filtrados = info.recordsDisplay;
                
                $('.card-tools .badge').text(
                    filtrados + (filtrados !== total ? ' de ' + total : '') + ' registros'
                );
            });
        });

        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#364E76',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    document.getElementById('form-eliminar-' + id).submit();
                }
            });
        }

        // Mostrar mensajes de éxito
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#364E76',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        // Mostrar mensajes de error
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#364E76'
            });
        @endif

        // Funcionalidad adicional: Click en las estadísticas para filtrar
        $('.small-box').on('click', function() {
            var estado = '';
            if ($(this).find('.bg-success').length) {
                estado = 'Realizada';
            } else if ($(this).find('.bg-warning').length) {
                estado = 'Programada';
            } else if ($(this).find('.bg-danger').length) {
                estado = 'Cancelada';
            }
            
            if (estado) {
                $('#filtro-estado').val(estado).trigger('change');
                $('html, body').animate({
                    scrollTop: $('#salidas-table').offset().top - 100
                }, 800);
            }
        });
    </script>
@stop
