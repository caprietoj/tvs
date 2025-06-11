@extends('adminlte::page')

@section('title', 'Gestión de Cotizaciones')

@section('content_header')
    <h1>Gestión de Cotizaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Solicitudes de Compra Pendientes de Cotización</h3>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover datatable">
                    <thead>
                        <tr>
                            <th>Solicitud</th>
                            <th>Solicitante</th>
                            <th>Sección/Área</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Cotizaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseRequests as $request)
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>{{ $request->requester }}</td>
                                <td>{{ $request->section_area }}</td>
                                <td>{{ $request->request_date->format('d/m/Y') }}</td>
                                <td>{{ $request->status }}</td>
                                <td>
                                    <span class="badge badge-{{ $request->hasRequiredQuotations() ? 'success' : 'warning' }}">
                                        {{ $request->getQuotationProgress() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('purchase-requests.show', $request->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        @if (!$request->hasRequiredQuotations())
                                            <a href="{{ route('quotations.create', $request->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Agregar Cotización
                                            </a>
                                            @if ($request->quotations->count() > 0)
                                                <a href="{{ route('quotations.ask-for-more', $request->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-question-circle"></i> ¿Más Cotizaciones?
                                                </a>
                                            @endif
                                        @endif
                                        @if(in_array($request->status, ['pending', 'En Cotización']))
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    data-toggle="modal" 
                                                    data-target="#cancelDescriptionModal"
                                                    data-request-id="{{ $request->id }}"
                                                    data-request-number="{{ $request->request_number }}"
                                                    title="Anular por falta de descripción">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay solicitudes de compra pendientes de cotización.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $purchaseRequests->links() }}
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
                <form id="cancelDescriptionForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Importante:</strong> Esta acción anulará la solicitud <span id="modalRequestNumber"></span> 
                            y notificará al solicitante que debe proporcionar una descripción más detallada.
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
@stop

@section('js')
    <script>
        $(function() {
            $('.datatable').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                }
            });

            // Manejo del modal de anulación por descripción
            $('#cancelDescriptionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var requestId = button.data('request-id');
                var requestNumber = button.data('request-number');
                
                var modal = $(this);
                modal.find('#modalRequestNumber').text('#' + requestNumber);
                modal.find('#cancelDescriptionForm').attr('action', '/quotations/cancel-description/' + requestId);
            });

            // Contador de caracteres
            $('#cancel_reason').on('input', function() {
                const length = $(this).val().length;
                $('#char-count').text(length);
                
                if (length > 500) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
                
                // Verificar si se puede habilitar el botón
                const isChecked = $('#confirm_cancel').is(':checked');
                const hasReason = $(this).val().trim().length > 0;
                $('#confirmCancelBtn').prop('disabled', !(isChecked && hasReason));
            });

            // Habilitar/deshabilitar botón de confirmación
            $('#confirm_cancel').on('change', function() {
                const isChecked = $(this).is(':checked');
                const hasReason = $('#cancel_reason').val().trim().length > 0;
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

            // Limpiar modal al cerrar
            $('#cancelDescriptionModal').on('hidden.bs.modal', function () {
                $('#cancel_reason').val('');
                $('#char-count').text('0');
                $('#confirm_cancel').prop('checked', false);
                $('#confirmCancelBtn').prop('disabled', true).html('<i class="fas fa-ban"></i> Anular solicitud');
                $('#cancel_reason').removeClass('is-invalid');
            });
        });
    </script>
@stop