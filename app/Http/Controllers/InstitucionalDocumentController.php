<?php

namespace App\Http\Controllers;

use App\Models\InstitucionalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class InstitucionalDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            // Verificar acceso usando la lógica simplificada
            if (!$user->hasRole('admin') && $user->email !== 'asistentegeneral@tvs.edu.co') {
                abort(403, 'No tienes permisos para acceder a esta sección.');
            }
            
            return $next($request);
        });
    }

    /**
     * Display a listing of the documents.
     */
    public function index()
    {
        $documents = InstitucionalDocument::with('user')->latest()->get();
        return view('institucional.documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        return view('institucional.documents.create');
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
            $rules['document'] = 'required|file|mimes:pdf|max:10240'; // 10MB max
        } elseif ($request->hasFile('folder')) {
            $rules['folder'] = 'required|array';
            $rules['folder.*'] = 'file|max:10240'; // 10MB per file
        }

        $messages = [
            'name.required' => 'El nombre del documento es obligatorio.',
            'document.required' => 'Debe seleccionar un archivo.',
            'document.mimes' => 'Solo se permiten archivos PDF.',
            'document.max' => 'El archivo no puede ser mayor a 10MB.',
            'folder.required' => 'Debe seleccionar una carpeta.',
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
        $path = $file->store('public/institucional/documents');
        
        InstitucionalDocument::create([
            'name' => $request->name,
            'file_path' => $path,
            'user_id' => Auth::id(),
            'type' => 'file',
            'original_filename' => $file->getClientOriginalName(),
            'file_count' => 1,
            'total_size' => $file->getSize(),
        ]);

        return redirect()->route('institucional.documents.index')
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
        $basePath = 'public/institucional/documents/' . $folderName;
        
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
                
                Log::info("Procesando archivo {$index}: {$relativePath}");
                
                // Limpiar la ruta relativa para uso seguro
                $relativePath = str_replace(['../', './'], '', $relativePath);
                $relativePath = ltrim($relativePath, '/');
                
                // Crear directorios necesarios
                $directory = dirname($relativePath);
                if ($directory !== '.' && $directory !== '') {
                    $fullDirectoryPath = storage_path('app/' . $basePath . '/' . $directory);
                    if (!File::exists($fullDirectoryPath)) {
                        File::makeDirectory($fullDirectoryPath, 0755, true, true);
                        Log::info("Creado directorio: {$fullDirectoryPath}");
                    }
                }
                
                // Guardar el archivo manteniendo la estructura
                try {
                    $file->storeAs($basePath, $relativePath);
                    Log::info("Archivo guardado: {$basePath}/{$relativePath}");
                    
                    // Agregar a la estructura
                    $folderStructure[] = [
                        'name' => basename($relativePath),
                        'path' => $relativePath,
                        'size' => $file->getSize(),
                        'extension' => $file->getClientOriginalExtension(),
                        'modified' => date('Y-m-d H:i:s'),
                        'type' => $file->getMimeType(),
                    ];
                    
                    $fileCount++;
                    
                } catch (\Exception $e) {
                    Log::error("Error procesando archivo {$relativePath}: " . $e->getMessage());
                }
            }
            
            if ($fileCount === 0) {
                return redirect()->back()->with('error', 'No se pudo procesar ningún archivo de la carpeta.');
            }
            
            Log::info("Guardando en BD: {$fileCount} archivos procesados, tamaño total: {$totalSize}");
            
            // Guardar en base de datos
            InstitucionalDocument::create([
                'name' => $request->name,
                'file_path' => $basePath,
                'user_id' => Auth::id(),
                'type' => 'folder',
                'original_filename' => $rootFolderName,
                'file_count' => $fileCount,
                'total_size' => $totalSize,
                'folder_structure' => $folderStructure,
            ]);
            
            return redirect()->route('institucional.documents.index')
                ->with('success', "Carpeta '{$rootFolderName}' subida exitosamente. {$fileCount} archivos procesados.");
                
        } catch (\Exception $e) {
            // Limpiar en caso de error
            $fullPath = storage_path('app/' . $basePath);
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
            }
            
            Log::error("Error procesando carpeta: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al procesar la carpeta: ' . $e->getMessage());
        }
    }

    /**
     * Show the structure of a folder.
     */
    public function showStructure($id)
    {
        $document = InstitucionalDocument::findOrFail($id);
        
        if (!$document->isFolder()) {
            return redirect()->route('institucional.documents.index')
                ->with('error', 'Este documento no es una carpeta.');
        }
        
        return view('institucional.documents.structure', compact('document'));
    }

    /**
     * Download the specified document.
     */
    public function download($id)
    {
        $document = InstitucionalDocument::findOrFail($id);
        
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
        $folderPath = storage_path('app/' . $document->file_path);
        
        if (!File::exists($folderPath)) {
            abort(404, 'La carpeta no existe.');
        }
        
        // Crear ZIP temporal
        $zipName = $document->name . '.zip';
        $tempZipPath = storage_path('app/temp/' . $zipName);
        
        // Crear directorio temp si no existe
        File::makeDirectory(dirname($tempZipPath), 0755, true, true);
        
        $zip = new ZipArchive;
        $result = $zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($result !== TRUE) {
            abort(500, 'No se pudo crear el archivo ZIP.');
        }
        
        // Agregar archivos al ZIP
        $files = File::allFiles($folderPath);
        foreach ($files as $file) {
            $relativePath = str_replace($folderPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);
            $zip->addFile($file->getPathname(), $relativePath);
        }
        
        $zip->close();
        
        // Descargar y limpiar
        return response()->download($tempZipPath, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $document = InstitucionalDocument::findOrFail($id);
        
        if ($document->isFile()) {
            // Eliminar archivo único
            Storage::delete($document->file_path);
        } else {
            // Eliminar carpeta completa
            $folderPath = storage_path('app/' . $document->file_path);
            if (File::exists($folderPath)) {
                File::deleteDirectory($folderPath);
            }
        }
        
        // Eliminar registro de base de datos
        $document->delete();

        return redirect()->route('institucional.documents.index')
            ->with('success', 'Documento eliminado exitosamente.');
    }
}
