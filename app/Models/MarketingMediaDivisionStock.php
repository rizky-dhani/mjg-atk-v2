<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingMediaDivisionStock extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'category_id',
        'current_stock',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaItem::class, 'item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaCategory::class, 'category_id');
    }

    public function setting()
    {
        return $this->hasOne(MarketingMediaDivisionStockSetting::class, 'division_id', 'division_id')
            ->wherePivot('item_id', 'marketing_media_division_stocks.item_id');
    }

    public function marketingMediaStockRequests()
    {
        return $this->hasManyThrough(
            MarketingMediaStockRequest::class,
            MarketingMediaStockRequestItem::class,
            'item_id', // foreign key on MarketingMediaStockRequestItem
            'id',      // local key on MarketingMediaStockRequest
            'item_id', // local key on MarketingMediaDivisionStock
            'request_id' // other key on MarketingMediaStockRequestItem
        );
    }

    public function marketingMediaStockUsages()
    {
        return $this->hasManyThrough(
            MarketingMediaStockUsage::class,
            MarketingMediaStockUsageItem::class,
            'item_id', // foreign key on MarketingMediaStockUsageItem
            'id',      // local key on MarketingMediaStockUsage
            'item_id', // local key on MarketingMediaDivisionStock
            'usage_id' // other key on MarketingMediaStockUsageItem
        );
    }
}
