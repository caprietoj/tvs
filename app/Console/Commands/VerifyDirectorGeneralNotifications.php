<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PurchaseRequest;
use App\Services\SectionClassifierService;

class VerifyDirectorGeneralNotifications extends Command
{
    protected $signature = 'verify:director-general-notifications {--test-amounts=100000,600000 : Montos a probar separados por comas}';
    protected $description = 'Verifica que el director general solo reciba notificaciones para montos >= $500,000';

    public function handle()
    {
        $this->info('🔍 VERIFICACIÓN: Notificaciones condicionales para Director General');
        $this->newLine();

        // Verificar que el usuario existe y tiene el rol correcto
        $this->verifyUserAndRole();
        
        // Verificar la lógica del servicio
        $this->verifyServiceLogic();
        
        $this->newLine();
        $this->info('✅ Verificación completada');
    }

    private function verifyUserAndRole()
    {
        $this->line('📋 1. Verificando usuario y rol...');
        
        $user = User::where('email', 'generaldirector@tvs.edu.co')->first();
        if (!$user) {
            $this->error('   ❌ Usuario generaldirector@tvs.edu.co no encontrado');
            return;
        }
        
        $this->info('   ✅ Usuario encontrado: ' . $user->name);
        
        if ($user->hasRole('director-general')) {
            $this->info('   ✅ Usuario tiene rol "director-general"');
        } else {
            $this->warn('   ⚠️  Usuario NO tiene rol "director-general"');
        }
        
        // Verificar permisos específicos
        $permissions = [
            'almacen',
            'preaprobaciones',
            'aprobaciones'
        ];
        
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                $this->info("   ✅ Permiso: {$permission}");
            } else {
                $this->warn("   ❌ Falta permiso: {$permission}");
            }
        }
    }

    private function verifyServiceLogic()
    {
        $this->line('📋 2. Verificando lógica del servicio SectionClassifierService...');
        
        $service = new SectionClassifierService();
        $testAmounts = array_map('floatval', explode(',', $this->option('test-amounts')));
        
        foreach ($testAmounts as $amount) {
            $this->line("   💰 Probando con monto: $" . number_format($amount, 0));
            
            // Probar getDirectorEmail
            $directorEmail = $service->getDirectorEmail('Académica', $amount);
            if ($amount >= 500000) {
                if ($directorEmail === 'generaldirector@tvs.edu.co') {
                    $this->info("     ✅ Director email correcto para monto >= \$500,000: {$directorEmail}");
                } else {
                    $this->warn("     ⚠️  Director email para monto >= \$500,000: {$directorEmail} (esperado: generaldirector@tvs.edu.co)");
                }
            } else {
                if ($directorEmail !== 'generaldirector@tvs.edu.co') {
                    $this->info("     ✅ Director email correcto para monto < \$500,000: {$directorEmail}");
                } else {
                    $this->warn("     ⚠️  Director email para monto < \$500,000 es generaldirector@tvs.edu.co (no debería ser)");
                }
            }
            
            // Probar getSectionEmails
            $sectionEmails = $service->getSectionEmails('Académica', $amount);
            $hasGeneralDirector = in_array('generaldirector@tvs.edu.co', $sectionEmails);
            
            if ($amount >= 500000) {
                if ($hasGeneralDirector) {
                    $this->info("     ✅ Section emails incluye director general para monto >= \$500,000");
                } else {
                    $this->warn("     ⚠️  Section emails NO incluye director general para monto >= \$500,000");
                }
            } else {
                if (!$hasGeneralDirector) {
                    $this->info("     ✅ Section emails NO incluye director general para monto < \$500,000");
                } else {
                    $this->warn("     ⚠️  Section emails incluye director general para monto < \$500,000 (no debería)");
                }
            }
        }
        
        // Verificar método getTotalAmountFromPurchaseRequest
        $this->line('   🔍 Verificando método getTotalAmountFromPurchaseRequest...');
        
        // Buscar una purchase request existente
        $purchaseRequest = PurchaseRequest::with(['quotations'])->first();
        if ($purchaseRequest) {
            try {
                $calculatedAmount = $service->getTotalAmountFromPurchaseRequest($purchaseRequest);
                $this->info("     ✅ Método funciona - Monto calculado: $" . number_format($calculatedAmount, 2));
                $this->line("     📝 Request: {$purchaseRequest->request_number} - Quotations: {$purchaseRequest->quotations->count()}");
            } catch (\Exception $e) {
                $this->warn("     ⚠️  Error al calcular monto: " . $e->getMessage());
            }
        } else {
            $this->warn('     ⚠️  No se encontraron solicitudes para probar el cálculo de monto');
        }
    }
}