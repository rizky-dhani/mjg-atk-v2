<?php

namespace App\Filament\Widgets;

use App\Models\AtkStockRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AtkStockRequestStatus extends StatsOverviewWidget
{
    protected ?string $heading = 'Permintaan ATK';

    protected static ?int $sort = 2;

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

            // Count pending requests: division's requests where there is no approval history or the latest approval history action is not 'approved' or 'rejected'
            $pendingCount = AtkStockRequest::where('division_id', $divisionId)
                ->whereDoesntHave('approvalHistory', function ($query) {
                    $query->orderByDesc('performed_at')->where('action', 'rejected');
                })
                ->whereDoesntHave('approvalHistory', function ($query) {
                    $query->orderByDesc('performed_at')->where('action', 'partially_approved');
                })
                ->whereDoesntHave('approvalHistory', function ($query): void {
                    $query->orderByDesc('performed_at')->where('action', 'approved');
                })
                ->count();

            // Count approved requests: division's requests where the latest approval history action is 'approved'
            $approvedCount = AtkStockRequest::where('division_id', $divisionId)
                ->whereHas('approvalHistory', function ($query) {
                    $query->orderBy('performed_at', 'desc')->limit(1)->where('action', 'approved');
                })
                ->count();

            // Count on progress requests: This may need to be adjusted based on your specific business logic
            $onProgressCount = AtkStockRequest::where('division_id', $divisionId)
                ->whereHas('approvalHistory', function ($query) {
                    $query->orderBy('performed_at', 'desc')->limit(1)->where('action', 'partially_approved');
                })
                ->count();
        }

        return [
            Stat::make('Pending Requests', $pendingCount)
                ->description('Waiting for approval')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('On Progress', $onProgressCount)
                ->description('Partially approved')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Approved', $approvedCount)
                ->description('Successfully approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
