<?php

namespace App\Services;

use App\Models\AtkStockUsage;
use App\Models\AtkStockUsageItem;
use App\Models\AtkDivisionStock;
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
     * @param User $user The user creating the usage
     * @param array $data The usage data
     * @return AtkStockUsage
     */
    public function createStockUsage(User $user, array $data): AtkStockUsage
    {
        return DB::transaction(function () use ($user, $data) {
            // Get the user's division for the initial
            $userDivision = $user->division;
            $divisionInitial = substr(strtoupper($userDivision->name), 0, 3);
            
            // Generate usage number
            $lastUsage = AtkStockUsage::orderBy('id', 'desc')->first();
            $nextId = $lastUsage ? $lastUsage->id + 1 : 1;
            $usageNumber = $divisionInitial . '-USE-' . str_pad($nextId, 8, '0', STR_PAD_LEFT);

            // Create the stock usage
            $stockUsage = new AtkStockUsage([
                'usage_number' => $usageNumber,
                'requester_id' => $user->id,
                'division_id' => $data['division_id'],
                'notes' => $data['notes'] ?? null
            ]);
            $stockUsage->save();

            // Create the usage items
            foreach ($data['items'] as $itemData) {
                $usageItem = new AtkStockUsageItem([
                    'usage_id' => $stockUsage->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_used' => $itemData['quantity']
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
     * @param AtkStockUsage $stockUsage The stock usage to approve
     * @param User $user The user approving the usage
     * @param string|null $notes Optional notes
     * @return bool True if the approval is completed, false if there are more steps
     */
    public function approveStockUsage(AtkStockUsage $stockUsage, User $user, ?string $notes = null): bool
    {
        $approval = $stockUsage->approval;
        
        if (!$approval) {
            throw new \Exception("No approval found for this stock usage");
        }

        // Process the approval step
        $isCompleted = $this->approvalService->processApprovalStep($approval, $user, 'approve', $notes);

        // If approval is completed, update the division stocks
        if ($isCompleted && $approval->status === 'approved') {
            $this->updateDivisionStocks($stockUsage);
        }

        return $isCompleted;
    }

    /**
     * Reject a stock usage
     *
     * @param AtkStockUsage $stockUsage The stock usage to reject
     * @param User $user The user rejecting the usage
     * @param string|null $notes Optional notes
     * @return void
     */
    public function rejectStockUsage(AtkStockUsage $stockUsage, User $user, ?string $notes = null): void
    {
        $approval = $stockUsage->approval;
        
        if (!$approval) {
            throw new \Exception("No approval found for this stock usage");
        }

        // Process the rejection
        $this->approvalService->processApprovalStep($approval, $user, 'reject', $notes);
    }

    /**
     * Update division stocks when a stock usage is approved
     *
     * @param AtkStockUsage $stockUsage The approved stock usage
     * @return void
     */
    protected function updateDivisionStocks(AtkStockUsage $stockUsage): void
    {
        foreach ($stockUsage->atkStockUsageItems as $usageItem) {
            // Find the division stock record
            $divisionStock = AtkDivisionStock::where([
                'division_id' => $stockUsage->division_id,
                'item_id' => $usageItem->item_id
            ])->first();
            
            if ($divisionStock) {
                // Check if there's enough stock
                if ($divisionStock->quantity < $usageItem->quantity_used) {
                    throw new \Exception("Insufficient stock for item: " . $usageItem->item->name);
                }
                
                // Update the quantity
                $divisionStock->quantity -= $usageItem->quantity_used;
                $divisionStock->save();
            } else {
                throw new \Exception("Stock record not found for item: " . $usageItem->item->name);
            }
        }
    }

    /**
     * Cancel a stock usage
     *
     * @param AtkStockUsage $stockUsage The stock usage to cancel
     * @param User $user The user cancelling the usage
     * @return void
     */
    public function cancelStockUsage(AtkStockUsage $stockUsage, User $user): void
    {
        $approval = $stockUsage->approval;
        
        if (!$approval) {
            throw new \Exception("No approval found for this stock usage");
        }

        // Cancel the approval
        $this->approvalService->cancelApproval($approval, $user);
    }
}