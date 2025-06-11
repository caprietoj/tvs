@extends('adminlte::page')

@section('title', 'Detalle de Solicitud de Préstamo')

@section('css')
<style>
    :root {
        --institutional-blue: #364e76;
        --institutional-blue-hover: #293d5f;
    }

    .content-header h1 {
        color: var(--institutional-blue);
    }

    .card {
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-top: 3px solid var(--institutional-blue);
    }

    .info-box {
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }

    .info-box-icon {
        border-radius: 8px 0 0 8px;
        opacity: 0.8;
    }

    .info-box-content {
        padding: 15px;
    }

    .info-box-number {
        font-size: 1.4rem;
        font-weight: 600;
        color: var(--institutional-blue);
    }

    .info-box-text {
        color: #666;
        font-weight: 500;
    }

    .detail-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .detail-section h5 {
        color: var(--institutional-blue);
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--institutional-blue);
    }

    .detail-row {
        margin-bottom: 15px;
    }

    .detail-label {
        font-weight: 600;
        color: #495057;
    }

    .detail-value {
        color: #212529;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .status-pending { background-color: #ffc107; color: #212529; }
    .status-reviewed { background-color: #17a2b8; color: white; }
    .status-approved { background-color: #28a745; color: white; }
    .status-rejected { background-color: #dc3545; color: white; }

    .signature-box {
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: white;
        margin-top: 10px;
    }

    .signature-box p {
        margin: 0;
        font-style: italic;
        color: #666;
    }

    .timeline {
        margin-top: 20px;
    }

    .timeline-item {
        padding: 15px;
        border-left: 2px solid var(--institutional-blue);
        position: relative;
        margin-bottom: 15px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 20px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--institutional-blue);
    }

    .btn-institutional {
        background-color: var(--institutional-blue);
        color: white;
        padding: 8px 20px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-institutional:hover {
        background-color: var(--institutional-blue-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .payment-schedule {
        margin-top: 20px;
    }
    
    .payment-schedule table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .payment-schedule th,
    .payment-schedule td {
        padding: 10px;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    
    .payment-schedule th {
        background-color: var(--institutional-blue);
        color: white;
        font-weight: 500;
    }
    
    .payment-schedule tr:nth-child(even) {
        background-color: rgba(0,0,0,0.02);
    }
    
    .payment-schedule tr:hover {
        background-color: rgba(54, 78, 118, 0.05);
    }

    .loan-purpose {
        margin-top: 20px;
        padding: 15px;
        border-radius: 6px;
        border-left: 4px solid var(--institutional-blue);
        background: #f8f9fa;
    }

    .document-link {
        display: inline-block;
        padding: 8px 15px;
        background: var(--institutional-blue);
        color: white;
        border-radius: 4px;
        margin-top: 10px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .document-link:hover {
        background: var(--institutional-blue-hover);
        transform: translateY(-2px);
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .financial-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }
    
    .financial-data {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .financial-label {
        font-weight: 500;
        color: #555;
    }
    
    .progress-tracker {
        margin: 30px 0;
    }
    
    .progress-step {
        display: flex;
        margin-bottom: 10px;
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ccc;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }
    
    .step-icon.active {
        background: var(--institutional-blue);
    }
    
    .step-content {
        flex: 1;
        padding-bottom: 15px;
        position: relative;
    }
    
    .step-content::after {
        content: '';
        position: absolute;
        left: -23px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: #ccc;
    }
    
    .progress-step:last-child .step-content::after {
        display: none;
    }
    
    .step-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .step-description {
        color: #666;
        font-size: 0.9rem;
    }

    .attachments-section {
        margin-top: 20px;
    }
    
    .attachment-item {
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .attachment-icon {
        margin-right: 15px;
        color: var(--institutional-blue);
        font-size: 24px;
    }
    
    .attachment-details {
        flex: 1;
    }
    
    .attachment-name {
        font-weight: 500;
    }
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-money-check-alt mr-2"></i>Solicitud de Préstamo #{{ $loanRequest->id }}</h1>
    <a href="{{ route('loan-requests.index') }}" class="btn btn-institutional">
        <i class="fas fa-arrow-left mr-1"></i> Volver al listado
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Monto Solicitado</span>
                <span class="info-box-number">${{ number_format($loanRequest->amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cuotas</span>
                <span class="info-box-number">
                    {{ $loanRequest->installments }}
                    <small>({{ $loanRequest->installment_type == 'monthly' ? 'Mensuales' : 'Quincenales' }})</small>
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Cuota</span>
                <span class="info-box-number">${{ number_format($loanRequest->installment_value, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="detail-section">
                    <h5><i class="fas fa-user mr-2"></i>Información del Solicitante</h5>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Nombre Completo</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->full_name }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Documento</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->document_number }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Cargo</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->position }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Departamento</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->department ?? 'No especificado' }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Teléfono</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->phone ?? 'No especificado' }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Correo Electrónico</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->email ?? $loanRequest->user->email }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Antigüedad</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->employment_years ?? 'No especificado' }} años</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Tipo de Contrato</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->contract_type ?? 'No especificado' }}</div>
                    </div>
                </div>

                <!-- Sección de Información Financiera y Laboral -->
                <div class="detail-section">
                    <h5><i class="fas fa-file-invoice-dollar mr-2"></i>Información Financiera y Laboral</h5>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Salario Actual</div>
                        <div class="col-md-8 detail-value">
                            @if(isset($loanRequest->current_salary))
                                ${{ number_format($loanRequest->current_salary, 0, ',', '.') }}
                            @else
                                No registrado
                            @endif
                        </div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Préstamos Activos</div>
                        <div class="col-md-8 detail-value">
                            @if(isset($loanRequest->has_active_loans))
                                @if($loanRequest->has_active_loans)
                                    <span class="badge badge-warning">Sí</span>
                                    @if(isset($loanRequest->current_loan_balance) && $loanRequest->current_loan_balance > 0)
                                        - Saldo actual: ${{ number_format($loanRequest->current_loan_balance, 0, ',', '.') }}
                                    @endif
                                @else
                                    <span class="badge badge-success">No</span>
                                @endif
                            @else
                                No verificado
                            @endif
                        </div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Anticipos Pendientes</div>
                        <div class="col-md-8 detail-value">
                            @if(isset($loanRequest->has_advances))
                                @if($loanRequest->has_advances)
                                    <span class="badge badge-warning">Sí</span>
                                    @if(isset($loanRequest->advances_amount) && $loanRequest->advances_amount > 0)
                                        - Monto: ${{ number_format($loanRequest->advances_amount, 0, ',', '.') }}
                                    @endif
                                @else
                                    <span class="badge badge-success">No</span>
                                @endif
                            @else
                                No verificado
                            @endif
                        </div>
                    </div>
                    @if(isset($loanRequest->hr_signature) && !empty($loanRequest->hr_signature))
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Verificado por RRHH</div>
                        <div class="col-md-8 detail-value">
                            <div class="signature-box">
                                <p>{{ $loanRequest->hr_signature }}</p>
                                @if(isset($loanRequest->review_date))
                                    <small class="text-muted">{{ $loanRequest->review_date->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="detail-section">
                    <h5><i class="fas fa-calendar-alt mr-2"></i>Información del Préstamo</h5>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Monto Solicitado</div>
                        <div class="col-md-8 detail-value">${{ number_format($loanRequest->amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Número de Cuotas</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->installments }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Tipo de Cuota</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->installment_type == 'monthly' ? 'Mensual' : 'Quincenal' }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Valor de Cuota</div>
                        <div class="col-md-8 detail-value">${{ number_format($loanRequest->installment_value, 0, ',', '.') }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Interés</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->interest_rate ?? '0' }}%</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Fecha de Inicio de Descuento</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->deduction_start_date ? $loanRequest->deduction_start_date->format('d/m/Y') : 'Por definir' }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Fecha Estimada de Finalización</div>
                        <div class="col-md-8 detail-value">
                            @if($loanRequest->deduction_start_date && $loanRequest->installments)
                                @php
                                    $endDate = clone $loanRequest->deduction_start_date;
                                    if($loanRequest->installment_type == 'monthly') {
                                        $endDate->addMonths($loanRequest->installments - 1);
                                    } else {
                                        $endDate->addDays(($loanRequest->installments - 1) * 15);
                                    }
                                @endphp
                                {{ $endDate->format('d/m/Y') }}
                            @else
                                Por definir
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Detalles bancarios -->
                <div class="detail-section">
                    <h5><i class="fas fa-university mr-2"></i>Información de Pago</h5>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Entidad Bancaria</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->bank_name ?? 'No especificado' }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Tipo de Cuenta</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->account_type ?? 'No especificado' }}</div>
                    </div>
                    <div class="row detail-row">
                        <div class="col-md-4 detail-label">Número de Cuenta</div>
                        <div class="col-md-8 detail-value">{{ $loanRequest->account_number ?? 'No especificado' }}</div>
                    </div>
                </div>

                <!-- Propósito del préstamo -->
                <div class="loan-purpose">
                    <h5><i class="fas fa-info-circle mr-2"></i>Propósito del Préstamo</h5>
                    <p>{{ $loanRequest->purpose ?? 'No se ha especificado el propósito del préstamo.' }}</p>
                </div>

                <!-- Cronograma de pagos -->
                @if($loanRequest->status === 'approved' && $loanRequest->deduction_start_date && $loanRequest->installments)
                <div class="payment-schedule">
                    <h5><i class="fas fa-calendar-check mr-2"></i>Cronograma de Pagos</h5>
                    <table>
                        <thead>
                            <tr>
                                <th>Cuota</th>
                                <th>Fecha Estimada</th>
                                <th>Valor</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $paymentDate = clone $loanRequest->deduction_start_date;
                            @endphp
                            @for($i = 1; $i <= $loanRequest->installments; $i++)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>
                                        @if($i > 1)
                                            @php
                                                if($loanRequest->installment_type == 'monthly') {
                                                    $paymentDate->addMonth();
                                                } else {
                                                    $paymentDate->addDays(15);
                                                }
                                            @endphp
                                        @endif
                                        {{ $paymentDate->format('d/m/Y') }}
                                    </td>
                                    <td>${{ number_format($loanRequest->installment_value, 0, ',', '.') }}</td>
                                    <td>
                                        @if($paymentDate->isPast())
                                            <span class="badge badge-success">Pagado</span>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Documentos adjuntos -->
                @if(isset($loanRequest->attachments) && count($loanRequest->attachments) > 0)
                <div class="attachments-section">
                    <h5><i class="fas fa-paperclip mr-2"></i>Documentos Adjuntos</h5>
                    
                    @foreach($loanRequest->attachments as $attachment)
                    <div class="attachment-item">
                        <div class="attachment-icon">
                            <i class="far fa-file-pdf"></i>
                        </div>
                        <div class="attachment-details">
                            <div class="attachment-name">{{ $attachment->name }}</div>
                            <div class="text-muted">{{ $attachment->size }} - Subido el {{ $attachment->created_at->format('d/m/Y') }}</div>
                        </div>
                        <a href="{{ route('loan-requests.download-attachment', $attachment->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($loanRequest->status !== 'pending')
                <div class="progress-tracker">
                    <h5><i class="fas fa-history mr-2"></i>Historial de la Solicitud</h5>
                    
                    <div class="progress-step">
                        <div class="step-icon active">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="step-content">
                            <div class="step-title">Solicitud Creada</div>
                            <div class="step-description">
                                <strong>{{ $loanRequest->created_at->format('d/m/Y H:i') }}</strong> por {{ $loanRequest->user->name }}
                            </div>
                        </div>
                    </div>

                    @if($loanRequest->status === 'reviewed' || $loanRequest->status === 'approved' || $loanRequest->status === 'rejected')
                        <div class="progress-step">
                            <div class="step-icon active">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Revisado por RRHH</div>
                                <div class="step-description">
                                    <strong>{{ $loanRequest->review_date ? $loanRequest->review_date->format('d/m/Y H:i') : 'Fecha no disponible' }}</strong>
                                    <div class="signature-box mt-2">
                                        <p>Firma: {{ $loanRequest->hr_signature ?? 'No disponible' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($loanRequest->status === 'approved' || $loanRequest->status === 'rejected')
                        <div class="progress-step">
                            <div class="step-icon active">
                                <i class="fas fa-{{ $loanRequest->status === 'approved' ? 'check' : 'times' }}"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">{{ $loanRequest->status === 'approved' ? 'Aprobado' : 'Rechazado' }} por Financiera</div>
                                <div class="step-description">
                                    <strong>{{ $loanRequest->decision_date ? $loanRequest->decision_date->format('d/m/Y H:i') : 'Fecha no disponible' }}</strong>
                                    @if($loanRequest->admin_signature)
                                        <div class="signature-box mt-2">
                                            <p>Firma: {{ $loanRequest->admin_signature }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($loanRequest->status === 'approved')
                        <div class="progress-step">
                            <div class="step-icon active">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Préstamo Desembolsado</div>
                                <div class="step-description">
                                    <strong>{{ $loanRequest->disbursement_date ? $loanRequest->disbursement_date->format('d/m/Y') : 'Pendiente de desembolso' }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estado de la Solicitud</h3>
            </div>
            <div class="card-body text-center">
                @php
                    $statusClasses = [
                        'pending' => 'status-pending',
                        'reviewed' => 'status-reviewed',
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected'
                    ];
                    $statusLabels = [
                        'pending' => 'Pendiente',
                        'reviewed' => 'Revisado',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado'
                    ];
                @endphp
                <span class="status-badge {{ $statusClasses[$loanRequest->status] }}">
                    {{ $statusLabels[$loanRequest->status] }}
                </span>

                @if($loanRequest->status === 'rejected' && $loanRequest->rejection_reason)
                    <div class="alert alert-danger mt-3">
                        <h5><i class="icon fas fa-ban"></i> Motivo del Rechazo</h5>
                        {{ $loanRequest->rejection_reason }}
                    </div>
                @endif

                <!-- Información financiera y capacidad de pago -->
                @if($loanRequest->status === 'reviewed' || $loanRequest->status === 'approved')
                    <div class="financial-info mt-4">
                        <h5 class="mb-3">Análisis Financiero</h5>
                        
                        <div class="financial-data">
                            <div class="financial-label">Salario Mensual:</div>
                            <div class="financial-value">${{ number_format($loanRequest->current_salary ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        @php
                            $currentDeductions = 0;
                            if(isset($loanRequest->current_loan_balance)) {
                                $currentDeductions += $loanRequest->current_loan_balance;
                            }
                            if(isset($loanRequest->advances_amount)) {
                                $currentDeductions += $loanRequest->advances_amount;
                            }
                            
                            // Calcular capacidad máxima (50% del salario)
                            $maxCapacity = isset($loanRequest->current_salary) ? ($loanRequest->current_salary * 0.5) : 0;
                        @endphp
                        
                        <div class="financial-data">
                            <div class="financial-label">Deducciones Actuales:</div>
                            <div class="financial-value">${{ number_format($currentDeductions, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="financial-data">
                            <div class="financial-label">Capacidad Máx. Endeudamiento:</div>
                            <div class="financial-value">${{ number_format($maxCapacity, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="financial-data">
                            <div class="financial-label">Cuota del préstamo:</div>
                            <div class="financial-value">${{ number_format($loanRequest->installment_value, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="financial-data">
                            <div class="financial-label">Capacidad después de préstamo:</div>
                            <div class="financial-value">
                                @php
                                    $remainingCapacity = $maxCapacity - $currentDeductions - $loanRequest->installment_value;
                                @endphp
                                <span class="{{ $remainingCapacity < 0 ? 'text-danger' : 'text-success' }}">
                                    ${{ number_format(max(0, $remainingCapacity), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($loanRequest->status === 'approved')
                    <div class="alert alert-success mt-4">
                        <h5 class="mb-2"><i class="icon fas fa-check"></i> Préstamo Aprobado</h5>
                        <p>El préstamo ha sido aprobado y será desembolsado según lo acordado.</p>
                        
                        @if(isset($loanRequest->disbursement_date))
                            <p><strong>Fecha de desembolso:</strong> {{ $loanRequest->disbursement_date->format('d/m/Y') }}</p>
                        @endif
                        
                        @if(isset($loanRequest->approval_notes) && !empty($loanRequest->approval_notes))
                            <p><strong>Notas:</strong> {{ $loanRequest->approval_notes }}</p>
                        @endif
                    </div>
                @endif

                @if(auth()->user()->can('approve-loan-requests') && ($loanRequest->status === 'pending' || $loanRequest->status === 'reviewed'))
                    <div class="mt-4">
                        <button type="button" class="btn btn-success approve-btn btn-block mb-2">
                            <i class="fas fa-check mr-1"></i> Aprobar Solicitud
                        </button>
                        <button type="button" class="btn btn-danger reject-btn btn-block" data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times mr-1"></i> Rechazar Solicitud
                        </button>
                    </div>
                @endif

                <!-- Documento PDF de la solicitud -->
                @if($loanRequest->status === 'approved')
                    <div class="mt-4">
                        <a href="{{ route('loan-requests.generate-pdf', $loanRequest->id) }}" class="document-link" target="_blank">
                            <i class="far fa-file-pdf mr-2"></i> Descargar Formato de Préstamo
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Acciones relacionadas a la solicitud -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Acciones Disponibles</h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="{{ route('loan-requests.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-list mr-2"></i> Ver todas las solicitudes
                    </a>
                    
                    @if($loanRequest->status === 'approved')
                        <a href="{{ route('loan-requests.amortization', $loanRequest->id) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-table mr-2"></i> Ver tabla de amortización
                        </a>
                    @endif
                    
                    @if(auth()->user()->can('create-loan-requests'))
                        <a href="{{ route('loan-requests.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus mr-2"></i> Crear nueva solicitud
                        </a>
                    @endif
                    
                    @if(auth()->user()->can('edit-loan-requests') && $loanRequest->status === 'pending')
                        <a href="{{ route('loan-requests.edit', $loanRequest->id) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit mr-2"></i> Editar esta solicitud
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for rejection reason -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Motivo de Rechazo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rejectForm" action="{{ route('loan-requests.reject', $loanRequest->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="rejection_reason">Por favor, indique el motivo del rechazo:</label>
                        <textarea id="rejection_reason" class="form-control" rows="4"></textarea>
                        <div class="invalid-feedback">El motivo de rechazo es obligatorio.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" id="confirmReject" class="btn btn-danger">Rechazar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Processing overlay -->
<div class="processing-overlay" style="display: none;">
    <div class="spinner-container">
        <div class="spinner-border" role="status">
            <span class="sr-only">Procesando...</span>
        </div>
        <div class="processing-text">Procesando solicitud...</div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Handle reject form submission
    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        
        const reason = $('#rejection_reason').val().trim();
        if (!reason) {
            Swal.fire('Error', 'Debe proporcionar una razón para el rechazo', 'error');
            return;
        }
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                rejection_reason: reason
            },
            beforeSend: function() {
                $('.processing-overlay').css('display', 'flex').fadeIn(200);
                $('#confirmReject').prop('disabled', true);
            },
            success: function(response) {
                $('.processing-overlay').fadeOut(200);
                $('#rejectModal').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Solicitud Rechazada',
                        text: 'La solicitud ha sido rechazada exitosamente',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                $('.processing-overlay').fadeOut(200);
                $('#confirmReject').prop('disabled', false);
                
                let errorMessage = 'Error al rechazar la solicitud';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    // Reset modal when closed
    $('#rejectModal').on('hidden.bs.modal', function() {
        $('#rejection_reason').val('').removeClass('is-invalid');
        $('#confirmReject').prop('disabled', false);
    });

    $('.approve-btn').click(function() {
        Swal.fire({
            title: '¿Confirmar aprobación?',
            text: "Esta acción no se puede deshacer",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("loan-requests.approve", $loanRequest) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('.approve-btn').prop('disabled', true);
                        $('.approve-btn').html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: '¡Aprobado!',
                                text: 'La solicitud ha sido aprobada correctamente',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Ha ocurrido un error', 'error');
                        }
                    },
                    error: function(xhr) {
                        $('.approve-btn').prop('disabled', false);
                        $('.approve-btn').html('<i class="fas fa-check mr-1"></i> Aprobar');
                        
                        let errorMessage = 'Error al aprobar la solicitud';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }
        });
    });
});
</script>
@stop
