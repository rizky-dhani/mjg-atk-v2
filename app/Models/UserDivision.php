<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'initial',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'division_user', 'division_id', 'user_id');
    }

    public function atkDivisionStocks(): HasMany
    {
        return $this->hasMany(AtkDivisionStock::class, 'division_id');
    }

    public function atkStockRequests(): HasMany
    {
        return $this->hasMany(AtkStockRequest::class, 'division_id');
    }

    public function atkStockUsages(): HasMany
    {
        return $this->hasMany(AtkStockUsage::class, 'division_id');
    }

    public function atkBudgetings(): HasMany
    {
        return $this->hasMany(AtkBudgeting::class, 'division_id');
    }

    public function approvalFlowSteps(): HasMany
    {
        return $this->hasMany(ApprovalFlowStep::class, 'division_id');
    }

    public function getNameWithInitialAttribute(): string
    {
        return $this->initial.' - '.$this->name;
    }
}
