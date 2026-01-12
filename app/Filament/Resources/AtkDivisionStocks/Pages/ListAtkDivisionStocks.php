<?php

namespace App\Filament\Resources\AtkDivisionStocks\Pages;

use App\Filament\Resources\AtkDivisionStocks\AtkDivisionStockResource;
use App\Models\AtkDivisionStock;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAtkDivisionStocks extends ListRecords
{
    protected static string $resource = AtkDivisionStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AtkDivisionStock::getImportAction()
                ->visible(fn () => auth()->user()->hasRole('Admin') && auth()->user()->division->initial === 'GA' || auth()->user()->hasRole('Super Admin')),
            CreateAction::make()
                ->successNotificationTitle('ATK Division Stock created'),
        ];
    }
}
