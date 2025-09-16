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
        
        <form action="{{ route('purchase-requests.store') }}" method="POST" id="servicesForm" enctype="multipart/form-data">
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
                            <option value="Preescolar y Primaria" {{ old('section_area') == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                            <option value="Escuela Media" {{ old('section_area') == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                            <option value="Escuela Alta / DP" {{ old('section_area') == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                            <option value="PAI" {{ old('section_area') == 'PAI' ? 'selected' : '' }}>PAI</option>
                            <option value="PEP" {{ old('section_area') == 'PEP' ? 'selected' : '' }}>PEP</option>
                            <option value="Deportes" {{ old('section_area') == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                            <option value="Psicología Institucional" {{ old('section_area') == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                            <option value="Biblioteca" {{ old('section_area') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                            <option value="Dirección General" {{ old('section_area') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                            <option value="CAS" {{ old('section_area') == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="Administración" {{ old('section_area') == 'Administración' ? 'selected' : '' }}>Administración</option>
                            <option value="Tecnología Institucional" {{ old('section_area') == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
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
                        <!-- Tipo de Servicio -->
                        <div class="form-group">
                            <label for="service_type">TIPO DE SERVICIO <span class="text-danger">*</span>:</label>
                            <select class="form-control @error('service_type') is-invalid @enderror" id="service_type" name="service_type" required>
                                <option value="">Seleccione el tipo de servicio...</option>
                                <option value="regular" {{ old('service_type') == 'regular' ? 'selected' : '' }}>Servicio Regular (Requiere cotización)</option>
                                <option value="no_quotation" {{ old('service_type') == 'no_quotation' ? 'selected' : '' }}>Servicio sin Cotización (Renovación de licencia, proveedor único, etc.)</option>
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Servicio Regular:</strong> Requiere cotizaciones antes de la aprobación final.<br>
                                <strong>Servicio sin Cotización:</strong> Para renovaciones de licencias, proveedores únicos o servicios que no requieren comparación de precios.
                            </small>
                        </div>

                        <!-- Información del Proveedor (solo para servicios sin cotización) -->
                        <div id="provider_info_section" style="display: none;">
                            <div class="card mt-3 border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-building mr-2"></i>Información del Proveedor</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="provider_id">NOMBRE DEL PROVEEDOR <span class="text-danger">*</span>:</label>
                                            <select class="form-control" id="provider_id" name="provider_id">
                                                <option value="">Seleccione un proveedor...</option>
                                                @foreach($providers as $provider)
                                                    <option value="{{ $provider->id }}" 
                                                            data-nit="{{ $provider->nit }}"
                                                            data-contact="{{ $provider->telefono }}"
                                                            data-email="{{ $provider->email }}"
                                                            {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                                        {{ $provider->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="provider_name" id="provider_name_hidden" value="{{ old('provider_name') }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="provider_nit">NIT DEL PROVEEDOR:</label>
                                            <input type="text" class="form-control" id="provider_nit" name="provider_nit" value="{{ old('provider_nit') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="provider_contact">CONTACTO:</label>
                                            <input type="text" class="form-control" id="provider_contact" name="provider_contact" value="{{ old('provider_contact') }}" readonly>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="provider_email">EMAIL:</label>
                                            <input type="email" class="form-control" id="provider_email" name="provider_email" value="{{ old('provider_email') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="no_quotation_reason">JUSTIFICACIÓN PARA NO COTIZAR <span class="text-danger">*</span>:</label>
                                        <textarea class="form-control" id="no_quotation_reason" name="no_quotation_reason" rows="2" placeholder="Ej: Renovación de licencia anual, Proveedor único autorizado, Continuidad de servicio, etc.">{{ old('no_quotation_reason') }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="quotation_file">COTIZACIÓN O ORDEN DE RENOVACIÓN <span class="text-danger">*</span>:</label>
                                        <input type="file" class="form-control-file @error('quotation_file') is-invalid @enderror" id="quotation_file" name="quotation_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                        @error('quotation_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Adjunte la cotización, orden de renovación o documento que respalde el costo del servicio. 
                                            Formatos permitidos: PDF, Word, Imágenes (JPG, PNG). Tamaño máximo: 10MB.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="service_justification">JUSTIFICACIÓN DEL SERVICIO <span class="text-danger">*</span>:</label>
                            <textarea class="form-control @error('service_justification') is-invalid @enderror" id="service_justification" name="service_justification" rows="3" placeholder="Describa la justificación para la contratación del servicio solicitado" required>{{ old('service_justification') }}</textarea>
                            @error('service_justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror                        </div>
                        
                        <!-- Sección de Presupuesto -->
                        <div class="card mt-4">
                            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #364E76;">
                                <h5 class="mb-0" style="color: #364E76;">
                                    <i class="fas fa-dollar-sign mr-2"></i>Información Presupuestaria
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="service_budget" class="font-weight-bold">VALOR PRESUPUESTADO PARA ESTE SERVICIO $ <span class="text-danger">*</span>:</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" style="background-color: #364E76; color: white;">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </span>
                                            </div>
                                            <input type="number" step="0.01" class="form-control @error('service_budget') is-invalid @enderror" id="service_budget" name="service_budget" value="{{ old('service_budget') }}" placeholder="Ej: 500000" required>
                                            @error('service_budget')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Ingrese el valor numérico del presupuesto estimado
                                        </small>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="service_budget_text" class="font-weight-bold">EN LETRAS:</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" style="background-color: #364E76; color: white;">
                                                    <i class="fas fa-spell-check"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control @error('service_budget_text') is-invalid @enderror" id="service_budget_text" name="service_budget_text" value="{{ old('service_budget_text') }}" placeholder="Ej: Quinientos mil pesos">
                                            @error('service_budget_text')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-magic mr-1"></i>
                                            Se completa automáticamente, pero puede editarlo manualmente
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Sección de Impuestos (solo para servicios sin cotización) -->
                                <div id="tax_section" style="display: none;">
                                    <hr>
                                    <h6 class="mb-3" style="color: #364E76;">
                                        <i class="fas fa-calculator mr-2"></i>Impuestos Aplicables
                                    </h6>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="font-weight-bold">SELECCIONE LOS IMPUESTOS A APLICAR:</label>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input tax-checkbox" id="iva_19" name="taxes[]" value="iva_19" data-rate="19">
                                                <label class="custom-control-label" for="iva_19">
                                                    <i class="fas fa-percent mr-1 text-info"></i>IVA (19%)
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input tax-checkbox" id="iva_5" name="taxes[]" value="iva_5" data-rate="5">
                                                <label class="custom-control-label" for="iva_5">
                                                    <i class="fas fa-percent mr-1 text-info"></i>IVA (5%)
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input tax-checkbox" id="consumo_8" name="taxes[]" value="consumo_8" data-rate="8">
                                                <label class="custom-control-label" for="consumo_8">
                                                    <i class="fas fa-shopping-cart mr-1 text-warning"></i>Impuesto al Consumo (8%)
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input tax-checkbox" id="consumo_4" name="taxes[]" value="consumo_4" data-rate="4">
                                                <label class="custom-control-label" for="consumo_4">
                                                    <i class="fas fa-shopping-cart mr-1 text-warning"></i>Impuesto al Consumo (4%)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="font-weight-bold">RESUMEN DE CÁLCULO:</label>
                                            <div class="card border-info">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between">
                                                        <span>Subtotal:</span>
                                                        <span id="tax_subtotal">$0</span>
                                                    </div>
                                                    <div id="tax_breakdown"></div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between font-weight-bold" style="color: #364E76;">
                                                        <span>Total con Impuestos:</span>
                                                        <span id="tax_total">$0</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="subtotal_amount" id="subtotal_amount" value="0">
                                            <input type="hidden" name="tax_amount" id="tax_amount" value="0">
                                            <input type="hidden" name="total_amount" id="total_amount" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mt-4">
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

                <!-- Configuración de Compra Compartida -->
                <div class="card mt-3" style="border-left: 4px solid #364E76;">
                    <div class="card-header" style="background-color: #f8f9fa;">
                        <h6 class="mb-0"><i class="fas fa-share-alt mr-2"></i>Configuración de Compra Compartida</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">¿Este servicio será compartido con otra sección?</label>
                            <div class="mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_shared" id="is_shared_no" value="no" checked>
                                    <label class="form-check-label" for="is_shared_no">
                                        <i class="fas fa-times text-danger mr-1"></i> No, esta compra es solo para mi sección
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_shared" id="is_shared_yes" value="yes">
                                    <label class="form-check-label" for="is_shared_yes">
                                        <i class="fas fa-check text-success mr-1"></i> Sí, esta compra será compartida
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de compra compartida -->
                        <div id="sharedConfig" style="display: none;">
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-user mr-1"></i> Mi Sección</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>Sección:</strong></p>
                                            <p class="section-display" id="currentSection">-</p>
                                            <p class="mb-1"><strong>Porcentaje a pagar:</strong></p>
                                            <div class="input-group">
                                                <input type="number" class="form-control percentage-input" id="myPercentage" name="my_percentage" min="1" max="99" value="50">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="fas fa-users mr-1"></i> Sección Compartida</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>Seleccionar sección:</strong></p>
                                            <select class="form-control" id="sharedSection" name="shared_section">
                                                <option value="">Seleccione una sección...</option>
                                                <option value="Preescolar y Primaria">Preescolar y Primaria</option>
                                                <option value="Escuela Media">Escuela Media</option>
                                                <option value="Escuela Alta / DP">Escuela Alta / DP</option>
                                                <option value="PAI">PAI</option>
                                                <option value="PEP">PEP</option>
                                                <option value="Deportes">Deportes</option>
                                                <option value="Psicología Institucional">Psicología Institucional</option>
                                                <option value="Biblioteca">Biblioteca</option>
                                                <option value="Dirección General">Dirección General</option>
                                                <option value="CAS">CAS</option>
                                                <option value="Administración">Administración</option>
                                                <option value="Tecnología Institucional">Tecnología Institucional</option>
                                            </select>
                                            <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                            <div class="input-group">
                                                <input type="number" class="form-control percentage-input" id="sharedPercentage" name="shared_percentage" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón para agregar tercera sección -->
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addThirdSection">
                                    <i class="fas fa-plus mr-1"></i> Agregar otra sección
                                </button>
                            </div>

                            <!-- Tercera sección compartida (oculta por defecto) -->
                            <div id="thirdSectionConfig" style="display: none;" class="mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-users mr-1"></i> Tercera Sección Compartida
                                                <button type="button" class="btn btn-sm btn-outline-danger float-right" id="removeThirdSection">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>Seleccionar sección:</strong></p>
                                            <select class="form-control" id="thirdSharedSection" name="third_shared_section" disabled tabindex="-1">
                                                <option value="">Seleccione una sección...</option>
                                                <option value="Preescolar y Primaria">Preescolar y Primaria</option>
                                                <option value="Escuela Media">Escuela Media</option>
                                                <option value="Escuela Alta / DP">Escuela Alta / DP</option>
                                                <option value="PAI">PAI</option>
                                                <option value="PEP">PEP</option>
                                                <option value="Deportes">Deportes</option>
                                                <option value="Psicología Institucional">Psicología Institucional</option>
                                                <option value="Biblioteca">Biblioteca</option>
                                                <option value="Dirección General">Dirección General</option>
                                                <option value="CAS">CAS</option>
                                                <option value="Administración">Administración</option>
                                                <option value="Tecnología Institucional">Tecnología Institucional</option>
                                            </select>
                                            <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                            <div class="input-group">
                                                <input type="number" class="form-control percentage-input" id="thirdSharedPercentage" name="third_shared_percentage" min="1" max="97" value="0" disabled tabindex="-1">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Importante:</strong> El total entre todas las secciones debe ser 100%. 
                                Los porcentajes se ajustarán automáticamente cuando agregue más secciones.
                            </div>
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
    }

    // Manejar cambio de tipo de servicio
    $('#service_type').on('change', function() {
        const serviceType = $(this).val();
        const providerSection = $('#provider_info_section');
        const taxSection = $('#tax_section');
        
        if (serviceType === 'no_quotation') {
            providerSection.show();
            taxSection.show();
            // Hacer campos obligatorios
            $('#provider_name, #no_quotation_reason').attr('required', true);
            // Calcular impuestos si hay valor presupuestado
            calculateTaxes();
        } else {
            providerSection.hide();
            taxSection.hide();
            // Quitar campos obligatorios y limpiar valores
            $('#provider_name, #provider_nit, #provider_contact, #provider_email, #no_quotation_reason').attr('required', false).val('');
            // Limpiar cálculos de impuestos
            $('.tax-checkbox').prop('checked', false);
            $('#tax_subtotal, #tax_total').text('$0');
            $('#tax_breakdown').empty();
            $('#subtotal_amount, #tax_amount, #total_amount').val('0');
        }
    });

    // Inicializar estado basado en valor actual
    const currentServiceType = $('#service_type').val();
    if (currentServiceType === 'no_quotation') {
        $('#provider_info_section').show();
        $('#provider_name, #no_quotation_reason').attr('required', true);
    } else {
        $('#provider_info_section').hide();
    }    // Validación del formulario
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
        
        // Verificar tipo de servicio
        if ($('#service_type').val() === '') {
            e.preventDefault();
            alert('Debe seleccionar el tipo de servicio.');
            $('#service_type').focus();
            isValid = false;
        }
        
        // Verificar campos del proveedor si es servicio sin cotización
        if ($('#service_type').val() === 'no_quotation') {
            if ($('#provider_name').val().trim() === '') {
                e.preventDefault();
                alert('El nombre del proveedor es obligatorio para servicios sin cotización.');
                $('#provider_name').focus();
                isValid = false;
            }
            
            if ($('#no_quotation_reason').val().trim() === '') {
                e.preventDefault();
                alert('La justificación para no cotizar es obligatoria.');
                $('#no_quotation_reason').focus();
                isValid = false;
            }
            
            if (!$('#quotation_file')[0].files.length) {
                e.preventDefault();
                alert('Debe adjuntar la cotización o orden de renovación.');
                $('#quotation_file').focus();
                isValid = false;
            }
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

    // Manejo de selección de proveedor
    $('#provider_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val()) {
            // Llenar los campos con la información del proveedor seleccionado
            $('#provider_nit').val(selectedOption.data('nit') || '');
            $('#provider_contact').val(selectedOption.data('contact') || '');
            $('#provider_email').val(selectedOption.data('email') || '');
            $('#provider_name_hidden').val(selectedOption.text());
        } else {
            // Limpiar los campos si no hay selección
            $('#provider_nit').val('');
            $('#provider_contact').val('');
            $('#provider_email').val('');
            $('#provider_name_hidden').val('');
        }
    });

    // Trigger del cambio si hay un valor preseleccionado (para casos de old input)
    if ($('#provider_id').val()) {
        $('#provider_id').trigger('change');
    }

    // Función para calcular impuestos
    function calculateTaxes() {
        const budget = parseFloat($('#service_budget').val()) || 0;
        let totalTaxes = 0;
        let taxBreakdown = '';
        
        if (budget > 0) {
            $('#tax_subtotal').text(formatCurrency(budget));
            $('#subtotal_amount').val(budget);
            
            $('.tax-checkbox:checked').each(function() {
                const rate = parseFloat($(this).data('rate'));
                const taxAmount = budget * (rate / 100);
                totalTaxes += taxAmount;
                
                const taxName = $(this).next('label').text().trim();
                taxBreakdown += `
                    <div class="d-flex justify-content-between text-muted">
                        <span>${taxName}:</span>
                        <span>${formatCurrency(taxAmount)}</span>
                    </div>
                `;
            });
            
            $('#tax_breakdown').html(taxBreakdown);
            $('#tax_total').text(formatCurrency(budget + totalTaxes));
            $('#tax_amount').val(totalTaxes);
            $('#total_amount').val(budget + totalTaxes);
        } else {
            $('#tax_subtotal').text('$0');
            $('#tax_total').text('$0');
            $('#tax_breakdown').empty();
            $('#subtotal_amount').val('0');
            $('#tax_amount').val('0');
            $('#total_amount').val('0');
        }
    }
    
    // Función para formatear moneda
    function formatCurrency(amount) {
        return '$' + amount.toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
    
    // Manejar cambios en checkboxes de impuestos
    $('.tax-checkbox').on('change', function() {
        calculateTaxes();
    });
    
    // Manejar cambios en el valor presupuestado
    $('#service_budget').on('input', function() {
        handleBudgetConversion();
        if ($('#service_type').val() === 'no_quotation') {
            calculateTaxes();
        }
    });

    // Inicializar estado de los botones
    updateDeleteButtons();

    // ==================== FUNCIONALIDAD DE COMPRA COMPARTIDA ====================
    
    // Función para actualizar la sección actual cuando cambie
    $('#section_area').change(function() {
        const currentSection = $(this).find('option:selected').text();
        $('#currentSection').text(currentSection || '-');
        
        // Filtrar la sección actual del dropdown de sección compartida
        $('#sharedSection option').show();
        $('#sharedSection option').each(function() {
            if ($(this).val() === $('#section_area').val()) {
                $(this).hide();
            }
        });
        
        // Reset de sección compartida si está seleccionada la misma
        if ($('#sharedSection').val() === $('#section_area').val()) {
            $('#sharedSection').val('');
        }
    });

    // Manejar cambio en la pregunta inicial
    $('input[name="is_shared"]').change(function() {
        if ($(this).val() === 'yes') {
            $('#sharedConfig').slideDown();
        } else {
            $('#sharedConfig').slideUp();
            // Limpiar también los campos de tercera sección cuando se deshabilita la compra compartida
            $('#thirdSectionConfig').hide();
            $('#addThirdSection').show();
            
            // Deshabilitar y limpiar los campos de la tercera sección
            $('#thirdSharedSection').prop('disabled', true).attr('tabindex', '-1').val('');
            $('#thirdSharedPercentage').prop('disabled', true).attr('tabindex', '-1').val(0);
        }
    });

    // Calcular porcentaje automáticamente para dos secciones
    $('#myPercentage').on('input', function() {
        calculateSharedPercentages();
    });

    // Manejar cambios en el porcentaje de la tercera sección
    $('#thirdSharedPercentage').on('input', function() {
        calculateSharedPercentages();
    });

    // Función para calcular porcentajes automáticamente
    function calculateSharedPercentages() {
        const hasThirdSection = $('#thirdSectionConfig').is(':visible');
        const myPercentage = parseInt($('#myPercentage').val()) || 0;
        
        if (hasThirdSection) {
            const thirdPercentage = parseInt($('#thirdSharedPercentage').val()) || 0;
            const sharedPercentage = 100 - myPercentage - thirdPercentage;
            
            // Validar que los porcentajes sean válidos
            if (myPercentage + thirdPercentage > 99) {
                // Ajustar automáticamente
                const newThirdPercentage = 99 - myPercentage;
                $('#thirdSharedPercentage').val(Math.max(1, newThirdPercentage));
                $('#sharedPercentage').val(1);
            } else if (sharedPercentage < 1) {
                $('#sharedPercentage').val(1);
            } else {
                $('#sharedPercentage').val(sharedPercentage);
            }
        } else {
            // Solo dos secciones
            const sharedPercentage = 100 - myPercentage;
            
            if (myPercentage < 1) {
                $('#myPercentage').val(1);
                $('#sharedPercentage').val(99);
            } else if (myPercentage > 99) {
                $('#myPercentage').val(99);
                $('#sharedPercentage').val(1);
            } else {
                $('#sharedPercentage').val(sharedPercentage);
            }
        }
    }

    // Manejar agregar tercera sección
    $('#addThirdSection').click(function() {
        $('#thirdSectionConfig').slideDown();
        $(this).hide();
        
        // Habilitar los campos de la tercera sección
        $('#thirdSharedSection').prop('disabled', false).removeAttr('tabindex');
        $('#thirdSharedPercentage').prop('disabled', false).removeAttr('tabindex');
        
        // Redistribuir porcentajes equitativamente
        const newPercentage = Math.floor(100 / 3);
        $('#myPercentage').val(newPercentage);
        $('#sharedPercentage').val(newPercentage);
        $('#thirdSharedPercentage').val(100 - (newPercentage * 2));
        
        updateSharedSectionFilters();
    });

    // Manejar remover tercera sección
    $('#removeThirdSection').click(function() {
        $('#thirdSectionConfig').slideUp();
        $('#addThirdSection').show();
        
        // Deshabilitar y limpiar los campos de la tercera sección
        $('#thirdSharedSection').prop('disabled', true).attr('tabindex', '-1');
        $('#thirdSharedPercentage').prop('disabled', true).attr('tabindex', '-1');
        
        // Resetear a dos secciones (50-50)
        $('#myPercentage').val(50);
        $('#sharedPercentage').val(50);
        $('#thirdSharedPercentage').val(0);
        $('#thirdSharedSection').val('');
        
        updateSharedSectionFilters();
    });

    // Función para actualizar filtros de secciones
    function updateSharedSectionFilters() {
        const currentSection = $('#section_area').val();
        const sharedSection = $('#sharedSection').val();
        const thirdSection = $('#thirdSharedSection').val();
        
        // Filtrar opciones para evitar duplicados
        $('#sharedSection option, #thirdSharedSection option').show();
        
        $('#sharedSection option').each(function() {
            if ($(this).val() === currentSection || $(this).val() === thirdSection) {
                $(this).hide();
            }
        });
        
        $('#thirdSharedSection option').each(function() {
            if ($(this).val() === currentSection || $(this).val() === sharedSection) {
                $(this).hide();
            }
        });
    }

    // Manejar cambios en las secciones para filtrar duplicados
    $('#section_area, #sharedSection, #thirdSharedSection').change(function() {
        updateSharedSectionFilters();
        
        if ($(this).is('#section_area')) {
            const currentSection = $(this).find('option:selected').text();
            $('#currentSection').text(currentSection || '-');
        }
    });

    // Agregar validación de compra compartida al formulario
    $('#servicesForm').off('submit').on('submit', function(e) {
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
        
        // Verificar tipo de servicio
        if ($('#service_type').val() === '') {
            e.preventDefault();
            alert('Debe seleccionar el tipo de servicio.');
            $('#service_type').focus();
            isValid = false;
        }
        
        // Verificar campos del proveedor si es servicio sin cotización
        if ($('#service_type').val() === 'no_quotation') {
            if ($('#provider_name').val().trim() === '') {
                e.preventDefault();
                alert('El nombre del proveedor es obligatorio para servicios sin cotización.');
                $('#provider_name').focus();
                isValid = false;
            }
            
            if ($('#no_quotation_reason').val().trim() === '') {
                e.preventDefault();
                alert('La justificación para no cotizar es obligatoria.');
                $('#no_quotation_reason').focus();
                isValid = false;
            }
            
            if (!$('#quotation_file')[0].files.length) {
                e.preventDefault();
                alert('Debe adjuntar la cotización o orden de renovación.');
                $('#quotation_file').focus();
                isValid = false;
            }
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
        
        // Validar configuración de compra compartida
        const isShared = $('input[name="is_shared"]:checked').val();
        if (isShared === 'yes') {
            const sharedSection = $('#sharedSection').val();
            if (!sharedSection) {
                e.preventDefault();
                alert('Por favor seleccione la segunda sección para compartir esta compra.');
                $('#sharedSection').focus();
                isValid = false;
            }
            
            // Si la tercera sección está visible, validar que esté seleccionada
            if ($('#thirdSectionConfig').is(':visible')) {
                const thirdSection = $('#thirdSharedSection').val();
                if (!thirdSection) {
                    e.preventDefault();
                    alert('Por favor seleccione la tercera sección o remueva la opción de tercera sección.');
                    $('#thirdSharedSection').focus();
                    isValid = false;
                }
            }
            
            // Validar que los porcentajes sumen 100%
            const myPercentage = parseInt($('#myPercentage').val()) || 0;
            const sharedPercentage = parseInt($('#sharedPercentage').val()) || 0;
            const thirdPercentage = parseInt($('#thirdSharedPercentage').val()) || 0;
            const total = myPercentage + sharedPercentage + thirdPercentage;
            
            if (total !== 100) {
                e.preventDefault();
                alert(`Los porcentajes deben sumar exactamente 100%. Actualmente suman ${total}%.`);
                isValid = false;
            }
        }
        
        return isValid;
    });

    // Inicializar la sección actual en el display
    const initialSection = $('#section_area option:selected').text();
    $('#currentSection').text(initialSection || '-');
    
    // Establecer valores iniciales para compra compartida
    $('#sharedPercentage').val(50);
    updateSharedSectionFilters();
});
</script>
@stop
