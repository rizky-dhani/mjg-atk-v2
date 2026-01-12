<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkItem extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'notes',
        'category_id',
        'unit_of_measure',
    ];

    public function category()
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    public function atkDivisionStocks()
    {
        return $this->hasMany(AtkDivisionStock::class, 'item_id');
    }

    public function atkFloatingStock()
    {
        return $this->hasOne(AtkFloatingStock::class, 'item_id');
    }

    public function atkStockRequestItems()
    {
        return $this->hasMany(AtkStockRequestItem::class, 'item_id');
    }

    public function atkStockUsageItems()
    {
        return $this->hasMany(AtkStockUsageItem::class, 'item_id');
    }

    public function atkItemPrices(): HasMany
    {
        return $this->hasMany(AtkItemPrice::class, 'item_id');
    }

    public function atkItemPriceHistories(): HasMany
    {
        return $this->hasMany(AtkItemPriceHistory::class, 'item_id');
    }

    // Relationship for active price
    public function activePrice()
    {
        return $this->hasOne(AtkItemPrice::class, 'item_id')
            ->where('is_active', true)
            ->latest('effective_date');
    }

    // Relationship for latest price (by date)
    public function latestPrice()
    {
        return $this->hasOne(AtkItemPrice::class, 'item_id')
            ->latest('effective_date');
    }
}
