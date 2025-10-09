@extends('adminlte::page')

@section('title', 'Atención de Colaboradores - Enfermería')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-user-tie mr-2"></i>Atención de Colaboradores
        </h1>
        <div>
            <a href="{{ route('enfermeria.reporte_colaboradores') }}" class="btn btn-success mr-2">
                <i class="fas fa-file-excel mr-1"></i>Reporte
            </a>
            <a href="{{ route('enfermeria.ingreso_colaboradores.create') }}" class="btn btn-primary">
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

            <!-- Estadísticas -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Atenciones</span>
                            <span class="info-box-number">{{ $totalAtenciones }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-calendar-day"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Atenciones Hoy</span>
                            <span class="info-box-number">{{ $atencionesHoy }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Esta Semana</span>
                            <span class="info-box-number">{{ $atencionesEstaSemana }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de registros -->
            <div class="card">
                <div class="card-header" style="background-color: #3498db; color: white;">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>Registros de Atención
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Colaborador</th>
                                <th>Documento</th>
                                <th>Área</th>
                                <th>Email</th>
                                <th>Motivo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ingresos as $ingreso)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ingreso->hora)->format('H:i') }}</td>
                                    <td>
                                        <strong>{{ $ingreso->nombre_completo }}</strong>
                                    </td>
                                    <td>{{ $ingreso->documento_colaborador }}</td>
                                    <td>
                                        @if($ingreso->area_colaborador)
                                            <span class="badge badge-info">{{ $ingreso->area_colaborador }}</span>
                                        @else
                                            <span class="badge badge-secondary">No registrado</span>
                                        @endif
                                    </td>
                                    <td>{{ $ingreso->email ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-warning">{{ Str::limit($ingreso->motivo, 50) }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" 
                                                    class="btn btn-info" 
                                                    onclick="verDetalle({{ $ingreso->id }})"
                                                    title="Ver Detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-warning" 
                                                    onclick="editarIngreso({{ $ingreso->id }})"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    onclick="eliminarIngreso({{ $ingreso->id }})"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No hay registros de atención de colaboradores</p>
                                        <a href="{{ route('enfermeria.ingreso_colaboradores.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus mr-1"></i>Crear primer registro
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $ingresos->links() }}
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
        .info-box-icon {
            border-radius: .25rem;
            align-items: center;
            display: flex;
            font-size: 1.875rem;
            justify-content: center;
            text-align: center;
            width: 70px;
        }
        .info-box-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.8;
            flex: 1;
            padding: 0 10px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Función para ver detalle
        function verDetalle(id) {
            window.location.href = '/enfermeria/ingreso-colaboradores/' + id;
        }

        // Función para editar
        function editarIngreso(id) {
            window.location.href = '/enfermeria/ingreso-colaboradores/' + id + '/edit';
        }

        // Función para eliminar
        function eliminarIngreso(id) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear formulario dinámico para DELETE
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/enfermeria/ingreso-colaboradores/' + id;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    
                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@stop
