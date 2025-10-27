<?php

namespace App\Filament\Resources\MarketingMediaDivisionStockSettings\Pages;

use App\Models\MarketingMediaDivisionStockSetting;
use App\Models\MarketingMediaItem;
use App\Models\UserDivision;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\MarketingMediaDivisionStockSettings\MarketingMediaDivisionStockSettingResource;
use Filament\Notifications\Notification;

class ListMarketingMediaDivisionStockSettings extends ListRecords
{
    protected static string $resource = MarketingMediaDivisionStockSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateItemList')
                ->label('Generate Item List from Marketing Media Items')
                ->button()
                ->action(function () {
                    // Get all divisions and items
                    $divisions = UserDivision::all();
                    $items = MarketingMediaItem::with('category')->get();

                    foreach ($divisions as $division) {
                        foreach ($items as $item) {
                            // Check if a setting already exists for this division-item combination
                            $existingSetting = MarketingMediaDivisionStockSetting::where('division_id', $division->id)
                                ->where('item_id', $item->id)
                                ->first();

                            // If no setting exists, create one with default max_limit
                            if (!$existingSetting) {
                                MarketingMediaDivisionStockSetting::create([
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
                ->modalDescription('This will create stock settings for all Marketing Media items across all divisions. Existing settings will not be modified.')
                ->modalSubmitActionLabel('Generate'),
        ];
    }
}