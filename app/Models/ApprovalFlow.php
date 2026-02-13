<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalFlow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'model_type',
        'division_id',
        'is_active',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ApprovalFlow $flow) {
            $query = static::where('model_type', $flow->model_type)
                ->where('division_id', $flow->division_id);

            if ($query->exists()) {
                if (empty($flow->division_id)) {
                    throw new \InvalidArgumentException('A global flow for this model type already exists.');
                }

                throw new \InvalidArgumentException('A flow for this division and model type already exists.');
            }
        });

        static::updating(function (ApprovalFlow $flow) {
            $query = static::where('model_type', $flow->model_type)
                ->where('division_id', $flow->division_id)
                ->where('id', '!=', $flow->id);

            if ($query->exists()) {
                if (empty($flow->division_id)) {
                    throw new \InvalidArgumentException('A global flow for this model type already exists.');
                }

                throw new \InvalidArgumentException('A flow for this division and model type already exists.');
            }
        });
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function approvalFlowSteps()
    {
        return $this->hasMany(ApprovalFlowStep::class, 'flow_id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'flow_id');
    }
}
