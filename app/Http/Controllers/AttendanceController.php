<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Exports\AttendanceExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function showUploadForm()
    {
        return view('attendance.upload');
    }

    public function importData(Request $request)
    {
        try {
            \DB::beginTransaction();

            $request->validate([
                'mes' => 'required|string',
                'datos' => 'required|string'
            ]);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            $rows = preg_split('/\r\n|\r|\n/', $request->datos);
            foreach ($rows as $index => $row) {
                if (trim($row) === '') continue;

                $columns = $this->splitAttendanceRow($row);

                if ($this->looksLikeAttendanceHeader($columns)) {
                    continue;
                }

                try {
                    $data = $this->parseAttendanceRow($columns);
                    $data['mes'] = $request->mes;

                    $record = AttendanceRecord::updateOrCreate(
                        [
                            'no_id' => $data['no_id'],
                            'fecha' => $data['fecha'],
                            'mes' => $data['mes'],
                        ],
                        $data
                    );

                    if ($record) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Error al crear registro (fila " . ($index + 1) . ")";
                    }
                } catch (\InvalidArgumentException $e) {
                    $errorCount++;
                    $errors[] = "Fila " . ($index + 1) . ": " . $e->getMessage();
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error en fila " . ($index + 1) . ": " . $e->getMessage();
                    \Log::error('Error importando registro: ' . $e->getMessage());
                }
            }

            \DB::commit();

            $message = "Registros importados: {$successCount}. Errores: {$errorCount}";
            if (count($errors) > 0) {
                $message .= "\nPrimeros errores:\n" . implode("\n", array_slice($errors, 0, 15));
                if (count($errors) > 15) {
                    $message .= "\n... y " . (count($errors) - 15) . " errores más (ver log).";
                }
                \Log::warning('Importación biométrica con errores: ' . $message);
            }

            \Log::info($message);
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error general en importación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al importar datos: ' . $e->getMessage());
        }
    }

    private function splitAttendanceRow(string $row): array
    {
        $row = str_replace(["\xC2\xA0", "\r", "\n"], [' ', '', ''], $row);
        $row = trim($row);

        if ($row === '') {
            return [];
        }

        $columns = str_contains($row, "\t")
            ? explode("\t", $row)
            : preg_split('/\s{2,}/', $row);

        $columns = array_map(fn ($column) => trim((string) $column), $columns ?: []);

        while (count($columns) > 0 && reset($columns) === '') {
            array_shift($columns);
        }

        while (count($columns) > 0 && end($columns) === '') {
            array_pop($columns);
        }

        return array_values($columns);
    }

    private function looksLikeAttendanceHeader(array $columns): bool
    {
        $text = mb_strtolower(implode(' ', $columns));
        $keywords = ['documento', 'nombre', 'fecha', 'entrada', 'salida', 'departamento'];

        $matches = 0;
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $matches++;
            }
        }

        return $matches >= 3;
    }

    private function parseAttendanceRow(array $columns): array
    {
        $dateIndex = null;
        $dateValue = null;
        $combinedTime = null;

        foreach ([false, true] as $allowExcelSerial) {
            foreach ($columns as $index => $column) {
                $parsedDate = $this->parseAttendanceDate((string) $column, $allowExcelSerial);

                if ($parsedDate !== null) {
                    $dateIndex = $index;
                    $dateValue = $parsedDate[0];
                    $combinedTime = $parsedDate[1];
                    break 2;
                }
            }
        }

        if ($dateIndex === null) {
            throw new \InvalidArgumentException('no se encontró una fecha válida');
        }

        if ($dateIndex < 2) {
            throw new \InvalidArgumentException('faltan las columnas de documento y nombre antes de la fecha');
        }

        $noId = trim((string) ($columns[$dateIndex - 2] ?? ''));
        $name = trim((string) ($columns[$dateIndex - 1] ?? ''));

        if ($noId === '' || $name === '') {
            throw new \InvalidArgumentException('documento o nombre vacío');
        }

        $remaining = array_values(array_slice($columns, $dateIndex + 1));
        $remaining = array_map(fn ($column) => trim((string) $column), $remaining);

        while (count($remaining) > 0 && end($remaining) === '') {
            array_pop($remaining);
        }

        $entrada = null;
        $salida = null;
        $departamento = null;

        if ($combinedTime !== null) {
            if (count($remaining) >= 3) {
                $entrada = $this->normalizeAttendanceTime($remaining[0]) ?: $combinedTime;
                $salida = $this->normalizeAttendanceTime($remaining[1]);
                $departamento = $remaining[2];
            } elseif (count($remaining) === 2) {
                $entrada = $combinedTime;
                $salida = $this->normalizeAttendanceTime($remaining[0]);
                $departamento = $remaining[1];
            } elseif (count($remaining) === 1) {
                $entrada = $combinedTime;
                $departamento = $remaining[0];
            } else {
                $entrada = $combinedTime;
            }
        } else {
            if (count($remaining) >= 3) {
                $entrada = $this->normalizeAttendanceTime($remaining[0]);
                $salida = $this->normalizeAttendanceTime($remaining[1]);
                $departamento = $remaining[2];
            } elseif (count($remaining) === 2) {
                $entrada = $this->normalizeAttendanceTime($remaining[0]);
                $departamento = $remaining[1];
            } elseif (count($remaining) === 1) {
                $departamento = $remaining[0];
            }
        }

        $departamento = trim((string) $departamento);

        if ($departamento === '') {
            throw new \InvalidArgumentException('departamento vacío');
        }

        return [
            'no_id' => $noId,
            'nombre_apellidos' => $name,
            'fecha' => $dateValue,
            'entrada' => $entrada,
            'salida' => $salida,
            'departamento' => $departamento,
        ];
    }

    private function parseAttendanceDate(string $value, bool $allowExcelSerial = false): ?array
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $timePart = null;

        if (is_numeric($value)) {
            if (!$allowExcelSerial) {
                return null;
            }

            $numericValue = (float) $value;

            if ($numericValue < 43831 || $numericValue > 73051) {
                return null;
            }

            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue);
                $excelTime = $numericValue - floor($numericValue);
                $timePart = $excelTime > 0 ? $this->normalizeAttendanceTime($excelTime) : null;

                return [$date->format('Y-m-d'), $timePart];
            } catch (\Exception $e) {
                return null;
            }
        }

        if (preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)/', $value, $matches)) {
            $timePart = $this->normalizeAttendanceTime($matches[1]);
            $value = trim(str_replace($matches[1], ' ', $value));
        }

        $value = trim(preg_replace('/\s+/', ' ', $value));

        if (!preg_match('/\d/', $value)) {
            return null;
        }

        $value = str_replace(['.', '-'], '/', $value);
        $formats = ['d/m/Y', 'd/m/y', 'Y/m/d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            $lastErrors = \DateTime::getLastErrors();

            if ($date instanceof \DateTime && ($lastErrors === false || ($lastErrors['warning_count'] === 0 && $lastErrors['error_count'] === 0))) {
                return [$date->format('Y-m-d'), $timePart];
            }
        }

        try {
            return [Carbon::parse($value)->format('Y-m-d'), $timePart];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizeAttendanceTime($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $numericValue = (float) $value;

            if ($numericValue >= 0 && $numericValue < 1) {
                $seconds = (int) round($numericValue * 86400);
                return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
            }

            if ($numericValue >= 1 && $numericValue < 100000) {
                $fraction = $numericValue - floor($numericValue);
                $seconds = (int) round($fraction * 86400);
                return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
            }
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(['a. m.', 'a.m.', 'p. m.', 'p.m.'], ['AM', 'AM', 'PM', 'PM'], $value);
        $value = str_replace('.', ':', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));

        if (preg_match('/^\d{3,4}$/', $value)) {
            $value = str_pad($value, 4, '0', STR_PAD_LEFT);
            $value = substr($value, 0, 2) . ':' . substr($value, 2, 2);
        }

        $formats = ['H:i:s', 'H:i', 'G:i', 'g:i A', 'g:iA', 'h:i A', 'h:iA', 'H:i:s A', 'H:i A'];

        foreach ($formats as $format) {
            $time = \DateTime::createFromFormat('!' . $format, $value);
            $lastErrors = \DateTime::getLastErrors();

            if ($time instanceof \DateTime && ($lastErrors === false || ($lastErrors['warning_count'] === 0 && $lastErrors['error_count'] === 0))) {
                return $time->format('H:i:s');
            }
        }

        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function dashboard($mes = 'actual')
    {
        $mes = $this->resolveMonth($mes);

        try {
            $records = AttendanceRecord::where('mes', $mes)->get();
            
            if ($records->isEmpty()) {
                return view('attendance.dashboard', [
                    'records' => collect(),
                    'totalEmployees' => 0,
                    'lateArrivalsCount' => 0,
                    'lateArrivalsHours' => [],
                    'absences' => ['total' => 0, 'byDepartment' => []],
                    'averageAttendance' => 0,
                    'departmentStats' => collect(),
                    'weeklyTrends' => collect(),
                    'mes' => $mes,
                    'retrasosPorDepartamento' => [],
                    'retrasos700710' => [],
                    'retrasos710720' => [],
                    'retrasos720Plus' => []
                ]);
            }

            // Estadísticas básicas
            $totalEmployees = $records->unique('no_id')->count();
            $totalDays = $records->unique('fecha')->count();
            
            // Análisis de llegadas tarde
            $lateArrivalsData = $this->analyzeLateArrivals($records);
            $lateArrivalsCount = $lateArrivalsData['total'];
            $lateArrivalsHours = $lateArrivalsData['byHour'];
            
            // Análisis de ausencias
            $absencesData = $this->analyzeAbsences($records);
            $absences = [
                'total' => $absencesData['total'],
                'byDepartment' => $absencesData['byDepartment']
            ];
            
            // Calcular promedio de asistencia
            $totalAsistencias = $records->count();
            $averageAttendance = $totalAsistencias > 0 
                ? round((($totalAsistencias - $absences['total']) / $totalAsistencias) * 100, 2)
                : 0;
            
            // Análisis por departamento
            $departmentStats = $this->analyzeDepartments($records);
            
            // Tendencia semanal
            $weeklyTrends = $this->analyzeWeeklyTrends($records);

            // Obtener estadísticas de retrasos dinámicamente
            $delayStats = $this->analyzeDelaysByDepartment($records);
            $retrasosPorDepartamento = $delayStats['retrasosPorDepartamento'];
            $retrasos700710 = $delayStats['retrasos700710'];
            $retrasos710720 = $delayStats['retrasos710720'];
            $retrasos720Plus = $delayStats['retrasos720Plus'];

            return view('attendance.dashboard', compact(
                'records',
                'totalEmployees',
                'totalDays',
                'lateArrivalsCount',
                'lateArrivalsHours',
                'absences',
                'averageAttendance',
                'departmentStats',
                'weeklyTrends',
                'mes',
                'retrasosPorDepartamento',
                'retrasos700710',
                'retrasos710720',
                'retrasos720Plus'
            ));

        } catch (\Exception $e) {
            \Log::error('Error en dashboard: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los datos del dashboard');
        }
    }

    private function analyzeLateArrivals($records)
    {
        $lateArrivalsCount = $records->filter(function($record) {
            if (empty($record->entrada)) return false;
            try {
                $entrada = Carbon::parse($record->entrada);
                return $entrada->format('H:i') > '07:00';
            } catch (\Exception $e) {
                return false;
            }
        })->count();

        $lateByHour = $records->filter(function($record) {
            if (empty($record->entrada)) return false;
            try {
                $entrada = Carbon::parse($record->entrada);
                return $entrada->format('H:i') > '07:00';
            } catch (\Exception $e) {
                return false;
            }
        })->groupBy(function($record) {
            return Carbon::parse($record->entrada)->format('H');
        })->map->count();

        return [
            'total' => $lateArrivalsCount,
            'byHour' => $lateByHour
        ];
    }

    private function analyzeAbsences($records)
    {
        $absencesCount = $records->filter(function($record) {
            return empty($record->entrada);
        })->count();

        $absencesByDepartment = $records->filter(function($record) {
            return empty($record->entrada);
        })->groupBy('departamento')->map->count();

        return [
            'total' => $absencesCount,
            'byDepartment' => $absencesByDepartment
        ];
    }

    private function analyzeDepartments($records)
    {
        return $records->groupBy('departamento')->map(function($deptRecords) {
            $total = $deptRecords->count();
            $lateCount = $deptRecords->filter(function($record) {
                if (empty($record->entrada)) return false;
                try {
                    $entrada = Carbon::parse($record->entrada);
                    return $entrada->format('H:i') > '07:00';
                } catch (\Exception $e) {
                    return false;
                }
            })->count();

            return [
                'total' => $total,
                'onTime' => $total - $lateCount,
                'late' => $lateCount,
                'absences' => $deptRecords->filter(function($record) {
                    return empty($record->entrada);
                })->count(),
                'attendanceRate' => $total > 0 ? round((($total - $lateCount) / $total) * 100, 2) : 0
            ];
        });
    }

    private function analyzeWeeklyTrends($records)
    {
        return $records->groupBy(function($record) {
            return Carbon::parse($record->fecha)->format('N'); // 1 (Monday) through 7 (Sunday)
        })->map(function($dayRecords) {
            $total = $dayRecords->count();
            $lateCount = $dayRecords->filter(function($record) {
                if (empty($record->entrada)) return false;
                try {
                    $entrada = Carbon::parse($record->entrada);
                    return $entrada->format('H:i') > '07:00';
                } catch (\Exception $e) {
                    return false;
                }
            })->count();

            return [
                'total' => $total,
                'onTime' => $total - $lateCount,
                'late' => $lateCount
            ];
        })->sortKeys();
    }

    private function analyzeDelaysByDepartment($records)
    {
        $departments = $records->pluck('departamento')->unique();
        $retrasosPorDepartamento = [];
        $retrasos700710 = [];
        $retrasos710720 = [];
        $retrasos720Plus = [];
        
        $totalRetrasos = [
            'total' => 0,
            '700710' => 0,
            '710720' => 0,
            '720plus' => 0
        ];

        foreach ($departments as $dept) {
            $deptRecords = $records->where('departamento', $dept);
            
            $retrasos = [
                '700710' => 0,
                '710720' => 0,
                '720plus' => 0
            ];

            foreach ($deptRecords as $record) {
                if (!empty($record->entrada)) {
                    try {
                        $entrada = Carbon::parse($record->entrada);
                        $hora = $entrada->format('H:i');
                        
                        if ($hora > '07:00') {
                            if ($hora <= '07:10') {
                                $retrasos['700710']++;
                                $totalRetrasos['700710']++;
                            } elseif ($hora <= '07:20') {
                                $retrasos['710720']++;
                                $totalRetrasos['710720']++;
                            } else {
                                $retrasos['720plus']++;
                                $totalRetrasos['720plus']++;
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            $totalDeptRetrasos = $retrasos['700710'] + $retrasos['710720'] + $retrasos['720plus'];
            
            $retrasosPorDepartamento[] = [
                'Departamento' => $dept,
                'Retrasos' => $totalDeptRetrasos
            ];
            
            $retrasos700710[] = [
                'Departamento' => $dept,
                'Retrasos' => $retrasos['700710']
            ];
            
            $retrasos710720[] = [
                'Departamento' => $dept,
                'Retrasos' => $retrasos['710720']
            ];
            
            $retrasos720Plus[] = [
                'Departamento' => $dept,
                'Retrasos' => $retrasos['720plus']
            ];
        }

        // Agregar totales
        $totalGeneral = $totalRetrasos['700710'] + $totalRetrasos['710720'] + $totalRetrasos['720plus'];
        
        $retrasosPorDepartamento[] = ['Departamento' => 'Total', 'Retrasos' => $totalGeneral];
        $retrasos700710[] = ['Departamento' => 'Total', 'Retrasos' => $totalRetrasos['700710']];
        $retrasos710720[] = ['Departamento' => 'Total', 'Retrasos' => $totalRetrasos['710720']];
        $retrasos720Plus[] = ['Departamento' => 'Total', 'Retrasos' => $totalRetrasos['720plus']];

        return [
            'retrasosPorDepartamento' => $retrasosPorDepartamento,
            'retrasos700710' => $retrasos700710,
            'retrasos710720' => $retrasos710720,
            'retrasos720Plus' => $retrasos720Plus
        ];
    }

    public function exportToExcel($mes = 'actual')
    {
        try {
            $mes = $this->resolveMonth($mes);

            $records = AttendanceRecord::where('mes', $mes)->get();
            
            if ($records->isEmpty()) {
                return redirect()->back()->with('error', 'No hay registros disponibles para exportar en el mes de ' . $mes);
            }

            $filename = 'asistencia_' . strtolower($mes) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(new AttendanceExport($records, $mes), $filename);

        } catch (\Exception $e) {
            \Log::error('Error al exportar asistencia: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los datos: ' . $e->getMessage());
        }
    }

    public function exportHtml($mes = 'actual')
    {
        try {
            $mes = $this->resolveMonth($mes);

            $records = AttendanceRecord::where('mes', $mes)->get();
            
            if ($records->isEmpty()) {
                return redirect()->back()->with('error', 'No hay registros disponibles para exportar en el mes de ' . $mes);
            }

            // Obtener estadísticas para el informe
            $totalEmployees = $records->unique('no_id')->count();
            $totalDays = $records->unique('fecha')->count();
            
            // Análisis de llegadas tarde
            $lateArrivalsData = $this->analyzeLateArrivals($records);
            $lateArrivalsCount = $lateArrivalsData['total'];
            
            // Análisis de ausencias
            $absencesData = $this->analyzeAbsences($records);
            $absences = [
                'total' => $absencesData['total'],
                'byDepartment' => $absencesData['byDepartment']
            ];
            
            // Calcular promedio de asistencia
            $totalAsistencias = $records->count();
            $averageAttendance = $totalAsistencias > 0 
                ? round((($totalAsistencias - $absences['total']) / $totalAsistencias) * 100, 2)
                : 0;
            
            // Análisis por departamento
            $departmentStats = $this->analyzeDepartments($records);

            // Calcular estadísticas adicionales
            $attendanceStats = [
                'total_registros' => $records->count(),
                'total_empleados' => $totalEmployees,
                'total_dias' => $totalDays,
                'llegadas_tarde' => $lateArrivalsCount,
                'ausencias_total' => $absences['total'],
                'promedio_asistencia' => $averageAttendance,
                'registros_puntuales' => $records->count() - $lateArrivalsCount,
                'promedio_diario' => $totalDays > 0 ? round($records->count() / $totalDays, 1) : 0
            ];

            $filename = 'Informe_Asistencia_' . ucfirst($mes) . '_' . date('Y-m-d') . '.html';

            // Generar HTML
            $html = view('attendance.export-html', [
                'attendanceStats' => $attendanceStats,
                'records' => $records,
                'departmentStats' => $departmentStats,
                'mes' => $mes,
                'mesTexto' => ucfirst($mes) . ' ' . date('Y')
            ])->render();

            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Error al exportar asistencia HTML: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los datos: ' . $e->getMessage());
        }
    }

    /**
     * Convierte 'actual' al mes actual en español
     */
    private function resolveMonth($mes)
    {
        if ($mes === 'actual') {
            $mesActual = Carbon::now()->format('F');
            $mesesEspanol = [
                'January' => 'Enero',
                'February' => 'Febrero', 
                'March' => 'Marzo',
                'April' => 'Abril',
                'May' => 'Mayo',
                'June' => 'Junio',
                'July' => 'Julio',
                'August' => 'Agosto',
                'September' => 'Septiembre',
                'October' => 'Octubre',
                'November' => 'Noviembre',
                'December' => 'Diciembre'
            ];
            return $mesesEspanol[$mesActual] ?? 'Enero';
        }
        
        return $mes;
    }
}