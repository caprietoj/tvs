@extends('adminlte::page')

@section('title', 'Informe de Préstamos')

@section('content_header')
    <h1>Informe de Préstamos de Equipos</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Tarjetas de Equipos por Sección -->
    <div class="row">
        <div class="col-lg-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="ipads-primaria">0</h3>
                    <p>iPads Primaria</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tablet-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="ipads-bachillerato">0</h3>
                    <p>iPads Bachillerato</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tablet-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="laptops-bachillerato">0</h3>
                    <p>Portátiles Bachillerato</p>
                </div>
                <div class="icon">
                    <i class="fas fa-laptop"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Préstamos por Sección -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Préstamos por Sección</h3>
                </div>
                <div class="card-body">
                    <canvas id="loansBySectionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Equipos más Solicitados -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Equipos más Solicitados</h3>
                </div>
                <div class="card-body">
                    <canvas id="mostRequestedChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Préstamos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Préstamos</h3>
                    <div class="card-tools">
                        <select id="monthFilter" class="form-control">
                            <option value="">Todos los meses</option>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <table id="loansTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sección</th>
                                <th>Grado</th>
                                <th>Tipo de Equipo</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@stop

@section('js')
<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para actualizar los contadores
    function updateCounters(json) {
        // Actualizar las tarjetas con los totales del resumen
        document.getElementById('ipads-primaria').textContent = json.summary.ipads_primaria || 0;
        document.getElementById('ipads-bachillerato').textContent = json.summary.ipads_bachillerato || 0;
        document.getElementById('laptops-bachillerato').textContent = json.summary.laptops_bachillerato || 0;
    }

    // Gráfico de Préstamos por Sección
    new Chart(document.getElementById('loansBySectionChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($loansBySection->pluck('section')) !!},
            datasets: [{
                label: 'Préstamos',
                data: {!! json_encode($loansBySection->pluck('total')) !!},
                borderColor: '#3c8dbc',
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de Equipos más Solicitados
    new Chart(document.getElementById('mostRequestedChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($mostRequestedEquipment->map(function($item) {
                return $item->type . ' - ' . $item->section;
            })) !!},
            datasets: [{
                label: 'Número de Préstamos',
                data: {!! json_encode($mostRequestedEquipment->pluck('total_loans')) !!},
                backgroundColor: '#00a65a'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // DataTable Initialization (corregido)
    let table = $('#loansTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('equipment.loans.data') }}",
            data: function(d) {
                d.month = $('#monthFilter').val();
                return d;
            }
        },
        columns: [
            {"data": "section"},
            {"data": "grade"},
            {"data": "equipment_type"},
            {"data": "units_requested"},
            {"data": "loan_date"},
            {"data": "start_time"},
            {"data": "end_time"}
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        order: [[4, "desc"]],
        drawCallback: function(settings) {
            if (settings.json && settings.json.summary) {
                updateCounters(settings.json);
            }
        }
    });

    // Filtro por mes
    $('#monthFilter').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@stop
