<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $spaces = Space::all();
        return view('spaces.index', compact('spaces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $skillCategories = SkillCategory::with(['subcategories' => function($q) {
            $q->where('active', true)
              ->orderBy('name');
        }])
        ->where('active', true)
        ->orderBy('name')
        ->get();
        
        return view('spaces.create', compact('skillCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'nullable',
            'is_library' => 'nullable',
            'skills' => 'nullable|array',
        ]);

        $validated['active'] = $request->has('active') ? true : false;
        $validated['is_library'] = $request->has('is_library') ? true : false;
        
        // Procesar la imagen si se ha subido una
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/spaces', $imageName);
            $validated['image_path'] = str_replace('public/', 'storage/', $path);
        }
        
        // Crear el espacio y guardar sus datos básicos
        $space = Space::create($validated);
        
        // Procesar las habilidades si existen
        if ($request->has('skills')) {
            $skillsToAdd = [];
            
            foreach ($request->skills as $category => $subcategories) {
                foreach ($subcategories as $subcategory => $items) {
                    foreach ($items as $item) {
                        if (!empty($item['name'])) {
                            // Buscar primero si existe una habilidad con el mismo nombre en la misma subcategoría
                            $existingSkill = Skill::where('name', $item['name'])
                                ->where('skill_subcategory_id', $item['subcategory_id'])
                                ->first();
                            
                            if ($existingSkill) {
                                $skillsToAdd[] = [
                                    'skill_id' => $existingSkill->id,
                                    'description' => $item['description'] ?? null
                                ];
                            } else {
                                $skill = Skill::create([
                                    'skill_subcategory_id' => $item['subcategory_id'],
                                    'name' => $item['name'],
                                    'description' => $item['description'] ?? null,
                                    'active' => true
                                ]);
                                
                                $skillsToAdd[] = [
                                    'skill_id' => $skill->id,
                                    'description' => $item['description'] ?? null
                                ];
                            }
                        }
                    }
                }
            }
            
            // Adjuntar todas las habilidades al espacio
            foreach ($skillsToAdd as $skillData) {
                $space->skills()->attach($skillData['skill_id'], [
                    'description' => $skillData['description']
                ]);
            }
            
            // Verificar que se hayan guardado las habilidades
            $space->load('skills');
            if ($space->skills->count() === 0 && count($skillsToAdd) > 0) {
                return redirect()->back()->with('error', 'Hubo un problema al guardar las habilidades. Por favor, intente de nuevo.')
                    ->withInput();
            }
        }
        
        // Procesar los implementos para préstamo si existen
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                if (!empty($item['name'])) {
                    $space->items()->create([
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'available' => true
                    ]);
                }
            }
        }
        
        return redirect()->route('spaces.index')
            ->with('success', 'Espacio creado exitosamente.');
    }

    /**
     * Get space details in JSON format for AJAX requests
     */
    public function getDetails(Space $space)
    {
        // Cargar el espacio con sus implementos y habilidades, incluyendo las relaciones necesarias
        $space->load([
            'items',
            'skills.subcategory.category' // Cargar la jerarquía completa de habilidades
        ]);

        // Transformar los datos para incluir la información de categorías
        $spaceData = $space->toArray();
        
        // Si hay habilidades, agregar la información de categorías y subcategorías
        if (!empty($spaceData['skills'])) {
            foreach ($spaceData['skills'] as &$skill) {
                $skill['category_id'] = $skill['subcategory']['category']['id'];
                $skill['category_name'] = $skill['subcategory']['category']['name'];
                $skill['subcategory_name'] = $skill['subcategory']['name'];
                // Incluir la subcategoría completa para acceder a su descripción
                $skill['subcategory'] = [
                    'name' => $skill['subcategory']['name'],
                    'description' => $skill['subcategory']['description']
                ];
            }
        }

        return response()->json($spaceData);
    }

    /**
     * Display the specified resource.
     */
    public function show(Space $space)
    {
        $space->load(['items', 'skills.subcategory.category']);
        return view('spaces.show', compact('space'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Space $space)
    {
        // Cargar el espacio con sus habilidades
        $space->load(['skills' => function($query) {
            $query->with('subcategory.category');
        }]);
        
        // Verificar que se hayan cargado las habilidades
        \Log::info('Editing space ID: ' . $space->id . ' with ' . $space->skills->count() . ' skills');
        foreach ($space->skills as $skill) {
            \Log::info('Skill: ' . $skill->name . ', Subcategory ID: ' . $skill->skill_subcategory_id);
        }
        
        $skillCategories = SkillCategory::with(['subcategories' => function($q) {
            $q->where('active', true)
              ->orderBy('name');
        }])
        ->where('active', true)
        ->orderBy('name')
        ->get();
        
        return view('spaces.edit', compact('space', 'skillCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Space $space)
    {
        // Depuración para ver los datos que llegan
        \Log::info('Actualizando espacio', [
            'space_id' => $space->id,
            'is_library' => $request->has('is_library'),
            'skills_data' => $request->has('skills') ? json_encode($request->skills) : 'No hay habilidades'
        ]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'nullable',
            'is_library' => 'nullable',
        ]);

        $validated['active'] = $request->has('active') ? true : false;
        $validated['is_library'] = $request->has('is_library') ? true : false;
        
        // Limpiar habilidades si el espacio deja de ser biblioteca
        if (!$request->has('is_library') && $space->is_library) {
            $space->skills()->detach();
        }
        
        // Procesar la imagen si se ha subido una nueva
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($space->image_path && Storage::exists(str_replace('storage/', 'public/', $space->image_path))) {
                Storage::delete(str_replace('storage/', 'public/', $space->image_path));
            }
            
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/spaces', $imageName);
            $validated['image_path'] = str_replace('public/', 'storage/', $path);
        }
        
        $space->update($validated);
        
        // Procesar las habilidades solo si el espacio es una biblioteca
        if ($validated['is_library']) {
            // Eliminar todas las relaciones existentes solo si se están enviando nuevas habilidades
            if ($request->has('skills')) {
                $space->skills()->detach();
                
                $skillsToAdd = [];
                
                try {
                    foreach ($request->skills as $category => $subcategories) {
                        foreach ($subcategories as $subcategory => $items) {
                            foreach ($items as $index => $item) {
                                // Saltar si el nombre está vacío
                                if (empty($item['name'])) continue;
                                
                                // Obtener o crear la habilidad
                                if (!empty($item['id'])) {
                                    $skill = Skill::find($item['id']);
                                } else if (!empty($item['subcategory_id'])) {
                                    $skill = Skill::firstOrCreate(
                                        [
                                            'name' => $item['name'],
                                            'skill_subcategory_id' => $item['subcategory_id']
                                        ],
                                        [
                                            'description' => $item['description'] ?? null,
                                            'active' => true
                                        ]
                                    );
                                } else {
                                    // No hay suficiente información para crear la habilidad
                                    continue;
                                }
                                
                                if ($skill) {
                                    $skillsToAdd[] = [
                                        'skill_id' => $skill->id,
                                        'description' => $item['description'] ?? null
                                    ];
                                }
                            }
                        }
                    }
                    
                    // Adjuntar todas las habilidades al espacio
                    foreach ($skillsToAdd as $skillData) {
                        $space->skills()->attach($skillData['skill_id'], [
                            'description' => $skillData['description']
                        ]);
                    }
                    
                    // Verificar que se hayan guardado las habilidades
                    $space->load('skills');
                    \Log::info('Habilidades guardadas', ['count' => $space->skills->count()]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error al procesar habilidades', ['error' => $e->getMessage()]);
                    return redirect()->route('spaces.index')
                        ->with('warning', 'El espacio se actualizó pero hubo un problema con las habilidades: ' . $e->getMessage());
                }
            }
        }
        
        // Procesar los implementos para préstamo
        if ($request->has('items')) {
            // Crear un array para almacenar los IDs de los implementos que debemos mantener
            $keepItemIds = [];
            
            foreach ($request->items as $itemData) {
                // Si existe un ID, estamos actualizando un implemento existente
                if (isset($itemData['id'])) {
                    $item = $space->items()->find($itemData['id']);
                    
                    if ($item) {
                        $item->update([
                            'name' => $itemData['name'],
                            'description' => $itemData['description'] ?? null,
                            'quantity' => $itemData['quantity'] ?? 1,
                            'available' => isset($itemData['available'])
                        ]);
                        
                        $keepItemIds[] = $item->id;
                    }
                } 
                // Si no hay ID pero hay nombre, creamos un nuevo implemento
                elseif (!empty($itemData['name'])) {
                    $newItem = $space->items()->create([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $itemData['quantity'] ?? 1,
                        'available' => isset($itemData['available'])
                    ]);
                    
                    $keepItemIds[] = $newItem->id;
                }
            }
            
            // Eliminar implementos que no están en la lista a mantener
            if (!empty($keepItemIds)) {
                $space->items()->whereNotIn('id', $keepItemIds)->delete();
            } else {
                // Si no hay implementos para mantener, eliminar todos los existentes
                $space->items()->delete();
            }
        } else {
            // Si no hay datos de implementos, eliminar todos los existentes
            $space->items()->delete();
        }
        
        return redirect()->route('spaces.index')
            ->with('success', 'Espacio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Space $space)
    {
        // Verificar si hay reservas asociadas a este espacio
        if ($space->reservations()->exists()) {
            return redirect()->route('spaces.index')
                ->with('error', 'No se puede eliminar el espacio porque tiene reservas asociadas.');
        }
        
        // Eliminar la imagen del espacio si existe
        if ($space->image_path && Storage::exists(str_replace('storage/', 'public/', $space->image_path))) {
            Storage::delete(str_replace('storage/', 'public/', $space->image_path));
        }
        
        // Eliminar los bloqueos asociados al espacio
        $space->blocks()->delete();
        
        // Eliminar el espacio
        $space->delete();
        
        return redirect()->route('spaces.index')
            ->with('success', 'Espacio eliminado exitosamente.');
    }
}
