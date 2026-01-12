<?php

namespace App\Filament\Resources\AtkItemPrices\Schemas;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use Filament\Schemas\Schema;

class AtkItemPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Forms\Components\Select::make('category_id')
                ->label('Category')
                ->options(AtkCategory::pluck('name', 'id'))
                ->required()
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function (callable $set) {
                    $set('item_id', null); // Reset item selection when category changes
                })
                ->live(),
            \Filament\Forms\Components\Select::make('item_id')
                ->label('Item')
                ->options(function (callable $get) {
                    $categoryId = $get('category_id');
                    if (! $categoryId) {
                        return AtkItem::all()->pluck('name', 'id');
                    }

                    return AtkItem::where('category_id', $categoryId)->pluck('name', 'id');
                })
                ->required()
                ->searchable()
                ->live(),
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
