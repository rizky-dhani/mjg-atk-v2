<?php

namespace App\Models;

use App\Traits\StockRequestModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class AtkStockRequest extends Model
{
    use StockRequestModelTrait;
    
    protected $fillable = [
        'request_number',
        'requester_id',
        'division_id',
        'notes',
        'request_type',
    ];

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
