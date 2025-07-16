<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EnfermeriaDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'type',
        'original_filename',
        'file_count',
        'total_size',
        'folder_structure',
        'user_id',
    ];

    protected $casts = [
        'folder_structure' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isFolder()
    {
        return $this->type === 'folder';
    }

    public function isFile()
    {
        return $this->type === 'file';
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->total_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
