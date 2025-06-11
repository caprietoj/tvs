@extends('adminlte::page')

@section('title', 'Evaluaciones de Proveedores')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Evaluaciones de Proveedores</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('evaluaciones.create') }}" class="btn btn-primary float-right">
                    <i class="fas fa-plus"></i> Nueva Evaluación
                </a>
            </div>
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
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Contrato</th>
                            <th>Fecha</th>
                            <th>Puntaje Total</th>
                            <th>Evaluado Por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluaciones as $evaluacion)
                            <tr>
                                <td>{{ $evaluacion->proveedor->nombre }}</td>
                                <td>{{ $evaluacion->numero_contrato }}</td>
                                <td>{{ $evaluacion->fecha_evaluacion->format('d/m/Y') }}</td>
                                <td>{{ number_format($evaluacion->puntaje_total, 2) }}</td>
                                <td>{{ $evaluacion->evaluado_por }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('evaluaciones.show', $evaluacion->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('evaluaciones.edit', $evaluacion->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card-header {
        background-color: #364E76 !important;
        color: white;
    }
    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            }
        });
    });
</script>
@stop
