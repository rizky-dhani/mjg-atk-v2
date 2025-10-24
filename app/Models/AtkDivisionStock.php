<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkDivisionStock extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'category_id',
        'current_stock',
        'max_stock_limit',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    public function setting()
    {
        return $this->hasOne(AtkDivisionStockSetting::class)
            ->where('division_id', $this->division_id)
            ->where('item_id', $this->item_id);
    }
}
