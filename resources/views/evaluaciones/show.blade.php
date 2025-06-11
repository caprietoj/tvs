@extends('adminlte::page')

@section('title', 'Ver Evaluación')

@section('content_header')
    <h1>Detalles de la Evaluación</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Evaluación de Proveedor</h3>
            <div class="card-tools">
                <a href="{{ route('evaluaciones.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Información General</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Proveedor:</th>
                            <td>{{ $evaluacion->proveedor->nombre }}</td>
                        </tr>
                        <tr>
                            <th>NIT:</th>
                            <td>{{ $evaluacion->proveedor->nit }}</td>
                        </tr>
                        <tr>
                            <th>Contrato:</th>
                            <td>{{ $evaluacion->numero_contrato }}</td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td>{{ $evaluacion->fecha_evaluacion->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Lugar:</th>
                            <td>{{ $evaluacion->lugar_evaluacion }}</td>
                        </tr>
                        <tr>
                            <th>Evaluado por:</th>
                            <td>{{ $evaluacion->evaluado_por }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Resultados de la Evaluación</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Cumplimiento y Entrega:</th>
                            <td>{{ number_format($evaluacion->cumplimiento_entrega, 1) }}/5.0</td>
                        </tr>
                        <tr>
                            <th>Calidad y Especificaciones:</th>
                            <td>{{ number_format($evaluacion->calidad_especificaciones, 1) }}/5.0</td>
                        </tr>
                        <tr>
                            <th>Documentación y Garantías:</th>
                            <td>{{ number_format($evaluacion->documentacion_garantias, 1) }}/5.0</td>
                        </tr>
                        <tr>
                            <th>Servicio Postventa:</th>
                            <td>{{ number_format($evaluacion->servicio_postventa, 1) }}/5.0</td>
                        </tr>
                        <tr>
                            <th>Precio:</th>
                            <td>{{ number_format($evaluacion->precio, 1) }}/5.0</td>
                        </tr>
                        <tr>
                            <th>Capacidad Instalada:</th>
                            <td>{{ number_format($evaluacion->capacidad_instalada, 1) }}/5.0</td>
                        </tr>
                        <tr>
                            <th>Soporte Técnico:</th>
                            <td>{{ number_format($evaluacion->soporte_tecnico, 1) }}/5.0</td>
                        </tr>
                        <tr class="table-primary">
                            <th>PUNTAJE TOTAL:</th>
                            <td><strong>{{ number_format($evaluacion->puntaje_total, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($evaluacion->observaciones)
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Observaciones</h5>
                        <div class="p-3 bg-light">
                            {{ $evaluacion->observaciones }}
                        </div>
                    </div>
                </div>
            @endif
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
    .table-primary {
        background-color: #364E76 !important;
        color: white;
    }
</style>
@stop
