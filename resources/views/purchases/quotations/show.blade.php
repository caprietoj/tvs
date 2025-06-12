@extends('adminlte::page')

@section('title', 'Detalles de cotización')

@section('content_header')
    <h1>Detalles de Cotización - Solicitud {{ $purchaseRequest->request_number }}</h1>
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
        <div class="card-header bg-info">
            <h3 class="card-title">Detalle de la cotización</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> {{ $quotation->id }}</p>
                    <p><strong>Proveedor:</strong> {{ $quotation->provider_name }}</p>
                    <p><strong>Monto total:</strong> ${{ number_format($quotation->total_amount, 2) }}</p>
                    @if($quotation->comments)
                        <p><strong>Comentarios:</strong> {{ $quotation->comments }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p><strong>Fecha de cotización:</strong> {{ $quotation->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Cargada por:</strong> {{ $quotation->uploadedByUser ? $quotation->uploadedByUser->name : 'Sistema' }}</p>
                    <p><strong>Archivo:</strong> 
                        <a href="{{ route('quotations.download', $quotation) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-download"></i> Descargar cotización
                        </a>
                    </p>
                </div>
            </div>

            <hr>

            <div class="mt-3">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                
                @if($purchaseRequest->status == 'En Cotización' || $purchaseRequest->status == 'pending')
                    <a href="{{ route('quotations.select', $quotation) }}" class="btn btn-success">
                        <i class="fas fa-check"></i> Pre-aprobar esta cotización
                    </a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        console.log('Hi!');
    </script>
@stop
