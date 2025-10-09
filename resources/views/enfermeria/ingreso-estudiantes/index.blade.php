@extends('adminlte::page')

@section('title', 'Atención de Estudiantes - Enfermería')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-user-plus mr-2"></i>Atención de Estudiantes
        </h1>
        <div>
            <a href="{{ route('enfermeria.reporte_estudiantes') }}" class="btn btn-success mr-2">
                <i class="fas fa-file-excel mr-1"></i>Reporte
            </a>
            <a href="{{ route('enfermeria.ingreso_estudiantes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Nueva Atención
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-1"></i>
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header" style="background-color: #e74c3c; color: white;">
                    <h3 class="card-title">
                        <i class="fas fa-heartbeat mr-2"></i>Registros de Atención de Estudiantes
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Atenciones</span>
                                    <span class="info-box-number">{{ $totalAtenciones }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">Historial completo</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-calendar-day"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Atenciones Hoy</span>
                                    <span class="info-box-number">{{ $atencionesHoy }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: {{ $totalAtenciones > 0 ? ($atencionesHoy / $totalAtenciones) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="progress-description">{{ date('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-calendar-week"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Esta Semana</span>
                                    <span class="info-box-number">{{ $atencionesEstaSemana }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: {{ $totalAtenciones > 0 ? ($atencionesEstaSemana / $totalAtenciones) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="progress-description">Semana actual</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-hospital"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Derivaciones</span>
                                    <span class="info-box-number">{{ $derivaciones }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: {{ $totalAtenciones > 0 ? ($derivaciones / $totalAtenciones) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="progress-description">Requieren seguimiento</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="estudiantes-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estudiante</th>
                                    <th>Curso</th>
                                    <th>Motivo</th>
                                    <th>Derivación</th>
                                    <th>Registrado por</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ingresos as $ingreso)
                                    <tr>
                                        <td>{{ $ingreso->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $ingreso->hora }}</td>
                                        <td>
                                            <strong>{{ $ingreso->estudiante }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $ingreso->curso }}</span>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $ingreso->motivo }}">
                                                {{ $ingreso->motivo }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($ingreso->derivacion_estudiante)
                                                <span class="badge badge-warning">{{ $ingreso->derivacion_estudiante }}</span>
                                            @else
                                                <span class="text-muted">Sin derivación</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ingreso->user)
                                                <small class="text-muted">{{ $ingreso->user->name }}</small>
                                            @else
                                                <small class="text-muted">Usuario eliminado</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-info btn-sm" 
                                                        onclick="verDetalle({{ $ingreso->id }})" 
                                                        title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm" 
                                                        onclick="editarIngreso({{ $ingreso->id }})" 
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        onclick="eliminarIngreso({{ $ingreso->id }})" 
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            No hay registros disponibles. Haga clic en "Nueva Atención" para agregar el primer registro.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($ingresos->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $ingresos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: .25rem;
            background-color: #fff;
            display: flex;
            margin-bottom: 1rem;
            min-height: 80px;
            padding: .5rem;
            position: relative;
            width: 100%;
        }
        
        .info-box-content {
            flex: 1;
            padding: 5px 10px;
        }
        
        .info-box-number {
            font-size: 2.2rem !important;
            font-weight: 700;
        }
        
        .progress {
            height: 4px !important;
            margin: 5px 0;
        }
        
        .progress-description {
            font-size: 12px;
            color: #6c757d;
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        
        .btn-primary {
            background-color: #e74c3c;
            border-color: #e74c3c;
        }
        
        .btn-primary:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
        
        .badge-primary {
            background-color: #007bff;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#estudiantes-table').DataTable({
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                order: [[0, 'desc'], [1, 'desc']], // Ordenar por fecha y hora descendente
                columnDefs: [
                    { orderable: false, targets: [7] } // Deshabilitar ordenamiento en la columna de acciones
                ]
            });
        });

        function verDetalle(id) {
            // Redirigir a la ruta de show (o abrir modal)
            window.location.href = '/enfermeria/ingreso-estudiantes/' + id;
        }

        function editarIngreso(id) {
            // Redirigir a la página de edición
            window.location.href = '/enfermeria/ingreso-estudiantes/' + id + '/edit';
        }

        function eliminarIngreso(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implementar eliminación
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'Funcionalidad de eliminación en desarrollo',
                        icon: 'success'
                    });
                }
            });
        }
    </script>
@stop