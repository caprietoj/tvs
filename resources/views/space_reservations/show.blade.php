@extends('adminlte::page')

@section('title', 'Detalle de Reserva')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalle de Reserva #{{ $reservation->id }}</h1>
        <div>
            <a href="{{ route('space-reservations.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            @if(($reservation->status === 'pending' || $reservation->status === 'approved') && $reservation->date >= now())
                <a href="{{ route('space-reservations.edit', $reservation) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <button type="button" class="btn btn-danger" onclick="confirmCancellation()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form id="cancel-form" action="{{ route('space-reservations.destroy', $reservation) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de la Reserva</h3>
                    <div class="card-tools">
                        @if($reservation->status === 'pending')
                            <span class="badge badge-warning">Pendiente</span>
                        @elseif($reservation->status === 'approved')
                            <span class="badge badge-success">Aprobada</span>
                        @elseif($reservation->status === 'rejected')
                            <span class="badge badge-danger">Rechazada</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($reservation->status) }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Espacio:</dt>
                        <dd class="col-sm-8">{{ $reservation->space->name }}</dd>
                        
                        <dt class="col-sm-4">Ubicación:</dt>
                        <dd class="col-sm-8">{{ $reservation->space->location ?? 'No especificada' }}</dd>
                        
                        <dt class="col-sm-4">Fecha:</dt>
                        <dd class="col-sm-8">
                            {{ $reservation->date->format('d/m/Y') }}
                            ({{ $reservation->date->locale('es')->isoFormat('dddd') }})
                        </dd>
                        
                        <dt class="col-sm-4">Horario:</dt>
                        <dd class="col-sm-8">{{ $reservation->formatted_start_time }} - {{ $reservation->formatted_end_time }}</dd>
                        
                        <dt class="col-sm-4">Propósito:</dt>
                        <dd class="col-sm-8">{{ $reservation->purpose }}</dd>
                        
                        @if($reservation->notes)
                            <dt class="col-sm-4">Notas:</dt>
                            <dd class="col-sm-8">{{ $reservation->notes }}</dd>
                        @endif

                        <dt class="col-sm-4">Acompañamiento de bibliotecóloga:</dt>
                        <dd class="col-sm-8">{{ $reservation->requires_librarian ? 'Sí' : 'No' }}</dd>
                        
                        <dt class="col-sm-4">Solicitante:</dt>
                        <dd class="col-sm-8">{{ $reservation->user->name }}</dd>
                        
                        <dt class="col-sm-4">Fecha de solicitud:</dt>
                        <dd class="col-sm-8">{{ $reservation->created_at->format('d/m/Y H:i') }}</dd>
                        
                        @if($reservation->updated_at != $reservation->created_at)
                            <dt class="col-sm-4">Última actualización:</dt>
                            <dd class="col-sm-8">{{ $reservation->updated_at->format('d/m/Y H:i') }}</dd>
                        @endif
                        
                        @if($reservation->approved_by)
                            <dt class="col-sm-4">Aprobado por:</dt>
                            <dd class="col-sm-8">{{ $reservation->approver->name ?? 'Usuario Eliminado' }}</dd>
                            
                            <dt class="col-sm-4">Fecha de aprobación:</dt>
                            <dd class="col-sm-8">{{ $reservation->approved_at ? $reservation->approved_at->format('d/m/Y H:i') : 'No disponible' }}</dd>
                        @endif
                        
                        @if($reservation->status === 'rejected' && $reservation->rejection_reason)
                            <dt class="col-sm-4">Motivo de rechazo:</dt>
                            <dd class="col-sm-8">{{ $reservation->rejection_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
            
            <!-- Sección de implementos solicitados -->
            @if($reservation->items->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Implementos Solicitados</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Implemento</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reservation->items as $reservationItem)
                                <tr>
                                    <td>
                                        <strong>{{ $reservationItem->item->name }}</strong>
                                        @if($reservationItem->item->description)
                                            <br><small class="text-muted">{{ $reservationItem->item->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $reservationItem->quantity }}</td>
                                    <td>
                                        @if($reservationItem->status === 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($reservationItem->status === 'approved')
                                            <span class="badge badge-success">Aprobado</span>
                                        @elseif($reservationItem->status === 'rejected')
                                            <span class="badge badge-danger">Rechazado</span>
                                        @elseif($reservationItem->status === 'returned')
                                            <span class="badge badge-info">Devuelto</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($reservationItem->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles del Espacio</h3>
                </div>
                
                <div class="card-body">
                    <dl>
                        <dt>Capacidad:</dt>
                        <dd>{{ $reservation->space->capacity ?? 'No especificada' }}</dd>
                        
                        <dt>Tipo:</dt>
                        <dd>{{ $reservation->space->type ?? 'No especificado' }}</dd>
                        
                        <dt>Recursos:</dt>
                        <dd>
                            @if($reservation->space->resources)
                                <ul class="pl-3">
                                    @foreach(explode(',', $reservation->space->resources) as $resource)
                                        <li>{{ trim($resource) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No especificados</span>
                            @endif
                        </dd>
                    </dl>
                    
                    @if($reservation->space->description)
                        <hr>
                        <h5>Descripción</h5>
                        <p>{{ $reservation->space->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    function confirmCancellation() {
        if (confirm('¿Está seguro que desea cancelar esta reserva?')) {
            document.getElementById('cancel-form').submit();
        }
    }
</script>
@endsection