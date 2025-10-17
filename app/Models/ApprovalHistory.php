<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalHistory extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'document_id',
        'approval_id',
        'step_id',
        'user_id',
        'action',
        'rejection_reason',
        'notes',
        'performed_at',
        'metadata'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $dates = [
        'performed_at',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function approval(): BelongsTo
    {
        return $this->belongsTo(Approval::class, 'approval_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlowStep::class, 'step_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope to filter by action type
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // Scope to filter by approvable model
    public function scopeForApprovable($query, string $type, int $id)
    {
        return $query->where('approvable_type', $type)
                    ->where('approvable_id', $id);
    }

    // Scope to filter by document ID
    public function scopeByDocumentId($query, string $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    // Scope to filter by user
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope to filter by date range
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    // Helper methods
    public function isApproved(): bool
    {
        return $this->action === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->action === 'rejected';
    }

    public function isPending(): bool
    {
        return $this->action === 'pending';
    }

    public function isSubmitted(): bool
    {
        return $this->action === 'submitted';
    }

    public function isReturned(): bool
    {
        return $this->action === 'returned';
    }
}