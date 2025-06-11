@extends('adminlte::page')

@section('title', 'Agregar cotización')

@section('content_header')
    <h1>Agregar Cotización - Solicitud {{ $purchaseRequest->request_number }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la solicitud</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número:</strong> {{ $purchaseRequest->request_number }}</p>
                    <p><strong>Solicitante:</strong> {{ $purchaseRequest->requester }}</p>
                    <p><strong>Sección/Área:</strong> {{ $purchaseRequest->section_area }}</p>
                    <p><strong>Fecha:</strong> {{ $purchaseRequest->request_date instanceof \DateTime ? $purchaseRequest->request_date->format('d/m/Y') : 'No establecida' }}</p>
                    <p><strong>Estado:</strong> {{ $purchaseRequest->status }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Justificación:</strong> {{ $purchaseRequest->purchase_justification }}</p>
                    <p>
                        <strong>Cotizaciones:</strong> 
                        <span class="badge badge-{{ $purchaseRequest->hasRequiredQuotations() ? 'success' : 'warning' }}">
                            {{ $purchaseRequest->getQuotationProgress() }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón para anular por falta de descripción -->
    @if(in_array($purchaseRequest->status, ['pending', 'En Cotización']))
    <div class="card border-warning">
        <div class="card-header bg-warning">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle"></i> ¿Falta información en la descripción?
            </h3>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Si considera que la solicitud no tiene suficiente descripción para poder cotizar adecuadamente, 
                puede anularla para que el solicitante proporcione más información.
            </p>
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#cancelDescriptionModal">
                <i class="fas fa-ban"></i> Anular por falta de descripción
            </button>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Agregar nueva cotización</h3>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('quotations.store', $purchaseRequest) }}" method="POST" enctype="multipart/form-data" id="quotationForm">
                @csrf
                <div class="form-group">
                    <label for="provider_name">Nombre del proveedor *</label>
                    <select name="provider_name" id="provider_name" class="form-control select2 {{ $errors->has('provider_name') ? 'is-invalid' : '' }}" required>
                        <option value="">-- Seleccione un proveedor --</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->nombre }}" {{ old('provider_name') == $proveedor->nombre ? 'selected' : '' }}>
                                {{ $proveedor->nombre }} {{ $proveedor->nit ? '- ' . $proveedor->nit : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('provider_name'))
                        <div class="invalid-feedback">{{ $errors->first('provider_name') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="total_amount">Monto total *</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" name="total_amount" id="total_amount" class="form-control {{ $errors->has('total_amount') ? 'is-invalid' : '' }}" value="{{ old('total_amount') }}" step="0.01" min="0" required>
                        @if ($errors->has('total_amount'))
                            <div class="invalid-feedback">{{ $errors->first('total_amount') }}</div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="delivery_time">Tiempo de entrega</label>
                            <input type="text" name="delivery_time" id="delivery_time" class="form-control {{ $errors->has('delivery_time') ? 'is-invalid' : '' }}" value="{{ old('delivery_time') }}" placeholder="Ej: 15 días hábiles">
                            @if ($errors->has('delivery_time'))
                                <div class="invalid-feedback">{{ $errors->first('delivery_time') }}</div>
                            @endif
                            <small class="form-text text-muted">Especifique el tiempo estimado de entrega (días, semanas, etc.)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_method">Forma de pago</label>
                            <select name="payment_method" id="payment_method" class="form-control {{ $errors->has('payment_method') ? 'is-invalid' : '' }}">
                                <option value="">-- Seleccione --</option>
                                <option value="Contado" {{ old('payment_method') == 'Contado' ? 'selected' : '' }}>Contado</option>
                                <option value="Crédito a 30 días" {{ old('payment_method') == 'Crédito a 30 días' ? 'selected' : '' }}>Crédito a 30 días</option>
                                <option value="Crédito a 60 días" {{ old('payment_method') == 'Crédito a 60 días' ? 'selected' : '' }}>Crédito a 60 días</option>
                                <option value="Anticipado" {{ old('payment_method') == 'Anticipado' ? 'selected' : '' }}>Pago anticipado</option>
                                <option value="Otro" {{ old('payment_method') == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @if ($errors->has('payment_method'))
                                <div class="invalid-feedback">{{ $errors->first('payment_method') }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="validity">Validez de la oferta</label>
                            <input type="text" name="validity" id="validity" class="form-control {{ $errors->has('validity') ? 'is-invalid' : '' }}" value="{{ old('validity') }}" placeholder="Ej: 30 días">
                            @if ($errors->has('validity'))
                                <div class="invalid-feedback">{{ $errors->first('validity') }}</div>
                            @endif
                            <small class="form-text text-muted">Período durante el cual la cotización mantiene su validez</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="warranty">Garantía</label>
                            <input type="text" name="warranty" id="warranty" class="form-control {{ $errors->has('warranty') ? 'is-invalid' : '' }}" value="{{ old('warranty') }}" placeholder="Ej: 3 meses">
                            @if ($errors->has('warranty'))
                                <div class="invalid-feedback">{{ $errors->first('warranty') }}</div>
                            @endif
                            <small class="form-text text-muted">Período de garantía ofrecido por el proveedor</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="quotation_file">Archivo de cotización (PDF) *</label>
                    <div class="custom-file">
                        <input type="file" name="quotation_file" id="quotation_file" class="custom-file-input {{ $errors->has('quotation_file') ? 'is-invalid' : '' }}" accept="application/pdf" required>
                        <label class="custom-file-label" for="quotation_file">Seleccionar archivo</label>
                        @if ($errors->has('quotation_file'))
                            <div class="invalid-feedback">{{ $errors->first('quotation_file') }}</div>
                        @endif
                    </div>
                    <small class="form-text text-muted">Solo se permiten archivos PDF (máx. 5MB)</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Guardar cotización
                    </button>
                    <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

        <div class="card-footer">
            <div class="text-muted">
                * Campos obligatorios
            </div>
        </div>
    </div>

    <!-- Modal para anular por falta de descripción -->
    <div class="modal fade" id="cancelDescriptionModal" tabindex="-1" role="dialog" aria-labelledby="cancelDescriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="cancelDescriptionModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Anular solicitud por falta de descripción
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('quotations.cancel-description', $purchaseRequest) }}" method="POST" id="cancelDescriptionForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Importante:</strong> Esta acción anulará la solicitud y notificará al solicitante 
                            que debe proporcionar una descripción más detallada.
                        </div>
                        
                        <div class="form-group">
                            <label for="cancel_reason">Motivo de la anulación *</label>
                            <textarea name="reason" id="cancel_reason" class="form-control" rows="4" 
                                      placeholder="Explique específicamente qué información adicional necesita en la descripción..." 
                                      maxlength="500" required></textarea>
                            <small class="form-text text-muted">
                                Máximo 500 caracteres. Sea específico sobre qué información falta.
                            </small>
                            <div class="text-right">
                                <small class="text-muted">
                                    <span id="char-count">0</span>/500 caracteres
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirm_cancel" required>
                                <label class="custom-control-label" for="confirm_cancel">
                                    Confirmo que he revisado la solicitud y considero que necesita más información
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning" id="confirmCancelBtn" disabled>
                            <i class="fas fa-ban"></i> Anular solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de notificación para la tercera cotización -->
    <div class="modal fade" id="notificationModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="notificationModalLabel">
                        <i class="fas fa-paper-plane mr-2"></i>Enviando notificación
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-4" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <h5>Procesando cotización</h5>
                    <p class="mb-0">Se está enviando la notificación al coordinador del área para pre-aprobación.</p>
                    <p class="text-muted">Esto puede tomar unos momentos, por favor espere...</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Inicializar Select2 para el selector de proveedores
        $('#provider_name').select2({
            placeholder: "Seleccione un proveedor",
            allowClear: true,
            width: '100%'
        });
        
        // Mostrar el nombre del archivo seleccionado
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // Variable para verificar si esta es la tercera cotización
        const isThirdQuotation = {{ $purchaseRequest->quotations->count() == 2 ? 'true' : 'false' }};

        // Mostrar spinner al enviar el formulario
        $('#quotationForm').on('submit', function(e) {
            // Validación básica del formulario
            if (!this.checkValidity()) {
                return true; // Permitir que el navegador maneje la validación estándar
            }

            // Deshabilitar el botón de envío
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            
            // Si es la tercera cotización, mostrar el modal con spinner
            if (isThirdQuotation) {
                $('#notificationModal').modal('show');
                
                // Simular un pequeño retraso para asegurar que se vea el spinner (opcional)
                setTimeout(function() {
                    return true; // Continuar con el envío después del retraso
                }, 500);
            }
            
            return true; // Permite que el formulario se envíe normalmente
        });
        
        // Si hay errores de validación y volvemos a mostrar el formulario, habilitar el botón nuevamente
        if ($('.invalid-feedback').length > 0) {
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar cotización');
        }

        // Manejo del modal de anulación por descripción
        $('#cancel_reason').on('input', function() {
            const length = $(this).val().length;
            $('#char-count').text(length);
            
            if (length > 500) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Habilitar/deshabilitar botón de confirmación
        $('#confirm_cancel').on('change', function() {
            const isChecked = $(this).is(':checked');
            const hasReason = $('#cancel_reason').val().trim().length > 0;
            $('#confirmCancelBtn').prop('disabled', !(isChecked && hasReason));
        });

        $('#cancel_reason').on('input', function() {
            const length = $(this).val().length;
            $('#char-count').text(length);
            
            const isChecked = $('#confirm_cancel').is(':checked');
            const hasReason = $(this).val().trim().length > 0;
            $('#confirmCancelBtn').prop('disabled', !(isChecked && hasReason));
        });

        // Confirmación antes de enviar anulación
        $('#cancelDescriptionForm').on('submit', function(e) {
            e.preventDefault();
            
            const reason = $('#cancel_reason').val().trim();
            if (reason.length === 0) {
                alert('Por favor, proporcione un motivo para la anulación.');
                return false;
            }
            
            if (reason.length > 500) {
                alert('El motivo no puede exceder 500 caracteres.');
                return false;
            }
            
            if (!$('#confirm_cancel').is(':checked')) {
                alert('Debe confirmar que ha revisado la solicitud.');
                return false;
            }
            
            if (confirm('¿Está seguro de que desea anular esta solicitud por falta de descripción? Esta acción no se puede deshacer.')) {
                $('#confirmCancelBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                this.submit();
            }
        });
    });
</script>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Estilos personalizados para Select2 */
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal;
        padding-left: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }
</style>
@stop