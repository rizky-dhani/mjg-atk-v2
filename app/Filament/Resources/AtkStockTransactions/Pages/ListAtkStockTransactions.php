<?php

namespace App\Filament\Resources\AtkStockTransactions\Pages;

use App\Filament\Resources\AtkStockTransactions\AtkStockTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkStockTransactions extends ListRecords
{
    protected static string $resource = AtkStockTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotificationTitle('ATK Stock Transaction created'),
        ];
    }
}