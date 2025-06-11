@extends('adminlte::page')

@section('title', 'Help Desk')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Sistema de Help Desk</h1>
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nuevo Ticket
        </a>
    </div>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="ticketsTable" class="table table-hover">
                <thead class="thead-primary">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Tipo de Requerimiento</th>
                        <th>Usuario</th>
                        <th>Técnico</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td>{{ $ticket->titulo }}</td>
                        <td>
                            <span class="badge 
                                @if($ticket->estado == 'Abierto') badge-abierto 
                                @elseif($ticket->estado == 'En Proceso') badge-enproceso 
                                @else badge-cerrado 
                                @endif">
                                {{ $ticket->estado }}
                            </span>
                        </td>
                        <td>
                            <span class="badge 
                                @if($ticket->prioridad == 'Baja') badge-baja 
                                @elseif($ticket->prioridad == 'Media') badge-media 
                                @else badge-alta 
                                @endif">
                                {{ $ticket->prioridad }}
                            </span>
                        </td>
                        <td>{{ $ticket->tipo_requerimiento }}</td>
                        <td>{{ $ticket->user->name }}</td>
                        <td>
                            {{ $ticket->tecnico ? $ticket->tecnico->name : 'Sin asignar' }}
                        </td>
                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @can('ticket.show')
                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-sm btn-info">Ver</a>
                            @endcan
                            @can('ticket.edit')
                                <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-sm btn-primary">Editar</a>
                            @endcan
                            @can('ticket.delete')
                                <button data-id="{{ $ticket->id }}" class="btn btn-sm btn-danger delete-ticket">Eliminar</button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
    }

    .text-primary { color: var(--primary) !important; }
    
    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }

    .custom-card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .thead-primary {
        background-color: var(--primary);
        color: white;
    }

    .table th { border-top: none; }

    .badge {
        padding: 0.5em 1em;
        font-size: 0.85em;
    }

    /* Estado colors */
    .badge-abierto { background-color: #28a745; }
    .badge-enproceso { background-color: #ffc107; color: #000; }
    .badge-cerrado { background-color: #dc3545; }

    /* Prioridad colors */
    .badge-alta { background-color: #dc3545; }
    .badge-media { background-color: #ffc107; color: #000; }
    .badge-baja { background-color: #28a745; }

    /* Button styles */
    .btn-group .btn {
        margin: 0 2px;
    }

    .btn-info {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-info:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }

    .btn-danger {
        background-color: var(--accent);
        border-color: var(--accent);
    }

    /* DataTables customization */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        color: white !important;
        border: 1px solid var(--primary) !important;
    }
</style>
@stop

@section('js')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#ticketsTable').DataTable();

        $('.delete-ticket').click(function() {
            var ticketId = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/tickets/' + ticketId,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Eliminado!', response.message, 'success');
                            location.reload();
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'No se pudo eliminar el ticket.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@stop