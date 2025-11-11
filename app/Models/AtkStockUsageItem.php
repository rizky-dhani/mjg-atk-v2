<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockUsageItem extends Model
{
    protected $fillable = [
        'usage_id',
        'item_id',
        'category_id',
        'quantity',
        'moving_average_cost',
    ];

    protected $casts = [
        'moving_average_cost' => 'integer',
    ];

    public function usage()
    {
        return $this->belongsTo(AtkStockUsage::class, 'usage_id');
    }

    public function item()
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }
    
    /**
     * Update the parent AtkStockUsage potential_cost after saving or deleting
     */
    protected static function booted()
    {
        static::saved(function ($item) {
            $usage = $item->usage;
            if ($usage) {
                $usage->updatePotentialCost();
            }
        });
        
        static::deleted(function ($item) {
            $usage = $item->usage;
            if ($usage) {
                $usage->updatePotentialCost();
            }
        });
    }
}
