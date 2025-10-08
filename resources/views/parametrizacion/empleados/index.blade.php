@extends('adminlte::page')

@section('title', 'Gestión de Empleados')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-user-tie mr-2"></i>Información de Empleados
        </h1>
        <div>
            <a href="{{ route('empleados.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Nuevo Empleado
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

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle mr-1"></i>
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Estadísticas -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total de Empleados Activos</span>
                            <span class="info-box-number">{{ $estadisticas['total'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de empleados -->
            <div class="card">
                <div class="card-header" style="background-color: #3498db; color: white;">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>Lista de Empleados
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Documento</th>
                                <th>Email</th>
                                <th>Área</th>
                                <th>Sexo</th>
                                <th>Tipo de Sangre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empleados as $empleado)
                                <tr>
                                    <td>{{ $empleado->id }}</td>
                                    <td><strong>{{ $empleado->nombre_completo }}</strong></td>
                                    <td>{{ $empleado->documento }}</td>
                                    <td>{{ $empleado->email ?? 'No registrado' }}</td>
                                    <td>
                                        @if($empleado->area)
                                            <span class="badge badge-info">{{ $empleado->area }}</span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($empleado->sexo === 'M')
                                            <span class="badge badge-primary">Masculino</span>
                                        @elseif($empleado->sexo === 'F')
                                            <span class="badge badge-danger">Femenino</span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $empleado->tipo_sangre ?? 'N/A' }}</td>
                                    <td>
                                        @if($empleado->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('empleados.edit', $empleado->id) }}" class="btn btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('empleados.destroy', $empleado->id) }}" method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('¿Está seguro de desactivar este empleado?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Desactivar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No hay empleados registrados</p>
                                        <a href="{{ route('empleados.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus mr-1"></i>Registrar primer empleado
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $empleados->links() }}
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
