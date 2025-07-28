<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class InstitucionalDocument extends Model
{
    use HasFactory;

    protected $table = 'institucional_documents';

    protected $fillable = [
        'name',
        'file_path',
        'user_id',
        'type',
        'original_filename',
        'file_count',
        'total_size',
        'folder_structure'
    ];

    protected $casts = [
        'folder_structure' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that uploaded the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the document is a file.
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    /**
     * Check if the document is a folder.
     */
    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->total_size ?? 0;
        
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get the file extension from original filename.
     */
    public function getFileExtensionAttribute(): string
    {
        if ($this->isFolder()) {
            return 'folder';
        }

        return pathinfo($this->original_filename ?? '', PATHINFO_EXTENSION) ?: 'pdf';
    }
}
