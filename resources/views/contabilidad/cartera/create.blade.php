@extends('adminlte::page')

@section('title', 'Nuevo Registro de Cartera')

@section('content_header')
    <h1>Nuevo Registro de Cartera</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary">
        <h3 class="card-title">Formulario de Registro</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('contabilidad.cartera.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="mes">Mes</label>
                <select name="mes" class="form-control @error('mes') is-invalid @enderror" required>
                    <option value="">Seleccione un mes</option>
                    @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                             'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $mes)
                        <option value="{{ $mes }}" {{ old('mes') == $mes ? 'selected' : '' }}>{{ $mes }}</option>
                    @endforeach
                </select>
                @error('mes')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="valor_recaudado">Valor Recaudado</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    <input type="number" name="valor_recaudado" 
                           class="form-control @error('valor_recaudado') is-invalid @enderror" 
                           step="0.01" required value="{{ old('valor_recaudado') }}">
                </div>
                @error('valor_recaudado')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="valor_facturado">Valor Facturado</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    <input type="number" name="valor_facturado" 
                           class="form-control @error('valor_facturado') is-invalid @enderror" 
                           step="0.01" required value="{{ old('valor_facturado') }}">
                </div>
                @error('valor_facturado')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ route('contabilidad.cartera.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
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
        color: white;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #2a3f5f;
        border-color: #2a3f5f;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    label {
        color: var(--primary);
        font-weight: bold;
    }

    .input-group-text {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
</style>
@stop
