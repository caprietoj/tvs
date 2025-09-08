<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBlock;
use App\Models\SchoolCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipmentBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blocks = EquipmentBlock::with(['equipment', 'schoolCycle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('equipment.blocks.index', compact('blocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipments = Equipment::all();
        $schoolCycles = SchoolCycle::all();
        $activeSchoolCycle = SchoolCycle::where('active', true)->first();

        return view('equipment.blocks.create', compact('equipments', 'schoolCycles', 'activeSchoolCycle'));
    }

    /**
     * Store a newly created resource (cycle day blocks) in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'school_cycle_id' => 'required|exists:school_cycles,id',
            'cycle_days' => 'required|array|min:1',
            'cycle_days.*' => 'integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'blocked_units' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        // Verificar que el ciclo escolar tenga días con el número de ciclo especificado
        $schoolCycle = SchoolCycle::findOrFail($request->school_cycle_id);
        $maxCycleDay = max($request->cycle_days);
        
        if ($maxCycleDay > $schoolCycle->cycle_length) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cycle_days' => 'Los días de ciclo deben ser menores o iguales a la longitud del ciclo escolar (' . $schoolCycle->cycle_length . ').']);
        }

        // Verificar que las unidades bloqueadas no excedan las unidades totales del equipo
        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($validated['blocked_units'] > $equipment->total_units) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['blocked_units' => 'Las unidades bloqueadas no pueden exceder las unidades totales del equipo (' . $equipment->total_units . ').']);
        }

        // Procesar cada día seleccionado
        $createdCount = 0;
        $errors = [];
        
        foreach ($request->cycle_days as $cycleDay) {
            // Verificar si ya existe un bloqueo para este equipo, ciclo y día con horarios superpuestos
            $existingBlocks = EquipmentBlock::where('equipment_id', $validated['equipment_id'])
                ->where('school_cycle_id', $validated['school_cycle_id'])
                ->where('cycle_day', $cycleDay)
                ->where('is_weekday_block', false)
                ->get();

            $hasConflict = false;
            foreach ($existingBlocks as $existingBlock) {
                // Verificar si hay superposición de horarios
                if ($this->hasTimeOverlap(
                    $validated['start_time'], 
                    $validated['end_time'], 
                    $existingBlock->start_time, 
                    $existingBlock->end_time
                )) {
                    $errors[] = "Día de ciclo {$cycleDay}: Ya existe un bloqueo con horarios superpuestos de {$existingBlock->start_time} a {$existingBlock->end_time}.";
                    $hasConflict = true;
                    break;
                }
            }

            if (!$hasConflict) {
                EquipmentBlock::create([
                    'equipment_id' => $validated['equipment_id'],
                    'school_cycle_id' => $validated['school_cycle_id'],
                    'cycle_day' => $cycleDay,
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'blocked_units' => $validated['blocked_units'],
                    'reason' => $validated['reason'],
                    'is_weekday_block' => false,
                ]);
                $createdCount++;
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => implode(' ', $errors)]);
        }

        return redirect()->route('equipment.blocks.index')
            ->with('success', "Se crearon {$createdCount} bloqueos exitosamente.");
    }

    /**
     * Store a weekly block in storage.
     */
    public function storeWeekly(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'reason' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'blocked_units' => 'required|integer|min:1',
        ]);
        
        // Obtener automáticamente el ciclo escolar activo
        $schoolCycle = SchoolCycle::where('active', true)->firstOrFail();
        
        // Validar que al menos un día de la semana esté seleccionado
        $weekdays = [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
        ];
        
        $anyWeekdaySelected = false;
        foreach ($weekdays as $day) {
            if ($request->has($day)) {
                $anyWeekdaySelected = true;
                break;
            }
        }
        
        if (!$anyWeekdaySelected) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['weekdays' => 'Debe seleccionar al menos un día de la semana.']);
        }

        // Verificar que las unidades bloqueadas no excedan las unidades totales del equipo
        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($validated['blocked_units'] > $equipment->total_units) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['blocked_units' => 'Las unidades bloqueadas no pueden exceder las unidades totales del equipo (' . $equipment->total_units . ').']);
        }
        
        // Verificar conflictos con bloqueos semanales existentes para el mismo equipo
        $weekdaysToCheck = [];
        foreach ($weekdays as $day) {
            if ($request->has($day)) {
                $weekdaysToCheck[] = $day;
            }
        }
        
        $existingWeeklyBlocks = EquipmentBlock::where('equipment_id', $validated['equipment_id'])
            ->where('school_cycle_id', $schoolCycle->id)
            ->where('is_weekday_block', true)
            ->get();
            
        foreach ($existingWeeklyBlocks as $existingBlock) {
            // Verificar si hay superposición de días y horarios
            $hasOverlapDays = false;
            foreach ($weekdaysToCheck as $day) {
                if ($existingBlock->$day) {
                    $hasOverlapDays = true;
                    break;
                }
            }
            
            if ($hasOverlapDays && $this->hasTimeOverlap(
                $validated['start_time'], 
                $validated['end_time'], 
                $existingBlock->start_time, 
                $existingBlock->end_time
            )) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['general' => 'Ya existe un bloqueo semanal para este equipo con días y horarios superpuestos.']);
            }
        }
        
        // Crear el bloqueo con los días de la semana seleccionados
        $equipmentBlock = EquipmentBlock::create([
            'equipment_id' => $validated['equipment_id'],
            'school_cycle_id' => $schoolCycle->id,
            'cycle_day' => 0, // Valor especial para bloqueos semanales
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'blocked_units' => $validated['blocked_units'],
            'reason' => $validated['reason'],
            'is_weekday_block' => true,
            'monday' => $request->has('monday'),
            'tuesday' => $request->has('tuesday'),
            'wednesday' => $request->has('wednesday'),
            'thursday' => $request->has('thursday'),
            'friday' => $request->has('friday'),
            'saturday' => $request->has('saturday'),
            'sunday' => $request->has('sunday'),
        ]);

        return redirect()->route('equipment.blocks.index')
            ->with('success', 'Bloqueo semanal creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EquipmentBlock $equipmentBlock)
    {
        $equipmentBlock->load(['equipment', 'schoolCycle']);
        return view('equipment.blocks.show', compact('equipmentBlock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EquipmentBlock $equipmentBlock)
    {
        $equipments = Equipment::all();
        $schoolCycles = SchoolCycle::all();
        
        return view('equipment.blocks.edit', compact('equipmentBlock', 'equipments', 'schoolCycles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EquipmentBlock $equipmentBlock)
    {
        if ($equipmentBlock->is_weekday_block) {
            return $this->updateWeekly($request, $equipmentBlock);
        } else {
            return $this->updateCycleDay($request, $equipmentBlock);
        }
    }

    /**
     * Update a cycle day block.
     */
    private function updateCycleDay(Request $request, EquipmentBlock $equipmentBlock)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'school_cycle_id' => 'required|exists:school_cycles,id',
            'cycle_day' => 'required|integer|min:1',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'blocked_units' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        // Verificar que el ciclo escolar tenga días con el número de ciclo especificado
        $schoolCycle = SchoolCycle::findOrFail($request->school_cycle_id);
        if ($validated['cycle_day'] > $schoolCycle->cycle_length) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cycle_day' => 'El día de ciclo debe ser menor o igual a la longitud del ciclo escolar (' . $schoolCycle->cycle_length . ').']);
        }

        // Verificar que las unidades bloqueadas no excedan las unidades totales del equipo
        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($validated['blocked_units'] > $equipment->total_units) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['blocked_units' => 'Las unidades bloqueadas no pueden exceder las unidades totales del equipo (' . $equipment->total_units . ').']);
        }

        // Verificar si ya existe un bloqueo para este equipo, ciclo y día con horarios superpuestos (excluyendo el actual)
        $existingBlocks = EquipmentBlock::where('equipment_id', $validated['equipment_id'])
            ->where('school_cycle_id', $validated['school_cycle_id'])
            ->where('cycle_day', $validated['cycle_day'])
            ->where('id', '!=', $equipmentBlock->id)
            ->where('is_weekday_block', false)
            ->get();

        foreach ($existingBlocks as $existingBlock) {
            // Verificar si hay superposición de horarios
            if ($this->hasTimeOverlap(
                $validated['start_time'], 
                $validated['end_time'], 
                $existingBlock->start_time, 
                $existingBlock->end_time
            )) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['general' => 'Ya existe un bloqueo para este equipo, ciclo y día con horarios superpuestos.']);
            }
        }

        $equipmentBlock->update($validated);

        return redirect()->route('equipment.blocks.index')
            ->with('success', 'Bloqueo actualizado exitosamente.');
    }

    /**
     * Update a weekly block.
     */
    private function updateWeekly(Request $request, EquipmentBlock $equipmentBlock)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'reason' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'blocked_units' => 'required|integer|min:1',
        ]);

        // Verificar que las unidades bloqueadas no excedan las unidades totales del equipo
        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($validated['blocked_units'] > $equipment->total_units) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['blocked_units' => 'Las unidades bloqueadas no pueden exceder las unidades totales del equipo (' . $equipment->total_units . ').']);
        }
        
        // Validar que al menos un día de la semana esté seleccionado
        $weekdays = [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
        ];
        
        $anyWeekdaySelected = false;
        foreach ($weekdays as $day) {
            if ($request->has($day)) {
                $anyWeekdaySelected = true;
                break;
            }
        }
        
        if (!$anyWeekdaySelected) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['weekdays' => 'Debe seleccionar al menos un día de la semana.']);
        }

        // Verificar conflictos con otros bloqueos semanales (excluyendo el actual)
        $weekdaysToCheck = [];
        foreach ($weekdays as $day) {
            if ($request->has($day)) {
                $weekdaysToCheck[] = $day;
            }
        }
        
        $existingWeeklyBlocks = EquipmentBlock::where('equipment_id', $validated['equipment_id'])
            ->where('school_cycle_id', $equipmentBlock->school_cycle_id)
            ->where('is_weekday_block', true)
            ->where('id', '!=', $equipmentBlock->id)
            ->get();
            
        foreach ($existingWeeklyBlocks as $existingBlock) {
            // Verificar si hay superposición de días y horarios
            $hasOverlapDays = false;
            foreach ($weekdaysToCheck as $day) {
                if ($existingBlock->$day) {
                    $hasOverlapDays = true;
                    break;
                }
            }
            
            if ($hasOverlapDays && $this->hasTimeOverlap(
                $validated['start_time'], 
                $validated['end_time'], 
                $existingBlock->start_time, 
                $existingBlock->end_time
            )) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['general' => 'Ya existe un bloqueo semanal para este equipo con días y horarios superpuestos.']);
            }
        }
        
        // Actualizar el bloqueo
        $updateData = $validated;
        foreach ($weekdays as $day) {
            $updateData[$day] = $request->has($day);
        }

        $equipmentBlock->update($updateData);

        return redirect()->route('equipment.blocks.index')
            ->with('success', 'Bloqueo semanal actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EquipmentBlock $equipmentBlock)
    {
        $equipmentBlock->delete();
        
        return redirect()->route('equipment.blocks.index')
            ->with('success', 'Bloqueo eliminado exitosamente.');
    }

    /**
     * Get blocked units for a specific equipment, date and time.
     */
    public function getBlockedUnits(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|integer|exists:equipment,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $blockedUnits = EquipmentBlock::getTotalBlockedUnits(
            $request->equipment_id,
            $request->date,
            $request->start_time,
            $request->end_time
        );

        $equipment = Equipment::findOrFail($request->equipment_id);
        $availableUnits = $equipment->getAvailableUnitsForDateTime(
            $request->date,
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'blocked_units' => $blockedUnits,
            'available_units' => $availableUnits,
            'total_units' => $equipment->total_units
        ]);
    }

    /**
     * Obtiene los días de ciclo únicos para un ciclo escolar específico
     */
    public function getCycleDays(Request $request)
    {
        $request->validate([
            'school_cycle_id' => 'required|exists:school_cycles,id'
        ]);

        $cycleDays = \App\Models\CycleDay::where('school_cycle_id', $request->school_cycle_id)
            ->select('cycle_day')
            ->distinct()
            ->orderBy('cycle_day')
            ->pluck('cycle_day')
            ->toArray();

        return response()->json([
            'cycle_days' => $cycleDays
        ]);
    }

    /**
     * Verifica si hay superposición entre dos rangos de tiempo
     */
    private function hasTimeOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        return $start1 < $end2 && $end1 > $start2;
    }
}
