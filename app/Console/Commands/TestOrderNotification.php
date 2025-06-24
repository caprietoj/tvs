<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\OrderCreated;
use Illuminate\Support\Facades\Notification;

class TestOrderNotification extends Command
{
    protected $signature = 'test:order-notification {email}';
    protected $description = 'Test order notification email';

    public function handle()
    {
        $email = $this->argument('email');
        
        // Buscar una orden de compra existente
        $order = PurchaseOrder::with(['purchaseRequest', 'provider'])->first();
        
        if (!$order) {
            $this->error('No se encontró ninguna orden de compra para enviar la notificación.');
            return;
        }
        
        $this->info("Enviando notificación de prueba para la orden: {$order->order_number}");
        $this->info("Destinatario: {$email}");
        
        try {
            // Crear un usuario temporal para la notificación
            $user = new User();
            $user->email = $email;
            $user->name = 'Usuario de Prueba';
            
            // Enviar la notificación
            $user->notify(new OrderCreated($order));
            
            $this->info('✅ Notificación enviada exitosamente.');
            $this->info("📧 Revisa el correo: {$email}");
            $this->info("🔗 El enlace debe apuntar a: " . route('purchase-orders.view', $order->id));
            
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar la notificación: {$e->getMessage()}");
        }
        
        return 0;
    }
}
