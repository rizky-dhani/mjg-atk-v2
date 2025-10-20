<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockUsageItem extends Model
{
    protected $fillable = [
        'usage_id',
        'item_id',
        'quantity',
    ];

    public function usage()
    {
        return $this->belongsTo(AtkStockUsage::class, 'usage_id');
    }

    public function item()
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }
}
