<?php

namespace App\Utils;

class FileHelper
{
    /**
     * Format file size in human readable format
     */
    public static function formatFileSize($bytes)
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get file extension from filename
     */
    public static function getFileExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Get file icon based on file extension
     */
    public static function getFileIcon($filename)
    {
        $extension = strtolower(self::getFileExtension($filename));
        
        $icons = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'ppt' => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'txt' => 'fas fa-file-alt text-secondary',
            'jpg' => 'fas fa-file-image text-info',
            'jpeg' => 'fas fa-file-image text-info',
            'png' => 'fas fa-file-image text-info',
            'gif' => 'fas fa-file-image text-info',
            'zip' => 'fas fa-file-archive text-dark',
            'rar' => 'fas fa-file-archive text-dark',
        ];

        return $icons[$extension] ?? 'fas fa-file text-muted';
    }

    /**
     * Check if file is image
     */
    public static function isImage($filename)
    {
        $extension = strtolower(self::getFileExtension($filename));
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        
        return in_array($extension, $imageExtensions);
    }

    /**
     * Check if file is document
     */
    public static function isDocument($filename)
    {
        $extension = strtolower(self::getFileExtension($filename));
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        
        return in_array($extension, $documentExtensions);
    }

    /**
     * Validate file size
     */
    public static function validateFileSize($fileSize, $maxSizeInMB = 10)
    {
        $maxSizeInBytes = $maxSizeInMB * 1024 * 1024;
        return $fileSize <= $maxSizeInBytes;
    }

    /**
     * Generate unique filename
     */
    public static function generateUniqueFilename($originalFilename)
    {
        $extension = self::getFileExtension($originalFilename);
        $nameWithoutExtension = pathinfo($originalFilename, PATHINFO_FILENAME);
        $timestamp = time();
        $random = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        return $nameWithoutExtension . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
}
