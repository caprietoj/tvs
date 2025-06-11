<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillSubcategory;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $skills = Skill::with('subcategory.category')->orderBy('name')->get();
        return view('skills.index', compact('skills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subcategories = SkillSubcategory::with('category')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('category.name');
        return view('skills.create', compact('subcategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_subcategory_id' => 'required|exists:skill_subcategories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        $skill = Skill::create($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Habilidad creada exitosamente.');
    }

    /**
     * Store a new skill via API.
     */
    public function apiCreate(Request $request)
    {
        $validated = $request->validate([
            'skill_subcategory_id' => 'required|exists:skill_subcategories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['active'] = true;

        $skill = Skill::create($validated);

        return response()->json($skill, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Skill $skill)
    {
        $skill->load('subcategory.category');
        return view('skills.show', compact('skill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Skill $skill)
    {
        $subcategories = SkillSubcategory::with('category')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('category.name');
        return view('skills.edit', compact('skill', 'subcategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'skill_subcategory_id' => 'required|exists:skill_subcategories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        $skill->update($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Habilidad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill)
    {
        // Verificar si hay espacios o reservas que usen esta habilidad
        if ($skill->spaces()->count() > 0 || $skill->spaceReservations()->count() > 0) {
            return redirect()->route('skills.index')
                ->with('error', 'No se puede eliminar esta habilidad porque está siendo utilizada por espacios o reservas.');
        }

        $skill->delete();

        return redirect()->route('skills.index')
            ->with('success', 'Habilidad eliminada exitosamente.');
    }
}
