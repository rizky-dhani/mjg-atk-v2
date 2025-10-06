<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $headRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Head']);

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Division management
            'view divisions',
            'create divisions',
            'edit divisions',
            'delete divisions',

            // ATK Category management
            'view atk-categories',
            'create atk-categories',
            'edit atk-categories',
            'delete atk-categories',

            // ATK Item management
            'view atk-items',
            'create atk-items',
            'edit atk-items',
            'delete atk-items',

            // Division Stock management
            'view division-stocks',
            'edit division-stocks',

            // Stock Request management
            'view stock-requests',
            'create stock-requests',
            'edit stock-requests',
            'delete stock-requests',
            'approve stock-requests',

            // Stock Usage management
            'view stock-usages',
            'create stock-usages',
            'edit stock-usages',
            'delete stock-usages',
            'approve stock-usages',

            // Approval Flow management
            'view approval-flows',
            'create approval-flows',
            'edit approval-flows',
            'delete approval-flows',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $superAdminRole->givePermissionTo($permissions);
        
        $adminRole->givePermissionTo([
            'view atk-categories',
            'view atk-items',
            'view division-stocks',
            'view stock-requests',
            'create stock-requests',
            'edit stock-requests',
            'view stock-usages',
            'create stock-usages',
            'edit stock-usages',
        ]);

        $headRole->givePermissionTo([
            'view atk-items',
            'view division-stocks',
            'view stock-requests',
            'approve stock-requests',
            'view stock-usages',
            'approve stock-usages',
        ]);
    }
}
