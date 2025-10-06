<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockUsage extends Model
{
    protected $fillable = [
        'usage_number',
        'requester_id',
        'division_id',
        'notes'
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
}
