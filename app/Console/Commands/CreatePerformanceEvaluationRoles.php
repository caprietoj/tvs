<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreatePerformanceEvaluationRoles extends Command
{
    protected $signature = 'roles:create-performance-evaluation';
    protected $description = 'Crear roles para el sistema de evaluaciones de desempeño';

    public function handle()
    {
        $this->info('Creando roles para el sistema de evaluaciones de desempeño...');
        
        // Crear roles si no existen
        $roles = ['admin', 'supervisor', 'employee'];
        
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
                $this->info("✓ Rol '{$roleName}' creado");
            } else {
                $this->line("- Rol '{$roleName}' ya existe");
            }
        }
        
        // Crear permisos específicos
        $permissions = [
            'create-performance-evaluations',
            'view-all-performance-evaluations',
            'evaluate-as-supervisor',
            'self-evaluate'
        ];
        
        foreach ($permissions as $permissionName) {
            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
                $this->info("✓ Permiso '{$permissionName}' creado");
            } else {
                $this->line("- Permiso '{$permissionName}' ya existe");
            }
        }
        
        // Asignar permisos a roles
        $adminRole = Role::findByName('admin');
        $supervisorRole = Role::findByName('supervisor');
        $employeeRole = Role::findByName('employee');
        
        // Admin puede hacer todo
        $adminRole->syncPermissions($permissions);
        $this->info("✓ Permisos asignados al rol 'admin'");
        
        // Supervisor puede evaluar como supervisor y autoevaluarse
        $supervisorRole->syncPermissions(['evaluate-as-supervisor', 'self-evaluate']);
        $this->info("✓ Permisos asignados al rol 'supervisor'");
        
        // Employee solo puede autoevaluarse
        $employeeRole->syncPermissions(['self-evaluate']);
        $this->info("✓ Permisos asignados al rol 'employee'");
        
        $this->info('');
        $this->info('Roles disponibles:');
        Role::all()->each(function($role) {
            $this->line("- {$role->name}");
        });
        
        return 0;
    }
}
