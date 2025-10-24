<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\AtkStockRequest;
use App\Models\AtkStockUsage;
use App\Models\MarketingMediaStockRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class ApprovalHistoryService
{
    /**
     * Log an approval action to the approval history
     */
    public function logApprovalAction($model, User $user, string $action, ?string $documentId = null, ?string $rejectionReason = null, ?string $notes = null, ?int $stepId = null)
    {
        return ApprovalHistory::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'document_id' => $documentId ?? $this->getDocumentId($model),
            'approval_id' => $model->approval->id ?? null,
            'step_id' => $stepId,
            'user_id' => $user->id,
            'action' => $action,
            'rejection_reason' => $rejectionReason,
            'notes' => $notes,
            'metadata' => [
                'model_class' => get_class($model),
                'model_id' => $model->id,
            ],
        ]);
    }

    /**
     * Get document ID from model (e.g., stock request number)
     */
    private function getDocumentId($model)
    {
        // Try common document ID fields
        if (isset($model->stock_request_number)) {
            return $model->stock_request_number;
        } elseif (isset($model->request_number)) {
            return $model->request_number;
        } elseif (isset($model->stock_usage_number)) {
            return $model->stock_usage_number;
        } elseif (isset($model->usage_number)) {
            return $model->usage_number;
        } elseif (isset($model->id)) {
            // Fallback to model ID with prefix
            $prefix = class_basename($model);

            return $prefix.'-'.$model->id;
        }

        return null;
    }

    /**
     * Get approval history for a specific model
     */
    public function getApprovalHistory($model): Collection
    {
        return ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->with(['user', 'step'])
            ->orderBy('performed_at', 'asc')
            ->get();
    }

    /**
     * Get approval history for a specific document ID
     */
    public function getApprovalHistoryByDocumentId(string $documentId): Collection
    {
        return ApprovalHistory::where('document_id', $documentId)
            ->with(['user', 'step', 'approvable'])
            ->orderBy('performed_at', 'asc')
            ->get();
    }

    /**
     * Get the latest action from approval history for a model
     */
    public function getLatestApprovalAction($model): ?ApprovalHistory
    {
        return ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->orderBy('performed_at', 'desc')
            ->first();
    }

    /**
     * Create an approval history record when a new approval is created
     */
    public function logNewApproval($model, User $user, ?string $documentId = null): void
    {
        $this->logApprovalAction(
            $model,
            $user,
            'submitted', // Initial submission
            $documentId,
            null, // No rejection reason
            'Request submitted for approval',
            null // No specific step for initial submission
        );
    }
}