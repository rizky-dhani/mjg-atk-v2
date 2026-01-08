<?php

namespace App\Filament\Resources\AtkItems\Pages;

use App\Filament\Resources\AtkItems\AtkItemResource;
use App\Models\AtkDivisionStock;
use App\Models\UserDivision;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListAtkItems extends ListRecords
{
    protected static string $resource = AtkItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->successNotificationTitle('ATK Item created')
                ->using(function (array $data) {
                    $selectedDivisions = $data['division_id'] ?? [];
                    unset($data['division_id']);
                    $data['slug'] = Str::slug($data['name']);
                    $atkItem = \App\Models\AtkItem::create($data);

                    // If no divisions selected, add to all divisions
                    if (empty($selectedDivisions)) {
                        $userDivisions = UserDivision::all();
                    } else {
                        $userDivisions = UserDivision::whereIn('id', $selectedDivisions)->get();
                    }

                    foreach ($userDivisions as $userDivision) {
                        AtkDivisionStock::create([
                            'item_id' => $atkItem->id,
                            'category_id' => $atkItem->category_id,
                            'division_id' => $userDivision->id,
                            'current_stock' => 0,
                        ]);
                    }

                    return $atkItem;
                }),
        ];
    }
}
