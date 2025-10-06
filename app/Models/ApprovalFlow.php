<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'model_type',
        'is_active'
    ];

    public function approvalFlowSteps()
    {
        return $this->hasMany(ApprovalFlowStep::class, 'flow_id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'flow_id');
    }
}
