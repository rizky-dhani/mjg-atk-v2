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
        'status'
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
}
