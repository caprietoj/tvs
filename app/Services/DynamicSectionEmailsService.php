<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class DynamicSectionEmailsService
{
    /**
     * Get the current configuration source
     */
    public static function getCurrentConfigSource(): string
    {
        return env('SECTION_EMAILS_CONFIG', 'section_emails');
    }

    /**
     * Check if we're in testing mode
     */
    public static function isTestingMode(): bool
    {
        return self::getCurrentConfigSource() === 'section-mail-test';
    }

    /**
     * Get configuration value dynamically
     */
    public static function getConfig(string $key, $default = null)
    {
        $configSource = self::getCurrentConfigSource();
        $cacheKey = "dynamic_section_emails_{$configSource}_{$key}";
        
        return Cache::remember($cacheKey, 60, function () use ($configSource, $key, $default) {
            return config("{$configSource}.{$key}", $default);
        });
    }

    /**
     * Get sections configuration
     */
    public static function getSections(): array
    {
        return self::getConfig('sections', []);
    }

    /**
     * Get directors configuration
     */
    public static function getDirectors(): array
    {
        return self::getConfig('directors', []);
    }

    /**
     * Get materials approval emails
     */
    public static function getMaterialsApprovalEmails(): array
    {
        return self::getConfig('materials_approval_emails', []);
    }

    /**
     * Get always notify emails
     */
    public static function getAlwaysNotify(): array
    {
        $alwaysNotify = self::getConfig('always_notify', []);
        return is_array($alwaysNotify) ? $alwaysNotify : [$alwaysNotify];
    }

    /**
     * Get default email
     */
    public static function getDefault()
    {
        return self::getConfig('default');
    }

    /**
     * Clear cache for configuration
     */
    public static function clearCache(): void
    {
        $configSource = self::getCurrentConfigSource();
        $pattern = "dynamic_section_emails_{$configSource}_*";
        
        // Clear all cached configurations for current source
        Cache::flush();
    }
}
