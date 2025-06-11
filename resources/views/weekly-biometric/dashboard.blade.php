@extends('adminlte::page')

@section('title', 'Dashboard Biométrico Semanal')

@section('content_header')
    <h1 class="text-primary font-weight-light">Dashboard Biométrico Semanal</h1>
@stop

@section('content')
<div class="card mb-4 border-0 shadow-sm bg-white">
    <div class="card-body py-3">
        <form id="filterForm" class="row align-items-end">
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label class="text-muted small mb-1">MES</label>
                    <select class="form-control" name="month">
                        <option value="">Todos los meses</option>
                        @foreach($months as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label class="text-muted small mb-1">FECHA INICIAL</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}" placeholder="dd/mm/aaaa">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label class="text-muted small mb-1">FECHA FINAL</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" placeholder="dd/mm/aaaa">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    @foreach($stats as $stat)
    <div class="col-md-6">
        <div class="card mb-4 border-0 shadow-sm bg-white">
            <div class="card-body">
                <h5 class="mb-4 pb-2 border-bottom text-primary">{{ $stat->department }}</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">TOTAL EMPLEADOS</p>
                            <h3 class="font-weight-light text-primary">{{ $stat->total_employees }}</h3>
                        </div>
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">MARCACIONES ESPERADAS</p>
                            <h3 class="font-weight-light text-primary">{{ $stat->expected_marks }}</h3>
                        </div>
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">MARCACIONES COMPLETAS</p>
                            <h5 class="font-weight-light text-primary">{{ $stat->complete_marks }}</h5>
                        </div>
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">MARCACIONES INCOMPLETAS</p>
                            <h5 class="font-weight-light text-primary">{{ $stat->incomplete_marks }}</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">MARCACIONES REGISTRADAS</p>
                            <h3 class="font-weight-light text-primary">{{ $stat->total_marks }}</h3>
                        </div>
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">CUMPLIMIENTO</p>
                            <h3 class="font-weight-light text-primary">{{ number_format($stat->marks_percentage, 1) }}%</h3>
                        </div>
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">LLEGADAS TARDE</p>
                            <h5 class="font-weight-light">
                                <a href="{{ route('weekly-biometric.late-details', $stat->department) }}" 
                                   class="text-primary" 
                                   onclick="window.open(this.href, 'LlegadasTarde', 
                                   'width=800,height=600,menubar=no,toolbar=no,location=no,status=no'); return false;">
                                    {{ $stat->late_count }} <i class="fas fa-external-link-alt text-muted" style="font-size: 12px;"></i>
                                </a>
                            </h5>
                        </div>
                        <div class="mb-4">
                            <p class="text-muted small text-uppercase mb-1">AUSENCIAS</p>
                            <h5 class="font-weight-light text-primary">{{ $stat->absent_count }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body">
                <h4 class="mb-4 font-weight-light text-primary">Detalle Diario por Departamento</h4>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="border-top-0 text-white small text-uppercase bg-primary">Departamento</th>
                                <th class="border-top-0 text-white small text-uppercase bg-primary">Fecha</th>
                                <th class="border-top-0 text-white small text-uppercase bg-primary">Empleados</th>
                                <th class="border-top-0 text-white small text-uppercase bg-primary">Total</th>
                                <th class="border-top-0 text-white small text-uppercase bg-primary">Completas</th>
                                <th class="border-top-0 text-white small text-uppercase bg-primary">Parciales</th>
                                <th class="border-top-0 text-white small text-uppercase bg-primary">Sin Marcar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyStats as $stat)
                            <tr>
                                <td>{{ $stat->department }}</td>
                                <td>{{ $stat->date }}</td>
                                <td>{{ $stat->unique_employees }}</td>
                                <td>{{ $stat->total_marks }}</td>
                                <td>{{ $stat->complete_marks }}</td>
                                <td>{{ $stat->partial_marks }}</td>
                                <td>{{ $stat->missing_marks }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $dailyStats->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function() {
    // Form handling
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const params = new URLSearchParams($(this).serialize());
        window.location.href = `{{ route('weekly-biometric.dashboard') }}?${params}`;
    });
});
</script>

<style>
    /* Colores institucionales */
    :root {
        --primary-color: #364E76;
        --white-color: #FEFEFE;
    }
    
    /* Estilos minimalistas */
    body {
        font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
        color: #333;
        background-color: #f8f9fa;
    }
    
    .card {
        border-radius: 4px;
        margin-bottom: 24px;
        background-color: var(--white-color);
    }
    
    .card-body {
        padding: 24px;
    }
    
    h1, h2, h3, h4, h5, h6 {
        color: var(--primary-color);
    }
    
    .text-primary {
        color: var(--primary-color) !important;
    }
    
    .bg-primary {
        background-color: var(--primary-color) !important;
    }
    
    .bg-white {
        background-color: var(--white-color) !important;
    }
    
    /* Estilos para la tabla */
    .table {
        width: 100%;
        margin-bottom: 0;
        color: #212529;
        font-size: 0.85rem;
        background-color: var(--white-color);
    }
    
    .table th {
        font-weight: 500;
        border-top: 0;
        border-bottom: 1px solid #dee2e6;
        padding: 8px 6px;
    }
    
    .table td {
        padding: 8px 6px;
        border-top: 0;
        border-bottom: 1px solid #f1f1f1;
        vertical-align: middle;
    }
    
    .table tr:last-child td {
        border-bottom: 0;
    }
    
    .table-sm th,
    .table-sm td {
        padding: 6px;
    }
    
    /* Estilos para formularios */
    .form-control {
        border-radius: 4px;
        height: 38px;
        font-size: 14px;
        border: 1px solid #ced4da;
        background-color: var(--white-color);
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.15);
    }
    
    /* Estilos para el filtro */
    #filterForm label {
        font-size: 12px;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 4px;
        font-weight: 500;
    }
    
    #filterForm .form-control {
        background-color: var(--white-color);
    }
    
    #filterForm .btn {
        height: 38px;
        font-size: 14px;
        font-weight: 500;
    }
    
    /* Estilos para botones */
    .btn {
        border-radius: 4px;
        font-weight: 400;
        padding: 8px 16px;
        transition: all 0.2s;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--white-color);
    }
    
    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #243451;
    }
    
    /* Estilos para la paginación */
    .pagination {
        margin: 0;
    }
    
    .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .page-link {
        color: var(--primary-color);
        border: none;
        padding: 6px 10px;
        font-size: 0.85rem;
    }
    
    .page-link:hover {
        background-color: #f8f9fa;
    }
    
    /* Estilos para enlaces */
    a {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    a:hover {
        color: #2a3d5d;
    }
    
    /* Sombras sutiles */
    .shadow-sm {
        box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
    }
    
    /* Espaciado */
    .mb-4 {
        margin-bottom: 1.5rem !important;
    }
    
    /* Tipografía */
    .font-weight-light {
        font-weight: 300 !important;
    }
    
    .text-uppercase {
        letter-spacing: 0.5px;
    }
    
    .text-muted {
        color: #6c757d !important;
    }
    
    .small {
        font-size: 85%;
    }
    
    /* Responsive para tabla */
    @media (max-width: 768px) {
        .table {
            font-size: 0.75rem;
        }
        
        .table th,
        .table td {
            padding: 4px;
        }
    }
</style>
@stop