<?php

namespace App\Services;

use App\Models\AtkBudgeting;
use App\Models\UserDivision;
use App\Models\AtkStockRequest;
use App\Models\AtkStockUsage;
use App\Models\AtkDivisionStock;
use Illuminate\Support\Facades\DB;

class BudgetValidationService
{
    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * Validate if a division has sufficient budget for an AtkRequest
     */
    public function validateRequestBudget(AtkStockRequest $request): array
    {
        $divisionId = $request->division_id;
        $fiscalYear = $request->created_at->year ?? now()->year;
        
        // Calculate total cost of the request items using moving_average_cost from AtkDivisionStock
        $totalCost = $this->calculateRequestCost($request);
        
        $hasBudget = $this->budgetService->hasSufficientBudget($divisionId, $totalCost, $fiscalYear);
        
        return [
            'valid' => $hasBudget,
            'total_cost' => $totalCost,
            'available_budget' => $hasBudget ? 
                $this->budgetService->getBudgetInfo($divisionId, $fiscalYear)?->remaining_amount : 0,
            'required_budget' => $totalCost
        ];
    }

    /**
     * Calculate the total cost of an AtkRequest based on moving_average_cost
     */
    private function calculateRequestCost(AtkStockRequest $request): float
    {
        $totalCost = 0;
        
        foreach ($request->items as $item) {
            // Get the moving_average_cost from AtkDivisionStock for the specific item and division
            $stock = AtkDivisionStock::where('division_id', $request->division_id)
                ->where('atk_item_id', $item->atk_item_id)
                ->first();
            
            if ($stock) {
                $itemCost = $stock->moving_average_cost * $item->quantity;
                $totalCost += $itemCost;
            } else {
                // If no stock exists, we can't calculate the cost, so assume 0 or throw an exception
                // For now, we'll assume 0 to allow the validation to continue
                $totalCost += 0;
            }
        }
        
        return $totalCost;
    }

    /**
     * Validate if a division has sufficient budget for an AtkUsage
     */
    public function validateUsageBudget(AtkStockUsage $usage): array
    {
        $divisionId = $usage->division_id;
        $fiscalYear = $usage->created_at->year ?? now()->year;
        
        // Calculate total cost of the usage items using moving_average_cost from AtkDivisionStock
        $totalCost = $this->calculateUsageCost($usage);
        
        $hasBudget = $this->budgetService->hasSufficientBudget($divisionId, $totalCost, $fiscalYear);
        
        return [
            'valid' => $hasBudget,
            'total_cost' => $totalCost,
            'available_budget' => $hasBudget ? 
                $this->budgetService->getBudgetInfo($divisionId, $fiscalYear)?->remaining_amount : 0,
            'required_budget' => $totalCost
        ];
    }

    /**
     * Calculate the total cost of an AtkUsage based on moving_average_cost
     */
    private function calculateUsageCost(AtkStockUsage $usage): float
    {
        $totalCost = 0;
        
        foreach ($usage->items as $item) {
            // Get the moving_average_cost from AtkDivisionStock for the specific item and division
            $stock = AtkDivisionStock::where('division_id', $usage->division_id)
                ->where('atk_item_id', $item->atk_item_id)
                ->first();
            
            if ($stock) {
                $itemCost = $stock->moving_average_cost * $item->quantity;
                $totalCost += $itemCost;
            } else {
                // If no stock exists, we can't calculate the cost, so assume 0
                $totalCost += 0;
            }
        }
        
        return $totalCost;
    }

    /**
     * Check if a budget override is required for a request
     */
    public function requiresOverride(AtkStockRequest $request): bool
    {
        $validation = $this->validateRequestBudget($request);
        return !$validation['valid'];
    }

    /**
     * Check if a budget override is required for a usage
     */
    public function requiresOverrideForUsage(AtkStockUsage $usage): bool
    {
        $validation = $this->validateUsageBudget($usage);
        return !$validation['valid'];
    }

    /**
     * Perform a budget validation check and throw exception if insufficient budget
     */
    public function validateAndCheck(AtkStockRequest $request, bool $allowOverride = false): void
    {
        $validation = $this->validateRequestBudget($request);
        
        if (!$validation['valid'] && !$allowOverride) {
            throw new \Exception(
                "Insufficient budget for division ID {$request->division_id}. " . 
                "Required: {$validation['required_budget']}, " . 
                "Available: {$validation['available_budget']}"
            );
        }
    }

    /**
     * Perform a budget validation check for usage and throw exception if insufficient budget
     */
    public function validateAndCheckUsage(AtkStockUsage $usage, bool $allowOverride = false): void
    {
        $validation = $this->validateUsageBudget($usage);
        
        if (!$validation['valid'] && !$allowOverride) {
            throw new \Exception(
                "Insufficient budget for division ID {$usage->division_id}. " . 
                "Required: {$validation['required_budget']}, " . 
                "Available: {$validation['available_budget']}"
            );
        }
    }
}