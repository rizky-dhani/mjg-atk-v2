<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@medquest.co.id',
            'initial' => 'SA',
            'password' => Hash::make('Superadmin2025!'),
        ]);
        $superAdmin->assignRole('Super Admin');

        $division = UserDivision::all();
        foreach ($division as $div) {
            $head = User::create([
                'name' => $div->name.' Head',
                'email' => strtolower($div->initial).'.head@medquest.co.id',
                'initial' => 'H'.$div->initial,
                'password' => Hash::make('Atk2025!'),
                'division_id' => $div->id,
            ]);
            $head->assignRole('Head');

            $admin = User::create([
                'name' => $div->name.' Admin',
                'email' => strtolower($div->initial).'.admin@medquest.co.id',
                'initial' => 'A'.$div->initial,
                'password' => Hash::make('Atk2025!'),
                'division_id' => $div->id,
            ]);
            $admin->assignRole('Admin');
        }

        // Assign default permission to Head and Admin role
        $headRole = Role::where('name', 'Head')->first();
        $adminRole = Role::where('name', 'Admin')->first();

        if ($headRole) {
            // Head role gets view-any permissions
            $permissions = [
                'view-any atk-division-stock',
                'view-any atk-stock-request',
                'view-any atk-stock-usage',
                'view-any marketing-media-stock-request',
                'view-any marketing-media-stock-usage',
                'view-any atk-transfer-stock',
            ];
            foreach ($permissions as $permissionName) {
                $headPermission = Permission::where('name', $permissionName)->first();
                if ($headPermission) {
                    $headRole->givePermissionTo($headPermission);
                }
            }
        }

        if ($adminRole) {
            // Admin role gets view-any, view, create, edit, delete permissions
            $permissions = [
                'view-any atk-stock-request',
                'view atk-stock-request',
                'create atk-stock-request',
                'edit atk-stock-request',
                'delete atk-stock-request',
                'view-any atk-stock-usage',
                'view atk-stock-usage',
                'create atk-stock-usage',
                'edit atk-stock-usage',
                'delete atk-stock-usage',
                'view-any marketing-media-stock-request',
                'view marketing-media-stock-request',
                'create marketing-media-stock-request',
                'edit marketing-media-stock-request',
                'delete marketing-media-stock-request',
                'view-any marketing-media-stock-usage',
                'view marketing-media-stock-usage',
                'create marketing-media-stock-usage',
                'edit marketing-media-stock-usage',
                'delete marketing-media-stock-usage',
                'view-any atk-transfer-stock',
                'view atk-transfer-stock',
                'create atk-transfer-stock',
                'edit atk-transfer-stock',
                'delete atk-transfer-stock',
            ];

            foreach ($permissions as $permissionName) {
                $adminPermission = Permission::where('name', $permissionName)->first();
                if ($adminPermission) {
                    $adminRole->givePermissionTo($adminPermission);
                }
            }
        }

    }
}
