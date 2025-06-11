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
                            <p>Puede copiar y pegar la información directamente desde Excel u otra fuente sin preocuparse por el formato. El sistema intentará procesarla automáticamente.</p>
                            
                            <p><strong>Formato esperado:</strong> Cada línea debe contener un producto con la siguiente estructura:</p>
                            <p class="text-success"><code>Nombre del Producto     Cantidad Sugerida    Stock Actual</code></p>
                            
                            <p><strong>Ejemplos de datos válidos:</strong></p>
                            <pre><code>Lápices de colores x12    100    85
Papel Bond Carta        200    150
Carpetas plásticas     50     30</code></pre>
                            
                            <p class="mb-0"><strong>Notas importantes:</strong></p>
                            <ul>
                                <li>El nombre del producto puede contener espacios.</li>
                                <li>Las dos últimas columnas deben ser números (cantidad sugerida y stock).</li>
                                <li>El sistema procesará automáticamente cualquier separador (espacios múltiples o tabulaciones).</li>
                                <li>Si un producto ya existe, se actualizarán sus cantidades.</li>
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