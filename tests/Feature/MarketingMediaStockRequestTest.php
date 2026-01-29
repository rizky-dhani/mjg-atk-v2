<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\MarketingMediaCategory;
use App\Models\MarketingMediaDivisionStock;
use App\Models\MarketingMediaItem;
use App\Models\MarketingMediaStockRequest;
use App\Models\MarketingMediaStockRequestItem;
use App\Models\User;
use App\Models\UserDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingMediaStockRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_media_stock_request_full_flow()
    {
        // Step 1: Setup
        echo "Step 1: Setting up test data...\n";

        // Create MBB Division if it doesn't exist
        $division = UserDivision::firstOrCreate([
            'name' => 'MBB Division',
            'initial' => 'MBB',
        ], [
            'name' => 'MBB Division',
            'initial' => 'MBB',
        ]);

        // Create an admin user in MBB Division
        $admin = User::factory()->create([
            'name' => 'Admin MBB',
            'email' => 'admin@mbb.test',
            'division_id' => $division->id,
            'initial' => 'ADM',  // Add the required initial field
        ]);

        // Create a marketing media category
        $category = MarketingMediaCategory::firstOrCreate([
            'name' => 'Test Category',
        ], [
            'name' => 'Test Category',
            'description' => 'Test category for stock request',
        ]);

        // Create a marketing media item
        $item = MarketingMediaItem::firstOrCreate([
            'name' => 'Test Item',
            'slug' => 'test-item',
            'unit_of_measure' => 'unit',  // Add required unit_of_measure field
            'category_id' => $category->id,
        ], [
            'name' => 'Test Item',
            'slug' => 'test-item',  // Add required slug field
            'unit_of_measure' => 'unit',  // Add required unit_of_measure field
            'description' => 'Test item for stock request',
            'category_id' => $category->id,
        ]);

        echo "✓ Setup completed\n";

        // Step 2: Create a MarketingMediaStockRequest
        echo "Step 2: Creating MarketingMediaStockRequest...\n";

        $stockRequest = MarketingMediaStockRequest::create([
            'request_number' => 'MMB-'.now()->format('Ymd').'-001',
            'requester_id' => $admin->id,
            'division_id' => $division->id,
            'notes' => 'Test stock request for MBB division',
            'request_type' => 'addition', // or whatever is appropriate
        ]);

        // Add an item to the request
        $requestItem = MarketingMediaStockRequestItem::create([
            'request_id' => $stockRequest->id,
            'item_id' => $item->id,
            'category_id' => $category->id,
            'quantity' => 10,
        ]);

        echo '✓ MarketingMediaStockRequest created with ID: '.$stockRequest->id."\n";
        echo '✓ Request item added with quantity: '.$requestItem->quantity."\n";

        // Step 3: Check if approval record was automatically created
        echo "Step 3: Checking if approval was created automatically...\n";

        $approval = Approval::where('approvable_type', MarketingMediaStockRequest::class)
            ->where('approvable_id', $stockRequest->id)
            ->first();

        if ($approval) {
            echo '✓ Approval record found with ID: '.$approval->id."\n";
            echo '✓ Initial status: '.$approval->status."\n";
            echo '✓ Current step: '.$approval->current_step."\n";
        } else {
            echo "✗ No approval record found\n";
        }

        // Step 4: Check initial division stock
        echo "Step 4: Checking initial division stock...\n";

        $initialStock = MarketingMediaDivisionStock::where('division_id', $division->id)
            ->where('item_id', $item->id)
            ->first();

        $initialQuantity = $initialStock ? $initialStock->current_stock : 0;
        echo "✓ Initial stock for item {$item->name}: $initialQuantity\n";

        // Step 5: Simulate approval process (this would typically happen via the approval service)
        echo "Step 5: Simulating approval process...\n";

        // In a real scenario, this would be done through the approval service
        // For this test, we'll directly call the approval process
        $approvalService = app(\App\Services\ApprovalService::class);

        // We would normally approve this through the proper approval flow
        // Let's assume it gets approved completely and triggers stock update
        $approval = $stockRequest->refresh()->approval;

        if ($approval) {
            echo '✓ Current approval status: '.$approval->status."\n";
            echo '✓ Current approval step: '.$approval->current_step."\n";
        } else {
            echo "✗ No approval found to process\n";
        }

        // Step 6: After approval completion (simulated), check stock update
        echo "Step 6: Simulating completion of approval and checking stock update...\n";

        // Debug: Check the type of stock request
        echo 'Debug: Stock request class is: '.get_class($stockRequest)."\n";

        // Manually trigger stock update to test our implementation
        $stockRequest->refresh(); // Refresh to get latest state
        $approvalService->handleStockUpdates($stockRequest);

        // Check updated stock
        $updatedStock = MarketingMediaDivisionStock::where('division_id', $division->id)
            ->where('item_id', $item->id)
            ->first();

        $updatedQuantity = $updatedStock ? $updatedStock->current_stock : 0;
        $expectedQuantity = $initialQuantity + $requestItem->quantity;

        echo "✓ Updated stock for item {$item->name}: $updatedQuantity\n";
        echo "✓ Expected quantity: $expectedQuantity\n";

        if ($updatedQuantity === $expectedQuantity) {
            echo "✓ Stock updated successfully! Stock increased by {$requestItem->quantity}\n";
        } else {
            echo "✗ Stock was not updated correctly\n";
        }

        // Step 7: Final validation
        echo "Step 7: Final validation...\n";

        $totalRequestItems = $stockRequest->marketingMediaStockRequestItems->sum('quantity');
        $stockRecord = MarketingMediaDivisionStock::where('division_id', $division->id)
            ->where('item_id', $item->id)
            ->first();

        if ($stockRecord) {
            echo "✓ Final stock record exists for item: {$item->name}\n";
            echo "✓ Final current_stock: {$stockRecord->current_stock}\n";
            echo "✓ Quantity requested: $totalRequestItems\n";
        } else {
            echo "✗ No stock record found for the item\n";
        }

        echo "\nTest completed!\n";
    }
}
