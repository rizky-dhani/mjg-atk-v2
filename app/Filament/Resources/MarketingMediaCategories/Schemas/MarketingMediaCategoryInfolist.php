<?php

namespace App\Filament\Resources\MarketingMediaCategories\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MarketingMediaCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategori Marketing Media')
                    ->description('Informasi dasar kategori marketing media')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
