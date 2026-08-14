<?php

namespace App\Filament\Resources\AtkStockUsages\Pages;

use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAtkStockUsage extends CreateRecord
{
    protected static string $resource = AtkStockUsageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] = auth()->id();
        $data['division_id'] = $data['division_id'] ?? auth()->user()->divisions->first()?->id;

        // Calculate potential_cost from stock usage items
        $potentialCost = 0;
        if (isset($data['atkStockUsageItems']) && is_array($data['atkStockUsageItems'])) {
            foreach ($data['atkStockUsageItems'] as $item) {
                if (isset($item['quantity']) && isset($item['moving_average_cost'])) {
                    $potentialCost += (int) $item['quantity'] * (int) $item['moving_average_cost'];
                }
            }
        }
        $data['potential_cost'] = $potentialCost;

        return $data;
    }
}
