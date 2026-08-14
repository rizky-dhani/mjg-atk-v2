<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission mapping per approved rules:
     *
     * 1. Admin (all divisions): core resources scoped to their divisions.
     * 2. Head (all divisions): same core resources. Approval ability comes
     *    from ApprovalFlow matching (canUserSeeApprovalNav / ApprovalService),
     *    not permission rows.
     * 3. GA Admin: extra — global Stok Inventaris visibility (handled by
     *    DivisionScoped GA exception) + Stock Limit ATK (per-user grant).
     * 4. HCG Head: same extras as GA Admin (per-user grant).
     *
     * Division scoping is NOT stored on role_has_permissions.division_id.
     * DivisionScoped derives it from the division_user pivot
     * (GA/HCG/Super Admin = global scope).
     */
    public function run(): void
    {
        $this->syncRolePermissions();
        $this->grantDivisionExtraAccess();

        $this->command?->info('RolePermissionSeeder: role permissions + GA/HCG extras synced.');
    }

    /**
     * Grant core permissions to shared Admin and Head roles (global scope).
     */
    protected function syncRolePermissions(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $headRole = Role::where('name', 'Head')->first();

        $core = $this->corePermissions();

        if ($adminRole) {
            $this->givePermissions($adminRole, $core);
        }

        if ($headRole) {
            $this->givePermissions($headRole, $core);
        }
    }

    /**
     * Grant Stock Limit ATK + extra access per-user for:
     * - Admins with a GA (General Affairs) division
     * - Heads with an HCG (Human Capital General) division
     *
     * Per-user grant keeps the shared roles lean — only GA/HCG users
     * get these extras via model_has_permissions.
     */
    protected function grantDivisionExtraAccess(): void
    {
        $extras = $this->gaExtraPermissions();

        $gaAdmins = User::role('Admin')->whereHas('divisions', function ($q) {
            $q->where('user_divisions.initial', 'GA');
        })->get();

        $hcgHeads = User::role('Head')->whereHas('divisions', function ($q) {
            $q->where('user_divisions.initial', 'HCG');
        })->get();

        foreach ($gaAdmins->merge($hcgHeads) as $user) {
            $this->givePermissions($user, $extras);
        }
    }

    /**
     * Core resources every Admin/Head can access, scoped to their divisions.
     */
    protected function corePermissions(): array
    {
        return [
            // Stok Inventaris
            'view-any atk-division-stock',
            'view atk-division-stock',

            // Permintaan ATK
            'view-any atk-stock-request',
            'view atk-stock-request',
            'create atk-stock-request',
            'edit atk-stock-request',
            'delete atk-stock-request',

            // Pengeluaran ATK
            'view-any atk-stock-usage',
            'view atk-stock-usage',
            'create atk-stock-usage',
            'edit atk-stock-usage',
            'delete atk-stock-usage',

            // Minta Stok Umum
            'view-any atk-request-from-floating-stock',
            'view atk-request-from-floating-stock',
            'create atk-request-from-floating-stock',
            'edit atk-request-from-floating-stock',
            'delete atk-request-from-floating-stock',
        ];
    }

    /**
     * Extra permissions for GA Admin / HCG Head (Stock Limit ATK).
     */
    protected function gaExtraPermissions(): array
    {
        return [
            'view-any atk-division-stock-setting',
            'view atk-division-stock-setting',
            'edit atk-division-stock-setting',
        ];
    }

    /**
     * Grant permissions to a role or user (skip if already granted).
     */
    protected function givePermissions($model, array $permissionNames): void
    {
        foreach ($permissionNames as $name) {
            $permission = Permission::where('name', $name)->first();
            if ($permission && ! $model->hasPermissionTo($permission)) {
                $model->givePermissionTo($permission);
            }
        }
    }
}
