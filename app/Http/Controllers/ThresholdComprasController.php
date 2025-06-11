<?php

namespace App\Http\Controllers;

use App\Models\ComprasThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThresholdComprasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createCompras()
    {
        return view('threshold.compras.create');
    }

    public function storeCompras(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kpi_name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ComprasThreshold::create([
            'area' => 'compras',
            'kpi_name' => $request->kpi_name,
            'value' => $request->value,
        ]);

        return redirect()->route('umbral.compras.show')
            ->with('success', 'Umbral creado exitosamente');
    }

    public function editCompras($id)
    {
        $threshold = ComprasThreshold::findOrFail($id);
        return view('threshold.compras.edit', compact('threshold'));
    }

    public function updateCompras(Request $request, $id)
    {
        $threshold = ComprasThreshold::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'kpi_name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $threshold->update([
            'kpi_name' => $request->kpi_name,
            'value' => $request->value,
        ]);

        return redirect()->route('umbral.compras.show')
            ->with('success', 'Umbral actualizado exitosamente');
    }

    public function showCompras()
    {
        $thresholds = ComprasThreshold::where('area', 'compras')->get();
        return view('threshold.compras.show', compact('thresholds'));
    }

    public function destroyCompras($id)
    {
        try {
            $threshold = ComprasThreshold::findOrFail($id);
            $threshold->delete();
            return response()->json([
                'success' => true,
                'message' => 'Umbral eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el umbral: ' . $e->getMessage()
            ], 500);
        }
    }
}
