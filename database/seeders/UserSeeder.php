<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Models\UserDivision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
        foreach($division as $div){
            $head = User::create([
                'name' => $div->name.' Head',
                'email' => strtolower($div->initial).'.head@medquest.co.id',
                'initial' => 'H'.$div->initial,
                'password' => Hash::make('Atk2025!'),
                'division_id' => $div->id
            ]);
            $head->assignRole('Head');

            $admin = User::create([
                'name' => $div->name.' Admin',
                'email' => strtolower($div->initial).'.admin@medquest.co.id',
                'initial' => 'A'.$div->initial,
                'password' => Hash::make('Atk2025!'),
                'division_id' => $div->id
            ]);
            $admin->assignRole('Admin');
        }

        // Assign default permission to Head and Admin role
        $headRole = Role::where('name', 'Head')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        
        if ($headRole) {
            // Head role gets only view-any and view permissions for atk-stock-request
            $viewAnyAtkStockRequestPermission = Permission::where('name', 'view-any atk-stock-request')->first();
            $viewAtkStockRequestPermission = Permission::where('name', 'view atk-stock-request')->first();
            
            if ($viewAnyAtkStockRequestPermission) {
                $headRole->givePermissionTo($viewAnyAtkStockRequestPermission);
            }
            if ($viewAtkStockRequestPermission) {
                $headRole->givePermissionTo($viewAtkStockRequestPermission);
            }
        }
        
        if ($adminRole) {
            // Admin role gets view-any, view, create, edit, delete permissions for atk-stock-request
            $permissions = [
                'view-any atk-stock-request',
                'view atk-stock-request',
                'create atk-stock-request',
                'edit atk-stock-request',
                'delete atk-stock-request'
            ];
            
            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $adminRole->givePermissionTo($permission);
                }
            }
        }
        
    }
}
