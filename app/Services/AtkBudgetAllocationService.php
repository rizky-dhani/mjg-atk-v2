<?php

namespace App\Services;

use App\Models\AtkBudgeting;
use App\Models\UserDivision;
use Illuminate\Support\Facades\DB;

class AtkBudgetAllocationService
{
    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * Allocate budget to a division for a specific fiscal year
     */
    public function allocateBudget(int $divisionId, float $amount, int $fiscalYear): AtkBudgeting
    {
        $fiscalYear = $fiscalYear ?: now()->year;
        
        // Validate division exists
        $division = UserDivision::find($divisionId);
        if (!$division) {
            throw new \Exception("Division with ID {$divisionId} not found");
        }

        // Set the budget using BudgetService
        return $this->budgetService->setBudget($divisionId, $amount, $fiscalYear);
    }

    /**
     * Bulk allocate budgets to multiple divisions for a specific fiscal year
     */
    public function bulkAllocateBudgets(array $budgetData, int $fiscalYear): array
    {
        $fiscalYear = $fiscalYear ?: now()->year;
        $results = [];
        
        DB::transaction(function () use ($budgetData, $fiscalYear, &$results) {
            foreach ($budgetData as $data) {
                $divisionId = $data['division_id'];
                $amount = $data['amount'];
                
                $results[] = $this->allocateBudget($divisionId, $amount, $fiscalYear);
            }
        });
        
        return $results;
    }

    /**
     * Get all division budgets for a fiscal year
     */
    public function getAllDivisionBudgets(int $fiscalYear): \Illuminate\Support\Collection
    {
        $fiscalYear = $fiscalYear ?: now()->year;
        
        return AtkBudgeting::with('division')
            ->where('fiscal_year', $fiscalYear)
            ->get();
    }

    /**
     * Update an existing budget allocation
     */
    public function updateBudgetAllocation(int $budgetingId, float $newAmount): AtkBudgeting
    {
        $budgeting = AtkBudgeting::findOrFail($budgetingId);
        
        // Calculate the difference to adjust used_amount accordingly
        $difference = $newAmount - $budgeting->budget_amount;
        
        // Update the budget amount
        $budgeting->budget_amount = $newAmount;
        $budgeting->remaining_amount = $budgeting->calculateRemainingAmount();
        $budgeting->save();
        
        return $budgeting;
    }
}