@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark">Gestión de Roles</h1>
        <a href="{{ route('roles.create') }}" class="btn btn-primary" style="background-color: #364E76; border-color: #364E76;">
            <i class="fas fa-plus-circle mr-2"></i>Nuevo Rol
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="roles-table" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th style="width: 25%">Nombre</th>
                        <th style="width: 45%">Permisos</th>
                        <th style="width: 20%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>
                                <span class="font-weight-bold">
                                    <i class="fas fa-shield-alt text-primary mr-2"></i>
                                    {{ $role->name }}
                                </span>
                            </td>
                            <td>
                                @foreach($role->permissions as $permission)
                                    <span class="badge badge-info m-1">
                                        <i class="fas fa-key mr-1"></i>
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('roles.edit', $role->id) }}" 
                                       class="btn btn-primary btn-sm"
                                       data-toggle="tooltip"
                                       title="Editar rol">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!in_array($role->name, ['SuperAdmin', 'Admin']))
                                        <button type="button" 
                                                class="btn btn-danger btn-sm delete-role"
                                                data-id="{{ $role->id }}"
                                                data-toggle="tooltip"
                                                title="Eliminar rol">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    .table th {
        background-color: #364E76;
        color: white;
        border-color: #2c3e5f;
    }

    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }

    .btn-group .btn {
        margin: 0 2px;
    }

    #roles-table_wrapper .dataTables_filter input {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 4px 8px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#roles-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [2, 3] }
        ]
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('.delete-role').click(function() {
        const roleId = $(this).data('id');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará el rol y sus asignaciones. No se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#364E76',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('#delete-form');
                form.attr('action', `/admin/roles/${roleId}`);
                form.submit();
            }
        });
    });

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session("success") }}',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
});
</script>
@stop
