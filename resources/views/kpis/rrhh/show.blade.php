{{-- resources/views/kpis/rrhh/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detalle del KPI - Recursos Humanos')

@section('content_header')
    <h1>Detalle del KPI - Recursos Humanos</h1>
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
            <span class="badge {{ $kpi->status=='Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                {{ $kpi->status }}
            </span>
        </p>
    </div>
    <div class="card-footer">
        <a href="{{ route('kpis.rrhh.edit', $kpi->id) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('kpis.rrhh.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #1a4884;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --border-radius: 8px;
        --box-shadow: 0 2px 4px rgba(0,0,0,.08);
    }

    .card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,.12);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        color: white;
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        padding: 1.5rem;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .card-body {
        padding: 2rem;
    }

    .card-body p {
        margin-bottom: 1.25rem;
        font-size: 1rem;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-body p strong {
        color: var(--primary);
        min-width: 200px;
    }

    .badge {
        padding: 0.5rem 1rem;
        font-weight: 500;
        border-radius: 20px;
    }

    .badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #f86384 100%);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(26, 72, 132, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2a5298 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 72, 132, 0.35);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
        border: none;
    }
</style>
@stop