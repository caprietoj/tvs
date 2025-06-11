@extends('adminlte::page')

@section('title', 'Informe de Fotocopias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-print mr-2"></i>Informe del Servicio de Fotocopias</h1>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#filtersModal">
                <i class="fas fa-filter mr-1"></i>Filtros
            </button>
            <a href="{{ route('photocopies.export-data', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-download mr-1"></i>Exportar Datos
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alerta informativa -->
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-info"></i> Información del Período</h5>
        Mostrando datos desde <strong>{{ $startDate }}</strong> hasta <strong>{{ $endDate }}</strong>
        @if($copiesRequests->count() == 0)
            <br><small class="text-muted">No se encontraron solicitudes de fotocopias en este período.</small>
        @else
            <br><small class="text-muted">Se encontraron <strong>{{ $copiesRequests->count() }}</strong> solicitudes de fotocopias.</small>
        @endif
    </div>

    <!-- Tarjetas de Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($statistics['total']) }}</h3>
                    <p>Total de Solicitudes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($statistics['totalImpresiones']) }}</h3>
                    <p>Total de Impresiones</p>
                </div>
                <div class="icon">
                    <i class="fas fa-print"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $statistics['porcentajeSatisfaccion'] }}%</h3>
                    <p>Satisfacción</p>
                </div>
                <div class="icon">
                    <i class="fas fa-smile"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $statistics['kpi'] }}%</h3>
                    <p>KPI General</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    @if($statistics['total'] > 0)
        <!-- Gráficos y Análisis -->
        <div class="row mb-4">
            <!-- Distribución de Impresiones -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Distribución de Impresiones</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $total = $statistics['totalImpresiones'];
                            $porcBN = $total > 0 ? round(($statistics['blancoNegro'] / $total) * 100, 1) : 0;
                            $porcColor = $total > 0 ? round(($statistics['color'] / $total) * 100, 1) : 0;
                            $porcDoble = $total > 0 ? round(($statistics['dobleCarta'] / $total) * 100, 1) : 0;
                        @endphp
                        
                        <div class="mb-3">
                            <span class="text-muted">Blanco y Negro ({{ $porcBN }}%)</span>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-secondary" style="width: {{ $porcBN }}%">
                                    {{ number_format($statistics['blancoNegro']) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <span class="text-muted">Color ({{ $porcColor }}%)</span>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: {{ $porcColor }}%">
                                    {{ number_format($statistics['color']) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <span class="text-muted">Doble Carta Color ({{ $porcDoble }}%)</span>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-info" style="width: {{ $porcDoble }}%">
                                    {{ number_format($statistics['dobleCarta']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análisis de Satisfacción -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Análisis de Satisfacción</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-success">{{ $statistics['satisfaccion']['si'] }}</h4>
                                <small class="text-muted">Satisfactorio</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-danger">{{ $statistics['satisfaccion']['no'] }}</h4>
                                <small class="text-muted">No Satisfactorio</small>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: {{ $statistics['porcentajeSatisfaccion'] }}%">
                                    {{ $statistics['porcentajeSatisfaccion'] }}%
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <span class="badge badge-lg 
                                @if($statistics['porcentajeSatisfaccion'] >= 90) badge-success
                                @elseif($statistics['porcentajeSatisfaccion'] >= 70) badge-info
                                @elseif($statistics['porcentajeSatisfaccion'] >= 50) badge-warning
                                @else badge-danger
                                @endif">
                                @if($statistics['porcentajeSatisfacción'] >= 90)
                                    Excelente nivel de servicio
                                @elseif($statistics['porcentajeSatisfaccion'] >= 70)
                                    Buen nivel de servicio
                                @elseif($statistics['porcentajeSatisfaccion'] >= 50)
                                    Servicio moderado
                                @else
                                    Requiere atención inmediata
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rankings -->
        <div class="row mb-4">
            <!-- Top Docentes -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title text-dark"><i class="fas fa-user-graduate mr-2"></i>Top 5 Docentes</h3>
                    </div>
                    <div class="card-body p-2">
                        @if(count($statistics['topDocentes']) > 0)
                            @foreach(array_slice($statistics['topDocentes'], 0, 5, true) as $docente => $data)
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <div>
                                        <strong>{{ $docente }}</strong><br>
                                        <small class="text-muted">{{ $data['solicitudes'] }} solicitudes</small>
                                    </div>
                                    <span class="badge badge-primary">{{ number_format($data['total']) }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center">No hay datos disponibles</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Top Secciones -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title text-white"><i class="fas fa-building mr-2"></i>Top 5 Secciones</h3>
                    </div>
                    <div class="card-body p-2">
                        @if(count($statistics['topSecciones']) > 0)
                            @foreach(array_slice($statistics['topSecciones'], 0, 5, true) as $seccion => $data)
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <div>
                                        <strong>{{ $seccion }}</strong><br>
                                        <small class="text-muted">{{ $data['solicitudes'] }} solicitudes</small>
                                    </div>
                                    <span class="badge badge-info">{{ number_format($data['total']) }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center">No hay datos disponibles</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Top Cursos -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title text-white"><i class="fas fa-graduation-cap mr-2"></i>Top 5 Cursos</h3>
                    </div>
                    <div class="card-body p-2">
                        @if(count($statistics['topCursos']) > 0)
                            @foreach(array_slice($statistics['topCursos'], 0, 5, true) as $curso => $data)
                                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                    <div>
                                        <strong>{{ $curso }}</strong><br>
                                        <small class="text-muted">{{ $data['solicitudes'] }} solicitudes</small>
                                    </div>
                                    <span class="badge badge-success">{{ number_format($data['total']) }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center">No hay datos disponibles</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabla de Solicitudes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Detalle de Solicitudes</h3>
            <div class="card-tools">
                <span class="badge badge-info">{{ $copiesRequests->count() }} registros</span>
            </div>
        </div>
        <div class="card-body">
            @if($copiesRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="copiesTable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Docente</th>
                                <th>Sección</th>
                                <th>Curso</th>
                                <th>B/N</th>
                                <th>Color</th>
                                <th>Doble Carta</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($copiesRequests as $request)
                                @php
                                    $totalBN = 0;
                                    $totalColor = 0;
                                    $totalDobleCarta = 0;
                                    
                                    if (is_array($request->copy_items)) {
                                        foreach ($request->copy_items as $item) {
                                            $totalBN += (int)($item['black_white'] ?? 0);
                                            $totalColor += (int)($item['color'] ?? 0);
                                            $totalDobleCarta += (int)($item['double_letter_color'] ?? 0);
                                        }
                                    }
                                    
                                    $totalImpresiones = $totalBN + $totalColor + $totalDobleCarta;
                                    
                                    $statusClass = 'secondary';
                                    $statusText = 'Pendiente';
                                    
                                    switch(strtolower($request->status ?? 'pending')) {
                                        case 'approved':
                                            $statusClass = 'success';
                                            $statusText = 'Aprobado';
                                            break;
                                        case 'completed':
                                            $statusClass = 'info';
                                            $statusText = 'Completado';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'danger';
                                            $statusText = 'Rechazado';
                                            break;
                                        default:
                                            $statusClass = 'warning';
                                            $statusText = 'Pendiente';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $request->requester ?? 'No especificado' }}</td>
                                    <td>{{ $request->section ?? 'No especificado' }}</td>
                                    <td>{{ $request->grade ?? 'No especificado' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ number_format($totalBN) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ number_format($totalColor) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ number_format($totalDobleCarta) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format($totalImpresiones) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-toggle="modal" 
                                                data-target="#detailModal{{ $request->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal de Detalles -->
                                <div class="modal fade" id="detailModal{{ $request->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Detalles de Solicitud #{{ $request->request_number }}</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Información General:</strong>
                                                        <ul class="list-unstyled mt-2">
                                                            <li><strong>Fecha:</strong> {{ $request->created_at->format('d/m/Y H:i:s') }}</li>
                                                            <li><strong>Solicitante:</strong> {{ $request->requester }}</li>
                                                            <li><strong>Sección:</strong> {{ $request->section }}</li>
                                                            <li><strong>Grado:</strong> {{ $request->grade }}</li>
                                                            <li><strong>Estado:</strong> <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span></li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Detalles de Impresión:</strong>
                                                        @if(is_array($request->copy_items) && count($request->copy_items) > 0)
                                                            <div class="table-responsive mt-2">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Item</th>
                                                                            <th>Descripción</th>
                                                                            <th>Copias</th>
                                                                            <th>B/N</th>
                                                                            <th>Color</th>
                                                                            <th>Doble</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($request->copy_items as $index => $item)
                                                                            <tr>
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>{{ $item['original'] ?? 'N/A' }}</td>
                                                                                <td>{{ $item['copies_required'] ?? 0 }}</td>
                                                                                <td>{{ $item['black_white'] ?? 0 }}</td>
                                                                                <td>{{ $item['color'] ?? 0 }}</td>
                                                                                <td>{{ $item['double_letter_color'] ?? 0 }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <p class="text-muted mt-2">No hay detalles de impresión disponibles.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-copy fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No hay solicitudes de fotocopias</h4>
                    <p class="text-muted">
                        No se encontraron solicitudes de fotocopias en el período seleccionado.
                        <br>
                        <strong>Período:</strong> {{ $startDate }} a {{ $endDate }}
                    </p>
                    <div class="mt-4">
                        <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#filtersModal">
                            <i class="fas fa-filter mr-2"></i>Ajustar Filtros de Búsqueda
                        </button>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            Prueba expandir el rango de fechas o quitar filtros para ver más resultados.
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Filtros -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="{{ route('photocopies.dashboard') }}">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-filter mr-2"></i>Filtros de Búsqueda</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar mr-1"></i>Fecha Inicio</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar mr-1"></i>Fecha Fin</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-building mr-1"></i>Sección</label>
                        <select name="section" class="form-control">
                            <option value="">Todas las secciones</option>
                            @foreach($filters['sections'] as $sectionOption)
                                <option value="{{ $sectionOption }}" {{ $section == $sectionOption ? 'selected' : '' }}>
                                    {{ $sectionOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user-graduate mr-1"></i>Docente</label>
                        <select name="teacher" class="form-control">
                            <option value="">Todos los docentes</option>
                            @foreach($filters['teachers'] as $teacherOption)
                                <option value="{{ $teacherOption }}" {{ $teacher == $teacherOption ? 'selected' : '' }}>
                                    {{ $teacherOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-graduation-cap mr-1"></i>Curso</label>
                        <select name="course" class="form-control">
                            <option value="">Todos los cursos</option>
                            @foreach($filters['courses'] as $courseOption)
                                <option value="{{ $courseOption }}" {{ $course == $courseOption ? 'selected' : '' }}>
                                    {{ $courseOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i>Aplicar Filtros
                    </button>
                    <a href="{{ route('photocopies.dashboard') }}" class="btn btn-warning">
                        <i class="fas fa-redo mr-1"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        border: none;
    }
    .progress {
        border-radius: 10px;
        height: 20px !important;
    }
    .progress-bar {
        border-radius: 10px;
    }
    .badge {
        font-size: 85%;
    }
    .badge-lg {
        font-size: 1rem;
        padding: 8px 12px;
    }
    #copiesTable {
        font-size: 0.9rem;
    }
    .alert {
        border-radius: 8px;
    }
    .modal-content {
        border-radius: 8px;
    }
    .btn {
        border-radius: 6px;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .empty-state {
        padding: 60px 20px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // DataTable para la tabla de solicitudes
    if ($('#copiesTable').length) {
        $('#copiesTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            order: [[0, 'desc']],
            pageLength: 25,
            columnDefs: [
                { targets: [4, 5, 6, 7, 8, 9], orderable: false }
            ]
        });
    }
    
    // Auto-cerrar alertas después de 10 segundos
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 10000);
    
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop
