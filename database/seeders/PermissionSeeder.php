<?php

namespace Database\Seeders;

use App\Filament\Actions\GenerateModelPermissionsAction;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate permissions for all models
        GenerateModelPermissionsAction::generatePermissions();

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $headRole = Role::where('name', 'Head')->first();

        // Super Admin — all permissions, all divisions (division_id = null = global)
        if ($superAdminRole) {
            $superAdminRole->syncPermissions(Permission::all());
        }

        // Admin — view-any, view, create, edit for all models, all divisions
        if ($adminRole) {
            $adminPerms = Permission::where('name', 'like', 'view%')
                ->orWhere('name', 'like', 'create%')
                ->orWhere('name', 'like', 'edit%')
                ->get();
            $adminRole->syncPermissions($adminPerms);
        }

        // Head — view-only permissions, all divisions
        if ($headRole) {
            $headPerms = Permission::where('name', 'like', 'view%')->get();
            $headRole->syncPermissions($headPerms);
        }
    }
}
