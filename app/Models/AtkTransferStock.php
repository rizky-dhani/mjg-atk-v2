<?php

namespace App\Models;

use App\Traits\StockTransferModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkTransferStock extends Model
{
    use HasFactory, StockTransferModelTrait;

    protected $fillable = [
        'transfer_number',
        'requester_id',
        'requesting_division_id',
        'notes',
    ];

    protected $casts = [
        'requester_id' => 'integer',
        'requesting_division_id' => 'integer',
    ];

    // Relationship with the requesting division
    public function requestingDivision(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'requesting_division_id');
    }

    // Relationship with the source divisions (from items)
    public function sourceDivisions()
    {
        return $this->hasManyThrough(
            UserDivision::class,
            AtkTransferStockItem::class,
            'transfer_stock_id', // Foreign key on atk_transfer_stock_items table
            'id',                // Foreign key on user_divisions table
            'id',                // Local key on atk_transfer_stocks table
            'id'                 // Local key on atk_transfer_stock_items table
        )->distinct();
    }

    // Relationship with transfer stock items
    public function transferStockItems(): HasMany
    {
        return $this->hasMany(AtkTransferStockItem::class, 'transfer_stock_id');
    }

    // Relationship with the user who requested
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    // Accessor to get count of source divisions
    public function getSourceDivisionsCountAttribute()
    {
        return $this->sourceDivisions()->count();
    }

    // Accessor for approval status with detailed information
    public function getApprovalStatusAttribute()
    {
        $approval = $this->approval;
        if (! $approval) {
            return 'Pending';
        }

        // Get the latest approval step approval
        $latestApproval = $approval
            ->approvalStepApprovals()
            ->with(['user', 'user.division'])
            ->latest('approved_at')
            ->first();

        if ($latestApproval) {
            $status = ucfirst($latestApproval->status);

            if ($latestApproval->user && $latestApproval->user->division) {
                // Get division's initial and user's first role name
                $divisionInitial = $latestApproval->user->division->initial ?? 'N/A';
                $roleNames = $latestApproval->user->getRoleNames();
                $role = $roleNames->first() ?? 'N/A';

                return "{$status} by {$divisionInitial} {$role}";
            } else {
                return $status;
            }
        }

        return $approval->status
            ? ucfirst($approval->status)
            : 'Pending';
    }

    // Relationship with approval
    public function approval(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable');
    }

    // Relationship with approval history
    public function approvalHistories()
    {
        return $this->morphMany(ApprovalHistory::class, 'approvable');
    }

    // Add status attribute if it doesn't exist
    public function getStatusAttribute()
    {
        return $this->approval ? $this->approval->status : 'pending';
    }

    public function setStatusAttribute($value)
    {
        if ($this->approval) {
            $this->approval->update(['status' => $value]);
        }
    }
}
