<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlowStep extends Model
{
    protected $fillable = [
        'flow_id',
        'step_name',
        'step_number',
        'role_id',
        'division_id',
        'description',
        'allow_resubmission'
    ];

    protected $casts = [
        'allow_resubmission' => 'boolean',
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

    // Check if this step is valid for a specific division
    public function isForDivision($divisionId)
    {
        // If division_id is null, this step is available to the user's own division
        // (e.g., Division Head can approve their own division's requests)
        if (is_null($this->division_id)) {
            return true; // This will be handled at the request level
        }
        // Otherwise, check if it matches the specific division
        return $this->division_id == $divisionId;
    }

    public function approvalStepApprovals()
    {
        return $this->hasMany(ApprovalStepApproval::class, 'step_id');
    }
}
