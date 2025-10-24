<?php

namespace App\Filament\Resources\AtkDivisionStockSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkDivisionStockSettingForm
{
    public static function configure(Schema $form): Schema
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
                TextInput::make('max_stock_limit')
                    ->label('Max Stock Limit')
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }
}
