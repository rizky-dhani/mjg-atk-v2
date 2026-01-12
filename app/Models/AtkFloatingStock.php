<?php

namespace App\Models;

use App\Services\FloatingStockService;
use App\Services\StockTransactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    /**
     * Distribute stock from floating to a specific division
     */
    public function distributeToDivision(int $divisionId, int $quantity, ?string $notes = null): void
    {
        if ($quantity <= 0 || $quantity > $this->current_stock) {
            throw new \InvalidArgumentException('Invalid quantity to distribute.');
        }

        DB::transaction(function () use ($divisionId, $quantity, $notes) {
            $floatingService = app(FloatingStockService::class);
            $divisionService = app(StockTransactionService::class);

            $unitCost = $this->moving_average_cost;

            // 1. Reduce from Floating Stock
            $floatingService->recordTransaction(
                $this->item_id,
                'out',
                $quantity,
                $unitCost,
                $this,
                null,
                $divisionId,
                $notes
            );

            // 2. Add to Division Stock
            $divisionService->recordTransaction(
                $divisionId,
                $this->item_id,
                'transfer',
                $quantity,
                $unitCost,
                $this,
                $notes
            );
        });
    }

    /**
     * Distribute multiple items to a specific division
     *
     * @param  array  $items  Array of ['item_id' => $id, 'quantity' => $qty]
     */
    public static function distributeBulkToDivision(array $items, int $divisionId, ?string $notes = null): void
    {
        DB::transaction(function () use ($items, $divisionId, $notes) {
            foreach ($items as $itemData) {
                $floatingStock = self::where('item_id', $itemData['item_id'])->firstOrFail();
                $floatingStock->distributeToDivision($divisionId, $itemData['quantity'], $notes);
            }
        });
    }

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
        return $this->hasMany(AtkFloatingStockTransactionHistory::class, 'item_id', 'item_id');
    }

    public function getTotalStockValue(): float
    {
        return $this->current_stock * $this->moving_average_cost;
    }
}
