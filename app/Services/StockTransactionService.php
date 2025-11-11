<?php

namespace App\Services;

use App\Models\AtkDivisionStock;
use App\Models\AtkStockTransaction;
use App\Models\AtkStockRequest;
use App\Models\AtkStockUsage;
use Illuminate\Support\Facades\DB;

class StockTransactionService
{
    /**
     * Record a stock transaction and update the moving average cost
     *
     * @param int $divisionId
     * @param int $itemId
     * @param string $type
     * @param int $quantity
     * @param float $unitCost
     * @param object $transactionSource
     * @return AtkStockTransaction
     */
    public function recordTransaction(int $divisionId, int $itemId, string $type, int $quantity, int $unitCost, $transactionSource): AtkStockTransaction
    {
        return DB::transaction(function () use ($divisionId, $itemId, $type, $quantity, $unitCost, $transactionSource) {
            // Get the current division stock record
            $divisionStock = AtkDivisionStock::firstOrCreate(
                [
                    'division_id' => $divisionId,
                    'item_id' => $itemId,
                ],
                [
                    'current_stock' => 0,
                    'moving_average_cost' => 0,
                    'category_id' => $transactionSource->item->category_id ?? null, // Get category from the item if possible
                ]
            );

            // Calculate new balance based on transaction type
            $newBalance = $divisionStock->current_stock;
            if ($type === 'request') {
                // Check max limit setting if it exists
                $stockSetting = $divisionStock->getSetting();
                $maxLimit = $stockSetting ? $stockSetting->max_limit : PHP_INT_MAX; // Use a large number if no limit set
                
                $newBalance += $quantity;
                
                // Ensure we don't exceed the max limit
                if ($newBalance > $maxLimit) {
                    $newBalance = $maxLimit;
                    // Adjust quantity to respect the limit
                    $quantity = $maxLimit - $divisionStock->current_stock;
                    if ($quantity < 0) $quantity = 0; // Prevent negative quantity
                }
            } elseif ($type === 'usage') {
                $newBalance = max(0, $newBalance - $quantity); // Prevent negative stock
            } elseif ($type === 'adjustment') {
                // For adjustment, check if it's adding stock
                if ($quantity > 0) {
                    // Check max limit setting if it exists
                    $stockSetting = $divisionStock->getSetting();
                    $maxLimit = $stockSetting ? $stockSetting->max_limit : PHP_INT_MAX; // Use a large number if no limit set
                    
                    $newBalance += $quantity;
                    
                    // Ensure we don't exceed the max limit
                    if ($newBalance > $maxLimit) {
                        $newBalance = $maxLimit;
                        // Adjust quantity to respect the limit
                        $quantity = $maxLimit - $divisionStock->current_stock;
                        if ($quantity < 0) $quantity = 0; // Prevent negative quantity
                    }
                } else {
                    $newBalance = max(0, $newBalance + $quantity); // quantity is negative for reductions
                }
            } elseif ($type === 'transfer') {
                // For transfers adding stock, check max limit
                if ($quantity > 0) {
                    $stockSetting = $divisionStock->getSetting();
                    $maxLimit = $stockSetting ? $stockSetting->max_limit : PHP_INT_MAX; // Use a large number if no limit set
                    
                    $newBalance = $newBalance + $quantity;
                    
                    // Ensure we don't exceed the max limit
                    if ($newBalance > $maxLimit) {
                        $newBalance = $maxLimit;
                        // Adjust quantity to respect the limit
                        $quantity = $maxLimit - $divisionStock->current_stock;
                        if ($quantity < 0) $quantity = 0; // Prevent negative quantity
                    }
                } else {
                    // For transfer reducing stock
                    $newBalance = max(0, $newBalance + $quantity); // quantity is negative for reductions
                }
            }

            // Calculate new moving average cost if this is a stock addition (request)
            $newMovingAverageCost = $divisionStock->moving_average_cost;
            if ($type === 'request' && $quantity > 0) {
                $newMovingAverageCost = $this->calculateNewMovingAverageCost(
                    $divisionStock->current_stock,
                    $divisionStock->moving_average_cost,
                    $quantity, // Using the adjusted quantity that respects the limit
                    $unitCost
                );
            } elseif ($type === 'adjustment' && $quantity > 0) {
                // Also calculate MAC for upward adjustments
                $newMovingAverageCost = $this->calculateNewMovingAverageCost(
                    $divisionStock->current_stock,
                    $divisionStock->moving_average_cost,
                    $quantity, // Using the adjusted quantity that respects the limit
                    $unitCost
                );
            }

            // Update the division stock
            $divisionStock->update([
                'current_stock' => $newBalance,
                'moving_average_cost' => $newMovingAverageCost,
            ]);

            // Create the transaction record
            $transaction = AtkStockTransaction::create([
                'division_id' => $divisionId,
                'item_id' => $itemId,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'mac_snapshot' => $divisionStock->moving_average_cost, // Store the MAC after update
                'balance_snapshot' => $newBalance,
                'trx_src_type' => get_class($transactionSource),
                'trx_src_id' => $transactionSource->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Calculate the new moving average cost using the formula:
     * New MAC = ((Old Stock × Old MAC) + (Incoming Stock × Incoming Unit Cost)) / (Old Stock + Incoming Stock)
     *
     * @param int $oldStock
     * @param float $oldMac
     * @param int $incomingStock
     * @param float $incomingUnitCost
     * @return float
     */
    public function calculateNewMovingAverageCost(int $oldStock, int $oldMac, int $incomingStock, int $incomingUnitCost): int
    {
        if (($oldStock + $incomingStock) == 0) {
            return 0;
        }

        $totalValue = ($oldStock * $oldMac) + ($incomingStock * $incomingUnitCost);
        $totalQuantity = $oldStock + $incomingStock;
        
        // Round to integer since we're storing as integer
        return (int) round($totalValue / $totalQuantity);
    }

    /**
     * Recalculate the moving average cost for all division stocks based on transaction history
     * This method can be used to rebuild MAC if needed
     * 
     * @param int $divisionId
     * @param int $itemId
     * @return int
     */
    public function recalculateMovingAverageCost(int $divisionId, int $itemId): int
    {
        $transactions = AtkStockTransaction::where('division_id', $divisionId)
            ->where('item_id', $itemId)
            ->orderBy('created_at', 'asc')
            ->get();

        $currentStock = 0;
        $currentMac = 0;

        foreach ($transactions as $transaction) {
            // Calculate the stock after this transaction
            $newStock = $currentStock;
            if ($transaction->type === 'request') {
                $newStock += $transaction->quantity;
            } elseif ($transaction->type === 'usage') {
                $newStock = max(0, $newStock - $transaction->quantity);
            } elseif ($transaction->type === 'adjustment') {
                $newStock = max(0, $newStock + $transaction->quantity);
            }

            // Recalculate the MAC after this transaction
            if ($transaction->type === 'request' && $transaction->quantity > 0) {
                $currentMac = $this->calculateNewMovingAverageCost(
                    $currentStock,
                    $currentMac,
                    $transaction->quantity,
                    $transaction->unit_cost
                );
            }

            $currentStock = $newStock;
        }

        // Update the AtkDivisionStock record with the recalculated values
        $divisionStock = AtkDivisionStock::firstOrCreate(
            [
                'division_id' => $divisionId,
                'item_id' => $itemId,
            ],
            [
                'current_stock' => 0,
                'moving_average_cost' => 0,
            ]
        );

        $divisionStock->update([
            'current_stock' => $currentStock,
            'moving_average_cost' => $currentMac,
        ]);

        return $currentMac;
    }

    /**
     * Process a stock request by recording a transaction and updating stock
     *
     * @param AtkStockRequest $stockRequest
     * @return void
     */
    public function processStockRequest(AtkStockRequest $stockRequest): void
    {
        $stockRequest->load('items.item'); // Load items with their associated item details

        foreach ($stockRequest->items as $item) {
            // For stock requests, we need to determine the cost of the incoming items
            // This could come from AtkItemPrice or other cost source
            $priceModel = $item->item->latestPrice()->first();
            $unitCost = $priceModel?->price ?? 0;
            
            $this->recordTransaction(
                $stockRequest->division_id,
                $item->item_id,
                'request',  // type
                $item->quantity,
                $unitCost,
                $stockRequest
            );
        }
    }

    /**
     * Process a stock usage by recording a transaction and updating stock
     *
     * @param AtkStockUsage $stockUsage
     * @return void
     */
    public function processStockUsage(AtkStockUsage $stockUsage): void
    {
        $stockUsage->load('items.item'); // Load items with their associated item details

        foreach ($stockUsage->items as $item) {
            // Get the moving average cost from the division stock at the time of usage
            $divisionStock = AtkDivisionStock::where('division_id', $stockUsage->division_id)
                ->where('item_id', $item->item_id)
                ->first();

            $unitCost = $divisionStock ? $divisionStock->moving_average_cost : 0;
            
            $this->recordTransaction(
                $stockUsage->division_id,
                $item->item_id,
                'usage',  // type
                $item->quantity,
                $unitCost,
                $stockUsage
            );
        }
    }

    /**
     * Get transaction history for a specific division and item
     *
     * @param int $divisionId
     * @param int $itemId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransactionHistory(int $divisionId, int $itemId)
    {
        return AtkStockTransaction::where('division_id', $divisionId)
            ->where('item_id', $itemId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Rollback a transaction if needed (e.g., when a request is cancelled or rejected)
     *
     * @param AtkStockTransaction $transaction
     * @return bool
     */
    public function rollbackTransaction(AtkStockTransaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            // Find the corresponding division stock
            $divisionStock = AtkDivisionStock::where('division_id', $transaction->division_id)
                ->where('item_id', $transaction->item_id)
                ->first();

            if (!$divisionStock) {
                return false;
            }

            // Reverse the transaction based on its type
            $newStock = $divisionStock->current_stock;
            if ($transaction->type === 'request') {
                $newStock = max(0, $newStock - $transaction->quantity);
            } elseif ($transaction->type === 'usage') {
                $newStock += $transaction->quantity;
            } elseif ($transaction->type === 'adjustment') {
                // Reverse the adjustment (subtract what was added, add what was subtracted)
                $newStock -= $transaction->quantity;
                $newStock = max(0, $newStock); // Ensure non-negative
            }

            // Update the division stock
            $divisionStock->update([
                'current_stock' => $newStock,
            ]);

            // Optionally, we might need to recalculate the moving average cost
            // This could be complex if multiple transactions have occurred after this one
            // For now, we'll just delete the transaction record
            $transaction->delete();

            return true;
        });
    }
}