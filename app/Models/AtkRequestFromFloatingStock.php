<?php

namespace App\Models;

use App\Traits\StockRequestModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class AtkRequestFromFloatingStock extends Model
{
    use StockRequestModelTrait;

    protected $table = 'atk_requests_from_floating_stock';

    protected $fillable = [
        'request_number',
        'requester_id',
        'division_id',
        'notes',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function atkRequestFromFloatingStockItems(): HasMany
    {
        return $this->hasMany(AtkRequestFromFloatingStockItem::class, 'request_id');
    }

    /**
     * Generic items relationship for unified approval system
     */
    public function items()
    {
        return $this->hasMany(AtkRequestFromFloatingStockItem::class, 'request_id');
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
     * Get the approval status from the latest approval history
     */
    public function getApprovalStatusAttribute()
    {
        $latestApproval = $this->approvalHistory()
            ->orderBy('performed_at', 'desc')
            ->first();

        if (! $latestApproval) {
            return $this->approval ? $this->approval->status : 'pending';
        }

        return $latestApproval->action;
    }
}
