<?php

namespace App\Http\Controllers;

use App\Models\EnfermeriaDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class EnfermeriaDocumentController extends Controller
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
        $documents = EnfermeriaDocument::with('user')->latest()->get();
        return view('enfermeria.documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        return view('enfermeria.documents.create');
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        // Validación dinámica basada en el tipo de subida
        $rules = [
            'name' => 'required|string|max:255',
        ];

        if ($request->hasFile('document')) {
            $rules['document'] = 'required|file|mimes:pdf|max:10240';
        } elseif ($request->hasFile('folder')) {
            $rules['folder'] = 'required|array|min:1';
            $rules['folder.*'] = 'file|max:10240'; // 10MB por archivo individual
            $rules['folder_paths'] = 'array';
            $rules['folder_paths.*'] = 'string';
        }

        $messages = [
            'name.required' => 'El nombre del documento es obligatorio.',
            'document.required' => 'Debe seleccionar un archivo PDF.',
            'document.mimes' => 'El archivo debe ser un PDF.',
            'document.max' => 'El archivo no puede ser mayor a 10MB.',
            'folder.required' => 'Debe seleccionar una carpeta.',
            'folder.array' => 'La carpeta debe contener archivos.',
            'folder.min' => 'La carpeta debe contener al menos un archivo.',
            'folder.*.file' => 'Todos los elementos deben ser archivos válidos.',
            'folder.*.max' => 'Cada archivo no puede ser mayor a 10MB.',
        ];

        $request->validate($rules, $messages);

        if ($request->hasFile('document')) {
            return $this->storeSingleFile($request);
        } elseif ($request->hasFile('folder')) {
            return $this->storeFolder($request);
        }

        return redirect()->back()->with('error', 'Debe seleccionar un archivo o carpeta.');
    }

    /**
     * Store a single PDF file
     */
    private function storeSingleFile(Request $request)
    {
        $file = $request->file('document');
        $path = $file->store('public/enfermeria/documents');
        
        EnfermeriaDocument::create([
            'name' => $request->name,
            'file_path' => $path,
            'user_id' => Auth::id(),
            'type' => 'file',
            'original_filename' => $file->getClientOriginalName(),
            'file_count' => 1,
            'total_size' => $file->getSize(),
        ]);

        return redirect()->route('enfermeria.documents.index')
            ->with('success', 'Documento subido exitosamente.');
    }

    /**
     * Store a folder (multiple files from folder selection)
     */
    private function storeFolder(Request $request)
    {
        $files = $request->file('folder');
        
        if (empty($files)) {
            return redirect()->back()->with('error', 'No se seleccionaron archivos.');
        }
        
        // Obtener las rutas relativas de los inputs hidden
        $folderPaths = $request->input('folder_paths', []);
        
        // Crear directorio único para esta carpeta
        $folderName = str_replace(' ', '_', $request->name) . '_' . time();
        $basePath = 'public/enfermeria/documents/' . $folderName;
        
        try {
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
            
            Log::info("Procesando carpeta: {$rootFolderName} con " . count($files) . " archivos");
            
            // Procesar cada archivo
            foreach ($files as $index => $file) {
                // Obtener la ruta relativa del input hidden
                $relativePath = $folderPaths[$index] ?? $file->getClientOriginalName();
                
                Log::info("Procesando archivo $index: " . $file->getClientOriginalName() . " -> $relativePath");
                
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
            
            // Crear registro en la base de datos
            EnfermeriaDocument::create([
                'name' => $request->name,
                'file_path' => $basePath,
                'user_id' => Auth::id(),
                'type' => 'folder',
                'original_filename' => $rootFolderName,
                'file_count' => $fileCount,
                'total_size' => $totalSize,
                'folder_structure' => $folderStructure,
            ]);

            Log::info("Carpeta guardada exitosamente con $fileCount archivos y tamaño total: $totalSize bytes");

            return redirect()->route('enfermeria.documents.index')
                ->with('success', "Carpeta subida exitosamente. $fileCount archivos procesados.");

        } catch (\Exception $e) {
            Log::error('Error al subir carpeta: ' . $e->getMessage());
            
            // Limpiar archivos parciales en caso de error
            if (Storage::exists($basePath)) {
                Storage::deleteDirectory($basePath);
            }
            
            return redirect()->back()->with('error', 'Error al subir la carpeta: ' . $e->getMessage());
        }
    }

    /**
     * Show the structure of a folder.
     */
    public function showStructure($id)
    {
        $document = EnfermeriaDocument::findOrFail($id);
        
        if (!$document->isFolder()) {
            return redirect()->route('enfermeria.documents.index')
                ->with('error', 'Este documento no es una carpeta.');
        }
        
        return view('enfermeria.documents.structure', compact('document'));
    }

    /**
     * Download the specified document.
     */
    public function download($id)
    {
        $document = EnfermeriaDocument::findOrFail($id);
        
        if ($document->isFile()) {
            return Storage::download($document->file_path, $document->original_filename ?? $document->name . '.pdf');
        } else {
            // Para carpetas, crear y descargar ZIP
            return $this->downloadFolderAsZip($document);
        }
    }

    /**
     * Download folder as ZIP
     */
    private function downloadFolderAsZip($document)
    {
        $zipFileName = $document->name . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Crear directorio temporal si no existe
        if (!File::exists(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }
        
        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $this->addFolderToZip($zip, $document->file_path, '');
            $zip->close();
            
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }
        
        return redirect()->back()->with('error', 'Error al crear el archivo ZIP.');
    }

    /**
     * Add folder contents to ZIP recursively
     */
    private function addFolderToZip($zip, $folderPath, $zipPath)
    {
        $files = Storage::allFiles($folderPath);
        
        foreach ($files as $file) {
            $relativePath = str_replace($folderPath . '/', '', $file);
            if (!empty($zipPath)) {
                $relativePath = $zipPath . '/' . $relativePath;
            }
            
            $zip->addFile(Storage::path($file), $relativePath);
        }
    }

    /**
     * Preview a document or folder structure
     */
    public function preview($id)
    {
        $document = EnfermeriaDocument::with('user')->findOrFail($id);
        
        if ($document->isFile()) {
            // Para archivos individuales, mostrar PDF en viewer
            $filePath = Storage::url($document->file_path);
            return view('enfermeria.documents.preview', compact('document', 'filePath'));
        } else {
            // Para carpetas, mostrar estructura de archivos
            $structure = $this->buildFolderStructure($document->file_path);
            return view('enfermeria.documents.folder-preview', compact('document', 'structure'));
        }
    }

    /**
     * Build folder structure for preview
     */
    private function buildFolderStructure($basePath, $relativePath = '')
    {
        $structure = [
            'folders' => [],
            'files' => [],
            'allFiles' => [] // Array plano con todos los archivos recursivamente
        ];
        
        $currentPath = $relativePath ? $basePath . '/' . $relativePath : $basePath;
        $directories = Storage::directories($currentPath);
        $files = Storage::files($currentPath);
        
        // Procesar carpetas recursivamente
        foreach ($directories as $directory) {
            $folderName = basename($directory);
            $folderRelativePath = $relativePath ? $relativePath . '/' . $folderName : $folderName;
            
            $subStructure = $this->buildFolderStructure($basePath, $folderRelativePath);
            $structure['folders'][$folderName] = $subStructure;
            
            // Agregar archivos de subcarpetas al array plano
            $structure['allFiles'] = array_merge($structure['allFiles'], $subStructure['allFiles']);
        }
        
        // Procesar archivos del nivel actual
        foreach ($files as $file) {
            $fileName = basename($file);
            $fileSize = Storage::size($file);
            $fileUrl = Storage::url($file);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $fileData = [
                'name' => $fileName,
                'size' => $this->formatBytes($fileSize),
                'sizeBytes' => $fileSize,
                'url' => $fileUrl,
                'path' => $file,
                'relativePath' => str_replace($basePath . '/', '', $file),
                'extension' => $extension,
                'folder' => $relativePath ?: 'Raíz'
            ];
            
            $structure['files'][] = $fileData;
            $structure['allFiles'][] = $fileData;
        }
        
        return $structure;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $document = EnfermeriaDocument::findOrFail($id);
        
        // Delete the file or folder
        if ($document->isFile()) {
            Storage::delete($document->file_path);
        } else {
            Storage::deleteDirectory($document->file_path);
        }
        
        // Delete the record
        $document->delete();

        return redirect()->route('enfermeria.documents.index')
            ->with('success', 'Documento eliminado exitosamente.');
    }
}
