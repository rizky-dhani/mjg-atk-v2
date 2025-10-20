<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockUsage extends Model
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
                $model->request_number = \App\Helpers\StockNumberGenerator::generateOfficeStationeryUsageNumber($model->division_id);
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

            // Delete related stock usage items
            $model->atkStockUsageItems()->delete();
        });
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function division()
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function atkStockUsageItems()
    {
        return $this->hasMany(AtkStockUsageItem::class, 'usage_id');
    }

    public function approval()
    {
        return $this->morphOne(Approval::class, 'approvable');
    }

    /**
     * Generic items relationship for unified approval system
     */
    public function items()
    {
        return $this->hasMany(AtkStockUsageItem::class, 'usage_id');
    }
}
