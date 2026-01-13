<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\Pages;

use App\Filament\Resources\AtkRequestFromFloatingStocks\AtkRequestFromFloatingStockResource;
use App\Services\ApprovalProcessingService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListAtkRequestFromFloatingStocks extends ListRecords
{
    protected static string $resource = AtkRequestFromFloatingStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::SevenExtraLarge)
                ->mutateFormDataUsing(function (array $data): array {
                    $data['requester_id'] = auth()->id();
                    $data['division_id'] = auth()->user()->division_id;

                    return $data;
                })
                ->after(function ($record) {
                    app(ApprovalProcessingService::class)->createApproval($record, \App\Models\AtkRequestFromFloatingStock::class);
                })
                ->successNotificationTitle('Permintaan stok umum berhasil dibuat'),
        ];
    }
}
