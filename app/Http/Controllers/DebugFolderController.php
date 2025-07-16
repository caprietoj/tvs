<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebugFolderController extends Controller
{
    public function debug(Request $request)
    {
        \Log::info('=== DEBUG FOLDER UPLOAD ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Has file folder: ' . ($request->hasFile('folder') ? 'YES' : 'NO'));
        
        if ($request->hasFile('folder')) {
            $files = $request->file('folder');
            \Log::info('Number of files: ' . count($files));
            
            foreach ($files as $index => $file) {
                \Log::info("File {$index}:");
                \Log::info("  - Original name: " . $file->getClientOriginalName());
                \Log::info("  - Size: " . $file->getSize());
                \Log::info("  - MIME: " . $file->getMimeType());
                \Log::info("  - Extension: " . $file->getClientOriginalExtension());
            }
        }
        
        \Log::info('Folder structure data: ' . $request->input('folder_structure_data', 'EMPTY'));
        
        $structureData = json_decode($request->input('folder_structure_data', '[]'), true);
        if (!empty($structureData)) {
            \Log::info('Parsed structure data:');
            foreach ($structureData as $index => $item) {
                \Log::info("  Item {$index}: " . json_encode($item));
            }
        }
        
        return response()->json([
            'status' => 'debug_complete',
            'has_files' => $request->hasFile('folder'),
            'file_count' => $request->hasFile('folder') ? count($request->file('folder')) : 0,
            'structure_data_length' => strlen($request->input('folder_structure_data', ''))
        ]);
    }
}
