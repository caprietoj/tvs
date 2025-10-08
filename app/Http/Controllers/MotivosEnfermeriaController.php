<?php

namespace App\Http\Controllers;

use App\Models\MotivoEnfermeria;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MotivosEnfermeriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view.enfermeria');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $motivos = MotivoEnfermeria::ordenados()->get();
        
        return view('parametrizacion.motivos-enfermeria.index', compact('motivos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('parametrizacion.motivos-enfermeria.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:motivos_enfermeria,nombre',
            'codigo_cie10' => 'nullable|string|max:10',
            'categoria' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'icono' => 'nullable|string|max:10',
            'orden' => 'required|integer|min:1'
        ]);

        MotivoEnfermeria::create($request->all());

        return redirect()->route('motivos-enfermeria.index')
            ->with('success', 'Motivo de enfermería creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MotivoEnfermeria $motivoEnfermeria): View
    {
        return view('parametrizacion.motivos-enfermeria.show', compact('motivoEnfermeria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MotivoEnfermeria $motivoEnfermeria): View
    {
        return view('parametrizacion.motivos-enfermeria.edit', compact('motivoEnfermeria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MotivoEnfermeria $motivoEnfermeria): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:motivos_enfermeria,nombre,' . $motivoEnfermeria->id,
            'codigo_cie10' => 'nullable|string|max:10',
            'categoria' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'icono' => 'nullable|string|max:10',
            'orden' => 'required|integer|min:1',
            'activo' => 'boolean'
        ]);

        $motivoEnfermeria->update($request->all());

        return redirect()->route('motivos-enfermeria.index')
            ->with('success', 'Motivo de enfermería actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MotivoEnfermeria $motivoEnfermeria): RedirectResponse
    {
        $motivoEnfermeria->delete();

        return redirect()->route('motivos-enfermeria.index')
            ->with('success', 'Motivo de enfermería eliminado exitosamente.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(MotivoEnfermeria $motivoEnfermeria): RedirectResponse
    {
        $motivoEnfermeria->update(['activo' => !$motivoEnfermeria->activo]);
        
        $status = $motivoEnfermeria->activo ? 'activado' : 'desactivado';
        
        return redirect()->route('motivos-enfermeria.index')
            ->with('success', "Motivo {$status} exitosamente.");
    }

    /**
     * Import motivos from Excel data
     */
    public function import(Request $request)
    {
        $request->validate([
            'motivos' => 'required|array|min:1',
            'motivos.*.nombre' => 'required|string|max:255',
            'motivos.*.codigo_cie10' => 'nullable|string|max:10',
            'motivos.*.categoria' => 'nullable|string|max:100',
            'motivos.*.descripcion' => 'nullable|string|max:500',
            'motivos.*.orden' => 'required|integer|min:1'
        ]);

        $imported = 0;
        $errors = [];

        foreach ($request->motivos as $motivoData) {
            try {
                // Verificar si ya existe un motivo con el mismo nombre
                $exists = MotivoEnfermeria::where('nombre', $motivoData['nombre'])->exists();
                
                if (!$exists) {
                    MotivoEnfermeria::create([
                        'nombre' => $motivoData['nombre'],
                        'codigo_cie10' => $motivoData['codigo_cie10'] ?? null,
                        'categoria' => $motivoData['categoria'] ?? null,
                        'descripcion' => $motivoData['descripcion'] ?? null,
                        'icono' => null, // Se puede asignar manualmente después
                        'orden' => $motivoData['orden'],
                        'activo' => true
                    ]);
                    $imported++;
                } else {
                    $errors[] = "El motivo '{$motivoData['nombre']}' ya existe";
                }
            } catch (\Exception $e) {
                $errors[] = "Error al importar '{$motivoData['nombre']}': " . $e->getMessage();
            }
        }

        if ($imported > 0) {
            return response()->json([
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
                'message' => "Se importaron {$imported} motivos exitosamente."
            ]);
        } else {
            return response()->json([
                'success' => false,
                'imported' => 0,
                'errors' => $errors,
                'message' => 'No se pudo importar ningún motivo.'
            ], 422);
        }
    }
}
