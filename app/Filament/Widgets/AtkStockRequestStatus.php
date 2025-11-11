<?php

namespace App\Filament\Widgets;

use App\Models\AtkStockRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AtkStockRequestStatus extends StatsOverviewWidget
{
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

            // Count pending requests: division's requests with 'pending' status in the approval system
            $pendingCount = AtkStockRequest::where('division_id', $divisionId)
                ->whereHas('approval', function ($query) {
                    $query->where('status', 'pending');
                })
                ->count();

            // Count on progress requests: requests with 'partially_approved' status in the approval system
            $onProgressCount = AtkStockRequest::where('division_id', $divisionId)
                ->whereHas('approval', function ($query) {
                    $query->where('status', 'partially_approved');
                })
                ->count();

            // Count approved requests: requests with 'approved' status in the approval system
            $approvedCount = AtkStockRequest::where('division_id', $divisionId)
                ->whereHas('approval', function ($query) {
                    $query->where('status', 'approved');
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
