@extends('adminlte::page')

@section('title', 'Importar Productos')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Importar Productos al Inventario</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventario</a></li>
                    <li class="breadcrumb-item active">Importar</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Importar datos de productos</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Instrucciones</h5>
                            <p>Puede copiar y pegar la información directamente desde Excel. El sistema procesará los datos separados por tabulaciones automáticamente.</p>
                            
                            <p><strong>Formato esperado (separado por tabulaciones):</strong> Cada línea debe contener las columnas en el siguiente orden:</p>
                            <p class="text-success"><code>DESCRIPCION DEL MATERIAL    COLOR    CANTIDAD SUGERIDA    STOCK    SOBRE STOCK    CANTIDAD A COMPRAR    UNIDAD DE MEDIDA O PRESENTACION</code></p>
                            
                            <p><strong>Ejemplos de datos válidos:</strong></p>
                            <pre><code>PAPEL CREPE    ROSADO    0    24    24    0    PLIEGO * UNIDAD
PAPEL CREPE    VIOLETA    0    17    17    0    PLIEGO * UNIDAD
CARTULINA IRIS    AZUL    0    20    20    0    PLIEGO * UNIDAD</code></pre>
                            
                            <p class="mb-0"><strong>Notas importantes:</strong></p>
                            <ul>
                                <li>Las columnas de cantidad y stock deben ser números.</li>
                                <li>El color se guarda en la columna <strong>Color</strong> del inventario.</li>
                                <li>Si el color es <strong>N/A</strong> se dejará vacío.</li>
                                <li>La descripción y la unidad de medida se combinan para formar el nombre del producto.</li>
                                <li>La fila de encabezados se ignora automáticamente.</li>
                                <li>Si un producto con el mismo nombre y color ya existe, se actualizarán sus cantidades.</li>
                            </ul>
                        </div>

                        <form action="{{ route('inventory.process-import') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="data">Datos a importar</label>
                                <textarea class="form-control @error('data') is-invalid @enderror" id="data" name="data" rows="15" placeholder="Pegue aquí los datos como se muestra en el ejemplo arriba" required>{{ old('data') }}</textarea>
                                <small class="form-text text-muted">Puede pegar directamente desde Excel u otras fuentes.</small>
                                @error('data')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Procesar Importación</button>
                                <a href="{{ route('inventory.index') }}" class="btn btn-default">Cancelar</a>
                            </div>
                        </form>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
@stop