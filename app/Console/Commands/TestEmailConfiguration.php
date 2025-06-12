<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la configuración de correo electrónico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE CONFIGURACIÓN DE CORREO ===');
        $this->newLine();

        // Verificar configuración de correo
        $mailDriver = Config::get('mail.default');
        $this->line("Driver de correo: {$mailDriver}");
        
        $mailConfig = Config::get("mail.mailers.{$mailDriver}");
        $this->line("Configuración del driver:");
        
        foreach ($mailConfig as $key => $value) {
            if ($key === 'password') {
                $value = str_repeat('*', strlen($value));
            }
            $this->line("  - {$key}: {$value}");
        }
        
        $this->newLine();
        
        // Verificar configuración de FROM
        $fromAddress = Config::get('mail.from.address');
        $fromName = Config::get('mail.from.name');
        $this->line("Dirección FROM: {$fromAddress}");
        $this->line("Nombre FROM: {$fromName}");
        
        $this->newLine();
        
        // Estado de la cola
        $queueConnection = Config::get('queue.default');
        $this->line("Conexión de cola: {$queueConnection}");
        
        if ($queueConnection === 'sync') {
            $this->warn("⚠ Las notificaciones se están enviando de forma síncrona");
            $this->line("  Esto significa que se envían inmediatamente, no en cola");
        } else {
            $this->line("📋 Las notificaciones se están encolando");
            $this->line("  Asegúrate de que el worker de cola esté ejecutándose:");
            $this->line("  php artisan queue:work");
        }
        
        $this->newLine();
        
        // Probar envío de email básico
        if ($this->confirm('¿Deseas enviar un email de prueba?')) {
            $testEmail = $this->ask('Ingresa el email de destino para la prueba:', 'compras@tvs.edu.co');
            
            try {
                $this->info("Enviando email de prueba a: {$testEmail}");
                
                Mail::raw(
                    "Este es un email de prueba enviado desde el comando de verificación.\n\n" .
                    "Si recibes este mensaje, la configuración de correo está funcionando correctamente.\n\n" .
                    "Fecha y hora: " . now()->format('Y-m-d H:i:s'),
                    function ($message) use ($testEmail) {
                        $message->to($testEmail)
                                ->subject('Email de Prueba - Configuración TVS');
                    }
                );
                
                $this->info('✓ Email de prueba enviado exitosamente');
                $this->line('Revisa la bandeja de entrada (y spam) del destinatario');
                
            } catch (\Exception $e) {
                $this->error('✗ Error al enviar email de prueba:');
                $this->line($e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info('=== RECOMENDACIONES ===');
        
        if ($queueConnection !== 'sync') {
            $this->line('1. Asegúrate de que el worker de cola esté ejecutándose:');
            $this->line('   php artisan queue:work');
            $this->newLine();
        }
        
        $this->line('2. Para verificar notificaciones de pre-aprobación:');
        $this->line('   - Revisa que el email de prueba se reciba correctamente');
        $this->line('   - Ejecuta una pre-aprobación real desde la interfaz web');
        $this->line('   - Verifica logs con: php artisan check:preapprovals');
        
        return 0;
    }
}
