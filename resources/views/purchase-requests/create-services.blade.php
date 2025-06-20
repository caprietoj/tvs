@extends('adminlte::page')

@section('title', 'Solicitud de Servicios')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-tools mr-2"></i>Solicitud de Servicios</h1>
@stop

@section('content')
<div class="container">
    <div class="card card-outline" style="border-top-color: #364E76;">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Solicitud de Servicios</h3>
        </div>
        
        <form action="{{ route('purchase-requests.store') }}" method="POST" id="servicesForm">
            @csrf
            <input type="hidden" name="type" value="services">
            
            <div class="card-body">
                <!-- Cabecera del formato -->
                <div class="text-center mb-3">
                    <h4 class="font-weight-bold" style="color: #364E76;">GESTIÓN ADMINISTRATIVA Y FINANCIERA</h4>
                    <h4 class="font-weight-bold" style="color: #364E76;">COLEGIO VICTORIA SAS</h4>
                    <div>FORMATO SOLICITUD DE SERVICIOS</div>
                </div>

                <!-- Datos generales -->
                <div class="form-group">
                    <label for="requester">DOCENTE Y/O SOLICITANTE:</label>
                    <input type="text" class="form-control @error('requester') is-invalid @enderror" id="requester" name="requester" value="{{ old('requester', auth()->user()->name) }}" readonly>
                    @error('requester')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="section_area">SECCIÓN Y/O ÁREA:</label>
                        <select class="form-control @error('section_area') is-invalid @enderror" id="section_area" name="section_area">
                            <option value="">Seleccione...</option>
                            <option value="Pre Escolar" {{ old('section_area') == 'Pre Escolar' ? 'selected' : '' }}>Pre Escolar</option>
                            <option value="Primaria" {{ old('section_area') == 'Primaria' ? 'selected' : '' }}>Primaria</option>
                            <option value="Bachillerato" {{ old('section_area') == 'Bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                            <option value="PEP" {{ old('section_area') == 'PEP' ? 'selected' : '' }}>PEP</option>
                            <option value="PAI" {{ old('section_area') == 'PAI' ? 'selected' : '' }}>PAI</option>
                            <option value="Diploma" {{ old('section_area') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                            <option value="Administración" {{ old('section_area') == 'Administración' ? 'selected' : '' }}>Administración</option>
                            <option value="Dirección General" {{ old('section_area') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                            <option value="CAS" {{ old('section_area') == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="Departamento de Apoyo" {{ old('section_area') == 'Departamento de Apoyo' ? 'selected' : '' }}>Departamento de Apoyo</option>
                            <option value="Biblioteca" {{ old('section_area') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                        </select>
                        @error('section_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="request_date">FECHA DE SOLICITUD:</label>
                        <input type="date" class="form-control" id="request_date" name="request_date" value="{{ old('request_date', date('Y-m-d')) }}" readonly>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="reception_date">FECHA DE RECEPCIÓN:</label>
                        <input type="date" class="form-control" id="reception_date" name="reception_date" disabled>
                        <small class="form-text text-muted">Será completado por el departamento de compras</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="coordinator">COORDINADOR DE SECCIÓN Y/O JEFE DE ÁREA:</label>
                    <input type="text" class="form-control" id="coordinator" name="coordinator" value="{{ old('coordinator') }}">
                </div>                <!-- Servicios -->
                <div class="card my-4 card-outline" style="border-top-color: #364E76;">
                    <div class="card-header" style="background-color: #364E76; color: white;">
                        <h5 class="mb-0">SERVICIOS</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="service_justification">JUSTIFICACIÓN DEL SERVICIO <span class="text-danger">*</span>:</label>
                            <textarea class="form-control @error('service_justification') is-invalid @enderror" id="service_justification" name="service_justification" rows="3" placeholder="Describa la justificación para la contratación del servicio solicitado" required>{{ old('service_justification') }}</textarea>
                            @error('service_justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="service_budget">VALOR PRESUPUESTADO PARA ESTE SERVICIO $ <span class="text-danger">*</span>:</label>
                                <input type="number" step="0.01" class="form-control @error('service_budget') is-invalid @enderror" id="service_budget" name="service_budget" value="{{ old('service_budget') }}" placeholder="Ej: 500000" required>
                                @error('service_budget')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-dollar-sign"></i> 
                                    Ingrese el valor numérico del presupuesto estimado
                                </small>
                            </div>                            <div class="form-group col-md-8">
                                <label for="service_budget_text">EN LETRAS:</label>
                                <input type="text" class="form-control @error('service_budget_text') is-invalid @enderror" id="service_budget_text" name="service_budget_text" value="{{ old('service_budget_text') }}" placeholder="Ej: Quinientos mil pesos">
                                @error('service_budget_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Este campo se completa automáticamente al escribir el valor numérico, pero puede editarlo manualmente si es necesario.
                                </small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="serviceItemsTable">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 5%;">ITEM</th>
                                        <th style="width: 10%;">CANT.</th>
                                        <th style="width: 55%;">DESCRIPCIÓN DEL SERVICIO</th>
                                        <th style="width: 25%;">OBSERVACIONES</th>
                                        <th style="width: 5%;">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="serviceItemsBody">
                                    <tr id="serviceItem-1">
                                        <td>1</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" name="service_items[0][quantity]" min="1" value="1">
                                            <input type="hidden" name="service_items[0][item]" value="1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="service_items[0][description]" placeholder="Descripción detallada del servicio requerido" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="service_items[0][observations]" placeholder="Observaciones adicionales">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger delete-row" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-center">                                            <button type="button" class="btn btn-sm" id="addServiceItem" style="background-color: #364E76; color: white;">
                                                <i class="fas fa-plus"></i> Agregar Servicio
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Importante:</strong> Para servicios que requieran especificaciones técnicas especiales, por favor incluya toda la información necesaria en la descripción del servicio.
                        </div>
                    </div>
                </div>

                <!-- Observaciones generales -->
                <div class="form-group">
                    <label for="general_observations">OBSERVACIONES GENERALES:</label>
                    <textarea class="form-control" id="general_observations" name="general_observations" rows="3" placeholder="Información adicional relevante para la solicitud">{{ old('general_observations') }}</textarea>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Nota:</strong> Una vez enviada, esta solicitud será revisada por el área de compras y se procesará según los procedimientos establecidos.
                </div>
            </div>            <div class="card-footer text-center">
                <button type="submit" class="btn btn-lg" style="background-color: #364E76; color: white;">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar Solicitud de Servicios
                </button>
                <a href="{{ route('purchase-requests.create') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .card-outline {
        border-top: 3px solid;
    }
      .form-control:focus {
        border-color: #364E76;
        box-shadow: 0 0 0 0.2rem rgba(54, 78, 118, 0.25);
    }
    
    .btn[style*="#364E76"]:hover {
        background-color: #2a3d5d !important;
        border-color: #2a3d5d !important;
    }
    
    .table th {
        background-color: #f8f9fa !important;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .delete-row:disabled {
        opacity: 0.3;
    }
      .alert-warning {
        border-left: 4px solid #364E76;
    }
    
    .alert-info {
        border-left: 4px solid #17a2b8;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let serviceItemCount = 1;

    // Función para agregar nueva fila de servicios
    $('#addServiceItem').click(function() {
        serviceItemCount++;
        
        const newRow = `
            <tr id="serviceItem-${serviceItemCount}">
                <td>${serviceItemCount}</td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="service_items[${serviceItemCount - 1}][quantity]" min="1" value="1">
                    <input type="hidden" name="service_items[${serviceItemCount - 1}][item]" value="${serviceItemCount}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="service_items[${serviceItemCount - 1}][description]" placeholder="Descripción detallada del servicio requerido" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="service_items[${serviceItemCount - 1}][observations]" placeholder="Observaciones adicionales">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger delete-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#serviceItemsBody').append(newRow);
        updateDeleteButtons();
        updateItemNumbers();
    });

    // Función para eliminar fila
    $(document).on('click', '.delete-row', function() {
        if (!$(this).prop('disabled')) {
            $(this).closest('tr').remove();
            updateDeleteButtons();
            updateItemNumbers();
        }
    });

    // Función para actualizar los botones de eliminar
    function updateDeleteButtons() {
        const rows = $('#serviceItemsBody tr');
        rows.each(function(index) {
            const deleteBtn = $(this).find('.delete-row');
            if (rows.length === 1) {
                deleteBtn.prop('disabled', true);
            } else {
                deleteBtn.prop('disabled', false);
            }
        });
    }

    // Función para actualizar numeración de items
    function updateItemNumbers() {
        $('#serviceItemsBody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
            $(this).attr('id', 'serviceItem-' + (index + 1));
            
            // Actualizar nombres de los inputs
            $(this).find('input[name*="[quantity]"]').attr('name', `service_items[${index}][quantity]`);
            $(this).find('input[name*="[item]"]').attr('name', `service_items[${index}][item]`).val(index + 1);
            $(this).find('input[name*="[description]"]').attr('name', `service_items[${index}][description]`);
            $(this).find('input[name*="[observations]"]').attr('name', `service_items[${index}][observations]`);
        });
        
        serviceItemCount = $('#serviceItemsBody tr').length;
    }    // Función completa para convertir números a palabras en español
    function convertNumberToWords(num) {
        if (num === 0) return 'cero pesos';
        
        const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        const especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
        const decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        const centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        
        function convertirGrupo(n) {
            if (n === 0) return '';
            if (n === 100) return 'cien';
            
            let resultado = '';
            
            // Centenas
            if (n >= 100) {
                const c = Math.floor(n / 100);
                resultado += centenas[c];
                n %= 100;
                if (n > 0) resultado += ' ';
            }
            
            // Decenas y unidades
            if (n >= 20) {
                const d = Math.floor(n / 10);
                resultado += decenas[d];
                n %= 10;
                if (n > 0) {
                    resultado += ' y ' + unidades[n];
                }
            } else if (n >= 10) {
                resultado += especiales[n - 10];
            } else if (n > 0) {
                resultado += unidades[n];
            }
            
            return resultado;
        }
        
        let numero = Math.floor(num);
        let resultado = '';
        
        // Millones
        if (numero >= 1000000) {
            const millones = Math.floor(numero / 1000000);
            if (millones === 1) {
                resultado += 'un millón';
            } else {
                resultado += convertirGrupo(millones) + ' millones';
            }
            numero %= 1000000;
            if (numero > 0) resultado += ' ';
        }
        
        // Miles
        if (numero >= 1000) {
            const miles = Math.floor(numero / 1000);
            if (miles === 1) {
                resultado += 'mil';
            } else {
                resultado += convertirGrupo(miles) + ' mil';
            }
            numero %= 1000;
            if (numero > 0) resultado += ' ';
        }
        
        // Unidades, decenas y centenas
        if (numero > 0) {
            resultado += convertirGrupo(numero);
        }
        
        // Si no hay resultado aún, es cero
        if (resultado === '') {
            resultado = 'cero';
        }
        
        // Pluralización de pesos
        if (Math.floor(num) === 1) {
            resultado += ' peso';
        } else {
            resultado += ' pesos';
        }
        
        // Capitalizar la primera letra
        return resultado.charAt(0).toUpperCase() + resultado.slice(1);
    }    // Conversión de números a letras para el presupuesto
    function handleBudgetConversion() {
        const value = parseFloat($('#service_budget').val());
        if (!isNaN(value) && value > 0) {
            const valueInWords = convertNumberToWords(value);
            $('#service_budget_text').val(valueInWords);
        } else {
            $('#service_budget_text').val('');
        }
    }

    // Event listeners para la conversión
    $('#service_budget').on('input change blur', handleBudgetConversion);

    // Inicializar conversión si ya hay un valor
    const initialValue = parseFloat($('#service_budget').val());
    if (!isNaN(initialValue) && initialValue > 0) {
        const valueInWords = convertNumberToWords(initialValue);
        $('#service_budget_text').val(valueInWords);
    }// Validación del formulario
    $('#servicesForm').on('submit', function(e) {
        let hasServiceItems = false;
        let isValid = true;
        
        // Verificar si hay al menos un servicio con descripción
        $('#serviceItemsBody input[name*="[description]"]').each(function() {
            if ($(this).val().trim() !== '') {
                hasServiceItems = true;
                return false;
            }
        });
        
        if (!hasServiceItems) {
            e.preventDefault();
            alert('Debe agregar al menos un servicio con descripción.');
            isValid = false;
        }
        
        // Verificar justificación
        if ($('#service_justification').val().trim() === '') {
            e.preventDefault();
            alert('La justificación del servicio es obligatoria.');
            $('#service_justification').focus();
            isValid = false;
        }
        
        // Verificar presupuesto
        const budget = parseFloat($('#service_budget').val());
        if (isNaN(budget) || budget <= 0) {
            e.preventDefault();
            alert('Debe ingresar un valor presupuestado válido.');
            $('#service_budget').focus();
            isValid = false;
        }
        
        // Verificar sección/área
        if ($('#section_area').val() === '') {
            e.preventDefault();
            alert('Debe seleccionar una sección y/o área.');
            $('#section_area').focus();
            isValid = false;
        }
        
        return isValid;
    });

    // Inicializar estado de los botones
    updateDeleteButtons();
});
</script>
@stop
