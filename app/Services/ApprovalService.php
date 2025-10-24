<?php

namespace App\Services;

class ApprovalService
{
    protected ApprovalValidationService $validationService;
    protected ApprovalProcessingService $processingService;
    protected ApprovalHistoryService $historyService;
    protected StockUpdateService $stockUpdateService;

    public function __construct(
        ApprovalValidationService $validationService,
        ApprovalProcessingService $processingService,
        ApprovalHistoryService $historyService,
        StockUpdateService $stockUpdateService
    ) {
        $this->validationService = $validationService;
        $this->processingService = $processingService;
        $this->historyService = $historyService;
        $this->stockUpdateService = $stockUpdateService;
    }

    // Delegate validation methods
    public function canUserApprove($model, \App\Models\User $user): bool
    {
        return $this->validationService->canUserApprove($model, $user);
    }

    public function getEligibleApprovalSteps($model, \App\Models\User $user): \Illuminate\Support\Collection
    {
        return $this->validationService->getEligibleApprovalSteps($model, $user);
    }

    public function canUserApproveStockRequest(\App\Models\AtkStockRequest $stockRequest, \App\Models\User $user): bool
    {
        return $this->validationService->canUserApproveStockRequest($stockRequest, $user);
    }

    public function canUserApproveMarketingMediaStockRequest(\App\Models\MarketingMediaStockRequest $marketingMediaStockRequest, \App\Models\User $user): bool
    {
        return $this->validationService->canUserApproveMarketingMediaStockRequest($marketingMediaStockRequest, $user);
    }

    public function getMatchingApprovalStepsForStockRequest(\App\Models\AtkStockRequest $stockRequest, \App\Models\User $user): \Illuminate\Support\Collection
    {
        return $this->validationService->getMatchingApprovalStepsForStockRequest($stockRequest, $user);
    }

    public function getMatchingApprovalStepsForMarketingMediaStockRequest(\App\Models\MarketingMediaStockRequest $marketingMediaStockRequest, \App\Models\User $user): \Illuminate\Support\Collection
    {
        return $this->validationService->getMatchingApprovalStepsForMarketingMediaStockRequest($marketingMediaStockRequest, $user);
    }

    public function canUserApproveStockUsage(\App\Models\AtkStockUsage $stockUsage, \App\Models\User $user): bool
    {
        return $this->validationService->canUserApproveStockUsage($stockUsage, $user);
    }

    public function canUserApproveMarketingMediaStockUsage(\App\Models\MarketingMediaStockUsage $marketingMediaStockUsage, \App\Models\User $user): bool
    {
        return $this->validationService->canUserApproveMarketingMediaStockUsage($marketingMediaStockUsage, $user);
    }

    public function getMatchingApprovalStepsForStockUsage(\App\Models\AtkStockUsage $stockUsage, \App\Models\User $user): \Illuminate\Support\Collection
    {
        return $this->validationService->getMatchingApprovalStepsForStockUsage($stockUsage, $user);
    }

    // Delegate processing methods
    public function processApprovalStep(\App\Models\Approval $approval, \App\Models\User $user, string $action, ?string $notes = null): bool
    {
        return $this->processingService->processApprovalStep($approval, $user, $action, $notes);
    }

    public function createApproval($model, string $modelType): \App\Models\Approval
    {
        return $this->processingService->createApproval($model, $modelType);
    }

    public function cancelApproval(\App\Models\Approval $approval, \App\Models\User $user): void
    {
        $this->processingService->cancelApproval($approval, $user);
    }

    public function resubmitApproval(\App\Models\Approval $approval, \App\Models\User $user): void
    {
        $this->processingService->resubmitApproval($approval, $user);
    }

    // Delegate history methods
    public function logApprovalAction($model, \App\Models\User $user, string $action, ?string $documentId = null, ?string $rejectionReason = null, ?string $notes = null, ?int $stepId = null)
    {
        return $this->historyService->logApprovalAction($model, $user, $action, $documentId, $rejectionReason, $notes, $stepId);
    }

    public function getApprovalHistory($model): \Illuminate\Support\Collection
    {
        return $this->historyService->getApprovalHistory($model);
    }

    public function getApprovalHistoryByDocumentId(string $documentId): \Illuminate\Support\Collection
    {
        return $this->historyService->getApprovalHistoryByDocumentId($documentId);
    }

    public function getLatestApprovalAction($model): ?\App\Models\ApprovalHistory
    {
        return $this->historyService->getLatestApprovalAction($model);
    }

    public function logNewApproval($model, \App\Models\User $user, ?string $documentId = null): void
    {
        $this->historyService->logNewApproval($model, $user, $documentId);
    }

    // Delegate stock update methods
    public function handleStockUpdates($model): void
    {
        $this->stockUpdateService->handleStockUpdates($model);
    }

    public function syncApprovalStatus($model): void
    {
        $this->processingService->syncApprovalStatus($model);
    }
}