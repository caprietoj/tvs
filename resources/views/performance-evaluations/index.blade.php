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
        <div class="card-body p-0"> <!-- Eliminamos padding para evitar desbordamiento -->
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
            <div class="row mb-3 px-3 pt-3">
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
                            <th style="width: 18%;">Empleado</th>
                            <th style="width: 13%;">Evaluador</th>
                            <th style="width: 10%;">Tipo</th>
                            <th style="width: 12%;">Período</th>
                            <th style="width: 10%;">Estado</th>
                            <th style="width: 8%;">Puntaje Final</th>
                            <th style="width: 7%;">Nivel</th>
                            <th style="width: 14%;">Retroalimentación</th>
                            <th style="width: 8%;">Acciones</th>
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
                                <td class="text-nowrap">
                                    <small>{{ $evaluation->evaluation_period_start->format('d/m/Y') }}</small><br>
                                    <small>{{ $evaluation->evaluation_period_end->format('d/m/Y') }}</small>
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
                                <td class="feedback-status-cell">
                                    @php
                                        $feedbackSession = $evaluation->feedbackSessions->first(); // Ya está eager loaded
                                    @endphp
                                    @if($feedbackSession)
                                        <a href="{{ route('feedback-sessions.show', $feedbackSession) }}" class="text-decoration-none">
                                            @if($feedbackSession->status === 'programada')
                                                <span class="badge badge-info mb-1">
                                                    <i class="fas fa-calendar-check"></i> Programada
                                                </span><br>
                                                <small class="text-muted">
                                                    {{ $feedbackSession->scheduled_datetime ? $feedbackSession->scheduled_datetime->format('d/m/Y H:i') : 'Por confirmar' }}
                                                </small>
                                            @elseif($feedbackSession->status === 'realizada')
                                                <span class="badge badge-success mb-1">
                                                    <i class="fas fa-check-circle"></i> Realizada
                                                </span><br>
                                                <small class="text-muted">
                                                    {{ $feedbackSession->completed_at ? $feedbackSession->completed_at->format('d/m/Y') : 'Completada' }}
                                                </small>
                                            @elseif($feedbackSession->status === 'cancelada')
                                                <span class="badge badge-secondary mb-1">
                                                    <i class="fas fa-times-circle"></i> Cancelada
                                                </span>
                                            @endif
                                        </a>
                                    @else
                                        @if($evaluation->status === 'completed')
                                            <span class="badge badge-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Pendiente
                                            </span><br>
                                            <small class="text-muted">Sin programar</small>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-minus"></i> N/A
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <a href="{{ route('performance-evaluations.show', $evaluation) }}" 
                                           class="btn btn-sm btn-info mb-1" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($evaluation->user_id === auth()->id() && $evaluation->canSelfEvaluate())
                                            <a href="{{ route('performance-evaluations.self-evaluate', $evaluation) }}" 
                                               class="btn btn-sm btn-success mb-1" title="Realizar autoevaluación">
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
                <div class="pagination-container">
                    <div class="pagination-wrapper">
                        {{ $evaluations->links() }}
                    </div>
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
    
    /* Estilos para la columna de retroalimentación */
    .feedback-status-cell {
        min-width: 130px;
        text-align: center;
        vertical-align: middle;
    }
    
    .feedback-status-cell .badge {
        white-space: nowrap;
        font-size: 0.75em;
        padding: 4px 8px;
        transition: all 0.2s ease;
        margin-bottom: 2px;
    }
    
    .feedback-status-cell a {
        text-decoration: none !important;
        color: inherit;
    }
    
    .feedback-status-cell a:hover {
        opacity: 0.8;
        text-decoration: none !important;
    }
    
    .feedback-status-cell a:hover .badge {
        transform: scale(1.05);
    }
    
    .feedback-status-cell small {
        font-size: 0.7em;
        line-height: 1.2;
        display: block;
        margin-top: 2px;
    }
    
    /* Responsive para móviles */
    @media (max-width: 768px) {
        .feedback-status-cell {
            min-width: 100px;
        }
        .feedback-status-cell .badge {
            font-size: 0.65em;
            padding: 2px 6px;
        }
        .feedback-status-cell small {
            font-size: 0.6em;
        }
    }
    
    /* Estilos profesionales para el paginador */
    .pagination-container {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 1rem;
        margin-top: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
    }
    
    /* Sobrescribir estilos de Tailwind para un diseño más limpio */
    .pagination-wrapper nav {
        width: 100%;
        max-width: 100%;
    }
    
    .pagination-wrapper .flex {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        padding: 0.5rem !important;
    }
    
    .pagination-wrapper .flex.justify-between {
        justify-content: space-between !important;
    }
    
    .pagination-wrapper .hidden {
        display: none !important;
    }
    
    .pagination-wrapper .sm\\:flex-1 {
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 1rem !important;
    }
    
    .pagination-wrapper .sm\\:flex {
        display: flex !important;
    }
    
    .pagination-wrapper .sm\\:items-center {
        align-items: center !important;
    }
    
    .pagination-wrapper .sm\\:justify-between {
        justify-content: space-between !important;
    }
    
    /* Estilos para los elementos del paginador */
    .pagination-wrapper .inline-flex,
    .pagination-wrapper .relative {
        background-color: #fff !important;
        border: 1px solid #007bff !important;
        color: #007bff !important;
        padding: 0.375rem 0.75rem !important;
        margin: 0 0.125rem !important;
        border-radius: 0.25rem !important;
        text-decoration: none !important;
        transition: all 0.15s ease-in-out !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
        min-width: 2.5rem !important;
        text-align: center !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .pagination-wrapper .inline-flex:hover,
    .pagination-wrapper .relative:hover {
        background-color: #e9ecef !important;
        border-color: #0056b3 !important;
        color: #0056b3 !important;
    }
    
    .pagination-wrapper .inline-flex:focus,
    .pagination-wrapper .relative:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        outline: 0 !important;
    }
    
    .pagination-wrapper .cursor-default {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: #fff !important;
        cursor: default !important;
    }
    
    .pagination-wrapper .cursor-default:hover {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: #fff !important;
    }
    
    .pagination-wrapper [aria-current="page"] span {
        background-color: #007bff !important;
        border-color: #007bff !important;
        color: #fff !important;
    }
    
    .pagination-wrapper .text-sm {
        font-size: 0.875rem !important;
        color: #6c757d !important;
        padding: 0.375rem 0.5rem !important;
        background: transparent !important;
        border: none !important;
        text-align: center !important;
    }
    
    .pagination-wrapper .w-5 {
        width: 1rem !important;
        height: 1rem !important;
    }
    
    .pagination-wrapper .h-5 {
        height: 1rem !important;
    }
    
    /* Remover estilos de Tailwind innecesarios */
    .pagination-wrapper .shadow-sm {
        box-shadow: none !important;
    }
    
    .pagination-wrapper .rounded-md,
    .pagination-wrapper .rounded-l-md,
    .pagination-wrapper .rounded-r-md {
        border-radius: 0.25rem !important;
    }
    
    .pagination-wrapper .-ml-px {
        margin-left: 0 !important;
    }
    
    .pagination-wrapper .ml-3 {
        margin-left: 0.5rem !important;
    }
    
    /* Responsive para la tabla - Mejorado */
    .table-responsive {
        margin-bottom: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        max-width: 100%;
    }
    
    .table {
        margin-bottom: 0;
        min-width: 1000px; /* Aumentado para más espacio */
        white-space: nowrap;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .table td:first-child,
    .table td:nth-child(2) {
        white-space: normal; /* Permitir wrap en nombres largos */
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .btn-group-vertical .btn {
        border-radius: 0.25rem !important;
        margin-bottom: 0.125rem;
    }
    
    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }
    
    @media (max-width: 992px) {
        .table {
            min-width: 900px;
        }
        
        .table-responsive {
            font-size: 0.9rem;
        }
        
        .pagination-container {
            padding: 0.75rem;
        }
        
        .pagination-wrapper .inline-flex,
        .pagination-wrapper .relative {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.8rem !important;
            min-width: 2rem !important;
        }
        
        .pagination-wrapper .text-sm {
            font-size: 0.75rem !important;
        }
        
        .pagination-wrapper .w-5,
        .pagination-wrapper .h-5 {
            width: 0.875rem !important;
            height: 0.875rem !important;
        }
        
        .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            min-width: 2rem;
        }
    }
    
    @media (max-width: 768px) {
        .table {
            min-width: 800px;
            font-size: 0.85rem;
        }
        
        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
        }
        
        .pagination-container {
            padding: 0.5rem;
        }
        
        .pagination-wrapper .flex {
            flex-direction: column !important;
            gap: 0.5rem !important;
        }
        
        .pagination-wrapper .sm\\:flex-1 {
            flex-direction: column !important;
            gap: 0.5rem !important;
        }
        
        .pagination-wrapper .inline-flex,
        .pagination-wrapper .relative {
            padding: 0.25rem 0.4rem !important;
            font-size: 0.75rem !important;
            min-width: 1.8rem !important;
        }
        
        .pagination-wrapper .text-sm {
            font-size: 0.7rem !important;
        }
        
        .pagination-wrapper .w-5,
        .pagination-wrapper .h-5 {
            width: 0.75rem !important;
            height: 0.75rem !important;
        }
        
        .pagination {
            font-size: 0.75rem;
        }
        
        .page-link {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
            min-width: 1.8rem;
        }
        
        .btn-group-vertical .btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.4rem;
        }
    }
    
    @media (max-width: 576px) {
        .table {
            min-width: 700px;
            font-size: 0.8rem;
        }
        
        .table th:nth-child(4),
        .table td:nth-child(4) {
            display: none; /* Ocultar columna Período en móviles */
        }
        
        .table th:nth-child(6),
        .table td:nth-child(6) {
            display: none; /* Ocultar columna Puntaje Final en móviles */
        }
        
        .pagination-container {
            padding: 0.5rem;
        }
        
        .pagination-wrapper .flex {
            flex-direction: column !important;
            gap: 0.25rem !important;
        }
        
        .pagination-wrapper .sm\\:flex-1 {
            flex-direction: column !important;
            gap: 0.25rem !important;
        }
        
        .pagination-wrapper .inline-flex,
        .pagination-wrapper .relative {
            padding: 0.2rem 0.3rem !important;
            font-size: 0.7rem !important;
            min-width: 1.5rem !important;
            margin: 0 0.05rem !important;
        }
        
        .pagination-wrapper .text-sm {
            font-size: 0.65rem !important;
        }
        
        .pagination-wrapper .w-5,
        .pagination-wrapper .h-5 {
            width: 0.625rem !important;
            height: 0.625rem !important;
        }
        
        .pagination-wrapper .ml-3 {
            margin-left: 0.25rem !important;
        }
        
        .page-link {
            padding: 0.2rem 0.3rem;
            font-size: 0.7rem;
            min-width: 1.5rem;
            margin: 0 0.05rem;
        }
    }
    
    @media (max-width: 480px) {
        .table {
            min-width: 600px;
        }
        
        .table th:nth-child(7),
        .table td:nth-child(7) {
            display: none; /* Ocultar columna Nivel en móviles muy pequeños */
        }
        
        .pagination-container {
            padding: 0.25rem;
        }
        
        .pagination-wrapper .flex {
            flex-direction: column !important;
            gap: 0.125rem !important;
        }
        
        .pagination-wrapper .sm\\:flex-1 {
            flex-direction: column !important;
            gap: 0.125rem !important;
        }
        
        .pagination-wrapper .inline-flex,
        .pagination-wrapper .relative {
            padding: 0.15rem 0.25rem !important;
            font-size: 0.65rem !important;
            min-width: 1.2rem !important;
            margin: 0.05rem !important;
        }
        
        .pagination-wrapper .text-sm {
            font-size: 0.6rem !important;
        }
        
        .pagination-wrapper .w-5,
        .pagination-wrapper .h-5 {
            width: 0.5rem !important;
            height: 0.5rem !important;
        }
        
        .pagination {
            justify-content: center;
        }
        
        .page-link {
            padding: 0.15rem 0.25rem;
            font-size: 0.65rem;
            min-width: 1.2rem;
        }
    }
    
    /* Mejoras para badges */
    .badge {
        white-space: nowrap;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Mejoras para el contenedor principal */
    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
        max-width: 100%;
        overflow-x: hidden;
    }
    
    .card {
        margin-bottom: 1rem;
        border: 1px solid rgba(0,0,0,.125);
        border-radius: 0.375rem;
    }
    
    .card-body {
        position: relative;
    }
    
    /* Mejoras para filtros */
    .form-inline .form-group {
        margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
        .form-inline {
            flex-direction: column;
            align-items: stretch;
        }
        
        .form-inline .form-group {
            margin-right: 0 !important;
            margin-bottom: 0.75rem;
        }
        
        .form-inline .form-control {
            width: 100%;
        }
        
        .form-inline .btn {
            width: 100%;
            margin-bottom: 0.25rem;
        }
    }
    
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .card-header h3 {
            font-size: 1.1rem;
        }
        
        .content_header h1 {
            font-size: 1.5rem;
        }
        
        .content_header .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.5rem;
        }
    }
    
    /* Scroll suave para dispositivos táctiles */
    .table-responsive,
    .pagination-container {
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }
    
    /* Indicador visual para scroll horizontal de la tabla */
    .table-responsive::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 20px;
        background: linear-gradient(to left, rgba(255,255,255,0.8), transparent);
        pointer-events: none;
        z-index: 1;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Barras de scroll para el paginador */
    .pagination-container::-webkit-scrollbar {
        height: 6px;
    }
    
    .pagination-container::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 3px;
    }
    
    .pagination-container::-webkit-scrollbar-thumb {
        background: #007bff;
        border-radius: 3px;
    }
    
    .pagination-container::-webkit-scrollbar-thumb:hover {
        background: #0056b3;
    }
</style>
@stop
