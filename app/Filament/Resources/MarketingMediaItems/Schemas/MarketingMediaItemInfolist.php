<?php

namespace App\Filament\Resources\MarketingMediaItems\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;

class MarketingMediaItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('name')
                            ->icon('heroicon-m-pencil-square')
                            ->placeholder('N/A'),
                        TextEntry::make('description')
                            ->placeholder('N/A')
                            ->columnSpanFull(),
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->placeholder('N/A'),
                        TextEntry::make('unit')
                            ->placeholder('N/A'),
                        TextEntry::make('unit_price')
                            ->money('usd')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime('M j, Y H:i'),
                        TextEntry::make('updated_at')
                            ->dateTime('M j, Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function configureInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(static::configure(new Schema)->components);
    }
}
