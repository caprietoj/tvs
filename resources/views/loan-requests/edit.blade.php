@extends('adminlte::page')

@section('title', 'Editar Solicitud de Préstamo')

@section('css')
<style>
    :root {
        --institutional-blue: #364e76;
        --institutional-blue-hover: #293d5f;
        --institutional-blue-light: rgba(54, 78, 118, 0.1);
    }

    .content-header h1 {
        color: var(--institutional-blue);
        font-weight: 600;
    }
    
    .card {
        border-top: 3px solid var(--institutional-blue);
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    .form-control:focus {
        border-color: var(--institutional-blue);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-section h5 {
        color: var(--institutional-blue);
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--institutional-blue);
    }

    .form-group label {
        color: var(--institutional-blue);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25);
    }

    .btn-institutional {
        background-color: var(--institutional-blue);
        color: white;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-institutional:hover {
        background-color: var(--institutional-blue-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .installment-calculator {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid #e9ecef;
    }

    .calculator-result {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--institutional-blue);
        text-align: center;
        padding: 1rem;
        background: white;
        border-radius: 6px;
        margin-top: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .input-group-text {
        background-color: var(--institutional-blue);
        color: white;
        border: none;
        font-weight: 500;
    }

    .processing-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .spinner-container {
        text-align: center;
        padding: 20px;
        border-radius: 8px;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-edit mr-2"></i>Editar Solicitud de Préstamo</h1>
    <a href="{{ route('loan-requests.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<form id="loan-form" action="{{ route('loan-requests.update', $loanRequest) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-section">
                <h5><i class="fas fa-info-circle mr-2"></i>Información del Solicitante</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name">Nombre Completo</label>
                            <input type="text" class="form-control" value="{{ $loanRequest->full_name }}" readonly>
                            <input type="hidden" name="full_name" value="{{ $loanRequest->full_name }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="document_number">Número de Documento</label>
                            <input type="text" class="form-control" value="{{ $loanRequest->document_number }}" readonly>
                            <input type="hidden" name="document_number" value="{{ $loanRequest->document_number }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="position">Cargo</label>
                            <input type="text" class="form-control" value="{{ $loanRequest->position }}" readonly>
                            <input type="hidden" name="position" value="{{ $loanRequest->position }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5><i class="fas fa-money-check-alt mr-2"></i>Información del Préstamo</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Monto Solicitado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control" value="{{ number_format($loanRequest->amount, 0, ',', '.') }}" readonly>
                                <input type="hidden" name="amount" value="{{ $loanRequest->amount }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="installments">Número de Cuotas</label>
                            <input type="text" class="form-control" value="{{ $loanRequest->installments }}" readonly>
                            <input type="hidden" name="installments" value="{{ $loanRequest->installments }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="installment_type">Tipo de Cuota</label>
                            <input type="text" class="form-control" 
                                value="{{ $loanRequest->installment_type == 'monthly' ? 'Mensual' : 'Quincenal' }}" readonly>
                            <input type="hidden" name="installment_type" value="{{ $loanRequest->installment_type }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="deduction_start_date">Fecha de Inicio de Descuento</label>
                            <input type="text" class="form-control" 
                                value="{{ $loanRequest->deduction_start_date->format('d/m/Y') }}" readonly>
                            <input type="hidden" name="deduction_start_date" 
                                value="{{ $loanRequest->deduction_start_date->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="installment-calculator">
                    <h5 class="text-center mb-3">Valor de la Cuota</h5>
                    <div class="calculator-result">
                        ${{ number_format($loanRequest->installment_value, 2, ',', '.') }}
                        <input type="hidden" name="installment_value" value="{{ $loanRequest->installment_value }}">
                    </div>
                </div>
            </div>

            @hasrole(['rrhh', 'Admin'])
            <div class="form-section">
                <h5><i class="fas fa-user-tie mr-2"></i>Revisión de Recursos Humanos</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="current_salary">Salario Actual</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('current_salary') is-invalid @enderror" 
                                    id="current_salary" name="current_salary" 
                                    value="{{ old('current_salary', $loanRequest->current_salary) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>¿Tiene préstamos activos?</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="has_active_loans_yes" name="has_active_loans" 
                                    class="custom-control-input" value="1" 
                                    {{ old('has_active_loans', $loanRequest->has_active_loans) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="has_active_loans_yes">Sí</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="has_active_loans_no" name="has_active_loans" 
                                    class="custom-control-input" value="0" 
                                    {{ old('has_active_loans', $loanRequest->has_active_loans) === false ? 'checked' : '' }}>
                                <label class="custom-control-label" for="has_active_loans_no">No</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row loan-balance-section" style="display: none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="current_loan_balance">Saldo actual de préstamos</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('current_loan_balance') is-invalid @enderror" 
                                    id="current_loan_balance" name="current_loan_balance" 
                                    value="{{ old('current_loan_balance', $loanRequest->current_loan_balance) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>¿Tiene anticipos pendientes?</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="has_advances_yes" name="has_advances" 
                                    class="custom-control-input" value="1" 
                                    {{ old('has_advances', $loanRequest->has_advances) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="has_advances_yes">Sí</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="has_advances_no" name="has_advances" 
                                    class="custom-control-input" value="0" 
                                    {{ old('has_advances', $loanRequest->has_advances) === false ? 'checked' : '' }}>
                                <label class="custom-control-label" for="has_advances_no">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 advances-amount-section" style="display: none;">
                        <div class="form-group">
                            <label for="advances_amount">Monto de anticipos pendientes</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('advances_amount') is-invalid @enderror" 
                                    id="advances_amount" name="advances_amount" 
                                    value="{{ old('advances_amount', $loanRequest->advances_amount) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="hr_signature">Firma de Recursos Humanos</label>
                    <input type="text" class="form-control @error('hr_signature') is-invalid @enderror" 
                        id="hr_signature" 
                        name="hr_signature" 
                        value="{{ old('hr_signature', $loanRequest->hr_signature ?? auth()->user()->name) }}"
                        readonly>
                    @error('hr_signature')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            @endhasrole

            <div class="text-right mt-4">
                <a href="{{ route('loan-requests.index') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-institutional">
                    <i class="fas fa-save mr-1"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</form>

<div class="processing-overlay">
    <div class="spinner-container">
        <div class="spinner-border" role="status">
            <span class="sr-only">Procesando...</span>
        </div>
        <div class="processing-text mt-3">Guardando cambios...</div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    function calculateInstallment() {
        const amount = parseFloat($('#amount').val()) || 0;
        const installments = parseInt($('#installments').val()) || 1;
        const installmentValue = amount / installments;
        $('#installment-value').text(installmentValue.toLocaleString('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#installment_value').val(installmentValue);
    }

    $('#amount, #installments').on('input', calculateInstallment);

    $('#loan-form').on('submit', function() {
        $('.processing-overlay').css('display', 'flex').fadeIn(200);
        $(this).find('button[type="submit"]')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...');
    });

    // Mostrar/ocultar sección de saldo de préstamos
    $('input[name="has_active_loans"]').change(function() {
        $('.loan-balance-section').toggle($(this).val() === '1');
        if ($(this).val() === '0') {
            $('#current_loan_balance').val('');
        }
    });

    // Mostrar/ocultar sección de anticipos
    $('input[name="has_advances"]').change(function() {
        $('.advances-amount-section').toggle($(this).val() === '1');
        if ($(this).val() === '0') {
            $('#advances_amount').val('');
        }
    });

    // Inicializar estado de secciones condicionales
    $('input[name="has_active_loans"]:checked').trigger('change');
    $('input[name="has_advances"]:checked').trigger('change');
});
</script>
@stop
