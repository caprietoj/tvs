@extends('adminlte::page')

@section('title', 'Gestión de Personas - Portería')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-users-cog"></i> Gestión de Personas</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-right">
                <a href="{{ route('porteria.personas.import') }}" class="btn btn-warning">
                    <i class="fas fa-file-excel"></i> Importar desde Excel
                </a>
                <a href="{{ route('porteria.personas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Persona
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Mensajes --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Estadísticas --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total'] }}</h3>
                    <p>Total Personas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $estadisticas['empleados'] }}</h3>
                    <p>Empleados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['estudiantes'] }}</h3>
                    <p>Estudiantes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['activos'] }}</h3>
                    <p>Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Listado de Personas</h3>
        </div>
        <div class="card-body">
            <table id="tabla-personas" class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Documento</th>
                        <th>Nombre Completo</th>
                        <th>Tipo</th>
                        <th>Grado/Cargo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <style>
        :root {
            --color-institucional: #233e6c;
            --color-institucional-hover: #1a2e50;
        }

        .card-header {
            background: linear-gradient(to right, var(--color-institucional), var(--color-institucional-hover)) !important;
            color: white;
        }

        .card-primary.card-outline {
            border-top: 3px solid var(--color-institucional);
        }

        .thead-dark {
            background-color: var(--color-institucional) !important;
        }

        .small-box {
            border-radius: 5px;
        }

        .small-box .icon {
            top: 10px;
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    const tabla = $('#tabla-personas').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('porteria.personas.index') }}',
            type: 'GET'
        },
        columns: [
            { data: 'documento', name: 'documento' },
            { data: 'nombre_completo', name: 'nombre' },
            { data: 'tipo_badge', name: 'tipo_persona', orderable: false },
            { data: 'grado', name: 'grado' },
            { data: 'estado_badge', name: 'activo', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        }
    });

    // Eliminar persona
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará a ${nombre}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/porteria/personas/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success');
                            tabla.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'No se pudo eliminar la persona', 'error');
                    }
                });
            }
        });
    });
});
</script>
@stop
