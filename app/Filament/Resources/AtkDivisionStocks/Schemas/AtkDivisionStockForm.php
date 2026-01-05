<?php

namespace App\Filament\Resources\AtkDivisionStocks\Schemas;

use App\Models\AtkDivisionStockSetting;
use App\Models\AtkItem;
use App\Models\UserDivision;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkDivisionStockForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->components([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->required()
                    ->live()
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn (UserDivision $record): string => $record->getNameWithInitialAttribute())
                    ->afterStateUpdated(fn ($get, $set) => static::updateDependencies($get, $set)),
                Select::make('item_id')
                    ->relationship('item', 'name')
                    ->required()
                    ->live()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(fn ($get, $set) => static::updateDependencies($get, $set)),
                Hidden::make('category_id')
                    ->required(),
                TextInput::make('current_stock')
                    ->label('Current Stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_stock_limit')
                    ->label('Max Stock Limit')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($get) {
                        $divisionId = $get('division_id');
                        $itemId = $get('item_id');
                        if ($divisionId && $itemId) {
                            return AtkDivisionStockSetting::where('division_id', $divisionId)
                                ->where('item_id', $itemId)
                                ->first()?->max_limit;
                        }

                        return null;
                    }),
            ]);
    }

    protected static function updateDependencies($get, $set): void
    {
        $divisionId = $get('division_id');
        $itemId = $get('item_id');

        // Update category_id
        if ($itemId) {
            $item = AtkItem::find($itemId);
            if ($item) {
                $set('category_id', $item->category_id);
            }
        } else {
            $set('category_id', null);
        }

        // Update max_stock_limit
        if ($divisionId && $itemId) {
            $limit = AtkDivisionStockSetting::where('division_id', $divisionId)
                ->where('item_id', $itemId)
                ->first()?->max_limit;

            $set('max_stock_limit', $limit);
        } else {
            $set('max_stock_limit', null);
        }
    }
}
