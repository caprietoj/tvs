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

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-12">
                <form method="GET" action="{{ route('approvals.index') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="request_number" class="mr-2"><strong>No. Solicitud:</strong></label>
                        <input type="text" name="request_number" id="request_number" class="form-control" 
                               value="{{ $requestNumberFilter ?? '' }}" placeholder="Buscar por número...">
                    </div>
                    <div class="form-group mr-3">
                        <label for="section" class="mr-2"><strong>Área/Sección:</strong></label>
                        <select name="section" id="section" class="form-control">
                            <option value="all">Todas las secciones</option>
                            @foreach($sections as $section)
                                <option value="{{ $section }}" {{ $sectionFilter === $section ? 'selected' : '' }}>
                                    {{ $section }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-3">
                        <label for="type" class="mr-2"><strong>Tipo:</strong></label>
                        <select name="type" id="type" class="form-control">
                            <option value="all">Todos los tipos</option>
                            <option value="purchase" {{ $typeFilter === 'purchase' ? 'selected' : '' }}>Compra</option>
                            <option value="materials" {{ $typeFilter === 'materials' ? 'selected' : '' }}>Materiales</option>
                            <option value="services" {{ $typeFilter === 'services' ? 'selected' : '' }}>Servicios</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('approvals.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No. Solicitud</th>
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
                            <td>{{ $request->request_number }}</td>
                            <td>{{ $request->requester }}</td>
                            <td>{{ $request->section_area }}</td>
                            <td>{{ $request->request_date->format('d/m/Y') }}</td>
                            <td>
                                @if($request->type === 'purchase')
                                    <span class="badge badge-primary">Compra</span>
                                @elseif($request->type === 'materials')
                                    <span class="badge badge-info">Materiales</span>
                                @elseif($request->type === 'services')
                                    <span class="badge badge-success">Servicios</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($request->type) }}</span>
                                @endif
                            </td>
                            <td>{{ $request->preApprover ? $request->preApprover->name : 'N/A' }}</td>
                            <td>
                                @if($request->hasMixedSelection())
                                    <span class="badge badge-warning">Mixta</span>
                                @elseif($request->preApprovedQuotation)
                                    {{ $request->preApprovedQuotation->provider_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($request->hasMixedSelection())
                                    ${{ number_format($request->getMixedSelectionTotal(), 2, ',', '.') }}
                                @elseif($request->preApprovedQuotation)
                                    ${{ number_format($request->preApprovedQuotation->total_amount, 2, ',', '.') }}
                                @else
                                    <span class="text-muted">N/A</span>
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