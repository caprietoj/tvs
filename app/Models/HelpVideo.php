<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'video_url',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope para obtener solo videos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener el embed URL para YouTube
     */
    public function getEmbedUrlAttribute()
    {
        $url = $this->video_url;
        
        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }
        
        return $url;
    }

    /**
     * Obtener el thumbnail de YouTube
     */
    public function getThumbnailAttribute()
    {
        $url = $this->video_url;
        
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
        }
        
        return null;
    }

    /**
     * Obtener el proveedor del video (YouTube, Vimeo, etc.)
     */
    public function getVideoProvider()
    {
        $url = $this->video_url;
        
        // YouTube
        if (preg_match('/(?:youtube\.com|youtu\.be)/', $url)) {
            return 'YouTube';
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com/', $url)) {
            return 'Vimeo';
        }
        
        // Dailymotion
        if (preg_match('/dailymotion\.com/', $url)) {
            return 'Dailymotion';
        }
        
        // Facebook
        if (preg_match('/facebook\.com/', $url)) {
            return 'Facebook';
        }
        
        // Instagram
        if (preg_match('/instagram\.com/', $url)) {
            return 'Instagram';
        }
        
        // TikTok
        if (preg_match('/tiktok\.com/', $url)) {
            return 'TikTok';
        }
        
        return 'Otro';
    }
}
