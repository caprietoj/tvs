<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBlock;
use App\Models\EquipmentBlockOverride;
use App\Models\EquipmentLoan;
use App\Models\SchoolCycle;
use App\Models\User;
use App\Mail\EquipmentBlockCeded;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EquipmentBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blocks = EquipmentBlock::with(['equipment', 'schoolCycle'])
            ->orderBy('created_at', 'desc')
            ->get();

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
     * Muestra el formulario para ceder/reasignar un bloqueo a otro docente
     * para una fecha específica (excepción por fecha).
     */
    public function cedeForm(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        try {
            $date = Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $e) {
            $date = now()->toDateString();
        }

        $activeCycle = SchoolCycle::where('active', true)->first();
        $cycleDay = $activeCycle ? \App\Models\CycleDay::getCycleDayForDate($date, $activeCycle->id) : null;

        $blocks = $this->getBlocksForDate($date);
        $teachers = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('equipment.blocks.cede', compact('date', 'blocks', 'teachers', 'cycleDay', 'activeCycle'));
    }

    /**
     * Cede el espacio de un bloqueo a otro docente solo para la fecha indicada.
     * Crea una excepción por fecha y un préstamo a nombre del nuevo docente.
     */
    public function cede(Request $request, EquipmentBlock $equipmentBlock)
    {
        $validated = $request->validate([
            'override_date' => 'required|date',
            'assigned_user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:255',
        ]);

        $date = Carbon::parse($validated['override_date'])->format('Y-m-d');

        if (!$this->blockAppliesOnDate($equipmentBlock, $date)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El bloqueo seleccionado no aplica a la fecha indicada.');
        }

        DB::beginTransaction();

        try {
            $teacher = User::findOrFail($validated['assigned_user_id']);
            $equipment = $equipmentBlock->equipment;
            $startTime = Carbon::parse($equipmentBlock->start_time)->format('H:i');
            $endTime = Carbon::parse($equipmentBlock->end_time)->format('H:i');

            $override = EquipmentBlockOverride::firstOrNew([
                'equipment_block_id' => $equipmentBlock->id,
                'override_date' => $date,
            ]);

            // Si se vuelve a ceder, eliminar el préstamo pendiente anterior para no duplicar
            if ($override->exists && $override->equipment_loan_id) {
                $previousLoan = EquipmentLoan::find($override->equipment_loan_id);
                if ($previousLoan && $previousLoan->status === 'pending') {
                    $previousLoan->delete();
                }
            }

            // Crear el préstamo a nombre del docente (se permite la fecha del mismo día)
            $existingLoan = EquipmentLoan::where('equipment_id', $equipment->id)
                ->where('user_id', $teacher->id)
                ->where('loan_date', $date)
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->where('status', '!=', 'returned')
                ->first();

            if (!$existingLoan) {
                $existingLoan = EquipmentLoan::create([
                    'user_id' => $teacher->id,
                    'equipment_id' => $equipment->id,
                    'section' => $equipment->section,
                    'grade' => optional($equipment->space)->name ?: $equipment->section,
                    'loan_date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'units_requested' => (int) $equipmentBlock->blocked_units,
                    'status' => 'pending',
                    'auto_return' => true,
                    'period_id' => null,
                    'uses_electronic_resources' => false,
                    'uses_skills' => false,
                ]);
            }

            // Registrar/actualizar la excepción por fecha
            $override->fill([
                'new_reason' => $teacher->name,
                'assigned_user_id' => $teacher->id,
                'equipment_loan_id' => $existingLoan->id,
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ])->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Error al ceder bloqueo: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'No se pudo ceder el espacio: ' . $e->getMessage());
        }

        // Notificar por correo al docente que recibe la sala y a los responsables de sistemas
        try {
            $spaceName = optional($equipment->space)->name
                ?: strtoupper(str_replace('_', ' ', $equipment->section));

            $recipients = array_values(array_unique(array_filter([
                $teacher->email,
                'mmarcell@tvs.edu.co',
                'jefesistemas@tvs.edu.co',
                'auxiliarsistemas@tvs.edu.co',
            ])));

            Mail::to($recipients)->send(new EquipmentBlockCeded([
                'space_name' => $spaceName,
                'date' => Carbon::parse($date)->format('d/m/Y'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'original_teacher' => $equipmentBlock->reason,
                'new_teacher' => $teacher->name,
                'ceded_by' => optional(auth()->user())->name ?? 'Sistema',
            ]));
        } catch (\Throwable $e) {
            \Log::error('Error al enviar correo de cesión de sala: ' . $e->getMessage());
        }

        return redirect()->route('equipment.blocks.index')
            ->with('success', 'Espacio cedido correctamente. Se creó el préstamo para el docente.');
    }

    /**
     * Obtiene los bloqueos que aplican a una fecha (semanal + día de ciclo).
     */
    private function getBlocksForDate(string $date): \Illuminate\Support\Collection
    {
        $dateObj = Carbon::parse($date);
        $dayOfWeek = strtolower($dateObj->format('l'));

        $activeCycle = SchoolCycle::where('active', true)->first();
        if (!$activeCycle) {
            return collect();
        }

        $cycleDay = \App\Models\CycleDay::getCycleDayForDate($date, $activeCycle->id);

        $blocks = EquipmentBlock::with(['equipment.space'])
            ->where('school_cycle_id', $activeCycle->id)
            ->where(function ($q) use ($dayOfWeek, $cycleDay) {
                $q->where(function ($sub) use ($dayOfWeek) {
                    $sub->where('is_weekday_block', true)->where($dayOfWeek, true);
                });

                if ($cycleDay) {
                    $q->orWhere(function ($sub) use ($cycleDay) {
                        $sub->where('is_weekday_block', false)->where('cycle_day', $cycleDay->cycle_day);
                    });
                }
            })
            ->orderBy('start_time')
            ->get();

        $overrides = EquipmentBlockOverride::where('override_date', $date)
            ->whereIn('equipment_block_id', $blocks->pluck('id'))
            ->get()
            ->keyBy('equipment_block_id');

        $blocks->each(function ($block) use ($overrides) {
            $block->setAttribute('override', $overrides->get($block->id));
        });

        return $blocks;
    }

    /**
     * Verifica si un bloqueo aplica a una fecha específica.
     */
    private function blockAppliesOnDate(EquipmentBlock $block, string $date): bool
    {
        $activeCycle = SchoolCycle::where('active', true)->first();
        if (!$activeCycle || (int) $block->school_cycle_id !== (int) $activeCycle->id) {
            return false;
        }

        $dateObj = Carbon::parse($date);

        if ($block->is_weekday_block) {
            $dayOfWeek = strtolower($dateObj->format('l'));
            return (bool) $block->$dayOfWeek;
        }

        $cycleDay = \App\Models\CycleDay::getCycleDayForDate($date, $activeCycle->id);

        return $cycleDay && (int) $cycleDay->cycle_day === (int) $block->cycle_day;
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
