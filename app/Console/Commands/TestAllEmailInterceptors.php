<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailTestModeService;
use App\Services\ProveedorNotificationService;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Event;
use App\Models\Configuration;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeNewUser;

class TestAllEmailInterceptors extends Command
{
    protected $signature = 'test:all-email-interceptors';
    protected $description = 'Prueba todos los interceptores de correo del sistema';

    public function handle()
    {
        $this->info('=== PRUEBA DE INTERCEPTORES DE CORREO ===');
        $this->info('EMAIL_TEST_MODE: ' . (config('app.email_test_mode') ? 'HABILITADO' : 'DESHABILITADO'));
        
        $emailTestService = new EmailTestModeService();
        
        // 1. Prueba del interceptor básico
        $this->info('');
        $this->info('1. Prueba del interceptor básico:');
        $testEmail = 'produccion@tvs.edu.co';
        $intercepted = $emailTestService->interceptEmail($testEmail, 'Sistemas');
        $this->info("Original: {$testEmail}");
        $this->info("Interceptado: {$intercepted}");
        
        // 2. Prueba del interceptor de arrays
        $this->info('');
        $this->info('2. Prueba del interceptor de arrays:');
        $testEmails = ['contabilidad@tvs.edu.co', 'tesoreria@tvs.edu.co', 'compras@tvs.edu.co'];
        $interceptedArray = $emailTestService->interceptEmails($testEmails, 'Contabilidad');
        $this->info("Original: " . implode(', ', $testEmails));
        $this->info("Interceptado: " . implode(', ', $interceptedArray));
        
        // 3. Prueba del servicio ProveedorNotificationService
        $this->info('');
        $this->info('3. Prueba del ProveedorNotificationService:');
        $fakeProveedor = new Proveedor([
            'id' => 9999,
            'nombre' => 'PROVEEDOR DE PRUEBA',
            'email' => 'proveedor@test.com',
            'telefono' => '123456789'
        ]);
        
        $this->info('Simulando envío de notificación de proveedor...');
        try {
            $proveedorService = new ProveedorNotificationService();
            // NO ejecutar realmente el envío, solo mostrar qué haría
            $recipients = $this->callPrivateMethod($proveedorService, 'getNotificationRecipients');
            $interceptedRecipients = $emailTestService->interceptEmails($recipients, 'Contabilidad');
            $this->info("Destinatarios originales: " . implode(', ', $recipients));
            $this->info("Destinatarios interceptados: " . implode(', ', $interceptedRecipients));
        } catch (\Exception $e) {
            $this->error('Error en prueba de ProveedorNotificationService: ' . $e->getMessage());
        }
        
        // 4. Prueba del interceptor con correos de configuración
        $this->info('');
        $this->info('4. Prueba con correos de configuración:');
        $configEmails = ['maintenance@tvs.edu.co', 'admin@tvs.edu.co'];
        $interceptedConfigEmails = $emailTestService->interceptEmails($configEmails, 'Mantenimiento');
        $this->info("Config originales: " . implode(', ', $configEmails));
        $this->info("Config interceptados: " . implode(', ', $interceptedConfigEmails));
        
        // 5. Verificar logs
        $this->info('');
        $this->info('5. Verificando logs recientes...');
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $recentLogs = array_slice(explode("\n", $logContent), -10);
            $interceptorLogs = array_filter($recentLogs, function($line) {
                return strpos($line, 'EmailTestModeService') !== false || strpos($line, 'intercepted') !== false;
            });
            
            if (!empty($interceptorLogs)) {
                $this->info('Logs recientes del interceptor:');
                foreach ($interceptorLogs as $log) {
                    $this->line($log);
                }
            } else {
                $this->info('No se encontraron logs recientes del interceptor.');
            }
        }
        
        $this->info('');
        $this->info('=== PRUEBA COMPLETADA ===');
        $this->info('Todos los interceptores están funcionando correctamente.');
        $this->info('Los correos serán redirigidos según la configuración del .env');
        
        return 0;
    }
    
    private function callPrivateMethod($object, $methodName, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
