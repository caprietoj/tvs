@extends('adminlte::page')

@section('title', 'Editar Evaluación')

@section('content_header')
    <h1>Editar Evaluación</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Editar Evaluación de Proveedor</h3>
        </div>
        <form action="{{ route('evaluaciones.update', $evaluacion->id) }}" method="POST">
            @csrf
            @method('PUT')
            <!-- Reutilizar el mismo formulario que en create pero con los valores actuales -->
            <div class="card-body">
                <!-- Copiar todo el contenido del formulario de create.blade.php pero usando $evaluacion para los valores -->
                @include('evaluaciones.form', ['evaluacion' => $evaluacion])
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary" style="background-color: #364E76; border-color: #364E76;">
                    <i class="fas fa-save mr-2"></i>Actualizar Evaluación
                </button>
                <a href="{{ route('evaluaciones.index') }}" class="btn btn-secondary float-right">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .btn-primary:hover {
        background-color: #2b3e5f !important;
        border-color: #2b3e5f !important;
    }
</style>
@stop

@section('js')
    <!-- Copiar el mismo JavaScript de create.blade.php -->
@stop
