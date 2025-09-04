@extends('adminlte::page')

@section('title', 'Diagnóstico de Formularios - Producción')

@section('content_header')
    <h1>
        <i class="fas fa-stethoscope"></i> Diagnóstico de Formularios Grandes
        <small>Verificación de configuración para formularios con muchos ítems</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Información del Sistema
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-server"></i> Configuración PHP</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>max_input_vars:</strong></td>
                                <td>
                                    <span class="badge badge-{{ ini_get('max_input_vars') >= 1500 ? 'success' : 'warning' }}">
                                        {{ ini_get('max_input_vars') }}
                                    </span>
                                    @if(ini_get('max_input_vars') < 1500)
                                        <small class="text-muted">(Recomendado: 2000+)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>post_max_size:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ ini_get('post_max_size') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>memory_limit:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ ini_get('memory_limit') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>max_execution_time:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ ini_get('max_execution_time') }}s</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-globe"></i> Información del Servidor</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Servidor:</strong></td>
                                <td>{{ $_SERVER['SERVER_SOFTWARE'] ?? 'No detectado' }}</td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td>{{ phpversion() }}</td>
                            </tr>
                            <tr>
                                <td><strong>SAPI:</strong></td>
                                <td>{{ php_sapi_name() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Entorno:</strong></td>
                                <td>
                                    <span class="badge badge-{{ app()->environment() === 'production' ? 'danger' : 'warning' }}">
                                        {{ app()->environment() }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-vial"></i> Prueba de Formulario Grande
                </h3>
            </div>
            <div class="card-body">
                <p>Esta prueba simula un formulario con 24 ítems para verificar si la configuración puede manejarlo:</p>
                
                <button type="button" class="btn btn-primary" id="testLargeForm">
                    <i class="fas fa-play"></i> Ejecutar Prueba
                </button>
                
                <div id="testResults" class="mt-3" style="display: none;">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-clock"></i> Ejecutando prueba...</h5>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Recomendaciones para Producción
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5>Configuraciones recomendadas para formularios con 24+ ítems:</h5>
                    <ul class="mb-0">
                        <li><strong>max_input_vars:</strong> 2000 o superior</li>
                        <li><strong>post_max_size:</strong> 50M o superior</li>
                        <li><strong>max_execution_time:</strong> 300 segundos</li>
                        <li><strong>memory_limit:</strong> 512M o superior</li>
                        <li><strong>max_input_time:</strong> 300 segundos</li>
                    </ul>
                </div>
                
                @if(ini_get('max_input_vars') < 1500)
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-circle"></i> Problema Detectado</h5>
                        <p>El valor actual de <code>max_input_vars</code> ({{ ini_get('max_input_vars') }}) 
                           es insuficiente para formularios con 24 ítems.</p>
                        <p><strong>Cada ítem requiere 5 campos, por lo que 24 ítems necesitan ~120 campos 
                           + campos base del formulario = ~140 campos total.</strong></p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.getElementById('testLargeForm').addEventListener('click', function() {
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.style.display = 'block';
    
    // Simular datos de formulario grande
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('type', 'purchase');
    formData.append('requester', 'Usuario Test');
    formData.append('section_area', 'Administración');
    formData.append('purchase_justification', 'Justificación de prueba');
    formData.append('is_shared', 'no');
    
    // Agregar 24 ítems
    for (let i = 0; i < 24; i++) {
        formData.append(`purchase_items[${i}][item]`, i + 1);
        formData.append(`purchase_items[${i}][quantity]`, Math.floor(Math.random() * 5) + 1);
        formData.append(`purchase_items[${i}][description]`, `Artículo de prueba número ${i + 1}`);
        formData.append(`purchase_items[${i}][unit]`, 'Unidad');
        formData.append(`purchase_items[${i}][observations]`, `Observación ${i + 1}`);
    }
    
    // Calcular tamaño aproximado
    const params = new URLSearchParams(formData);
    const sizeInBytes = new Blob([params.toString()]).size;
    
    setTimeout(() => {
        resultsDiv.innerHTML = `
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> Resultado de la Prueba</h5>
                <p><strong>Total de campos generados:</strong> ${Array.from(formData.keys()).length}</p>
                <p><strong>Tamaño estimado:</strong> ${sizeInBytes} bytes (${(sizeInBytes/1024).toFixed(2)} KB)</p>
                <p><strong>Límite max_input_vars:</strong> {{ ini_get('max_input_vars') }}</p>
                
                ${Array.from(formData.keys()).length > {{ ini_get('max_input_vars') }} ? 
                    '<div class="alert alert-danger mt-2">❌ El formulario excedería el límite de max_input_vars</div>' :
                    '<div class="alert alert-success mt-2">✅ El formulario está dentro del límite de max_input_vars</div>'
                }
            </div>
        `;
    }, 2000);
});
</script>
@stop
