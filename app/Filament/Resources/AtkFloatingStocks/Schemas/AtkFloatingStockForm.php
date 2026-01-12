<?php

namespace App\Filament\Resources\AtkFloatingStocks\Schemas;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AtkFloatingStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Item')
                    ->options(AtkItem::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->disabledOn('edit')
                    ->afterStateUpdated(fn ($state, callable $set) => $set('category_id', AtkItem::find($state)?->category_id)
                    ),
                Select::make('category_id')
                    ->label('Category')
                    ->options(AtkCategory::all()->pluck('name', 'id'))
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('current_stock')
                    ->label('Current Stock')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->helperText('Updated via stock adjustments or transfers.'),
                TextInput::make('moving_average_cost')
                    ->label('Moving Average Cost')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->disabled()
                    ->helperText('Automatically calculated using MAC formula.'),
            ]);
    }
}
