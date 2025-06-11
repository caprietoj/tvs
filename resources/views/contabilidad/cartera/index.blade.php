@extends('adminlte::page')

@section('title', 'Recaudo de Cartera')

@section('content_header')
    <h1>Recaudo de Cartera</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Listado de Recaudos</h3>
            <a href="{{ route('contabilidad.cartera.create') }}" class="btn btn-light">
                <i class="fas fa-plus"></i> Nuevo Registro
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="recaudosTable">
                <thead class="thead-dark">
                    <tr>
                        <th>Mes</th>
                        <th>Valor Recaudado</th>
                        <th>Valor Facturado</th>
                        <th>Porcentaje de Recaudo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recaudos as $recaudo)
                    <tr>
                        <td>{{ $recaudo->mes }}</td>
                        <td>${{ number_format($recaudo->valor_recaudado, 2, ',', '.') }}</td>
                        <td>${{ number_format($recaudo->valor_facturado, 2, ',', '.') }}</td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $recaudo->porcentaje_recaudo }}%;"
                                     aria-valuenow="{{ $recaudo->porcentaje_recaudo }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ $recaudo->porcentaje_recaudo }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
    }
    
    .card-header {
        background-color: var(--primary) !important;
    }
    
    .progress-bar {
        background-color: var(--primary);
    }
    
    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    
    .btn-primary:hover {
        background-color: #2a3f5f;
        border-color: #2a3f5f;
    }

    .table thead th {
        background-color: var(--primary);
        color: white;
        border-bottom: 2px solid #2a3f5f;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#recaudosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        }
    });
});
</script>
@stop
