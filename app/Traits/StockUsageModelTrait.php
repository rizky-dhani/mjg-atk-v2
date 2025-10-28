<?php

namespace App\Traits;

use App\Helpers\StockNumberGenerator;
use App\Models\Approval;
use App\Models\ApprovalFlow;
use App\Models\ApprovalHistory;
use App\Models\AtkStockUsage;
use App\Models\MarketingMediaStockUsage;

trait StockUsageModelTrait
{
    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (empty($model->request_number)) {
                // Generate request number using the appropriate helper based on model type
                $modelClass = get_class($model);
                
                if ($modelClass === AtkStockUsage::class) {
                    $model->request_number = StockNumberGenerator::generateOfficeStationeryUsageNumber($model->division_id);
                } elseif ($modelClass === MarketingMediaStockUsage::class) {
                    $model->request_number = StockNumberGenerator::generateMarketingMediaUsageNumber($model->division_id);
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

            // Delete related stock usage items
            $model->items()->delete();
        });
    }
}