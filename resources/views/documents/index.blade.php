@extends('adminlte::page')

@section('title', 'Documentos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Gestión de Documentos</h1>
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i>Añadir nuevo item
        </a>
    </div>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="documents-table" class="table table-hover">
                <thead class="thead-primary">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                        <tr>
                            <td>{{ $document->id }}</td>
                            <td>{{ $document->name }}</td>
                            <td>{{ $document->description }}</td>
                            <td>{{ $document->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('documents.edit', $document) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('documents.destroy', $document) }}" 
                                          method="POST" 
                                          class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
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

    .text-primary { 
        color: var(--primary) !important;
        font-weight: 600;
    }

    .custom-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .thead-primary {
        background-color: var(--primary);
        color: white;
    }

    .thead-primary th {
        border: none;
        padding: 1rem;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(54, 78, 118, 0.05);
        transition: background-color 0.3s ease;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
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

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    /* DataTables Customization */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        color: white !important;
        border: 1px solid var(--primary) !important;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #2a3d5d !important;
        color: white !important;
        border: 1px solid #2a3d5d !important;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#documents-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 10
    });

    $('.delete-form').submit(function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#364E76',
            cancelButtonColor: '#ED3236',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>
@stop