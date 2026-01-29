<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'flow_id',
        'current_step',
        'status',
    ];

    public function approvalFlow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'flow_id');
    }

    public function approvalStepApprovals()
    {
        return $this->hasMany(ApprovalStepApproval::class, 'approval_id');
    }

    public function approvable()
    {
        return $this->morphTo();
    }

    /**
     * Get a combined view of approval steps, their status, and potential approvers.
     */
    public function getApprovalProgress(): \Illuminate\Support\Collection
    {
        $approvable = $this->approvable;
        $steps = $this->approvalFlow->approvalFlowSteps()->with(['role', 'user'])->orderBy('step_number')->get();
        $approvals = $this->approvalStepApprovals()->with('user')->get();

        return $steps->map(function ($step) use ($approvable, $approvals) {
            $stepApproval = $approvals->firstWhere('step_id', $step->id);

            $status = 'waiting';
            if ($stepApproval) {
                $status = $stepApproval->status;
            } elseif ($this->status === 'rejected') {
                $status = 'blocked';
            } elseif ($this->current_step == $step->step_number) {
                $status = 'pending';
            } elseif ($this->current_step > $step->step_number) {
                $status = 'approved'; // Assuming skipped or implicitly approved if current_step is past it
            }

            return [
                'step_id' => $step->id,
                'step_name' => $step->step_name,
                'step_number' => $step->step_number,
                'role' => $step->role?->name,
                'potential_approvers' => $step->getPotentialApprovers($approvable),
                'status' => $status,
                'approved_at' => $stepApproval?->approved_at,
                'approver_name' => $stepApproval?->user?->name,
                'notes' => $stepApproval?->notes,
            ];
        });
    }
}
