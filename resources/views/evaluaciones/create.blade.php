@extends('adminlte::page')

@section('title', 'Evaluar Proveedor')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">Evaluación de Proveedor</h3>
        </div>
        <form action="{{ route('evaluaciones.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <!-- Información del Proveedor -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Proveedor</label>
                            <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                                <option value="">Seleccione un proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" data-nit="{{ $proveedor->nit }}">
                                        {{ $proveedor->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Número de Contrato</label>
                            <input type="text" name="numero_contrato" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>NIT</label>
                            <input type="text" id="nit" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Lugar de Evaluación</label>
                            <input type="text" name="lugar_evaluacion" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha de Evaluación</label>
                            <input type="date" name="fecha_evaluacion" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Criterios de Evaluación -->
                <h4 class="mt-4">Criterios de Evaluación</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th>Características (100 puntos máximo)</th>
                                <th>Criterios</th>
                                <th width="150">Calificación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 1. CUMPLIMIENTO Y ENTREGA -->
                            <tr>
                                <td rowspan="4" class="align-middle">
                                    <strong>1. CUMPLIMIENTO Y ENTREGA</strong>
                                    <p class="text-muted small mb-1">Valor: 14.29 puntos</p>
                                    <p class="text-muted small">Entre 0.0 y 5.0 puntos</p>
                                </td>
                                <td>EXCELENTE (4.5-5.0): El contrato se terminó antes de lo estipulado.</td>
                                <td rowspan="4">
                                    <div class="form-group mb-0">
                                        <select name="cumplimiento_entrega" class="form-control" required>
                                            <option value="">Seleccione una calificación</option>
                                            <option value="5.0">EXCELENTE (4.5-5.0)</option>
                                            <option value="4.0">BUENO (3.9-4.4)</option>
                                            <option value="3.5">REGULAR (3.0-3.8)</option>
                                            <option value="2.0">NO CUMPLE (0.0-2.9)</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr><td>BUENO (3.9-4.4): El contrato se terminó en la fecha estipulada.</td></tr>
                            <tr><td>REGULAR (3.0-3.8): El contrato se entregó posterior a la fecha estipulada, pero no superior al 20%.</td></tr>
                            <tr><td>NO CUMPLE (0.0-2.9): El contrato se entregó con retraso superior al 20%.</td></tr>

                            <!-- 2. CALIDAD Y ESPECIFICACIONES -->
                            <tr>
                                <td rowspan="4" class="align-middle">
                                    <strong>2. CALIDAD Y ESPECIFICACIONES TÉCNICAS</strong>
                                    <p class="text-muted small mb-1">Valor: 14.29 puntos</p>
                                    <p class="text-muted small">Entre 0.0 y 5.0 puntos</p>
                                </td>
                                <td>EXCELENTE (4.5-5.0): Supera las expectativas y mejora las especificaciones técnicas.</td>
                                <td rowspan="4">
                                    <div class="form-group mb-0">
                                        <select name="calidad_especificaciones" class="form-control" required>
                                            <option value="">Seleccione una calificación</option>
                                            <option value="5.0">EXCELENTE (4.5-5.0)</option>
                                            <option value="4.0">BUENO (3.9-4.4)</option>
                                            <option value="3.5">REGULAR (3.0-3.8)</option>
                                            <option value="2.0">NO CUMPLE (0.0-2.9)</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr><td>BUENO (3.9-4.4): Cumple con los requisitos y especificaciones técnicas establecidas.</td></tr>
                            <tr><td>REGULAR (3.0-3.8): Faltó a especificaciones que fueron subsanadas sin perjuicios.</td></tr>
                            <tr><td>NO CUMPLE (0.0-2.9): Presentó inconformidades graves en calidad y especificaciones.</td></tr>

                            <!-- 3. DOCUMENTACIÓN Y GARANTÍAS -->
                            <tr>
                                <td rowspan="4" class="align-middle">
                                    <strong>3. DOCUMENTACIÓN Y GARANTÍAS</strong>
                                    <p class="text-muted small mb-1">Valor: 14.29 puntos</p>
                                    <p class="text-muted small">Entre 0.0 y 5.0 puntos</p>
                                </td>
                                <td>EXCELENTE (4.5-5.0): Mantiene documentación actualizada y constituye garantías oportunamente.</td>
                                <td rowspan="4">
                                    <div class="form-group mb-0">
                                        <select name="documentacion_garantias" class="form-control" required>
                                            <option value="">Seleccione una calificación</option>
                                            <option value="5.0">EXCELENTE (4.5-5.0)</option>
                                            <option value="4.0">BUENO (3.9-4.4)</option>
                                            <option value="3.5">REGULAR (3.0-3.8)</option>
                                            <option value="2.0">NO CUMPLE (0.0-2.9)</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr><td>BUENO (3.9-4.4): Presenta documentación y constituye garantías en término pactado.</td></tr>
                            <tr><td>REGULAR (3.0-3.8): No actualiza documentos o constituye garantías posterior al término.</td></tr>
                            <tr><td>NO CUMPLE (0.0-2.9): No actualiza documentos ni constituye garantías requeridas.</td></tr>

                            <!-- 4. SERVICIO POSTVENTA -->
                            <tr>
                                <td rowspan="4" class="align-middle">
                                    <strong>4. SERVICIO POSTVENTA</strong>
                                    <p class="text-muted small mb-1">Valor: 14.29 puntos</p>
                                    <p class="text-muted small">Entre 0.0 y 5.0 puntos</p>
                                </td>
                                <td>EXCELENTE (4.5-5.0): Realiza control postventa sin requerimiento.</td>
                                <td rowspan="4">
                                    <div class="form-group mb-0">
                                        <select name="servicio_postventa" class="form-control" required>
                                            <option value="">Seleccione una calificación</option>
                                            <option value="5.0">EXCELENTE (4.5-5.0)</option>
                                            <option value="4.0">BUENO (3.9-4.4)</option>
                                            <option value="3.5">REGULAR (3.0-3.8)</option>
                                            <option value="2.0">NO CUMPLE (0.0-2.9)</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr><td>BUENO (3.9-4.4): Atiende peticiones y garantiza calidad del servicio.</td></tr>
                            <tr><td>REGULAR (3.0-3.8): Atiende en forma desobligada las peticiones.</td></tr>
                            <tr><td>NO CUMPLE (0.0-2.9): Desatiende o atiende tardíamente las peticiones.</td></tr>

                            <!-- 5. PRECIO -->
                            <tr>
                                <td rowspan="2" class="align-middle">
                                    <strong>5. PRECIO</strong>
                                    <p class="text-muted small mb-1">Valor: 14.29 puntos</p>
                                    <p class="text-muted small">Entre 0.0 y 5.0 puntos</p>
                                </td>
                                <td>EXCELENTE (3.0-5.0): El precio es competitivo.</td>
                                <td rowspan="2">
                                    <div class="form-group mb-0">
                                        <select name="precio" class="form-control" required>
                                            <option value="">Seleccione una calificación</option>
                                            <option value="5.0">EXCELENTE (3.0-5.0)</option>
                                            <option value="2.0">NO CUMPLE (0.0-2.9)</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr><td>NO CUMPLE (0.0-2.9): El precio no es competitivo.</td></tr>

                            <!-- 6. CAPACIDAD INSTALADA -->
                            <tr>
                                <td rowspan="4" class="align-middle">
                                    <strong>6. CAPACIDAD INSTALADA</strong>
                                    <p class="text-muted small mb-1">Valor: 14.29 puntos</p>
                                    <p class="text-muted small">Entre 0.0 y 5.0 puntos</p>
                                </td>
                                <td>EXCELENTE (4.5-5.0): Instalaciones y tecnología superan expectativas.</td>
                                <td rowspan="4">
                                    <div class="form-group mb-0">
                                        <select name="capacidad_instalada" class="form-control" required>
                                            <option value="">Seleccione una calificación</option>
                                            <option value="5.0">EXCELENTE (4.5-5.0)</option>
                                            <option value="4.0">BUENO (3.9-4.4)</option>
                                            <option value="3.5">REGULAR (3.0-3.8)</option>
                                            <option value="2.0">NO CUMPLE (0.0-2.9)</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr><td>BUENO (3.9-4.4): Instalaciones y tecnología son suficientes.</td></tr>
                            <tr><td>REGULAR (3.0-3.8): Instalaciones y tecnología no son suficientes.</td></tr>
                            <tr><td>NO CUMPLE (0.0-2.9): No tiene instalaciones ni tecnología adecuadas.</td></tr>

                            <!-- 7. SOPORTE TÉCNICO -->
                            <tr>
                                <td rowspan="4" class="align-middle">
                                    <strong>7. SOPORTE TÉCNICO</strong>
                                    <p class="text-muted small mb-1">Valor: 14.29 puntos</p>
                                    <p class="text-muted small">Entre 0.0 y 5.0 puntos</p>
                                </td>
                                <td>EXCELENTE (4.5-5.0): La asesoría es oportuna y acertada.</td>
                                <td rowspan="4">
                                    <div class="form-group mb-0">
                                        <select name="soporte_tecnico" class="form-control" required>
                                            <option value="">Seleccione una calificación</option>
                                            <option value="5.0">EXCELENTE (4.5-5.0)</option>
                                            <option value="4.0">BUENO (3.9-4.4)</option>
                                            <option value="3.5">REGULAR (3.0-3.8)</option>
                                            <option value="2.0">NO CUMPLE (0.0-2.9)</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr><td>BUENO (3.9-4.4): Realiza asesoría cuando se requiere.</td></tr>
                            <tr><td>REGULAR (3.0-3.8): La asesoría es ocasional.</td></tr>
                            <tr><td>NO CUMPLE (0.0-2.9): No realiza el servicio de asesorías pactado.</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Observaciones -->
                <div class="form-group mt-4">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"></textarea>
                </div>

                <!-- Evaluador -->
                <div class="form-group">
                    <label>Evaluado por</label>
                    <input type="text" name="evaluado_por" class="form-control" required>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Guardar Evaluación</button>
                <a href="{{ route('evaluaciones.index') }}" class="btn btn-secondary float-right">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .table thead th {
        background-color: #364E76;
        color: white;
    }
    
    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6;
    }
    
    .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }
    
    .btn-primary {
        background-color: #364E76;
        border-color: #364E76;
    }
    
    .card-header {
        background-color: #364E76 !important;
        color: white;
    }
    
    .bg-primary {
        background-color: #364E76 !important;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#proveedor_id').change(function() {
        let nit = $(this).find(':selected').data('nit');
        $('#nit').val(nit);
    });
});
</script>
@stop
