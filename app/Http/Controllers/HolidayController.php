<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\SchoolCycle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $holidays = Holiday::orderBy('date')->get();
        return view('holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('holidays.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $holiday = Holiday::create($validated);
        
        // Recalcular los días de ciclo después de la fecha del día festivo
        $this->recalculateCycleDays(Carbon::parse($holiday->date));
        
        return redirect()->route('holidays.index')
            ->with('success', 'Día festivo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        return view('holidays.show', compact('holiday'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        return view('holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $oldDate = $holiday->date;
        $holiday->update($validated);
        
        // Si cambió la fecha, recalcular los días de ciclo desde la fecha anterior o la nueva, la que sea menor
        if ($oldDate != $holiday->date) {
            $recalculateDate = Carbon::parse($oldDate)->min(Carbon::parse($holiday->date));
            $this->recalculateCycleDays($recalculateDate);
        }
        
        return redirect()->route('holidays.index')
            ->with('success', 'Día festivo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $deletedDate = Carbon::parse($holiday->date);
        $holiday->delete();
        
        // Recalcular los días de ciclo después de la fecha del día festivo eliminado
        $this->recalculateCycleDays($deletedDate);
        
        return redirect()->route('holidays.index')
            ->with('success', 'Día festivo eliminado exitosamente.');
    }

    /**
     * Muestra el formulario para importar días festivos.
     */
    public function importForm()
    {
        return view('holidays.import');
    }

    /**
     * Importa días festivos desde un archivo.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // 5MB máximo
        ]);

        try {
            $file = $request->file('file');
            $minDate = null;

            // Leer el archivo
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Verificar que el archivo tenga encabezados
            $headers = array_shift($rows); // Extraer primera fila como encabezados
            $requiredColumns = ['date', 'name'];
            
            // Verificar que existan las columnas requeridas
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $headers)) {
                    return redirect()->back()->with('error', "El archivo no tiene la columna requerida: {$column}");
                }
            }

            // Mapear las columnas a sus índices
            $columnIndexes = [];
            foreach ($headers as $index => $header) {
                $columnIndexes[strtolower(trim($header))] = $index;
            }

            // Iniciar una transacción para asegurar la integridad de los datos
            DB::beginTransaction();
            
            // Contador de registros procesados
            $created = 0;
            $updated = 0;
            $errors = 0;
            
            foreach ($rows as $rowIndex => $row) {
                try {
                    // Extraer datos de las columnas correspondientes
                    $dateValue = trim($row[$columnIndexes['date']]);
                    $nameValue = trim($row[$columnIndexes['name']]);
                    $descriptionValue = isset($columnIndexes['description']) ? trim($row[$columnIndexes['description']]) : null;
                    
                    // Validar que los campos requeridos no estén vacíos
                    if (empty($dateValue) || empty($nameValue)) {
                        $errors++;
                        continue; // Saltar esta fila
                    }
                    
                    // Convertir y validar la fecha
                    try {
                        $date = Carbon::parse($dateValue)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $errors++;
                        continue; // Saltar esta fila si la fecha no es válida
                    }
                    
                    // Actualizar la fecha mínima para recalcular los días de ciclo
                    if ($minDate === null || Carbon::parse($date)->lt($minDate)) {
                        $minDate = Carbon::parse($date);
                    }
                    
                    // Buscar si ya existe un día festivo para esta fecha
                    $holiday = Holiday::where('date', $date)->first();
                    
                    if ($holiday) {
                        // Actualizar día festivo existente
                        $holiday->update([
                            'name' => $nameValue,
                            'description' => $descriptionValue,
                        ]);
                        $updated++;
                    } else {
                        // Crear nuevo día festivo
                        Holiday::create([
                            'date' => $date,
                            'name' => $nameValue,
                            'description' => $descriptionValue,
                        ]);
                        $created++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    // Continuar con la siguiente fila
                }
            }
            
            // Si hay una fecha mínima, recalcular los días de ciclo
            if ($minDate) {
                $this->recalculateCycleDays($minDate);
            }
            
            DB::commit();
            
            $message = "Importación finalizada: {$created} días festivos nuevos, {$updated} actualizados";
            if ($errors > 0) {
                $message .= ", {$errors} filas con errores";
            }
            
            return redirect()->route('holidays.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al importar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Recalcula los días de ciclo a partir de una fecha específica.
     */
    private function recalculateCycleDays(Carbon $fromDate)
    {
        // Obtener todos los ciclos escolares activos
        $schoolCycles = SchoolCycle::where('active', true)->get();
        
        foreach ($schoolCycles as $schoolCycle) {
            // Eliminar los días de ciclo desde la fecha especificada
            $schoolCycle->cycleDays()->where('date', '>=', $fromDate->format('Y-m-d'))->delete();
            
            // Obtener el último día de ciclo antes de la fecha especificada
            $lastCycleDay = $schoolCycle->cycleDays()
                ->where('date', '<', $fromDate->format('Y-m-d'))
                ->orderBy('date', 'desc')
                ->first();
            
            if ($lastCycleDay) {
                // Continuar la generación desde el último día conocido
                $startDate = Carbon::parse($lastCycleDay->date)->addDay();
                $cycleDay = $lastCycleDay->cycle_day;
                
                // Avanzar el contador del día de ciclo
                $cycleDay++;
                if ($cycleDay > $schoolCycle->cycle_length) {
                    $cycleDay = 1;
                }
            } else {
                // No hay días de ciclo previos, comenzar desde el inicio
                $startDate = Carbon::parse($schoolCycle->start_date);
                $cycleDay = 1;
            }
            
            // Generar los días de ciclo hasta un año después de la fecha actual
            $endDate = Carbon::now()->addYear();
            
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Saltar fines de semana (sábado y domingo)
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Verificar si es un día festivo
                $isHoliday = Holiday::where('date', $currentDate->format('Y-m-d'))->exists();
                if ($isHoliday) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Crear el día de ciclo
                $schoolCycle->cycleDays()->create([
                    'date' => $currentDate->format('Y-m-d'),
                    'cycle_day' => $cycleDay,
                ]);
                
                // Incrementar el contador del día de ciclo
                $cycleDay++;
                if ($cycleDay > $schoolCycle->cycle_length) {
                    $cycleDay = 1;
                }
                
                $currentDate->addDay();
            }
        }
    }
}
