<?php

namespace App\Filament\Resources\MarketingMediaDivisionStocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketingMediaDivisionStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['item', 'category'])
                    ->where(
                        'division_id',
                        auth()->user()->division_id,
                    ),
            )
            ->columns([
                TextColumn::make('item.name')
                    ->label('Nama Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('max_stock_limit')
                    ->label('Max Limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
