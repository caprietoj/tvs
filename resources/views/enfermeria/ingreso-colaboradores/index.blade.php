@extends('adminlte::page')

@section('title', 'Atención de Colaboradores - Enfermería')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-user-tie mr-2"></i>Atención de Colaboradores
        </h1>
        <div>
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
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalVer{{ $ingreso->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Ver Detalles -->
                                <div class="modal fade" id="modalVer{{ $ingreso->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-info-circle mr-2"></i>Detalles de Atención
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</p>
                                                        <p><strong>Hora:</strong> {{ \Carbon\Carbon::parse($ingreso->hora)->format('H:i') }}</p>
                                                        <p><strong>Colaborador:</strong> {{ $ingreso->nombre_completo }}</p>
                                                        <p><strong>Documento:</strong> {{ $ingreso->documento_colaborador }}</p>
                                                        <p><strong>Email:</strong> {{ $ingreso->email ?? 'N/A' }}</p>
                                                        <p><strong>Área:</strong> 
                                                            @if($ingreso->area_colaborador)
                                                                <span class="badge badge-info">{{ $ingreso->area_colaborador }}</span>
                                                            @else
                                                                <span class="badge badge-secondary">No registrado</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>EPS:</strong> {{ $ingreso->eps_colaborador ?? 'N/A' }}</p>
                                                        <p><strong>Sexo:</strong> {{ $ingreso->sexo_colaborador ?? 'N/A' }}</p>
                                                        <p><strong>Tipo de Sangre:</strong> {{ $ingreso->tipo_sangre_colaborador ?? 'N/A' }}</p>
                                                        <p><strong>Registrado por:</strong> {{ $ingreso->usuario->name ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p><strong>Motivo:</strong> {{ $ingreso->motivo }}</p>
                                                <p><strong>Descripción del Evento:</strong><br>{{ $ingreso->descripcion_evento }}</p>
                                                <p><strong>Acción de Enfermería:</strong><br>{{ $ingreso->accion_enfermeria }}</p>
                                                @if($ingreso->seguimiento)
                                                    <p><strong>Seguimiento:</strong><br>{{ $ingreso->seguimiento }}</p>
                                                @endif
                                                @if($ingreso->encuesta)
                                                    <p><strong>Encuesta de Satisfacción:</strong> {{ $ingreso->encuesta }}</p>
                                                @endif
                                                @if($ingreso->encuesta_observaciones)
                                                    <p><strong>Observaciones de Encuesta:</strong><br>{{ $ingreso->encuesta_observaciones }}</p>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    </script>
@stop
