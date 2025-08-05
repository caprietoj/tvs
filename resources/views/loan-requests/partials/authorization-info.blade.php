<div class="authorization-info">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-clipboard-check mr-2"></i>Información General del Préstamo</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Solicitud ID:</strong> {{ $loan_request->id }}<br>
                            <strong>Solicitante:</strong> {{ $loan_request->full_name }}<br>
                            <strong>Monto:</strong> ${{ number_format($loan_request->amount, 2, ',', '.') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong> <span class="badge badge-success">Aprobado</span><br>
                            <strong>Fecha de Solicitud:</strong> {{ $loan_request->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($hr_signature) && $hr_signature)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-user-tie mr-2"></i>Revisión de Recursos Humanos</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-signature mr-1"></i>Revisado por:</strong><br>
                            <span class="text-primary">{{ $hr_signature }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-calendar-alt mr-1"></i>Fecha y Hora de Revisión:</strong><br>
                            @if(isset($review_date) && $review_date)
                                <span class="text-success">{{ $review_date->format('d/m/Y') }}</span><br>
                                <small class="text-muted">{{ $review_date->format('H:i:s') }}</small>
                            @else
                                <span class="text-muted">No disponible</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($admin_signature) && $admin_signature)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-user-shield mr-2"></i>Autorización Final</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-signature mr-1"></i>Autorizado por:</strong><br>
                            <span class="text-primary">{{ $admin_signature }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-calendar-check mr-1"></i>Fecha y Hora de Autorización:</strong><br>
                            @if(isset($decision_date) && $decision_date)
                                <span class="text-success">{{ $decision_date->format('d/m/Y') }}</span><br>
                                <small class="text-muted">{{ $decision_date->format('H:i:s') }}</small>
                            @else
                                <span class="text-muted">No disponible</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!isset($hr_signature) && !isset($admin_signature))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No se encontró información detallada de autorización para esta solicitud.
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <small>
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Nota:</strong> Esta información es solo visible para administradores y muestra el historial de autorización del préstamo.
                </small>
            </div>
        </div>
    </div>
</div>

<style>
.authorization-info .card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.authorization-info .card-header {
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

.authorization-info .text-primary {
    font-weight: 600;
}

.authorization-info .text-success {
    font-weight: 500;
}
</style>
