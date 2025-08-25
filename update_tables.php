<?php

// Script temporal para actualizar todas las tablas de secciones

$viewFile = 'c:\\xampp\\htdocs\\tvs\\resources\\views\\presupuesto\\spreadsheet.blade.php';
$content = file_get_contents($viewFile);

// Patrones y reemplazos para cada sección
$replacements = [
    // PEP
    [
        'search' => '@php
                                            $seccion = \'PEP\';
                                            $pepConceptos = $seccionesData[$seccion] ?? [];
                                        @endphp
                                        @foreach($pepConceptos as $concepto => $valorEjecucion)
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="pep" data-concept="{{ $concepto }}" data-type="presupuesto">0</td>
                                                <td class="number-cell editable" data-section="pep" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($valorEjecucion, 0, \',\', \'.\') }}</td>
                                                <td class="number-cell calculated {{ $valorEjecucion > 0 ? \'negative\' : \'\' }}">{{ number_format(0 - $valorEjecucion, 0, \',\', \'.\') }}</td>
                                            </tr>
                                        @endforeach',
        'replace' => '@php
                                            $seccion = \'PEP\';
                                            $pepConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($pepConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos[\'presupuesto\'] ?? 0;
                                                $ejecucion = $datos[\'ejecutado\'] ?? 0;
                                                $saldo = $datos[\'saldo\'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="pep" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, \',\', \'.\') }}</td>
                                                <td class="number-cell editable" data-section="pep" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, \',\', \'.\') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? \'negative\' : \'\' }}">{{ number_format($saldo, 0, \',\', \'.\') }}</td>
                                            </tr>
                                        @endforeach'
    ],
    // DEPORTES
    [
        'search' => '@php
                                            $seccion = \'DEPORTES\';
                                            $deportesConceptos = $seccionesData[$seccion] ?? [];
                                        @endphp
                                        @foreach($deportesConceptos as $concepto => $valorEjecucion)
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="deportes" data-concept="{{ $concepto }}" data-type="presupuesto">0</td>
                                                <td class="number-cell editable" data-section="deportes" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($valorEjecucion, 0, \',\', \'.\') }}</td>
                                                <td class="number-cell calculated {{ $valorEjecucion > 0 ? \'negative\' : \'\' }}">{{ number_format(0 - $valorEjecucion, 0, \',\', \'.\') }}</td>
                                            </tr>
                                        @endforeach',
        'replace' => '@php
                                            $seccion = \'DEPORTES\';
                                            $deportesConceptos = $seccionesData[$seccion] ?? [];
                                            $totalPresupuesto = 0;
                                            $totalEjecucion = 0;
                                            $totalSaldo = 0;
                                        @endphp
                                        @foreach($deportesConceptos as $concepto => $datos)
                                            @php
                                                $presupuesto = $datos[\'presupuesto\'] ?? 0;
                                                $ejecucion = $datos[\'ejecutado\'] ?? 0;
                                                $saldo = $datos[\'saldo\'] ?? 0;
                                                $totalPresupuesto += $presupuesto;
                                                $totalEjecucion += $ejecucion;
                                                $totalSaldo += $saldo;
                                            @endphp
                                            <tr>
                                                <td>{{ $concepto }}</td>
                                                <td class="number-cell editable" data-section="deportes" data-concept="{{ $concepto }}" data-type="presupuesto">{{ number_format($presupuesto, 0, \',\', \'.\') }}</td>
                                                <td class="number-cell editable" data-section="deportes" data-concept="{{ $concepto }}" data-type="ejecucion">{{ number_format($ejecucion, 0, \',\', \'.\') }}</td>
                                                <td class="number-cell calculated {{ $saldo < 0 ? \'negative\' : \'\' }}">{{ number_format($saldo, 0, \',\', \'.\') }}</td>
                                            </tr>
                                        @endforeach'
    ]
];

echo "Iniciando actualizaciones...\n";

foreach ($replacements as $index => $replacement) {
    if (strpos($content, $replacement['search']) !== false) {
        $content = str_replace($replacement['search'], $replacement['replace'], $content);
        echo "Actualización " . ($index + 1) . " aplicada.\n";
    } else {
        echo "Patrón " . ($index + 1) . " no encontrado.\n";
    }
}

echo "Guardando archivo...\n";
file_put_contents($viewFile, $content);
echo "Actualización completada.\n";
