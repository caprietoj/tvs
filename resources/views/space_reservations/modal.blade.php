<div class="reservation-details">
    <dl>
        <dt>Espacio:</dt>
        <dd>{{ $spaceReservation->space->name }}</dd>
        
        <dt>Solicitante:</dt>
        <dd>{{ $spaceReservation->user->name }}</dd>
        
        <dt>Fecha:</dt>
        <dd>{{ $spaceReservation->formatted_date }}</dd>
        
        <dt>Horario:</dt>
        <dd>{{ $spaceReservation->formatted_start_time }} - {{ $spaceReservation->formatted_end_time }}</dd>
        
        <dt>Propósito:</dt>
        <dd>{{ $spaceReservation->purpose }}</dd>
        
        <dt>Estado:</dt>
        <dd>
            @switch($spaceReservation->status)
                @case('pending')
                    <span class="badge badge-warning">Pendiente</span>
                    @break
                @case('approved')
                    <span class="badge badge-success">Aprobada</span>
                    @break
                @case('rejected')
                    <span class="badge badge-danger">Rechazada</span>
                    @break
                @case('cancelled')
                    <span class="badge badge-secondary">Cancelada</span>
                    @break
                @default
                    <span class="badge badge-info">{{ $spaceReservation->status }}</span>
            @endswitch
        </dd>
        
        @if($spaceReservation->notes)
            <dt>Notas:</dt>
            <dd>{{ $spaceReservation->notes }}</dd>
        @endif
        
        @if($spaceReservation->comments)
            <dt>Comentarios:</dt>
            <dd>{{ $spaceReservation->comments }}</dd>
        @endif
        
        @if($spaceReservation->items->count() > 0)
            <dt>Implementos solicitados:</dt>
            <dd>
                <ul class="list-group list-group-flush">
                    @foreach($spaceReservation->items as $item)
                        <li class="list-group-item p-1">
                            {{ $item->spaceItem->name ?? 'Implemento' }} ({{ $item->quantity }})
                            
                            @switch($item->status)
                                @case('pending')
                                    <span class="badge badge-warning float-right">Pendiente</span>
                                    @break
                                @case('approved')
                                    <span class="badge badge-success float-right">Aprobado</span>
                                    @break
                                @case('rejected')
                                    <span class="badge badge-danger float-right">Rechazado</span>
                                    @break
                                @default
                                    <span class="badge badge-info float-right">{{ $item->status }}</span>
                            @endswitch
                        </li>
                    @endforeach
                </ul>
            </dd>
        @endif
        
        @if($spaceReservation->approved_at)
            <dt>Aprobado por:</dt>
            <dd>
                {{ optional($spaceReservation->approver)->name ?? 'Sistema' }}
                ({{ $spaceReservation->formatted_approved_at }})
            </dd>
        @endif
        
        @if($spaceReservation->rejected_at)
            <dt>Rechazado por:</dt>
            <dd>
                {{ optional($spaceReservation->rejecter)->name ?? 'Sistema' }}
                ({{ $spaceReservation->formatted_rejected_at }})
            </dd>
        @endif
    </dl>
</div>