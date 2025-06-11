<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\SpaceBlock;
use App\Models\SchoolCycle;
use Illuminate\Http\Request;

class SpaceBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $spaceBlocks = SpaceBlock::with(['space', 'schoolCycle'])
            ->orderBy('school_cycle_id')
            ->orderBy('space_id')
            ->orderBy('cycle_day')
            ->get();

        $spaces = Space::where('active', true)->get();
        $schoolCycles = SchoolCycle::where('active', true)->get();

        return view('space_blocks.index', compact('spaceBlocks', 'spaces', 'schoolCycles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spaces = Space::where('active', true)->get();
        $schoolCycles = SchoolCycle::where('active', true)->get();

        return view('space_blocks.create', compact('spaces', 'schoolCycles'));
    }
    
    /**
     * Show the form for creating a weekly block.
     */
    public function createWeekly()
    {
        $spaces = Space::where('active', true)->get();
        $schoolCycles = SchoolCycle::where('active', true)->get();

        return view('space_blocks.create-weekly', compact('spaces', 'schoolCycles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'school_cycle_id' => 'required|exists:school_cycles,id',
            'cycle_days' => 'required|array', // Cambiado a 'cycle_days' en plural y tipo array
            'cycle_days.*' => 'integer|min:1', // Validación para cada elemento del array
            'reason' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Verificar que el ciclo escolar tenga días con el número de ciclo especificado
        $schoolCycle = SchoolCycle::findOrFail($request->school_cycle_id);
        $maxCycleDay = max($request->cycle_days);
        
        if ($maxCycleDay > $schoolCycle->cycle_length) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cycle_days' => 'Los días de ciclo deben ser menores o iguales a la longitud del ciclo escolar (' . $schoolCycle->cycle_length . ').']);
        }

        // Procesar cada día seleccionado
        $createdCount = 0;
        $errors = [];
        
        foreach ($request->cycle_days as $cycleDay) {
            // Verificar si ya existe un bloqueo para este espacio, ciclo y día
            $existingBlock = SpaceBlock::where('space_id', $validated['space_id'])
                ->where('school_cycle_id', $validated['school_cycle_id'])
                ->where('cycle_day', $cycleDay)
                ->first();

            if ($existingBlock) {
                $errors[] = 'Ya existe un bloqueo para este espacio en el día ' . $cycleDay . ' del ciclo.';
                continue;
            }

            // Crear el bloqueo con horarios
            SpaceBlock::create([
                'space_id' => $validated['space_id'],
                'school_cycle_id' => $validated['school_cycle_id'],
                'cycle_day' => $cycleDay,
                'reason' => $validated['reason'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]);
            
            $createdCount++;
        }

        if ($createdCount > 0) {
            $message = 'Se ' . ($createdCount == 1 ? 'ha' : 'han') . ' creado ' . $createdCount . ' bloqueo' . ($createdCount == 1 ? '' : 's') . ' de espacio exitosamente.';
            return redirect()->route('space-blocks.index')
                ->with('success', $message);
        } else {
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'No se pudo crear ningún bloqueo: ' . implode(', ', $errors)]);
        }
    }
    
    /**
     * Store a newly created weekly block resource in storage.
     */
    public function storeWeekly(Request $request)
    {
        $validated = $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'reason' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
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
        
        // Crear el bloqueo con los días de la semana seleccionados
        $spaceBlock = SpaceBlock::create([
            'space_id' => $validated['space_id'],
            'school_cycle_id' => $schoolCycle->id, // Usar el ciclo escolar activo
            'cycle_day' => 0, // Valor especial para bloqueos semanales
            'reason' => $validated['reason'] ?? null,
            'is_weekday_block' => true,
            'monday' => $request->has('monday'),
            'tuesday' => $request->has('tuesday'),
            'wednesday' => $request->has('wednesday'),
            'thursday' => $request->has('thursday'),
            'friday' => $request->has('friday'),
            'saturday' => $request->has('saturday'),
            'sunday' => $request->has('sunday'),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);
        
        if ($spaceBlock) {
            // Construir un mensaje descriptivo de los días bloqueados
            $daysMessage = [];
            if ($spaceBlock->monday) $daysMessage[] = 'lunes';
            if ($spaceBlock->tuesday) $daysMessage[] = 'martes';
            if ($spaceBlock->wednesday) $daysMessage[] = 'miércoles';
            if ($spaceBlock->thursday) $daysMessage[] = 'jueves';
            if ($spaceBlock->friday) $daysMessage[] = 'viernes';
            if ($spaceBlock->saturday) $daysMessage[] = 'sábado';
            if ($spaceBlock->sunday) $daysMessage[] = 'domingo';
            
            $message = 'Se ha creado un bloqueo semanal para ' . implode(', ', $daysMessage) . ' en el horario de ' . 
                       $spaceBlock->start_time . ' a ' . $spaceBlock->end_time;
                       
            return redirect()->route('space-blocks.index')
                ->with('success', $message);
        } else {
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'No se pudo crear el bloqueo semanal.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SpaceBlock $spaceBlock)
    {
        return view('space_blocks.show', compact('spaceBlock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SpaceBlock $spaceBlock)
    {
        $spaces = Space::where('active', true)->get();
        $schoolCycles = SchoolCycle::where('active', true)->get();

        return view('space_blocks.edit', compact('spaceBlock', 'spaces', 'schoolCycles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpaceBlock $spaceBlock)
    {
        $validated = $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'school_cycle_id' => 'required|exists:school_cycles,id',
            'cycle_day' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Verificar que el ciclo escolar tenga días con el número de ciclo especificado
        $schoolCycle = SchoolCycle::findOrFail($request->school_cycle_id);
        if ($validated['cycle_day'] > $schoolCycle->cycle_length) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cycle_day' => 'El día de ciclo debe ser menor o igual a la longitud del ciclo escolar (' . $schoolCycle->cycle_length . ').']);
        }

        // Verificar si ya existe un bloqueo para este espacio, ciclo y día (excluyendo el actual)
        $existingBlock = SpaceBlock::where('space_id', $validated['space_id'])
            ->where('school_cycle_id', $validated['school_cycle_id'])
            ->where('cycle_day', $validated['cycle_day'])
            ->where('id', '!=', $spaceBlock->id)
            ->first();

        if ($existingBlock) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cycle_day' => 'Ya existe un bloqueo para este espacio en este día del ciclo.']);
        }

        // Actualizar el bloqueo con los campos de horario
        $spaceBlock->update($validated);

        return redirect()->route('space-blocks.index')
            ->with('success', 'Bloqueo de espacio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpaceBlock $spaceBlock)
    {
        $spaceBlock->delete();

        return redirect()->route('space-blocks.index')
            ->with('success', 'Bloqueo de espacio eliminado exitosamente.');
    }

    /**
     * Obtener bloqueos por espacio.
     */
    public function getBlocksBySpace(int $spaceId)
    {
        $space = Space::findOrFail($spaceId);
        $activeSchoolCycle = SchoolCycle::where('active', true)->first();

        if (!$activeSchoolCycle) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un ciclo escolar activo.'
            ]);
        }

        $blocks = SpaceBlock::where('space_id', $spaceId)
            ->where('school_cycle_id', $activeSchoolCycle->id)
            ->get()
            ->pluck('cycle_day')
            ->toArray();

        return response()->json([
            'success' => true,
            'space' => $space->name,
            'cycle_id' => $activeSchoolCycle->id,
            'cycle_length' => $activeSchoolCycle->cycle_length,
            'blocks' => $blocks
        ]);
    }
}
