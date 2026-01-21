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

        if (! $user || ! $user->division_id) {
            return [
                Stat::make(__('No Access'), __('Please log in to view budget information'))
                    ->description(__('Budget data is only available for authenticated users'))
                    ->color('warning'),
            ];
        }

        $currentYear = now()->year;

        // Get the budget for the current user's division
        $budgeting = AtkBudgeting::where('division_id', $user->division_id)
            ->where('fiscal_year', $currentYear)
            ->first();

        if (! $budgeting) {
            return [
                Stat::make(__('Budget Information'), __('No budget set for your division'))
                    ->description(__('Contact administrator to set budget for :year', ['year' => $currentYear]))
                    ->color('warning'),
            ];
        }

        // Format the budget amounts with 'Rp' prefix
        $budgetAmount = 'Rp '.number_format($budgeting->budget_amount, 0, ',', '.');
        $usedAmount = 'Rp '.number_format($budgeting->used_amount, 0, ',', '.');
        $remainingAmount = 'Rp '.number_format($budgeting->remaining_amount, 0, ',', '.');

        // Calculate utilization percentage
        $utilizationPercentage = $budgeting->budget_amount > 0
            ? round(($budgeting->used_amount / $budgeting->budget_amount) * 100, 2).'%'
            : '0%';

        return [
            Stat::make(__('Total Budget'), $budgetAmount)
                ->description($user->division->name.' - '.$currentYear)
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make(__('Used Amount'), $usedAmount)
                ->description(__(':percentage Utilized', ['percentage' => $utilizationPercentage]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make(__('Remaining Budget'), $remainingAmount)
                ->description(__('Available for use'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
