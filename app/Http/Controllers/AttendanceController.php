<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

            $rows = explode("\n", $request->datos);
            foreach ($rows as $index => $row) {
                if (empty(trim($row))) continue;
                
                $columns = explode("\t", $row);
                if (count($columns) === 6) {
                    try {
                        $fechaStr = trim($columns[2]);
                        
                        try {
                            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $fechaStr)) {
                                $fecha = Carbon::createFromFormat('d/m/Y', $fechaStr)->format('Y-m-d');
                            } else {
                                $fecha = Carbon::parse($fechaStr)->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            $errors[] = "Error en fecha (fila " . ($index + 1) . "): " . $fechaStr;
                            $errorCount++;
                            continue;
                        }

                        // Formatear hora de entrada
                        $entrada = trim($columns[3]);
                        if (!empty($entrada)) {
                            try {
                                // Convertir la hora al formato correcto
                                $entrada = Carbon::createFromFormat('H:i', $entrada)->format('H:i:s');
                            } catch (\Exception $e) {
                                try {
                                    $entrada = Carbon::parse($entrada)->format('H:i:s');
                                } catch (\Exception $e) {
                                    $entrada = null;
                                }
                            }
                        }

                        $data = [
                            'no_id' => trim($columns[0]),
                            'nombre_apellidos' => trim($columns[1]),
                            'fecha' => $fecha,
                            'entrada' => $entrada,
                            'salida' => trim($columns[4]) ?: null,
                            'departamento' => trim($columns[5]),
                            'mes' => $request->mes
                        ];

                        \Log::info("Intentando crear registro:", $data);
                        
                        $record = AttendanceRecord::create($data);
                        
                        if ($record) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Error al crear registro (fila " . ($index + 1) . ")";
                        }

                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Error en fila " . ($index + 1) . ": " . $e->getMessage();
                        \Log::error('Error importando registro: ' . $e->getMessage());
                        continue;
                    }
                } else {
                    $errorCount++;
                    $errors[] = "Formato incorrecto en fila " . ($index + 1) . ")";
                }
            }

            \DB::commit();
            
            $message = "Registros importados: {$successCount}. Errores: {$errorCount}";
            if (count($errors) > 0) {
                $message .= "\nDetalles de errores:\n" . implode("\n", $errors);
            }
            
            \Log::info($message);
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error general en importación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al importar datos: ' . $e->getMessage());
        }
    }

    public function dashboard($mes = 'actual')
    {
        if ($mes === 'actual') {
            $mes = 'Enero';
        }

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
}