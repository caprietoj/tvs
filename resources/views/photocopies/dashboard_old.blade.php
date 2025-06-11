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
                        <span class="text-muted">Doble Carta ({{ $porcDoble }}%)</span>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-info" style="width: {{ $porcDoble }}%">
                                {{ number_format($statistics['dobleCarta']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Detallado -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Desglose del KPI</h3>
                </div>
                <div class="card-body">
                    @php
                        $factorSatisfaccion = $statistics['total'] > 0 ? 
                            round(($statistics['satisfaccion']['si'] / $statistics['total']) * 70, 1) : 0;
                        
                        $porcentajeAtencion = $statistics['total'] > 0 ? 
                            round(($statistics['satisfaccion']['si'] / $statistics['total']) * 100, 1) : 0;
                        
                        $factorCobertura = 10;
                        if ($porcentajeAtencion >= 95) $factorCobertura = 20;
                        elseif ($porcentajeAtencion >= 85) $factorCobertura = 18;
                        elseif ($porcentajeAtencion >= 75) $factorCobertura = 16;
                        elseif ($porcentajeAtencion >= 65) $factorCobertura = 14;
                        elseif ($porcentajeAtencion >= 50) $factorCobertura = 12;
                        
                        $factorActividad = 5;
                        if ($statistics['total'] >= 50) $factorActividad = 10;
                        elseif ($statistics['total'] >= 30) $factorActividad = 9;
                        elseif ($statistics['total'] >= 20) $factorActividad = 8;
                        elseif ($statistics['total'] >= 10) $factorActividad = 7;
                        elseif ($statistics['total'] >= 5) $factorActividad = 6;
                    @endphp
                    
                    <div class="row">
                        <div class="col-6">
                            <span class="text-muted">Satisfacción (70%):</span>
                            <h5 class="text-primary">{{ $factorSatisfaccion }} pts</h5>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Cobertura (20%):</span>
                            <h5 class="text-success">{{ $factorCobertura }} pts</h5>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Actividad (10%):</span>
                            <h5 class="text-info">{{ $factorActividad }} pts</h5>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Total KPI:</span>
                            <h4 class="text-bold text-primary">{{ $statistics['kpi'] }}%</h4>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar 
                                @if($statistics['kpi'] >= 90) bg-success
                                @elseif($statistics['kpi'] >= 70) bg-info
                                @elseif($statistics['kpi'] >= 50) bg-warning
                                @else bg-danger
                                @endif" 
                                style="width: {{ $statistics['kpi'] }}%">
                                {{ $statistics['kpi'] }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            @if($statistics['kpi'] >= 90)
                                Excelente nivel de servicio
                            @elseif($statistics['kpi'] >= 70)
                                Buen nivel de servicio
                            @elseif($statistics['kpi'] >= 50)
                                Servicio moderado
                            @else
                                Requiere atención inmediata
                            @endif
                        </small>
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
                    @foreach(array_slice($statistics['topDocentes'], 0, 5, true) as $index => $data)
                        @php
                            $nombre = $index;
                            $datos = $data;
                            $posicion = array_search($index, array_keys($statistics['topDocentes']));
                        @endphp
                        <div class="d-flex justify-content-between align-items-center py-2 {{ $posicion < 3 ? 'border-bottom' : '' }}">
                            <span class="small">
                                <span class="badge 
                                    @if($posicion === 0) badge-warning
                                    @elseif($posicion === 1) badge-secondary
                                    @elseif($posicion === 2) badge-dark
                                    @else badge-light
                                    @endif mr-1">{{ $posicion + 1 }}</span>
                                {{ strlen($nombre) > 20 ? substr($nombre, 0, 20) . '...' : $nombre }}
                            </span>
                            <span class="small font-weight-bold">{{ number_format($datos['total']) }}</span>
                        </div>
                    @endforeach
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
                    @foreach(array_slice($statistics['topSecciones'], 0, 5, true) as $index => $data)
                        @php
                            $nombre = $index;
                            $datos = $data;
                            $posicion = array_search($index, array_keys($statistics['topSecciones']));
                        @endphp
                        <div class="d-flex justify-content-between align-items-center py-2 {{ $posicion < 3 ? 'border-bottom' : '' }}">
                            <span class="small">
                                <span class="badge 
                                    @if($posicion === 0) badge-warning
                                    @elseif($posicion === 1) badge-secondary
                                    @elseif($posicion === 2) badge-dark
                                    @else badge-light
                                    @endif mr-1">{{ $posicion + 1 }}</span>
                                {{ $nombre }}
                            </span>
                            <span class="small font-weight-bold">{{ number_format($datos['total']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Cursos -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white"><i class="fas fa-book mr-2"></i>Top 5 Cursos</h3>
                </div>
                <div class="card-body p-2">
                    @foreach(array_slice($statistics['topCursos'], 0, 5, true) as $index => $data)
                        @php
                            $nombre = $index;
                            $datos = $data;
                            $posicion = array_search($index, array_keys($statistics['topCursos']));
                        @endphp
                        <div class="d-flex justify-content-between align-items-center py-2 {{ $posicion < 3 ? 'border-bottom' : '' }}">
                            <span class="small">
                                <span class="badge 
                                    @if($posicion === 0) badge-warning
                                    @elseif($posicion === 1) badge-secondary
                                    @elseif($posicion === 2) badge-dark
                                    @else badge-light
                                    @endif mr-1">{{ $posicion + 1 }}</span>
                                {{ strlen($nombre) > 15 ? substr($nombre, 0, 15) . '...' : $nombre }}
                            </span>
                            <span class="small font-weight-bold">{{ number_format($datos['total']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución Mensual -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark">
                    <h3 class="card-title text-white"><i class="fas fa-calendar-alt mr-2"></i>Distribución Mensual</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($statistics['meses'] as $mes => $datos)
                            @php
                                $satisfaccionMes = $datos['solicitudes'] > 0 ? 
                                    round(($datos['satisfecho'] / $datos['solicitudes']) * 100, 1) : 0;
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>{{ $mes }}</strong>
                                        <span class="badge 
                                            @if($satisfaccionMes >= 90) badge-success
                                            @elseif($satisfaccionMes >= 70) badge-info
                                            @elseif($satisfaccionMes >= 50) badge-warning
                                            @else badge-danger
                                            @endif">{{ $satisfaccionMes }}%</span>
                                    </div>
                                    <div class="text-muted">
                                        <small>{{ number_format($datos['total']) }} impresiones</small><br>
                                        <small>{{ $datos['solicitudes'] }} solicitudes</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- Tabla de Solicitudes -->
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
                                    <td>{{ $request->created_at->format('d/m/Y') }}</td>
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-copy fa-3x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No hay solicitudes de fotocopias</h4>
                    <p class="text-muted">
                        No se encontraron solicitudes de fotocopias en el período seleccionado.
                        <br>
                        <strong>Período:</strong> {{ $startDate }} a {{ $endDate }}
                    </p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#filtersModal">
                            <i class="fas fa-filter mr-1"></i>Ajustar Filtros
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
                                                 strpos($satisfecho, 'entregado') !== false ||
                                                 strpos($satisfecho, 'completado') !== false ||
                                                 strpos($satisfecho, 'listo') !== false ||
                                                 strpos($satisfecho, 'conforme') !== false ||
                                                 strpos($satisfecho, 'finalizado') !== false ||
                                                 strpos($satisfecho, 'ok') !== false ||
                                                 (strpos($satisfecho, 'pendiente') === false && 
                                                  strpos($satisfecho, 'no') === false && 
                                                  !empty(trim($satisfecho)));
                                
                                $total = ($request->black_white_prints ?? 0) + 
                                        ($request->color_prints ?? 0) + 
                                        ($request->double_letter_color ?? 0);
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y') }}</td>
                                <td>{{ $request->teacher_name ?? 'N/A' }}</td>
                                <td>{{ $request->section ?? 'N/A' }}</td>
                                <td>{{ $request->course ?? 'N/A' }}</td>
                                <td>{{ number_format($request->black_white_prints ?? 0) }}</td>
                                <td>{{ number_format($request->color_prints ?? 0) }}</td>
                                <td>{{ number_format($request->double_letter_color ?? 0) }}</td>
                                <td><strong>{{ number_format($total) }}</strong></td>
                                <td>
                                    @if($esSatisfactorio)
                                        <span class="badge badge-success">Satisfactorio</span>
                                    @else
                                        <span class="badge badge-danger">No Satisfactorio</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                                <label>Fecha Inicio</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sección</label>
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
                        <label>Docente</label>
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
                        <label>Curso</label>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
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
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }
    .progress {
        border-radius: 10px;
    }
    .progress-bar {
        border-radius: 10px;
    }
    .badge {
        font-size: 85%;
    }
    #copiesTable {
        font-size: 0.9rem;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#copiesTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[0, 'desc']],
        pageLength: 25
    });
});
</script>
@stop
