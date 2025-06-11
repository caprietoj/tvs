<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class EmcTestController extends Controller
{
    /**
     * Mostrar información del rol EMC para pruebas
     */
    public function testEmcRole()
    {
        // Obtener el rol EMC
        $emcRole = Role::where('name', 'EMC')->first();
        
        if (!$emcRole) {
            return response()->json([
                'error' => 'El rol EMC no existe'
            ], 404);
        }

        // Obtener los permisos del rol
        $permissions = $emcRole->permissions()->pluck('name')->toArray();
        
        // Verificar permisos específicos para autorización
        $authorizationPermissions = [
            'approve-purchase-requests',
            'approve-material-requests', 
            'approve-copy-requests',
            'view-purchase-requests',
            'view-purchase-orders',
            'manage.quotations',
            'pre-approve.quotations',
            'cotizaciones',
            'preaprobaciones',
            'aprobaciones',
            'ordenes_compra',
            'fotocopias_list',
        ];

        $hasAuthorizationPermissions = [];
        foreach ($authorizationPermissions as $permission) {
            $hasAuthorizationPermissions[$permission] = in_array($permission, $permissions);
        }

        // Obtener el rol profesor para comparar
        $profesorRole = Role::where('name', 'profesor')->first();
        $profesorPermissions = $profesorRole ? $profesorRole->permissions()->pluck('name')->toArray() : [];

        return response()->json([
            'emc_role' => [
                'id' => $emcRole->id,
                'name' => $emcRole->name,
                'created_at' => $emcRole->created_at,
                'total_permissions' => count($permissions),
                'permissions' => $permissions,
            ],
            'authorization_permissions_check' => $hasAuthorizationPermissions,
            'profesor_comparison' => [
                'total_permissions' => count($profesorPermissions),
                'permissions' => $profesorPermissions,
                'additional_permissions_in_emc' => array_diff($permissions, $profesorPermissions),
            ],
            'menu_access' => [
                'solicitudes_compra' => in_array('solicitudes_compra', $permissions),
                'cotizaciones' => in_array('cotizaciones', $permissions),
                'preaprobaciones' => in_array('preaprobaciones', $permissions),
                'aprobaciones' => in_array('aprobaciones', $permissions),
                'ordenes_compra' => in_array('ordenes_compra', $permissions),
                'fotocopias_list' => in_array('fotocopias_list', $permissions),
            ],
            'authorization_capabilities' => [
                'can_approve_purchases' => in_array('approve-purchase-requests', $permissions),
                'can_approve_materials' => in_array('approve-material-requests', $permissions),
                'can_approve_copies' => in_array('approve-copy-requests', $permissions),
                'can_manage_quotations' => in_array('manage.quotations', $permissions),
                'can_pre_approve' => in_array('pre-approve.quotations', $permissions),
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Verificar la funcionalidad completa del rol EMC
     */
    public function verifyEmcFunctionality()
    {
        $emcRole = Role::where('name', 'EMC')->first();
        
        if (!$emcRole) {
            return response()->json(['error' => 'Rol EMC no encontrado'], 404);
        }

        $result = [
            'role_created' => true,
            'authorization_process_access' => [
                'purchase_requests' => $emcRole->hasPermissionTo('solicitudes_compra'),
                'quotations' => $emcRole->hasPermissionTo('cotizaciones'),
                'pre_approvals' => $emcRole->hasPermissionTo('preaprobaciones'),
                'final_approvals' => $emcRole->hasPermissionTo('aprobaciones'),
                'purchase_orders' => $emcRole->hasPermissionTo('ordenes_compra'),
                'photocopies_list' => $emcRole->hasPermissionTo('fotocopias_list'),
            ],
            'authorization_permissions' => [
                'approve_purchases' => $emcRole->hasPermissionTo('approve-purchase-requests'),
                'approve_materials' => $emcRole->hasPermissionTo('approve-material-requests'),
                'approve_copies' => $emcRole->hasPermissionTo('approve-copy-requests'),
                'view_purchase_requests' => $emcRole->hasPermissionTo('view-purchase-requests'),
                'view_purchase_orders' => $emcRole->hasPermissionTo('view-purchase-orders'),
                'manage_quotations' => $emcRole->hasPermissionTo('manage.quotations'),
                'pre_approve_quotations' => $emcRole->hasPermissionTo('pre-approve.quotations'),
            ],
            'profesor_permissions_inherited' => [
                'dashboard' => $emcRole->hasPermissionTo('view.dashboard'),
                'tickets' => $emcRole->hasPermissionTo('ticket.view'),
                'documents' => $emcRole->hasPermissionTo('document-requests'),
                'equipment_reservations' => $emcRole->hasPermissionTo('equipment.reserva'),
                'view_reservations' => $emcRole->hasPermissionTo('view.reservas'),
                'maintenance' => $emcRole->hasPermissionTo('view.maintenance'),
                'loan_requests' => $emcRole->hasPermissionTo('view-loan-requests'),
                'create_loan_requests' => $emcRole->hasPermissionTo('create-loan-requests'),
                'events' => $emcRole->hasPermissionTo('view.events'),
                'calendar' => $emcRole->hasPermissionTo('view.calendar'),
                'space_reservations' => $emcRole->hasPermissionTo('view.space-reservations'),
                'almacen' => $emcRole->hasPermissionTo('almacen'),
            ]
        ];

        // Verificar que todas las funcionalidades estén disponibles
        $allGood = true;
        foreach ($result['authorization_process_access'] as $access) {
            if (!$access) $allGood = false;
        }
        foreach ($result['authorization_permissions'] as $permission) {
            if (!$permission) $allGood = false;
        }

        $result['all_permissions_working'] = $allGood;
        $result['summary'] = $allGood ? 
            'El rol EMC tiene todos los permisos necesarios para autorizar compras, materiales y fotocopias' :
            'Faltan algunos permisos en el rol EMC';

        return response()->json($result, 200, [], JSON_PRETTY_PRINT);
    }
}
