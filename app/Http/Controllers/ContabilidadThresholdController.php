<?php

namespace App\Http\Controllers;

use App\Models\ContabilidadThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContabilidadThresholdController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexContabilidad()
    {
        $thresholds = ContabilidadThreshold::orderBy('created_at', 'desc')->get();
        return view('threshold.contabilidad.index', compact('thresholds'));
    }

    public function createContabilidad()
    {
        return view('threshold.contabilidad.create');
    }

    public function storeContabilidad(Request $request)
    {
        $validated = $request->validate([
            'kpi_name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string'
        ]);

        ContabilidadThreshold::create($validated);

        return redirect()->route('umbral.contabilidad.index')
            ->with('success', 'Umbral creado exitosamente');
    }

    public function editContabilidad($id)
    {
        $threshold = ContabilidadThreshold::findOrFail($id);
        return view('threshold.contabilidad.edit', compact('threshold'));
    }

    public function updateContabilidad(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kpi_name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $threshold = ContabilidadThreshold::findOrFail($id);
        $threshold->update([
            'kpi_name' => $request->kpi_name,
            'value' => $request->value
        ]);

        return redirect()->route('umbral.contabilidad.index')
            ->with('success', 'Umbral de Contabilidad actualizado exitosamente.');
    }

    public function showContabilidad()
    {
        $thresholds = ContabilidadThreshold::orderBy('created_at', 'desc')->get();
        return view('threshold.contabilidad.show', compact('thresholds'));
    }

    public function destroyContabilidad($id)
    {
        try {
            $threshold = ContabilidadThreshold::findOrFail($id);
            $threshold->delete();
            return response()->json([
                'success' => true,
                'message' => 'Umbral de Contabilidad eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el umbral: ' . $e->getMessage()
            ], 500);
        }
    }
}