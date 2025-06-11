<?php

namespace App\Http\Controllers;

use App\Models\SistemasDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SistemasDocumentController extends Controller
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
        $documents = SistemasDocument::with('user')->latest()->get();
        return view('sistemas.documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        return view('sistemas.documents.create');
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
        $path = $file->store('public/sistemas/documents');
        
        SistemasDocument::create([
            'name' => $request->name,
            'file_path' => $path,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('sistemas.documents.index')
            ->with('success', 'Documento subido exitosamente.');
    }

    /**
     * Download the specified document.
     */
    public function download($id)
    {
        $document = SistemasDocument::findOrFail($id);
        return Storage::download($document->file_path);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $document = SistemasDocument::findOrFail($id);
        
        // Delete the file
        Storage::delete($document->file_path);
        
        // Delete the record
        $document->delete();

        return redirect()->route('sistemas.documents.index')
            ->with('success', 'Documento eliminado exitosamente.');
    }
}
