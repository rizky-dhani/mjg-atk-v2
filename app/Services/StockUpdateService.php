<?php

namespace App\Services;

use App\Models\AtkDivisionStock;
use App\Models\AtkStockRequest;
use App\Models\AtkStockUsage;
use App\Models\MarketingMediaStockRequest;

class StockUpdateService
{
    /**
     * Handle stock updates for various model types when they are fully approved
     *
     * @param  mixed  $model  The approved model that may require stock updates
     */
    public function handleStockUpdates($model): void
    {
        $modelClass = get_class($model);

        // Check if the model has a request_type field (for future unified model)
        if (isset($model->request_type)) {
            // Use the request_type field to determine the operation
            $this->updateStockByRequestType($model);
        } else {
            // For the current separate models, use the existing logic
            switch ($modelClass) {
                case \App\Models\AtkStockRequest::class:
                case \App\Models\MarketingMediaStockRequest::class:
                    $this->updateStockForAddition($model);
                    break;

                case \App\Models\AtkStockUsage::class:
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
        // Load the items to ensure they are available
        $stockRequest->load('items');

        // Determine the correct division stock model based on the request type
        $divisionStockModel = match (get_class($stockRequest)) {
            \App\Models\AtkStockRequest::class => \App\Models\AtkDivisionStock::class,
            \App\Models\MarketingMediaStockRequest::class => \App\Models\MarketingMediaDivisionStock::class,
            default => \App\Models\AtkDivisionStock::class, // Default fallback
        };

        // Loop through each item in the stock request and update the division stock
        foreach ($stockRequest->items as $requestItem) {
            // For MarketingMedia models, we need to include category_id in defaults
            if ($divisionStockModel === \App\Models\MarketingMediaDivisionStock::class) {
                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $stockRequest->division_id,
                        'item_id' => $requestItem->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        'max_stock_limit' => 0,
                        'category_id' => $requestItem->category_id,  // Include category_id for MarketingMedia models
                    ]
                );
            } else {
                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $stockRequest->division_id,
                        'item_id' => $requestItem->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        // AtkDivisionStock doesn't have max_stock_limit field
                    ]
                );
            }

            // Update the quantity by adding the requested quantity
            $newQuantity = $divisionStock->current_stock + $requestItem->quantity;
            $divisionStock->update([
                'current_stock' => $newQuantity,
            ]);
        }
    }

    /**
     * Update division stock for stock reduction (e.g., AtkStockUsage, MarketingMediaStockUsage)
     *
     * @param  mixed  $stockUsage  The approved stock usage
     */
    private function updateStockForReduction($stockUsage): void
    {
        // Load the items to ensure they are available
        $stockUsage->load('items');

        // Determine the correct division stock model based on the request type
        $divisionStockModel = match (get_class($stockUsage)) {
            \App\Models\AtkStockUsage::class => \App\Models\AtkDivisionStock::class,
            \App\Models\MarketingMediaStockUsage::class => \App\Models\MarketingMediaDivisionStock::class,
            default => \App\Models\AtkDivisionStock::class, // Default fallback
        };

        // Loop through each item in the stock usage and update the division stock
        foreach ($stockUsage->items as $usageItem) {
            // For MarketingMedia models, we need to include category_id in defaults
            if ($divisionStockModel === \App\Models\MarketingMediaDivisionStock::class) {
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
                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $stockUsage->division_id,
                        'item_id' => $usageItem->item_id,
                    ],
                    [
                        'current_stock' => 0,
                        // AtkDivisionStock doesn't have max_stock_limit field
                    ]
                );
            }

            // Reduce the quantity, ensuring it doesn't go below zero
            $newQuantity = max(0, $divisionStock->current_stock - $usageItem->quantity);
            $divisionStock->update([
                'current_stock' => $newQuantity,
            ]);
        }
    }

    /**
     * Update division stock based on a request_type field
     * This method is designed to work with a future unified model
     *
     * @param  mixed  $model  The model with request_type field
     */
    private function updateStockByRequestType($model): void
    {
        // Determine the operation based on request_type
        $operation = $model->request_type;

        // Determine the correct division stock model based on the model type
        $divisionStockModel = match (get_class($model)) {
            \App\Models\AtkStockRequest::class, \App\Models\AtkStockUsage::class => \App\Models\AtkDivisionStock::class,
            \App\Models\MarketingMediaStockRequest::class, \App\Models\MarketingMediaStockUsage::class => \App\Models\MarketingMediaDivisionStock::class,
            default => \App\Models\AtkDivisionStock::class, // Default fallback
        };

        // Determine default attributes based on the model type
        $defaultAttributes = match (get_class($model)) {
            \App\Models\MarketingMediaStockRequest::class, \App\Models\MarketingMediaStockUsage::class => [
                'current_stock' => 0,
                'max_stock_limit' => 0, // MarketingMediaDivisionStock has this field
            ],
            default => [
                'current_stock' => 0,
                // AtkDivisionStock doesn't have max_stock_limit field
            ],
        };

        // Set the correct relationship name and quantity field based on model type
        $itemsRelation = match (get_class($model)) {
            \App\Models\AtkStockRequest::class => 'items', // Uses the generic relationship we added
            \App\Models\AtkStockUsage::class => 'items',   // Uses the generic relationship we added
            default => $model->items_relation ?? 'items' // Default to 'items' relation
        };

        $quantityField = match (get_class($model)) {
            \App\Models\AtkStockRequest::class => 'quantity',
            \App\Models\AtkStockUsage::class => 'quantity',
            default => $model->quantity ?? 'quantity' // Default to 'quantity' field
        };

        // Load the items relationship to ensure it's available
        $model->load($itemsRelation);

        // Get the items to process
        $items = $model->{$itemsRelation};

        foreach ($items as $item) {
            $quantity = $item->{$quantityField} ?? 0;

            // Skip if quantity is zero or negative
            if ($quantity <= 0) {
                continue;
            }

            // For MarketingMedia models, we need to include category_id in defaults
            if ($divisionStockModel === \App\Models\MarketingMediaDivisionStock::class) {
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
                $divisionStock = $divisionStockModel::firstOrCreate(
                    [
                        'division_id' => $model->division_id,
                        'item_id' => $item->item_id,
                    ],
                    $defaultAttributes
                );
            }

            // Calculate new quantity based on the operation type
            $newQuantity = match ($operation) {
                'addition', 'increase' => $divisionStock->current_stock + $quantity,
                'reduction', 'decrease' => max(0, $divisionStock->current_stock - $quantity), // Prevent negative stock
                default => $divisionStock->current_stock // No change for other types
            };

            // Update the division stock
            $divisionStock->update([
                'current_stock' => $newQuantity,
            ]);
        }
    }
}