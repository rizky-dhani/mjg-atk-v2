<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class AtkStockRequest extends Model
{
    protected $fillable = [
        'request_number',
        'requester_id',
        'division_id',
        'notes',
        'request_type',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (empty($model->request_number)) {
                // Generate request number using the helper
                $model->request_number = \App\Helpers\StockNumberGenerator::generateOfficeStationeryRequestNumber($model->division_id);
            }
        });
        static::created(function ($model) {
            // Find an appropriate approval flow for AtkStockRequest
            $approvalFlow = \App\Models\ApprovalFlow::where('model_type', 'App\Models\AtkStockRequest')
                ->where('is_active', true)
                ->first();

            if ($approvalFlow) {
                // Create an approval record associated with this model if one doesn't exist yet
                if (! $model->approval) {
                    $model->approval()->create([
                        'flow_id' => $approvalFlow->id,
                        'current_step' => 1, // Start with the first step
                        'status' => 'pending', // Initially pending
                    ]);
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
                \App\Models\ApprovalHistory::where('approvable_type', get_class($model))
                    ->where('approvable_id', $model->id)
                    ->delete();

                // Finally delete the approval record itself
                $model->approval->delete();
            }

            // Delete related stock request items
            $model->atkStockRequestItems()->delete();
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function atkStockRequestItems(): HasMany
    {
        return $this->hasMany(AtkStockRequestItem::class, 'request_id');
    }

    public function approval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable');
    }

    public function approvalHistory()
    {
        return $this->morphMany(ApprovalHistory::class, 'approvable');
    }

    /**
     * Generic items relationship for unified approval system
     */
    public function items()
    {
        return $this->hasMany(AtkStockRequestItem::class, 'request_id');
    }
}
