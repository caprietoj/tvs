<!-- Botón de aprobación masiva para fotocopias -->
@if($canBulkApproveCopies)
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Aprobación masiva disponible:</strong> 
                    Hay {{ $copiesCount }} solicitudes de fotocopias pendientes en la sección "{{ request('section') }}"
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
