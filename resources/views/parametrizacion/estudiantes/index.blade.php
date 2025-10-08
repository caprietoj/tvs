@extends('adminlte::page')

@section('title', 'Gestión de Estudiantes - Parametrización')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-users mr-2"></i>Gestión de Estudiantes
        </h1>
        <div>
            <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#importModal">
                <i class="fas fa-file-excel mr-1"></i>Importar desde Excel
            </button>
            <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Nuevo Estudiante
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
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-users"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Estudiantes</span>
                            <span class="info-box-number">{{ $estadisticas['total'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-2"></i>Distribución por Curso
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($estadisticas['por_curso'] as $curso => $total)
                                    <div class="col-md-2 col-sm-4 col-6">
                                        <div class="description-block border-right">
                                            <span class="description-percentage text-success">
                                                <i class="fas fa-graduation-cap"></i>
                                            </span>
                                            <h5 class="description-header">{{ $total }}</h5>
                                            <span class="description-text">{{ $curso }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de estudiantes -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>Lista de Estudiantes
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="estudiantes-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre Completo</th>
                                    <th>Curso</th>
                                    <th>Documento</th>
                                    <th>EPS</th>
                                    <th>Sexo</th>
                                    <th>Tipo Sangre</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estudiantes as $estudiante)
                                    <tr>
                                        <td><strong>{{ $estudiante->codigo }}</strong></td>
                                        <td>{{ $estudiante->nombre_completo }}</td>
                                        <td>
                                            <span class="badge badge-primary">{{ $estudiante->curso }}</span>
                                        </td>
                                        <td>{{ $estudiante->documento }}</td>
                                        <td>{{ $estudiante->eps ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $estudiante->sexo == 'M' ? 'badge-info' : 'badge-pink' }}">
                                                {{ $estudiante->sexo == 'M' ? 'Masculino' : 'Femenino' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($estudiante->tipo_sangre)
                                                <span class="badge badge-secondary">{{ $estudiante->tipo_sangre }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($estudiante->activo)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('estudiantes.edit', $estudiante) }}" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" 
                                                      action="{{ route('estudiantes.toggle-active', $estudiante) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-{{ $estudiante->activo ? 'secondary' : 'success' }} btn-sm"
                                                            title="{{ $estudiante->activo ? 'Desactivar' : 'Activar' }}"
                                                            onclick="return confirm('¿Está seguro?')">
                                                        <i class="fas fa-{{ $estudiante->activo ? 'eye-slash' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" 
                                                      action="{{ route('estudiantes.destroy', $estudiante) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger btn-sm"
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Está seguro de eliminar este estudiante?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            No hay estudiantes registrados. Use "Nuevo Estudiante" o "Importar desde Excel".
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($estudiantes->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $estudiantes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Importación -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-file-excel mr-2"></i>Importar Estudiantes desde Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('estudiantes.import') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle mr-1"></i> Instrucciones:</h6>
                            <ol class="mb-0">
                                <li>Copie los datos desde Excel (incluya los encabezados)</li>
                                <li>Pegue en el área de texto de abajo</li>
                                <li>Estructura requerida: <strong>Curso | Nombre | Apellido 1 | Apellido 2 | Código | Documento | EPS | Sexo | Tipo de sangre</strong></li>
                                <li>Los campos Apellido 2, EPS y Tipo de sangre son opcionales</li>
                                <li>Sexo: M/F o Masculino/Femenino</li>
                                <li>Tipo de sangre: A+, A-, B+, B-, AB+, AB-, O+, O-</li>
                            </ol>
                        </div>

                        <div class="form-group">
                            <label for="datos_excel">
                                <i class="fas fa-paste mr-1"></i>Datos desde Excel:
                            </label>
                            <textarea class="form-control" 
                                      id="datos_excel" 
                                      name="datos_excel" 
                                      rows="15" 
                                      placeholder="Pegue aquí los datos copiados desde Excel..."
                                      required></textarea>
                            <small class="form-text text-muted">
                                Ejemplo:<br>
                                Curso&nbsp;&nbsp;&nbsp;&nbsp;Nombre&nbsp;&nbsp;&nbsp;&nbsp;Apellido 1&nbsp;&nbsp;&nbsp;&nbsp;Apellido 2&nbsp;&nbsp;&nbsp;&nbsp;Código&nbsp;&nbsp;&nbsp;&nbsp;Documento&nbsp;&nbsp;&nbsp;&nbsp;EPS&nbsp;&nbsp;&nbsp;&nbsp;Sexo&nbsp;&nbsp;&nbsp;&nbsp;Tipo de sangre<br>
                                5°&nbsp;&nbsp;&nbsp;&nbsp;Juan&nbsp;&nbsp;&nbsp;&nbsp;Pérez&nbsp;&nbsp;&nbsp;&nbsp;García&nbsp;&nbsp;&nbsp;&nbsp;20251001&nbsp;&nbsp;&nbsp;&nbsp;12345678&nbsp;&nbsp;&nbsp;&nbsp;Sura&nbsp;&nbsp;&nbsp;&nbsp;M&nbsp;&nbsp;&nbsp;&nbsp;O+
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-upload mr-1"></i>Importar Estudiantes
                        </button>
                    </div>
                </form>
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
        
        .badge-pink {
            background-color: #e91e63;
            color: white;
        }
        
        .description-block {
            text-align: center;
            padding: 10px;
        }
        
        .description-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .description-text {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
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
                order: [[1, 'asc']], // Ordenar por nombre
                columnDefs: [
                    { orderable: false, targets: [8] } // Deshabilitar ordenamiento en acciones
                ],
                pageLength: 25
            });
        });
    </script>
@stop