<?php

namespace App\Services;

use App\Models\AtkFloatingStock;
use App\Models\AtkFloatingStockTransactionHistory;
use Illuminate\Support\Facades\DB;

class FloatingStockService
{
    /**
     * Record a stock transaction and update the moving average cost
     */
    public function recordTransaction(int $itemId, string $type, int $quantity, int $unitCost, $transactionSource = null): AtkFloatingStockTransactionHistory
    {
        return DB::transaction(function () use ($itemId, $type, $quantity, $unitCost, $transactionSource) {
            // Get the current floating stock record
            $floatingStock = AtkFloatingStock::firstOrCreate(
                ['item_id' => $itemId],
                [
                    'current_stock' => 0,
                    'moving_average_cost' => 0,
                    'category_id' => $transactionSource->item->category_id ?? \App\Models\AtkItem::find($itemId)->category_id,
                ]
            );

            $oldStock = $floatingStock->current_stock;
            $oldMac = $floatingStock->moving_average_cost;

            // Calculate new balance
            $newBalance = $oldStock;
            if (in_array($type, ['in', 'transfer'])) {
                $newBalance += $quantity;
            } elseif ($type === 'out') {
                $newBalance = max(0, $newBalance - $quantity);
            } elseif ($type === 'adjustment') {
                $newBalance = max(0, $newBalance + $quantity); // quantity can be negative
            }

            // Calculate new MAC if stock is added
            $newMac = $oldMac;
            if (in_array($type, ['in', 'adjustment']) && $quantity > 0) {
                $newMac = $this->calculateNewMovingAverageCost($oldStock, $oldMac, $quantity, $unitCost);
            }

            // Update the floating stock
            $floatingStock->update([
                'current_stock' => $newBalance,
                'moving_average_cost' => $newMac,
            ]);

            // Create the transaction record
            return AtkFloatingStockTransactionHistory::create([
                'item_id' => $itemId,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => abs($quantity * $unitCost),
                'mac_snapshot' => $newMac,
                'balance_snapshot' => $newBalance,
                'trx_src_type' => $transactionSource ? get_class($transactionSource) : null,
                'trx_src_id' => $transactionSource ? $transactionSource->id : null,
            ]);
        });
    }

    /**
     * Calculate new MAC formula
     */
    public function calculateNewMovingAverageCost(int $oldStock, int $oldMac, int $incomingStock, int $incomingUnitCost): int
    {
        if (($oldStock + $incomingStock) <= 0) {
            return $incomingUnitCost;
        }

        $totalValue = ($oldStock * $oldMac) + ($incomingStock * $incomingUnitCost);
        $totalQuantity = $oldStock + $incomingStock;

        return (int) round($totalValue / $totalQuantity);
    }
}
