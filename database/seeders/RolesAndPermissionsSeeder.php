<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Limpia la caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos existentes para el manejo de tickets
        $ticketPermissions = 
        [
            'view.dashboard',
            'ticket.view',
            'ticket.edit',
            'ticket.delete',
            'ticket.show',
            'documents',
            'document-requests',
            'kpis.enfermeria.create',
            'kpis.enfermeria.index',
            'umbral.enfermeria.create',
            'umbral.enfermeria.show',
            'kpis.compras.create',
            'kpis.compras.index',
            'umbral.compras.create',
            'umbral.compras.show',
            'kpis.recursoshumanos.create',
            'kpis.recursoshumanos.index',
            'umbral.recursoshumanos.create',
            'umbral.recursoshumanos.show',
            'kpis.sistemas.create',
            'kpis.sistemas.index',
            'umbral.sistemas.create',
            'umbral.sistemas.index',
            'view.roles',
            'view.users',
            'equipment.manage',
            'equipment.reset',
            'equipment.inventory',
            'equipment.reserva',
            'view.reservas',
            'view.reservations',
            'view.events',
            'view.reports',
            'view.upload',
            'kpis.contabilidad.create',
            'kpis.contabilidad.index',
            'umbral.contabilidad.create',
            'umbral.contabilidad.show',
            'view.kpis',
            'view.budget',
            'view.announcements',
            'view.calendar',
            'view.maintenance',
            'view-admin-dashboard',
            'manage.configuration',
            'ver almacen',
            'editar-solicitud',
            'acciones-mantenimiento',
            'view.budget',
            'Ejecución Presupuestal',
            'Registrar Presupuesto',
            'Recaudo Cartera',
            'contabilidad.proceso.admin',
            'contabilidad.gestion.documental',
            'manage.configuration',
            'documents',
            'documents-enfermeria',
            'documents-compras',
            'documents-rrhh',
            'documents-sistemas',
            'documents-contabilidad',
            'view.salidas',
            'documents-new',
            'inpersonate',
            'purchases.requests.index',
            'approve-loan-requests',
            'impersonate',
            'special.access',
            // rol para aprobar solicitudes de compra
            'approve-purchase-requests',
            'almacen',
            'cotizaciones',
            'preaprobaciones',
            'aprobaciones',
            'odenes_compra',
            'solicitudes_compra',
            'listado-proveedores',
            'evaluacion-proveedores',
            'admin.spaces',
            'view.space-reservations',
            'create.space-reservations',
            'edit.space-reservations',
            'delete.space-reservations',
            'view.all-space-reservations',
            'approve.space-reservations',
            'reject.space-reservations',
            'cancel.space-reservations',
            'spaces.view',
            'spaces.create',
            'spaces.edit',
            'spaces.delete',
            'space-blocks.view',
            'space-blocks.create',
            'space-blocks.edit',
            'space-blocks.delete',
            'school-cycles.view',
            'school-cycles.create',
            'school-cycles.edit',
            'school-cycles.delete',
            'holidays.view',
            'holidays.create',
            'holidays.edit',
            'holidays.delete',
            'solicitudes_compra',
            'fotocopias_list'
        ];
       
        foreach ($ticketPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Permiso adicional para el dashboard
        Permission::firstOrCreate(['name' => 'view.dashboard']);

        // Rol de administrador (ya creado en ejemplo anterior)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Usuario administrador
        $adminEmail = 'intranet@tvs.edu.co';
        $adminName = 'Cristian Andres Prieto J.';
        $adminPassword = 'voavfucimbpjnqsw';

        $adminUser = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
            ]
        );
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }

        // Crear el rol "usuario" y asignar solo view.dashboard y ticket.view
        $usuarioRole = Role::firstOrCreate(['name' => 'usuario']);
        $usuarioRole->syncPermissions([
            Permission::firstOrCreate(['name' => 'view.dashboard']),
            Permission::firstOrCreate(['name' => 'ticket.view']),
            Permission::firstOrCreate(['name' => 'document-requests']),
            Permission::firstOrCreate(['name' => 'view.maintenance']),
            Permission::firstOrCreate(['name' => 'equipment.reserva']),
            Permission::firstOrCreate(['name' => 'view.reservas']),
            Permission::firstOrCreate(['name' => 'purchases.requests.index']),
            Permission::firstOrCreate(['name' => 'ver almacen']),
            Permission::firstOrCreate(['name' => 'view-loan-requests']),
            Permission::firstOrCreate(['name' => 'create-loan-requests']),
            Permission::firstOrCreate(['name' => 'solicitudes_compra']),
            Permission::firstOrCreate(['name' => 'almacen']),
            // Agregar permisos de reserva de espacios consistentes con profesor
            Permission::firstOrCreate(['name' => 'view.space-reservations']),
            Permission::firstOrCreate(['name' => 'create.space-reservations']),
        
        ]);

         // Crear el rol "enfermeria"
         $usuarioRole = Role::firstOrCreate(['name' => 'enfermeria']);
         $usuarioRole->syncPermissions([
             Permission::firstOrCreate(['name' => 'view.dashboard']),
             Permission::firstOrCreate(['name' => 'ticket.view']),
             Permission::firstOrCreate(['name' => 'document-requests']),
             Permission::firstOrCreate(['name' => 'kpis.enfermeria.create']),
             Permission::firstOrCreate(['name' => 'kpis.enfermeria.index']),
             Permission::firstOrCreate(['name' => 'umbral.enfermeria.create']),
             Permission::firstOrCreate(['name' => 'umbral.enfermeria.show']),
             Permission::firstOrCreate(['name' => 'view.kpis']),
             Permission::firstOrCreate(['name' => 'view.maintenance']),
             Permission::firstOrCreate(['name' => 'documents-enfermeria']),
             Permission::firstOrCreate(['name' => 'view-loan-requests']),
             Permission::firstOrCreate(['name' => 'create-loan-requests']),
             Permission::firstOrCreate(['name' => 'documents']),
             Permission::firstOrCreate(['name' => 'documents-enfermeria']),
             Permission::firstOrCreate(['name' => 'solicitudes_compra']),
             Permission::firstOrCreate(['name' => 'almacen']),
             // Agregar permisos de reserva de espacios consistentes con profesor
             Permission::firstOrCreate(['name' => 'view.space-reservations']),
             Permission::firstOrCreate(['name' => 'create.space-reservations']),
             // Agregar permisos para acceso completo a Gestión Académica
             Permission::firstOrCreate(['name' => 'view.reservas']),
             Permission::firstOrCreate(['name' => 'view.events']),
             Permission::firstOrCreate(['name' => 'view.salidas']),
         ]);

          // Crear el rol "contabilidad"
        $usuarioRole = Role::firstOrCreate(['name' => 'contabilidad']);
        $usuarioRole->syncPermissions([
            Permission::firstOrCreate(['name' => 'view.dashboard']),
            Permission::firstOrCreate(['name' => 'ticket.view']),
            Permission::firstOrCreate(['name' => 'document-requests']),
            Permission::firstOrCreate(['name' => 'kpis.contabilidad.create']),
            Permission::firstOrCreate(['name' => 'kpis.contabilidad.index']),
            Permission::firstOrCreate(['name' => 'kpis.contabilidad.edit']),
            Permission::firstOrCreate(['name' => 'kpis.contabilidad.show']),
            Permission::firstOrCreate(['name' => 'umbral.contabilidad.create']),
            Permission::firstOrCreate(['name' => 'umbral.contabilidad.show']),
            Permission::firstOrCreate(['name' => 'view.kpis']),  // Permiso para ver sección de KPIs
            Permission::firstOrCreate(['name' => 'view.process.admin']), // Permiso para Proceso Administrativo
            Permission::firstOrCreate(['name' => 'contabilidad.proceso.admin']), // Permiso específico para proceso administrativo de contabilidad
            Permission::firstOrCreate(['name' => 'view.budget']),
            Permission::firstOrCreate(['name' => 'Ejecución Presupuestal']), // Usando nombre original del permiso
            Permission::firstOrCreate(['name' => 'Registrar Presupuesto']),  // Usando nombre original del permiso
            Permission::firstOrCreate(['name' => 'Recaudo Cartera']),  // Añadido permiso para Recaudo cartera
            Permission::firstOrCreate(['name' => 'view.maintenance']),
            Permission::firstOrCreate(['name' => 'documents-contabilidad']),
            Permission::firstOrCreate(['name' => 'contabilidad.gestion.documental']), // Permiso específico para gestión documental
            Permission::firstOrCreate(['name' => 'view-purchase-requests']),
            Permission::firstOrCreate(['name' => 'view-purchase-orders']),
            Permission::firstOrCreate(['name' => 'view-loan-requests']),
            Permission::firstOrCreate(['name' => 'create-loan-requests']),
            Permission::firstOrCreate(['name' => 'documents']),
            Permission::firstOrCreate(['name' => 'documents-contabilidad']),
            Permission::firstOrCreate(['name' => 'ordenes_compra']),
            Permission::firstOrCreate(['name' => 'solicitudes_compra']),
            Permission::firstOrCreate(['name' => 'almacen']),
            Permission::firstOrCreate(['name' => 'fotocopias_list']),
            // Agregar permisos de reserva de espacios consistentes con profesor
            Permission::firstOrCreate(['name' => 'view.space-reservations']),
            Permission::firstOrCreate(['name' => 'create.space-reservations']),
            // Agregar permisos para acceso completo a Gestión Académica
            Permission::firstOrCreate(['name' => 'view.reservas']),
            Permission::firstOrCreate(['name' => 'view.events']),
            Permission::firstOrCreate(['name' => 'view.salidas']),
        ]);

         // Crear el rol "compras"
         $usuarioRole = Role::firstOrCreate(['name' => 'compras']);
         $usuarioRole->syncPermissions([
             Permission::firstOrCreate(['name' => 'view.dashboard']),
             Permission::firstOrCreate(['name' => 'ticket.view']),
             Permission::firstOrCreate(['name' => 'document-requests']),
             Permission::firstOrCreate(['name' => 'kpis.compras.create']),
             Permission::firstOrCreate(['name' => 'kpis.compras.index']),
             Permission::firstOrCreate(['name' => 'umbral.compras.create']),
             Permission::firstOrCreate(['name' => 'umbral.compras.show']),
             Permission::firstOrCreate(['name' => 'view.kpis']),
             Permission::firstOrCreate(['name' => 'view.maintenance']),
             Permission::firstOrCreate(['name' => 'documents-compras']),
             Permission::firstOrCreate(['name' => 'view-loan-requests']),
             Permission::firstOrCreate(['name' => 'create-loan-requests']),
             Permission::firstOrCreate(['name' => 'documents']),
             Permission::firstOrCreate(['name' => 'documents-compras']),
             Permission::firstOrCreate(['name' => 'solicitudes_compra']),
             Permission::firstOrCreate(['name' => 'almacen']),
             Permission::firstOrCreate(['name' => 'cotizaciones']),
             Permission::firstOrCreate(['name' => 'preaprobaciones']),
             Permission::firstOrCreate(['name' => 'aprobaciones']),
             Permission::firstOrCreate(['name' => 'ordenes_compra']),
             Permission::firstOrCreate(['name' => 'listado-proveedores']),
             Permission::firstOrCreate(['name' => 'evaluacion-proveedores']),
             Permission::firstOrCreate(['name' => 'fotocopias_list']),
             // Agregar permisos de reserva de espacios consistentes con profesor
             Permission::firstOrCreate(['name' => 'view.space-reservations']),
             Permission::firstOrCreate(['name' => 'create.space-reservations']),
             // Agregar permisos para acceso completo a Gestión Académica
             Permission::firstOrCreate(['name' => 'view.reservas']),
             Permission::firstOrCreate(['name' => 'view.events']),
             Permission::firstOrCreate(['name' => 'view.salidas']),
         ]);

          // Crear el rol "rrhh"
        $usuarioRole = Role::firstOrCreate(['name' => 'rrhh']);
        $usuarioRole->syncPermissions([
            Permission::firstOrCreate(['name' => 'view.dashboard']),
            Permission::firstOrCreate(['name' => 'ticket.view']),
            Permission::firstOrCreate(['name' => 'document-requests']),
            Permission::firstOrCreate(['name' => 'kpis.recursoshumanos.create']),
            Permission::firstOrCreate(['name' => 'kpis.recursoshumanos.index']),
            Permission::firstOrCreate(['name' => 'umbral.recursoshumanos.create']),
            Permission::firstOrCreate(['name' => 'umbral.recursoshumanos.show']),
            Permission::firstOrCreate(['name' => 'view.kpis']),
            Permission::firstOrCreate(['name' => 'documents']),
            Permission::firstOrCreate(['name' => 'document-requests']),
            Permission::firstOrCreate(['name' => 'view.maintenance']),
            Permission::firstOrCreate(['name' => 'editar-solicitud']),
            Permission::firstOrCreate(['name' => 'documents-rrhh']),
            Permission::firstOrCreate(['name' => 'documents-new']),
            Permission::firstOrCreate(['name' => 'view-loan-requests']),
            Permission::firstOrCreate(['name' => 'create-loan-requests']),
            Permission::firstOrCreate(['name' => 'documents-rrhh']),
            Permission::firstOrCreate(['name' => 'solicitudes_compra']),
            Permission::firstOrCreate(['name' => 'almacen']),
            // Agregar permisos de reserva de espacios consistentes con profesor
            Permission::firstOrCreate(['name' => 'view.space-reservations']),
            Permission::firstOrCreate(['name' => 'create.space-reservations']),
            // Agregar permisos para acceso completo a Gestión Académica
            Permission::firstOrCreate(['name' => 'view.reservas']),
            Permission::firstOrCreate(['name' => 'view.events']),
            Permission::firstOrCreate(['name' => 'view.salidas']),
        ]);

         // Crear el rol "profesor"
         $usuarioRole = Role::firstOrCreate(['name' => 'profesor']);
         $usuarioRole->syncPermissions([
             Permission::firstOrCreate(['name' => 'view.dashboard']),
             Permission::firstOrCreate(['name' => 'ticket.view']),
             Permission::firstOrCreate(['name' => 'document-requests']),
             Permission::firstOrCreate(['name' => 'equipment.reserva']),
             Permission::firstOrCreate(['name' => 'view.reservas']),
             Permission::firstOrCreate(['name' => 'view.maintenance']),
             Permission::firstOrCreate(['name' => 'view-loan-requests']),
             Permission::firstOrCreate(['name' => 'create-loan-requests']),
             Permission::firstOrCreate(['name' => 'solicitudes_compra']),
             // Nuevos permisos para las URLs especificadas
             Permission::firstOrCreate(['name' => 'view.salidas']),
             Permission::firstOrCreate(['name' => 'view.events']),
             Permission::firstOrCreate(['name' => 'confirm.events']),
             Permission::firstOrCreate(['name' => 'view.calendar']),
             Permission::firstOrCreate(['name' => 'view.space-reservations']),
             Permission::firstOrCreate(['name' => 'create.space-reservations']),
             Permission::firstOrCreate(['name' => 'almacen']),
         ]);

         // Crear el rol "reservation_manager"
         $usuarioRole = Role::firstOrCreate(['name' => 'reservation_manager']);
         $usuarioRole->syncPermissions([
             Permission::firstOrCreate(['name' => 'view.dashboard']),
             Permission::firstOrCreate(['name' => 'ticket.view']),
             Permission::firstOrCreate(['name' => 'document-requests']),
             Permission::firstOrCreate(['name' => 'equipment.reserva']),
             Permission::firstOrCreate(['name' => 'view.reservas']),
             Permission::firstOrCreate(['name' => 'view.maintenance']),
             Permission::firstOrCreate(['name' => 'view-loan-requests']),
             Permission::firstOrCreate(['name' => 'create-loan-requests']),
             Permission::firstOrCreate(['name' => 'solicitudes_compra']),
             Permission::firstOrCreate(['name' => 'admin.spaces']),
             Permission::firstOrCreate(['name' => 'equipment.loans.manage']),

         ]);

         // Crear el rol "mantenimiento"
         $usuarioRole = Role::firstOrCreate(['name' => 'mantenimiento']);
         $usuarioRole->syncPermissions([
             Permission::firstOrCreate(['name' => 'view.dashboard']),
             Permission::firstOrCreate(['name' => 'ticket.view']),
             Permission::firstOrCreate(['name' => 'document-requests']),
             Permission::firstOrCreate(['name' => 'view.maintenance']),
             Permission::firstOrCreate(['name' => 'acciones-mantenimiento']),
             Permission::firstOrCreate(['name' => 'view-loan-requests']),
             Permission::firstOrCreate(['name' => 'create-loan-requests']),
             Permission::firstOrCreate(['name' => 'solicitudes_compra']),
             Permission::firstOrCreate(['name' => 'almacen']),
             // Agregar permisos de reserva de espacios consistentes con profesor
             Permission::firstOrCreate(['name' => 'view.space-reservations']),
             Permission::firstOrCreate(['name' => 'create.space-reservations']),
             // Agregar permisos para acceso completo a Gestión Académica
             Permission::firstOrCreate(['name' => 'view.reservas']),
             Permission::firstOrCreate(['name' => 'view.events']),
             Permission::firstOrCreate(['name' => 'view.salidas']),
         ]);

            // Crear el rol "asistentes"
         $usuarioRole = Role::firstOrCreate(['name' => 'asistentes']);
         $usuarioRole->syncPermissions([
             Permission::firstOrCreate(['name' => 'view.dashboard']),
             Permission::firstOrCreate(['name' => 'ticket.view']),
             Permission::firstOrCreate(['name' => 'document-requests']),
             Permission::firstOrCreate(['name' => 'view.maintenance']),
             Permission::firstOrCreate(['name' => 'view.events']),
             Permission::firstOrCreate(['name' => 'view.calendar']),
             Permission::firstOrCreate(['name' => 'view.salidas']),
             Permission::firstOrCreate(['name' => 'view-loan-requests']),
             Permission::firstOrCreate(['name' => 'create-loan-requests']),
             Permission::firstOrCreate(['name' => 'solicitudes_compra']),
             Permission::firstOrCreate(['name' => 'almacen']),
             // Agregar permisos de reserva de espacios consistentes con profesor
             Permission::firstOrCreate(['name' => 'view.space-reservations']),
             Permission::firstOrCreate(['name' => 'create.space-reservations']),
         ]);

         // Crear el rol "tecnicos"
         $usuarioRole = Role::firstOrCreate(['name' => 'technician']);
         $usuarioRole->syncPermissions([
            Permission::firstOrCreate(['name' => 'view.dashboard']),
            Permission::firstOrCreate(['name' => 'ticket.view']),
            Permission::firstOrCreate(['name' => 'document-requests']),
            Permission::firstOrCreate(['name' => 'view.maintenance']),
            Permission::firstOrCreate(['name' => 'view-loan-requests']),
            Permission::firstOrCreate(['name' => 'create-loan-requests']),
            Permission::firstOrCreate(['name' => 'solicitudes_compra']),
            Permission::firstOrCreate(['name' => 'almacen']),
            // Agregar permisos de reserva de espacios consistentes con profesor
            Permission::firstOrCreate(['name' => 'view.space-reservations']),
            Permission::firstOrCreate(['name' => 'create.space-reservations']),
            // Agregar permisos para acceso completo a Gestión Académica
            Permission::firstOrCreate(['name' => 'view.reservas']),
            Permission::firstOrCreate(['name' => 'view.events']),
            Permission::firstOrCreate(['name' => 'view.salidas']),
         ]);

         // Crear el rol "space_viewer" (visualizador de reservas de espacios)
         $usuarioRole = Role::firstOrCreate(['name' => 'space_viewer']);
         $usuarioRole->syncPermissions([
            Permission::firstOrCreate(['name' => 'view.dashboard']),
            Permission::firstOrCreate(['name' => 'view.space-reservations']),
            // Agregar también el permiso de crear reservas para consistencia
            Permission::firstOrCreate(['name' => 'create.space-reservations']),
            // Agregar permisos para acceso completo a Gestión Académica
            Permission::firstOrCreate(['name' => 'view.reservas']),
            Permission::firstOrCreate(['name' => 'view.events']),
            Permission::firstOrCreate(['name' => 'view.salidas']),
         ]);

         // Crear el rol "almacen" (gestor de inventario y almacén)
         $usuarioRole = Role::firstOrCreate(['name' => 'almacen']);
         $usuarioRole->syncPermissions([
            Permission::firstOrCreate(['name' => 'view.dashboard']),
            Permission::firstOrCreate(['name' => 'almacen']),
            Permission::firstOrCreate(['name' => 'ver almacen']),
            // Agregar permisos de reserva de espacios consistentes con otros roles
            Permission::firstOrCreate(['name' => 'view.space-reservations']),
            Permission::firstOrCreate(['name' => 'create.space-reservations']),
            // Agregar permisos para acceso completo a Gestión Académica
            Permission::firstOrCreate(['name' => 'view.reservas']),
            Permission::firstOrCreate(['name' => 'view.events']),
            Permission::firstOrCreate(['name' => 'view.salidas']),
         ]);

         // Crear el rol "admin-espacios" (administrador de reservas de espacios)
         $adminEspaciosRole = Role::firstOrCreate(['name' => 'admin-espacios']);
         $adminEspaciosRole->syncPermissions([
            // Permisos básicos del sistema
            Permission::firstOrCreate(['name' => 'view.dashboard']),
            Permission::firstOrCreate(['name' => 'ticket.view']),
            
            // Permiso crucial para acceso al menú de administración
            Permission::firstOrCreate(['name' => 'admin.spaces']),
            
            // Permisos específicos de reservas de espacios (equivalentes a admin)
            Permission::firstOrCreate(['name' => 'view.space-reservations']),
            Permission::firstOrCreate(['name' => 'create.space-reservations']),
            Permission::firstOrCreate(['name' => 'edit.space-reservations']),
            Permission::firstOrCreate(['name' => 'delete.space-reservations']),
            
            // Permisos para gestión de espacios
            Permission::firstOrCreate(['name' => 'spaces.view']),
            Permission::firstOrCreate(['name' => 'spaces.create']),
            Permission::firstOrCreate(['name' => 'spaces.edit']),
            Permission::firstOrCreate(['name' => 'spaces.delete']),
            
            // Permisos para gestión de bloqueos y configuración de espacios
            Permission::firstOrCreate(['name' => 'space-blocks.view']),
            Permission::firstOrCreate(['name' => 'space-blocks.create']),
            Permission::firstOrCreate(['name' => 'space-blocks.edit']),
            Permission::firstOrCreate(['name' => 'space-blocks.delete']),
            
            // Permisos para gestión de ciclos escolares y días festivos
            Permission::firstOrCreate(['name' => 'school-cycles.view']),
            Permission::firstOrCreate(['name' => 'school-cycles.create']),
            Permission::firstOrCreate(['name' => 'school-cycles.edit']),
            Permission::firstOrCreate(['name' => 'school-cycles.delete']),
            Permission::firstOrCreate(['name' => 'holidays.view']),
            Permission::firstOrCreate(['name' => 'holidays.create']),
            Permission::firstOrCreate(['name' => 'holidays.edit']),
            Permission::firstOrCreate(['name' => 'holidays.delete']),
            
            // Permisos para ver todas las reservas (no solo las propias)
            Permission::firstOrCreate(['name' => 'view.all-space-reservations']),
            Permission::firstOrCreate(['name' => 'approve.space-reservations']),
            Permission::firstOrCreate(['name' => 'reject.space-reservations']),
            Permission::firstOrCreate(['name' => 'cancel.space-reservations']),
            
            // Permisos para acceso completo a Gestión Académica
            Permission::firstOrCreate(['name' => 'view.reservas']),
            Permission::firstOrCreate(['name' => 'view.events']),
            Permission::firstOrCreate(['name' => 'view.salidas']),
         ]);
    }
}