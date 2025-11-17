<?php

namespace App\Services;

use App\Models\AtkStockUsage;
use App\Models\AtkStockRequest;
use App\Models\AtkDivisionStock;
use App\Models\AtkStockUsageItem;
use App\Models\MarketingMediaStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaDivisionStock;
use App\Services\StockTransactionService;

class StockUpdateService
{
    protected BudgetService $budgetService;
    protected StockTransactionService $stockTransactionService;
    
    public function __construct(BudgetService $budgetService, StockTransactionService $stockTransactionService)
    {
        $this->budgetService = $budgetService;
        $this->stockTransactionService = $stockTransactionService;
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

                case \App\Models\AtkTransferStock::class:
                    $this->updateStockForTransfer($model);
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
                // Get the active price with the latest effective_date
                $priceModel = $requestItem->item->activePrice()->first();
                
                \Log::info('StockUpdateService: Price retrieval for item', [
                    'item_id' => $requestItem->item_id,
                    'request_id' => $stockRequest->id,
                    'price_model_exists' => $priceModel ? true : false,
                    'price_model_details' => $priceModel ? [
                        'id' => $priceModel->id,
                        'unit_price' => $priceModel->unit_price,
                        'is_active' => $priceModel->is_active,
                        'effective_date' => $priceModel->effective_date
                    ] : null
                ]);
                
                if ($priceModel && isset($priceModel->unit_price) && $priceModel->unit_price !== null && $priceModel->unit_price > 0) {
                    $incomingUnitCost = $priceModel->unit_price;
                    \Log::info('StockUpdateService: Using active price for MAC calculation', [
                        'item_id' => $requestItem->item_id,
                        'unit_price_used' => $incomingUnitCost
                    ]);
                } else {
                    // Fallback: try to get the latest price if no active price exists
                    $fallbackPriceModel = $requestItem->item->latestPrice()->first();
                    \Log::info('StockUpdateService: No active price found, checking fallback', [
                        'item_id' => $requestItem->item_id,
                        'fallback_price_exists' => $fallbackPriceModel ? true : false,
                        'fallback_price_details' => $fallbackPriceModel ? [
                            'id' => $fallbackPriceModel->id,
                            'unit_price' => $fallbackPriceModel->unit_price,
                            'is_active' => $fallbackPriceModel->is_active,
                            'effective_date' => $fallbackPriceModel->effective_date
                        ] : null
                    ]);
                    
                    if ($fallbackPriceModel && isset($fallbackPriceModel->unit_price) && $fallbackPriceModel->unit_price !== null && $fallbackPriceModel->unit_price > 0) {
                        $incomingUnitCost = $fallbackPriceModel->unit_price;
                        \Log::info('StockUpdateService: Using fallback price for MAC calculation', [
                            'item_id' => $requestItem->item_id,
                            'unit_price_used' => $incomingUnitCost
                        ]);
                    } else {
                        \Log::warning('No price found for item during stock update', [
                            'item_id' => $requestItem->item_id,
                            'request_id' => $stockRequest->id,
                            'price_model' => $priceModel ? $priceModel->toArray() : null,
                            'fallback_model' => $fallbackPriceModel ? $fallbackPriceModel->toArray() : null
                        ]);
                        // Use 0 as final fallback - this will result in MAC being 0 if no pricing info is available
                    }
                }
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
            
            // Record the transaction for tracking purposes
            if (get_class($stockRequest) === AtkStockRequest::class) {
                $this->stockTransactionService->recordTransactionOnly(
                    $stockRequest->division_id,
                    $requestItem->item_id,
                    'request',
                    $requestItem->quantity,
                    $incomingUnitCost,
                    $stockRequest
                );
            }
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
            // Use the stored potential_cost that was calculated when the form was submitted
            $totalCost = $stockUsage->potential_cost;

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
            
            // Record the transaction for tracking purposes
            if (get_class($stockUsage) === AtkStockUsage::class) {
                $this->stockTransactionService->recordTransactionOnly(
                    $stockUsage->division_id,
                    $usageItem->item_id,
                    'usage',
                    $usageItem->quantity,
                    $unitCost,
                    $stockUsage
                );
            }
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
                // Get the active price with the latest effective_date
                $priceModel = $item->item->activePrice()->first();
                
                \Log::info('StockUpdateService: Price retrieval for item in updateStockByRequestType', [
                    'item_id' => $item->item_id,
                    'model_id' => $model->id,
                    'model_type' => get_class($model),
                    'price_model_exists' => $priceModel ? true : false,
                    'price_model_details' => $priceModel ? [
                        'id' => $priceModel->id,
                        'unit_price' => $priceModel->unit_price,
                        'is_active' => $priceModel->is_active,
                        'effective_date' => $priceModel->effective_date
                    ] : null
                ]);
                
                if ($priceModel && isset($priceModel->unit_price) && $priceModel->unit_price !== null && $priceModel->unit_price > 0) {
                    $unitCost = $priceModel->unit_price;
                    \Log::info('StockUpdateService: Using active price for MAC calculation in updateStockByRequestType', [
                        'item_id' => $item->item_id,
                        'unit_price_used' => $unitCost
                    ]);
                } else {
                    // Fallback: try to get the latest price if no active price exists
                    $fallbackPriceModel = $item->item->latestPrice()->first();
                    \Log::info('StockUpdateService: No active price found in updateStockByRequestType, checking fallback', [
                        'item_id' => $item->item_id,
                        'model_id' => $model->id,
                        'model_type' => get_class($model),
                        'fallback_price_exists' => $fallbackPriceModel ? true : false,
                        'fallback_price_details' => $fallbackPriceModel ? [
                            'id' => $fallbackPriceModel->id,
                            'unit_price' => $fallbackPriceModel->unit_price,
                            'is_active' => $fallbackPriceModel->is_active,
                            'effective_date' => $fallbackPriceModel->effective_date
                        ] : null
                    ]);
                    
                    if ($fallbackPriceModel && isset($fallbackPriceModel->unit_price) && $fallbackPriceModel->unit_price !== null && $fallbackPriceModel->unit_price > 0) {
                        $unitCost = $fallbackPriceModel->unit_price;
                        \Log::info('StockUpdateService: Using fallback price for MAC calculation in updateStockByRequestType', [
                            'item_id' => $item->item_id,
                            'unit_price_used' => $unitCost
                        ]);
                    } else {
                        \Log::warning('No price found for item during stock update by request type', [
                            'item_id' => $item->item_id,
                            'model_id' => $model->id,
                            'model_type' => get_class($model),
                            'price_model' => $priceModel ? $priceModel->toArray() : null,
                            'fallback_model' => $fallbackPriceModel ? $fallbackPriceModel->toArray() : null
                        ]);
                    }
                }
                $transactionType = 'request';
            } elseif (get_class($model) === AtkStockUsage::class && $operation === 'reduction') {
                $unitCost = $divisionStock->moving_average_cost ?? 0;
                $transactionType = 'usage';
            }

            // Calculate new MAC if this is an addition and we have a unit cost
            $newMovingAverageCost = $divisionStock->moving_average_cost; // Default to existing MAC
            if ($operation === 'addition' && $unitCost > 0) {
                // Calculate new moving average cost using the formula:
                // New MAC = ((Old Stock × Old MAC) + (Incoming Stock × Incoming Unit Cost)) / (Old Stock + Incoming Stock)
                $oldStock = $divisionStock->current_stock;
                $oldMac = $divisionStock->moving_average_cost;

                $totalValue = ($oldStock * $oldMac) + ($quantity * $unitCost);
                $totalQuantity = $oldStock + $quantity;

                // Calculate new MAC, ensuring we don't divide by zero
                $newMovingAverageCost = $totalQuantity > 0 ? $totalValue / $totalQuantity : $unitCost;
            }

            // Update the division stock
            $divisionStock->update([
                'current_stock' => $newQuantity,
                'moving_average_cost' => (int) round($newMovingAverageCost), // Store as integer
            ]);

            \Log::info('StockUpdateService: Updated division stock in updateStockByRequestType', [
                'division_id' => $model->division_id,
                'item_id' => $item->item_id,
                'operation' => $operation,
                'quantity' => $quantity,
                'current_stock_before' => $currentStockBefore,
                'new_quantity' => $newQuantity,
                'current_stock_after' => $newQuantity,
                'old_mac' => $divisionStock->moving_average_cost,
                'new_mac' => (int) round($newMovingAverageCost)
            ]);
            
            // Record the transaction for tracking purposes
            if (in_array(get_class($model), [AtkStockRequest::class, AtkStockUsage::class])) {
                $this->stockTransactionService->recordTransactionOnly(
                    $model->division_id,
                    $item->item_id,
                    $transactionType,  // 'request', 'usage', etc.
                    $quantity,
                    $unitCost,
                    $model
                );
            }
        }
        
        // For AtkStockUsage reduction operations, also handle budget deduction
        if (get_class($model) === AtkStockUsage::class && in_array($operation, ['reduction', 'decrease'])) {
            // Get the potential cost from the model
            $totalCost = $model->potential_cost ?? 0;
            
            // Deduct the cost from the division's budget
            $this->budgetService->deductFromBudget(
                $model->division_id,
                $totalCost,
                $model->created_at->year ?? now()->year
            );
        }
    }

    /**
     * Update division stock for stock transfer between divisions (AtkTransferStock)
     * This reduces stock from source division and adds to requesting division
     *
     * @param  \App\Models\AtkTransferStock  $transferStock  The approved transfer stock request
     */
    private function updateStockForTransfer($transferStock): void
    {
        \Log::info('StockUpdateService: updateStockForTransfer called', [
            'transfer_id' => $transferStock->id,
            'transfer_number' => $transferStock->transfer_number,
            'source_division_id' => $transferStock->source_division_id,
            'requesting_division_id' => $transferStock->requesting_division_id,
            'timestamp' => now()->toISOString()
        ]);

        // Load the transfer stock items to ensure they are available
        $transferStock->load('transferStockItems.item');

        // Loop through each transfer stock item and update the division stocks
        foreach ($transferStock->transferStockItems as $transferItem) {
            $quantity = $transferItem->quantity;
            $itemId = $transferItem->item_id;
            $sourceDivisionId = $transferStock->source_division_id;
            $requestingDivisionId = $transferStock->requesting_division_id;

            \Log::info('StockUpdateService: Processing transfer item', [
                'transfer_id' => $transferStock->id,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'source_division_id' => $sourceDivisionId,
                'requesting_division_id' => $requestingDivisionId
            ]);

            // Reduce stock from source division
            $sourceStock = AtkDivisionStock::where('division_id', $sourceDivisionId)
                ->where('item_id', $itemId)
                ->first();

            if ($sourceStock && $sourceStock->current_stock >= $quantity) {
                $newQuantity = $sourceStock->current_stock - $quantity;
                $sourceStock->update([
                    'current_stock' => $newQuantity,
                    // moving_average_cost remains unchanged for transfers
                ]);

                \Log::info('StockUpdateService: Updated source division stock', [
                    'division_id' => $sourceDivisionId,
                    'item_id' => $itemId,
                    'old_stock' => $sourceStock->current_stock,
                    'reduced_quantity' => $quantity,
                    'new_quantity' => $newQuantity,
                ]);

                // Record the transfer out transaction (negative quantity for reduction)
                $this->stockTransactionService->recordTransactionOnly(
                    $sourceDivisionId,
                    $itemId,
                    'transfer',
                    -$quantity,  // negative quantity to indicate a reduction
                    $sourceStock->moving_average_cost ?? 0,
                    $transferStock
                );
            } else {
                // If source doesn't have enough stock, log an error
                $sourceStockQuantity = $sourceStock ? $sourceStock->current_stock : 0;
                \Log::error('Insufficient stock for transfer from source division', [
                    'transfer_id' => $transferStock->id,
                    'item_id' => $itemId,
                    'required_quantity' => $quantity,
                    'available_quantity' => $sourceStockQuantity,
                    'source_division_id' => $sourceDivisionId,
                    'transfer_number' => $transferStock->transfer_number
                ]);

                // Continue to the next item instead of stopping the whole process
                continue;
            }

            // Add stock to requesting division
            $item = $transferItem->item; // Load the item to get category_id
            $categoryId = $item ? $item->category_id : null;

            $requestingStock = AtkDivisionStock::firstOrCreate(
                [
                    'division_id' => $requestingDivisionId,
                    'item_id' => $itemId,
                ],
                [
                    'current_stock' => 0,
                    'moving_average_cost' => 0, // Initialize to 0 for new records
                    'category_id' => $categoryId,
                ]
            );

            $newQuantity = $requestingStock->current_stock + $quantity;

            // For transfers, we should NOT update the MAC at all
            // The MAC should remain completely untouched - transfers don't change cost basis
            $requestingStock->update([
                'current_stock' => $newQuantity,
                // moving_average_cost remains completely untouched (transfer doesn't change MAC)
            ]);

            \Log::info('StockUpdateService: Updated requesting division stock', [
                'division_id' => $requestingDivisionId,
                'item_id' => $itemId,
                'old_stock' => $requestingStock->current_stock - $quantity,
                'added_quantity' => $quantity,
                'new_quantity' => $newQuantity,
            ]);

            // Record the transfer in transaction
            $this->stockTransactionService->recordTransactionOnly(
                $requestingDivisionId,
                $itemId,
                'transfer',
                $quantity,  // positive quantity to indicate an addition
                $sourceStock->moving_average_cost ?? 0,
                $transferStock
            );
        }
    }
}