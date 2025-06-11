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
        <div class="card-header">
            <div class="d-flex justify-content-end">
                <a href="{{ route('copies-requests.export') }}" class="btn btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Exportar a Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="copiesTable">                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>DOCENTE</th>
                            <th>SECCIÓN</th>
                            <th>CURSO</th>
                            <th>BLANCO Y NEGRO</th>
                            <th>COLOR</th>
                            <th>DOBLE CARTA COLOR</th>
                            <th>FECHA DE ENTREGA</th>
                            <th>RECIBIDO POR</th>
                        </tr>
                    </thead>                    <tbody>
                    @foreach($requests as $request)
                        @foreach($request->copy_items as $item)
                        <tr>
                            <td>{{ $request->request_date->format('d/m/Y') }}</td>
                            <td>{{ $request->requester }}</td>
                            <td>{{ $request->section }}</td>
                            <td>{{ $request->grade }}</td>
                            <td class="text-center">
                                {{ $item['black_white'] ?? '0' }}
                            </td>
                            <td class="text-center">
                                {{ $item['color'] ?? '0' }}
                            </td>
                            <td class="text-center">
                                {{ $item['double_letter_color'] ?? '0' }}
                            </td>
                            <td>{{ $request->delivery_date ? $request->delivery_date->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $request->approved_by ? optional($request->approver)->name : 'Pendiente' }}</td>
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
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {            $('#copiesTable').DataTable({
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
                "dom": 'Bfrtip',
                "buttons": [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
@stop
