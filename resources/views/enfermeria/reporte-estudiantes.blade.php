@extends('adminlte::page')

@section('title', 'Reporte de Ingresos de Estudiantes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Reporte de Ingresos de Estudiantes</h1>
        <a href="{{ route('enfermeria.ingreso_estudiantes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Resumen por Fecha y Área
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm" onclick="exportTableToExcel('reportTable', 'Reporte_Ingresos_Estudiantes')">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reportTable" class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">FECHA</th>
                            <th class="text-center">PREESCOLAR</th>
                            <th class="text-center">PRIMARIA</th>
                            <th class="text-center">BACHILLERATO</th>
                            <th class="text-center">ACTIVIDADES DEPORTIVAS</th>
                            <th class="text-center">CASOS ESPECIALES</th>
                            <th class="text-center">SALIDAS</th>
                            <th class="text-center" style="min-width: 200px;">OBSERVACIONES</th>
                            <th class="text-center" style="min-width: 200px;">NOVEDADES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reporteData as $dato)
                            <tr>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($dato->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $dato->preescolar }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $dato->primaria }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $dato->bachillerato }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning">{{ $dato->deportivas }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger">{{ $dato->casos_especiales }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $dato->salidas }}</span>
                                </td>
                                <td class="text-left">
                                    <small>{{ $dato->observaciones ?: 'Sin observaciones' }}</small>
                                </td>
                                <td class="text-left">
                                    <small class="text-danger">
                                        <strong>{{ $dato->novedades ?: 'Sin novedades' }}</strong>
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> No hay datos disponibles para mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($reporteData->count() > 0)
                        <tfoot class="bg-light">
                            <tr>
                                <td class="text-right"><strong>TOTALES:</strong></td>
                                <td class="text-center">
                                    <strong class="text-info">{{ $reporteData->sum('preescolar') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-primary">{{ $reporteData->sum('primaria') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">{{ $reporteData->sum('bachillerato') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-warning">{{ $reporteData->sum('deportivas') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-danger">{{ $reporteData->sum('casos_especiales') }}</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-info">{{ $reporteData->sum('salidas') }}</strong>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table th {
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
        }
        .badge {
            font-size: 14px;
            padding: 5px 10px;
        }
        thead.thead-dark th {
            background-color: #304367 !important;
            color: white !important;
            font-weight: bold;
        }
    </style>
@stop

@section('js')
    <script>
        function exportTableToExcel(tableID, filename = '') {
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID);
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            
            // Specify file name
            filename = filename ? filename + '.xls' : 'excel_data.xls';
            
            // Create download link element
            downloadLink = document.createElement("a");
            
            document.body.appendChild(downloadLink);
            
            if(navigator.msSaveOrOpenBlob){
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob( blob, filename);
            }else{
                // Create a link to the file
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
            
                // Setting the file name
                downloadLink.download = filename;
                
                //triggering the function
                downloadLink.click();
            }
        }

        // DataTables initialization with date sorting
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('#reportTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    },
                    "order": [[0, "desc"]],
                    "pageLength": 25,
                    "responsive": true,
                    "dom": 'Bfrtip',
                    "buttons": []
                });
            }
        });
    </script>
@stop
