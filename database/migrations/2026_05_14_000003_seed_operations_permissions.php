<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $now = now();
        $permissions = [
            'operations.manage' => 'Manage table operations and order exceptions',
            'shifts.manage' => 'Manage cashier shifts and reconciliation',
            'refunds.manage' => 'Manage refunds and voids',
        ];

        foreach ($permissions as $name => $label) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['label' => $label, 'updated_at' => $now, 'created_at' => $now],
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_keys($permissions))
            ->pluck('id', 'name');

        $roleNames = ['admin', 'manager', 'cashier'];
        $roleIds = DB::table('roles')->whereIn('name', $roleNames)->pluck('id', 'name');

        foreach ($roleIds as $roleName => $roleId) {
            foreach ($permissionIds as $permissionName => $permissionId) {
                if ($roleName === 'cashier' && ! in_array($permissionName, ['operations.manage', 'shifts.manage', 'refunds.manage'], true)) {
                    continue;
                }

                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['created_at' => $now, 'updated_at' => $now],
                );
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['operations.manage', 'shifts.manage', 'refunds.manage'])
            ->pluck('id');

        if ($permissionIds->isNotEmpty() && Schema::hasTable('permission_role')) {
            DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        }

        DB::table('permissions')
            ->whereIn('name', ['operations.manage', 'shifts.manage', 'refunds.manage'])
            ->delete();
    }
};
