@extends('adminlte::page')

@section('title', 'Editar Solicitud de Servicios')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-edit mr-2"></i>Editar Solicitud de Servicios</h1>
@stop

@section('content')
<div class="container">
    <div class="card card-outline" style="border-top-color: #364E76;">
        <div class="card-header" style="background-color: #364E76; color: white;">
            <h3 class="card-title">Editar Solicitud de Servicios #{{ $purchaseRequest->request_number }}</h3>
        </div>
        
        <form action="{{ route('purchase-requests.update', $purchaseRequest) }}" method="POST" id="servicesForm">
            @csrf
            @method('PUT')
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
                    <input type="text" class="form-control @error('requester') is-invalid @enderror" id="requester" name="requester" value="{{ old('requester', $purchaseRequest->requester) }}" readonly>
                    @error('requester')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="section_area">SECCIÓN Y/O ÁREA:</label>
                        <select class="form-control @error('section_area') is-invalid @enderror" id="section_area" name="section_area">
                            <option value="">Seleccione...</option>
                            <option value="Preescolar y Primaria" {{ old('section_area', $purchaseRequest->section_area) == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                            <option value="Escuela Media" {{ old('section_area', $purchaseRequest->section_area) == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                            <option value="Escuela Alta / DP" {{ old('section_area', $purchaseRequest->section_area) == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                            <option value="PAI" {{ old('section_area', $purchaseRequest->section_area) == 'PAI' ? 'selected' : '' }}>PAI</option>
                            <option value="PEP" {{ old('section_area', $purchaseRequest->section_area) == 'PEP' ? 'selected' : '' }}>PEP</option>
                            <option value="Deportes" {{ old('section_area', $purchaseRequest->section_area) == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                            <option value="Psicología Institucional" {{ old('section_area', $purchaseRequest->section_area) == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                            <option value="Biblioteca" {{ old('section_area', $purchaseRequest->section_area) == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                            <option value="Dirección General" {{ old('section_area', $purchaseRequest->section_area) == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                            <option value="CAS" {{ old('section_area', $purchaseRequest->section_area) == 'CAS' ? 'selected' : '' }}>CAS</option>
                            <option value="Administración" {{ old('section_area', $purchaseRequest->section_area) == 'Administración' ? 'selected' : '' }}>Administración</option>
                            <option value="Tecnología Institucional" {{ old('section_area', $purchaseRequest->section_area) == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                        </select>
                        @error('section_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="request_date">FECHA DE SOLICITUD:</label>
                        <input type="text" class="form-control" id="request_date" name="request_date" value="{{ $purchaseRequest->created_at->format('d/m/Y') }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label for="coordinator">COORDINADOR DE SECCIÓN Y/O JEFE DE ÁREA:</label>
                    <input type="text" class="form-control" id="coordinator" name="coordinator" value="{{ old('coordinator', $purchaseRequest->coordinator) }}">
                </div>

                <!-- Servicios -->
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
                                <option value="regular" {{ old('service_type', $purchaseRequest->service_type) == 'regular' ? 'selected' : '' }}>Servicio Regular (Requiere cotización)</option>
                                <option value="no_quotation" {{ old('service_type', $purchaseRequest->service_type) == 'no_quotation' ? 'selected' : '' }}>Servicio sin Cotización (Renovación de licencia, proveedor único, etc.)</option>
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
                        <div id="provider_info_section" style="display: {{ old('service_type', $purchaseRequest->service_type) == 'no_quotation' ? 'block' : 'none' }};">
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
                                                            {{ (old('provider_id', $purchaseRequest->provider_id) == $provider->id) ? 'selected' : '' }}>
                                                        {{ $provider->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="provider_name" id="provider_name_hidden" value="{{ old('provider_name', $purchaseRequest->provider_name) }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="provider_nit">NIT DEL PROVEEDOR:</label>
                                            <input type="text" class="form-control" id="provider_nit" name="provider_nit" value="{{ old('provider_nit', $purchaseRequest->provider_nit) }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="provider_contact">CONTACTO:</label>
                                            <input type="text" class="form-control" id="provider_contact" name="provider_contact" value="{{ old('provider_contact', $purchaseRequest->provider_contact) }}" readonly>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="provider_email">EMAIL:</label>
                                            <input type="email" class="form-control" id="provider_email" name="provider_email" value="{{ old('provider_email', $purchaseRequest->provider_email) }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="no_quotation_reason">JUSTIFICACIÓN PARA NO COTIZAR <span class="text-danger">*</span>:</label>
                                        <textarea class="form-control" id="no_quotation_reason" name="no_quotation_reason" rows="2" placeholder="Ej: Renovación de licencia anual, Proveedor único autorizado, Continuidad de servicio, etc.">{{ old('no_quotation_reason', $purchaseRequest->no_quotation_reason) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="service_justification">JUSTIFICACIÓN DEL SERVICIO <span class="text-danger">*</span>:</label>
                            <textarea class="form-control @error('service_justification') is-invalid @enderror" id="service_justification" name="service_justification" rows="3" placeholder="Describa la justificación para la contratación del servicio solicitado" required>{{ old('service_justification', $purchaseRequest->service_justification) }}</textarea>
                            @error('service_justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
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
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" class="form-control @error('service_budget') is-invalid @enderror" id="service_budget" name="service_budget" min="0" step="0.01" value="{{ old('service_budget', $purchaseRequest->service_budget) }}" required>
                                        </div>
                                        @error('service_budget')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="service_budget_text">VALOR EN LETRAS:</label>
                                        <input type="text" class="form-control @error('service_budget_text') is-invalid @enderror" id="service_budget_text" name="service_budget_text" value="{{ old('service_budget_text', $purchaseRequest->service_budget_text) }}" readonly>
                                        @error('service_budget_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Servicios solicitados -->
                        <div class="card mt-4">
                            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 2px solid #364E76;">
                                <h5 class="mb-0" style="color: #364E76;">
                                    <i class="fas fa-list mr-2"></i>Detalle de Servicios Solicitados
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%;">ITEM</th>
                                                <th style="width: 10%;">CANT.</th>
                                                <th style="width: 60%;">DESCRIPCIÓN <span class="text-danger">*</span></th>
                                                <th style="width: 20%;">OBSERVACIONES</th>
                                                <th style="width: 5%;">ACCIÓN</th>
                                            </tr>
                                        </thead>
                                        <tbody id="serviceItemsBody">
                                            @if(old('service_items', $purchaseRequest->service_items))
                                                @foreach(old('service_items', $purchaseRequest->service_items) as $index => $service)
                                                    @if(!empty($service['description']) || !empty($service['quantity']))
                                                    <tr>
                                                        <td>
                                                            <input type="number" class="form-control" name="service_items[{{ $index }}][item]" value="{{ $service['item'] ?? ($index + 1) }}" min="1">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="service_items[{{ $index }}][quantity]" value="{{ $service['quantity'] ?? 1 }}" min="1">
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control" name="service_items[{{ $index }}][description]" rows="2">{{ $service['description'] ?? '' }}</textarea>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control" name="service_items[{{ $index }}][observations]" rows="2">{{ $service['observations'] ?? '' }}</textarea>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm delete-service-btn">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td>
                                                        <input type="number" class="form-control" name="service_items[0][item]" value="1" min="1">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="service_items[0][quantity]" value="1" min="1">
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control" name="service_items[0][description]" rows="2"></textarea>
                                                    </td>
                                                    <td>
                                                        <textarea class="form-control" name="service_items[0][observations]" rows="2"></textarea>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm delete-service-btn">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-secondary btn-sm" id="addServiceItem">
                                        <i class="fas fa-plus mr-1"></i> Agregar Servicio
                                    </button>
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
                                            <input class="form-check-input" type="radio" name="is_shared" id="is_shared_no" value="no" {{ old('is_shared', $purchaseRequest->is_shared ? 'yes' : 'no') == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_shared_no">
                                                <i class="fas fa-times text-danger mr-1"></i> No, esta compra es solo para mi sección
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_shared" id="is_shared_yes" value="yes" {{ old('is_shared', $purchaseRequest->is_shared ? 'yes' : 'no') == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_shared_yes">
                                                <i class="fas fa-check text-success mr-1"></i> Sí, esta compra será compartida
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuración de compra compartida -->
                                <div id="sharedConfig" style="display: {{ old('is_shared', $purchaseRequest->is_shared ? 'yes' : 'no') == 'yes' ? 'block' : 'none' }};">
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-user mr-1"></i> Mi Sección</h6>
                                                </div>
                                                <div class="card-body">
                                                    <p class="mb-1"><strong>Sección:</strong></p>
                                                    <p class="section-display" id="currentSection">{{ $purchaseRequest->section_area ?? '-' }}</p>
                                                    <p class="mb-1"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="myPercentage" name="my_percentage" min="1" max="99" value="{{ old('my_percentage', $purchaseRequest->my_percentage ?? 50) }}">
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
                                                        <option value="Preescolar y Primaria" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                                                        <option value="Escuela Media" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                                                        <option value="Escuela Alta / DP" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                                                        <option value="PAI" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'PAI' ? 'selected' : '' }}>PAI</option>
                                                        <option value="PEP" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'PEP' ? 'selected' : '' }}>PEP</option>
                                                        <option value="Deportes" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                                                        <option value="Psicología Institucional" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                                                        <option value="Biblioteca" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                                                        <option value="Dirección General" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                                                        <option value="CAS" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'CAS' ? 'selected' : '' }}>CAS</option>
                                                        <option value="Administración" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Administración' ? 'selected' : '' }}>Administración</option>
                                                        <option value="Tecnología Institucional" {{ old('shared_section', $purchaseRequest->shared_section ?? '') == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                                                    </select>
                                                    <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="sharedPercentage" name="shared_percentage" value="{{ old('shared_percentage', $purchaseRequest->shared_percentage ?? '') }}" readonly>
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
                                    <div id="thirdSectionConfig" style="display: {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') ? 'block' : 'none' }};" class="mt-3">
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
                                                    <select class="form-control" id="thirdSharedSection" name="third_shared_section">
                                                        <option value="">Seleccione una sección...</option>
                                                        <option value="Preescolar y Primaria" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Preescolar y Primaria' ? 'selected' : '' }}>Preescolar y Primaria</option>
                                                        <option value="Escuela Media" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Escuela Media' ? 'selected' : '' }}>Escuela Media</option>
                                                        <option value="Escuela Alta / DP" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Escuela Alta / DP' ? 'selected' : '' }}>Escuela Alta / DP</option>
                                                        <option value="PAI" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'PAI' ? 'selected' : '' }}>PAI</option>
                                                        <option value="PEP" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'PEP' ? 'selected' : '' }}>PEP</option>
                                                        <option value="Deportes" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Deportes' ? 'selected' : '' }}>Deportes</option>
                                                        <option value="Psicología Institucional" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Psicología Institucional' ? 'selected' : '' }}>Psicología Institucional</option>
                                                        <option value="Biblioteca" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Biblioteca' ? 'selected' : '' }}>Biblioteca</option>
                                                        <option value="Dirección General" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Dirección General' ? 'selected' : '' }}>Dirección General</option>
                                                        <option value="CAS" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'CAS' ? 'selected' : '' }}>CAS</option>
                                                        <option value="Administración" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Administración' ? 'selected' : '' }}>Administración</option>
                                                        <option value="Tecnología Institucional" {{ old('third_shared_section', $purchaseRequest->third_shared_section ?? '') == 'Tecnología Institucional' ? 'selected' : '' }}>Tecnología Institucional</option>
                                                    </select>
                                                    <p class="mb-1 mt-2"><strong>Porcentaje a pagar:</strong></p>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control percentage-input" id="thirdSharedPercentage" name="third_shared_percentage" min="1" max="97" value="{{ old('third_shared_percentage', $purchaseRequest->third_shared_percentage ?? 0) }}">
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
                        <div class="form-group mt-4">
                            <label for="general_observations">OBSERVACIONES GENERALES:</label>
                            <textarea class="form-control @error('general_observations') is-invalid @enderror" id="general_observations" name="general_observations" rows="3" placeholder="Observaciones adicionales sobre la solicitud">{{ old('general_observations', $purchaseRequest->general_observations) }}</textarea>
                            @error('general_observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Actualizar Solicitud
                </button>
                <a href="{{ route('purchase-requests.show', $purchaseRequest) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let serviceIndex = {{ count(old('service_items', $purchaseRequest->service_items ?? [])) }};

    // Agregar nuevo servicio
    $('#addServiceItem').click(function() {
        const newRow = `
            <tr>
                <td>
                    <input type="number" class="form-control" name="service_items[${serviceIndex}][item]" value="${serviceIndex + 1}" min="1">
                </td>
                <td>
                    <input type="number" class="form-control" name="service_items[${serviceIndex}][quantity]" value="1" min="1">
                </td>
                <td>
                    <textarea class="form-control" name="service_items[${serviceIndex}][description]" rows="2"></textarea>
                </td>
                <td>
                    <textarea class="form-control" name="service_items[${serviceIndex}][observations]" rows="2"></textarea>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm delete-service-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#serviceItemsBody').append(newRow);
        serviceIndex++;
        updateDeleteButtons();
    });

    // Eliminar servicio
    $(document).on('click', '.delete-service-btn', function() {
        $(this).closest('tr').remove();
        updateDeleteButtons();
    });

    // Función para actualizar botones de eliminar
    function updateDeleteButtons() {
        const rowCount = $('#serviceItemsBody tr').length;
        $('.delete-service-btn').prop('disabled', rowCount <= 1);
    }

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
        calculatePercentages();
    });

    // Manejar cambios en el porcentaje de la tercera sección
    $('#thirdSharedPercentage').on('input', function() {
        calculatePercentages();
    });

    // Función para calcular porcentajes automáticamente
    function calculatePercentages() {
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
        
        updateSectionFilters();
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
        
        updateSectionFilters();
    });

    // Función para actualizar filtros de secciones
    function updateSectionFilters() {
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
        updateSectionFilters();
        
        if ($(this).is('#section_area')) {
            const currentSection = $(this).find('option:selected').text();
            $('#currentSection').text(currentSection || '-');
        }
    });

    // Validación adicional en el envío del formulario
    $('#servicesForm').submit(function(e) {
        // Validar configuración de compra compartida
        const isShared = $('input[name="is_shared"]:checked').val();
        if (isShared === 'yes') {
            const sharedSection = $('#sharedSection').val();
            if (!sharedSection) {
                alert('Por favor seleccione la segunda sección para compartir esta compra.');
                $('#sharedSection').focus();
                e.preventDefault();
                return false;
            }
            
            // Si la tercera sección está visible, validar que esté seleccionada
            if ($('#thirdSectionConfig').is(':visible')) {
                const thirdSection = $('#thirdSharedSection').val();
                if (!thirdSection) {
                    alert('Por favor seleccione la tercera sección o remueva la opción de tercera sección.');
                    $('#thirdSharedSection').focus();
                    e.preventDefault();
                    return false;
                }
            }
            
            // Validar que los porcentajes sumen 100%
            const myPercentage = parseInt($('#myPercentage').val()) || 0;
            const sharedPercentage = parseInt($('#sharedPercentage').val()) || 0;
            const thirdPercentage = parseInt($('#thirdSharedPercentage').val()) || 0;
            const total = myPercentage + sharedPercentage + thirdPercentage;
            
            if (total !== 100) {
                alert(`Los porcentajes deben sumar exactamente 100%. Actualmente suman ${total}%.`);
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });

    // Inicializar los valores al cargar la página
    updateDeleteButtons();
    
    // Inicializar la sección actual en el display
    const initialSection = $('#section_area option:selected').text();
    $('#currentSection').text(initialSection || '-');
    
    // Inicializar filtros de secciones
    updateSectionFilters();
    
    // Si hay datos previos de tercera sección, mostrar la configuración
    @if(old('third_shared_section', $purchaseRequest->third_shared_section ?? ''))
        $('#addThirdSection').hide();
        $('#thirdSharedSection').prop('disabled', false).removeAttr('tabindex');
        $('#thirdSharedPercentage').prop('disabled', false).removeAttr('tabindex');
    @endif

    // Función para convertir números a palabras (versión simplificada)
    function convertNumberToWords(num) {
        const unidades = ['', 'Un', 'Dos', 'Tres', 'Cuatro', 'Cinco', 'Seis', 'Siete', 'Ocho', 'Nueve'];
        const decenas = ['', '', 'Veinte', 'Treinta', 'Cuarenta', 'Cincuenta', 'Sesenta', 'Setenta', 'Ochenta', 'Noventa'];
        const especiales = ['Diez', 'Once', 'Doce', 'Trece', 'Catorce', 'Quince', 'Dieciséis', 'Diecisiete', 'Dieciocho', 'Diecinueve'];
        
        if (num === 0) return 'Cero pesos';
        if (num < 10) return unidades[num] + ' pesos';
        if (num >= 10 && num < 20) return especiales[num - 10] + ' pesos';
        if (num >= 20 && num < 100) {
            const dec = Math.floor(num / 10);
            const uni = num % 10;
            return decenas[dec] + (uni > 0 ? ' y ' + unidades[uni].toLowerCase() : '') + ' pesos';
        }
        
        // Para números más grandes, devolver una representación simplificada
        let resultado = num.toLocaleString('es-CO') + ' pesos';
        
        // Capitalizar la primera letra
        return resultado.charAt(0).toUpperCase() + resultado.slice(1);
    }

    // Conversión de números a letras para el presupuesto
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

    // Manejar cambio de tipo de servicio
    $('#service_type').on('change', function() {
        const serviceType = $(this).val();
        const providerSection = $('#provider_info_section');
        
        if (serviceType === 'no_quotation') {
            providerSection.show();
            // Hacer campos obligatorios
            $('#provider_name, #no_quotation_reason').attr('required', true);
        } else {
            providerSection.hide();
            // Quitar campos obligatorios y limpiar valores
            $('#provider_name, #provider_nit, #provider_contact, #provider_email, #no_quotation_reason').attr('required', false);
        }
    });

    // Inicializar estado basado en valor actual
    const currentServiceType = $('#service_type').val();
    if (currentServiceType === 'no_quotation') {
        $('#provider_info_section').show();
        $('#provider_name, #no_quotation_reason').attr('required', true);
    } else {
        $('#provider_info_section').hide();
    }

    // Validación del formulario
    $('#servicesForm').on('submit', function(e) {
        let hasServiceItems = false;
        let isValid = true;
        
        // Verificar si hay al menos un servicio con descripción
        $('#serviceItemsBody input[name*="[description]"], #serviceItemsBody textarea[name*="[description]"]').each(function() {
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

    // Inicializar estado de los botones
    updateDeleteButtons();
});
</script>
@stop
