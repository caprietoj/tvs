<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Agregar permiso al rol rrhh para crear evaluaciones
$rrhhRole = Role::where('name', 'rrhh')->first();
if ($rrhhRole) {
    $createPermission = Permission::where('name', 'create-performance-evaluations')->first();
    if ($createPermission && !$rrhhRole->hasPermissionTo($createPermission)) {
        $rrhhRole->givePermissionTo($createPermission);
        echo "Permiso 'create-performance-evaluations' añadido al rol 'rrhh'\n";
    } else {
        echo "El rol 'rrhh' ya tiene el permiso 'create-performance-evaluations'\n";
    }
} else {
    echo "No se encontró el rol 'rrhh'\n";
}

// Verificar permisos de todos los roles relevantes
echo "\nPermisos relacionados con evaluaciones:\n";
$evaluationPermissions = Permission::where('name', 'like', '%performance-evaluation%')
    ->orWhere('name', 'like', '%evaluate%')
    ->get();

foreach(['admin', 'supervisor', 'employee', 'rrhh'] as $roleName) {
    $role = Role::where('name', $roleName)->first();
    if ($role) {
        echo "\nRol '$roleName':\n";
        foreach($evaluationPermissions as $permission) {
            $hasPermission = $role->hasPermissionTo($permission->name) ? '✓' : '✗';
            echo "  $hasPermission {$permission->name}\n";
        }
    }
}
