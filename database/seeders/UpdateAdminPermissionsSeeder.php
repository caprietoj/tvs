<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateAdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // Clear cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Get admin role
            $adminRole = Role::where('name', 'Admin')->first();
            if (!$adminRole) {
                $adminRole = Role::create(['name' => 'Admin']);
            }

            // Get all permissions
            $permissions = Permission::all();

            // Assign all permissions to admin role
            $adminRole->syncPermissions($permissions);

            // Get admin user
            $admin = User::where('email', 'admin@tvs.edu.co')->first();
            if ($admin) {
                // Clear and reassign role
                DB::table('model_has_roles')->where('model_id', $admin->id)->delete();
                DB::table('model_has_permissions')->where('model_id', $admin->id)->delete();
                $admin->assignRole('Admin');
            }

            DB::commit();
            $this->command->info('Admin permissions updated successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Error updating admin permissions: ' . $e->getMessage());
            throw $e;
        }
    }
}
