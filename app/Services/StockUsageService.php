<?php

namespace App\Services;

use App\Models\AtkDivisionStock;
use App\Models\AtkStockUsage;
use App\Models\AtkStockUsageItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockUsageService
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Create a new stock usage
     *
     * @param  User  $user  The user creating the usage
     * @param  array  $data  The usage data
     */
    public function createStockUsage(User $user, array $data): AtkStockUsage
    {
        return DB::transaction(function () use ($user, $data) {
            // Get the selected division for the initial
            $selectedDivision = \App\Models\UserDivision::find($data['division_id']);
            $divisionInitial = $selectedDivision ? $selectedDivision->initial : 'USE';

            // Generate usage number
            $lastUsage = AtkStockUsage::orderBy('id', 'desc')->first();
            $nextId = $lastUsage ? $lastUsage->id + 1 : 1;
            $usageNumber = $divisionInitial.'-USE-'.str_pad($nextId, 8, '0', STR_PAD_LEFT);

            // Create the stock usage
            $stockUsage = new AtkStockUsage([
                'request_number' => $usageNumber,
                'requester_id' => $user->id,
                'division_id' => $data['division_id'],
                'notes' => $data['notes'] ?? null,
            ]);
            $stockUsage->save();

            // Create the usage items
            foreach ($data['items'] as $itemData) {
                // Get the moving_average_cost from AtkDivisionStock for this item and division
                $divisionStock = AtkDivisionStock::where('division_id', $data['division_id'])
                    ->where('item_id', $itemData['item_id'])
                    ->first();

                $movingAverageCost = $divisionStock ? $divisionStock->moving_average_cost : 0;

                $usageItem = new AtkStockUsageItem([
                    'usage_id' => $stockUsage->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'moving_average_cost' => $movingAverageCost,
                ]);
                $usageItem->save();
            }

            // Create approval for the stock usage
            $approval = $this->approvalService->createApproval($stockUsage, 'AtkStockUsage');

            return $stockUsage;
        });
    }

    /**
     * Approve a stock usage
     *
     * @param  AtkStockUsage  $stockUsage  The stock usage to approve
     * @param  User  $user  The user approving the usage
     * @param  string|null  $notes  Optional notes
     * @return bool True if the approval is completed, false if there are more steps
     */
    public function approveStockUsage(AtkStockUsage $stockUsage, User $user, ?string $notes = null): bool
    {
        $approval = $stockUsage->approval;

        if (! $approval) {
            throw new \Exception('No approval found for this stock usage');
        }

        // Process the approval step
        $isCompleted = $this->approvalService->processApprovalStep($approval, $user, 'approve', $notes);

        return $isCompleted;
    }

    /**
     * Reject a stock usage
     *
     * @param  AtkStockUsage  $stockUsage  The stock usage to reject
     * @param  User  $user  The user rejecting the usage
     * @param  string|null  $notes  Optional notes
     */
    public function rejectStockUsage(AtkStockUsage $stockUsage, User $user, ?string $notes = null): void
    {
        $approval = $stockUsage->approval;

        if (! $approval) {
            throw new \Exception('No approval found for this stock usage');
        }

        // Process the rejection
        $this->approvalService->processApprovalStep($approval, $user, 'reject', $notes);
    }

    /**
     * Update division stocks when a stock usage is approved
     *
     * @param  AtkStockUsage  $stockUsage  The approved stock usage
     */
    protected function updateDivisionStocks(AtkStockUsage $stockUsage): void
    {
        foreach ($stockUsage->atkStockUsageItems as $usageItem) {
            // Find the division stock record
            $divisionStock = AtkDivisionStock::where([
                'division_id' => $stockUsage->division_id,
                'item_id' => $usageItem->item_id,
            ])->first();

            if ($divisionStock) {
                // Check if there's enough stock
                if ($divisionStock->current_stock < $usageItem->quantity) {
                    throw new \Exception('Insufficient stock for item: '.$usageItem->item->name);
                }

                // Update the quantity
                $divisionStock->current_stock -= $usageItem->quantity;
                $divisionStock->save();
            } else {
                throw new \Exception('Stock record not found for item: '.$usageItem->item->name);
            }
        }
    }

    /**
     * Cancel a stock usage
     *
     * @param  AtkStockUsage  $stockUsage  The stock usage to cancel
     * @param  User  $user  The user cancelling the usage
     */
    public function cancelStockUsage(AtkStockUsage $stockUsage, User $user): void
    {
        $approval = $stockUsage->approval;

        if (! $approval) {
            throw new \Exception('No approval found for this stock usage');
        }

        // Cancel the approval
        $this->approvalService->cancelApproval($approval, $user);
    }
}
