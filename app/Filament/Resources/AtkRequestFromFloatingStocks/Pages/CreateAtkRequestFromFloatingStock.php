<?php

namespace App\Filament\Resources\AtkRequestFromFloatingStocks\Pages;

use App\Filament\Resources\AtkRequestFromFloatingStocks\AtkRequestFromFloatingStockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAtkRequestFromFloatingStock extends CreateRecord
{
    protected static string $resource = AtkRequestFromFloatingStockResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] = auth()->id();
        $data['division_id'] = $data['division_id'] ?? auth()->user()->divisions->first()?->id;

        return $data;
    }
}
