@extends('adminlte::page')

@section('title', 'Editar Ticket')

@section('content_header')
    <h1 class="text-primary">Editar Ticket #{{ $ticket->id }}</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-body">
        <form id="editTicketForm">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" value="{{ auth()->user()->name }}" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="{{ $ticket->titulo }}" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" required>{{ $ticket->descripcion }}</textarea>
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="Abierto" {{ $ticket->estado == 'Abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="En Proceso" {{ $ticket->estado == 'En Proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="Cerrado" {{ $ticket->estado == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            <div class="form-group">
                <label for="prioridad">Prioridad</label>
                <select name="prioridad" id="prioridad" class="form-control" required>
                    <option value="Baja" {{ $ticket->prioridad == 'Baja' ? 'selected' : '' }}>Baja</option>
                    <option value="Media" {{ $ticket->prioridad == 'Media' ? 'selected' : '' }}>Media</option>
                    <option value="Alta" {{ $ticket->prioridad == 'Alta' ? 'selected' : '' }}>Alta</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tipo_requerimiento">Tipo de Requerimiento</label>
                <select name="tipo_requerimiento" id="tipo_requerimiento" class="form-control" required>
                    <option value="Hardware" {{ $ticket->tipo_requerimiento == 'Hardware' ? 'selected' : '' }}>Hardware</option>
                    <option value="Software" {{ $ticket->tipo_requerimiento == 'Software' ? 'selected' : '' }}>Software</option>
                    <option value="Mantenimiento" {{ $ticket->tipo_requerimiento == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="Instalación" {{ $ticket->tipo_requerimiento == 'Instalación' ? 'selected' : '' }}>Instalación</option>
                    <option value="Conectividad" {{ $ticket->tipo_requerimiento == 'Conectividad' ? 'selected' : '' }}>Conectividad</option>
                </select>
            </div>
            <!-- Nueva sección para asignar técnico al editar -->
            <div class="form-group">
                <label for="tecnico_id">Técnico Asignado</label>
                <select name="tecnico_id" id="tecnico_id" class="form-control">
                    <option value="">Sin asignar</option>
                    @foreach($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}" {{ $ticket->tecnico_id == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar Ticket</button>
        </form>
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

    .custom-card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .form-control {
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn i {
        margin-right: 0.5rem;
    }

    select.form-control {
        padding: 0.375rem 0.75rem;
        background-position: right 0.75rem center;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('#editTicketForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("tickets.update", $ticket->id) }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire(
                    'Éxito!',
                    response.message,
                    'success'
                ).then(() => {
                    window.location.href = '{{ route("tickets.index") }}';
                });
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = '';
                $.each(errors, function(key, value) {
                    errorMessage += value[0] + '<br>';
                });
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });
</script>
@stop