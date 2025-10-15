<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowStep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'updated_at'=> now(),
        ]);

        // Create Approval Flow Steps
        ApprovalFlowStep::insert([
            [
                'flow_id' => 1,
                'step_name' => 'Division Head',
                'step_number' => 1,
                'role_id' => 2,
                'division_id' => null,
                'description' => 'Division Head approval'
            ],            
        ]);
    }
}
