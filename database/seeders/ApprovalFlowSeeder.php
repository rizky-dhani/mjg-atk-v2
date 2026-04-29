<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use App\Models\Role;
use App\Models\UserDivision;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE: ApprovalFlow uses firstOrCreate() so this seeder is idempotent.
        // ApprovalFlowStep uses insert() so steps are NOT idempotent — only run
        // this seeder on a fresh DB (migrate:fresh --seed) or if no steps exist.

        $divGA = UserDivision::where('initial', 'GA')->first()?->id ?? 5;
        $divIPC = UserDivision::where('initial', 'IPC')->first()?->id ?? 7;
        $divHCG = UserDivision::where('initial', 'HCG')->first()?->id ?? 6;

        $roleAdmin = Role::where('name', 'Admin')->first()?->id ?? 3;
        $roleDivHead = Role::where('name', 'Head')->first()?->id ?? 2;

        // Clear existing steps to avoid duplicates on re-run
        ApprovalFlowStep::query()->delete();

        // Create or retrieve Approval Flows (idempotent — safe to re-run)
        $atkRequestFlow = ApprovalFlow::firstOrCreate(
            ['model_type' => 'App\Models\AtkStockRequest', 'division_ids' => null],
            [
                'name' => 'ATK Stock Request',
                'description' => 'Approval flow for ATK Stock Request (Penambahan stock ATK)',
                'is_active' => true,
            ],
        );

        $atkUsageFlow = ApprovalFlow::firstOrCreate(
            ['model_type' => 'App\Models\AtkStockUsage', 'division_ids' => null],
            [
                'name' => 'ATK Stock Usage',
                'description' => 'Approval flow for ATK Stock Usage (Pengeluaran stock ATK)',
                'is_active' => true,
            ],
        );

        $mmRequestFlow = ApprovalFlow::firstOrCreate(
            ['model_type' => 'App\Models\MarketingMediaStockRequest', 'division_ids' => null],
            [
                'name' => 'Marketing Media Stock Request',
                'description' => 'Approval flow for Marketing Media Stock Request (Penambahan stock Marketing Media)',
                'is_active' => true,
            ],
        );

        $mmUsageFlow = ApprovalFlow::firstOrCreate(
            ['model_type' => 'App\Models\MarketingMediaStockUsage', 'division_ids' => null],
            [
                'name' => 'Marketing Media Stock Usage',
                'description' => 'Approval flow for Marketing Media Stock Usage (Pengeluaran stock Marketing Media)',
                'is_active' => true,
            ],
        );

        $transferFlow = ApprovalFlow::firstOrCreate(
            ['model_type' => 'App\Models\AtkTransferStock', 'division_ids' => null],
            [
                'name' => 'Transfer Stock',
                'description' => 'Approval flow for Transfer Stock between divisions',
                'is_active' => true,
            ],
        );

        $floatingRequestFlow = ApprovalFlow::firstOrCreate(
            ['model_type' => 'App\Models\AtkRequestFromFloatingStock', 'division_ids' => null],
            [
                'name' => 'ATK Request from Floating Stock',
                'description' => 'Approval flow for requesting ATK items from Floating Stock',
                'is_active' => true,
            ],
        );

        // ATK Stock Request
        ApprovalFlowStep::insert([
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => $roleDivHead,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => $roleAdmin,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => $roleDivHead,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'IPC Admin',
                'step_number' => 4,
                'role_id' => $roleAdmin,
                'division_id' => $divIPC,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'IPC Head',
                'step_number' => 5,
                'role_id' => $roleDivHead,
                'division_id' => $divIPC,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 6,
                'role_id' => $roleAdmin,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $atkRequestFlow->id,
                'step_name' => 'HCG Head',
                'step_number' => 7,
                'role_id' => $roleDivHead,
                'division_id' => $divHCG,
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
                'role_id' => $roleDivHead,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkUsageFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => $roleAdmin,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $atkUsageFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => $roleDivHead,
                'division_id' => $divGA,
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
                'role_id' => $roleDivHead,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => $roleAdmin,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => $roleDivHead,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'IPC Admin',
                'step_number' => 4,
                'role_id' => $roleAdmin,
                'division_id' => $divIPC,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'IPC Head',
                'step_number' => 5,
                'role_id' => $roleDivHead,
                'division_id' => $divIPC,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 6,
                'role_id' => $roleAdmin,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => false,
            ],
            [
                'flow_id' => $mmRequestFlow->id,
                'step_name' => 'Marketing Support Head',
                'step_number' => 7,
                'role_id' => $roleDivHead,
                'division_id' => $divHCG,
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
                'role_id' => $roleDivHead,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmUsageFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => $roleAdmin,
                'division_id' => $divGA,
                'description' => '',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $mmUsageFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => $roleDivHead,
                'division_id' => $divGA,
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
                'role_id' => $roleDivHead,
                'division_id' => null, // Will be the requesting division's head
                'description' => 'Division Head approval for transfer request',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $transferFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => $roleAdmin,
                'division_id' => $divGA, // GA division
                'description' => 'GA Admin reviews and assigns source division',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $transferFlow->id,
                'step_name' => 'Source Division Head',
                'step_number' => 3,
                'role_id' => $roleDivHead,
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
                'role_id' => $roleDivHead,
                'division_id' => null,
                'description' => 'Division Head approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $floatingRequestFlow->id,
                'step_name' => 'GA Admin',
                'step_number' => 2,
                'role_id' => $roleAdmin,
                'division_id' => $divGA,
                'description' => 'GA Admin approval',
                'allow_resubmission' => true,
            ],
            [
                'flow_id' => $floatingRequestFlow->id,
                'step_name' => 'GA Head',
                'step_number' => 3,
                'role_id' => $roleDivHead,
                'division_id' => $divGA,
                'description' => 'GA Head final approval',
                'allow_resubmission' => true,
            ],
        ]);
    }
}
