<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class AtkItemPrice extends Model
{
    protected $fillable = [
        'item_id',
        'category_id',
        'unit_price',
        'effective_date',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(AtkItemPriceHistory::class, 'item_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Store the original model state before updating
        static::updating(function ($model) {
            // Get the original state
            $original = $model->getOriginal();

            // Check if the unit price has changed
            if ($model->isDirty('unit_price')) {
                $oldPrice = $original['unit_price'];
                $newPrice = $model->unit_price;

                // Log the price change in the history table
                AtkItemPriceHistory::create([
                    'item_id' => $model->item_id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'effective_date' => $model->effective_date,
                    'changed_by' => Auth::id(),
                ]);
            }

            // If is_active is being set to true, deactivate all other prices for the same item
            if ($model->is_active && $model->isDirty('is_active')) {
                self::where('item_id', $model->item_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
            }
        });

        static::created(function ($model) {
            // Log the initial price creation in the history table
            AtkItemPriceHistory::create([
                'item_id' => $model->item_id,
                'old_price' => null, // No old price for new records
                'new_price' => $model->unit_price,
                'effective_date' => $model->effective_date,
                'changed_by' => Auth::id(),
            ]);

            // If this new record has is_active = true, deactivate all other prices for the same item
            if ($model->is_active) {
                self::where('item_id', $model->item_id)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
