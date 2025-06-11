@extends('adminlte::page')

@section('title', 'Registrar Ejecución Presupuestal')

@section('content_header')
    <h1>Registrar Ejecución Presupuestal</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('budget.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="department">Departamento</label>
                <select name="department" class="form-control @error('department') is-invalid @enderror">
                    <option value="">Seleccione un departamento</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}">{{ $department }}</option>
                    @endforeach
                </select>
                @error('department')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="month">Mes</label>
                <select name="month" class="form-control @error('month') is-invalid @enderror">
                    <option value="">Seleccione un mes</option>
                    @foreach($months as $month)
                        <option value="{{ $month }}">{{ $month }}</option>
                    @endforeach
                </select>
                @error('month')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="budget_amount">Valor Presupuestado (COP)</label>
                <input type="number" name="budget_amount" class="form-control" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="executed_amount">Valor Ejecutado (COP)</label>
                <input type="number" name="executed_amount" class="form-control" step="0.01" required>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('budget.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop
