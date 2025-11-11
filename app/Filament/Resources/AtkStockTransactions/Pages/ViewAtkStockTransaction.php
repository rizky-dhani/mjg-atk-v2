<?php

namespace App\Filament\Resources\AtkStockTransactions\Pages;

use App\Filament\Resources\AtkStockTransactions\AtkStockTransactionResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Resources\Pages\ViewRecord;

class ViewAtkStockTransaction extends ViewRecord
{
    protected static string $resource = AtkStockTransactionResource::class;
}