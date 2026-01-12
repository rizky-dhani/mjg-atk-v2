<?php

namespace App\Filament\Resources\AtkItemPriceHistories\Schemas;

use Filament\Schemas\Schema;

class AtkItemPriceHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Infolists\Components\Section::make('Price Change Details')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('item.name')
                        ->label('Item'),
                    \Filament\Infolists\Components\TextEntry::make('old_price')
                        ->label('Old Price')
                        ->formatStateUsing(fn ($state) => $state ? 'Rp '.number_format($state, 0, ',', '.') : 'N/A'),
                    \Filament\Infolists\Components\TextEntry::make('new_price')
                        ->label('New Price')
                        ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 0, ',', '.')),
                    \Filament\Infolists\Components\TextEntry::make('effective_date')
                        ->label('Effective Date')
                        ->date(),
                    \Filament\Infolists\Components\TextEntry::make('user.name')
                        ->label('Changed By'),
                ])
                ->columns(2),
        ]);
    }
}
