<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\Pages;

use App\Filament\Resources\AtkRequestFromFloatingStocks\AtkRequestFromFloatingStockResource;
use App\Filament\Resources\AtkRequestFromFloatingStocks\Tables\AtkRequestFromFloatingStocksTable;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApprovalAtkRequestFromFloatingStock extends ListRecords
{
    protected static string $resource = AtkRequestFromFloatingStockResource::class;

    protected static ?string $title = 'Persetujuan Permintaan Stok Umum';

    public function table(Table $table): Table
    {
        return AtkRequestFromFloatingStocksTable::configure($table)
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                // Get steps where this user is an approver
                $eligibleStepIds = \App\Models\ApprovalFlowStep::where(function ($query) use ($user) {
                    $query->whereIn('role_id', $user->roles->pluck('id'));
                })
                    ->where(function ($query) use ($user) {
                        $query->whereNull('division_id')
                            ->orWhere('division_id', $user->division_id);
                    })
                    ->pluck('id');

                return $query->whereHas('approval', function ($query) use ($eligibleStepIds) {
                    $query->where('status', 'pending')
                        ->whereHas('approvalFlow.approvalFlowSteps', function ($query) use ($eligibleStepIds) {
                            $query->whereIn('id', $eligibleStepIds);
                        });
                })->with(['requester', 'division', 'approval', 'approvalHistory']);
            });
    }
}
