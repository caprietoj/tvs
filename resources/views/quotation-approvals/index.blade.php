@extends('adminlte::page')

@section('title', 'Preaprobación de Cotizaciones')

@section('content_header')
    <h1 class="m-0 text-dark">Preaprobación de Cotizaciones</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="card-title">Solicitudes Pendientes de Preaprobación</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

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
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-info-circle text-info fa-lg mr-2"></i>
                                        No hay solicitudes pendientes de preaprobación.
                                    </td>
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
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#requests-table').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });
    });
</script>
@stop