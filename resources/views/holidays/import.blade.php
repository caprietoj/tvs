@extends('adminlte::page')

@section('title', 'Importar Días Festivos')

@section('content_header')
    <h1>Importar Días Festivos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Información:</strong>
                <ul class="mb-0">
                    <li>Suba un archivo Excel o CSV con las columnas: <code>date</code>, <code>name</code> y opcionalmente <code>description</code>.</li>
                    <li>La columna <code>date</code> debe tener formato de fecha (YYYY-MM-DD, DD/MM/YYYY, etc.).</li>
                    <li>Si ya existe un día festivo para alguna fecha, se actualizará con los nuevos datos.</li>
                    <li>Los días de ciclo serán recalculados automáticamente para considerar estos días festivos.</li>
                </ul>
            </div>

            <form action="{{ route('holidays.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="file">Archivo Excel/CSV</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <label class="custom-file-label" for="file">Elegir archivo</label>
                        </div>
                    </div>
                    @error('file')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Tamaño máximo: 5MB</small>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Importar
                    </button>
                    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>

            <div class="mt-4">
                <h5>Ejemplo de formato</h5>
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>date</th>
                            <th>name</th>
                            <th>description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-01-01</td>
                            <td>Año Nuevo</td>
                            <td>Día festivo nacional</td>
                        </tr>
                        <tr>
                            <td>2025-05-01</td>
                            <td>Día del Trabajo</td>
                            <td>Día festivo nacional</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Mostrar nombre de archivo seleccionado
        $('input[type="file"]').change(function(e) {
            var fileName = e.target.files[0].name;
            $('.custom-file-label').html(fileName);
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert-dismissible').alert('close');
        }, 5000);
    });
</script>
@stop