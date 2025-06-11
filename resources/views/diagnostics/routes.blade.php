@extends('adminlte::page')

@section('title', 'Diagnóstico de Rutas')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-stethoscope mr-2"></i>Diagnóstico del Sistema</h1>
    <a href="{{ route('diagnostics.fix-routes') }}" class="btn btn-warning">
        <i class="fas fa-sync mr-1"></i> Limpiar Caché de Rutas
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary">
        <h3 class="card-title">Diagnóstico de Rutas - Préstamos</h3>
    </div>
    <div class="card-body">
        <h4 class="mb-3">Rutas detectadas para solicitudes de préstamo:</h4>
        
        @if(count($diagnoseResults['routes']) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>URI</th>
                            <th>Métodos</th>
                            <th>Nombre</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diagnoseResults['routes'] as $route)
                            <tr>
                                <td>{{ $route['uri'] }}</td>
                                <td>{{ $route['methods'] }}</td>
                                <td>{{ $route['name'] }}</td>
                                <td>{{ $route['action'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No se encontraron rutas para solicitudes de préstamo.
            </div>
        @endif
        
        <h4 class="mt-4 mb-3">Estado de base de datos:</h4>
        <div class="card">
            <div class="card-body">
                <p><strong>Tabla loan_requests existe:</strong> 
                    @if($diagnoseResults['database']['table_exists'])
                        <span class="badge badge-success">Sí</span>
                    @else
                        <span class="badge badge-danger">No</span>
                    @endif
                </p>
                
                @if($diagnoseResults['database']['table_exists'])
                    <p><strong>Columnas:</strong></p>
                    <ul>
                        @foreach($diagnoseResults['database']['columns'] as $column)
                            <li>{{ $column }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        
        <h4 class="mt-4 mb-3">Controlador:</h4>
        <div class="card">
            <div class="card-body">
                <p><strong>Controlador existe:</strong> 
                    @if($diagnoseResults['controller']['exists'])
                        <span class="badge badge-success">Sí</span>
                    @else
                        <span class="badge badge-danger">No</span>
                        <p class="text-danger">Error: {{ $diagnoseResults['controller']['error'] }}</p>
                    @endif
                </p>
                
                @if($diagnoseResults['controller']['exists'])
                    <p><strong>Métodos:</strong></p>
                    <ul>
                        @foreach($diagnoseResults['controller']['methods'] as $method)
                            <li>{{ $method }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        
        <h4 class="mt-4 mb-3">Prueba de Ruta loan-requests.store:</h4>
        <div class="card">
            <div class="card-body">
                <p><strong>Ruta store existe:</strong> 
                    @if($diagnoseResults['route_test']['store_route_exists'])
                        <span class="badge badge-success">Sí</span>
                        <p><strong>URL:</strong> {{ $diagnoseResults['route_test']['store_route_url'] }}</p>
                    @else
                        <span class="badge badge-danger">No</span>
                    @endif
                </p>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Posibles soluciones si hay problemas:</h4>
            <ol>
                <li>Limpiar la caché de rutas con el botón de arriba.</li>
                <li>Verificar que la tabla en base de datos contiene todas las columnas esperadas.</li>
                <li>Comprobar que el controlador tiene los métodos necesarios (store, create, etc).</li>
                <li>Revisar los logs para ver errores específicos en <code>storage/logs/laravel.log</code>.</li>
            </ol>
        </div>
    </div>
</div>
@stop
