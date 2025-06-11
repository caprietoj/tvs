<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class VerifyEmcRole extends Command
{
    protected $signature = 'emc:verify';
    protected $description = 'Verificar el rol EMC y sus permisos';

    public function handle()
    {
        $role = Role::where('name', 'EMC')->first();
        
        if (!$role) {
            $this->error('❌ Rol EMC NO encontrado');
            return;
        }

        $this->info("✅ Rol EMC encontrado con ID: {$role->id}");
        $this->info("📊 Total de permisos: " . $role->permissions()->count());
        
        $this->line("\n🔐 Permisos de autorización:");
        $authPerms = [
            'approve-purchase-requests' => 'Aprobar solicitudes de compra',
            'approve-material-requests' => 'Aprobar solicitudes de materiales', 
            'approve-copy-requests' => 'Aprobar solicitudes de fotocopias',
            'view-purchase-requests' => 'Ver solicitudes de compra',
            'manage.quotations' => 'Gestionar cotizaciones',
            'preaprobaciones' => 'Acceso a preaprobaciones',
            'aprobaciones' => 'Acceso a aprobaciones',
            'fotocopias_list' => 'Listado de fotocopias'
        ];
        
        foreach ($authPerms as $perm => $desc) {
            $has = $role->hasPermissionTo($perm);
            $icon = $has ? '✅' : '❌';
            $this->line("  {$icon} {$desc}: " . ($has ? 'SÍ' : 'NO'));
        }

        $this->line("\n🎯 Resumen:");
        $totalAuthPerms = count($authPerms);
        $hasAuthPerms = 0;
        foreach (array_keys($authPerms) as $perm) {
            if ($role->hasPermissionTo($perm)) {
                $hasAuthPerms++;
            }
        }
        
        $this->info("✅ Permisos de autorización: {$hasAuthPerms}/{$totalAuthPerms}");
        
        if ($hasAuthPerms === $totalAuthPerms) {
            $this->info("🎉 El rol EMC está configurado correctamente para autorizar compras, materiales y fotocopias");
        } else {
            $this->warn("⚠️  Faltan algunos permisos de autorización");
        }
    }
}
