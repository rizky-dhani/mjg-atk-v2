<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkBudgeting extends Model
{
    protected $fillable = [
        'division_id',
        'budget_amount',
        'used_amount',
        'remaining_amount',
        'fiscal_year',
    ];

    protected $casts = [
        'budget_amount' => 'integer',
        'used_amount' => 'integer',
        'remaining_amount' => 'integer',
        'fiscal_year' => 'integer',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    /**
     * Calculate remaining amount
     */
    public function calculateRemainingAmount(): float
    {
        return $this->budget_amount - $this->used_amount;
    }

    /**
     * Update the remaining amount
     */
    public function updateRemainingAmount(): void
    {
        $this->remaining_amount = $this->calculateRemainingAmount();
        $this->saveQuietly();
    }

    /**
     * Check if the budget has sufficient funds
     */
    public function hasSufficientBudget(float $amount): bool
    {
        return $this->remaining_amount >= $amount;
    }
    

}
