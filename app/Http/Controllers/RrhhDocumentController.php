<?php

namespace App\Http\Controllers;

use App\Models\RrhhDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Support\Str;

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
        // Determine if this is a folder or file upload
        $isFolder = $request->input('is_folder', 0);
        
        if ($isFolder) {
            return $this->storeFolder($request);
        } else {
            return $this->storeFile($request);
        }
    }

    /**
     * Store a single file
     */
    private function storeFile(Request $request)
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
            'type' => 'file',
            'original_filename' => $file->getClientOriginalName(),
            'file_count' => 1,
            'total_size' => $file->getSize(),
        ]);

        return redirect()->route('rrhh.documents.index')
            ->with('success', 'Documento subido exitosamente.');
    }

    /**
     * Store a folder with multiple files
     */
    private function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'folder.*' => 'required|file|max:10240',
        ], [
            'name.required' => 'El nombre del documento es obligatorio.',
            'folder.*.required' => 'Debe seleccionar archivos.',
            'folder.*.max' => 'Cada archivo no puede ser mayor a 10MB.',
        ]);

        $files = $request->file('folder');
        $folderPaths = $request->input('folder_paths', []);
        
        if (!$files || count($files) === 0) {
            return back()->withErrors(['folder' => 'Debe seleccionar al menos un archivo.']);
        }

        // Create a unique folder for this upload
        $folderName = Str::slug($request->name) . '_' . time();
        $basePath = 'rrhh/documents/' . $folderName;
        
        $totalSize = 0;
        $fileCount = 0;
        $folderStructure = [];
        
        // Calcular tamaño total primero
        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }
        
        // Validar tamaño total (100MB máximo)
        $maxTotalSize = 100 * 1024 * 1024; // 100MB
        if ($totalSize > $maxTotalSize) {
            return redirect()->back()->with('error', 'El tamaño total de la carpeta excede los 100MB permitidos.');
        }
        
        // Obtener el nombre de la carpeta raíz
        $rootFolderName = 'Archivos';
        if (!empty($folderPaths)) {
            $firstPath = $folderPaths[0] ?? '';
            $pathParts = explode('/', $firstPath);
            if (count($pathParts) > 0) {
                $rootFolderName = $pathParts[0];
            }
        }
        
        // Procesar cada archivo
        foreach ($files as $index => $file) {
            // Obtener la ruta relativa del input hidden
            $relativePath = $folderPaths[$index] ?? $file->getClientOriginalName();
            
            // Crear estructura completa del archivo
            $fullPath = $basePath . '/' . $relativePath;
            
            // Crear directorio si no existe
            $directory = dirname($fullPath);
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
            
            // Guardar archivo
            $file->storeAs($directory, basename($relativePath));
            
            $fileCount++;
            
            // Agregar a la estructura de carpetas
            $pathParts = explode('/', $relativePath);
            $currentLevel = &$folderStructure;
            
            foreach ($pathParts as $i => $part) {
                if ($i === count($pathParts) - 1) {
                    // Es un archivo
                    $currentLevel['files'][] = [
                        'name' => $part,
                        'size' => $file->getSize(),
                        'path' => $relativePath
                    ];
                } else {
                    // Es una carpeta
                    if (!isset($currentLevel['folders'][$part])) {
                        $currentLevel['folders'][$part] = [
                            'folders' => [],
                            'files' => []
                        ];
                    }
                    $currentLevel = &$currentLevel['folders'][$part];
                }
            }
        }

        // Create document record
        RrhhDocument::create([
            'name' => $request->name,
            'file_path' => $basePath,
            'user_id' => Auth::id(),
            'type' => 'folder',
            'original_filename' => $rootFolderName,
            'file_count' => $fileCount,
            'total_size' => $totalSize,
            'folder_structure' => $folderStructure,
        ]);

        return redirect()->route('rrhh.documents.index')
            ->with('success', "Carpeta subida exitosamente con {$fileCount} archivos.");
    }

    /**
     * Show folder structure
     */
    public function showStructure($id)
    {
        $document = RrhhDocument::findOrFail($id);
        
        if (!$document->isFolder()) {
            abort(404);
        }
        
        return view('rrhh.documents.structure', compact('document'));
    }

    /**
     * Download a folder as ZIP
     */
    public function downloadFolderAsZip($id)
    {
        try {
            $document = RrhhDocument::findOrFail($id);
            
            if (!$document->isFolder()) {
                abort(404, 'Document is not a folder');
            }

            $zip = new ZipArchive();
            $zipFileName = storage_path('app/temp/' . Str::slug($document->name) . '_' . time() . '.zip');
            
            // Create temp directory if it doesn't exist
            if (!file_exists(dirname($zipFileName))) {
                mkdir(dirname($zipFileName), 0755, true);
            }

            if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
                abort(500, 'Cannot create zip file');
            }

            $this->addFolderToZip($zip, $document->folder_structure, '', $document->file_path);
            $zip->close();

            return response()->download($zipFileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error creating ZIP file: ' . $e->getMessage());
            abort(500, 'Error creating ZIP file: ' . $e->getMessage());
        }
    }

    /**
     * Recursively add folder contents to ZIP
     */
    private function addFolderToZip($zip, $structure, $basePath, $documentPath = '')
    {
        // Add files in current level
        if (isset($structure['files'])) {
            foreach ($structure['files'] as $file) {
                $filePath = $basePath . $file['name'];
                $fullPath = storage_path('app/' . $documentPath . '/' . $file['path']);
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $filePath);
                }
            }
        }
        
        // Add subfolders
        if (isset($structure['folders'])) {
            foreach ($structure['folders'] as $folderName => $folderData) {
                $folderPath = $basePath . $folderName . '/';
                $zip->addEmptyDir($basePath . $folderName);
                $this->addFolderToZip($zip, $folderData, $folderPath, $documentPath);
            }
        }
    }

    /**
     * Download the specified document.
     */
    public function download($id)
    {
        try {
            $document = RrhhDocument::findOrFail($id);
            
            if ($document->isFolder()) {
                return $this->downloadFolderAsZip($id);
            }
            
            // Check if file exists before attempting download
            if (!Storage::exists($document->file_path)) {
                abort(404, 'File not found');
            }
            
            return Storage::download($document->file_path);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error downloading document: ' . $e->getMessage());
            abort(500, 'Error downloading file: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $document = RrhhDocument::findOrFail($id);
        
        if ($document->isFolder()) {
            // Delete entire folder
            Storage::deleteDirectory($document->file_path);
        } else {
            // Delete single file
            Storage::delete($document->file_path);
        }
        
        // Delete the record
        $document->delete();

        return redirect()->route('rrhh.documents.index')
            ->with('success', 'Documento eliminado exitosamente.');
    }
}
