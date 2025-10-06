<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlowStep extends Model
{
    protected $fillable = [
        'flow_id',
        'step_number',
        'role_id',
        'division_id',
        'description'
    ];

    public function approvalFlow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'flow_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    public function division()
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function approvalStepApprovals()
    {
        return $this->hasMany(ApprovalStepApproval::class, 'step_id');
    }
}
