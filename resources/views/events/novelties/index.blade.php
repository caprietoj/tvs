@extends('adminlte::page')

@section('title', 'Novedades del Evento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Novedades: {{ $event->event_name }}</h1>
        <div>
            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Volver al Evento
            </a>
            <a href="{{ route('event.novelties.create', $event) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Novedad
            </a>
        </div>
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
                <h3 class="card-title">Listado de Novedades</h3>
            </div>
            <div class="card-body">
                @if(count($novelties) > 0)
                    <div class="timeline">
                        @foreach($novelties as $novelty)
                        <div class="time-label">
                            <span class="bg-primary">{{ $novelty->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <i class="fas fa-clipboard-list bg-info"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $novelty->created_at->format('h:i A') }}</span>
                                <h3 class="timeline-header">
                                    <strong>{{ $novelty->user->name }}</strong> registró una novedad:
                                </h3>
                                <div class="timeline-body">
                                    {{ $novelty->observation }}
                                </div>
                                <div class="timeline-footer">
                                    @if(auth()->user()->hasAnyRole(['admin', 'Admin', 'modificacion-novedad']))
                                        <a href="{{ route('event.novelties.edit', ['event' => $event, 'novelty' => $novelty]) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <form action="{{ route('event.novelties.destroy', ['event' => $event, 'novelty' => $novelty]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm delete-novelty">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay novedades registradas para este evento.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .timeline {
        position: relative;
        margin: 0 0 30px 0;
        padding: 0;
        list-style: none;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #ddd;
        left: 31px;
        margin: 0;
        border-radius: 2px;
    }
    
    .timeline > div {
        position: relative;
        margin-right: 10px;
        margin-bottom: 15px;
    }
    
    .time-label {
        border-radius: 4px;
        background-color: #fff;
        display: inline-block;
        margin-left: 45px;
        padding: 5px;
        font-weight: 600;
    }
    
    .time-label > span {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
    }
    
    .timeline-item {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 4px;
        margin-top: 0;
        background-color: #fff;
        color: #495057;
        margin-left: 60px;
        margin-right: 15px;
        margin-bottom: 10px;
        padding: 0;
        position: relative;
    }
    
    .timeline-body, .timeline-footer {
        padding: 10px 15px;
    }
    
    .timeline-footer {
        background-color: rgba(0,0,0,.05);
        border-radius: 0 0 4px 4px;
    }
    
    .timeline-header {
        border-radius: 4px 4px 0 0;
        padding: 10px;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    
    .timeline > div > i {
        width: 30px;
        height: 30px;
        font-size: 15px;
        line-height: 30px;
        position: absolute;
        color: #fff;
        background-color: #6c757d;
        border-radius: 50%;
        text-align: center;
        left: 18px;
        top: 0;
    }
</style>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert para mensajes de sesión
    @if(session('swal'))
        Swal.fire({
            icon: '{{ session("swal.icon") }}',
            title: '{{ session("swal.title") }}',
            text: '{{ session("swal.text") }}',
            showConfirmButton: true,
            timer: 3000
        });
    @endif

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