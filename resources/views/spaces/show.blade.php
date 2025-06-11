@extends('adminlte::page')

@section('title', 'Detalles del Espacio')

@section('content_header')
    <h1>Detalles del Espacio</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title">{{ $space->name }}</h3>
                <div>
                    <a href="{{ route('spaces.edit', $space) }}" class="btn btn-warning mr-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('spaces.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información del Espacio</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Nombre:</dt>
                                <dd class="col-sm-8">{{ $space->name }}</dd>
                                
                                <dt class="col-sm-4">Descripción:</dt>
                                <dd class="col-sm-8">{{ $space->description ?? 'No disponible' }}</dd>
                                
                                <dt class="col-sm-4">Ubicación:</dt>
                                <dd class="col-sm-8">{{ $space->location ?? 'No especificada' }}</dd>
                                
                                <dt class="col-sm-4">Capacidad:</dt>
                                <dd class="col-sm-8">{{ $space->capacity ?? 'No especificada' }}</dd>
                                
                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8">
                                    @if($space->active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Estadísticas y Bloqueos</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Reservas activas:</strong> {{ $space->reservations()->whereIn('status', ['pending', 'approved'])->count() }}</p>
                            <p><strong>Días bloqueados:</strong> {{ $space->blocks()->count() }}</p>
                            
                            <div class="mt-3">
                                <a href="{{ route('space-reservations.calendar', $space->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-calendar-alt"></i> Ver Calendario
                                </a>
                                <a href="{{ route('space-blocks.by-space', $space->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-ban"></i> Administrar Bloqueos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Últimas Reservas</h5>
                </div>
                <div class="card-body">
                    @if($space->reservations()->exists())
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Horario</th>
                                        <th>Usuario</th>
                                        <th>Propósito</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($space->reservations()->with('user')->orderBy('date', 'desc')->take(5)->get() as $reservation)
                                        <tr>
                                            <td>{{ $reservation->date->format('d/m/Y') }}</td>
                                            <td>{{ $reservation->start_time }} - {{ $reservation->end_time }}</td>
                                            <td>{{ $reservation->user->name }}</td>
                                            <td>{{ $reservation->purpose }}</td>
                                            <td>
                                                @if($reservation->status == 'approved')
                                                    <span class="badge badge-success">Aprobada</span>
                                                @elseif($reservation->status == 'pending')
                                                    <span class="badge badge-warning">Pendiente</span>
                                                @elseif($reservation->status == 'rejected')
                                                    <span class="badge badge-danger">Rechazada</span>
                                                @else
                                                    <span class="badge badge-secondary">Cancelada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('space-reservations.index', ['space_id' => $space->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-list"></i> Ver Todas las Reservas
                            </a>
                        </div>
                    @else
                        <p class="text-muted">No hay reservas registradas para este espacio.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert-dismissible').alert('close');
            }, 5000);
        });
    </script>
@stop