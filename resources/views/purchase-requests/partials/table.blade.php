<!-- Botón de aprobación masiva para fotocopias -->
@if($canBulkApproveCopies)
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Aprobación masiva disponible:</strong> 
                    Hay {{ $copiesCount }} solicitudes de fotocopias pendientes en la sección "{{ $sectionFilter ?? request('section') }}"
                </div>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#bulkApproveModal">
                    <i class="fas fa-check-double mr-1"></i>
                    Aprobar todas las fotocopias
                </button>
            </div>
        </div>
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="requestsTable">
        <thead>
            <tr>
                <th>N° Solicitud</th>
                <th>Tipo</th>
                <th>Solicitante</th>
                <th>Área/Sección</th>
                <th>Fecha</th>
                @if(in_array($typeFilter, ['copies', 'materials']))
                    <th>Observaciones</th>
                @endif
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr>
                    <td>{{ $request->request_number }}</td>
                    <td>
                        @if($request->type == 'purchase')
                            <span class="badge badge-primary">Compra</span>
                        @elseif($request->type == 'services')
                            <span class="badge badge-warning">Servicios</span>
                            @if($request->service_type === 'no_quotation')
                                <br><small><span class="badge badge-secondary">Sin Cotización</span></small>
                            @endif
                        @elseif($request->isCopiesRequest())
                            <span class="badge badge-info">Fotocopias</span>
                        @else
                            <span class="badge badge-success">Materiales</span>
                        @endif
                    </td>
                    <td>{{ $request->requester }}</td>
                    <td>{{ $request->section_area }}</td>
                    <td>{{ $request->request_date->format('d/m/Y') }}</td>
                    @if(in_array($typeFilter, ['copies', 'materials']))
                        <td class="text-center">
                            @if($typeFilter === 'copies' && $request->special_details)
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        data-toggle="popover" 
                                        data-trigger="hover" 
                                        data-placement="left"
                                        data-content="{{ $request->special_details }}" 
                                        title="Observaciones de Fotocopias">
                                    <i class="fas fa-comment-alt"></i>
                                </button>
                            @elseif($typeFilter === 'materials' && $request->observations)
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        data-toggle="popover" 
                                        data-trigger="hover" 
                                        data-placement="left"
                                        data-content="{{ $request->observations }}" 
                                        title="Observaciones de Materiales">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endif
                    <td>
                        @if($request->status == 'pending')
                            <span class="badge badge-warning">Pendiente</span>
                        @elseif($request->status == 'approved')
                            <span class="badge badge-success">Aprobada</span>
                        @elseif($request->status == 'in_process')
                            <span class="badge badge-success">Aprobada</span>
                        @elseif($request->status == 'rejected')
                            <span class="badge badge-danger">Rechazada</span>
                        @elseif($request->status == 'En Cotización')
                            <span class="badge badge-info">En Cotización</span>
                        @elseif($request->status == 'En pre-aprobación')
                            <span class="badge badge-primary">En pre-aprobación</span>
                        @elseif($request->status == 'Pre-aprobada')
                            <span class="badge badge-primary">Pre-aprobada</span>
                        @else
                            <span class="badge badge-secondary">{{ $request->status }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="{{ route('purchase-requests.show', $request) }}" class="btn btn-sm btn-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            @php
                                $permissionService = new \App\Services\PurchaseRequestPermissionService();
                            @endphp
                            @if($permissionService->canEditRequest($request))
                                <a href="{{ route('purchase-requests.edit', $request) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if($permissionService->canDeleteRequest($request))
                                <button type="button" class="btn btn-sm btn-danger" 
                                    data-toggle="modal" 
                                    data-target="#deleteModal" 
                                    data-request-id="{{ $request->id }}"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ in_array($typeFilter, ['copies', 'materials']) ? '8' : '7' }}" class="text-center">No hay solicitudes registradas</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal de Aprobación Masiva -->
@if($canBulkApproveCopies)
<div class="modal fade" id="bulkApproveModal" tabindex="-1" role="dialog" aria-labelledby="bulkApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="bulkApproveModalLabel">
                    <i class="fas fa-check-double mr-2"></i>
                    Confirmar Aprobación Masiva
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                </div>
                <h6 class="text-center mb-3">¿Está seguro de que desea aprobar todas las solicitudes de fotocopias?</h6>
                <div class="alert alert-info">
                    <strong>Detalles de la acción:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Sección:</strong> {{ $sectionFilter ?? request('section') }}</li>
                        <li><strong>Solicitudes a aprobar:</strong> {{ $copiesCount }}</li>
                        <li><strong>Tipo:</strong> Fotocopias</li>
                    </ul>
                </div>
                <p class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Esta acción marcará todas las solicitudes como aprobadas y no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <form method="POST" action="{{ route('purchase-requests.bulk-approve-copies') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="section" value="{{ $sectionFilter ?? request('section') }}">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-double mr-1"></i>
                        Aprobar {{ $copiesCount }} solicitudes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
