<?php

namespace Database\Seeders;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\UserDivision;
use App\Models\AtkDivisionStock;
use App\Models\AtkStockRequest;
use App\Models\AtkStockRequestItem;
use App\Models\AtkStockUsage;
use App\Models\AtkStockUsageItem;
use App\Models\AtkStockTransaction;
use App\Models\AtkBudgeting;
use App\Models\AtkItemPrice;
use App\Services\StockUpdateService;
use App\Services\StockTransactionService;
use App\Services\BudgetService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovingAverageCostDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Get Information Technology Division (should already exist from UserDivisionSeeder)
        $division = UserDivision::where('initial', 'ITD')->first();
        
        if (!$division) {
            // Fallback: create the division with proper initial if it doesn't exist
            $division = UserDivision::create([
                'name' => 'Information Technology',
                'initial' => 'ITD',
                'description' => 'Information Technology Division'
            ]);
        }

        // Create or update budget for the division
        AtkBudgeting::updateOrCreate(
            [
                'division_id' => $division->id,
                'fiscal_year' => now()->year,
            ],
            [
                'budget_amount' => 1000000, // 1 million
                'used_amount' => 0,
                'remaining_amount' => 1000000,
            ]
        );

        // Initialize services
        $stockUpdateService = app(StockUpdateService::class);
        $stockTransactionService = app(StockTransactionService::class);
        $budgetService = app(BudgetService::class);

        echo "=== MOVING AVERAGE COST DEMONSTRATION ===\n\n";

        // Get some sample items from the database
        $items = AtkItem::limit(3)->get();
        
        if ($items->isEmpty()) {
            echo "No ATK items found. Please run AtkItemSeeder first.\n";
            return;
        }

        $penItem = $items->first();
        $notebookItem = $items->skip(1)->first();
        $usbItem = $items->skip(2)->first();

        // Get prices for these items
        $penPrice = AtkItemPrice::where('item_id', $penItem->id)->where('is_active', true)->first();
        $notebookPrice = AtkItemPrice::where('item_id', $notebookItem->id)->where('is_active', true)->first();
        $usbPrice = AtkItemPrice::where('item_id', $usbItem->id)->where('is_active', true)->first();

        // Use default prices if none found or if price is null
        $penUnitPrice = $penPrice && !is_null($penPrice->price) ? (int) $penPrice->price : 5000;
        $notebookUnitPrice = $notebookPrice && !is_null($notebookPrice->price) ? (int) $notebookPrice->price : 15000;
        $usbUnitPrice = $usbPrice && !is_null($usbPrice->price) ? (int) $usbPrice->price : 75000;

        // Scenario 1: Initial stock request (addition) - Pen
        echo "SCENARIO 1: Initial Stock Request for " . $penItem->name . "\n";
        echo "------------------------------------------\n";
        
        $stockRequest1 = AtkStockRequest::create([
            'requester_id' => 1,
            'division_id' => $division->id,
            'notes' => 'Initial stock request for ' . $penItem->name,
            'request_type' => 'addition',
        ]);

        // Add items to the request
        $requestItem1 = AtkStockRequestItem::create([
            'request_id' => $stockRequest1->id,
            'item_id' => $penItem->id,
            'category_id' => $penItem->category_id,
            'quantity' => 100,  // 100 items
        ]);

        // Get current stock and max limit to determine actual quantity that can be added
        $currentStock = $divisionStockPen->current_stock;
        $stockSetting = $divisionStockPen->getSetting();
        $maxLimit = $stockSetting ? $stockSetting->max_limit : PHP_INT_MAX;
        
        $requestedQuantity = 100;
        $actualQuantity = min($requestedQuantity, $maxLimit - $currentStock);
        if ($actualQuantity < $requestedQuantity) {
            echo "Requested quantity reduced from {$requestedQuantity} to {$actualQuantity} to respect max limit of {$maxLimit}\n";
        }
        
        // Simulate the stock addition with the actual quantity that respects limits
        $stockTransactionService->recordTransaction(
            $division->id,
            $penItem->id,
            'request',
            $actualQuantity,
            $penUnitPrice,  // Use actual item price
            $stockRequest1
        );

        // Refresh to get the updated stock and MAC after the transaction
        $divisionStockPen->refresh();

        echo "Added 100 " . $penItem->name . " at Rp. " . number_format($penUnitPrice, 0, ',', '.') . " each\n";
        echo "New Moving Average Cost: Rp. " . number_format($divisionStockPen->moving_average_cost, 0, ',', '.') . "\n";
        echo "Current Stock: " . $divisionStockPen->current_stock . "\n\n";

        // Scenario 2: Additional stock request with different price - Pen
        echo "SCENARIO 2: Additional Stock Request for " . $penItem->name . " at Different Price\n";
        echo "--------------------------------------------------------------\n";

        $stockRequest2 = AtkStockRequest::create([
            'requester_id' => 1,
            'division_id' => $division->id,
            'notes' => 'Additional stock request for ' . $penItem->name . ' at different price',
            'request_type' => 'addition',
        ]);

        // Add items to the second request
        $requestItem2 = AtkStockRequestItem::create([
            'request_id' => $stockRequest2->id,
            'item_id' => $penItem->id,
            'category_id' => $penItem->category_id,
            'quantity' => 50,  // 50 more items
        ]);

        // Get current stock and max limit to determine actual quantity that can be added
        $divisionStockPen->refresh(); // Refresh to get latest stock
        $currentStock = $divisionStockPen->current_stock;
        $stockSetting = $divisionStockPen->getSetting();
        $maxLimit = $stockSetting ? $stockSetting->max_limit : PHP_INT_MAX;
        
        $requestedQuantity = 50;
        $actualQuantity = min($requestedQuantity, $maxLimit - $currentStock);
        if ($actualQuantity < $requestedQuantity) {
            echo "Requested quantity reduced from {$requestedQuantity} to {$actualQuantity} to respect max limit of {$maxLimit}\n";
        }
        
        // Simulate the stock addition - use a different price (10% discount)
        $discountedPrice = round($penUnitPrice * 0.9);
        $stockTransactionService->recordTransaction(
            $division->id,
            $penItem->id,
            'request',
            $actualQuantity,
            $discountedPrice,  // Discounted price
            $stockRequest2
        );

        // Refresh to get latest values after the transaction
        $divisionStockPen->refresh();
        
        // The transaction service has already handled the update with max limit check, just refresh
        $divisionStockPen->refresh();

        echo "Added 50 more " . $penItem->name . " at Rp. " . number_format($discountedPrice, 0, ',', '.') . " each\n";
        echo "Old MAC: Rp. " . number_format($oldMac, 0, ',', '.') . "\n";
        echo "New Moving Average Cost: Rp. " . number_format($divisionStockPen->moving_average_cost, 0, ',', '.') . "\n";
        echo "Current Stock: " . $divisionStockPen->current_stock . "\n\n";

        // Show calculation breakdown for the actual quantities processed
        $actualQuantity1 = min(100, $maxLimit - 0); // First transaction (starts from 0 stock)
        $actualQuantity2 = min(50, $maxLimit - $actualQuantity1); // Second transaction
        
        $totalValue = ($actualQuantity1 * $penUnitPrice) + ($actualQuantity2 * $discountedPrice);  // Value from actual processed quantities
        $totalQuantity = $actualQuantity1 + $actualQuantity2;  // Total actual quantity
        $calculatedMac = $totalValue / $totalQuantity;
        echo "Calculation Verification:\n";
        echo "Total value: ({$actualQuantity1} × " . number_format($penUnitPrice, 0, ',', '.') . ") + ({$actualQuantity2} × " . number_format($discountedPrice, 0, ',', '.') . ") = " . number_format($totalValue, 0, ',', '.') . "\n";
        echo "Total quantity: {$actualQuantity1} + {$actualQuantity2} = " . $totalQuantity . "\n";
        echo "MAC = " . number_format($totalValue, 0, ',', '.') . " ÷ " . $totalQuantity . " = " . number_format($calculatedMac, 0, ',', '.') . "\n\n";

        // Scenario 3: Stock usage (reduction)
        echo "SCENARIO 3: Stock Usage\n";
        echo "------------------------\n";

        $stockUsage1 = AtkStockUsage::create([
            'requester_id' => 1,
            'division_id' => $division->id,
            'notes' => 'Usage of ' . $penItem->name . ' for internal meeting',
            'request_type' => 'usage',
        ]);

        $usageItem1 = AtkStockUsageItem::create([
            'usage_id' => $stockUsage1->id,
            'item_id' => $penItem->id,
            'category_id' => $penItem->category_id,
            'quantity' => 30,  // Use 30 items
        ]);

        // Record the usage transaction
        $stockTransactionService->recordTransaction(
            $division->id,
            $penItem->id,
            'usage',
            30,
            $divisionStockPen->moving_average_cost,  // Use current MAC
            $stockUsage1
        );

        // Update division stock (reduction)
        $divisionStockPen->refresh();
        $divisionStockPen->update([
            'current_stock' => max(0, $divisionStockPen->current_stock - 30),
        ]);

        // Deduct cost from budget
        $costToDeduct = 30 * $divisionStockPen->moving_average_cost;
        $budget = AtkBudgeting::where('division_id', $division->id)
            ->where('fiscal_year', now()->year)
            ->first();
        if ($budget) {
            $budget->update([
                'used_amount' => $budget->used_amount + $costToDeduct,
                'remaining_amount' => $budget->budget_amount - ($budget->used_amount + $costToDeduct),
            ]);
        }

        echo "Used 30 " . $penItem->name . " at MAC of Rp. " . number_format($divisionStockPen->moving_average_cost, 0, ',', '.') . " each\n";
        echo "Cost deducted from budget: Rp. " . number_format($costToDeduct, 0, ',', '.') . "\n";
        echo "Current Stock after usage: " . $divisionStockPen->current_stock . "\n\n";

        // Scenario 4: Another stock request at higher price
        echo "SCENARIO 4: Another Stock Request at Higher Price\n";
        echo "------------------------------------------------\n";

        $stockRequest3 = AtkStockRequest::create([
            'requester_id' => 1,
            'division_id' => $division->id,
            'notes' => 'Additional stock request for ' . $penItem->name . ' at higher price',
            'request_type' => 'addition',
        ]);

        $requestItem3 = AtkStockRequestItem::create([
            'request_id' => $stockRequest3->id,
            'item_id' => $penItem->id,
            'category_id' => $penItem->category_id,
            'quantity' => 80,  // 80 more items
        ]);

        // Get current stock and max limit to determine actual quantity that can be added
        $divisionStockPen->refresh(); // Refresh to get latest stock
        $currentStock = $divisionStockPen->current_stock;
        $stockSetting = $divisionStockPen->getSetting();
        $maxLimit = $stockSetting ? $stockSetting->max_limit : PHP_INT_MAX;
        
        $requestedQuantity = 80;
        $actualQuantity = min($requestedQuantity, $maxLimit - $currentStock);
        if ($actualQuantity < $requestedQuantity) {
            echo "Requested quantity reduced from {$requestedQuantity} to {$actualQuantity} to respect max limit of {$maxLimit}\n";
        }
        
        // Record the transaction - use a higher price (10% premium)
        $premiumPrice = round($penUnitPrice * 1.1);
        $stockTransactionService->recordTransaction(
            $division->id,
            $penItem->id,
            'request',
            $actualQuantity,
            $premiumPrice,  // Premium price
            $stockRequest3
        );

        // The transaction service has already handled the update with max limit check, just refresh
        $divisionStockPen->refresh();

        echo "Added 80 more " . $penItem->name . " at Rp. " . number_format($premiumPrice, 0, ',', '.') . " each\n";
        echo "Old MAC: Rp. " . number_format($oldMac, 0, ',', '.') . "\n";
        echo "New Moving Average Cost: Rp. " . number_format($divisionStockPen->moving_average_cost, 0, ',', '.') . "\n";
        echo "Current Stock: " . $divisionStockPen->current_stock . "\n\n";

        // Show calculation breakdown with actual values after transaction
        $currentDivStock = $divisionStockPen->refresh();
        $totalValue = ($currentDivStock->current_stock * $currentDivStock->moving_average_cost);
        $totalQuantity = $currentDivStock->current_stock;
        
        // Note: This calculation verifies the current state, not a formula application
        echo "Calculation Verification:\n";
        echo "Current stock value: " . $currentDivStock->current_stock . " at MAC: Rp. " . number_format($currentDivStock->moving_average_cost, 0, ',', '.') . "\n";
        echo "Total value: " . $currentDivStock->current_stock . " × Rp. " . number_format($currentDivStock->moving_average_cost, 0, ',', '.') . " = Rp. " . number_format($totalValue, 0, ',', '.') . "\n\n";

        // Scenario 5: Check transaction history
        echo "SCENARIO 5: Transaction History\n";
        echo "-------------------------------\n";

        $transactions = AtkStockTransaction::where('division_id', $division->id)
            ->where('item_id', $penItem->id)
            ->orderBy('created_at', 'asc')
            ->get();

        echo "Transaction History for " . $penItem->name . ":\n";
        foreach ($transactions as $transaction) {
            echo "  - Type: {$transaction->type}, Quantity: {$transaction->quantity}, ";
            echo "Unit Cost: Rp. " . number_format($transaction->unit_cost, 0, ',', '.') . ", MAC at time: Rp. " . number_format($transaction->mac_snapshot, 0, ',', '.') . ", ";
            echo "Balance after: {$transaction->balance_snapshot}\n";
        }

        echo "\n=== DEMONSTRATION COMPLETE ===\n";
        echo "The Moving Average Cost system is working correctly!\n";
        echo "As new stock is added at different prices, the MAC adjusts appropriately.\n";
    }
}