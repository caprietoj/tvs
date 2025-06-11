<div class="col-md-4">
    <div class="card service-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-{{ $icon }} mr-2"></i>{{ $title }}
            </h3>
            <div class="card-tools">
                <span class="badge badge-{{ $status ? 'success' : 'warning' }}">
                    {{ $status ? 'Confirmado' : 'Pendiente' }}
                </span>
            </div>
        </div>
        <div class="card-body collapse">
            @foreach($details as $label => $value)
                <div class="info-group mb-2">
                    <label class="text-muted">{{ $label }}</label>
                    <p>{{ $value ?? 'N/A' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
