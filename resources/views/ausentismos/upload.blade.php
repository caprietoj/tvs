@extends('adminlte::page')

@section('title', 'Subir Ausentismos')

@section('content_header')
    <h1>Subir Informe de Ausentismos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5><i class="icon fas fa-ban"></i> Errores de validación:</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="uploadTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="file-tab" data-toggle="tab" href="#file" role="tab">
                        <i class="fas fa-file-excel mr-1"></i>Cargar Archivo Excel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="paste-tab" data-toggle="tab" href="#paste" role="tab">
                        <i class="fas fa-paste mr-1"></i>Copiar y Pegar Datos
                    </a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content mt-3">
                <!-- Cargar archivo -->
                <div class="tab-pane fade show active" id="file" role="tabpanel">
                    <form action="{{ route('ausentismos.store') }}" method="POST" enctype="multipart/form-data" id="fileForm">
                        @csrf
                        <div class="form-group">
                            <label>Mes <span class="text-danger">*</span></label>
                            <select name="mes" class="form-control" required>
                                <option value="">Seleccione mes...</option>
                                <option value="Enero">Enero</option>
                                <option value="Febrero">Febrero</option>
                                <option value="Marzo">Marzo</option>
                                <option value="Abril">Abril</option>
                                <option value="Mayo">Mayo</option>
                                <option value="Junio">Junio</option>
                                <option value="Julio">Julio</option>
                                <option value="Agosto">Agosto</option>
                                <option value="Septiembre">Septiembre</option>
                                <option value="Octubre">Octubre</option>
                                <option value="Noviembre">Noviembre</option>
                                <option value="Diciembre">Diciembre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Archivo Excel <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="excelFile" name="archivo" 
                                       accept=".xlsx,.xls,.csv" required>
                                <label class="custom-file-label" for="excelFile">Seleccionar archivo...</label>
                            </div>
                            <small class="form-text text-muted">
                                Formatos aceptados: .xlsx, .xls, .csv (máximo 10MB)
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitFileBtn">
                            <i class="fas fa-upload mr-1"></i>Cargar Archivo
                        </button>
                    </form>
                </div>

                <!-- Copiar y pegar -->
                <div class="tab-pane fade" id="paste" role="tabpanel">
                    <form action="{{ route('ausentismos.store') }}" method="POST" id="pasteForm">
                        @csrf
                        <div class="form-group">
                            <label>Mes <span class="text-danger">*</span></label>
                            <select name="mes" class="form-control" required>
                                <option value="">Seleccione mes...</option>
                                <option value="Enero">Enero</option>
                                <option value="Febrero">Febrero</option>
                                <option value="Marzo">Marzo</option>
                                <option value="Abril">Abril</option>
                                <option value="Mayo">Mayo</option>
                                <option value="Junio">Junio</option>
                                <option value="Julio">Julio</option>
                                <option value="Agosto">Agosto</option>
                                <option value="Septiembre">Septiembre</option>
                                <option value="Octubre">Octubre</option>
                                <option value="Noviembre">Noviembre</option>
                                <option value="Diciembre">Diciembre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Datos del Excel (Copiar y Pegar) <span class="text-danger">*</span></label>
                            <textarea name="datos" class="form-control" rows="10" required 
                                      placeholder="Copie los datos directamente desde Excel y péguelos aquí..."></textarea>
                            <small class="form-text text-muted">
                                Copie las filas completas desde Excel (incluyendo todas las columnas) y péguelas aquí.
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check mr-1"></i>Cargar Datos
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle mr-2"></i>Instrucciones:</h5>
                    <ul class="mb-0">
                        <li><strong>Opción 1 - Cargar archivo:</strong> Seleccione el mes y cargue el archivo Excel del biométrico.</li>
                        <li><strong>Opción 2 - Copiar y pegar:</strong> Abra el Excel, seleccione todas las filas con datos, cópielas (Ctrl+C) y péguelas en el área de texto.</li>
                        <li>El archivo debe contener las columnas: Persona, Fecha de creación, Dependencia, Fecha desde, Fecha hasta, Asistencia, Duración, Motivo.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Actualizar nombre del archivo seleccionado
    $('#excelFile').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Prevenir múltiples envíos
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Procesando...');
    });
</script>
@stop
