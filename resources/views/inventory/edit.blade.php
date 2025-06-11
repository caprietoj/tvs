@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Producto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventario</a></li>
                    <li class="breadcrumb-item active">Editar Producto</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información del Producto</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form action="{{ route('inventory.update', $inventory->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="producto">Nombre del Producto</label>
                                <input type="text" class="form-control @error('producto') is-invalid @enderror" id="producto" name="producto" value="{{ old('producto', $inventory->producto) }}" placeholder="Ingrese el nombre del producto" required>
                                @error('producto')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="cantidad_sugerida">Cantidad Sugerida</label>
                                <input type="number" class="form-control @error('cantidad_sugerida') is-invalid @enderror" id="cantidad_sugerida" name="cantidad_sugerida" value="{{ old('cantidad_sugerida', $inventory->cantidad_sugerida) }}" min="0" placeholder="Ingrese la cantidad mínima sugerida" required>
                                <small class="form-text text-muted">Esta es la cantidad mínima que se debería tener en inventario</small>
                                @error('cantidad_sugerida')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="stock">Stock Actual</label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $inventory->stock) }}" min="0" placeholder="Ingrese la cantidad actual en stock" required>
                                @error('stock')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
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