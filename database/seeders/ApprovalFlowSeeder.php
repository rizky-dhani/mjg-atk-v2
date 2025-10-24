<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  Create Approval Flow
        ApprovalFlow::create([
            'name' => 'ATK Stock Request',
            'description' => 'Approval flow for ATK Stock Request (Penambahan stock ATK)',
            'model_type' => 'App\Models\AtkStockRequest',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ApprovalFlow::create([
            'name' => 'ATK Stock Usage',
            'description' => 'Approval flow for ATK Stock Usage (Pengeluaran stock ATK)',
            'model_type' => 'App\Models\AtkStockUsage',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ApprovalFlow::create([
            'name' => 'Marketing Media Stock Request',
            'description' => 'Approval flow for Marketing Media Stock Request (Penambahan stock Marketing Media)',
            'model_type' => 'App\Models\MarketingMediaStockRequest',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ApprovalFlow::create([
            'name' => 'Marketing Media Stock Usage',
            'description' => 'Approval flow for Marketing Media Stock Usage (Pengeluaran stock Marketing Media)',
            'model_type' => 'App\Models\MarketingMediaStockUsage',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ATK Stock Request
        ApprovalFlowStep::insert([
            [
                'flow_id' => 1,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 1,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 1,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 1,
                'step_name' => 'IPC Admin',
                'step_number' => 4,
                'role_id' => 3,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => 1,
                'step_name' => 'IPC Head',
                'step_number' => 5,
                'role_id' => 2,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => 1,
                'step_name' => 'GA Admin',
                'step_number' => 6,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => 1,
                'step_name' => 'HCG Head',
                'step_number' => 7,
                'role_id' => 2,
                'division_id' => 6,
                'description' => '',
                'allow_resubmission' => false,
            ],
        ]);
        // ATK Stock Usage
        ApprovalFlowStep::insert([
            [
                'flow_id' => 2,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 2,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 2,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
        ]);
        // Marketing Media Stock Request
        ApprovalFlowStep::insert([
            [
                'flow_id' => 3,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 3,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 3,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 3,
                'step_name' => 'IPC Admin',
                'step_number' => 4,
                'role_id' => 3,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => 3,
                'step_name' => 'IPC Head',
                'step_number' => 5,
                'role_id' => 2,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => 3,
                'step_name' => 'GA Admin',
                'step_number' => 6,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => 3,
                'step_name' => 'Marketing Support Head',
                'step_number' => 7,
                'role_id' => 2,
                'division_id' => 6,
                'description' => '',
                'allow_resubmission' => false,
            ],
        ]);
        // Marketing Media Stock Usage
        ApprovalFlowStep::insert([
            [
                'flow_id' => 4,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 4,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => 4,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
        ]);
    }
}
