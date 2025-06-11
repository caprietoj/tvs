<?php

namespace App\Http\Controllers;

use App\Models\SkillCategory;
use Illuminate\Http\Request;

class SkillCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = SkillCategory::orderBy('name')->get();
        return view('skills.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('skills.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skill_categories,name',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        $category = SkillCategory::create($validated);

        return redirect()->route('skill-categories.index')
            ->with('success', 'Categoría de habilidades creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SkillCategory $skillCategory)
    {
        return view('skills.categories.show', compact('skillCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SkillCategory $skillCategory)
    {
        return view('skills.categories.edit', compact('skillCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SkillCategory $skillCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skill_categories,name,' . $skillCategory->id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        $skillCategory->update($validated);

        return redirect()->route('skill-categories.index')
            ->with('success', 'Categoría de habilidades actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SkillCategory $skillCategory)
    {
        // Verificar si tiene subcategorías antes de eliminar
        if ($skillCategory->subcategories()->count() > 0) {
            return redirect()->route('skill-categories.index')
                ->with('error', 'No se puede eliminar esta categoría porque tiene subcategorías asociadas.');
        }

        $skillCategory->delete();

        return redirect()->route('skill-categories.index')
            ->with('success', 'Categoría de habilidades eliminada exitosamente.');
    }
}
