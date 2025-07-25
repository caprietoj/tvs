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
                        <input type="number" name="total_amount" id="total_amount" class="form-control {{ $errors->has('total_amount') ? 'is-invalid' : '' }}" value="{{ old('total_amount') }}" step="0.01" min="0" required readonly>
                        @if ($errors->has('total_amount'))
                            <div class="invalid-feedback">{{ $errors->first('total_amount') }}</div>
                        @endif
                    </div>
                    <small class="form-text text-muted">El monto total se calculará automáticamente basado en el subtotal y el IVA</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="subtotal">Subtotal *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="subtotal" id="subtotal" class="form-control {{ $errors->has('subtotal') ? 'is-invalid' : '' }}" value="{{ old('subtotal') }}" step="0.01" min="0" required>
                                @if ($errors->has('subtotal'))
                                    <div class="invalid-feedback">{{ $errors->first('subtotal') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Opción de modo de aplicación de impuestos -->
                        <div class="form-group" id="tax-mode-container" style="display: none;">
                            <label>Aplicación de Impuestos</label>
                            <div class="form-check">
                                <input type="radio" name="tax_application_mode" id="tax_mode_global" class="form-check-input" value="global" {{ old('tax_application_mode', 'global') == 'global' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tax_mode_global">
                                    <strong>Aplicar globalmente</strong> <small class="text-muted">(a toda la cotización)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="tax_application_mode" id="tax_mode_per_item" class="form-check-input" value="per_item" {{ old('tax_application_mode') == 'per_item' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tax_mode_per_item">
                                    <strong>Aplicar por item</strong> <small class="text-muted">(cada item puede tener impuestos diferentes)</small>
                                </label>
                            </div>
                            <small class="form-text text-muted">Esta opción aparece cuando hay múltiples items en la cotización.</small>
                        </div>
                        
                        <!-- Impuestos globales -->
                        <div class="form-group" id="global-taxes-container">
                            <label>Impuestos Aplicables</label>
                            <div class="form-check">
                                <input type="checkbox" name="includes_iva_19" id="includes_iva_19" class="form-check-input" value="1" {{ old('includes_iva_19') ? 'checked' : '' }}>
                                <label class="form-check-label" for="includes_iva_19">
                                    Aplicar IVA (19%)
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="includes_iva_5" id="includes_iva_5" class="form-check-input" value="1" {{ old('includes_iva_5') ? 'checked' : '' }}>
                                <label class="form-check-label" for="includes_iva_5">
                                    Aplicar IVA (5%)
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="includes_ipoconsumo_8" id="includes_ipoconsumo_8" class="form-check-input" value="1" {{ old('includes_ipoconsumo_8') ? 'checked' : '' }}>
                                <label class="form-check-label" for="includes_ipoconsumo_8">
                                    Aplicar Ipoconsumo (8%)
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="includes_ipoconsumo_4" id="includes_ipoconsumo_4" class="form-check-input" value="1" {{ old('includes_ipoconsumo_4') ? 'checked' : '' }}>
                                <label class="form-check-label" for="includes_ipoconsumo_4">
                                    Aplicar Ipoconsumo (4%)
                                </label>
                            </div>
                            <small class="form-text text-muted">Seleccione los impuestos que aplican a esta cotización.</small>
                        </div>
                    </div>
                </div>

                <!-- Campos de impuestos calculados -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="iva_19_amount">IVA (19%)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="iva_19_amount" id="iva_19_amount" class="form-control" value="{{ old('iva_19_amount', '0') }}" step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="iva_5_amount">IVA (5%)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="iva_5_amount" id="iva_5_amount" class="form-control" value="{{ old('iva_5_amount', '0') }}" step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ipoconsumo_8_amount">Ipoconsumo (8%)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="ipoconsumo_8_amount" id="ipoconsumo_8_amount" class="form-control" value="{{ old('ipoconsumo_8_amount', '0') }}" step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ipoconsumo_4_amount">Ipoconsumo (4%)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="ipoconsumo_4_amount" id="ipoconsumo_4_amount" class="form-control" value="{{ old('ipoconsumo_4_amount', '0') }}" step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campo para mantener compatibilidad con IVA antiguo -->
                <input type="hidden" name="includes_iva" id="includes_iva_hidden" value="0">
                <input type="hidden" name="iva_amount" id="iva_amount_hidden" value="0">

                <!-- Items adicionales para la cotización -->
                <div class="card mt-4 mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Items Adicionales</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Agregue items adicionales si la cotización incluye productos o servicios que no estaban en la solicitud original.
                        </div>
                        
                        <div id="additional-items-container">
                            <!-- Los items adicionales se agregarán aquí dinámicamente -->
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-success" id="add-item-btn">
                            <i class="fas fa-plus"></i> Agregar Item
                        </button>
                        
                        <!-- Resumen de totales -->
                        <div class="card mt-3">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-12">
                                        <table class="table table-sm mb-0">
                                            <tbody>
                                                <tr>
                                                    <th>Subtotal Items Adicionales:</th>
                                                    <td>$<span id="additional-items-total">0.00</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen general de totales -->
                        <div class="card mt-3 bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">Resumen de Totales</h6>
                            </div>
                            <div class="card-body p-3">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Subtotal Principal:</td>
                                            <td class="text-right">$<span id="subtotal-display">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td>Subtotal Items Adicionales:</td>
                                            <td class="text-right">$<span id="additional-items-total-display">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Subtotal Total:</strong></td>
                                            <td class="text-right"><strong>$<span id="total-subtotal-display">0.00</span></strong></td>
                                        </tr>
                                        <tr id="iva-19-row" style="display: none;">
                                            <td>IVA (19%):</td>
                                            <td class="text-right">$<span id="iva-19-display">0.00</span></td>
                                        </tr>
                                        <tr id="iva-5-row" style="display: none;">
                                            <td>IVA (5%):</td>
                                            <td class="text-right">$<span id="iva-5-display">0.00</span></td>
                                        </tr>
                                        <tr id="ipoconsumo-8-row" style="display: none;">
                                            <td>Ipoconsumo (8%):</td>
                                            <td class="text-right">$<span id="ipoconsumo-8-display">0.00</span></td>
                                        </tr>
                                        <tr id="ipoconsumo-4-row" style="display: none;">
                                            <td>Ipoconsumo (4%):</td>
                                            <td class="text-right">$<span id="ipoconsumo-4-display">0.00</span></td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><strong>Total General:</strong></td>
                                            <td class="text-right"><strong>$<span id="total-general-display">0.00</span></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

        // ============ Funciones para cálculos de precios e impuestos ============
        
        // Función para calcular totales
        function calculateTotals() {
            const subtotal = parseFloat($('#subtotal').val()) || 0;
            const taxMode = $('input[name="tax_application_mode"]:checked').val();
            let additionalItemsTotal = 0;
            let totalIva19 = 0;
            let totalIva5 = 0;
            let totalIpoconsumo8 = 0;
            let totalIpoconsumo4 = 0;
            
            // Calcular totales de items adicionales
            $('.additional-item-row').each(function() {
                const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
                const price = parseFloat($(this).find('.item-price').val()) || 0;
                const itemSubtotal = quantity * price;
                $(this).find('.item-total-display').text(itemSubtotal.toFixed(2));
                additionalItemsTotal += itemSubtotal;
                
                // Si está en modo por item, calcular impuestos por item
                if (taxMode === 'per_item') {
                    const includesIva19 = $(this).find('input[name*="[includes_iva_19]"]').prop('checked');
                    const includesIva5 = $(this).find('input[name*="[includes_iva_5]"]').prop('checked');
                    const includesIpoconsumo8 = $(this).find('input[name*="[includes_ipoconsumo_8]"]').prop('checked');
                    const includesIpoconsumo4 = $(this).find('input[name*="[includes_ipoconsumo_4]"]').prop('checked');
                    
                    if (includesIva19) totalIva19 += itemSubtotal * 0.19;
                    if (includesIva5) totalIva5 += itemSubtotal * 0.05;
                    if (includesIpoconsumo8) totalIpoconsumo8 += itemSubtotal * 0.08;
                    if (includesIpoconsumo4) totalIpoconsumo4 += itemSubtotal * 0.04;
                }
            });
            
            const totalSubtotal = subtotal + additionalItemsTotal;
            
            // Calcular impuestos según el modo
            if (taxMode === 'global') {
                // Modo global: aplicar impuestos a todo el subtotal
                const includesIva19 = $('#includes_iva_19').prop('checked');
                const includesIva5 = $('#includes_iva_5').prop('checked');
                const includesIpoconsumo8 = $('#includes_ipoconsumo_8').prop('checked');
                const includesIpoconsumo4 = $('#includes_ipoconsumo_4').prop('checked');
                
                totalIva19 = includesIva19 ? totalSubtotal * 0.19 : 0;
                totalIva5 = includesIva5 ? totalSubtotal * 0.05 : 0;
                totalIpoconsumo8 = includesIpoconsumo8 ? totalSubtotal * 0.08 : 0;
                totalIpoconsumo4 = includesIpoconsumo4 ? totalSubtotal * 0.04 : 0;
            } else {
                // Modo por item: agregar impuestos del subtotal principal si aplica
                // (en este caso asumimos que el subtotal principal no tiene impuestos individuales
                // y se aplicarían solo a los items adicionales)
            }
            
            // Calcular total general
            const totalImpuestos = totalIva19 + totalIva5 + totalIpoconsumo8 + totalIpoconsumo4;
            const totalGeneral = totalSubtotal + totalImpuestos;
            
            // Actualizar campos de impuestos
            $('#iva_19_amount').val(totalIva19.toFixed(2));
            $('#iva_5_amount').val(totalIva5.toFixed(2));
            $('#ipoconsumo_8_amount').val(totalIpoconsumo8.toFixed(2));
            $('#ipoconsumo_4_amount').val(totalIpoconsumo4.toFixed(2));
            $('#total_amount').val(totalGeneral.toFixed(2));
            
            // Actualizar campos ocultos para compatibilidad
            const includesIva19 = (taxMode === 'global' && $('#includes_iva_19').prop('checked')) || 
                                 (taxMode === 'per_item' && totalIva19 > 0);
            $('#includes_iva_hidden').val(includesIva19 ? '1' : '0');
            $('#iva_amount_hidden').val(totalIva19.toFixed(2));
            
            // Actualizar displays del resumen
            $('#subtotal-display').text(subtotal.toFixed(2));
            $('#additional-items-total').text(additionalItemsTotal.toFixed(2));
            $('#additional-items-total-display').text(additionalItemsTotal.toFixed(2));
            $('#total-subtotal-display').text(totalSubtotal.toFixed(2));
            $('#iva-19-display').text(totalIva19.toFixed(2));
            $('#iva-5-display').text(totalIva5.toFixed(2));
            $('#ipoconsumo-8-display').text(totalIpoconsumo8.toFixed(2));
            $('#ipoconsumo-4-display').text(totalIpoconsumo4.toFixed(2));
            $('#total-general-display').text(totalGeneral.toFixed(2));
            
            // Mostrar/ocultar filas de impuestos según estén presentes
            $('#iva-19-row').toggle(totalIva19 > 0);
            $('#iva-5-row').toggle(totalIva5 > 0);
            $('#ipoconsumo-8-row').toggle(totalIpoconsumo8 > 0);
            $('#ipoconsumo-4-row').toggle(totalIpoconsumo4 > 0);
        }
        
        // Función para calcular total de items adicionales (método legacy - ya no se usa)
        function calculateAdditionalItemsTotal() {
            let total = 0;
            $('.additional-item-row').each(function() {
                const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
                const price = parseFloat($(this).find('.item-price').val()) || 0;
                const itemTotal = quantity * price;
                $(this).find('.item-total-display').text(itemTotal.toFixed(2));
                total += itemTotal;
            });
            return total;
        }
        
        // Event listeners para cálculos automáticos
        $('#subtotal, #includes_iva_19, #includes_iva_5, #includes_ipoconsumo_8, #includes_ipoconsumo_4').on('change input', function() {
            calculateTotals();
            updateTaxModeVisibility();
        });
        
        // Agregar item adicional
        let itemCounter = 0;
        $('#add-item-btn').on('click', function() {
            itemCounter++;
            const taxMode = $('input[name="tax_application_mode"]:checked').val();
            const perItemTaxesHtml = taxMode === 'per_item' ? `
                <div class="col-12 mt-3">
                    <div class="card card-light">
                        <div class="card-header py-2">
                            <h6 class="mb-0">Impuestos para este item</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="additional_items[${itemCounter}][includes_iva_19]" class="form-check-input item-tax-checkbox" value="1">
                                        <label class="form-check-label">IVA (19%)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="additional_items[${itemCounter}][includes_iva_5]" class="form-check-input item-tax-checkbox" value="1">
                                        <label class="form-check-label">IVA (5%)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="additional_items[${itemCounter}][includes_ipoconsumo_8]" class="form-check-input item-tax-checkbox" value="1">
                                        <label class="form-check-label">Ipoconsumo (8%)</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="additional_items[${itemCounter}][includes_ipoconsumo_4]" class="form-check-input item-tax-checkbox" value="1">
                                        <label class="form-check-label">Ipoconsumo (4%)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ` : '';
            
            const itemHtml = `
                <div class="additional-item-row border p-3 mb-3 rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Descripción 
                                    @if($purchaseRequest->type === 'purchase')
                                        del Artículo
                                    @else
                                        del Servicio
                                    @endif
                                </label>
                                ${createDescriptionSelector(itemCounter)}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" name="additional_items[${itemCounter}][quantity]" class="form-control item-quantity" step="0.01" min="0" placeholder="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Unidad</label>
                                <input type="text" name="additional_items[${itemCounter}][unit]" class="form-control" placeholder="Ej: Unidad">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Precio Unit.</label>
                                <input type="number" name="additional_items[${itemCounter}][price]" class="form-control item-price" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>Total</label>
                                <div class="form-control-plaintext">$<span class="item-total-display">0.00</span></div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm remove-item d-block">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        ${perItemTaxesHtml}
                    </div>
                </div>
            `;
            $('#additional-items-container').append(itemHtml);
            
            // Mostrar u ocultar la opción de modo de impuestos si hay más de un item
            updateTaxModeVisibility();
        });
        
        // Remover item adicional
        $(document).on('click', '.remove-item', function() {
            $(this).closest('.additional-item-row').remove();
            calculateTotals();
            updateTaxModeVisibility();
        });
        
        // Calcular cuando cambian los valores de items adicionales
        $(document).on('input change', '.item-quantity, .item-price', calculateTotals);
        
        // Calcular cuando cambian los impuestos por item
        $(document).on('change', '.item-tax-checkbox', calculateTotals);
        
        // Manejar cambio de modo de aplicación de impuestos
        $('input[name="tax_application_mode"]').on('change', function() {
            const mode = $(this).val();
            toggleTaxMode(mode);
            calculateTotals();
        });
        
        // Función para actualizar la visibilidad del selector de modo de impuestos
        function updateTaxModeVisibility() {
            const itemCount = $('.additional-item-row').length;
            const hasSubtotal = parseFloat($('#subtotal').val()) > 0;
            const totalItems = (hasSubtotal ? 1 : 0) + itemCount;
            
            if (totalItems > 1) {
                $('#tax-mode-container').show();
            } else {
                $('#tax-mode-container').hide();
                // Si solo hay un item, asegurar que esté en modo global
                $('#tax_mode_global').prop('checked', true);
                toggleTaxMode('global');
            }
        }
        
        // Función para alternar entre modos de impuestos
        function toggleTaxMode(mode) {
            if (mode === 'per_item') {
                $('#global-taxes-container').hide();
                $('.additional-item-row').each(function() {
                    if ($(this).find('.card-light').length === 0) {
                        // Agregar impuestos por item si no existen
                        const itemIndex = $('.additional-item-row').index(this) + 1;
                        const taxesHtml = `
                            <div class="col-12 mt-3">
                                <div class="card card-light">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0">Impuestos para este item</h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input type="checkbox" name="additional_items[${itemIndex}][includes_iva_19]" class="form-check-input item-tax-checkbox" value="1">
                                                    <label class="form-check-label">IVA (19%)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input type="checkbox" name="additional_items[${itemIndex}][includes_iva_5]" class="form-check-input item-tax-checkbox" value="1">
                                                    <label class="form-check-label">IVA (5%)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input type="checkbox" name="additional_items[${itemIndex}][includes_ipoconsumo_8]" class="form-check-input item-tax-checkbox" value="1">
                                                    <label class="form-check-label">Ipoconsumo (8%)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input type="checkbox" name="additional_items[${itemIndex}][includes_ipoconsumo_4]" class="form-check-input item-tax-checkbox" value="1">
                                                    <label class="form-check-label">Ipoconsumo (4%)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $(this).find('.row').append(taxesHtml);
                    }
                });
            } else {
                $('#global-taxes-container').show();
                // Remover impuestos por item
                $('.additional-item-row .card-light').parent().remove();
            }
        }
        
        // Obtener items originales de la solicitud para autocomplete
        const originalItems = [
            @if($purchaseRequest->type === 'purchase' && is_array($purchaseRequest->purchase_items))
                @foreach($purchaseRequest->purchase_items as $item)
                    @if(!empty($item['quantity']))
                        {
                            description: "{{ $item['description'] ?? '' }}",
                            quantity: "{{ $item['quantity'] ?? '' }}",
                            unit: "{{ $item['unit'] ?? '' }}",
                            type: "artículo"
                        },
                    @endif
                @endforeach
            @elseif($purchaseRequest->type === 'services' && $purchaseRequest->service_type === 'regular' && is_array($purchaseRequest->service_items))
                @foreach($purchaseRequest->service_items as $item)
                    @if(!empty($item['quantity']))
                        {
                            description: "{{ $item['description'] ?? '' }}",
                            quantity: "{{ $item['quantity'] ?? '' }}",
                            unit: "",
                            type: "servicio"
                        },
                    @endif
                @endforeach
            @endif
        ];

        // Función para crear selector de descripción
        function createDescriptionSelector(itemIndex) {
            if (originalItems.length === 0) {
                return `<input type="text" name="additional_items[${itemIndex}][description]" class="form-control" placeholder="Descripción del item">`;
            }
            
            let selectorHtml = `
                <div class="input-group">
                    <select class="form-control description-selector" data-item-index="${itemIndex}">
                        <option value="">-- Escribir descripción personalizada --</option>
            `;
            
            originalItems.forEach((item, index) => {
                selectorHtml += `<option value="${item.description}" data-quantity="${item.quantity}" data-unit="${item.unit}">${item.description}</option>`;
            });
            
            selectorHtml += `
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" title="Usar descripción personalizada" onclick="toggleCustomDescription(${itemIndex})">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <input type="text" name="additional_items[${itemIndex}][description]" class="form-control mt-2" placeholder="Descripción del item" style="display: none;">
            `;
            
            return selectorHtml;
        }

        // Función para alternar entre selector y input personalizado
        window.toggleCustomDescription = function(itemIndex) {
            const container = $(`.additional-item-row:has([data-item-index="${itemIndex}"])`);
            const selector = container.find('.description-selector').parent();
            const customInput = container.find('input[name*="[description]"]');
            
            if (customInput.is(':visible')) {
                selector.show();
                customInput.hide().val('');
            } else {
                selector.hide();
                customInput.show().focus();
            }
        };

        // Event handler para cuando se selecciona una descripción original
        $(document).on('change', '.description-selector', function() {
            const selectedOption = $(this).find(':selected');
            const description = selectedOption.val();
            const quantity = selectedOption.data('quantity');
            const unit = selectedOption.data('unit');
            const itemIndex = $(this).data('item-index');
            
            const container = $(this).closest('.additional-item-row');
            const hiddenInput = container.find('input[name*="[description]"]');
            
            if (description) {
                hiddenInput.val(description);
                
                // Auto-completar cantidad y unidad si están disponibles
                if (quantity) {
                    container.find('input[name*="[quantity]"]').val(quantity);
                }
                if (unit) {
                    container.find('input[name*="[unit]"]').val(unit);
                }
                
                // Recalcular totales
                calculateTotals();
            } else {
                hiddenInput.val('');
            }
        });

        // Calcular totales al cargar la página
        calculateTotals();
        updateTaxModeVisibility();
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