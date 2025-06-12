<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use Carbon\Carbon;

class CheckRecentPreApprovals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:preapprovals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar pre-aprobaciones recientes y su configuración de notificaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE PRE-APROBACIONES RECIENTES ===');
        $this->newLine();

        // Buscar solicitudes pre-aprobadas recientes
        $requests = PurchaseRequest::whereIn('status', ['Pre-aprobada', 'pre-approved'])
            ->latest()
            ->take(10)
            ->get(['id', 'request_number', 'section_area', 'status', 'pre_approved_at', 'pre_approved_by']);

        if ($requests->isEmpty()) {
            $this->warn('No se encontraron solicitudes pre-aprobadas recientes.');
            $this->newLine();
            
            // Buscar solicitudes en cotización
            $quotationRequests = PurchaseRequest::where('status', 'En Cotización')
                ->latest()
                ->take(5)
                ->get(['id', 'request_number', 'section_area', 'status']);
                
            if (!$quotationRequests->isEmpty()) {
                $this->info('Solicitudes disponibles en estado "En Cotización":');
                foreach ($quotationRequests as $req) {
                    $this->line("  - ID: {$req->id}, Número: {$req->request_number}, Sección: {$req->section_area}");
                }
            }
        } else {
            $this->info('Solicitudes pre-aprobadas encontradas:');
            $this->newLine();
            
            foreach ($requests as $req) {
                $this->line("ID: {$req->id}");
                $this->line("  - Número: {$req->request_number}");
                $this->line("  - Sección: {$req->section_area}");
                $this->line("  - Estado: {$req->status}");
                $this->line("  - Pre-aprobada: " . ($req->pre_approved_at ? $req->pre_approved_at->format('Y-m-d H:i:s') : 'N/A'));
                $this->line("  - Pre-aprobada por: " . ($req->pre_approved_by ? "User ID {$req->pre_approved_by}" : 'N/A'));
                $this->newLine();
            }
        }

        // Verificar configuración de logs
        $this->info('=== VERIFICACIÓN DE LOGS ===');
        $logPath = storage_path('logs/laravel.log');
        
        if (file_exists($logPath)) {
            $this->line("Log encontrado en: $logPath");
            
            // Buscar entradas recientes de pre-aprobación
            $logContent = file_get_contents($logPath);
            $preApprovalLines = [];
            
            $lines = explode("\n", $logContent);
            $today = Carbon::today()->format('Y-m-d');
            
            foreach ($lines as $line) {
                if (strpos($line, $today) !== false && 
                    (strpos($line, 'pre-aprobación') !== false || 
                     strpos($line, 'QuotationPreApproved') !== false ||
                     strpos($line, 'Enviando notificación de pre-aprobación') !== false)) {
                    $preApprovalLines[] = $line;
                }
            }
            
            if (!empty($preApprovalLines)) {
                $this->info('Entradas de log de pre-aprobación de hoy:');
                foreach (array_slice($preApprovalLines, -10) as $line) {
                    $this->line($line);
                }
            } else {
                $this->warn('No se encontraron entradas de log de pre-aprobación para hoy.');
            }
        } else {
            $this->error("No se encontró el archivo de log en: $logPath");
        }
        
        $this->newLine();
        $this->info('=== RECOMENDACIONES ===');
        
        if ($requests->isEmpty()) {
            $this->line('1. Crear una solicitud de prueba en estado "En Cotización"');
            $this->line('2. Agregar cotizaciones a la solicitud');
            $this->line('3. Ejecutar pre-aprobación desde la interfaz web');
            $this->line('4. Verificar logs para confirmar envío de notificaciones');
        } else {
            $this->line('1. Las solicitudes pre-aprobadas existen en el sistema');
            $this->line('2. Verificar si los destinarios están recibiendo los emails');
            $this->line('3. Revisar configuración de servidor de correo');
            $this->line('4. Confirmar que los emails no están en spam');
        }
        
        return 0;
    }
}
