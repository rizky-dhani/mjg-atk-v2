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

        // Get the Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if ($superAdminRole) {
            // Get all permissions
            $allPermissions = Permission::all();

            // Assign all permissions to Super Admin role
            $superAdminRole->syncPermissions($allPermissions);
        }
    }
}
