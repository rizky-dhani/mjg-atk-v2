<?php

namespace App\Filament\Resources\AtkDivisionStocks\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Actions\ImportStockAction;
use App\Filament\Resources\AtkDivisionStocks\AtkDivisionStockResource;

class ListAtkDivisionStocks extends ListRecords
{
    protected static string $resource = AtkDivisionStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportStockAction::make()
                ->visible(fn() => auth()->user()->hasRole('Admin') && auth()->user()->division->initial === 'GA' || auth()->user()->hasRole('Super Admin')),
            CreateAction::make(),
        ];
    }
}
