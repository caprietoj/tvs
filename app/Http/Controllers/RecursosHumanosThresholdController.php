<?php

namespace App\Http\Controllers;

use App\Models\RecursosHumanosThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecursosHumanosThresholdController extends Controller
{
    public function __construct()
    {
         $this->middleware('auth');
    }

    // Muestra el formulario para crear un nuevo threshold para RRHH
    public function createRecursosHumanos()
    {
         return view('threshold.rrhh.create');
    }

    // Almacena el threshold para RRHH
    public function storeRecursosHumanos(Request $request)
    {
         $validator = Validator::make($request->all(), [
              'kpi_name' => 'required|string|max:255',
              'value'    => 'required|numeric|min:0|max:100',
         ]);

         if ($validator->fails()){
              return redirect()->back()->withErrors($validator)->withInput();
         }

         RecursosHumanosThreshold::create([
              'kpi_name' => $request->kpi_name,
              'value'    => $request->value,
         ]);

         return redirect()->route('umbral.rrhh.show')->with('success', 'Threshold de Recursos Humanos creado exitosamente.');
    }

    // Muestra el formulario para editar el threshold para RRHH
    public function editRecursosHumanos($id)
    {
        $threshold = RecursosHumanosThreshold::findOrFail($id);
        return view('threshold.rrhh.edit', compact('threshold'));
    }

    // Actualiza el threshold para RRHH
    public function updateRecursosHumanos(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kpi_name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $threshold = RecursosHumanosThreshold::findOrFail($id);
        $threshold->update([
            'kpi_name' => $request->kpi_name,
            'value' => $request->value
        ]);

        return redirect()->route('umbral.rrhh.show')
            ->with('success', 'Threshold de Recursos Humanos actualizado exitosamente.');
    }

    // Muestra en una tabla todos los thresholds configurados para RRHH (vista con DataTables y SweetAlert2)
    public function showRecursosHumanos()
    {
         $thresholds = RecursosHumanosThreshold::all();
         return view('threshold.rrhh.show', compact('thresholds'));
    }

    // Elimina un threshold para RRHH (vía AJAX con SweetAlert2)
    public function destroyRecursosHumanos($id)
    {
         $threshold = RecursosHumanosThreshold::findOrFail($id);
         $threshold->delete();
         return response()->json(['message' => 'Threshold de Recursos Humanos eliminado exitosamente.'], 200);
    }
}
