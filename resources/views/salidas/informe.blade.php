<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Salida Pedagógica {{ $salida->consecutivo }}</title>
    <style>
        @page {
            size: letter;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.3;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            font-size: 11px;
        }

        .titulo-informe {
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 4px;
            color: #233e6c;
        }

        .subtitulo-informe {
            text-align: center;
            font-size: 12px;
            margin-bottom: 4px;
            color: #555;
        }

        .meta-informe {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-bottom: 15px;
        }

        .section {
            margin-bottom: 16px;
        }

        .section-title {
            background-color: #233e6c;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            padding: 6px 10px;
            border-radius: 3px;
            margin-bottom: 8px;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.info-table td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            font-size: 10.5px;
            vertical-align: top;
        }

        table.info-table td.label {
            background-color: #f0f3f8;
            font-weight: bold;
            width: 30%;
            color: #233e6c;
        }

        table.info-table tr:nth-child(even) td:not(.label) {
            background-color: #fafbfe;
        }

        .historial-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .historial-table th {
            background-color: #233e6c;
            color: #fff;
            padding: 6px 7px;
            text-align: left;
            border: 1px solid #233e6c;
            text-transform: uppercase;
            font-size: 9.5px;
        }

        .historial-table td {
            border: 1px solid #d0d0d0;
            padding: 5px 7px;
            vertical-align: top;
        }

        .historial-table tr:nth-child(even) td {
            background-color: #f4f6fb;
        }

        .badge-action {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9.5px;
            color: #fff;
        }

        .badge-created { background-color: #28a745; }
        .badge-updated { background-color: #ffc107; color: #333; }
        .badge-manual_edit { background-color: #17a2b8; }
        .badge-deleted { background-color: #dc3545; }

        .change-detail {
            margin-bottom: 4px;
        }

        .change-field {
            font-weight: bold;
            color: #233e6c;
        }

        .empty {
            color: #999;
            font-style: italic;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="titulo-informe">Informe Detallado de Salida Pedagógica</div>
    <div class="subtitulo-informe">Consecutivo: <strong>{{ $salida->consecutivo }}</strong></div>
    <div class="meta-informe">
        Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} por {{ auth()->user()->name }}
        ({{ auth()->user()->email }})
    </div>

    <!-- Información General -->
    <div class="section">
        <div class="section-title">Información General</div>
        <table class="info-table">
            <tr>
                <td class="label">Consecutivo</td>
                <td>{{ $salida->consecutivo }}</td>
                <td class="label">Estado</td>
                <td>{{ $salida->estado }}</td>
            </tr>
            <tr>
                <td class="label">Grados</td>
                <td>{{ $salida->grados }}</td>
                <td class="label">Lugar</td>
                <td>{{ $salida->lugar }}</td>
            </tr>
            <tr>
                <td class="label">Responsable</td>
                <td>{{ $salida->responsable ? $salida->responsable->name : '(sin asignar)' }}</td>
                <td class="label">Fecha de solicitud</td>
                <td>{{ $salida->fecha_solicitud ? \Carbon\Carbon::parse($salida->fecha_solicitud)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha de salida</td>
                <td>{{ $salida->fecha_salida ? \Carbon\Carbon::parse($salida->fecha_salida)->format('d/m/Y H:i') : '-' }}</td>
                <td class="label">Fecha de regreso</td>
                <td>{{ $salida->fecha_regreso ? \Carbon\Carbon::parse($salida->fecha_regreso)->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Cantidad de pasajeros</td>
                <td>{{ $salida->cantidad_pasajeros }}</td>
                <td class="label">Cancela / Motivo</td>
                <td>{{ $salida->estado === 'Cancelada' && $salida->motivo_cancelacion ? $salida->motivo_cancelacion : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Calendario general</td>
                <td>{{ $salida->calendario_general ? 'Sí' : 'No' }}</td>
                <td class="label">Visita de inspección</td>
                <td>{{ $salida->visita_inspeccion ? 'Sí' : 'No' }}</td>
            </tr>
            <tr>
                <td class="label">Contacto del lugar</td>
                <td colspan="3">{{ $salida->contacto_lugar ?: '-' }}</td>
            </tr>
        </table>
    </div>

    @if($salida->detalles_inspeccion)
    <div class="section">
        <div class="section-title">Detalles de Inspección</div>
        <table class="info-table">
            <tr><td>{{ $salida->detalles_inspeccion }}</td></tr>
        </table>
    </div>
    @endif

    @if($salida->observaciones)
    <div class="section">
        <div class="section-title">Observaciones Generales</div>
        <table class="info-table">
            <tr><td>{!! nl2br(e($salida->observaciones)) !!}</td></tr>
        </table>
    </div>
    @endif

    <!-- Servicios -->
    <div class="section">
        <div class="section-title">Servicios y Confirmaciones</div>
        <table class="info-table">
            <tr>
                <td class="label">Transporte confirmado</td>
                <td>
                    {{ $salida->transporte_confirmado ? 'Sí' : 'No' }}
                    @if($salida->transporte_confirmado)
                        <br>Por: {{ $salida->transporteConfirmadoPor ? $salida->transporteConfirmadoPor->name : '-' }}
                        <br>{{ $salida->transporte_confirmado_at ? \Carbon\Carbon::parse($salida->transporte_confirmado_at)->format('d/m/Y H:i') : '' }}
                    @endif
                </td>
                <td class="label">Hora salida/regreso bus</td>
                <td>{{ $salida->hora_salida_bus ?: '-' }} / {{ $salida->hora_regreso_bus ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Requiere alimentación</td>
                <td>{{ $salida->requiere_alimentacion ? 'Sí' : 'No' }}</td>
                <td class="label">Alimentación confirmada</td>
                <td>
                    {{ $salida->alimentacion_confirmada ? 'Sí' : 'No' }}
                    @if($salida->alimentacion_confirmada)
                        <br>Por: {{ $salida->alimentacionConfirmadaPor ? $salida->alimentacionConfirmadaPor->name : '-' }}
                    @endif
                </td>
            </tr>
            @if($salida->requiere_alimentacion)
            <tr>
                <td class="label">Snacks / Almuerzos</td>
                <td>{{ $salida->cantidad_snacks ?: 0 }} / {{ $salida->cantidad_almuerzos ?: 0 }}</td>
                <td class="label">Hora entrega alimentos</td>
                <td>{{ $salida->hora_entrega_alimentos ? \Carbon\Carbon::parse($salida->hora_entrega_alimentos)->format('H:i') : '-' }}</td>
            </tr>
            @if($salida->menu_sugerido)
            <tr>
                <td class="label">Menú sugerido</td>
                <td colspan="3">{{ $salida->menu_sugerido }}</td>
            </tr>
            @endif
            @if($salida->observaciones_dieteticas)
            <tr>
                <td class="label">Observaciones dietéticas</td>
                <td colspan="3">{{ $salida->observaciones_dieteticas }}</td>
            </tr>
            @endif
            @endif
            <tr>
                <td class="label">Requiere enfermería</td>
                <td>{{ $salida->requiere_enfermeria ? 'Sí' : 'No' }}</td>
                <td class="label">Enfermería confirmada</td>
                <td>
                    {{ $salida->enfermeria_confirmada ? 'Sí' : 'No' }}
                    @if($salida->enfermeria_confirmada)
                        <br>Por: {{ $salida->enfermeriaConfirmadaPor ? $salida->enfermeriaConfirmadaPor->name : '-' }}
                    @endif
                </td>
            </tr>
            @if($salida->observaciones_medicas)
            <tr>
                <td class="label">Observaciones médicas</td>
                <td colspan="3">{{ $salida->observaciones_medicas }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Requiere comunicaciones</td>
                <td>{{ $salida->requiere_comunicaciones ? 'Sí' : 'No' }}</td>
                <td class="label">Comunicaciones confirmada</td>
                <td>
                    {{ $salida->comunicaciones_confirmada ? 'Sí' : 'No' }}
                    @if($salida->comunicaciones_confirmada)
                        <br>Por: {{ $salida->comunicacionesConfirmadoPor ? $salida->comunicacionesConfirmadoPor->name : '-' }}
                    @endif
                </td>
            </tr>
            @if($salida->observaciones_comunicaciones)
            <tr>
                <td class="label">Observaciones comunicaciones</td>
                <td colspan="3">{{ $salida->observaciones_comunicaciones }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Hora de apertura de puertas</td>
                <td>{{ $salida->hora_apertura_puertas ? \Carbon\Carbon::parse($salida->hora_apertura_puertas)->format('H:i') : '-' }}</td>
                <td class="label">Accesos confirmados</td>
                <td>
                    {{ $salida->accesos_confirmados ? 'Sí' : 'No' }}
                    @if($salida->accesos_confirmados)
                        <br>Por: {{ $salida->accesosConfirmadosPor ? $salida->accesosConfirmadosPor->name : '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Requiere ARL</td>
                <td>{{ $salida->requiere_arl ? 'Sí' : 'No' }}</td>
                <td class="label">ARL confirmado</td>
                <td>
                    {{ $salida->arl_confirmado ? 'Sí' : 'No' }}
                    @if($salida->arl_confirmado)
                        <br>Por: {{ $salida->arlConfirmadoPor ? $salida->arlConfirmadoPor->name : '-' }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Historial de Auditoría -->
    <div class="section">
        <div class="section-title">Registro de Auditoría (Historial de la Base de Datos)</div>
        @if($salida->history && $salida->history->count() > 0)
            <table class="historial-table">
                <thead>
                    <tr>
                        <th style="width: 12%">Fecha/Hora</th>
                        <th style="width: 14%">Acción</th>
                        <th style="width: 16%">Usuario</th>
                        <th style="width: 12%">IP</th>
                        <th>Detalle / Cambios</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salida->history as $entry)
                        <tr>
                            <td style="white-space: nowrap;">{{ $entry->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @php
                                    $badgeClass = match($entry->action) {
                                        'created' => 'badge-created',
                                        'updated' => 'badge-updated',
                                        'manual_edit' => 'badge-manual_edit',
                                        'deleted' => 'badge-deleted',
                                        default => 'badge-created'
                                    };
                                    $actionLabel = match($entry->action) {
                                        'created' => 'Creación',
                                        'updated' => 'Actualización',
                                        'manual_edit' => 'Edición manual',
                                        'deleted' => 'Eliminación',
                                        default => ucfirst($entry->action)
                                    };
                                @endphp
                                <span class="badge-action {{ $badgeClass }}">{{ $actionLabel }}</span>
                            </td>
                            <td>{{ $entry->user ? $entry->user->name : 'Sistema' }}</td>
                            <td>{{ $entry->ip_address ?: '-' }}</td>
                            <td>
                                @if($entry->notes)
                                    <div><strong>{{ $entry->notes }}</strong></div>
                                @endif
                                @if($entry->action === 'created')
                                    <div class="change-field" style="margin-top:2px;">Datos adicionados en la creación:</div>
                                    <div class="change-detail">Consecutivo: <strong>{{ $salida->consecutivo }}</strong></div>
                                    <div class="change-detail">Grados: {{ $salida->grados }}</div>
                                    <div class="change-detail">Lugar: {{ $salida->lugar }}</div>
                                    <div class="change-detail">Responsable: {{ $salida->responsable ? $salida->responsable->name : '-' }}</div>
                                    <div class="change-detail">Fecha de salida: {{ $salida->fecha_salida ? \Carbon\Carbon::parse($salida->fecha_salida)->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="change-detail">Fecha de regreso: {{ $salida->fecha_regreso ? \Carbon\Carbon::parse($salida->fecha_regreso)->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="change-detail">Cantidad de pasajeros: {{ $salida->cantidad_pasajeros }}</div>
                                    <div class="change-detail">Estado inicial: {{ $salida->estado }}</div>
                                @elseif($entry->changes && is_array($entry->changes) && !empty($entry->changes))
                                    @if($entry->action === 'manual_edit')
                                        <div class="change-field" style="margin-top:2px;">Campos editados:</div>
                                    @else
                                        <div class="change-field" style="margin-top:2px;">Campos modificados:</div>
                                    @endif
                                    @foreach($entry->changes as $field => $change)
                                        @if(isset($change['old']) && isset($change['new']) && $change['old'] != $change['new'])
                                            @php
                                                $fieldLabels = [
                                                    'grados' => 'Grados',
                                                    'lugar' => 'Lugar',
                                                    'responsable_id' => 'Responsable',
                                                    'fecha_salida' => 'Fecha de salida',
                                                    'fecha_regreso' => 'Fecha de regreso',
                                                    'cantidad_pasajeros' => 'Cantidad de pasajeros',
                                                    'observaciones' => 'Observaciones',
                                                    'calendario_general' => 'Calendario general',
                                                    'visita_inspeccion' => 'Visita de inspección',
                                                    'detalles_inspeccion' => 'Detalles de inspección',
                                                    'contacto_lugar' => 'Contacto del lugar',
                                                    'requiere_alimentacion' => 'Requiere alimentación',
                                                    'cantidad_snacks' => 'Cantidad de snacks',
                                                    'cantidad_almuerzos' => 'Cantidad de almuerzos',
                                                    'hora_entrega_alimentos' => 'Hora de entrega de alimentos',
                                                    'menu_sugerido' => 'Menú sugerido',
                                                    'observaciones_dieteticas' => 'Observaciones dietéticas',
                                                    'hora_apertura_puertas' => 'Hora de apertura de puertas',
                                                    'requiere_enfermeria' => 'Requiere enfermería',
                                                    'requiere_comunicaciones' => 'Requiere comunicaciones',
                                                    'requiere_arl' => 'Requiere ARL',
                                                    'observaciones_comunicaciones' => 'Observaciones de comunicaciones',
                                                    'estado' => 'Estado',
                                                ];
                                                $fieldName = $fieldLabels[$field] ?? $field;
                                                $oldValue = $change['old'];
                                                $newValue = $change['new'];

                                                if (in_array($field, ['calendario_general', 'visita_inspeccion', 'requiere_alimentacion', 'requiere_enfermeria', 'requiere_comunicaciones', 'requiere_arl'])) {
                                                    $oldValue = $oldValue ? 'Sí' : 'No';
                                                    $newValue = $newValue ? 'Sí' : 'No';
                                                }

                                                if (in_array($field, ['fecha_salida', 'fecha_regreso'])) {
                                                    try {
                                                        $oldValue = $oldValue ? \Carbon\Carbon::parse($oldValue)->format('d/m/Y H:i') : '(vacío)';
                                                        $newValue = $newValue ? \Carbon\Carbon::parse($newValue)->format('d/m/Y H:i') : '(vacío)';
                                                    } catch (\Exception $e) {}
                                                }

                                                if (in_array($field, ['hora_entrega_alimentos', 'hora_apertura_puertas'])) {
                                                    try {
                                                        $oldValue = $oldValue ? \Carbon\Carbon::parse($oldValue)->format('H:i') : '(vacío)';
                                                        $newValue = $newValue ? \Carbon\Carbon::parse($newValue)->format('H:i') : '(vacío)';
                                                    } catch (\Exception $e) {}
                                                }

                                                if ($field === 'responsable_id') {
                                                    if ($oldValue) {
                                                        $u = \App\Models\User::find($oldValue);
                                                        $oldValue = $u ? $u->name : "ID {$oldValue}";
                                                    }
                                                    if ($newValue) {
                                                        $u2 = \App\Models\User::find($newValue);
                                                        $newValue = $u2 ? $u2->name : "ID {$newValue}";
                                                    }
                                                }

                                                $oldValue = (is_null($oldValue) || $oldValue === '') ? '(vacío)' : $oldValue;
                                                $newValue = (is_null($newValue) || $newValue === '') ? '(vacío)' : $newValue;
                                            @endphp
                                            <div class="change-detail">
                                                <span class="change-field">{{ $fieldName }}:</span>
                                                {{ $oldValue }} <strong>→</strong> {{ $newValue }}
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="empty">Sin cambios detallados</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <span class="empty">No hay registros de auditoría para esta salida.</span>
        @endif
    </div>

    <div class="footer">
        Informe generado automáticamente desde el Sistema de Intranet TVS - Salidas Pedagógicas.
        Este documento corresponde a la salida {{ $salida->consecutivo }} (ID {{ $salida->id }}).
    </div>
</body>
</html>
