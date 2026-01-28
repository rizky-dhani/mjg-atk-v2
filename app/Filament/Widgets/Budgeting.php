<?php

namespace App\Filament\Widgets;

use App\Models\AtkBudgeting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class Budgeting extends StatsOverviewWidget
{
    protected ?string $heading = 'Anggaran';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user || $user->divisions->isEmpty()) {
            return [
                Stat::make(__('No Access'), __('Please log in or assign to a division to view budget information'))
                    ->description(__('Budget data is only available for users assigned to divisions'))
                    ->color('warning'),
            ];
        }

        $currentYear = now()->year;

        // Get the budget for the user's divisions
        $budgetings = AtkBudgeting::whereIn('division_id', $user->divisions->pluck('id'))
            ->where('fiscal_year', $currentYear)
            ->get();

        if ($budgetings->isEmpty()) {
            return [
                Stat::make(__('Budget Information'), __('No budget set for your divisions'))
                    ->description(__('Contact administrator to set budget for :year', ['year' => $currentYear]))
                    ->color('warning'),
            ];
        }

        $totalBudget = $budgetings->sum('budget_amount');
        $totalUsed = $budgetings->sum('used_amount');
        $totalRemaining = $budgetings->sum('remaining_amount');

        // Format the budget amounts with 'Rp' prefix
        $budgetAmountLabel = 'Rp '.number_format($totalBudget, 0, ',', '.');
        $usedAmountLabel = 'Rp '.number_format($totalUsed, 0, ',', '.');
        $remainingAmountLabel = 'Rp '.number_format($totalRemaining, 0, ',', '.');

        // Calculate utilization percentage
        $utilizationPercentage = $totalBudget > 0
            ? round(($totalUsed / $totalBudget) * 100, 2).'%'
            : '0%';

        $divisionLabel = $user->divisions->count() > 1
            ? __('All assigned divisions')
            : $user->divisions->first()->name;

        return [
            Stat::make(__('Total Budget'), $budgetAmountLabel)
                ->description($divisionLabel.' - '.$currentYear)
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make(__('Used Amount'), $usedAmountLabel)
                ->description(__(':percentage Utilized', ['percentage' => $utilizationPercentage]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make(__('Remaining Budget'), $remainingAmountLabel)
                ->description(__('Available for use'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
