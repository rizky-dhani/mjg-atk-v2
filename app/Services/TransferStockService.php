<?php

namespace App\Services;

use App\Models\AtkDivisionStock;
use App\Models\AtkDivisionStockSetting;
use App\Models\AtkTransferStock;
use Illuminate\Support\Facades\DB;

class TransferStockService
{
    /**
     * Process a completed transfer stock request
     */
    public function processTransfer(AtkTransferStock $transferStock): bool
    {
        // Check if the transfer is approved and not already processed
        if ($transferStock->status !== 'approved') {
            throw new \Exception('Transfer stock request is not approved');
        }

        return DB::transaction(function () use ($transferStock) {
            foreach ($transferStock->transferStockItems as $item) {
                // Validate that source division is set for the item
                if (! $item->sourceDivision) {
                    throw new \Exception("Source division must be specified for item ID {$item->item_id}");
                }

                // Get the AtkDivisionStock records for both divisions
                $sourceStock = AtkDivisionStock::firstOrCreate([
                    'division_id' => $item->sourceDivision->id,
                    'item_id' => $item->item_id,
                ], [
                    'current_stock' => 0,
                ]);

                $requestingStock = AtkDivisionStock::firstOrCreate([
                    'division_id' => $transferStock->requestingDivision->id,
                    'item_id' => $item->item_id,
                ], [
                    'current_stock' => 0,
                ]);

                // Check if source division has enough stock
                if ($sourceStock->current_stock < $item->quantity) {
                    throw new \Exception("Insufficient stock in source division for item ID {$item->item_id}");
                }

                // Get the max limit for the requesting division
                $stockSetting = AtkDivisionStockSetting::where('division_id', $transferStock->requestingDivision->id)
                    ->where('item_id', $item->item_id)
                    ->first();

                $maxLimit = $stockSetting ? $stockSetting->max_limit : PHP_INT_MAX;

                // Check if the transfer would exceed max limit in requesting division
                if ($requestingStock->current_stock + $item->quantity > $maxLimit) {
                    throw new \Exception("Transfer would exceed max limit for item ID {$item->item_id} in requesting division");
                }

                // Perform the stock transfer
                $sourceStock->decrement('current_stock', $item->quantity);
                $requestingStock->increment('current_stock', $item->quantity);
            }

            // Update transfer status to completed
            $transferStock->update(['status' => 'completed']);

            return true;
        });
    }

    /**
     * Validate a transfer request before approval
     */
    public function validateTransfer(AtkTransferStock $transferStock): array
    {
        $errors = [];

        foreach ($transferStock->transferStockItems as $item) {
            // Check if source division has been specified for the item
            if (! $item->sourceDivision) {
                $errors[] = "Source division must be specified for item ID {$item->item_id}";

                continue;
            }

            // Get the source stock
            $sourceStock = AtkDivisionStock::where('division_id', $item->sourceDivision->id)
                ->where('item_id', $item->item_id)
                ->first();

            if (! $sourceStock) {
                $errors[] = "No stock record found for item ID {$item->item_id} in source division";

                continue;
            }

            if ($sourceStock->current_stock < $item->quantity) {
                $errors[] = "Insufficient stock for item ID {$item->item_id} in source division. Available: {$sourceStock->current_stock}, Requested: {$item->quantity}";
            }
        }

        return $errors;
    }
}
