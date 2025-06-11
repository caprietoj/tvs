@extends('adminlte::page')

@section('title', 'Ver KPI - Enfermería')

@section('content_header')
    <h1>KPIs - Enfermería</h1>
@stop

@section('content')
<!-- Sección de KPIs de Medición -->
<div class="card">
    <div class="card-header">
        <div class="float-right">
            <form method="GET" action="{{ route('kpis.enfermeria.index') }}" class="form-inline">
                <label for="month" class="mr-2">Filtrar por Mes:</label>
                <select name="month" id="month" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </form>
        </div>
        <h3 class="card-title"><b>KPIs de Medición</b></h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <table id="measurementTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del KPI</th>
                    <th>Metodología</th>
                    <th>Frecuencia</th>
                    <th>Fecha de Medición</th>
                    <th>Porcentaje</th>
                    <th>Análisis</th> <!-- New column -->
                    <th>URL</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kpis->where('type', 'measurement') as $kpi)
                    <tr>
                        <td>{{ $kpi->id }}</td>
                        <td>{{ $kpi->name }}</td>
                        <td>{{ $kpi->methodology }}</td>
                        <td>{{ $kpi->frequency }}</td>
                        <td>{{ \Carbon\Carbon::parse($kpi->measurement_date)->format('d/m/Y') }}</td>
                        <td class="percentage-cell" 
                            data-toggle="tooltip" 
                            data-html="true" 
                            title="<div class='text-left'>
                                <strong>Análisis:</strong><br>
                                Estado: {{ $kpi->status }}<br>
                                Umbral: {{ $kpi->threshold->value }}%<br>
                                @if(strpos($kpi->methodology, 'atenciones basado en') !== false)
                                    Tipo: Atención Médica<br>
                                    {{ explode('.', $kpi->methodology)[0] }}
                                @else
                                    Tipo: General<br>
                                    Variación: {{ $kpi->percentage - $kpi->threshold->value }}%
                                @endif
                            </div>"
                            data-kpi-id="{{ $kpi->id }}"
                            data-kpi-type="{{ strpos($kpi->methodology, 'atenciones basado en') !== false ? 'patient-care' : 'general' }}"
                            style="cursor: pointer;">
                            {{ $kpi->percentage }}%
                        </td>
                        <td>{{ $kpi->analysis ?? 'No disponible' }}</td> <!-- New column -->
                        <td>
                            @if(isset($kpi->url) && !empty($kpi->url))
                                <a href="{{ $kpi->url }}" target="_blank" class="btn-link">
                                    <i class="fas fa-external-link-alt"></i> Ver enlace
                                </a>
                            @else
                                <span class="text-muted">No disponible</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $kpi->status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                                {{ $kpi->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('kpis.enfermeria.show', $kpi->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('kpis.enfermeria.edit', $kpi->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-kpi" data-id="{{ $kpi->id }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Sección de KPIs Informativos -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title"><b>KPIs Informativos</b></h3>
    </div>
    <div class="card-body">
        <table id="informativeTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del KPI</th>
                    <th>Metodología</th>
                    <th>Frecuencia</th>
                    <th>Fecha de Medición</th>
                    <th>Porcentaje</th>
                    <th>Análisis</th> <!-- New column -->
                    <th>URL</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kpis->where('type', 'informative') as $kpi)
                    <tr>
                        <td>{{ $kpi->id }}</td>
                        <td>{{ $kpi->name }}</td>
                        <td>{{ $kpi->methodology }}</td>
                        <td>{{ $kpi->frequency }}</td>
                        <td>{{ \Carbon\Carbon::parse($kpi->measurement_date)->format('d/m/Y') }}</td>
                        <td class="percentage-cell" 
                            data-toggle="tooltip" 
                            data-html="true" 
                            title="<div class='text-left'>
                                <strong>Análisis Rápido:</strong><br>
                                Estado: {{ $kpi->status }}<br>
                                Umbral: {{ $kpi->threshold->value }}%<br>
                                @if(strpos($kpi->methodology, 'atenciones basado en') !== false)
                                    Tipo: Atención Médica<br>
                                    {{ explode('.', $kpi->methodology)[0] }}
                                @else
                                    Tipo: General<br>
                                    Variación: {{ $kpi->percentage - $kpi->threshold->value }}%
                                @endif
                            </div>"
                            data-kpi-id="{{ $kpi->id }}"
                            data-kpi-type="{{ strpos($kpi->methodology, 'atenciones basado en') !== false ? 'patient-care' : 'general' }}"
                            style="cursor: pointer;">
                            {{ $kpi->percentage }}%
                        </td>
                        <td>{{ $kpi->analysis ?? 'No disponible' }}</td> <!-- New column -->
                        <td>
                            @if(isset($kpi->url) && !empty($kpi->url))
                                <a href="{{ $kpi->url }}" target="_blank" class="btn-link">
                                    <i class="fas fa-external-link-alt"></i> Ver enlace
                                </a>
                            @else
                                <span class="text-muted">No disponible</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $kpi->status == 'Alcanzado' ? 'badge-success' : 'badge-danger' }}">
                                {{ $kpi->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('kpis.enfermeria.show', $kpi->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('kpis.enfermeria.edit', $kpi->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-kpi" data-id="{{ $kpi->id }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary: #364E76;
        --accent: #ED3236;
        --success: #28a745;
        --warning: #ffc107;
        --info: #17a2b8;
    }

    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .card-header {
        background-color: var(--primary);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 1rem 1.5rem;
    }

    .table thead th {
        background-color: var(--primary);
        color: white;
        border: none;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        padding: 0.5em 1em;
        border-radius: 4px;
        font-weight: 500;
    }

    .badge-success { background-color: var(--success); }
    .badge-danger { background-color: var(--accent); }

    .btn-group .btn {
        margin: 0 2px;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
    }

    .alert {
        border-radius: 6px;
        margin-bottom: 1rem;
    }

    canvas {
        max-width: 100%;
    }

    /* DataTables customization */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: white !important;
    }
    
    /* Estilos para los botones de acción */
    .btn-group .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 3px;
        border-radius: 4px;
    }
    
    .btn-link {
        color: var(--primary);
        text-decoration: none;
    }
    
    .btn-link:hover {
        text-decoration: underline;
    }

    .kpi-modal {
        max-width: 400px !important;
    }

    .kpi-header {
        text-align: center;
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .kpi-body {
        padding: 1.5rem;
    }

    .kpi-progress {
        position: relative;
        width: 180px;
        height: 180px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #f3f3f3, #ffffff);
        border-radius: 50%;
        box-shadow: 
            8px 8px 16px rgba(0,0,0,0.1),
            -8px -8px 16px rgba(255,255,255,0.8);
    }

    .kpi-percentage {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2.5rem;
        font-weight: 700;
        color: #364E76;
        text-align: center;
        line-height: 1;
        z-index: 2;
    }

    .kpi-percentage::after {
        content: '';
        display: block;
        width: 40px;
        height: 3px;
        background: currentColor;
        opacity: 0.1;
        margin: 8px auto 0;
        border-radius: 2px;
    }

    .kpi-circle {
        transform: rotate(-90deg);
        position: relative;
        z-index: 1;
    }

    .kpi-circle-bg {
        fill: none;
        stroke: rgba(0,0,0,0.05);
        stroke-width: 8;
    }

    .kpi-circle-progress {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
        filter: drop-shadow(0 0 4px rgba(0,0,0,0.2));
        transition: stroke-dashoffset 1.5s ease-in-out, stroke 0.5s ease;
    }

    .kpi-status {
        text-align: center;
        margin-top: 1rem;
    }

    .kpi-details {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
        font-size: 0.9rem;
        color: #666;
    }
</style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize Bootstrap tooltips with options
    $('[data-toggle="tooltip"]').each(function() {
        // Remove any existing tooltip
        $(this).tooltip('dispose');
        // Initialize with options
        $(this).tooltip({
            html: true,
            placement: 'right',
            boundary: 'window',
            template: `
                <div class="tooltip" role="tooltip">
                    <div class="arrow"></div>
                    <div class="tooltip-inner bg-white text-dark"></div>
                </div>
            `
        });
    });

    // Handle percentage cell click
    $('.percentage-cell').click(function() {
        const cell = $(this); // Store reference to clicked cell
        const percentage = parseFloat($(this).text());
        const status = $(this).closest('tr').find('.badge').text().trim();
        const frequency = $(this).closest('tr').find('td:eq(3)').text().trim();
        const date = $(this).closest('tr').find('td:eq(4)').text().trim();
        
        const radius = 60;
        const circumference = 2 * Math.PI * radius;
        const progress = percentage / 100;
        const dashoffset = circumference * (1 - progress);
        let strokeColor;
        
        // Enhanced color based on percentage
        if (percentage >= 90) {
            strokeColor = '#28a745';
        } else if (percentage >= 70) {
            strokeColor = '#17a2b8';
        } else if (percentage >= 50) {
            strokeColor = '#ffc107';
        } else {
            strokeColor = '#dc3545';
        }

        Swal.fire({
            html: `
                <div class="kpi-header" role="region" aria-label="Detalles del KPI">
                    <div class="kpi-progress" role="progressbar" aria-valuenow="${percentage}" 
                         aria-valuemin="0" aria-valuemax="100">
                        <svg height="180" width="180" class="kpi-circle">
                            <circle class="kpi-circle-bg" 
                                cx="90" 
                                cy="90" 
                                r="${radius}"/>
                            <circle class="kpi-circle-progress" 
                                cx="90" 
                                cy="90" 
                                r="${radius}"
                                stroke="${strokeColor}"
                                stroke-dasharray="${circumference}"
                                stroke-dashoffset="${circumference}"
                                style="transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1)"/>
                        </svg>
                        <div class="kpi-percentage" aria-live="polite">
                            <span>0%</span>
                        </div>
                    </div>
                    <div class="kpi-status" role="status">
                        <span class="badge badge-${status === 'Alcanzado' ? 'success' : 'danger'} px-4 py-2">
                            ${status}
                        </span>
                    </div>
                </div>
                <div class="kpi-details" role="complementary">
                    <small>Última medición: ${date} (${frequency})</small>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            allowEscapeKey: true,
            allowOutsideClick: true,
            focusConfirm: false,
            customClass: {
                popup: 'kpi-modal',
                container: 'kpi-modal-container'
            },
            didOpen: (modal) => {
                modal.querySelector('.swal2-close').focus();
                
                requestAnimationFrame(() => {
                    const progressCircle = modal.querySelector('.kpi-circle-progress');
                    const percentageSpan = modal.querySelector('.kpi-percentage span');
                    progressCircle.style.strokeDashoffset = dashoffset;
                    
                    const duration = 1500;
                    const start = performance.now();
                    
                    const updateCounter = (currentTime) => {
                        const elapsed = currentTime - start;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        const currentPercentage = progress * percentage;
                        percentageSpan.textContent = `${Math.round(currentPercentage)}%`;
                        
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        }
                    };
                    
                    requestAnimationFrame(updateCounter);
                });
            },
            willClose: () => {
                cell.focus(); // Return focus to the clicked cell
            }
        });
    });

    // Add styles for better focus indication
    $('head').append(`
        <style>
            .swal2-popup *:focus {
                outline: 2px solid var(--primary);
                outline-offset: 2px;
            }
            
            .swal2-close:focus {
                box-shadow: none !important;
                outline: 2px solid var(--primary) !important;
                outline-offset: 2px;
            }
            
            [role="dialog"] {
                animation: none !important;
            }
            
            .kpi-modal-container {
                padding: 0;
            }
        </style>
    `);

    // Initialize DataTables
    $('#measurementTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });
    
    $('#informativeTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });

    // Manejo de eliminación
    $('.delete-kpi').click(function(){
        var kpiId = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/enfermeria/kpis/' + kpiId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            '¡Eliminado!',
                            response.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el KPI',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@stop