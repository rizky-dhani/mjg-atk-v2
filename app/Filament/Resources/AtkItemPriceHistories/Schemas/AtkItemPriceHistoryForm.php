<?php

namespace App\Filament\Resources\AtkItemPriceHistories\Schemas;

use App\Models\AtkItem;
use App\Models\User;
use Filament\Schemas\Schema;

class AtkItemPriceHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Forms\Components\Select::make('item_id')
                ->label('Item')
                ->options(AtkItem::pluck('name', 'id'))
                ->required()
                ->searchable(),
            \Filament\Forms\Components\TextInput::make('old_price')
                ->label('Old Price')
                ->required()
                ->numeric()
                ->minValue(0)
                ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.')),
            \Filament\Forms\Components\TextInput::make('new_price')
                ->label('New Price')
                ->required()
                ->numeric()
                ->minValue(0)
                ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.')),
            \Filament\Forms\Components\DatePicker::make('effective_date')
                ->label('Effective Date')
                ->required(),
            \Filament\Forms\Components\Select::make('changed_by')
                ->label('Changed By')
                ->options(User::pluck('name', 'id'))
                ->searchable(),
        ]);
    }
}
