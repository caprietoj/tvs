<div class="table-responsive">
    <table id="requests-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Solicitud Nº</th>
                <th>Solicitante</th>
                <th>Área/Sección</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Cotizaciones</th>
                <th>Hecho Cumplido</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr class="{{ $request->status == 'Pre-aprobada' ? 'table-success' : '' }}">
                    <td>{{ $request->request_number }}</td>
                    <td>{{ $request->user->name ?? 'N/A' }}</td>
                    <td>{{ $request->section_area }}</td>
                    <td>{{ $request->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if($request->status == 'Pre-aprobada')
                            <span class="badge badge-success">Pre-aprobada</span>
                        @else
                            <span class="badge badge-warning">En cotización</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-info">
                            {{ $request->quotations->count() }} cotización(es)
                            @if($request->status == 'Pre-aprobada')
                                <span class="ml-1">
                                    <i class="fas fa-check-circle text-success"></i>
                                </span>
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="text-center">
                            @if($request->hecho_cumplido)
                                <span class="text-success" title="Hecho cumplido el {{ $request->hecho_cumplido_at ? $request->hecho_cumplido_at->format('d/m/Y H:i') : '' }} por {{ $request->hechoComplidoMarker ? $request->hechoComplidoMarker->name : '' }}">
                                    <i class="fas fa-times fa-lg"></i>
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('quotation-approvals.show', $request->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </a>
                            <a href="{{ route('quotation-approvals.compare', $request->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-balance-scale"></i> Comparar
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-info-circle text-info fa-lg mr-2"></i>
                        No hay solicitudes pendientes de preaprobación.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
