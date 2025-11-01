<?php

namespace App\Filament\Resources\AtkItemPrices\Schemas;

use App\Models\AtkItem;
use App\Models\AtkCategory;
use Filament\Forms\Components\Component;
use Filament\Schemas\Schema;

class AtkItemPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Forms\Components\Select::make('item_id')
                ->label('Item')
                ->options(AtkItem::pluck('name', 'id'))
                ->required()
                ->searchable(),
            \Filament\Forms\Components\Select::make('category_id')
                ->label('Category')
                ->options(AtkCategory::pluck('name', 'id'))
                ->required()
                ->searchable(),
            \Filament\Forms\Components\TextInput::make('unit_price')
                ->label('Unit Price')
                ->required()
                ->numeric()
                ->minValue(0),
            \Filament\Forms\Components\DatePicker::make('effective_date')
                ->label('Effective Date')
                ->required()
                ->default(now()),
            \Filament\Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->inline(false)
                ->default(false),
        ]);
    }
}