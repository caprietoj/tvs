<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleCalendarService;

class TestGoogleCalendarConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:google-calendar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la configuración de Google Calendar para sesiones de retroalimentación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBA DE CONFIGURACIÓN GOOGLE CALENDAR ===');
        $this->newLine();

        // Verificar configuración
        $this->info('1. VERIFICANDO CONFIGURACIÓN:');
        
        $clientId = config('google.oauth.client_id');
        $clientSecret = config('google.oauth.client_secret');
        $credentialsPath = config('google.calendar.credentials_path');
        $calendarId = config('google.calendar.calendar_id');
        $appName = config('google.calendar.application_name');
        
        $this->line("   - Client ID: " . ($clientId ? '✓ Configurado' : '✗ No configurado'));
        $this->line("   - Client Secret: " . ($clientSecret ? '✓ Configurado' : '✗ No configurado'));
        $this->line("   - Credentials Path: " . $credentialsPath);
        $this->line("   - Archivo existe: " . (file_exists($credentialsPath) ? '✓ Sí' : '✗ No'));
        $this->line("   - Calendar ID: " . $calendarId);
        $this->line("   - Application Name: " . $appName);
        
        $this->newLine();

        // Probar servicio
        $this->info('2. PROBANDO SERVICIO:');
        
        try {
            $googleCalendarService = new GoogleCalendarService();
            $isConfigured = $googleCalendarService->isConfigured();
            
            if ($isConfigured) {
                $this->line("   ✓ Servicio configurado correctamente");
                
                // Si usa OAuth, mostrar URL de autorización
                if ($clientId) {
                    $authUrl = $googleCalendarService->getAuthUrl();
                    if ($authUrl) {
                        $this->line("   📝 URL de autorización OAuth disponible");
                        $this->line("   🔗 " . $authUrl);
                    }
                }
                
            } else {
                $this->line("   ✗ Servicio NO configurado");
            }
            
        } catch (\Exception $e) {
            $this->error("   ✗ Error al inicializar servicio: " . $e->getMessage());
        }
        
        $this->newLine();

        // Recomendaciones
        $this->info('3. RECOMENDACIONES:');
        
        if (!$clientId && !file_exists($credentialsPath)) {
            $this->warn("   ⚠ No se encontró configuración de Google Calendar");
            $this->line("   📋 Para configurar:");
            $this->line("      1. Ve a Google Cloud Console");
            $this->line("      2. Habilita Google Calendar API");
            $this->line("      3. Crea credenciales OAuth 2.0 o Service Account");
            $this->line("      4. Configura las variables en .env");
            $this->line("   📄 Ver ejemplo en .env.google-calendar.example");
        } elseif ($clientId && $clientSecret) {
            $this->line("   ✓ Configuración OAuth detectada");
            $this->line("   📋 Para usar:");
            $this->line("      1. Autoriza la aplicación usando la URL mostrada arriba");
            $this->line("      2. Implementa el callback de OAuth");
            $this->line("      3. Guarda el token de acceso");
        } elseif (file_exists($credentialsPath)) {
            $this->line("   ✓ Configuración Service Account detectada");
            $this->line("   📋 Para usar:");
            $this->line("      1. Asegúrate de que el archivo JSON sea válido");
            $this->line("      2. La service account debe tener permisos de Calendar");
        }
        
        $this->newLine();
        
        $this->info('4. MODO SIN GOOGLE CALENDAR:');
        $this->line("   ℹ Si no configuras Google Calendar, el sistema funcionará");
        $this->line("     normalmente pero solo enviará notificaciones por email.");
        $this->line("     Las sesiones se almacenarán en la base de datos.");
        
        $this->newLine();
        $this->info('=== FIN DE LA PRUEBA ===');
        
        return 0;
    }
}
