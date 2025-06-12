<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\Proveedor;

class VerifyProviderData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:provider-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar datos de proveedores en órdenes de compra';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Verificación de Datos de Proveedores ===');
        
        // Verificar proveedores
        $totalProveedores = Proveedor::count();
        $proveedoresConNit = Proveedor::whereNotNull('nit')->where('nit', '!=', '')->count();
        $proveedoresConContacto = Proveedor::whereNotNull('persona_contacto')->where('persona_contacto', '!=', '')->count();
        
        $this->info("Total de proveedores: {$totalProveedores}");
        $this->info("Proveedores con NIT: {$proveedoresConNit}");
        $this->info("Proveedores con contacto: {$proveedoresConContacto}");
        
        // Mostrar algunos proveedores
        $this->info("\nPrimeros 3 proveedores:");
        $proveedores = Proveedor::take(3)->get();
        foreach ($proveedores as $proveedor) {
            $this->line("ID: {$proveedor->id} | Nombre: {$proveedor->nombre}");
            $this->line("  NIT: " . ($proveedor->nit ?: '[VACÍO]'));
            $this->line("  Contacto: " . ($proveedor->persona_contacto ?: '[VACÍO]'));
            $this->line("  Teléfono: " . ($proveedor->telefono ?: '[VACÍO]'));
            $this->line("---");
        }
        
        // Verificar órdenes de compra
        $totalOrdenes = PurchaseOrder::count();
        $ordenesConProveedor = PurchaseOrder::whereNotNull('provider_id')->count();
        
        $this->info("\nTotal de órdenes de compra: {$totalOrdenes}");
        $this->info("Órdenes con proveedor asignado: {$ordenesConProveedor}");
        
        // Mostrar algunas órdenes con sus proveedores
        $this->info("\nPrimeras 3 órdenes con proveedores:");
        $ordenes = PurchaseOrder::with('provider')->take(3)->get();
        foreach ($ordenes as $orden) {
            $this->line("Orden ID: {$orden->id} | Provider ID: {$orden->provider_id}");
            if ($orden->provider) {
                $this->line("  Proveedor: {$orden->provider->nombre}");
                $this->line("  NIT: " . ($orden->provider->nit ?: '[VACÍO]'));
            } else {
                $this->error("  [ERROR] No se encontró el proveedor");
            }
            $this->line("---");
        }
        
        return 0;
    }
}
