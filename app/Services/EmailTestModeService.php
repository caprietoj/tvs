<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmailTestModeService
{
    /**
     * Check if email test mode is enabled
     */
    public static function isTestModeEnabled(): bool
    {
        // Si ya estamos usando configuración de prueba (section-mail-test), 
        // NO necesitamos interceptar porque los correos ya son de prueba
        if (\App\Services\DynamicSectionEmailsService::isTestingMode()) {
            return false; // No interceptar cuando ya usamos section-mail-test.php
        }
        
        // Intentar usar config() primero, luego env() como fallback
        $configValue = config('app.email_test_mode');
        if ($configValue !== null) {
            return $configValue === true || $configValue === 'true';
        }
        
        // Fallback a env() si no hay configuración cacheada
        return env('EMAIL_TEST_MODE', false) === true || env('EMAIL_TEST_MODE', false) === 'true';
    }

    /**
     * Get test email for a section
     */
    public static function getTestEmail(string $section = 'general'): string
    {
        $testEmails = [
            'sistemas' => config('app.email_test_sistemas', env('EMAIL_TEST_SISTEMAS', 'test-sistemas@tvs.edu.co')),
            'pai' => config('app.email_test_pai', env('EMAIL_TEST_PAI', 'test-pai@tvs.edu.co')),
            'compras' => 'test-compras@tvs.edu.co',
            'contabilidad' => 'test-contabilidad@tvs.edu.co',
            'tesoreria' => 'test-tesoreria@tvs.edu.co',
            'auxiliar' => 'test-auxiliar@tvs.edu.co',
            'administracion' => 'test-admin@tvs.edu.co',
            'general' => 'test-general@tvs.edu.co',
        ];

        $sectionLower = strtolower($section);
        
        return $testEmails[$sectionLower] ?? $testEmails['general'];
    }

    /**
     * Intercept email address if test mode is enabled
     */
    public static function interceptEmail(string $originalEmail): string
    {
        if (!self::isTestModeEnabled()) {
            return $originalEmail;
        }

        // Determine section based on original email
        $section = 'general';
        
        if (strpos($originalEmail, 'sistemas') !== false || strpos($originalEmail, 'jefesistemas') !== false) {
            $section = 'sistemas';
        } elseif (strpos($originalEmail, 'pai') !== false || strpos($originalEmail, 'coordpai') !== false) {
            $section = 'pai';
        } elseif (strpos($originalEmail, 'compras') !== false) {
            $section = 'compras';
        } elseif (strpos($originalEmail, 'contabilidad') !== false) {
            $section = 'contabilidad';
        } elseif (strpos($originalEmail, 'tesoreria') !== false) {
            $section = 'tesoreria';
        } elseif (strpos($originalEmail, 'auxiliar') !== false) {
            $section = 'auxiliar';
        } elseif (strpos($originalEmail, 'admin') !== false) {
            $section = 'administracion';
        }

        $testEmail = self::getTestEmail($section);
        
        Log::info('Email intercepted by test mode', [
            'original_email' => $originalEmail,
            'test_email' => $testEmail,
            'section' => $section
        ]);

        return $testEmail;
    }

    /**
     * Intercept array of email addresses
     */
    public static function interceptEmails(array $emails): array
    {
        if (!self::isTestModeEnabled()) {
            return $emails;
        }

        return array_map(function($email) {
            return self::interceptEmail($email);
        }, $emails);
    }
}
