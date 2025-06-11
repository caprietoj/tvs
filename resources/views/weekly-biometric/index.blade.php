@extends('adminlte::page')

@section('title', 'Subir Reporte Semanal')

@section('content_header')
    <h1>Subir Reporte Biométrico Semanal</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('weekly-biometric.process') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Mes</label>
                        <select class="form-control" name="month" required>
                            <option value="">Seleccione un mes...</option>
                            @foreach($months as $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha Inicial</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fecha Final</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-3">
                <label>Copiar y pegar datos del Excel</label>
                <textarea class="form-control" name="data" rows="10" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Procesar Datos
            </button>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    // Validación de fechas
    $('form').on('submit', function(e) {
        var startDate = new Date($('input[name="start_date"]').val());
        var endDate = new Date($('input[name="end_date"]').val());
        
        if (endDate < startDate) {
            e.preventDefault();
            alert('La fecha final debe ser posterior o igual a la fecha inicial');
        }
    });
});
</script>
@stop
