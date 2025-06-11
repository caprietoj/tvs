<?php

namespace App\Http\Controllers;

use App\Models\SkillCategory;
use App\Models\SkillSubcategory;
use Illuminate\Http\Request;

class SkillSubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = SkillSubcategory::with('category')->orderBy('name')->get();
        return view('skills.subcategories.index', compact('subcategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = SkillCategory::where('active', true)->orderBy('name')->get();
        return view('skills.subcategories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_category_id' => 'required|exists:skill_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        $subcategory = SkillSubcategory::create($validated);

        return redirect()->route('skill-subcategories.index')
            ->with('success', 'Subcategoría de habilidades creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SkillSubcategory $skillSubcategory)
    {
        $skillSubcategory->load('category', 'skills');
        return view('skills.subcategories.show', compact('skillSubcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SkillSubcategory $skillSubcategory)
    {
        $categories = SkillCategory::where('active', true)->orderBy('name')->get();
        return view('skills.subcategories.edit', compact('skillSubcategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SkillSubcategory $skillSubcategory)
    {
        $validated = $request->validate([
            'skill_category_id' => 'required|exists:skill_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        $skillSubcategory->update($validated);

        return redirect()->route('skill-subcategories.index')
            ->with('success', 'Subcategoría de habilidades actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SkillSubcategory $skillSubcategory)
    {
        // Verificar si tiene habilidades antes de eliminar
        if ($skillSubcategory->skills()->count() > 0) {
            return redirect()->route('skill-subcategories.index')
                ->with('error', 'No se puede eliminar esta subcategoría porque tiene habilidades asociadas.');
        }

        $skillSubcategory->delete();

        return redirect()->route('skill-subcategories.index')
            ->with('success', 'Subcategoría de habilidades eliminada exitosamente.');
    }
}
