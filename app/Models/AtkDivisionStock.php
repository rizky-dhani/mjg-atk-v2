<?php

namespace App\Models;

use App\Services\FloatingStockService;
use App\Services\StockTransactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AtkDivisionStock extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'category_id',
        'current_stock',
        'moving_average_cost',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'moving_average_cost' => 'integer',
    ];

    /**
     * Move stock from division to floating stock
     *
     * @param int $quantity
     * @return void
     */
    public function moveToFloating(int $quantity): void
    {
        if ($quantity <= 0 || $quantity > $this->current_stock) {
            throw new \InvalidArgumentException('Invalid quantity to move.');
        }

        DB::transaction(function () use ($quantity) {
            $floatingService = app(FloatingStockService::class);
            $divisionService = app(StockTransactionService::class);
            
            $unitCost = $this->moving_average_cost;

            // 1. Reduce from Division Stock
            $this->update(['current_stock' => $this->current_stock - $quantity]);

            // 2. Record Division Transaction
            $divisionService->recordTransactionOnly(
                $this->division_id,
                $this->item_id,
                'transfer',
                -$quantity,
                $unitCost,
                $this
            );

            // 3. Add to Floating Stock
            $floatingService->recordTransaction(
                $this->item_id,
                'transfer',
                $quantity,
                $unitCost,
                $this
            );
        });
    }

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

    /**
     * Get the setting for this division-stock combination
     */
    public function getSetting()
    {
        return AtkDivisionStockSetting::where('division_id', $this->division_id)
                    ->where('item_id', $this->item_id)
                    ->first();
    }

    /**
     * Relationship to get stock transactions that match this division and item
     * This is used by Filament for the relation manager
     */
    public function stockTransactions()
    {
        return $this->hasMany(AtkStockTransaction::class, 'item_id')
                    ->where('division_id', $this->division_id);
    }
    
    /**
     * Get the max_stock_limit from the setting
     */
    public function getMaxStockLimitAttribute(): int
    {
        $setting = $this->getSetting();
        return $setting ? $setting->max_limit : 0;
    }

    /**
     * Check if the stock has reached the maximum limit
     */
    public function hasReachedMaxLimit(): bool
    {
        $setting = $this->getSetting();
        $maxLimit = $setting ? $setting->max_limit : 0;
        return $this->current_stock >= $maxLimit;
    }
    
    /**
     * Calculate the total value of current stock
     */
    public function getTotalStockValue(): float
    {
        return $this->current_stock * $this->moving_average_cost;
    }
}
