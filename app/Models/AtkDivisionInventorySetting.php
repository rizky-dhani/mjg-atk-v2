<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkDivisionInventorySetting extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'category_id',
        'current_stock'
    ];
    protected $casts = [
        'current_stock' => 'integer',
    ];

    /**
     * Get the division that owns this atk stock.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    /**
     * Get the atk item for this atk stock.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    /**
     * Get the atk item for this atk stock.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }
}
