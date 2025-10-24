<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingMediaStockUsageItem extends Model
{
    protected $fillable = [
        'usage_id',
        'item_id',
        'category_id',
        'quantity',
    ];

    public function usage()
    {
        return $this->belongsTo(MarketingMediaStockUsage::class, 'usage_id');
    }

    public function item()
    {
        return $this->belongsTo(MarketingMediaItem::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(MarketingMediaCategory::class, 'category_id');
    }
}
