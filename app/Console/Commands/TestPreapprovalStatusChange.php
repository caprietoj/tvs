<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestPreapprovalStatusChange extends Command
{
    protected $signature = 'test:preapproval-status-change {id?}';
    protected $description = 'Prueba el cambio de estado cuando se envía para pre-aprobación';

    public function handle()
    {
        $id = $this->argument('id') ?? 1;
        
        $purchaseRequest = PurchaseRequest::with('preapprovalSender')->find($id);
        
        if (!$purchaseRequest) {
            $this->error("No se encontró la solicitud de compra con ID: {$id}");
            return;
        }
        
        $this->info("=== ESTADO ANTES DEL ENVÍO ===");
        $this->info("ID: {$purchaseRequest->id}");
        $this->info("Número: {$purchaseRequest->request_number}");
        $this->info("Estado: {$purchaseRequest->status}");
        $this->info("Pre-aprobación enviada: " . ($purchaseRequest->preapproval_sent_at ? 'SÍ' : 'NO'));
        
        if ($purchaseRequest->status !== 'En Cotización') {
            $this->warn("La solicitud no está en estado 'En Cotización'. Estado actual: {$purchaseRequest->status}");
            return;
        }
        
        if ($purchaseRequest->quotations->count() == 0) {
            $this->warn("La solicitud no tiene cotizaciones.");
            return;
        }
        
        $this->info("");
        $this->info("=== SIMULANDO ENVÍO DE PRE-APROBACIÓN ===");
        
        // Simular el usuario admin (ID 1)
        $user = User::find(1);
        if (!$user) {
            $this->error("No se encontró el usuario con ID 1");
            return;
        }
        
        // Simular el envío
        $purchaseRequest->update([
            'status' => 'En pre-aprobación',
            'preapproval_sent_at' => now(),
            'preapproval_sent_by' => $user->id
        ]);
        
        // Recargar para obtener los datos actualizados
        $purchaseRequest->refresh();
        $purchaseRequest->load('preapprovalSender');
        
        $this->info("");
        $this->info("=== ESTADO DESPUÉS DEL ENVÍO ===");
        $this->info("Estado: {$purchaseRequest->status}");
        $this->info("Pre-aprobación enviada: " . ($purchaseRequest->preapproval_sent_at ? 'SÍ' : 'NO'));
        if ($purchaseRequest->preapproval_sent_at) {
            $this->info("Fecha: {$purchaseRequest->preapproval_sent_at->format('d/m/Y H:i:s')}");
            if ($purchaseRequest->preapprovalSender) {
                $this->info("Enviado por: {$purchaseRequest->preapprovalSender->name}");
            }
        }
        
        $this->info("");
        $this->info("=== VERIFICACIÓN DE CONDICIONES DE BOTONES ===");
        
        // Verificar condiciones después del envío
        $showPreapprovalButton = $purchaseRequest->quotations->count() > 0 && 
                                 $purchaseRequest->status == 'En Cotización' && 
                                 is_null($purchaseRequest->preapproval_sent_at);
        
        $this->info("Mostrar botón 'Enviar para Pre-aprobación': " . ($showPreapprovalButton ? 'SÍ' : 'NO'));
        
        $showMixedSelectionButton = $purchaseRequest->quotations->count() > 1 && 
                                   $purchaseRequest->status !== 'approved' &&
                                   is_null($purchaseRequest->preapproval_sent_at);
        
        $this->info("Mostrar botón 'Selección Mixta': " . ($showMixedSelectionButton ? 'SÍ' : 'NO'));
        
        if ($purchaseRequest->status === 'En pre-aprobación' && !is_null($purchaseRequest->preapproval_sent_at)) {
            $this->info("");
            $this->info("✅ ÉXITO: El estado cambió correctamente a 'En pre-aprobación'");
            $this->info("✅ Los botones se deshabilitaron correctamente");
        } else {
            $this->error("❌ ERROR: El estado no cambió correctamente");
        }
        
        return 0;
    }
}
