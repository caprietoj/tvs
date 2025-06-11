<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Proveedor</label>
            <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                <option value="">Seleccione un proveedor</option>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" data-nit="{{ $proveedor->nit }}"
                        {{ isset($evaluacion) && $evaluacion->proveedor_id == $proveedor->id ? 'selected' : '' }}>
                        {{ $proveedor->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Número de Contrato</label>
            <input type="text" name="numero_contrato" class="form-control" required 
                value="{{ isset($evaluacion) ? $evaluacion->numero_contrato : old('numero_contrato') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>NIT</label>
            <input type="text" id="nit" class="form-control" readonly 
                value="{{ isset($evaluacion) ? $evaluacion->proveedor->nit : '' }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Lugar de Evaluación</label>
            <input type="text" name="lugar_evaluacion" class="form-control" required
                value="{{ isset($evaluacion) ? $evaluacion->lugar_evaluacion : old('lugar_evaluacion') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Fecha de Evaluación</label>
            <input type="date" name="fecha_evaluacion" class="form-control" required
                value="{{ isset($evaluacion) ? $evaluacion->fecha_evaluacion->format('Y-m-d') : old('fecha_evaluacion') }}">
        </div>
    </div>
</div>

<!-- Criterios de Evaluación -->
<h4 class="mt-4 text-primary">
    <i class="fas fa-clipboard-check"></i> Criterios de Evaluación
</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="bg-primary">
            <tr>
                <th>Características (100 puntos máximo)</th>
                <th>Criterios</th>
                <th width="200">Calificación</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. CUMPLIMIENTO Y ENTREGA -->
            <tr>
                <td rowspan="4" class="align-middle bg-light">
                    <strong>1. CUMPLIMIENTO Y ENTREGA</strong>
                    <p class="text-muted small mb-1">Entre 0.0 y 5.0 puntos</p>
                    <p class="text-muted small">Valor: 14.29 puntos</p>
                </td>
                <td>EXCELENTE (4.5-5.0): El contrato se terminó antes de lo estipulado.</td>
                <td rowspan="4" class="align-middle">
                    <div class="form-group mb-0">
                        <select name="cumplimiento_entrega" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="5.0" {{ (isset($evaluacion) && $evaluacion->cumplimiento_entrega == 5.0) ? 'selected' : '' }}>Excelente (4.5-5.0)</option>
                            <option value="4.0" {{ (isset($evaluacion) && $evaluacion->cumplimiento_entrega == 4.0) ? 'selected' : '' }}>Bueno (3.9-4.4)</option>
                            <option value="3.5" {{ (isset($evaluacion) && $evaluacion->cumplimiento_entrega == 3.5) ? 'selected' : '' }}>Regular (3.0-3.8)</option>
                            <option value="2.0" {{ (isset($evaluacion) && $evaluacion->cumplimiento_entrega == 2.0) ? 'selected' : '' }}>No Cumple (0.0-2.9)</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr><td>BUENO (3.9-4.4): El contrato se terminó en la fecha estipulada.</td></tr>
            <tr><td>REGULAR (3.0-3.8): El contrato se entregó posterior a la fecha estipulada, pero no superior al 20%.</td></tr>
            <tr><td>NO CUMPLE (0.0-2.9): El contrato se entregó con retraso superior al 20%.</td></tr>

            <!-- 2. CALIDAD Y ESPECIFICACIONES -->
            <tr>
                <td rowspan="4" class="align-middle bg-light">
                    <strong>2. CALIDAD Y ESPECIFICACIONES TÉCNICAS</strong>
                    <p class="text-muted small mb-1">Entre 0.0 y 5.0 puntos</p>
                    <p class="text-muted small">Valor: 14.29 puntos</p>
                </td>
                <td>EXCELENTE (4.5-5.0): Supera las expectativas y mejora las especificaciones técnicas.</td>
                <td rowspan="4" class="align-middle">
                    <div class="form-group mb-0">
                        <select name="calidad_especificaciones" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="5.0" {{ (isset($evaluacion) && $evaluacion->calidad_especificaciones == 5.0) ? 'selected' : '' }}>Excelente (4.5-5.0)</option>
                            <option value="4.0" {{ (isset($evaluacion) && $evaluacion->calidad_especificaciones == 4.0) ? 'selected' : '' }}>Bueno (3.9-4.4)</option>
                            <option value="3.5" {{ (isset($evaluacion) && $evaluacion->calidad_especificaciones == 3.5) ? 'selected' : '' }}>Regular (3.0-3.8)</option>
                            <option value="2.0" {{ (isset($evaluacion) && $evaluacion->calidad_especificaciones == 2.0) ? 'selected' : '' }}>No Cumple (0.0-2.9)</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr><td>BUENO (3.9-4.4): Cumple con los requisitos y especificaciones técnicas establecidas.</td></tr>
            <tr><td>REGULAR (3.0-3.8): Faltó a especificaciones que fueron subsanadas sin perjuicios.</td></tr>
            <tr><td>NO CUMPLE (0.0-2.9): Presentó inconformidades graves en calidad y especificaciones.</td></tr>

            <!-- 3. DOCUMENTACIÓN Y GARANTÍAS -->
            <tr>
                <td rowspan="4" class="align-middle bg-light">
                    <strong>3. DOCUMENTACIÓN Y GARANTÍAS</strong>
                    <p class="text-muted small mb-1">Entre 0.0 y 5.0 puntos</p>
                    <p class="text-muted small">Valor: 14.29 puntos</p>
                </td>
                <td>EXCELENTE (4.5-5.0): Mantiene documentación actualizada y constituye garantías oportunamente.</td>
                <td rowspan="4" class="align-middle">
                    <div class="form-group mb-0">
                        <select name="documentacion_garantias" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="5.0" {{ (isset($evaluacion) && $evaluacion->documentacion_garantias == 5.0) ? 'selected' : '' }}>Excelente (4.5-5.0)</option>
                            <option value="4.0" {{ (isset($evaluacion) && $evaluacion->documentacion_garantias == 4.0) ? 'selected' : '' }}>Bueno (3.9-4.4)</option>
                            <option value="3.5" {{ (isset($evaluacion) && $evaluacion->documentacion_garantias == 3.5) ? 'selected' : '' }}>Regular (3.0-3.8)</option>
                            <option value="2.0" {{ (isset($evaluacion) && $evaluacion->documentacion_garantias == 2.0) ? 'selected' : '' }}>No Cumple (0.0-2.9)</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr><td>BUENO (3.9-4.4): Presenta documentación y constituye garantías en término pactado.</td></tr>
            <tr><td>REGULAR (3.0-3.8): No actualiza documentos o constituye garantías posterior al término.</td></tr>
            <tr><td>NO CUMPLE (0.0-2.9): No actualiza documentos ni constituye garantías requeridas.</td></tr>

            <!-- 4. SERVICIO POSTVENTA -->
            <tr>
                <td rowspan="4" class="align-middle bg-light">
                    <strong>4. SERVICIO POSTVENTA</strong>
                    <p class="text-muted small mb-1">Entre 0.0 y 5.0 puntos</p>
                    <p class="text-muted small">Valor: 14.29 puntos</p>
                </td>
                <td>EXCELENTE (4.5-5.0): Realiza control postventa sin requerimiento.</td>
                <td rowspan="4" class="align-middle">
                    <div class="form-group mb-0">
                        <select name="servicio_postventa" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="5.0" {{ (isset($evaluacion) && $evaluacion->servicio_postventa == 5.0) ? 'selected' : '' }}>Excelente (4.5-5.0)</option>
                            <option value="4.0" {{ (isset($evaluacion) && $evaluacion->servicio_postventa == 4.0) ? 'selected' : '' }}>Bueno (3.9-4.4)</option>
                            <option value="3.5" {{ (isset($evaluacion) && $evaluacion->servicio_postventa == 3.5) ? 'selected' : '' }}>Regular (3.0-3.8)</option>
                            <option value="2.0" {{ (isset($evaluacion) && $evaluacion->servicio_postventa == 2.0) ? 'selected' : '' }}>No Cumple (0.0-2.9)</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr><td>BUENO (3.9-4.4): Atiende peticiones y garantiza calidad del servicio.</td></tr>
            <tr><td>REGULAR (3.0-3.8): Atiende en forma desobligada las peticiones.</td></tr>
            <tr><td>NO CUMPLE (0.0-2.9): Desatiende o atiende tardíamente las peticiones.</td></tr>

            <!-- 5. PRECIO -->
            <tr>
                <td rowspan="2" class="align-middle bg-light">
                    <strong>5. PRECIO</strong>
                    <p class="text-muted small mb-1">Entre 0.0 y 5.0 puntos</p>
                    <p class="text-muted small">Valor: 14.29 puntos</p>
                </td>
                <td>EXCELENTE (3.0-5.0): El precio es competitivo.</td>
                <td rowspan="2" class="align-middle">
                    <div class="form-group mb-0">
                        <select name="precio" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="5.0" {{ (isset($evaluacion) && $evaluacion->precio == 5.0) ? 'selected' : '' }}>Excelente (3.0-5.0)</option>
                            <option value="2.0" {{ (isset($evaluacion) && $evaluacion->precio == 2.0) ? 'selected' : '' }}>No Cumple (0.0-2.9)</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr><td>NO CUMPLE (0.0-2.9): El precio no es competitivo.</td></tr>

            <!-- 6. CAPACIDAD INSTALADA -->
            <tr>
                <td rowspan="4" class="align-middle bg-light">
                    <strong>6. CAPACIDAD INSTALADA</strong>
                    <p class="text-muted small mb-1">Entre 0.0 y 5.0 puntos</p>
                    <p class="text-muted small">Valor: 14.29 puntos</p>
                </td>
                <td>EXCELENTE (4.5-5.0): Instalaciones y tecnología superan expectativas.</td>
                <td rowspan="4" class="align-middle">
                    <div class="form-group mb-0">
                        <select name="capacidad_instalada" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="5.0" {{ (isset($evaluacion) && $evaluacion->capacidad_instalada == 5.0) ? 'selected' : '' }}>Excelente (4.5-5.0)</option>
                            <option value="4.0" {{ (isset($evaluacion) && $evaluacion->capacidad_instalada == 4.0) ? 'selected' : '' }}>Bueno (3.9-4.4)</option>
                            <option value="3.5" {{ (isset($evaluacion) && $evaluacion->capacidad_instalada == 3.5) ? 'selected' : '' }}>Regular (3.0-3.8)</option>
                            <option value="2.0" {{ (isset($evaluacion) && $evaluacion->capacidad_instalada == 2.0) ? 'selected' : '' }}>No Cumple (0.0-2.9)</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr><td>BUENO (3.9-4.4): Instalaciones y tecnología son suficientes.</td></tr>
            <tr><td>REGULAR (3.0-3.8): Instalaciones y tecnología no son suficientes.</td></tr>
            <tr><td>NO CUMPLE (0.0-2.9): No tiene instalaciones ni tecnología adecuadas.</td></tr>

            <!-- 7. SOPORTE TÉCNICO -->
            <tr>
                <td rowspan="4" class="align-middle bg-light">
                    <strong>7. SOPORTE TÉCNICO</strong>
                    <p class="text-muted small mb-1">Entre 0.0 y 5.0 puntos</p>
                    <p class="text-muted small">Valor: 14.29 puntos</p>
                </td>
                <td>EXCELENTE (4.5-5.0): La asesoría es oportuna y acertada.</td>
                <td rowspan="4" class="align-middle">
                    <div class="form-group mb-0">
                        <select name="soporte_tecnico" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="5.0" {{ (isset($evaluacion) && $evaluacion->soporte_tecnico == 5.0) ? 'selected' : '' }}>Excelente (4.5-5.0)</option>
                            <option value="4.0" {{ (isset($evaluacion) && $evaluacion->soporte_tecnico == 4.0) ? 'selected' : '' }}>Bueno (3.9-4.4)</option>
                            <option value="3.5" {{ (isset($evaluacion) && $evaluacion->soporte_tecnico == 3.5) ? 'selected' : '' }}>Regular (3.0-3.8)</option>
                            <option value="2.0" {{ (isset($evaluacion) && $evaluacion->soporte_tecnico == 2.0) ? 'selected' : '' }}>No Cumple (0.0-2.9)</option>
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

<div class="form-group mt-4">
    <label>Observaciones</label>
    <textarea name="observaciones" class="form-control" rows="3">{{ isset($evaluacion) ? $evaluacion->observaciones : old('observaciones') }}</textarea>
</div>

@inject('auth', 'Illuminate\Support\Facades\Auth')

<div class="form-group">
    <label>Evaluado por</label>
    <input type="text" name="evaluado_por" class="form-control" readonly
        value="{{ isset($evaluacion) ? $evaluacion->evaluado_por : $auth::user()->name }}">
</div>

<style>
    .table thead th {
        background-color: #364E76;
        color: white;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }
    
    .text-primary {
        color: #364E76 !important;
    }
    
    select.form-control {
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
    }
</style>
