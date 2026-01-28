<?php

namespace App\Services;

use App\Models\AtkDivisionStock;
use App\Models\AtkStockRequest;
use App\Models\AtkStockRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockRequestService
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Create a new stock request
     *
     * @param  User  $user  The user creating the request
     * @param  array  $data  The request data
     */
    public function createStockRequest(User $user, array $data): AtkStockRequest
    {
        return DB::transaction(function () use ($user, $data) {
            // Get the selected division for the initial
            $selectedDivision = \App\Models\UserDivision::find($data['division_id']);
            $divisionInitial = $selectedDivision ? $selectedDivision->initial : 'ATK';

            // Generate request number
            $lastRequest = AtkStockRequest::orderBy('id', 'desc')->first();
            $nextId = $lastRequest ? $lastRequest->id + 1 : 1;
            $requestNumber = $divisionInitial.'-REQ-'.str_pad($nextId, 8, '0', STR_PAD_LEFT);

            // Create the stock request
            $stockRequest = new AtkStockRequest([
                'requester_id' => $user->id,
                'division_id' => $data['division_id'],
                'request_number' => $requestNumber,
                'notes' => $data['notes'] ?? null,
            ]);
            $stockRequest->save();

            // Create the request items
            foreach ($data['items'] as $itemData) {
                $requestItem = new AtkStockRequestItem([
                    'request_id' => $stockRequest->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                ]);
                $requestItem->save();
            }

            // Create approval for the stock request
            $approval = $this->approvalService->createApproval($stockRequest, 'AtkStockRequest');

            return $stockRequest;
        });
    }

    /**
     * Approve a stock request
     *
     * @param  AtkStockRequest  $stockRequest  The stock request to approve
     * @param  User  $user  The user approving the request
     * @param  string|null  $notes  Optional notes
     * @return bool True if the approval is completed, false if there are more steps
     */
    public function approveStockRequest(AtkStockRequest $stockRequest, User $user, ?string $notes = null): bool
    {
        $approval = $stockRequest->approval;

        if (! $approval) {
            throw new \Exception('No approval found for this stock request');
        }

        // Process the approval step
        $isCompleted = $this->approvalService->processApprovalStep($approval, $user, 'approve', $notes);

        // If approval is completed, update the division stocks
        // NOTE: Stock updates are handled by StockUpdateService through the approval process
        // to ensure proper MAC calculations and other business logic
        if ($isCompleted && $approval->status === 'approved') {
            // $this->updateDivisionStocks($stockRequest); // COMMENTED OUT - DUPLICATE PROCESSING
        }

        return $isCompleted;
    }

    /**
     * Reject a stock request
     *
     * @param  AtkStockRequest  $stockRequest  The stock request to reject
     * @param  User  $user  The user rejecting the request
     * @param  string|null  $notes  Optional notes
     */
    public function rejectStockRequest(AtkStockRequest $stockRequest, User $user, ?string $notes = null): void
    {
        $approval = $stockRequest->approval;

        if (! $approval) {
            throw new \Exception('No approval found for this stock request');
        }

        // Process the rejection
        $this->approvalService->processApprovalStep($approval, $user, 'reject', $notes);
    }

    /**
     * Update division stocks when a stock request is approved
     * NOTE: This method is currently not used - stock updates are handled by StockUpdateService
     * This is kept for reference or future modifications
     *
     * @param  AtkStockRequest  $stockRequest  The approved stock request
     */
    protected function updateDivisionStocks(AtkStockRequest $stockRequest): void
    {
        foreach ($stockRequest->atkStockRequestItems as $requestItem) {
            // Find or create the division stock record
            $divisionStock = AtkDivisionStock::firstOrNew([
                'division_id' => $stockRequest->division_id,
                'item_id' => $requestItem->item_id,
            ]);

            // Update the quantity - NOTE: field name corrected from 'quantity' to 'current_stock'
            $divisionStock->current_stock += $requestItem->quantity;
            $divisionStock->save();
        }
    }

    /**
     * Cancel a stock request
     *
     * @param  AtkStockRequest  $stockRequest  The stock request to cancel
     * @param  User  $user  The user cancelling the request
     */
    public function cancelStockRequest(AtkStockRequest $stockRequest, User $user): void
    {
        $approval = $stockRequest->approval;

        if (! $approval) {
            throw new \Exception('No approval found for this stock request');
        }

        // Cancel the approval
        $this->approvalService->cancelApproval($approval, $user);
    }
}
