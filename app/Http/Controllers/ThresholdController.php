<?php

namespace App\Http\Controllers;

use App\Models\Threshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThresholdController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra el formulario para crear un nuevo threshold para el área de Enfermería.
     */
    public function createEnfermeria()
    {
        return view('threshold.enfermeria.create');
    }

    /**
     * Almacena el threshold creado para el área de Enfermería.
     */
    public function storeEnfermeria(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kpi_name' => 'required|string|max:255',
            'value'    => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Threshold::create([
            'area'     => 'enfermeria',
            'kpi_name' => $request->kpi_name,
            'value'    => $request->value,
        ]);

        // Redirige a la vista que muestra todos los thresholds configurados
        return redirect()->route('umbral.enfermeria.show')->with('success', 'Ubmral creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar el threshold para el área de Enfermería.
     * Se asume que se desea editar el threshold principal (por ejemplo, el primero) para Enfermería.
     */
    public function editEnfermeria($id)
    {
        $threshold = Threshold::findOrFail($id);
        return view('threshold.enfermeria.edit', compact('threshold'));
    }

    /**
     * Actualiza el threshold para el área de Enfermería.
     */
    public function updateEnfermeria(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kpi_name' => 'required|string|max:255',
            'value'    => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $threshold = Threshold::findOrFail($id);
        $threshold->update([
            'kpi_name' => $request->kpi_name,
            'value'    => $request->value,
        ]);

        // Redirige a la vista que muestra todos los thresholds configurados
        return redirect()->route('umbral.enfermeria.show')->with('success', 'Umbral actualizado exitosamente.');
    }

    /**
     * Muestra en una tabla todos los thresholds configurados para el área de Enfermería.
     * Esta vista debe integrarse con DataTables y SweetAlert2 para gestionar las acciones.
     */
    public function showEnfermeria()
    {
        $thresholds = Threshold::where('area', 'enfermeria')->get();
        return view('threshold.enfermeria.show', compact('thresholds'));
    }

    /**
     * Elimina un threshold para el área de Enfermería.
     * Este método devuelve una respuesta JSON para integrarlo con SweetAlert2.
     */
    public function destroyEnfermeria($id)
    {
        $threshold = Threshold::findOrFail($id);
        $threshold->delete();
        return response()->json(['message' => 'Umbral eliminado exitosamente.'], 200);
    }
}
