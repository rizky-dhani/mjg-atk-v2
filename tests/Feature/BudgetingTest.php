<?php

use App\Models\AtkBudgeting;
use App\Models\AtkDivisionStock;
use App\Models\AtkStockUsage;
use App\Models\AtkStockUsageItem;
use App\Services\BudgetService;
use App\Services\BudgetValidationService;
use Database\Factories\AtkItemFactory;
use Database\Factories\UserDivisionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('budget model is created with correct fields', function () {
    $division = UserDivisionFactory::new()->create();

    $budget = AtkBudgeting::create([
        'division_id' => $division->id,
        'budget_amount' => 1000.00,
        'used_amount' => 250.00,
        'fiscal_year' => 2025,
    ]);

    $budget->refresh(); // Reload from database

    expect($budget->division_id)->toBe($division->id)
        ->and($budget->budget_amount)->toBe(1000.00)
        ->and($budget->used_amount)->toBe(250.00)
        ->and($budget->fiscal_year)->toBe(2025)
        ->and($budget->remaining_amount)->toBe(750.00);
});

test('budget relationship with division works', function () {
    $division = UserDivisionFactory::new()->create();

    $budget = AtkBudgeting::create([
        'division_id' => $division->id,
        'budget_amount' => 1000.00,
        'used_amount' => 0,
        'fiscal_year' => 2025,
    ]);

    expect($budget->division->id)->toBe($division->id);
});

test('division relationship with budgets works', function () {
    $division = UserDivisionFactory::new()->create();

    $budget = AtkBudgeting::create([
        'division_id' => $division->id,
        'budget_amount' => 1000.00,
        'used_amount' => 0,
        'fiscal_year' => 2025,
    ]);

    expect($division->atkBudgetings->count())->toBe(1)
        ->and($division->atkBudgetings->first()->id)->toBe($budget->id);
});

test('budget calculation works correctly', function () {
    $division = UserDivisionFactory::new()->create();

    $budget = AtkBudgeting::create([
        'division_id' => $division->id,
        'budget_amount' => 1000.00,
        'used_amount' => 300.50,
        'fiscal_year' => 2025,
    ]);

    expect($budget->calculateRemainingAmount())->toBe(699.50);
});

test('budget service can set budget', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    $budget = $budgetService->setBudget($division->id, 5000.00, 2025);

    expect($budget->division_id)->toBe($division->id)
        ->and($budget->budget_amount)->toBe(5000.00)
        ->and($budget->used_amount)->toBe(0)
        ->and($budget->remaining_amount)->toBe(5000.00)
        ->and($budget->fiscal_year)->toBe(2025);
});

test('budget service can deduct from budget', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set initial budget
    $budget = $budgetService->setBudget($division->id, 1000.00, 2025);

    // Deduct amount
    $budgetService->deductFromBudget($division->id, 250.50, 2025);

    $budget->refresh();

    expect($budget->used_amount)->toBe(250.50)
        ->and($budget->remaining_amount)->toBe(749.50);
});

test('budget service can add back to budget', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set initial budget and deduct
    $budget = $budgetService->setBudget($division->id, 1000.00, 2025);
    $budgetService->deductFromBudget($division->id, 300.00, 2025);

    // Add back some amount
    $budgetService->addToBudget($division->id, 100.00, 2025);

    $budget->refresh();

    expect($budget->used_amount)->toBe(200.00)
        ->and($budget->remaining_amount)->toBe(800.00);
});

test('budget validation service can check sufficient budget', function () {
    $division = UserDivisionFactory::new()->create();
    $budgetService = new BudgetService;

    // Set budget
    $budgetService->setBudget($division->id, 1000.00, 2025);

    $validationService = new BudgetValidationService($budgetService);

    expect($validationService->hasSufficientBudget($division->id, 500.00, 2025))->toBeTrue();
    expect($validationService->hasSufficientBudget($division->id, 1500.00, 2025))->toBeFalse();
});

test('budget validation service calculates usage cost correctly', function () {
    $division = UserDivisionFactory::new()->create();
    $item = AtkItemFactory::new()->create();

    // Create division stock with moving average cost
    $stock = AtkDivisionStock::create([
        'division_id' => $division->id,
        'item_id' => $item->id,
        'current_stock' => 100,
        'moving_average_cost' => 10.50,
    ]);

    // Create a usage
    $usage = AtkStockUsage::create([
        'request_number' => 'USAGE001',
        'requester_id' => 1,
        'division_id' => $division->id,
        'notes' => 'Test usage',
        'request_type' => 'usage',
    ]);

    // Add usage item
    AtkStockUsageItem::create([
        'usage_id' => $usage->id,
        'item_id' => $item->id,
        'quantity' => 5,
    ]);

    $budgetService = new BudgetService;
    $validationService = new BudgetValidationService($budgetService);

    // Calculate the expected cost: 5 items * $10.50 per item = $52.50
    $expectedCost = 5 * 10.50;

    $result = $validationService->validateUsageBudget($usage);

    expect($result['total_cost'])->toBe($expectedCost);
});
