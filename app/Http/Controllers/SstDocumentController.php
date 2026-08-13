<?php

namespace App\Http\Controllers;

use App\Models\SstDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Support\Str;

class SstDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $documents = SstDocument::with('user')->latest()->get();
        return view('sst.documents.index', compact('documents'));
    }

    public function create()
    {
        return view('sst.documents.create');
    }

    public function store(Request $request)
    {
        $isFolder = $request->input('is_folder', 0);
        if ($isFolder) {
            return $this->storeFolder($request);
        } else {
            return $this->storeFile($request);
        }
    }

    private function storeFile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
        ], [
            'name.required' => 'El nombre del documento es obligatorio.',
            'document.required' => 'Debe seleccionar un archivo.',
            'document.max' => 'El archivo no puede ser mayor a 10MB.',
        ]);

        $file = $request->file('document');
        $path = $file->store('public/sst/documents');

        SstDocument::create([
            'name' => $request->name,
            'file_path' => $path,
            'user_id' => Auth::id(),
            'type' => 'file',
            'original_filename' => $file->getClientOriginalName(),
            'file_count' => 1,
            'total_size' => $file->getSize(),
        ]);

        return redirect()->route('sst.documents.index')
            ->with('success', 'Documento subido exitosamente.');
    }

    private function storeFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'folder.*' => 'required|file|max:10240',
        ]);

        $files = $request->file('folder');
        $folderPaths = $request->input('folder_paths', []);

        if (!$files || count($files) === 0) {
            return back()->withErrors(['folder' => 'Debe seleccionar al menos un archivo.']);
        }

        $folderName = Str::slug($request->name) . '_' . time();
        $basePath = 'sst/documents/' . $folderName;

        $totalSize = 0;
        $fileCount = 0;
        $folderStructure = [];

        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }

        $maxTotalSize = 100 * 1024 * 1024;
        if ($totalSize > $maxTotalSize) {
            return redirect()->back()->with('error', 'El tamaño total de la carpeta excede los 100MB permitidos.');
        }

        $rootFolderName = 'Archivos';
        if (!empty($folderPaths)) {
            $firstPath = $folderPaths[0] ?? '';
            $pathParts = explode('/', $firstPath);
            if (count($pathParts) > 0) {
                $rootFolderName = $pathParts[0];
            }
        }

        foreach ($files as $index => $file) {
            $relativePath = $folderPaths[$index] ?? $file->getClientOriginalName();
            $fullPath = $basePath . '/' . $relativePath;
            $directory = dirname($fullPath);
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
            $file->storeAs($directory, basename($relativePath));
            $fileCount++;

            $pathParts = explode('/', $relativePath);
            $currentLevel = &$folderStructure;
            foreach ($pathParts as $i => $part) {
                if ($i === count($pathParts) - 1) {
                    $currentLevel['files'][] = ['name' => $part, 'size' => $file->getSize(), 'path' => $relativePath];
                } else {
                    if (!isset($currentLevel['folders'][$part])) {
                        $currentLevel['folders'][$part] = ['folders' => [], 'files' => []];
                    }
                    $currentLevel = &$currentLevel['folders'][$part];
                }
            }
        }

        SstDocument::create([
            'name' => $request->name,
            'file_path' => $basePath,
            'user_id' => Auth::id(),
            'type' => 'folder',
            'original_filename' => $rootFolderName,
            'file_count' => $fileCount,
            'total_size' => $totalSize,
            'folder_structure' => $folderStructure,
        ]);

        return redirect()->route('sst.documents.index')
            ->with('success', "Carpeta subida exitosamente con {$fileCount} archivos.");
    }

    public function showStructure($id)
    {
        $document = SstDocument::findOrFail($id);
        if (!$document->isFolder()) abort(404);
        return view('sst.documents.structure', compact('document'));
    }

    public function download($id)
    {
        $document = SstDocument::findOrFail($id);
        if ($document->isFolder()) {
            return $this->downloadFolderAsZip($id);
        }
        if (!Storage::exists($document->file_path)) abort(404, 'File not found');
        return Storage::download($document->file_path);
    }

    private function downloadFolderAsZip($id)
    {
        $document = SstDocument::findOrFail($id);
        $zip = new ZipArchive();
        $zipFileName = storage_path('app/temp/' . Str::slug($document->name) . '_' . time() . '.zip');
        if (!file_exists(dirname($zipFileName))) mkdir(dirname($zipFileName), 0755, true);
        if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) abort(500, 'Cannot create zip file');
        $this->addFolderToZip($zip, $document->folder_structure, '', $document->file_path);
        $zip->close();
        return response()->download($zipFileName)->deleteFileAfterSend(true);
    }

    private function addFolderToZip($zip, $structure, $basePath, $documentPath = '')
    {
        if (isset($structure['files'])) {
            foreach ($structure['files'] as $file) {
                $filePath = $basePath . $file['name'];
                $fullPath = storage_path('app/' . $documentPath . '/' . $file['path']);
                if (file_exists($fullPath)) $zip->addFile($fullPath, $filePath);
            }
        }
        if (isset($structure['folders'])) {
            foreach ($structure['folders'] as $folderName => $folderData) {
                $zip->addEmptyDir($basePath . $folderName);
                $this->addFolderToZip($zip, $folderData, $basePath . $folderName . '/', $documentPath);
            }
        }
    }

    public function preview($id)
    {
        $document = SstDocument::with('user')->findOrFail($id);
        if ($document->isFile()) {
            $filePath = Storage::url($document->file_path);
            return view('sst.documents.preview', compact('document', 'filePath'));
        } else {
            $structure = $this->buildFolderStructure($document->file_path);
            return view('sst.documents.folder-preview', compact('document', 'structure'));
        }
    }

    private function buildFolderStructure($basePath, $relativePath = '')
    {
        $structure = ['folders' => [], 'files' => [], 'allFiles' => []];
        $currentPath = $relativePath ? $basePath . '/' . $relativePath : $basePath;
        foreach (Storage::directories($currentPath) as $directory) {
            $folderName = basename($directory);
            $folderRelativePath = $relativePath ? $relativePath . '/' . $folderName : $folderName;
            $subStructure = $this->buildFolderStructure($basePath, $folderRelativePath);
            $structure['folders'][$folderName] = $subStructure;
            $structure['allFiles'] = array_merge($structure['allFiles'], $subStructure['allFiles']);
        }
        foreach (Storage::files($currentPath) as $file) {
            $fileName = basename($file);
            $fileSize = Storage::size($file);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileData = [
                'name' => $fileName, 'size' => $this->formatBytes($fileSize), 'sizeBytes' => $fileSize,
                'url' => Storage::url($file), 'path' => $file,
                'relativePath' => str_replace($basePath . '/', '', $file),
                'extension' => $extension, 'folder' => $relativePath ?: 'Raíz'
            ];
            $structure['files'][] = $fileData;
            $structure['allFiles'][] = $fileData;
        }
        return $structure;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024; $i++) $bytes /= 1024;
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function destroy($id)
    {
        $document = SstDocument::findOrFail($id);
        if ($document->isFolder()) {
            Storage::deleteDirectory($document->file_path);
        } else {
            Storage::delete($document->file_path);
        }
        $document->delete();
        return redirect()->route('sst.documents.index')->with('success', 'Documento eliminado exitosamente.');
    }
}
