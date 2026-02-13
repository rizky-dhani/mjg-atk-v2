<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ApprovalFlow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'model_type',
        'division_ids',
        'is_active',
    ];

    protected $casts = [
        'division_ids' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ApprovalFlow $flow) {
            if (empty($flow->division_ids)) {
                // Global flow - check if one already exists
                $exists = static::where('model_type', $flow->model_type)
                    ->whereNull('division_ids')
                    ->orWhereRaw('division_ids = "[]"')
                    ->orWhereRaw('division_ids IS NULL')
                    ->exists();

                if ($exists) {
                    throw new \InvalidArgumentException('A global flow for this model type already exists.');
                }
            } else {
                // Division-specific flow - check for conflicts
                $exists = static::where('model_type', $flow->model_type)
                    ->where(function ($query) use ($flow) {
                        $query->whereJsonContains('division_ids', $flow->division_ids);
                    })
                    ->exists();

                if ($exists) {
                    throw new \InvalidArgumentException('A flow for these divisions and model type already exists.');
                }
            }
        });

        static::updating(function (ApprovalFlow $flow) {
            if (empty($flow->division_ids)) {
                // Global flow - check if one already exists (excluding current)
                $exists = static::where('model_type', $flow->model_type)
                    ->where('id', '!=', $flow->id)
                    ->where(function ($query) {
                        $query->whereNull('division_ids')
                            ->orWhereRaw('division_ids = "[]"');
                    })
                    ->exists();

                if ($exists) {
                    throw new \InvalidArgumentException('A global flow for this model type already exists.');
                }
            } else {
                // Division-specific flow - check for conflicts (excluding current)
                $exists = static::where('model_type', $flow->model_type)
                    ->where('id', '!=', $flow->id)
                    ->where(function ($query) use ($flow) {
                        $query->whereJsonContains('division_ids', $flow->division_ids);
                    })
                    ->exists();

                if ($exists) {
                    throw new \InvalidArgumentException('A flow for these divisions and model type already exists.');
                }
            }
        });
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(UserDivision::class, 'approval_flow_division', 'approval_flow_id', 'division_id');
    }

    public function approvalFlowSteps()
    {
        return $this->hasMany(ApprovalFlowStep::class, 'flow_id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'flow_id');
    }

    public function isGlobal(): bool
    {
        return empty($this->division_ids);
    }

    public function appliesToDivision(int $divisionId): bool
    {
        if ($this->isGlobal()) {
            return true;
        }

        return in_array($divisionId, $this->division_ids ?? []);
    }
}
