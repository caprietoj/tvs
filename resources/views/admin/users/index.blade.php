@extends('adminlte::page')

@section('title', 'Administrar Usuarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark">Gestión de Usuarios</h1>
        <div>
            <a href="{{ route('users.bulk.import') }}" class="btn btn-outline-primary mr-2" style="border-color: #364E76; color: #364E76;">
                <i class="fas fa-file-upload"></i> Importar Usuarios
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-primary" style="background-color: #364E76; border-color: #364E76;">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="users-table" class="table table-striped table-hover">
                <thead class="">
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 10%">Avatar</th>
                        <th style="width: 20%">Nombre</th>
                        <th style="width: 15%">Cargo</th>
                        <th style="width: 20%">Email</th>
                        <th style="width: 10%">Estado</th>
                        <th style="width: 10%">Roles</th>
                        <th style="width: 10%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td class="text-center">
                                <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF' }}" 
                                     alt="Avatar" class="img-circle elevation-1" width="35">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->cargo ?? 'Sin cargo' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-{{ $user->active ? 'success' : 'danger' }} badge-pill">
                                    <i class="fas fa-{{ $user->active ? 'check' : 'times' }} mr-1"></i>
                                    {{ $user->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info mr-1">
                                        <i class="fas fa-shield-alt mr-1"></i>{{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user->id) }}" 
                                       class="btn btn-info btn-sm" 
                                       data-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" 
                                       class="btn btn-primary btn-sm"
                                       data-toggle="tooltip" 
                                       title="Editar usuario">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @can('impersonate')
                                        @if($user->id !== auth()->id())
                                            <a href="{{ route('impersonate', $user->id) }}" 
                                               class="btn btn-secondary btn-sm"
                                               data-toggle="tooltip" 
                                               title="Cambiar a este usuario">
                                                <i class="fas fa-user-secret"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    @if($user->id !== auth()->id())
                                        <button type="button" 
                                                class="btn btn-danger btn-sm delete-user" 
                                                data-id="{{ $user->id }}"
                                                data-toggle="tooltip" 
                                                title="Eliminar usuario">
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
    .img-circle {
        border-radius: 50%;
        object-fit: cover;
    }
    
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }

    .btn-group .btn {
        margin: 0 2px;
    }

    .table th {
        background-color: #364E76;
        color: white;
        border-color: #2c3e5f;
    }

    #users-table_wrapper .dataTables_filter input {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 4px 8px;
    }

    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
    }

    .btn-primary:hover {
        background-color: #2c3e5f;
        border-color: #2c3e5f;
    }

    .btn-outline-primary:hover {
        background-color: #364E76;
        border-color: #364E76;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#users-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        order: [[2, 'asc']],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [1, 7] }
        ]
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Handle delete user
    $('.delete-user').click(function() {
        const userId = $(this).data('id');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('#delete-form');
                form.attr('action', `/admin/users/${userId}`);
                form.submit();
            }
        });
    });

    // Show success message if exists
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