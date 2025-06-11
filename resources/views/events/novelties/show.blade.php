@extends('adminlte::page')

@section('title', 'Detalle de Novedad')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalle de Novedad</h1>
        <a href="{{ route('event.novelties.index', $event) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Novedades
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Información del Evento</h3>
            </div>
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $event->event_name }}</h3>
                <p class="text-muted text-center">{{ $event->consecutive }}</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b><i class="fas fa-calendar"></i> Fecha</b> <a class="float-right">{{ $event->service_date->format('d/m/Y') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-clock"></i> Hora</b> <a class="float-right">{{ $event->service_date->format('h:i A') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-map-marker-alt"></i> Ubicación</b> <a class="float-right">{{ $event->location }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Novedad #{{ $novelty->id }}</h3>
                <div class="card-tools">
                    <span class="badge badge-info">
                        Creado por: {{ $novelty->user->name }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="callout callout-info">
                    <h5><i class="fas fa-calendar"></i> Fecha de registro: {{ $novelty->created_at->format('d/m/Y h:i A') }}</h5>
                </div>
                
                <div class="callout callout-primary">
                    <h5>Observación:</h5>
                    <p class="mt-3">{{ $novelty->observation }}</p>
                </div>
                
                @if($novelty->created_at != $novelty->updated_at)
                <div class="callout callout-warning">
                    <small>Última actualización: {{ $novelty->updated_at->format('d/m/Y h:i A') }}</small>
                </div>
                @endif
            </div>
            <div class="card-footer">
                @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'modificacion-novedad']))
                <div class="btn-group">
                    <a href="{{ route('event.novelties.edit', ['event' => $event, 'novelty' => $novelty]) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('event.novelties.destroy', ['event' => $event, 'novelty' => $novelty]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger delete-novelty">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert para confirmar eliminación
    $(document).on('click', '.delete-novelty', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        
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
                form.submit();
            }
        });
    });
});
</script>
@stop