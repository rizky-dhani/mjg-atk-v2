<?php

namespace App\Services;

use App\Models\AtkStockUsage;
use App\Models\AtkStockRequest;
use App\Models\AtkDivisionStock;
use App\Models\AtkStockUsageItem;
use App\Models\MarketingMediaStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaDivisionStock;

class StockUpdateService
{
    protected BudgetService $budgetService;
    
    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * Handle stock updates for various model types when they are fully approved
     *
     * @param  mixed  $model  The approved model that may require stock updates
     */
    public function handleStockUpdates($model): void
    {
        \Log::info('StockUpdateService: handleStockUpdates called', [
            'model_id' => $model->id,
            'model_type' => get_class($model),
            'timestamp' => now()->toISOString()
        ]);

        $modelClass = get_class($model);

        // Check if the model has a request_type field (for future unified model)
        if (isset($model->request_type)) {
            // Use the request_type field to determine the operation
            $this->updateStockByRequestType($model);
        } else {
            // For the current separate models, use the existing logic
            switch ($modelClass) {
                case AtkStockRequest::class:
                case MarketingMediaStockRequest::class:
                    $this->updateStockForAddition($model);
                    break;

                case AtkStockUsage::class:
                    $this->updateStockForReduction($model);
                    break;

                // Add more cases as needed for other models
                default:
                    // No stock update needed for this model type
                    break;
            }
        }
    }

    /**
     * Update division stock for stock addition (e.g., AtkStockRequest, MarketingMediaStockRequest)
     *
     * @param  mixed  $stockRequest  The approved stock request
     */
    private function updateStockForAddition($stockRequest): void
    {
        \Log::info('StockUpdateService: updateStockForAddition called', [
            'model_id' => $stockRequest->id,
            'model_type' => get_class($stockRequest),
            'timestamp' => now()->toISOString()
        ]);

        // Load the items to ensure they are available
        $stockRequest->load('items.item');

        // Determine the correct division stock model based on the request type
        $divisionStockModel = match (get_class($stockRequest)) {
            AtkStockRequest::class => AtkDivisionStock::class,
            MarketingMediaStockRequest::class => MarketingMediaDivisionStock::class,
            default => AtkDivisionStock::class, // Default fallback
        };

        // Process each unique item to prevent duplicate processing
        $processedItems = [];
        
        foreach ($stockRequest->items as $requestItem) {
            $itemKey = $requestItem->item_id;
            
            // Skip if this item has already been processed to prevent duplicate processing
            if (in_array($itemKey, $processedItems)) {
                \Log::warning('StockUpdateService: Duplicate item detected and skipped', [
                    'request_id' => $stockRequest->id,
                    'item_id' => $requestItem->item_id,
                    'quantity' => $requestItem->quantity,
                ]);
                continue;
            }
            
            $processedItems[] = $itemKey;
            
            \Log::info('StockUpdateService: Processing stock request item', [
                'request_id' => $stockRequest->id,
                'item_id' => $requestItem->item_id,
                'quantity' => $requestItem->quantity,
                'item_details' => $requestItem->item->name ?? 'unknown'
            ]);
            
            // For MarketingMedia models, we need to include category_id in defaults
            if ($divisionStockModel === MarketingMediaDivisionStock::class) {
                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $stockRequest->division_id,
                        'item_id' => $requestItem->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        'max_stock_limit' => 0, // MarketingMediaDivisionStock has this field
                        'category_id' => $requestItem->category_id,  // Include category_id for MarketingMedia models
                    ]
                );
            } else {
                // Get category_id from the item itself for AtkDivisionStock
                $item = $requestItem->item;
                $categoryId = $item ? $item->category_id : null;

                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $stockRequest->division_id,
                        'item_id' => $requestItem->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        'moving_average_cost' => 0, // Initialize MAC to 0
                        'category_id' => $categoryId,
                    ]
                );
            }

            // Determine unit cost for the item
            $incomingUnitCost = 0;
            if (get_class($stockRequest) === AtkStockRequest::class) {
                // Get the active price with the latest effective_date (similar to the form)
                $priceModel = $requestItem->item->activePrice()->first();
                $incomingUnitCost = $priceModel && $priceModel->unit_price !== null ? $priceModel->unit_price : 0;
            }

            // Calculate new moving average cost using the formula:
            // New MAC = ((Old Stock × Old MAC) + (Incoming Stock × Incoming Unit Cost)) / (Old Stock + Incoming Stock)
            $oldStock = $divisionStock->current_stock;
            $oldMac = $divisionStock->moving_average_cost;
            $incomingStock = $requestItem->quantity;

            $totalValue = ($oldStock * $oldMac) + ($incomingStock * $incomingUnitCost);
            $totalQuantity = $oldStock + $incomingStock;

            // Calculate new MAC, ensuring we don't divide by zero
            $newMovingAverageCost = $totalQuantity > 0 ? $totalValue / $totalQuantity : $incomingUnitCost;

            // Update the quantity by adding the requested quantity and update MAC
            $newQuantity = $oldStock + $incomingStock;
            $divisionStock->update([
                'current_stock' => $newQuantity,
                'moving_average_cost' => (int) round($newMovingAverageCost), // Store as integer
            ]);

            \Log::info('StockUpdateService: Updated division stock', [
                'division_id' => $stockRequest->division_id,
                'item_id' => $requestItem->item_id,
                'old_stock' => $oldStock,
                'added_quantity' => $incomingStock,
                'new_quantity' => $newQuantity,
                'old_mac' => $oldMac,
                'new_mac' => (int) round($newMovingAverageCost),
                'unit_cost_used' => $incomingUnitCost
            ]);
        }
    }

    /**
     * Update division stock for stock reduction (e.g., AtkStockUsage, MarketingMediaStockUsage)
     * This also handles budget deduction for ATK usage
     *
     * @param  mixed  $stockUsage  The approved stock usage
     */
    private function updateStockForReduction($stockUsage): void
    {
        // Load the items to ensure they are available
        $stockUsage->load('items.item');

        // Determine the correct division stock model based on the request type
        $divisionStockModel = match (get_class($stockUsage)) {
            AtkStockUsage::class => AtkDivisionStock::class,
            MarketingMediaStockUsage::class => MarketingMediaDivisionStock::class,
            default => AtkDivisionStock::class, // Default fallback
        };

        // Calculate the total cost for budget deduction (only for AtkStockUsage)
        if (get_class($stockUsage) === AtkStockUsage::class) {
            $totalCost = $this->calculateUsageTotalCost($stockUsage);

            // Deduct the cost from the division's budget
            $this->budgetService->deductFromBudget(
                $stockUsage->division_id,
                $totalCost,
                $stockUsage->created_at->year ?? now()->year
            );
        }

        // Loop through each item in the stock usage and update the division stock
        foreach ($stockUsage->items as $usageItem) {
            // For MarketingMedia models, we need to include category_id in defaults
            if ($divisionStockModel === MarketingMediaDivisionStock::class) {
                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $stockUsage->division_id,
                        'item_id' => $usageItem->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        'max_stock_limit' => 0,
                        'category_id' => $usageItem->category_id,  // Include category_id for MarketingMedia models
                    ]
                );
            } else {
                // Get category_id from the item itself for AtkDivisionStock
                $item = $usageItem->item;
                $categoryId = $item ? $item->category_id : null;

                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $stockUsage->division_id,
                        'item_id' => $usageItem->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        'category_id' => $categoryId,
                        // AtkDivisionStock doesn't have max_stock_limit field
                    ]
                );
            }

            // Get the moving average cost for the item
            $unitCost = 0;
            if (get_class($stockUsage) === AtkStockUsage::class) {
                $unitCost = $divisionStock->moving_average_cost ?? 0;
            }

            // Reduce the quantity, ensuring it doesn't go below zero
            $newQuantity = max(0, $divisionStock->current_stock - $usageItem->quantity);
            $divisionStock->update([
                'current_stock' => $newQuantity,
            ]);

            \Log::info('StockUpdateService: Updated division stock for usage', [
                'division_id' => $stockUsage->division_id,
                'item_id' => $usageItem->item_id,
                'old_stock' => $divisionStock->current_stock + $usageItem->quantity, // original stock before reduction
                'reduced_quantity' => $usageItem->quantity,
                'new_quantity' => $newQuantity,
                'unit_cost_used' => $unitCost
            ]);
        }
    }

    /**
     * Calculate the total cost of an AtkStockUsage based on moving_average_cost
     */
    private function calculateUsageTotalCost($stockUsage): float
    {
        $totalCost = 0;

        foreach ($stockUsage->items as $usageItem) {
            // Get the moving_average_cost from AtkDivisionStock for the specific item and division
            $stock = AtkDivisionStock::where('division_id', $stockUsage->division_id)
                ->where('item_id', $usageItem->item_id)
                ->first();

            if ($stock && $stock->moving_average_cost > 0) {
                $itemCost = $stock->moving_average_cost * $usageItem->quantity;
                $totalCost += $itemCost;
            } else {
                // If no stock exists or MAC is 0, try to get the price from the item itself
                $priceModel = $usageItem->item->latestPrice()->first();
                $itemPrice = $priceModel?->price ?? 0;
                $itemCost = $itemPrice * $usageItem->quantity;
                $totalCost += $itemCost;
            }
        }

        return $totalCost;
    }

    /**
     * Update division stock based on a request_type field
     * This method is designed to work with a future unified model
     *
     * @param  mixed  $model  The model with request_type field
     */
    private function updateStockByRequestType($model): void
    {
        \Log::info('StockUpdateService: updateStockByRequestType called', [
            'model_id' => $model->id,
            'model_type' => get_class($model),
            'request_type' => $model->request_type ?? 'not_set',
            'timestamp' => now()->toISOString()
        ]);

        // Determine the operation based on request_type
        $operation = $model->request_type;

        // Determine the correct division stock model based on the model type
        $divisionStockModel = match (get_class($model)) {
            AtkStockRequest::class, AtkStockUsage::class => AtkDivisionStock::class,
            MarketingMediaStockRequest::class, MarketingMediaStockUsage::class => MarketingMediaDivisionStock::class,
            default => AtkDivisionStock::class, // Default fallback
        };

        // Determine default attributes based on the model type
        $defaultAttributes = match (get_class($model)) {
            MarketingMediaStockRequest::class, MarketingMediaStockUsage::class => [
                'current_stock' => 0,
                'max_limit' => 0, // MarketingMediaDivisionStock has this field
            ],
            default => [
                'current_stock' => 0,
                // AtkDivisionStock doesn't have max_limit field
            ],
        };

        // Set the correct relationship name and quantity field based on model type
        $itemsRelation = match (get_class($model)) {
            AtkStockRequest::class => 'items', // Uses the generic relationship we added
            AtkStockUsage::class => 'items',   // Uses the generic relationship we added
            default => $model->items_relation ?? 'items' // Default to 'items' relation
        };

        $quantityField = match (get_class($model)) {
            AtkStockRequest::class => 'quantity',
            AtkStockUsage::class => 'quantity',
            default => $model->quantity ?? 'quantity' // Default to 'quantity' field
        };

        // Load the items relationship to ensure it's available
        $model->load($itemsRelation);
        $model->load('items.item'); // Load the actual items for cost information

        // Get the items to process
        $items = $model->{$itemsRelation};

        foreach ($items as $item) {
            \Log::info('StockUpdateService: Processing item in updateStockByRequestType', [
                'model_id' => $model->id,
                'item_id' => $item->item_id,
                'quantity' => $item->{$quantityField} ?? 0,
                'item_details' => $item->item->name ?? 'unknown'
            ]);

            $quantity = $item->{$quantityField} ?? 0;

            // Skip if quantity is zero or negative
            if ($quantity <= 0) {
                continue;
            }

            // For MarketingMedia models, we need to include category_id in defaults
            if ($divisionStockModel === MarketingMediaDivisionStock::class) {
                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $model->division_id,
                        'item_id' => $item->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        'max_stock_limit' => 0,
                        'category_id' => $item->category_id,  // Include category_id for MarketingMedia models
                    ]
                );
            } else {
                // Get category_id from the item itself for AtkDivisionStock
                $itemModel = $item->item;
                $categoryId = $itemModel ? $itemModel->category_id : null;

                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $model->division_id,
                        'item_id' => $item->item_id,
                    ],
                    array_merge($defaultAttributes, [
                        'category_id' => $categoryId,
                    ])
                );
            }

            $currentStockBefore = $divisionStock->current_stock;
            
            \Log::info('StockUpdateService: Division stock before update', [
                'division_id' => $model->division_id,
                'item_id' => $item->item_id,
                'current_stock_before' => $currentStockBefore,
                'quantity_to_add' => $quantity,
            ]);

            // Calculate new quantity based on the operation type
            $newQuantity = match ($operation) {
                'addition', 'increase' => $currentStockBefore + $quantity,
                'reduction', 'decrease' => max(0, $currentStockBefore - $quantity), // Prevent negative stock
                default => $currentStockBefore // No change for other types
            };

            // Determine unit cost for the item
            $unitCost = 0;
            $transactionType = 'adjustment'; // Default to adjustment
            if (get_class($model) === AtkStockRequest::class && $operation === 'addition') {
                $priceModel = $item->item->activePrice()->first();
                $unitCost = $priceModel && $priceModel->unit_price !== null ? $priceModel->unit_price : 0;
                $transactionType = 'request';
            } elseif (get_class($model) === AtkStockUsage::class && $operation === 'reduction') {
                $unitCost = $divisionStock->moving_average_cost ?? 0;
                $transactionType = 'usage';
            }

            // Update the division stock
            $divisionStock->update([
                'current_stock' => $newQuantity,
            ]);

            \Log::info('StockUpdateService: Updated division stock in updateStockByRequestType', [
                'division_id' => $model->division_id,
                'item_id' => $item->item_id,
                'operation' => $operation,
                'quantity' => $quantity,
                'current_stock_before' => $currentStockBefore,
                'new_quantity' => $newQuantity,
                'current_stock_after' => $newQuantity
            ]);
        }
    }
}