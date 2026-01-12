<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\Pages;

use App\Filament\Resources\AtkRequestFromFloatingStocks\AtkRequestFromFloatingStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkRequestFromFloatingStocks extends ListRecords
{
    protected static string $resource = AtkRequestFromFloatingStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['requester_id'] = auth()->id();
                    $data['division_id'] = auth()->user()->division_id;

                    return $data;
                })
                ->successNotificationTitle('Permintaan stok umum berhasil dibuat'),
        ];
    }
}
