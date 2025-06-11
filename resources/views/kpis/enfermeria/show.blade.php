@extends('adminlte::page')

@section('title', 'Detalle del KPI - Enfermería')

@section('content_header')
    <h1>Detalle del KPI - Enfermería</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $kpi->name }}</h3>
    </div>
    <div class="card-body">
        <p><strong>Metodología de Medición:</strong> {{ $kpi->methodology }}</p>
        <p><strong>Frecuencia de Medición:</strong> {{ $kpi->frequency }}</p>
        <p><strong>Fecha de Medición:</strong> {{ \Carbon\Carbon::parse($kpi->measurement_date)->format('d/m/Y') }}</p>
        <p><strong>Porcentaje Alcanzado:</strong> {{ $kpi->percentage }}%</p>
        <p>
            <strong>Estado:</strong> 
            <span class="badge {{ $kpi->status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                {{ $kpi->status }}
            </span>
        </p>
    </div>
    <div class="card-footer">
        <a href="{{ route('kpis.enfermeria.edit', $kpi->id) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('kpis.enfermeria.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --success: #28a745;
        --danger: #dc3545;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .card-header {
        background-color: var(--primary);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 1.5rem;
    }

    .card-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 1.25rem;
        color: white;
    }

    .card-body {
        padding: 2rem;
    }

    .card-body p {
        margin-bottom: 1.25rem;
        font-size: 1rem;
        line-height: 1.6;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-body strong {
        min-width: 180px;
        color: var(--primary);
        font-weight: 600;
    }

    .badge {
        padding: 0.6em 1.2em;
        font-size: 0.9rem;
        font-weight: 500;
        border-radius: 6px;
    }

    .badge-success {
        background-color: var(--success);
    }

    .badge-danger {
        background-color: var(--danger);
    }

    .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid rgba(0,0,0,0.125);
        padding: 1.5rem;
        border-radius: 0 0 8px 8px;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .card-body p {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
        }

        .card-body strong {
            min-width: auto;
        }
    }
</style>
@stop
