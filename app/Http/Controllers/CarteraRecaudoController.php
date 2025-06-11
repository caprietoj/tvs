<?php

namespace App\Http\Controllers;

use App\Models\CarteraRecaudo;
use Illuminate\Http\Request;

class CarteraRecaudoController extends Controller
{
    public function index()
    {
        $recaudos = CarteraRecaudo::orderBy('created_at', 'desc')->get();
        return view('contabilidad.cartera.index', compact('recaudos'));
    }

    public function create()
    {
        return view('contabilidad.cartera.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mes' => 'required|string',
            'valor_recaudado' => 'required|numeric|min:0',
            'valor_facturado' => 'required|numeric|min:0',
        ]);

        CarteraRecaudo::create($validated);

        return redirect()->route('contabilidad.cartera.index')
            ->with('success', 'Registro creado exitosamente');
    }
}
