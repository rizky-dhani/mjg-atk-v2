<?php

namespace App\Services;

use App\Models\AtkStockRequest;
use App\Models\AtkStockRequestItem;
use App\Enums\AtkStockRequestItemStatus;
use App\Enums\FulfillmentStatus;
use Illuminate\Support\Facades\DB;

class FulfillmentService
{
    protected StockTransactionService $stockTransactionService;

    public function __construct(StockTransactionService $stockTransactionService)
    {
        $this->stockTransactionService = $stockTransactionService;
    }

    /**
     * Process stock receipt for a single item
     */
    public function receiveItem(AtkStockRequestItem $item, int $receivedQuantity, ?string $notes = null): bool
    {
        if ($receivedQuantity <= 0) {
            return false;
        }

        $remainingQuantity = $item->quantity - $item->received_quantity;
        if ($receivedQuantity > $remainingQuantity) {
            throw new \Exception("Received quantity ({$receivedQuantity}) exceeds remaining requested quantity ({$remainingQuantity}).");
        }

        return DB::transaction(function () use ($item, $receivedQuantity, $notes) {
            $request = $item->request;
            
            // 1. Update the received quantity on the item
            $newReceivedQuantity = $item->received_quantity + $receivedQuantity;
            
            // 2. Determine new item status
            $newStatus = AtkStockRequestItemStatus::PartiallyReceived;
            if ($newReceivedQuantity >= $item->quantity) {
                $newStatus = AtkStockRequestItemStatus::FullyReceived;
            }

            $item->update([
                'received_quantity' => $newReceivedQuantity,
                'status' => $newStatus,
            ]);

            // 3. Record stock transaction and update inventory
            // Get unit cost from item's current active price if possible
            $priceModel = $item->item->activePrice()->first() ?? $item->item->latestPrice()->first();
            $unitCost = $priceModel ? ($priceModel->unit_price ?? $priceModel->price ?? 0) : 0;

            $this->stockTransactionService->recordTransaction(
                $request->division_id,
                $item->item_id,
                'request', // type is request because it's adding stock from a request
                $receivedQuantity,
                $unitCost,
                $item, // Source is the item itself
                $notes ?? "Partial fulfillment from Request {$request->request_number}"
            );

            // 4. Update parent request status (derived, but we might want to trigger any logic)
            // The overall fulfillment status is dynamically calculated via accessor in our model,
            // but we ensure the request record is touched if needed.
            $request->touch();

            return true;
        });
    }

    /**
     * Process stock receipt for multiple items
     * 
     * @param array $itemsData Array of ['id' => itemId, 'quantity' => quantity]
     */
    public function bulkReceive(array $itemsData, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($itemsData, $notes) {
            foreach ($itemsData as $data) {
                $item = AtkStockRequestItem::findOrFail($data['id']);
                $this->receiveItem($item, $data['quantity'], $notes);
            }
            return true;
        });
    }
}