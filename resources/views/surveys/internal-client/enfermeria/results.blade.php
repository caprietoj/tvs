@extends('adminlte::page')

@section('title', 'Resultados Detallados - Enfermería')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line mr-2"></i>Resultados Detallados - Enfermería</h1>
        <div>
            <a href="{{ route('surveys.internal-client.enfermeria') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Volver al Dashboard
            </a>
            @if($selectedPeriod ?? null)
                <a href="{{ route('surveys.internal-client.enfermeria.export', ['period' => $selectedPeriod]) }}" class="btn btn-success">
                    <i class="fas fa-download mr-1"></i>Exportar Datos
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Información del período -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información del Análisis
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong><i class="fas fa-calendar mr-1"></i>Período Evaluado:</strong>
                            <span class="badge badge-primary">{{ $selectedPeriod ?? 'No seleccionado' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-users mr-1"></i>Total de Respuestas:</strong>
                            <span class="badge badge-success">{{ $totalResponses ?? 0 }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-building mr-1"></i>Dependencias Participantes:</strong>
                            <span class="badge badge-info">{{ count($dependenciesData ?? []) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados por Categoría -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Análisis Detallado por Categoría
                    </h3>
                </div>
                <div class="card-body">
                    @if(!empty($chartData))
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Promedio</th>
                                        <th>Porcentaje</th>
                                        <th>Evaluaciones</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $categoryNames = [
                                            'experience' => 'Experiencia con Enfermería',
                                            'presentation' => 'Presentación Personal',
                                            'availability' => 'Disponibilidad del Personal',
                                            'professionalism' => 'Profesionalismo',
                                            'effective_response' => 'Respuesta Efectiva',
                                            'cleanliness' => 'Limpieza y Orden',
                                            'reports' => 'Reportes Oportunos',
                                            'clarity' => 'Claridad de Reportes'
                                        ];
                                    @endphp
                                    @foreach($chartData as $key => $category)
                                        @if($key !== 'dependency' && isset($category['data']) && is_array($category['data']) && count($category['data']) > 0)
                                            @php
                                                $average = collect($category['data'])->avg() ?: 0;
                                                $percentage = round($average, 1);
                                                $evaluations = count($category['data']);
                                                $status = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
                                                $statusText = $percentage >= 80 ? 'Excelente' : ($percentage >= 60 ? 'Bueno' : 'Requiere Mejora');
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $categoryNames[$key] ?? ucfirst($key) }}</strong></td>
                                                <td>{{ number_format($average/20, 1) }}/5.0</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-{{ $status }}" 
                                                             style="width: {{ $percentage }}%">
                                                            {{ $percentage }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $evaluations }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $status }}">{{ $statusText }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h4>No hay datos disponibles</h4>
                            <p>No se encontraron resultados para el período seleccionado.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución por Dependencias -->
    @if(!empty($dependenciesData))
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i>
                        Distribución por Dependencias
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Dependencia</th>
                                    <th>Número de Respuestas</th>
                                    <th>Porcentaje del Total</th>
                                    <th>Representación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalDep = $dependenciesData->sum('total'); @endphp
                                @foreach($dependenciesData as $dependency)
                                    @php
                                        $count = $dependency->total;
                                        $department = $dependency->dependencia;
                                        $percentage = $totalDep > 0 ? round(($count / $totalDep) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $department }}</strong></td>
                                        <td>{{ $count }}</td>
                                        <td>{{ $percentage }}%</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" 
                                                     style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th>Total</th>
                                    <th>{{ $totalDep }}</th>
                                    <th>100%</th>
                                    <th>-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Selector de Período -->
    @if($availablePeriods && count($availablePeriods) > 1)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i>
                        Filtrar por Período
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('surveys.internal-client.enfermeria.results') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period">Seleccionar Período:</label>
                                    <select name="period" id="period" class="form-control">
                                        @foreach($availablePeriods as $period)
                                            <option value="{{ $period }}" {{ $period == $selectedPeriod ? 'selected' : '' }}>
                                                {{ $period }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary form-control">
                                        <i class="fas fa-search mr-1"></i>Filtrar Resultados
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@stop
