<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function storeFile(UploadedFile $file, string $path): string
    {
        $filename = $this->generateUniqueFilename($file);
        return $file->storeAs($path, $filename, 'public');
    }
    
    public function deleteFile(string $path): bool
    {
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }
        
        return false;
    }
    
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp;
        $random = Str::random(8);
        
        return "{$timestamp}_{$random}.{$extension}";
    }
    
    public function getFullUrl(string $path): string
    {
        return Storage::url($path);
    }
}
