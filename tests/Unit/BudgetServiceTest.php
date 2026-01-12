<?php

use App\Services\BudgetService;
use Database\Factories\UserDivisionFactory;

test('BudgetService can check if division has sufficient budget', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set budget
    $budgetService->setBudget($division->id, 1000.00, 2025);

    // Check sufficient budget
    expect($budgetService->hasSufficientBudget($division->id, 500.00, 2025))->toBeTrue();

    // Check insufficient budget
    expect($budgetService->hasSufficientBudget($division->id, 1500.00, 2025))->toBeFalse();
});

test('BudgetService can deduct from budget', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set budget
    $budget = $budgetService->setBudget($division->id, 1000.00, 2025);

    // Deduct amount
    $result = $budgetService->deductFromBudget($division->id, 250.00, 2025);

    expect($result)->toBeTrue();

    $budget->refresh();
    expect($budget->used_amount)->toBe(250.00)
        ->and($budget->remaining_amount)->toBe(750.00);
});

test('BudgetService handles insufficient budget during deduction', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set small budget
    $budgetService->setBudget($division->id, 100.00, 2025);

    // Try to deduct more than available
    $this->expectException(Exception::class);
    $budgetService->deductFromBudget($division->id, 200.00, 2025);
});

test('BudgetService can add back to budget', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set budget and deduct
    $budget = $budgetService->setBudget($division->id, 1000.00, 2025);
    $budgetService->deductFromBudget($division->id, 300.00, 2025);

    // Add back amount
    $result = $budgetService->addToBudget($division->id, 100.00, 2025);

    expect($result)->toBeTrue();

    $budget->refresh();
    expect($budget->used_amount)->toBe(200.00)
        ->and($budget->remaining_amount)->toBe(800.00);
});

test('BudgetService prevents used_amount from going below zero', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set budget and deduct
    $budget = $budgetService->setBudget($division->id, 1000.00, 2025);
    $budgetService->deductFromBudget($division->id, 300.00, 2025);

    // Add back more than was used
    $budgetService->addToBudget($division->id, 500.00, 2025);

    $budget->refresh();
    // Should not go below 0
    expect($budget->used_amount)->toBe(0.00)
        ->and($budget->remaining_amount)->toBe(1000.00);
});

test('BudgetService can get budget info', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set budget
    $expectedBudget = $budgetService->setBudget($division->id, 5000.00, 2025);

    // Get budget info
    $budgetInfo = $budgetService->getBudgetInfo($division->id, 2025);

    expect($budgetInfo->id)->toBe($expectedBudget->id)
        ->and($budgetInfo->budget_amount)->toBe(5000.00)
        ->and($budgetInfo->used_amount)->toBe(0.00)
        ->and($budgetInfo->remaining_amount)->toBe(5000.00);
});
