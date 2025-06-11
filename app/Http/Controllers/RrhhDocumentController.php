<?php

namespace App\Http\Controllers;

use App\Models\RrhhDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RrhhDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the documents.
     */
    public function index()
    {
        $documents = RrhhDocument::with('user')->latest()->get();
        return view('rrhh.documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        return view('rrhh.documents.create');
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf|max:10240',
        ], [
            'name.required' => 'El nombre del documento es obligatorio.',
            'document.required' => 'Debe seleccionar un archivo PDF.',
            'document.mimes' => 'El archivo debe ser un PDF.',
            'document.max' => 'El archivo no puede ser mayor a 10MB.',
        ]);

        $file = $request->file('document');
        $path = $file->store('public/rrhh/documents');
        
        RrhhDocument::create([
            'name' => $request->name,
            'file_path' => $path,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('rrhh.documents.index')
            ->with('success', 'Documento subido exitosamente.');
    }

    /**
     * Download the specified document.
     */
    public function download($id)
    {
        $document = RrhhDocument::findOrFail($id);
        return Storage::download($document->file_path);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $document = RrhhDocument::findOrFail($id);
        
        // Delete the file
        Storage::delete($document->file_path);
        
        // Delete the record
        $document->delete();

        return redirect()->route('rrhh.documents.index')
            ->with('success', 'Documento eliminado exitosamente.');
    }
}
