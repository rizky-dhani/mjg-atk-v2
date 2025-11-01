<?php

namespace App\Filament\Resources\AtkItemPrices\Schemas;

use Filament\Infolists\Components\Component;
use Filament\Schemas\Schema;

class AtkItemPriceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Infolists\Components\Section::make('Price Details')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('item.name')
                        ->label('Item'),
                    \Filament\Infolists\Components\TextEntry::make('category.name')
                        ->label('Category'),
                    \Filament\Infolists\Components\TextEntry::make('unit_price')
                        ->label('Unit Price')
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                    \Filament\Infolists\Components\TextEntry::make('effective_date')
                        ->label('Effective Date')
                        ->date(),
                    \Filament\Infolists\Components\IconEntry::make('is_active')
                        ->label('Active')
                        ->boolean(),
                ])
                ->columns(2),
        ]);
    }
}