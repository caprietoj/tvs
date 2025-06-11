@extends('adminlte::page')

@section('title', 'Pre-aprobar cotización')

@section('content_header')
    <h1>Pre-aprobar cotización - Solicitud {{ $purchaseRequest->request_number }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">Información de la solicitud</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número:</strong> {{ $purchaseRequest->request_number }}</p>
                    <p><strong>Solicitante:</strong> {{ $purchaseRequest->requester }}</p>
                    <p><strong>Sección/Área:</strong> {{ $purchaseRequest->section_area }}</p>
                    <p><strong>Fecha:</strong> {{ $purchaseRequest->request_date instanceof \DateTime ? $purchaseRequest->request_date->format('d/m/Y') : 'No establecida' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Estado actual:</strong> {{ $purchaseRequest->status }}</p>
                    <p><strong>Total cotizaciones:</strong> {{ $purchaseRequest->getQuotationProgress() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-warning">
            <h3 class="card-title">Detalle de cotización seleccionada</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Proveedor:</strong> {{ $quotation->provider_name }}</p>
                    <p><strong>Monto total:</strong> ${{ number_format($quotation->total_amount, 2) }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Archivo:</strong> 
                        <a href="{{ route('quotations.download', $quotation) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-download"></i> Descargar cotización
                        </a>
                    </p>
                </div>
            </div>

            <hr>

            <form action="{{ route('quotations.select', $quotation) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="observation">Observaciones y justificación de selección *</label>
                    <textarea name="observation" id="observation" rows="4" class="form-control {{ $errors->has('observation') ? 'is-invalid' : '' }}" placeholder="Explique por qué se seleccionó esta cotización..." required>{{ old('observation') }}</textarea>
                    @if ($errors->has('observation'))
                        <div class="invalid-feedback">{{ $errors->first('observation') }}</div>
                    @endif
                    <small class="form-text text-muted">Indique los motivos por los que se seleccionó esta cotización sobre las demás.</small>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Al pre-aprobar esta cotización, la solicitud pasará a estado "Pre-aprobada" y podrá continuar con el proceso de compra.
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check"></i> Pre-aprobar esta cotización
                    </button>
                    <a href="{{ route('purchase-requests.show', $purchaseRequest->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a la solicitud
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop