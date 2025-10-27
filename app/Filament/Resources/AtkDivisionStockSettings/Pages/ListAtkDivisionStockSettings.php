<?php

namespace App\Filament\Resources\AtkDivisionStockSettings\Pages;

use App\Models\AtkDivisionStockSetting;
use App\Models\AtkItem;
use App\Models\UserDivision;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AtkDivisionStockSettings\AtkDivisionStockSettingResource;
use Filament\Notifications\Notification;

class ListAtkDivisionStockSettings extends ListRecords
{
    protected static string $resource = AtkDivisionStockSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateItemList')
                ->label('Generate Item List from Atk Items')
                ->button()
                ->action(function () {
                    // Get all divisions and items
                    $divisions = UserDivision::all();
                    $items = AtkItem::with('category')->get();

                    foreach ($divisions as $division) {
                        foreach ($items as $item) {
                            // Check if a setting already exists for this division-item combination
                            $existingSetting = AtkDivisionStockSetting::where('division_id', $division->id)
                                ->where('item_id', $item->id)
                                ->first();

                            // If no setting exists, create one with default max_limit
                            if (!$existingSetting) {
                                AtkDivisionStockSetting::create([
                                    'division_id' => $division->id,
                                    'item_id' => $item->id,
                                    'category_id' => $item->category_id,
                                    'max_limit' => 0, // Default max limit is 0, users can update as needed
                                ]);
                            }
                        }
                    }

                    Notification::make()
                        ->title('Item list generated successfully')
                        ->body(count($divisions) . ' divisions and ' . count($items) . ' items processed')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Item List')
                ->modalDescription('This will create stock settings for all ATK items across all divisions. Existing settings will not be modified.')
                ->modalSubmitActionLabel('Generate'),
        ];
    }
}