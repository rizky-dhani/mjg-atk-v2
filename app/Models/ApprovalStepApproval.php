<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStepApproval extends Model
{
    protected $fillable = [
        'approval_id',
        'step_id',
        'user_id',
        'approved_at',
        'status',
        'notes',
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id');
    }

    public function step()
    {
        return $this->belongsTo(ApprovalFlowStep::class, 'step_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
