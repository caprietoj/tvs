@extends('adminlte::page')

@section('title', 'Listado de Solicitudes de Fotocopias')

@section('content_header')
    <h1 style="color: #364E76;">
        <i class="fas fa-copy mr-2"></i>Listado de Solicitudes de Fotocopias
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="copiesTable">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>N° SOLICITUD</th>
                            <th>DOCENTE</th>
                            <th>SECCIÓN</th>
                            <th>GRADO</th>
                            <th>ORIGINAL</th>
                            <th>COPIAS REQ.</th>
                            <th>BLANCO Y NEGRO</th>
                            <th>COLOR</th>
                            <th>DOBLE CARTA COLOR</th>
                            <th>IMPRESIÓN</th>
                            <th>TOTAL</th>
                            <th>FECHA ENTREGA</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($requests as $request)
                        @foreach($request->copy_items as $item)
                        <tr>
                            <td>{{ $request->request_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $request->request_number }}</span>
                            </td>
                            <td>{{ $request->requester }}</td>
                            <td>{{ $request->section }}</td>
                            <td>{{ $request->grade }}</td>
                            <td>{{ $item['original'] ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $item['copies_required'] ?? '0' }}</span>
                            </td>
                            <td class="text-center">
                                @if(isset($item['black_white']) && $item['black_white'])
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($item['color']) && $item['color'])
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($item['double_letter_color']) && $item['double_letter_color'])
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($item['impresion']) && $item['impresion'])
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-warning">{{ $item['total'] ?? '0' }}</span>
                            </td>
                            <td>{{ $request->delivery_date ? $request->delivery_date->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-center">
                                @if($request->status === 'pending')
                                    <span class="badge badge-warning">Pendiente</span>
                                @elseif($request->status === 'approved')
                                    <span class="badge badge-success">Aprobada</span>
                                @elseif($request->status === 'rejected')
                                    <span class="badge badge-danger">Rechazada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchase-requests.show', $request->id) }}" 
                                       class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($request->status === 'approved' && Auth::user()->hasAnyRole(['admin', 'almacen']))
                                        <a href="{{ route('purchase-requests.edit', $request->id) }}" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .table th {
            background-color: #364E76;
            color: white;
            text-align: center;
            vertical-align: middle;
            font-size: 0.875rem;
        }
        
        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        /* Ajustar el ancho de la tabla en pantallas pequeñas */
        .table-responsive {
            overflow-x: auto;
        }
        
        /* Estilos para los iconos de check/times */
        .fa-check {
            font-size: 1.1rem;
        }
        
        .fa-times {
            font-size: 1.1rem;
        }
        
        /* Estilos para los botones de acción */
        .btn-group .btn {
            margin-right: 2px;
        }
        
        /* Estilo para DataTables */
        .dataTables_wrapper .dataTables_filter {
            float: right;
            margin-bottom: 10px;
        }
        
        .dataTables_wrapper .dataTables_length {
            float: left;
            margin-bottom: 10px;
        }
        
        .dt-buttons {
            margin-bottom: 10px;
        }
        
        .dt-button {
            margin-right: 5px !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#copiesTable').DataTable({
                "language": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                "order": [[0, "desc"]],
                "pageLength": 25,
                "responsive": true,
                "scrollX": true,
                "columnDefs": [
                    { "width": "8%", "targets": [0, 1] }, // Fecha y N° Solicitud
                    { "width": "12%", "targets": [2, 3, 4] }, // Docente, Sección, Grado
                    { "width": "15%", "targets": 5 }, // Original
                    { "width": "6%", "targets": [6, 11, 12] }, // Copias Req., Total, Fecha Entrega
                    { "width": "5%", "targets": [7, 8, 9, 10] }, // Checkboxes
                    { "width": "8%", "targets": 13 }, // Estado
                    { "width": "10%", "targets": 14 }, // Acciones
                    { "orderable": false, "targets": [7, 8, 9, 10, 14] } // Desactivar orden en checkboxes y acciones
                ],
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-info btn-sm'
                    }
                ]
            });
        });
    </script>
@stop
