<?php

namespace App\Filament\Resources\AtkDivisionStocks\Schemas;

use App\Models\AtkDivisionStockSetting;
use App\Models\UserDivision;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class AtkDivisionStockForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->required()
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn (UserDivision $record): string => $record->getNameWithInitialAttribute()),
                Select::make('item_id')
                    ->relationship('item', 'name')
                    ->required()
                    ->reactive()
                    ->preload()
                    ->searchable(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_stock_limit')
                    ->label('Max Stock Limit')
                    ->disabled()
                    ->state(function ($get) {
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
}
