@extends('adminlte::page')

@section('title', 'Evaluaciones de Desempeño')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Evaluaciones de Desempeño</h1>
        <div>
            @if(auth()->user()->hasRole('admin') || auth()->user()->can('view-all-performance-evaluations') || auth()->user()->can('create-performance-evaluations') || auth()->user()->can('export-performance-evaluations'))
                <a href="{{ route('performance-evaluations.export', request()->query()) }}" class="btn btn-success mr-2">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </a>
            @endif
            @if(auth()->user()->hasRole('admin') || auth()->user()->can('create-performance-evaluations'))
                <a href="{{ route('performance-evaluations.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Evaluación
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Evaluaciones</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>            @endif

            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('performance-evaluations.index') }}" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="status" class="mr-2"><strong>Estado:</strong></label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>
                                    Borrador
                                </option>
                                <option value="self_completed" {{ request('status') === 'self_completed' ? 'selected' : '' }}>
                                    Autoevaluación Completada
                                </option>
                                <option value="supervisor_review" {{ request('status') === 'supervisor_review' ? 'selected' : '' }}>
                                    En Revisión Supervisor
                                </option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                    Completada
                                </option>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label for="type" class="mr-2"><strong>Tipo:</strong></label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Todos los tipos</option>
                                <option value="periodo_prueba" {{ request('type') === 'periodo_prueba' ? 'selected' : '' }}>
                                    Período de Prueba
                                </option>
                                <option value="periodica" {{ request('type') === 'periodica' ? 'selected' : '' }}>
                                    Periódica
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('performance-evaluations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Evaluador</th>
                            <th>Tipo</th>
                            <th>Período</th>
                            <th>Estado</th>
                            <th>Puntaje Final</th>
                            <th>Nivel</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evaluations as $evaluation)
                            <tr>
                                <td>{{ $evaluation->user->name }}</td>
                                <td>{{ $evaluation->evaluator->name ?? 'No asignado' }}</td>
                                <td>
                                    @if($evaluation->evaluation_type === 'periodo_prueba')
                                        <span class="badge badge-info">Período de Prueba</span>
                                    @else
                                        <span class="badge badge-primary">Periódica</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $evaluation->evaluation_period_start->format('d/m/Y') }} - 
                                    {{ $evaluation->evaluation_period_end->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if($evaluation->status === 'draft')
                                        <span class="badge badge-secondary">Borrador</span>
                                    @elseif($evaluation->status === 'self_completed')
                                        <span class="badge badge-warning">Autoevaluación Completada</span>
                                    @elseif($evaluation->status === 'supervisor_review')
                                        <span class="badge badge-info">En Revisión</span>
                                    @elseif($evaluation->status === 'completed')
                                        <span class="badge badge-success">Completada</span>
                                    @endif
                                </td>
                                <td>
                                    @if($evaluation->final_average_score)
                                        {{ number_format($evaluation->final_average_score, 1) }}
                                    @elseif($evaluation->final_self_score)
                                        {{ number_format($evaluation->final_self_score, 1) }} (Auto)
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($evaluation->performance_level)
                                        {{ $evaluation->performance_level }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('performance-evaluations.show', $evaluation) }}" 
                                           class="btn btn-sm btn-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($evaluation->user_id === auth()->id() && $evaluation->canSelfEvaluate())
                                            <a href="{{ route('performance-evaluations.self-evaluate', $evaluation) }}" 
                                               class="btn btn-sm btn-success" title="Realizar autoevaluación">
                                                <i class="fas fa-user-edit"></i>
                                            </a>
                                        @endif
                                                          @if((auth()->user()->hasRole('admin') || 
                             (auth()->user()->can('evaluate-as-supervisor') && $evaluation->evaluator_id === auth()->id())) 
                             && $evaluation->canSupervisorEvaluate())
                            <a href="{{ route('performance-evaluations.supervisor-evaluate', $evaluation) }}" 
                               class="btn btn-sm btn-primary" title="Evaluar como supervisor">
                                <i class="fas fa-user-check"></i>
                            </a>
                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay evaluaciones registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($evaluations->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $evaluations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge {
        font-size: 0.9em;
    }
    .btn-group .btn {
        margin-right: 2px;
    }
</style>
@stop
