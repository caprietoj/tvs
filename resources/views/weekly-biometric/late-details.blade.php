<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de Llegadas Tarde - {{ $department }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            margin: 0; 
            padding: 10px; 
            background: white; 
            font-family: 'Segoe UI', sans-serif;
            font-size: 13px;
        }
        .table {
            margin-bottom: 0;
            font-size: 13px;
            border: 1px solid #dee2e6;
        }
        .table th { 
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding: 8px;
        }
        .table td {
            padding: 6px 8px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .time-badge {
            color: #666;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 2px 6px;
            font-size: 12px;
            border-radius: 3px;
            margin-right: 4px;
        }
        .text-muted {
            color: #6c757d !important;
        }
        .table-container {
            max-width: 700px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th class="text-center">Frecuencia</th>
                    <th>Registro de Llegadas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee['name'] }}</td>
                        <td class="text-center">{{ $employee['late_count'] }}</td>
                        <td>
                            @foreach($employee['entry_times'] as $time)
                                <span class="time-badge">{{ $time }}</span>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            No hay registros de llegadas tarde
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
