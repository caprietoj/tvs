<?php

namespace App\Http\Controllers;

use App\Models\SchoolCycle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchoolCycleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schoolCycles = SchoolCycle::orderBy('created_at', 'desc')->get();
        return view('school_cycles.index', compact('schoolCycles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school_cycles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'cycle_length' => 'required|integer|min:1|max:30',
        ]);

        // Si se solicita activar este ciclo, desactivamos todos los demás
        if ($request->has('active')) {
            SchoolCycle::where('active', true)->update(['active' => false]);
            $validated['active'] = true;
        } else {
            $validated['active'] = false;
        }

        $schoolCycle = SchoolCycle::create($validated);

        // Si se solicita generar automáticamente los días del ciclo
        if ($request->has('generate_days')) {
            // Determinar fecha de fin (por defecto 1 año)
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->input('end_date'))
                : Carbon::parse($validated['start_date'])->addYear();

            $schoolCycle->generateCycleDays($endDate);
        }

        return redirect()->route('school-cycles.index')
            ->with('success', 'Ciclo escolar creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolCycle $schoolCycle)
    {
        // Cargar los días del ciclo, ordenados por fecha
        $cycleDays = $schoolCycle->cycleDays()->orderBy('date')->get();
        
        return view('school_cycles.show', compact('schoolCycle', 'cycleDays'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolCycle $schoolCycle)
    {
        return view('school_cycles.edit', compact('schoolCycle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolCycle $schoolCycle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Si se solicita activar este ciclo, desactivamos todos los demás
        if ($request->has('active') && !$schoolCycle->active) {
            SchoolCycle::where('active', true)->update(['active' => false]);
            $validated['active'] = true;
        } elseif (!$request->has('active') && $schoolCycle->active) {
            // No permitir desactivar el único ciclo activo
            return redirect()->back()
                ->with('error', 'Debe haber al menos un ciclo escolar activo.');
        }

        $schoolCycle->update($validated);

        return redirect()->route('school-cycles.index')
            ->with('success', 'Ciclo escolar actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolCycle $schoolCycle)
    {
        // No permitir eliminar un ciclo activo
        if ($schoolCycle->active) {
            return redirect()->route('school-cycles.index')
                ->with('error', 'No se puede eliminar un ciclo escolar activo.');
        }

        // Verificar si hay bloqueos o reservas asociadas al ciclo
        if ($schoolCycle->spaceBlocks()->exists()) {
            return redirect()->route('school-cycles.index')
                ->with('error', 'No se puede eliminar el ciclo porque tiene bloqueos de espacios asociados.');
        }

        // Eliminar los días de ciclo asociados
        $schoolCycle->cycleDays()->delete();
        
        // Eliminar el ciclo
        $schoolCycle->delete();
        
        return redirect()->route('school-cycles.index')
            ->with('success', 'Ciclo escolar eliminado exitosamente.');
    }

    /**
     * Genera los días del ciclo escolar.
     */
    public function generateCycleDays(Request $request, SchoolCycle $schoolCycle)
    {
        $request->validate([
            'end_date' => 'required|date|after:' . $schoolCycle->start_date,
        ]);

        // Eliminar días existentes del ciclo si se solicita
        if ($request->has('reset_days')) {
            $schoolCycle->cycleDays()->delete();
        }

        // Generar los días del ciclo
        $endDate = Carbon::parse($request->input('end_date'));
        $daysGenerated = $schoolCycle->generateCycleDays($endDate);

        return redirect()->route('school-cycles.show', $schoolCycle)
            ->with('success', 'Se generaron ' . count($daysGenerated) . ' días de ciclo exitosamente.');
    }
}
