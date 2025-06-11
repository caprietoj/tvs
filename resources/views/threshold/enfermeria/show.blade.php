{{-- resources/views/threshold/enfermeria/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Ver Indicadores Configurados - Enfermería')

@section('content_header')
    <h1>Indicadores Configurados - Enfermería</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        {{-- Se han removido los botones redundantes de "Crear Nuevo Threshold" y "Editar Threshold" globales.
             Ahora, cada registro en la tabla tendrá su botón de "Editar". --}}
    </div>
    <div class="card-body">
        <table id="thresholdTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Indicador</th>
                    <th>Valor del Indicador (%)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($thresholds as $threshold)
                <tr>
                    <td>{{ $threshold->id }}</td>
                    <td>{{ $threshold->kpi_name }}</td>
                    <td>{{ $threshold->value }}</td>
                    <td>
                        <a href="{{ route('umbral.enfermeria.edit', $threshold->id) }}" class="btn btn-sm btn-primary">Editar</a>
                        <button class="btn btn-sm btn-danger delete-threshold" data-id="{{ $threshold->id }}">Eliminar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<style>
    :root {
        --primary: #1a4884;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,.12);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .table thead th {
        background: rgba(26, 72, 132, 0.05);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary);
    }

    .btn {
        padding: 0.5rem 1rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(26, 72, 132, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2a5298 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 72, 132, 0.35);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger) 0%, #dc3545 100%);
        border: none;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        transform: translateY(-2px);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: white !important;
        border-radius: 6px;
    }
</style>
@stop

@section('js')
<!-- jQuery, DataTables JS y SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inicializar DataTables para la tabla de thresholds
        $('#thresholdTable').DataTable();

        // Manejar la eliminación de un threshold con SweetAlert2
        $('.delete-threshold').click(function(){
            var thresholdId = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el threshold.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if(result.isConfirmed) {
                    $.ajax({
                        url: '/enfermeria/umbral/' + thresholdId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Eliminado!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'No se pudo eliminar el threshold.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@stop
