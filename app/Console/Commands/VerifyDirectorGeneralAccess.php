<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class VerifyDirectorGeneralAccess extends Command
{
    /**
     * Nombre del comando Artisan.
     *
     * @var string
     */
    protected $signature = 'verify:director-general-access';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Verificar que el usuario generaldirector@tvs.edu.co tenga acceso limitado solo a Aprobaciones en el módulo de Almacén y Compras';

    /**
     * Ejecutar el comando.
     */
    public function handle()
    {
        $this->info('🔍 Verificando configuración de acceso para generaldirector@tvs.edu.co');
        $this->newLine();

        // Buscar el usuario
        $user = User::where('email', 'generaldirector@tvs.edu.co')->first();
        
        if (!$user) {
            $this->error('❌ Usuario generaldirector@tvs.edu.co no encontrado');
            return 1;
        }

        $this->info("👤 Usuario encontrado: {$user->name} ({$user->email})");
        
        // Verificar roles
        $roles = $user->roles->pluck('name')->toArray();
        $this->info("🏷️  Roles asignados: " . implode(', ', $roles));
        
        if (count($roles) === 1 && $roles[0] === 'director-general') {
            $this->info('✅ Configuración de roles correcta');
        } else {
            $this->warn('⚠️  El usuario tiene múltiples roles o rol incorrecto');
        }

        // Verificar permisos específicos del módulo de compras
        $this->newLine();
        $this->info('📦 Verificando acceso al módulo de Almacén y Compras:');
        
        $comprasPermissions = [
            'almacen' => ['✅ Debe tener', 'Acceso general al módulo'],
            'preaprobaciones' => ['✅ Debe tener', 'Acceso a Preaprobaciones'],
            'aprobaciones' => ['✅ Debe tener', 'Acceso a Aprobaciones Finales'],
            'cotizaciones' => ['❌ NO debe tener', 'Acceso a Cotizaciones'],
            'ordenes_compra' => ['❌ NO debe tener', 'Acceso a Órdenes de Compra'],
            'solicitudes_compra' => ['❌ NO debe tener', 'Acceso a Solicitudes de Compra'],
            'fotocopias_list' => ['❌ NO debe tener', 'Acceso a Fotocopias'],
            'listado-proveedores' => ['❌ NO debe tener', 'Acceso a Proveedores'],
            'inventario.view' => ['❌ NO debe tener', 'Acceso a Inventario'],
        ];

        foreach ($comprasPermissions as $permission => $data) {
            $hasPermission = $user->can($permission);
            $expected = $data[0];
            $description = $data[1];
            
            if ($expected === '✅ Debe tener') {
                $status = $hasPermission ? '✅ CORRECTO' : '❌ PROBLEMA';
                $message = $hasPermission ? 'TIENE' : 'NO TIENE (debería tener)';
            } else {
                $status = $hasPermission ? '❌ PROBLEMA' : '✅ CORRECTO';
                $message = $hasPermission ? 'TIENE (no debería tener)' : 'NO TIENE';
            }
            
            $this->line("  {$status} {$description}: {$message}");
        }

        // Verificar otros permisos importantes
        $this->newLine();
        $this->info('🔧 Verificando otros permisos importantes:');
        
        $otherPermissions = [
            'view.dashboard' => 'Acceso al dashboard',
            'ticket.view' => 'Acceso a tickets',
            'document-requests' => 'Solicitudes de documentos',
            'previsitas.view' => 'Ver previsitas',
            'view.space-reservations' => 'Ver reservas de espacios',
        ];

        foreach ($otherPermissions as $permission => $description) {
            $hasPermission = $user->can($permission);
            $status = $hasPermission ? '✅' : '❌';
            $message = $hasPermission ? 'TIENE' : 'NO TIENE';
            $this->line("  {$status} {$description}: {$message}");
        }

        // Resumen final
        $this->newLine();
        $this->info('📋 RESUMEN DE LA CONFIGURACIÓN:');
        
        $canAccessAlmacen = $user->can('almacen');
        $canPreapprove = $user->can('preaprobaciones');
        $canApprove = $user->can('aprobaciones');
        $canAccessCotizaciones = $user->can('cotizaciones');
        $canAccessOrdenes = $user->can('ordenes_compra');
        $canAccessSolicitudes = $user->can('solicitudes_compra');

        if ($canAccessAlmacen && $canPreapprove && $canApprove && 
            !$canAccessCotizaciones && !$canAccessOrdenes && !$canAccessSolicitudes) {
            $this->info('🎉 CONFIGURACIÓN CORRECTA: El usuario puede acceder únicamente a:');
            $this->line('   - Almacén y Compras > Aprobaciones > Preaprobaciones');
            $this->line('   - Almacén y Compras > Aprobaciones > Aprobaciones Finales');
            $this->info('🔒 Y NO puede acceder a otras secciones del módulo de compras');
            return 0;
        } else {
            $this->error('❌ CONFIGURACIÓN INCORRECTA: El usuario tiene acceso a secciones no permitidas');
            return 1;
        }
    }
}