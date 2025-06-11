@extends('adminlte::page')

@section('title', 'Nuevo Ticket')

@section('content_header')
    <h1 class="text-primary">Crear Nuevo Ticket</h1>
@stop

@section('content')
<div class="card custom-card">
    <div class="card-body">
        <form id="createTicketForm">
            @csrf
            <!-- Mostrar el usuario creador (readonly) -->
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" value="{{ auth()->user()->name }}" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" required></textarea>
                <small class="form-text text-muted">
                    La prioridad del ticket se asignará automáticamente según el tipo de requerimiento y la descripción proporcionada.
                    Use palabras como "urgente", "crítico" o "no funciona" si necesita atención inmediata.
                </small>
            </div>
            <!-- Tipo de requerimiento -->
            <div class="form-group">
                <label for="tipo_requerimiento">Tipo de Requerimiento</label>
                <select name="tipo_requerimiento" id="tipo_requerimiento" class="form-control" required>
                    <option value="Hardware">Hardware</option>
                    <option value="Software">Software</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Instalación">Instalación</option>
                    <option value="Conectividad">Conectividad</option>
                </select>
                <small class="form-text text-muted">
                    Los tickets de Hardware y Conectividad tienen prioridad alta por defecto.
                </small>
            </div>
            <button type="submit" class="btn btn-primary">Crear Ticket</button>
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

    .btn i {
        margin-right: 0.5rem;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('#createTicketForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("tickets.store") }}',
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