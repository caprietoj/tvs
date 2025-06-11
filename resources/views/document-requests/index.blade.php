@extends('adminlte::page')

@section('title', 'Solicitudes de Documentos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">Solicitudes de Documentos</h1>
        <a href="{{ route('document-requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nueva Solicitud
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="requestsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Documento</th>
                        <th>Estado</th>
                        <th>Adjunto</th> <!-- New column -->
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ $request->document->name }}</td>
                            <td>
                              <span class="badge 
                                @if($request->status == 'abierto') badge-info 
                                @elseif($request->status == 'en proceso') badge-warning 
                                @else badge-success 
                                @endif">{{ $request->status }}</span>
                            </td>
                                    <td>
                                    @php
            // Buscar el archivo del certificado basado en el naming convention
            $files = Storage::disk('public')->files('certificates');
            $pattern = $request->id . '-' . \Str::slug($request->user->name) . '-';
            $certificateFile = collect($files)->first(function($file) use ($pattern) {
                return strpos(basename($file), $pattern) === 0;
            });
        @endphp

        @if($certificateFile)
            <a href="{{ asset('storage/' . $certificateFile) }}" target="_blank">Ver Certificado</a>
        @else
            N/A
        @endif
                            </td>
                            <td>
                              @can('editar-solicitud')<a href="{{ route('document-requests.edit', $request) }}" class="btn btn-sm btn-info">Editar</a>@endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --success: #28a745;
        --warning: #ffc107;
        --info: #17a2b8;
    }

    /* Header Styles */
    .text-primary {
        color: var(--primary) !important;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* Card Styles */
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        padding: 1.25rem;
    }

    /* Button Styles */
    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        padding: 0.5rem 1.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #2a3d5d;
        border-color: #2a3d5d;
        transform: translateY(-2px);
    }

    .btn-info {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    /* Table Styles */
    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 1rem;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }

    /* Badge Styles */
    .badge {
        padding: 0.5em 1em;
        font-size: 0.85em;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge-info { 
        background-color: var(--info);
        color: white;
    }

    .badge-warning {
        background-color: var(--warning);
        color: #000;
    }

    .badge-success {
        background-color: var(--success);
        color: white;
    }

    /* Link Styles */
    a {
        color: var(--primary);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    a:hover {
        color: #2a3d5d;
        text-decoration: underline;
    }

    /* DataTables Customization */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        color: white !important;
        border: 1px solid var(--primary) !important;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #2a3d5d !important;
        color: white !important;
        border: 1px solid #2a3d5d !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .table-responsive {
            border: 0;
        }
        
        .badge {
            display: inline-block;
            margin: 2px 0;
        }
    }
</style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#requestsTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@stop