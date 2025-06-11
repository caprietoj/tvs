<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // ...existing code...

        Permission::create(['name' => 'view-purchase-requests']);
        Permission::create(['name' => 'create-purchase-requests']);
        Permission::create(['name' => 'approve-purchase-requests']);
        Permission::create(['name' => 'view-purchase-orders']);
        Permission::create(['name' => 'create-purchase-orders']);
        Permission::create(['name' => 'approve-purchase-orders']);

        // ...existing code...
    }
}