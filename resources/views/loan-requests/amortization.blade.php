@extends('adminlte::page')

@section('title', 'Tabla de Amortización')

@section('content_header')
    <h1>Tabla de Amortización - Solicitud #{{ $loanRequest->id }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles del Préstamo</h3>
                    <div class="card-tools">
                        <a href="{{ route('loan-requests.show', $loanRequest) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a la Solicitud
                        </a>
                        <a href="{{ route('loan-requests.generate-pdf', $loanRequest) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-file-pdf"></i> Generar PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Solicitante:</th>
                                    <td>{{ $loanRequest->full_name }}</td>
                                </tr>
                                <tr>
                                    <th>Documento:</th>
                                    <td>{{ $loanRequest->document_number }}</td>
                                </tr>
                                <tr>
                                    <th>Cargo:</th>
                                    <td>{{ $loanRequest->position }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Monto Solicitado:</th>
                                    <td>${{ number_format($loanRequest->amount, 2, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Cuotas:</th>
                                    <td>{{ $loanRequest->installments }} {{ $loanRequest->installment_type === 'monthly' ? 'mensuales' : 'quincenales' }}</td>
                                </tr>
                                <tr>
                                    <th>Valor de cada cuota:</th>
                                    <td>${{ number_format($loanRequest->installment_value, 2, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Inicio de Deducciones:</th>
                                    <td>{{ \Carbon\Carbon::parse($loanRequest->deduction_start_date)->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tabla de Amortización</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Cuota</th>
                                    <th>Fecha de Pago</th>
                                    <th>Valor Cuota</th>
                                    <th>Capital</th>
                                    <th>Interés</th>
                                    <th>Saldo Pendiente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($amortizationTable as $row)
                                <tr>
                                    <td>{{ $row['installment_number'] }}</td>
                                    <td>{{ $row['payment_date'] }}</td>
                                    <td>${{ number_format($row['installment_value'], 2, ',', '.') }}</td>
                                    <td>${{ number_format($row['principal'], 2, ',', '.') }}</td>
                                    <td>${{ number_format($row['interest'], 2, ',', '.') }}</td>
                                    <td>${{ number_format($row['remaining_amount'], 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="2">Total</td>
                                    <td>${{ number_format(array_sum(array_column($amortizationTable, 'installment_value')), 2, ',', '.') }}</td>
                                    <td>${{ number_format(array_sum(array_column($amortizationTable, 'principal')), 2, ',', '.') }}</td>
                                    <td>${{ number_format(array_sum(array_column($amortizationTable, 'interest')), 2, ',', '.') }}</td>
                                    <td>$0,00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-muted">
                        <small>* La tabla de amortización es informativa y puede estar sujeta a cambios según las políticas de la institución.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Tabla de amortización cargada');
    </script>
@stop