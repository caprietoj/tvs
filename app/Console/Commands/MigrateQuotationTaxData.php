<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quotation;
use Illuminate\Support\Facades\Log;

class MigrateQuotationTaxData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotations:migrate-tax-data {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy quotation tax data to new specific tax fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Buscando cotizaciones legacy con configuración de IVA inconsistente...');
        
        // Buscar cotizaciones que tienen includes_iva=true y iva_amount>0 
        // pero no tienen configurados los campos específicos nuevos
        $quotationsWithLegacyIva = Quotation::where('includes_iva', true)
            ->where('iva_amount', '>', 0)
            ->where(function($query) {
                $query->where(function($subQuery) {
                    // No tienen IVA 19% configurado
                    $subQuery->where('includes_iva_19', false)
                             ->orWhereNull('includes_iva_19');
                })->where(function($subQuery) {
                    // Y no tienen IVA 5% configurado
                    $subQuery->where('includes_iva_5', false)
                             ->orWhereNull('includes_iva_5');
                });
            })
            ->with('purchaseRequest')
            ->get();
            
        if ($quotationsWithLegacyIva->isEmpty()) {
            $this->info('✅ No se encontraron cotizaciones legacy que requieran migración.');
            return 0;
        }
        
        $this->info("📋 Se encontraron {$quotationsWithLegacyIva->count()} cotizaciones que requieren migración:");
        
        $migratedCount = 0;
        $skippedCount = 0;
        
        foreach ($quotationsWithLegacyIva as $quotation) {
            $this->line(""); // Línea en blanco para separar
            $this->info("🔍 Procesando cotización ID: {$quotation->id}");
            $this->line("   Proveedor: {$quotation->provider_name}");
            $this->line("   Solicitud: " . ($quotation->purchaseRequest ? $quotation->purchaseRequest->request_number : 'Sin solicitud'));
            $this->line("   Total: \${$quotation->total_amount}");
            $this->line("   IVA actual: \${$quotation->iva_amount}");
            $this->line("   Subtotal: \${$quotation->subtotal}");
            
            // Calcular el subtotal si no existe
            $subtotal = $quotation->subtotal;
            if (!$subtotal || $subtotal <= 0) {
                $subtotal = $quotation->total_amount - $quotation->iva_amount;
                $this->line("   Subtotal calculado: \${$subtotal}");
            }
            
            if ($subtotal > 0) {
                $ivaPercentage = ($quotation->iva_amount / $subtotal) * 100;
                $this->line("   Porcentaje IVA: " . number_format($ivaPercentage, 2) . "%");
                
                $updateData = [];
                
                if ($ivaPercentage >= 18 && $ivaPercentage <= 20) {
                    $this->line("   ✅ Se migrará como IVA 19%");
                    $updateData = [
                        'includes_iva_19' => true,
                        'iva_19_amount' => $quotation->iva_amount,
                        'subtotal' => $subtotal // Asegurar que el subtotal esté correcto
                    ];
                } elseif ($ivaPercentage >= 4 && $ivaPercentage <= 6) {
                    $this->line("   ✅ Se migrará como IVA 5%");
                    $updateData = [
                        'includes_iva_5' => true,
                        'iva_5_amount' => $quotation->iva_amount,
                        'subtotal' => $subtotal
                    ];
                } else {
                    $this->warn("   ⚠️  Porcentaje IVA no reconocido ({$ivaPercentage}%), se omitirá");
                    $skippedCount++;
                    continue;
                }
                
                if (!$dryRun && !empty($updateData)) {
                    try {
                        $quotation->update($updateData);
                        $this->info("   ✅ Cotización migrada exitosamente");
                        $migratedCount++;
                        
                        Log::info('Cotización legacy migrada', [
                            'quotation_id' => $quotation->id,
                            'provider_name' => $quotation->provider_name,
                            'original_iva_amount' => $quotation->iva_amount,
                            'calculated_percentage' => $ivaPercentage,
                            'update_data' => $updateData
                        ]);
                        
                    } catch (\Exception $e) {
                        $this->error("   ❌ Error al migrar cotización: " . $e->getMessage());
                        $skippedCount++;
                    }
                } elseif ($dryRun) {
                    $this->line("   📝 DRY RUN: Se aplicarían los cambios: " . json_encode($updateData));
                    $migratedCount++;
                }
            } else {
                $this->warn("   ⚠️  Subtotal inválido ({$subtotal}), se omitirá");
                $skippedCount++;
            }
        }
        
        $this->line(""); // Línea en blanco
        $this->info("📊 RESUMEN:");
        
        if ($dryRun) {
            $this->info("🔍 DRY RUN - No se realizaron cambios reales");
            $this->line("   Cotizaciones que se migrarían: {$migratedCount}");
        } else {
            $this->line("   Cotizaciones migradas exitosamente: {$migratedCount}");
        }
        
        $this->line("   Cotizaciones omitidas: {$skippedCount}");
        
        if (!$dryRun && $migratedCount > 0) {
            $this->info("✅ Migración completada. Se recomienda verificar las órdenes de compra relacionadas.");
        }
        
        if ($dryRun && $migratedCount > 0) {
            $this->info("🚀 Para aplicar los cambios reales, ejecute: php artisan quotations:migrate-tax-data");
        }
        
        return 0;
    }
}