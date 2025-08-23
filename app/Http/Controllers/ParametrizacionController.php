<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParametrizacionController extends Controller
{
    /**
     * Display the parametrizacion index page.
     */
    public function index()
    {
        return view('parametrizacion.index');
    }

    /**
     * Store parametrizacion data.
     */
    public function store(Request $request)
    {
        // TODO: Implement parametrizacion store logic
        return redirect()->route('parametrizacion.index')
            ->with('success', 'Parametrización guardada correctamente.');
    }

    /**
     * Reset the system.
     */
    public function resetearSistema(Request $request)
    {
        // TODO: Implement system reset logic
        return redirect()->route('parametrizacion.index')
            ->with('success', 'Sistema reseteado correctamente.');
    }
}