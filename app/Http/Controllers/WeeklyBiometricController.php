<?php

namespace App\Http\Controllers;

use App\Models\WeeklyBiometricRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

class WeeklyBiometricController extends Controller
{
    public function index()
    {
        $months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        return view('weekly-biometric.index', compact('months'));
    }

    public function processData(Request $request)
    {
        $request->validate([
            'data' => 'required|string',
            'month' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $lines = explode("\n", $request->data);
        $records = [];

        foreach ($lines as $line) {
            $data = str_getcsv($line, "\t");
            if (count($data) >= 7) {
                $records[] = [
                    'employee_id' => $data[0],
                    'full_name' => $data[1],
                    'record_date' => Carbon::createFromFormat('d/m/Y', $data[2]),
                    'entry_time' => !empty($data[3]) ? $data[3] : null,
                    'exit_time' => !empty($data[4]) ? $data[4] : null,
                    'department' => $data[5],
                    'raw_marks' => $data[6],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        if (!empty($records)) {
            WeeklyBiometricRecord::insert($records);
        }

        return redirect()->route('weekly-biometric.dashboard')
            ->with('success', 'Datos procesados exitosamente');
    }

    public function dashboard(Request $request)
    {
        $months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        $query = WeeklyBiometricRecord::query();

        if ($request->filled('month')) {
            $query->whereMonth('record_date', array_search($request->month, $months) + 1);
        }

        if ($request->filled(['start_date', 'end_date'])) {
            $query->whereBetween('record_date', [$request->start_date, $request->end_date]);
        }

        // Calculate expected marks
        $expectedMarksPerDay = 2; // Entry and exit
        $daysInRange = 5; // Weekly workdays
        
        $stats = $query->select(
            'department',
            DB::raw('COUNT(DISTINCT employee_id) as total_employees'),
            DB::raw('COUNT(DISTINCT CASE WHEN entry_time IS NOT NULL THEN employee_id END) as present_count'),
            DB::raw('COUNT(DISTINCT CASE WHEN entry_time IS NULL THEN employee_id END) as absent_count'),
            DB::raw('COUNT(DISTINCT CASE WHEN TIME_TO_SEC(entry_time)/3600 > 7 THEN employee_id END) as late_count'),
            DB::raw('AVG(TIME_TO_SEC(entry_time)/3600) as avg_entry_time'),
            DB::raw('AVG(TIME_TO_SEC(exit_time)/3600) as avg_exit_time'),
            DB::raw('COUNT(*) as total_marks'),
            DB::raw('COUNT(CASE WHEN entry_time IS NOT NULL AND exit_time IS NOT NULL THEN 1 END) as complete_marks'),
            DB::raw('COUNT(CASE WHEN entry_time IS NOT NULL AND exit_time IS NULL THEN 1 END) as incomplete_marks')
        )
        ->groupBy('department')
        ->get()
        ->map(function($stat) use ($expectedMarksPerDay, $daysInRange) {
            $expectedTotalMarks = $stat->total_employees * $expectedMarksPerDay * $daysInRange;
            $stat->expected_marks = $expectedTotalMarks;
            $stat->marks_percentage = $expectedTotalMarks > 0 ? 
                ($stat->total_marks / $expectedTotalMarks) * 100 : 0;
            $stat->complete_percentage = $expectedTotalMarks > 0 ? 
                ($stat->complete_marks / $expectedTotalMarks) * 100 : 0;
            $stat->late_employees = $this->getLateEmployees($stat->department) ?? collect();
            return $stat;
        });

        // Get the department trends data
        $departmentStats = WeeklyBiometricRecord::select(
            'department',
            DB::raw('DATE(record_date) as date'),
            DB::raw('COUNT(DISTINCT employee_id) as total_employees'),
            DB::raw('COUNT(DISTINCT CASE WHEN entry_time IS NOT NULL THEN employee_id END) as present_count')
        )
        ->when($request->filled('month'), function($query) use ($request, $months) {
            $query->whereMonth('record_date', array_search($request->month, $months) + 1);
        })
        ->when($request->filled(['start_date', 'end_date']), function($query) use ($request) {
            $query->whereBetween('record_date', [$request->start_date, $request->end_date]);
        })
        ->groupBy('department', 'date')
        ->get()
        ->groupBy('department');

        // Get detailed daily statistics with pagination
        $dailyStats = $query->select(
            'department',
            DB::raw('DATE(record_date) as date'),
            DB::raw('COUNT(DISTINCT employee_id) as unique_employees'),
            DB::raw('COUNT(*) as total_marks'),
            DB::raw('COUNT(CASE WHEN entry_time IS NOT NULL AND exit_time IS NOT NULL THEN 1 END) as complete_marks'),
            DB::raw('COUNT(CASE WHEN entry_time IS NOT NULL AND exit_time IS NULL THEN 1 END) as partial_marks'),
            DB::raw('COUNT(CASE WHEN entry_time IS NULL AND exit_time IS NULL THEN 1 END) as missing_marks')
        )
        ->groupBy('department', 'date')
        ->orderBy('date', 'desc')
        ->paginate(10);

        return view('weekly-biometric.dashboard', compact('stats', 'months', 'departmentStats', 'dailyStats'));
    }

    public function lateDetails($department)
    {
        $lateEmployees = $this->getLateEmployees($department);
        return view('weekly-biometric.late-details', [
            'department' => $department,
            'employees' => $lateEmployees
        ]);
    }

    private function getLateEmployees($department)
    {
        return WeeklyBiometricRecord::select(
            'full_name as name',
            DB::raw('COUNT(*) as late_count'),
            DB::raw('GROUP_CONCAT(TIME_FORMAT(entry_time, "%H:%i") ORDER BY entry_time SEPARATOR ", ") as entry_times')
        )
        ->where('department', $department)
        ->whereNotNull('entry_time')
        ->whereRaw('TIME_TO_SEC(entry_time)/3600 > 7')
        ->groupBy('full_name')
        ->get()
        ->map(function($record) {
            return [
                'name' => $record->name,
                'late_count' => $record->late_count,
                'entry_times' => explode(', ', $record->entry_times)
            ];
        });
    }

    private function getStats()
    {
        // Implementa tu lógica actual de stats aquí
        return [
            (object)[
                'department' => 'Departamento 1',
                'total_employees' => 10,
                'expected_marks' => 20,
                'total_marks' => 18,
                'complete_marks' => 15,
                'incomplete_marks' => 3,
                'late_count' => 2,
                'absent_count' => 1,
                'marks_percentage' => 90,
                'complete_percentage' => 75
            ]
        ];
    }
}
