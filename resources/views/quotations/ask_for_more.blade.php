@extends('adminlte::page')

@section('title', '¿Adjuntar más cotizaciones?')

@section('content_header')
    <h1>¿Adjuntar más cotizaciones?</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-warning">
            <h3 class="card-title">Cotizaciones incompletas - Solicitud {{ $purchaseRequest->request_number }}</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p><strong>Actualmente hay {{ $purchaseRequest->getQuotationProgress() }} cotizaciones adjuntas a esta solicitud.</strong></p>
                <p>Se recomienda adjuntar {{ $purchaseRequest->getRequiredQuotationsCount() }} cotizaciones para cada solicitud de compra.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h4>Información de la solicitud</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Número:</strong> {{ $purchaseRequest->request_number }}</p>
                            <p><strong>Solicitante:</strong> {{ $purchaseRequest->requester }}</p>
                            <p><strong>Sección/Área:</strong> {{ $purchaseRequest->section_area }}</p>
                            <p><strong>Fecha:</strong> {{ $purchaseRequest->request_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h4>Cotizaciones actuales</h4>
                        </div>
                        <div class="card-body">
                            @if ($purchaseRequest->quotations->count() > 0)
                                <ul class="list-group">
                                    @foreach ($purchaseRequest->quotations as $quotation)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $quotation->provider_name }}
                                            <span class="badge bg-primary">${{ number_format($quotation->total_amount, 2) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No hay cotizaciones adjuntas.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h4>¿Desea adjuntar otra cotización?</h4>
                <p>Si responde "No", se enviará un correo a la sección correspondiente para que revisen la solicitud con las cotizaciones actuales.</p>
                
                <form action="{{ route('quotations.process-more', $purchaseRequest->id) }}" method="POST">
                    @csrf
                    <div class="btn-group">
                        <button type="submit" name="answer" value="yes" class="btn btn-lg btn-success">
                            <i class="fas fa-check"></i> Sí, adjuntar otra cotización
                        </button>
                        <button type="submit" name="answer" value="no" class="btn btn-lg btn-danger">
                            <i class="fas fa-times"></i> No, enviar para revisión
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('purchase-requests.show', $purchaseRequest->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la solicitud
            </a>
        </div>
    </div>
@stop