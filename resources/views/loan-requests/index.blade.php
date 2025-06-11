@extends('adminlte::page')

@section('title', 'Solicitudes de Préstamos')

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

    .table thead th {
        background: var(--institutional-blue);
        color: white;
        padding: 15px;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
        vertical-align: middle;
    }

    .table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-hover tbody tr:hover {
        background-color: var(--institutional-blue-light);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .badge {
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
    }
    
    .badge-pending {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-reviewed {
        background-color: #17a2b8;
        color: white;
    }
    
    .badge-approved {
        background-color: #28a745;
        color: white;
    }
    
    .badge-rejected {
        background-color: #dc3545;
        color: white;
    }

    .btn {
        border-radius: 5px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin: 0 2px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .btn-new {
        background-color: var(--institutional-blue);
        color: white;
    }
    
    .btn-new:hover {
        background-color: var(--institutional-blue-hover);
        color: white;
    }

    .btn-view {
        background-color: var(--institutional-blue);
        color: white;
    }
    
    .btn-view:hover {
        background-color: var(--institutional-blue-hover);
        color: white;
    }

    .btn-group .btn {
        width: 35px;
        height: 35px;
        line-height: 35px;
        padding: 0;
        text-align: center;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 6px 12px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        margin: 0 2px;
        transition: all 0.3s ease;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--institutional-blue);
        border-color: var(--institutional-blue);
        color: white !important;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }

    .empty-state i {
        font-size: 48px;
        color: var(--institutional-blue);
        margin-bottom: 20px;
    }

    .empty-state h4 {
        color: var(--institutional-blue);
        margin-bottom: 10px;
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

    .spinner-border {
        width: 3rem;
        height: 3rem;
        color: var(--institutional-blue);
    }

    .processing-text {
        margin-top: 15px;
        color: var(--institutional-blue);
        font-weight: 500;
    }

    .btn-institutional {
        background-color: var(--institutional-blue);
        color: white;
        padding: 0.5rem 1.5rem;
    }

    .btn-institutional:hover {
        background-color: var(--institutional-blue-hover);
        color: white;
    }
</style>
@stop

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-money-check-alt mr-2"></i>Solicitudes de Préstamos</h1>
    <a href="{{ route('loan-requests.create') }}" class="btn btn-institutional">
        <i class="fas fa-plus mr-1"></i> Nueva Solicitud
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if($loanRequests->isEmpty())
            <div class="empty-state">
                <i class="fas fa-money-check-alt"></i>
                <h4>No hay solicitudes de préstamo registradas</h4>
                <p>Las solicitudes aparecerán aquí una vez que sean creadas.</p>
            </div>
        @else
            <div class="table-responsive">
                <table id="loan-requests-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th width="8%">ID</th>
                            <th width="15%">Solicitante</th>
                            <th width="15%">Monto</th>
                            <th width="12%">Cuotas</th>
                            <th width="12%">Estado</th>
                            <th width="15%">Fecha</th>
                            <th width="15%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loanRequests as $loan)
                        <tr>
                            <td>{{ $loan->id }}</td>
                            <td>{{ $loan->user->name }}</td>
                            <td>${{ number_format($loan->amount, 0, ',', '.') }}</td>
                            <td>{{ $loan->installments }} 
                                <span class="text-muted">
                                    ({{ $loan->installment_type == 'monthly' ? 'Mensuales' : 'Quincenales' }})
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'badge-pending',
                                        'reviewed' => 'badge-reviewed',
                                        'approved' => 'badge-approved',
                                        'rejected' => 'badge-rejected'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pendiente',
                                        'reviewed' => 'Revisado',
                                        'approved' => 'Aprobado',
                                        'rejected' => 'Rechazado'
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$loan->status] }}">
                                    {{ $statusLabels[$loan->status] }}
                                </span>
                            </td>
                            <td>{{ $loan->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('loan-requests.show', $loan) }}" 
                                       class="btn btn-sm btn-view" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if(auth()->user()->hasRole(['Admin', 'rrhh']) && $loan->status === 'pending')
                                        <a href="{{ route('loan-requests.edit', $loan) }}"
                                           class="btn btn-sm btn-info" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    @if(auth()->user()->can('approve-loan-requests') && ($loan->status === 'pending' || $loan->status === 'reviewed'))
                                        <button type="button" 
                                                class="btn btn-sm btn-success approve-btn" 
                                                data-id="{{ $loan->id }}"
                                                title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger reject-btn"
                                                data-id="{{ $loan->id }}"
                                                title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Modal for rejection reason -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Razón de Rechazo</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Por favor, indique la razón del rechazo<span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                        <div class="invalid-feedback">
                            Este campo es obligatorio.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="confirmReject">
                        <i class="fas fa-times mr-1"></i>Rechazar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="processing-overlay">
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
    // Initialize DataTable
    $('#loan-requests-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[5, 'desc']], // Order by date column descending
        pageLength: 10,
        responsive: true,
        stateSave: true,
        columnDefs: [
            { orderable: false, targets: 6 }, // Disable ordering for actions column
            { className: 'text-center', targets: [4, 6] }
        ]
    });

    // Tooltips initialization
    $('[title]').tooltip();

    // Function to show/hide processing overlay
    function showProcessing() {
        $('.processing-overlay').css('display', 'flex').fadeIn(200);
    }

    function hideProcessing() {
        $('.processing-overlay').fadeOut(200);
    }

    // Handle approve button click
    $(document).on('click', '.approve-btn', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: '¿Aprobar préstamo?',
            text: "Esta acción no se puede deshacer",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showProcessing();
                
                $.ajax({
                    url: `{{ url('/') }}/loan-requests/${id}/approve`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        hideProcessing();
                        if (response.success) {
                            Swal.fire({
                                title: '¡Aprobado!',
                                text: 'El préstamo ha sido aprobado correctamente',
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Ha ocurrido un error', 'error');
                        }
                    },
                    error: function(xhr) {
                        hideProcessing();
                        console.error('XHR Response:', xhr);
                        let message = 'Error al aprobar el préstamo';
                        try {
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    message = response.message;
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                        }
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });

    // Handle reject button click
    $('.reject-btn').click(function(e) {
        e.preventDefault();
        let requestId = $(this).data('id');
        $('#rejectForm').data('request-id', requestId);
        $('#rejectModal').modal('show');
    });

    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        
        let requestId = $(this).data('request-id');
        let reason = $('#rejection_reason').val().trim();
        
        if (!reason) {
            $('#rejection_reason').addClass('is-invalid');
            return false;
        }

        $('#rejection_reason').removeClass('is-invalid');
        $('#confirmReject').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Procesando...');

        $.ajax({
            url: `/loan-requests/${requestId}/reject`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                rejection_reason: reason
            },
            success: function(response) {
                $('#rejectModal').modal('hide');
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Solicitud Rechazada!',
                        text: 'La solicitud ha sido rechazada exitosamente.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                $('#confirmReject').prop('disabled', false)
                    .html('<i class="fas fa-times mr-1"></i>Rechazar');
                
                let errorMessage = 'Error al rechazar la solicitud';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    $('#rejectModal').on('hidden.bs.modal', function() {
        $('#rejection_reason').val('').removeClass('is-invalid');
        $('#confirmReject').prop('disabled', false)
            .html('<i class="fas fa-times mr-1"></i>Rechazar');
    });
});
</script>
@stop
