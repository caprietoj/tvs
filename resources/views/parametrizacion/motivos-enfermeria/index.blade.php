@extends('adminlte::page')

@section('title', 'Motivos de Enfermería - Parametrización')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary">
            <i class="fas fa-cogs mr-2"></i>Motivos de Enfermería
        </h1>
        <div>
            <a href="{{ route('motivos-enfermeria.create') }}" class="btn btn-success mr-2">
                <i class="fas fa-plus mr-1"></i>Nuevo Motivo
            </a>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#importModal">
                <i class="fas fa-file-excel mr-1"></i>Importar desde Excel
            </button>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>Lista de Motivos
                    </h3>
                </div>
                <div class="card-body">
                    @if($motivos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="motivosTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Orden</th>
                                        <th>Código CIE-10</th>
                                        <th>Icono</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($motivos as $motivo)
                                        <tr>
                                            <td>
                                                <span class="badge badge-secondary">{{ $motivo->orden }}</span>
                                            </td>
                                            <td>
                                                @if($motivo->codigo_cie10)
                                                    <code class="text-primary">{{ $motivo->codigo_cie10 }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($motivo->icono)
                                                    <span style="font-size: 1.5em;">{{ $motivo->icono }}</span>
                                                @else
                                                    <i class="fas fa-minus text-muted"></i>
                                                @endif
                                            </td>
                                            <td class="font-weight-bold">{{ $motivo->nombre }}</td>
                                            <td>
                                                @if($motivo->categoria)
                                                    <span class="badge badge-info">{{ $motivo->categoria }}</span>
                                                @else
                                                    <span class="text-muted">Sin categoría</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($motivo->descripcion)
                                                    {{ Str::limit($motivo->descripcion, 50) }}
                                                @else
                                                    <span class="text-muted">Sin descripción</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($motivo->activo)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check mr-1"></i>Activo
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times mr-1"></i>Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $motivo->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('motivos-enfermeria.edit', $motivo) }}" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form action="{{ route('motivos-enfermeria.toggle-active', $motivo) }}" 
                                                          method="POST" 
                                                          style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $motivo->activo ? 'btn-secondary' : 'btn-success' }}"
                                                                title="{{ $motivo->activo ? 'Desactivar' : 'Activar' }}">
                                                            <i class="fas {{ $motivo->activo ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="confirmarEliminacion({{ $motivo->id }}, '{{ $motivo->nombre }}')"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay motivos registrados</h5>
                            <p class="text-muted">Comienza agregando tu primer motivo de enfermería.</p>
                            <a href="{{ route('motivos-enfermeria.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i>Crear Primer Motivo
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el motivo <strong id="motivoNombre"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de importación desde Excel -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-file-excel mr-2"></i>Importar Motivos desde Excel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="fas fa-info-circle mr-2"></i>Instrucciones:
                            </h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success mr-2"></i>Copia los datos de Excel</li>
                                <li><i class="fas fa-check text-success mr-2"></i>Pega en el área de texto</li>
                                <li><i class="fas fa-check text-success mr-2"></i>Formato: Código | Nombre | Descripción</li>
                                <li><i class="fas fa-check text-success mr-2"></i>Separar columnas con TAB</li>
                            </ul>
                            
                            <div class="alert alert-warning">
                                <strong>Ejemplo:</strong><br>
                                R070&nbsp;&nbsp;&nbsp;&nbsp;DOLOR DE GARGANTA&nbsp;&nbsp;&nbsp;&nbsp;Dolor en la garganta<br>
                                J80X&nbsp;&nbsp;&nbsp;&nbsp;DIFICULTAD RESPIRATORIA&nbsp;&nbsp;&nbsp;&nbsp;Problemas para respirar
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>Categorías automáticas:</strong><br>
                                <small>El sistema detectará automáticamente categorías como:<br>
                                • SUGESTIVOS DE RESFRIADO COMÚN<br>
                                • AFECCIONES EN ÓRGANOS DE LOS SENTIDOS<br>
                                • LESIONES OSTEOMUSCULARES<br>
                                • ABDOMINAL<br>
                                • NEUROLÓGICO<br>
                                • OTROS<br>
                                • PIEL
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="fas fa-paste mr-2"></i>Datos de Excel:
                            </h6>
                            <textarea 
                                id="excelData" 
                                class="form-control" 
                                rows="10" 
                                placeholder="Pega aquí los datos copiados de Excel...&#10;&#10;Formato esperado:&#10;R070	DOLOR DE GARGANTA&#10;J80X	DIFICULTAD RESPIRATORIA&#10;J00X	RESFRIADO COMUN&#10;&#10;O con categorías:&#10;SUGESTIVOS DE RESFRIADO COMUN&#10;R070	DOLOR DE GARGANTA&#10;J80X	DIFICULTAD RESPIRATORIA&#10;&#10;AFECCIONES EN ORGANOS DE LOS SENTIDOS&#10;T16X	CUERPO EXTRAÑO EN EL OIDO"
                            ></textarea>
                            <small class="text-muted">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Tip: Selecciona las celdas en Excel y usa Ctrl+C, luego Ctrl+V aquí
                            </small>
                        </div>
                    </div>
                    
                    <div class="row mt-3" id="previewSection" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-primary">
                                <i class="fas fa-eye mr-2"></i>Vista previa:
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="previewTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Código CIE-10</th>
                                            <th>Nombre</th>
                                            <th>Categoría</th>
                                            <th>Orden</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="previewBtn">
                        <i class="fas fa-eye mr-1"></i>Vista Previa
                    </button>
                    <button type="button" class="btn btn-success" id="importBtn" style="display: none;">
                        <i class="fas fa-upload mr-1"></i>Importar Datos
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table th {
            border-top: none;
            font-weight: 600;
            color: #364E76;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .btn-group .btn:last-child {
            margin-right: 0;
        }
        
        .alert {
            border-radius: 8px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#motivosTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                order: [[0, 'asc']], // Ordenar por columna orden
                columnDefs: [
                    { targets: [6], orderable: false } // No ordenar columna de acciones
                ]
            });
        });

        function confirmarEliminacion(id, nombre) {
            $('#motivoNombre').text(nombre);
            $('#deleteForm').attr('action', '/parametrizacion/motivos-enfermeria/' + id);
            $('#deleteModal').modal('show');
        }

        // Funcionalidad de importación desde Excel
        let parsedData = [];

        $('#previewBtn').click(function() {
            const data = $('#excelData').val().trim();
            
            if (!data) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos requeridos',
                    text: 'Por favor, pega los datos de Excel en el área de texto.'
                });
                return;
            }

            try {
                parsedData = parseExcelData(data);
                showPreview(parsedData);
                $('#previewSection').show();
                $('#importBtn').show();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el formato',
                    text: error.message
                });
            }
        });

        $('#importBtn').click(function() {
            if (parsedData.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin datos',
                    text: 'No hay datos válidos para importar.'
                });
                return;
            }

            Swal.fire({
                title: '¿Confirmar importación?',
                text: `Se importarán ${parsedData.length} motivos. ¿Continuar?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, importar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    importMotivos(parsedData);
                }
            });
        });

        function parseExcelData(data) {
            const lines = data.split('\n').filter(line => line.trim() !== '');
            const motivos = [];
            let currentCategory = null;
            let orden = 1;

            lines.forEach((line, index) => {
                const trimmedLine = line.trim();
                
                // Skip empty lines
                if (!trimmedLine) return;
                
                // Check if line is a category (no tab or code format)
                if (!trimmedLine.includes('\t') && trimmedLine.length > 0 && !trimmedLine.match(/^[A-Z]\d+[X]?\s/)) {
                    currentCategory = trimmedLine;
                    return;
                }
                
                // Parse motivo line
                const columns = trimmedLine.split('\t');
                
                if (columns.length < 2) {
                    // Try to parse without tab (space separated)
                    const match = trimmedLine.match(/^([A-Z]\d+[X]?)\s+(.+)/);
                    if (match) {
                        columns[0] = match[1];
                        columns[1] = match[2];
                    } else {
                        throw new Error(`Línea ${index + 1}: Formato inválido. Se requiere código y nombre.`);
                    }
                }

                const motivo = {
                    codigo_cie10: columns[0] ? columns[0].trim() : null,
                    nombre: columns[1] ? columns[1].trim() : '',
                    categoria: currentCategory,
                    descripcion: columns[2] ? columns[2].trim() : '',
                    orden: orden++
                };

                if (!motivo.nombre) {
                    throw new Error(`Línea ${index + 1}: El nombre es obligatorio`);
                }

                motivos.push(motivo);
            });

            return motivos;
        }

        function showPreview(motivos) {
            const tbody = $('#previewTableBody');
            tbody.empty();

            motivos.forEach((motivo, index) => {
                const row = `
                    <tr>
                        <td>
                            ${motivo.codigo_cie10 ? `<code class="text-primary">${motivo.codigo_cie10}</code>` : '<span class="text-muted">-</span>'}
                        </td>
                        <td class="font-weight-bold">${motivo.nombre}</td>
                        <td>
                            ${motivo.categoria ? `<span class="badge badge-info">${motivo.categoria}</span>` : '<span class="text-muted">Sin categoría</span>'}
                        </td>
                        <td><span class="badge badge-secondary">${motivo.orden}</span></td>
                        <td><span class="badge badge-success"><i class="fas fa-check mr-1"></i>Activo</span></td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        function importMotivos(motivos) {
            // Mostrar loader
            Swal.fire({
                title: 'Importando...',
                text: 'Por favor espera mientras se procesan los datos.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar datos al servidor
            $.ajax({
                url: '{{ route("motivos-enfermeria.import") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    motivos: motivos
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Importación exitosa!',
                        text: `Se importaron ${response.imported} motivos correctamente.`,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        // Recargar la página para mostrar los nuevos datos
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Error desconocido';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en la importación',
                        text: errorMessage,
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }

        // Limpiar modal al cerrarlo
        $('#importModal').on('hidden.bs.modal', function() {
            $('#excelData').val('');
            $('#previewSection').hide();
            $('#importBtn').hide();
            parsedData = [];
        });
    </script>
@stop