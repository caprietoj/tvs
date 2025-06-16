<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Agregar permisos faltantes al rol rrhh
$rrhhRole = Role::where('name', 'rrhh')->first();
if ($rrhhRole) {
    $permissions = [
        'view-all-performance-evaluations',
        'self-evaluate'
    ];
    
    foreach($permissions as $permissionName) {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && !$rrhhRole->hasPermissionTo($permission)) {
            $rrhhRole->givePermissionTo($permission);
            echo "Permiso '$permissionName' añadido al rol 'rrhh'\n";
        } else {
            echo "El rol 'rrhh' ya tiene el permiso '$permissionName'\n";
        }
    }
} else {
    echo "No se encontró el rol 'rrhh'\n";
}

echo "\nPermisos finales del rol RRHH:\n";
if ($rrhhRole) {
    $evaluationPermissions = Permission::where('name', 'like', '%performance-evaluation%')
        ->orWhere('name', 'like', '%evaluate%')
        ->get();
    
    foreach($evaluationPermissions as $permission) {
        $hasPermission = $rrhhRole->hasPermissionTo($permission->name) ? '✓' : '✗';
        echo "  $hasPermission {$permission->name}\n";
    }
}
