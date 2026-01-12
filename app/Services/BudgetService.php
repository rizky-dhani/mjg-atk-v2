<?php

namespace App\Services;

use App\Models\AtkBudgeting;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Check if a division has sufficient budget for a given amount
     */
    public function hasSufficientBudget(int $divisionId, float $amount, int $fiscalYear): bool
    {
        $fiscalYear = $fiscalYear ?: now()->year;

        $budgeting = AtkBudgeting::where('division_id', $divisionId)
            ->where('fiscal_year', $fiscalYear)
            ->first();

        if (! $budgeting) {
            return false; // No budget set for this division
        }

        return $budgeting->hasSufficientBudget($amount);
    }

    /**
     * Deduct amount from division budget
     */
    public function deductFromBudget(int $divisionId, float $amount, int $fiscalYear): bool
    {
        $fiscalYear = $fiscalYear ?: now()->year;

        return DB::transaction(function () use ($divisionId, $amount, $fiscalYear) {
            $budgeting = AtkBudgeting::where('division_id', $divisionId)
                ->where('fiscal_year', $fiscalYear)
                ->lockForUpdate()
                ->first();

            if (! $budgeting) {
                throw new \Exception("No budget found for division ID {$divisionId} in fiscal year {$fiscalYear}");
            }

            if (! $budgeting->hasSufficientBudget($amount)) {
                throw new \Exception("Insufficient budget for division ID {$divisionId}. Required: {$amount}, Available: {$budgeting->remaining_amount}");
            }

            $budgeting->used_amount += $amount;
            $budgeting->updateRemainingAmount();

            return true;
        });
    }

    /**
     * Add amount back to division budget (rollback)
     */
    public function addToBudget(int $divisionId, float $amount, int $fiscalYear): bool
    {
        $fiscalYear = $fiscalYear ?: now()->year;

        return DB::transaction(function () use ($divisionId, $amount, $fiscalYear) {
            $budgeting = AtkBudgeting::where('division_id', $divisionId)
                ->where('fiscal_year', $fiscalYear)
                ->lockForUpdate()
                ->first();

            if (! $budgeting) {
                throw new \Exception("No budget found for division ID {$divisionId} in fiscal year {$fiscalYear}");
            }

            $budgeting->used_amount -= $amount;
            // Ensure used_amount doesn't go below 0
            $budgeting->used_amount = max(0, $budgeting->used_amount);
            $budgeting->updateRemainingAmount();

            return true;
        });
    }

    /**
     * Get the current budget information for a division
     */
    public function getBudgetInfo(int $divisionId, int $fiscalYear): ?AtkBudgeting
    {
        $fiscalYear = $fiscalYear ?: now()->year;

        return AtkBudgeting::where('division_id', $divisionId)
            ->where('fiscal_year', $fiscalYear)
            ->first();
    }

    /**
     * Create or update budget for a division
     */
    public function setBudget(int $divisionId, float $budgetAmount, int $fiscalYear): AtkBudgeting
    {
        $fiscalYear = $fiscalYear ?: now()->year;

        $budgeting = AtkBudgeting::updateOrCreate(
            [
                'division_id' => $divisionId,
                'fiscal_year' => $fiscalYear,
            ],
            [
                'budget_amount' => $budgetAmount,
                'used_amount' => 0,
                'remaining_amount' => $budgetAmount,
            ]
        );

        return $budgeting;
    }
}
