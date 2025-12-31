<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkFloatingStock extends Model
{
    protected $fillable = [
        'item_id',
        'category_id',
        'current_stock',
        'moving_average_cost',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'moving_average_cost' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AtkFloatingStockTrx::class, 'item_id', 'item_id');
    }

    public function getTotalStockValue(): float
    {
        return $this->current_stock * $this->moving_average_cost;
    }
}