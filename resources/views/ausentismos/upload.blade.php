@extends('adminlte::page')

@section('title', 'Subir Ausentismos')

@section('content_header')
    <h1>Subir Informe de Ausentismos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('ausentismos.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Mes</label>
                    <select name="mes" class="form-control" required>
                        <option value="">Seleccione mes...</option>
                        <option value="Enero">Enero</option>
                        <option value="Febrero">Febrero</option>
                        <option value="Marzo">Marzo</option>
                        <option value="Abril">Abril</option>
                        <option value="Mayo">Mayo</option>
                        <option value="Junio">Junio</option>
                        <option value="Julio">Julio</option>
                        <option value="Agosto">Agosto</option>
                        <option value="Septiembre">Septiembre</option>
                        <option value="Octubre">Octubre</option>
                        <option value="Noviembre">Noviembre</option>
                        <option value="Diciembre">Diciembre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Datos del Excel (Copiar y Pegar)</label>
                    <textarea name="datos" class="form-control" rows="10" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Cargar Datos</button>
            </form>
        </div>
    </div>
@stop
