<?php

namespace App\Filament\Resources\AtkFloatingStockTransactionHistories\Pages;

use App\Filament\Resources\AtkFloatingStockTransactionHistories\AtkFloatingStockTransactionHistoryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAtkFloatingStockTransactionHistories extends ManageRecords
{
    protected static string $resource = AtkFloatingStockTransactionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
