<?php

namespace App\Traits;

use App\Helpers\StockNumberGenerator;
use App\Models\Approval;
use App\Models\ApprovalFlow;
use App\Models\ApprovalHistory;
use App\Models\AtkStockRequest;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\MarketingMediaStockRequest;

trait StockRequestModelTrait
{
    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (empty($model->request_number)) {
                // Generate request number using the appropriate helper based on model type
                $modelClass = get_class($model);

                if ($modelClass === AtkStockRequest::class) {
                    $model->request_number = StockNumberGenerator::generateOfficeStationeryRequestNumber($model->division_id);
                } elseif ($modelClass === AtkRequestFromFloatingStock::class) {
                    $model->request_number = StockNumberGenerator::generateAtkRequestFromFloatingStockNumber($model->division_id);
                } elseif ($modelClass === MarketingMediaStockRequest::class) {
                    $model->request_number = StockNumberGenerator::generateMarketingMediaRequestNumber($model->division_id);
                }
            }
        });
        static::created(function ($model) {
            // Find an appropriate approval flow for this model type
            $approvalFlow = ApprovalFlow::where('model_type', get_class($model))
                ->where('is_active', true)
                ->first();

            if ($approvalFlow) {
                // Create an approval record associated with this model if one doesn't exist yet
                if (! $model->approval) {
                    $approval = $model->approval()->create([
                        'flow_id' => $approvalFlow->id,
                        'current_step' => 1, // Start with the first step
                        'status' => 'pending', // Initially pending
                    ]);

                    // Set the relation on the model so it's not cached as null
                    $model->setRelation('approval', $approval);
                }

                // Get the first step of the approval flow to determine who should approve first
                $firstStep = $approvalFlow->approvalFlowSteps()
                    ->where('step_number', 1)
                    ->first();

                if ($firstStep) {
                    // The status remains 'pending' until the first approval is made
                    // The first approver will be determined by the approval flow configuration
                }
            }
        });

        static::deleting(function ($model) {
            // Delete related approval and approval history when the model is deleted
            if ($model->approval) {
                // Delete approval step approvals first (since they reference approval_id)
                $model->approval->approvalStepApprovals()->delete();

                // Delete approval history records for this approvable
                ApprovalHistory::where('approvable_type', get_class($model))
                    ->where('approvable_id', $model->id)
                    ->delete();

                // Finally delete the approval record itself
                $model->approval->delete();
            }

            // Delete related stock request items
            $model->items()->delete();
        });
    }
}
