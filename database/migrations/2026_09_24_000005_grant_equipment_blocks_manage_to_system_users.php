<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Usuarios que pueden gestionar (editar / ceder) los bloqueos de las salas de informática.
     */
    protected array $emails = [
        'jefesistemas@tvs.edu.co',
        'auxiliarsistemas@tvs.edu.co',
        'mmarcell@tvs.edu.co',
    ];

    public function up(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'equipment.blocks.manage')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'equipment.blocks.manage',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->emails as $email) {
            $userId = DB::table('users')->where('email', $email)->value('id');
            if (!$userId) {
                continue;
            }

            $exists = DB::table('model_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $userId)
                ->exists();

            if (!$exists) {
                DB::table('model_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $userId,
                ]);
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'equipment.blocks.manage')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$permissionId) {
            return;
        }

        $userIds = DB::table('users')->whereIn('email', $this->emails)->pluck('id')->all();

        if (!empty($userIds)) {
            DB::table('model_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('model_id', $userIds)
                ->delete();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
