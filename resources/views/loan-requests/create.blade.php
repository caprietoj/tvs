@extends('adminlte::page')

@section('title', 'Nueva Solicitud de Préstamo')

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
        border-top: 3px solid var(--institutional-blue);
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    .form-group label {
        color: var(--institutional-blue);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--institutional-blue);
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .input-group-text {
        background-color: var(--institutional-blue);
        color: white;
        border: none;
        font-weight: 500;
    }

    .installment-calculator {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid #e9ecef;
    }

    .installment-calculator h5 {
        color: var(--institutional-blue);
        margin-bottom: 1rem;
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

    .btn-institutional {
        background-color: var(--institutional-blue);
        color: white;
        padding: 0.75rem 2rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-institutional:hover {
        background-color: var(--institutional-blue-hover);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-section h5 {
        color: var(--institutional-blue);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--institutional-blue);
    }

    .payment-type-selector {
        display: flex;
        gap: 15px;
    }

    .payment-type-card {
        flex: 1;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .payment-type-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-color: #aaa;
    }

    .payment-type-card.selected {
        border-color: var(--institutional-blue);
        background-color: var(--institutional-blue-light);
    }

    .payment-type-card i {
        font-size: 24px;
        color: var(--institutional-blue);
        margin-bottom: 10px;
    }

    .payment-type-card h6 {
        margin: 0;
        color: #333;
        font-weight: 600;
    }

    .payment-type-card p {
        margin: 5px 0 0;
        font-size: 0.9rem;
        color: #666;
    }

    .select-styled {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23364e76' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1em;
        padding-right: 2.5em !important;
    }

    .select-styled option {
        font-size: 1rem;
        padding: 10px;
    }

    .contract-type-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }

    .contract-type-option {
        flex: 1;
        min-width: 150px;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .contract-type-option:hover {
        border-color: #aaa;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .contract-type-option.selected {
        border-color: var(--institutional-blue);
        background-color: rgba(54, 78, 118, 0.05);
    }

    .contract-type-option i {
        display: block;
        font-size: 20px;
        color: var(--institutional-blue);
        margin-bottom: 8px;
    }

    .contract-type-option h6 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }

    .contract-type-visual {
        display: none;
    }

    @media (max-width: 767px) {
        .contract-type-option {
            min-width: 100%;
        }
    }

    .account-type-visual {
        display: none;
        margin-top: 10px;
    }
    
    .account-type-selector {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .account-type-option {
        flex: 1;
        min-width: 100px;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .account-type-option:hover {
        border-color: #aaa;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .account-type-option.selected {
        border-color: var(--institutional-blue);
        background-color: rgba(54, 78, 118, 0.05);
    }
    
    .account-type-option i {
        display: block;
        font-size: 24px;
        color: var(--institutional-blue);
        margin-bottom: 8px;
    }
    
    .account-type-option h6 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }
    
    @media (max-width: 767px) {
        .account-type-option {
            min-width: 100%;
        }
    }
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-hand-holding-usd mr-2"></i>Nueva Solicitud de Préstamo</h1>
    <div>
        @if(auth()->user() && auth()->user()->hasRole('Admin'))
        <a href="{{ route('diagnostics.routes') }}" class="btn btn-info mr-2">
            <i class="fas fa-stethoscope mr-1"></i> Diagnosticar Rutas
        </a>
        @endif
        <a href="{{ route('loan-requests.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
</div>
@stop

@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Error:</strong> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <strong><i class="fas fa-check-circle mr-1"></i> Éxito:</strong> {{ session('success') }}
    </div>
@endif

<form id="loan-request-form" action="{{ route('loan-requests.store') }}" method="POST" novalidate>
    @csrf
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Errores en el formulario</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-section">
                <h5><i class="fas fa-info-circle mr-2"></i>Información Personal</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name">Nombre Completo</label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                id="full_name" name="full_name" value="{{ old('full_name', $user->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="document_number">Número de Documento</label>
                            <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                                id="document_number" name="document_number" value="{{ old('document_number') }}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="position">Cargo</label>
                            <input type="text" class="form-control @error('position') is-invalid @enderror" 
                                id="position" name="position" value="{{ old('position') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="department">Departamento</label>
                            <input type="text" class="form-control @error('department') is-invalid @enderror" 
                                id="department" name="department" value="{{ old('department') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                id="phone" name="phone" value="{{ old('phone') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email', $user->email) }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="employment_years">Antigüedad (años)</label>
                            <input type="number" class="form-control @error('employment_years') is-invalid @enderror" 
                                id="employment_years" name="employment_years" value="{{ old('employment_years') }}" min="0" step="1">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contract_type">Tipo de Contrato</label>
                            <select class="form-control select-styled @error('contract_type') is-invalid @enderror" 
                                id="contract_type" name="contract_type">
                                <option value="">Seleccione...</option>
                                <option value="Indefinido" {{ old('contract_type') == 'Indefinido' ? 'selected' : '' }}>Indefinido</option>
                                <option value="Término Fijo" {{ old('contract_type') == 'Término Fijo' ? 'selected' : '' }}>Término Fijo</option>
                                <option value="Obra o Labor" {{ old('contract_type') == 'Obra o Labor' ? 'selected' : '' }}>Obra o Labor</option>
                                <option value="Prestación de Servicios" {{ old('contract_type') == 'Prestación de Servicios' ? 'selected' : '' }}>Prestación de Servicios</option>
                            </select>
                            <div class="contract-type-visual" style="display:none;">
                                <div class="contract-type-selector">
                                    <div class="contract-type-option" data-value="Indefinido" onclick="selectContractType(this, 'Indefinido')">
                                        <i class="fas fa-infinity"></i>
                                        <h6>Indefinido</h6>
                                    </div>
                                    <div class="contract-type-option" data-value="Término Fijo" onclick="selectContractType(this, 'Término Fijo')">
                                        <i class="fas fa-calendar-alt"></i>
                                        <h6>Término Fijo</h6>
                                    </div>
                                    <div class="contract-type-option" data-value="Obra o Labor" onclick="selectContractType(this, 'Obra o Labor')">
                                        <i class="fas fa-hard-hat"></i>
                                        <h6>Obra o Labor</h6>
                                    </div>
                                    <div class="contract-type-option" data-value="Prestación de Servicios" onclick="selectContractType(this, 'Prestación de Servicios')">
                                        <i class="fas fa-file-contract"></i>
                                        <h6>Prestación de Servicios</h6>
                                    </div>
                                </div>
                            </div>
                            @error('contract_type')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5><i class="fas fa-money-bill-wave mr-2"></i>Información del Préstamo</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Monto Solicitado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                    id="amount" name="amount" value="{{ old('amount') }}" required min="1">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="installments">Número de Cuotas</label>
                            <input type="number" class="form-control @error('installments') is-invalid @enderror" 
                                id="installments" name="installments" value="{{ old('installments') }}" required min="1">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="installment_type">
                                <i class="fas fa-clock mr-1"></i>Tipo de Cuota
                            </label>
                            <div class="payment-type-selector">
                                <div class="payment-type-card" data-value="monthly" onclick="selectPaymentType(this, 'monthly')">
                                    <i class="fas fa-calendar-alt"></i>
                                    <h6>Mensual</h6>
                                    <p>Pago una vez al mes</p>
                                </div>
                                <div class="payment-type-card" data-value="biweekly" onclick="selectPaymentType(this, 'biweekly')">
                                    <i class="fas fa-calendar-week"></i>
                                    <h6>Quincenal</h6>
                                    <p>Pago cada 15 días</p>
                                </div>
                            </div>
                            <input type="hidden" name="installment_type" id="installment_type" value="{{ old('installment_type', 'monthly') }}">
                            @error('installment_type')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="deduction_start_date">Fecha de Inicio de Descuento</label>
                            <input type="date" class="form-control @error('deduction_start_date') is-invalid @enderror" 
                                id="deduction_start_date" name="deduction_start_date" 
                                value="{{ old('deduction_start_date') }}" required
                                min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="purpose">Propósito del Préstamo</label>
                    <textarea class="form-control @error('purpose') is-invalid @enderror" 
                        id="purpose" name="purpose" rows="3">{{ old('purpose') }}</textarea>
                    <small class="form-text text-muted">Explique brevemente el motivo por el cual solicita este préstamo.</small>
                </div>

                <div class="installment-calculator">
                    <h5 class="text-center mb-3">Calculadora de Cuotas</h5>
                    <div class="calculator-result">
                        Valor por cuota: $<span id="installment-value">0</span>
                    </div>
                    <input type="hidden" name="installment_value" id="installment_value">
                    
                    <div class="form-group mt-3">
                        <label for="signature">Firma Digital</label>
                        <input type="text" class="form-control @error('signature') is-invalid @enderror" 
                            id="signature" name="signature" value="{{ old('signature', auth()->user()->name) }}" required>
                        <small class="form-text text-muted">Escriba su nombre completo como firma digital.</small>
                        @error('signature')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5><i class="fas fa-university mr-2"></i>Información de Pago</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bank_name">Entidad Bancaria</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_type">Tipo de Cuenta</label>
                            <select class="form-control @error('account_type') is-invalid @enderror" 
                                id="account_type" name="account_type">
                                <option value="">Seleccione...</option>
                                <option value="Ahorros" {{ old('account_type') == 'Ahorros' ? 'selected' : '' }}>Ahorros</option>
                                <option value="Corriente" {{ old('account_type') == 'Corriente' ? 'selected' : '' }}>Corriente</option>
                                <option value="Nómina" {{ old('account_type') == 'Nómina' ? 'selected' : '' }}>Nómina</option>
                            </select>
                            <div class="account-type-visual" style="display:none;">
                                <div class="account-type-selector">
                                    <div class="account-type-option" data-value="Ahorros" onclick="selectAccountType(this, 'Ahorros')">
                                        <i class="fas fa-piggy-bank"></i>
                                        <h6>Ahorros</h6>
                                    </div>
                                    <div class="account-type-option" data-value="Corriente" onclick="selectAccountType(this, 'Corriente')">
                                        <i class="fas fa-wallet"></i>
                                        <h6>Corriente</h6>
                                    </div>
                                    <div class="account-type-option" data-value="Nómina" onclick="selectAccountType(this, 'Nómina')">
                                        <i class="fas fa-money-check-alt"></i>
                                        <h6>Nómina</h6>
                                    </div>
                                </div>
                            </div>
                            @error('account_type')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_number">Número de Cuenta</label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                id="account_number" name="account_number" value="{{ old('account_number') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mt-4">
                <a href="{{ route('loan-requests.index') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-institutional">
                    <i class="fas fa-save mr-1"></i>Guardar Solicitud
                </button>
            </div>
        </div>
    </div>
</form>
@stop

@section('js')
<script>
$(document).ready(function() {
    console.log('Formulario de solicitud de préstamo inicializado');
    
    // Calculate installment value
    function calculateInstallment() {
        const amount = parseFloat($('#amount').val()) || 0;
        const installments = parseInt($('#installments').val()) || 1;
        const installmentValue = amount / installments;
        $('#installment-value').text(installmentValue.toLocaleString('es-CO'));
        $('#installment_value').val(installmentValue);
        console.log('Calculado valor de cuota:', installmentValue);
    }

    $('#amount, #installments').on('input', calculateInstallment);
    
    // Mejora: validar formulario antes de enviar
    $('#loan-request-form').on('submit', function(e) {
        console.log('Formulario enviado, validando...');
        let isValid = true;
        
        // Validar campos requeridos
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
                console.error('Campo requerido vacío:', $(this).attr('name'));
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Por favor complete todos los campos requeridos.');
            console.error('Formulario inválido - envío cancelado');
            return false;
        }
        
        console.log('Formulario validado correctamente, continuando envío');
    });

    // Initialize visual selectors
    $('.contract-type-visual').show();
    $('#contract_type').hide();

    $('.account-type-visual').show();
    $('#account_type').hide();

    // Set initial selection based on old value
    const contractTypeValue = '{{ old('contract_type', '') }}';
    if (contractTypeValue) {
        const contractCard = document.querySelector(`.contract-type-option[data-value="${contractTypeValue}"]`);
        if (contractCard) {
            contractCard.classList.add('selected');
        }
    }
    
    const accountTypeValue = '{{ old('account_type', '') }}';
    if (accountTypeValue) {
        const accountCard = document.querySelector(`.account-type-option[data-value="${accountTypeValue}"]`);
        if (accountCard) {
            accountCard.classList.add('selected');
        }
    }
    
    // Set initial selection for payment type
    const paymentTypeValue = '{{ old('installment_type', 'monthly') }}';
    const paymentCard = document.querySelector(`.payment-type-card[data-value="${paymentTypeValue}"]`);
    if (paymentCard) {
        paymentCard.classList.add('selected');
    }
    
    // Calculate initial installment value
    calculateInstallment();
});

function selectPaymentType(element, value) {
    // Remove selected class from all cards
    document.querySelectorAll('.payment-type-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    element.classList.add('selected');
    
    // Update hidden input
    document.getElementById('installment_type').value = value;
}

function selectContractType(element, value) {
    // Remove selected class from all cards
    document.querySelectorAll('.contract-type-option').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    element.classList.add('selected');
    
    // Update hidden select
    document.getElementById('contract_type').value = value;
}

function selectAccountType(element, value) {
    // Remove selected class from all cards
    document.querySelectorAll('.account-type-option').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    element.classList.add('selected');
    
    // Update hidden select
    document.getElementById('account_type').value = value;
}
</script>
@stop
