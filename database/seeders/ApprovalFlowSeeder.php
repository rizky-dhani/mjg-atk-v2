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
        $atkRequestFlow = ApprovalFlow::create([
            'name' => 'ATK Stock Request',
            'description' => 'Approval flow for ATK Stock Request (Penambahan stock ATK)',
            'model_type' => 'App\Models\AtkStockRequest',
            'is_active' => true,
        ]);
        
        $atkUsageFlow = ApprovalFlow::create([
            'name' => 'ATK Stock Usage',
            'description' => 'Approval flow for ATK Stock Usage (Pengeluaran stock ATK)',
            'model_type' => 'App\Models\AtkStockUsage',
            'is_active' => true,
        ]);
        
        $mmRequestFlow = ApprovalFlow::create([
            'name' => 'Marketing Media Stock Request',
            'description' => 'Approval flow for Marketing Media Stock Request (Penambahan stock Marketing Media)',
            'model_type' => 'App\Models\MarketingMediaStockRequest',
            'is_active' => true,
        ]);
        
        $mmUsageFlow = ApprovalFlow::create([
            'name' => 'Marketing Media Stock Usage',
            'description' => 'Approval flow for Marketing Media Stock Usage (Pengeluaran stock Marketing Media)',
            'model_type' => 'App\Models\MarketingMediaStockUsage',
            'is_active' => true,
        ]);
        
        $transferFlow = ApprovalFlow::create([
            'name' => 'Transfer Stock',
            'description' => 'Approval flow for Transfer Stock between divisions',
            'model_type' => 'App\Models\AtkTransferStock',
            'is_active' => true,
        ]);

        $floatingRequestFlow = ApprovalFlow::create([
            'name' => 'ATK Request from Floating Stock',
            'description' => 'Approval flow for requesting ATK items from Floating Stock',
            'model_type' => 'App\Models\AtkRequestFromFloatingStock',
            'is_active' => true,
        ]);

        // ATK Stock Request
        ApprovalFlowStep::insert([
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'IPC Admin',
                'step_number' => 4,
                'role_id' => 3,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'IPC Head',
                'step_number' => 5,
                'role_id' => 2,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 6,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
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
                'flow_id' => $atkUsageFlow->id,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkUsageFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkUsageFlow->id,
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
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'IPC Admin',
                'step_number' => 4,
                'role_id' => 3,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'IPC Head',
                'step_number' => 5,
                'role_id' => 2,
                'division_id' => 7,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 6,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
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
                'flow_id' => $mmUsageFlow->id,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmUsageFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmUsageFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => '',
                'allow_resubmission' => true,
            ],
        ]);

        // Transfer Stock
        ApprovalFlowStep::insert([
            [
                'flow_id' => $transferFlow->id,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null, // Will be the requesting division's head
                'description' => 'Division Head approval for transfer request',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $transferFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5, // GA division
                'description' => 'GA Admin reviews and assigns source division',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $transferFlow->id,
                'step_name' => 'Source Division Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => null, // Will be the source division's head (dynamically assigned)
                'description' => 'Source Division Head approval for providing items',
                'allow_resubmission' => true,
            ],
        ]);

        // ATK Request from Floating Stock
        ApprovalFlowStep::insert([
            [
                'flow_id' => $floatingRequestFlow->id,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $floatingRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => 3,
                'division_id' => 5,
                'description' => 'GA Admin approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $floatingRequestFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => 2,
                'division_id' => 5,
                'description' => 'GA Head final approval',
                'allow_resubmission' => true,
            ],
        ]);
    }
}