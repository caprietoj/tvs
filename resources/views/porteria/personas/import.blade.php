@extends('adminlte::page')

@section('title', 'Importar Personas - Portería')

@section('content_header')
    <h1><i class="fas fa-file-excel"></i> Importar Personas desde Excel</h1>
@stop

@section('content')
    {{-- Mensajes --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {!! session('success') !!}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {!! session('warning') !!}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {!! session('error') !!}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Pegar Datos de Excel</h3>
                </div>
                <form action="{{ route('porteria.personas.import.process') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info-circle"></i> Instrucciones:</h5>
                            <ol>
                                <li>Abra su archivo Excel con los datos de las personas</li>
                                <li><strong>Seleccione SOLO las celdas con datos</strong> (sin encabezados, sin filas vacías)</li>
                                <li>Copie los datos seleccionados (Ctrl + C)</li>
                                <li>Haga clic en el área de texto de abajo</li>
                                <li>Pegue los datos (Ctrl + V)</li>
                                <li>Haga clic en "Importar Datos"</li>
                            </ol>
                            <p class="mb-0"><strong>Formato esperado (7 columnas separadas automáticamente por Excel):</strong><br>
                            <code>Documento | Nombre | Apellido | Tipo | Email | Teléfono | Grado</code></p>
                            <p class="text-sm mb-0 mt-2">
                                <i class="fas fa-exclamation-triangle text-warning"></i> 
                                <strong>Importante:</strong> NO escriba manualmente. Debe copiar directamente desde Excel para que los tabuladores se mantengan.
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="data">Datos de Excel <span class="text-danger">*</span></label>
                            <textarea 
                                name="data" 
                                id="data" 
                                rows="15" 
                                class="form-control @error('data') is-invalid @enderror" 
                                placeholder="Pegue aquí los datos copiados de Excel...
Ejemplo:
1234567890	Juan	Pérez	estudiante	juan@email.com	3001234567	5°A
0987654321	María	López	empleado	maria@email.com	3009876543	Coordinadora"
                                required
                            >{{ old('data') }}</textarea>
                            @error('data')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Tipo:</strong> Use "estudiante" o "empleado" (si no especifica, será "estudiante" por defecto)
                            </small>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Importante:</strong> Los documentos duplicados serán omitidos automáticamente.
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-upload"></i> Importar Datos
                        </button>
                        <a href="{{ route('porteria.personas.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Ejemplo de Excel</h3>
                </div>
                <div class="card-body">
                    <h6><strong>Estructura de las columnas:</strong></h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>#</th>
                                <th>Campo</th>
                                <th>Máx</th>
                                <th>Requerido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Documento</td>
                                <td><small>50</small></td>
                                <td><span class="badge badge-danger">Sí</span></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Nombre</td>
                                <td><small>100</small></td>
                                <td><span class="badge badge-danger">Sí</span></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Apellido</td>
                                <td><small>100</small></td>
                                <td><span class="badge badge-danger">Sí</span></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Tipo</td>
                                <td><small>-</small></td>
                                <td><span class="badge badge-secondary">No</span></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>Email</td>
                                <td><small>150</small></td>
                                <td><span class="badge badge-secondary">No</span></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>Teléfono</td>
                                <td><small class="text-danger">20</small></td>
                                <td><span class="badge badge-secondary">No</span></td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td>Grado/Cargo</td>
                                <td><small>50</small></td>
                                <td><span class="badge badge-secondary">No</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="alert alert-warning alert-sm">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            <strong>Máx:</strong> Número máximo de caracteres.<br>
                            Los datos que excedan se truncarán automáticamente.
                        </small>
                    </div>

                    <div class="mt-3">
                        <h6><strong>Vista previa de Excel:</strong></h6>
                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjE1MCIgeT0iNzUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+RWplbXBsbyBkZSBFeGNlbDwvdGV4dD48L3N2Zz4=" 
                             alt="Ejemplo Excel" class="img-fluid border">
                        <p class="text-sm text-muted mt-2">
                            Seleccione las celdas con datos (sin el encabezado) y cópielas directamente.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips Importantes</h3>
                </div>
                <div class="card-body">
                    <ul class="fa-ul text-sm">
                        <li><span class="fa-li"><i class="fas fa-check text-success"></i></span>
                            <strong>Copie solo los datos</strong>, sin encabezados
                        </li>
                        <li><span class="fa-li"><i class="fas fa-check text-success"></i></span>
                            <strong>NO escriba manualmente</strong> - debe copiar desde Excel
                        </li>
                        <li><span class="fa-li"><i class="fas fa-check text-success"></i></span>
                            Asegúrese de que su Excel tenga <strong>7 columnas</strong>
                        </li>
                        <li><span class="fa-li"><i class="fas fa-check text-success"></i></span>
                            El <strong>teléfono</strong> debe tener máximo <span class="badge badge-warning">20 caracteres</span>
                        </li>
                        <li><span class="fa-li"><i class="fas fa-check text-success"></i></span>
                            Los <strong>documentos duplicados</strong> se omitirán
                        </li>
                        <li><span class="fa-li"><i class="fas fa-check text-success"></i></span>
                            Verá un <strong>reporte detallado</strong> al finalizar
                        </li>
                    </ul>
                    
                    <div class="alert alert-danger alert-sm mt-3 mb-0">
                        <small>
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Error común:</strong> Si sus datos tienen espacios en lugar de TABs, 
                            vuelva a copiarlos desde Excel.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        :root {
            --color-institucional: #233e6c;
        }
        
        #data {
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .card-warning:not(.card-outline) > .card-header {
            background-color: #ffc107;
            color: #000;
        }
        
        .btn-warning {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }
        
        .btn-warning:hover {
            background-color: #e0a800 !important;
            border-color: #d39e00 !important;
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-enfocar el textarea
    $('#data').focus();
    
    // Contador de líneas
    $('#data').on('input', function() {
        const lineas = $(this).val().split('\n').filter(l => l.trim() !== '').length;
        if (lineas > 0) {
            console.log(`${lineas} líneas detectadas`);
        }
    });
});
</script>
@stop
