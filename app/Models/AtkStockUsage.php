<?php

namespace App\Models;

use App\Traits\StockUsageModelTrait;
use Illuminate\Database\Eloquent\Model;

class AtkStockUsage extends Model
{
    use StockUsageModelTrait;
    
    protected $fillable = [
        'request_number',
        'requester_id',
        'division_id',
        'notes',
        'request_type',
    ];

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
