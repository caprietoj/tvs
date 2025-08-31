<div class="col-md-4">
    <div class="card service-card {{ !$status ? 'clickable-pending' : '' }}" 
         data-service="{{ $service ?? '' }}" 
         data-salida-id="{{ $salidaId ?? '' }}">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-{{ $icon }} mr-2"></i>{{ $title }}
            </h3>
            <div class="card-tools">
                <span class="badge badge-{{ $status ? 'success' : 'warning' }}">
                    {{ $status ? 'Confirmado' : 'Pendiente' }}
                </span>
                @if(!$status)
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-hand-pointer"></i> Clic para confirmar
                    </small>
                @endif
            </div>
        </div>
        <div class="card-body collapse {{ $status ? 'show' : '' }}">
            @foreach($details as $label => $value)
                <div class="info-group mb-2">
                    <label class="text-muted">{{ $label }}</label>
                    <p>{{ $value ?? 'N/A' }}</p>
                </div>
            @endforeach
            
            @if($status && isset($confirmData))
                <div class="confirmation-info mt-3 pt-3 border-top">
                    <small class="text-muted">
                        <i class="fas fa-user"></i> Confirmado por: <strong>{{ $confirmData['user'] ?? 'N/A' }}</strong><br>
                        <i class="fas fa-clock"></i> Fecha: <strong>{{ $confirmData['date'] ?? 'N/A' }}</strong>
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>
