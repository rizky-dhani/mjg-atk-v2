<?php

namespace App\Filament\Resources\MarketingMediaDivisionStockSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class MarketingMediaDivisionStockSettingForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('division_id')
                    ->label('Division')
                    ->relationship('division', 'name')
                    ->required()
                    ->searchable(),
                Select::make('item_id')
                    ->label('Item')
                    ->relationship('item', 'name')
                    ->required()
                    ->searchable(),
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('max_limit')
                    ->label('Max Limit')
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }
}
