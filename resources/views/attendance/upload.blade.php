@extends('adminlte::page')

@section('title', 'Subir Informe Biométrico')

@section('content_header')
    <h1>Subir Informe Biométrico</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('attendance.import') }}" method="POST">
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
                <label>Datos de Excel (Copiar y pegar)</label>
                <textarea name="datos" class="form-control" rows="10" required></textarea>
                <small class="form-text text-muted">
                    Copie y pegue directamente desde Excel. Las columnas deben ser: No_id | nombre_apellidos | fecha | entrada | salida | departamento
                    <br>
                    El formato de fecha debe ser dd/mm/yyyy (ejemplo: 13/12/2024) y se guardará como yyyy-mm-dd
                </small>
            </div>
            <button type="submit" class="btn btn-primary">Importar Datos</button>
        </form>
    </div>
</div>
@stop
