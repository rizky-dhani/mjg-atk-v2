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
    public function recordTransaction(int $itemId, string $type, int $quantity, int $unitCost, $transactionSource = null, ?int $sourceDivisionId = null, ?int $destinationDivisionId = null, ?string $notes = null): AtkFloatingStockTransactionHistory
    {
        return DB::transaction(function () use ($itemId, $type, $quantity, $unitCost, $transactionSource, $sourceDivisionId, $destinationDivisionId, $notes) {
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

            // Logic for source and destination based on type
            if ($type === 'in') {
                // Incoming to Floating: From Division (Source) to Floating (Null Destination)
                if (is_null($sourceDivisionId) && ! is_null($transactionSource)) {
                    $sourceDivisionId = $transactionSource->division_id ??
                                       $transactionSource->source_division_id ??
                                       $transactionSource->requesting_division_id ??
                                       null;
                }
                $destinationDivisionId = null;
            } elseif ($type === 'out') {
                // Outgoing from Floating: From Floating (Null Source) to Division (Destination)
                $sourceDivisionId = null;
                if (is_null($destinationDivisionId) && ! is_null($transactionSource)) {
                    $destinationDivisionId = $transactionSource->division_id ??
                                            $transactionSource->requesting_division_id ??
                                            $transactionSource->destination_division_id ??
                                            null;
                }
            } else {
                // For other types (adjustment, transfer between floating pools if any), use provided IDs
                if (is_null($sourceDivisionId) && ! is_null($transactionSource)) {
                    $sourceDivisionId = $transactionSource->division_id ?? $transactionSource->source_division_id ?? null;
                }
                if (is_null($destinationDivisionId) && ! is_null($transactionSource)) {
                    $destinationDivisionId = $transactionSource->destination_division_id ?? $transactionSource->requesting_division_id ?? null;
                }
            }

            // Create the transaction record
            return AtkFloatingStockTransactionHistory::create([
                'item_id' => $itemId,
                'source_division_id' => $sourceDivisionId,
                'destination_division_id' => $destinationDivisionId,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => abs($quantity * $unitCost),
                'mac_snapshot' => $newMac,
                'balance_snapshot' => $newBalance,
                'trx_src_type' => $transactionSource ? get_class($transactionSource) : null,
                'trx_src_id' => $transactionSource ? $transactionSource->id : null,
                'notes' => $notes,
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
