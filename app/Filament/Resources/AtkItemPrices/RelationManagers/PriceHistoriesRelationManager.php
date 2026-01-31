<?php

namespace App\Filament\Resources\AtkItemPrices\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'priceHistories';

    protected static ?string $title = 'Riwayat Perubahan Harga';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Read-only view
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('effective_date')
            ->columns([
                TextColumn::make('old_price')
                    ->label('Harga Lama')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('new_price')
                    ->label('Harga Baru')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('effective_date')
                    ->label('Tanggal Efektif')
                    ->date()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Diubah Oleh')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu Perubahan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Read-only
            ])
            ->recordActions([
                // Read-only
            ])
            ->toolbarActions([
                // Read-only
            ])
            ->defaultSort('created_at', 'desc');
    }
}
