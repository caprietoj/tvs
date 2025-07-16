<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ContabilidadDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'user_id',
        'type',
        'original_filename',
        'file_count',
        'total_size',
        'folder_structure',
    ];

    protected $casts = [
        'folder_structure' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this document is a folder
     */
    public function isFolder()
    {
        return $this->type === 'folder';
    }

    /**
     * Check if this document is a single file
     */
    public function isFile()
    {
        return $this->type === 'file';
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute()
    {
        return $this->formatFileSize($this->total_size);
    }

    /**
     * Format file size in human readable format
     */
    private function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get the file extension or folder indicator
     */
    public function getExtensionAttribute()
    {
        if ($this->isFolder()) {
            return 'CARPETA';
        }
        
        if ($this->original_filename) {
            return strtoupper(pathinfo($this->original_filename, PATHINFO_EXTENSION));
        }
        
        return 'PDF';
    }
}
