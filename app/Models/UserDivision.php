<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDivision extends Model
{
    protected $fillable = [
        'name',
        'description',
        'initial'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'division_id');
    }

    public function atkDivisionStocks()
    {
        return $this->hasMany(AtkDivisionStock::class, 'division_id');
    }

    public function atkStockRequests()
    {
        return $this->hasMany(AtkStockRequest::class, 'division_id');
    }

    public function atkStockUsages()
    {
        return $this->hasMany(AtkStockUsage::class, 'division_id');
    }

    public function atkBudgetings()
    {
        return $this->hasMany(AtkBudgeting::class, 'division_id');
    }

    public function approvalFlowSteps()
    {
        return $this->hasMany(ApprovalFlowStep::class, 'division_id');
    }
    
    public function getNameWithInitialAttribute()
    {
        return $this->initial . ' - ' . $this->name;
    }
}
