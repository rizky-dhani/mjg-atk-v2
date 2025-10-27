<?php

namespace App\Filament\Resources\MarketingMediaItems\Pages;

use App\Filament\Resources\MarketingMediaItems\MarketingMediaItemResource;
use App\Models\MarketingMediaDivisionStock;
use App\Models\UserDivision;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListMarketingMediaItems extends ListRecords
{
    protected static string $resource = MarketingMediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data) {
                    $selectedDivisions = $data['division_id'] ?? [];
                    unset($data['division_id']);
                    $data['slug'] = Str::slug($data['name']);

                    $marketingMediaItem = \App\Models\MarketingMediaItem::create($data);

                    // If no divisions selected, add to all marketing divisions
                    if (empty($selectedDivisions)) {
                        $marketingDivisions = UserDivision::where('name', 'like', '%Marketing%')->get();
                    } else {
                        $marketingDivisions = UserDivision::whereIn('id', $selectedDivisions)->get();
                    }

                    foreach ($marketingDivisions as $division) {
                        MarketingMediaDivisionStock::create([
                            'item_id' => $marketingMediaItem->id,
                            'category_id' => $marketingMediaItem->category_id,
                            'division_id' => $division->id,
                            'current_stock' => 0,
                        ]);
                    }

                    return $marketingMediaItem;
                }),
        ];
    }
}
