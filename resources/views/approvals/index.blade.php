@extends('adminlte::page')

@section('title', 'Aprobación de Solicitudes')

@section('content_header')
<h1>Aprobación de Solicitudes</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Solicitudes pendientes de aprobación final</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error</h5>
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Área/Sección</th>
                        <th>Fecha de solicitud</th>
                        <th>Tipo</th>
                        <th>Pre-aprobada por</th>
                        <th>Cotización seleccionada</th>
                        <th>Monto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->requester }}</td>
                            <td>{{ $request->section_area }}</td>
                            <td>{{ $request->request_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $request->type === 'purchase' ? 'badge-primary' : 'badge-info' }}">
                                    {{ $request->type === 'purchase' ? 'Compra' : 'Materiales' }}
                                </span>
                            </td>
                            <td>{{ $request->preApprover ? $request->preApprover->name : 'N/A' }}</td>
                            <td>{{ $request->preApprovedQuotation ? $request->preApprovedQuotation->provider_name : 'N/A' }}</td>
                            <td>
                                @if($request->preApprovedQuotation)
                                    ${{ number_format($request->preApprovedQuotation->total_amount, 2, ',', '.') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('approvals.show', $request->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Ver detalles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No hay solicitudes pendientes de aprobación.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: none;
    }
    .table {
        margin-bottom: 0;
    }
    .pagination {
        justify-content: center;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop