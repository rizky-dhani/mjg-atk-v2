<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use App\Models\AtkStockUsage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AtkStockUsageStatus extends StatsOverviewWidget
{
    protected ?string $heading = 'Pengeluaran ATK';

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Initialize counts
        $pendingCount = 0;
        $onProgressCount = 0;
        $approvedCount = 0;

        if ($user && $user->division_id) {
            // Get the user's division ID for filtering
            $divisionId = $user->division_id;

            // Count records where the latest approval history action is 'approved'
            $pendingCount = AtkStockUsage::where('division_id', $divisionId)
                ->whereDoesntHave('approvalHistory', function ($query) {
                    $query->orderByDesc('performed_at')->where('action', 'rejected');
                })
                ->whereDoesntHave('approvalHistory', function ($query) {
                    $query->orderByDesc('performed_at')->where('action', 'partially_approved');
                })
                ->whereDoesntHave('approvalHistory', function ($query) {
                    $query->orderByDesc('performed_at')->where('action', 'approved');
                })
                ->count();

            // Count records where the latest approval history action is 'partially_approved'
            $onProgressCount = AtkStockUsage::where('division_id', $divisionId)
                ->whereHas('approvalHistory', function ($query) {
                    $query->orderByDesc('performed_at')->where('action', 'partially_approved');
                })
                ->count();

            // Count records that either have no approval history or the latest approval history action is not 'approved' or 'rejected'
            $approvedCount = AtkStockUsage::where('division_id', $divisionId)
                ->whereHas('approvalHistory', function ($query) {
                    $query->orderByDesc('performed_at')->where('action', 'approved');
                })
                ->count();
        }

        return [
            Stat::make(__('Pending Requests'), $pendingCount)
                ->description(__('Waiting for approval'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning')
                ->url(AtkStockUsageResource::getUrl('index', ['tableFilters[approval_status][value]' => 'pending'])),

            Stat::make(__('On Progress'), $onProgressCount)
                ->description(__('Partially approved'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->url(AtkStockUsageResource::getUrl('index', ['tableFilters[approval_status][value]' => 'partially_approved'])),

            Stat::make(__('Approved'), $approvedCount)
                ->description(__('Successfully approved'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(AtkStockUsageResource::getUrl('index', ['tableFilters[approval_status][value]' => 'approved'])),
        ];
    }
}
