@extends('adminlte::page')

@section('title', 'Confirmación Exitosa')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4><i class="fas fa-check-circle"></i> Confirmación Exitosa</h4>
                </div>
                <div class="card-body text-center">
                    <h5>Se ha confirmado la participación del área de {{ ucfirst($area) }}</h5>
                    <p>Para la salida pedagógica: {{ $salida->consecutivo }}</p>
                    <p class="text-muted">Fecha: {{ $salida->fecha_salida->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
