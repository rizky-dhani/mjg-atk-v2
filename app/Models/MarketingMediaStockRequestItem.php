<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingMediaStockRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'item_id',
        'category_id',
        'quantity',
    ];

    public function request()
    {
        return $this->belongsTo(MarketingMediaStockRequest::class, 'request_id');
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
