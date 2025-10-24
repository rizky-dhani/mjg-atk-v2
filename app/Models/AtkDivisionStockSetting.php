<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkDivisionStockSetting extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'category_id',
        'max_limit'
    ];
    
    protected $casts = [
        'max_limit' => 'integer',
    ];

    /**
     * Get the division that owns this setting.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    /**
     * Get the atk item for this setting.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    /**
     * Get the atk category for this setting.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    /**
     * Get the current stock record for this setting.
     */
    public function stock()
    {
        return $this->hasOne(AtkDivisionStock::class)
            ->where('division_id', $this->division_id)
            ->where('item_id', $this->item_id);
    }
}