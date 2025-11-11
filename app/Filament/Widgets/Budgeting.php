<?php

namespace App\Filament\Widgets;

use App\Models\AtkBudgeting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class Budgeting extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        
        if (!$user || !$user->division_id) {
            return [
                Stat::make('No Access', 'Please log in to view budget information')
                    ->description('Budget data is only available for authenticated users')
                    ->color('warning'),
            ];
        }

        $currentYear = now()->year;
        
        // Get the budget for the current user's division
        $budgeting = AtkBudgeting::where('division_id', $user->division_id)
            ->where('fiscal_year', $currentYear)
            ->first();

        if (!$budgeting) {
            return [
                Stat::make('Budget Information', 'No budget set for your division')
                    ->description('Contact administrator to set budget for ' . $currentYear)
                    ->color('warning'),
            ];
        }

        // Format the budget amounts with 'Rp' prefix
        $budgetAmount = 'Rp ' . number_format($budgeting->budget_amount, 0, ',', '.');
        $usedAmount = 'Rp ' . number_format($budgeting->used_amount, 0, ',', '.');
        $remainingAmount = 'Rp ' . number_format($budgeting->remaining_amount, 0, ',', '.');

        // Calculate utilization percentage
        $utilizationPercentage = $budgeting->budget_amount > 0 
            ? round(($budgeting->used_amount / $budgeting->budget_amount) * 100, 2) . '%' 
            : '0%';

        return [
            Stat::make('Total Budget', $budgetAmount)
                ->description($user->division->name . ' - ' . $currentYear)
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
                
            Stat::make('Used Amount', $usedAmount)
                ->description($utilizationPercentage . ' Utilized')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Remaining Budget', $remainingAmount)
                ->description('Available for use')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
