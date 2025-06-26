<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;

class TestPreapprovalFeature extends Command
{
    protected $signature = 'test:preapproval-feature {id?}';
    protected $description = 'Prueba la funcionalidad de pre-aprobación';

    public function handle()
    {
        $id = $this->argument('id') ?? 1;
        
        $purchaseRequest = PurchaseRequest::find($id);
        
        if (!$purchaseRequest) {
            $this->error("No se encontró la solicitud de compra con ID: {$id}");
            return;
        }
        
        $this->info("=== INFORMACIÓN DE LA SOLICITUD ===");
        $this->info("ID: {$purchaseRequest->id}");
        $this->info("Número: {$purchaseRequest->request_number}");
        $this->info("Estado: {$purchaseRequest->status}");
        $this->info("Cotizaciones: {$purchaseRequest->quotations->count()}");
        
        $this->info("");
        $this->info("=== ESTADO DE PRE-APROBACIÓN ===");
        
        if ($purchaseRequest->preapproval_sent_at) {
            $this->info("✅ Pre-aprobación enviada:");
            $this->info("   Fecha: {$purchaseRequest->preapproval_sent_at->format('d/m/Y H:i:s')}");
            if ($purchaseRequest->preapprovalSender) {
                $this->info("   Enviado por: {$purchaseRequest->preapprovalSender->name}");
            }
        } else {
            $this->info("❌ Pre-aprobación NO enviada");
        }
        
        $this->info("");
        $this->info("=== CONDICIONES DE BOTONES ===");
        
        // Condición para botón de envío de pre-aprobación
        $showPreapprovalButton = $purchaseRequest->quotations->count() > 0 && 
                                 $purchaseRequest->status == 'En Cotización' && 
                                 is_null($purchaseRequest->preapproval_sent_at);
        
        $this->info("Mostrar botón 'Enviar para Pre-aprobación': " . ($showPreapprovalButton ? 'SÍ' : 'NO'));
        
        if (!$showPreapprovalButton) {
            $reasons = [];
            if ($purchaseRequest->quotations->count() == 0) $reasons[] = "Sin cotizaciones";
            if ($purchaseRequest->status != 'En Cotización') $reasons[] = "Estado: {$purchaseRequest->status}";
            if (!is_null($purchaseRequest->preapproval_sent_at)) $reasons[] = "Ya enviado";
            
            $this->info("   Razones: " . implode(', ', $reasons));
        }
        
        // Condición para botón de selección mixta
        $showMixedSelectionButton = $purchaseRequest->quotations->count() > 1 && 
                                   $purchaseRequest->status !== 'approved' &&
                                   is_null($purchaseRequest->preapproval_sent_at);
        
        $this->info("Mostrar botón 'Selección Mixta': " . ($showMixedSelectionButton ? 'SÍ' : 'NO'));
        
        if (!$showMixedSelectionButton) {
            $reasons = [];
            if ($purchaseRequest->quotations->count() <= 1) $reasons[] = "≤1 cotización";
            if ($purchaseRequest->status === 'approved') $reasons[] = "Ya aprobado";
            if (!is_null($purchaseRequest->preapproval_sent_at)) $reasons[] = "Enviado para pre-aprobación";
            
            $this->info("   Razones: " . implode(', ', $reasons));
        }
        
        return 0;
    }
}
