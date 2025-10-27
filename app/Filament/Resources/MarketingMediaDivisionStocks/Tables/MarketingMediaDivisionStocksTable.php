<?php

namespace App\Filament\Resources\MarketingMediaDivisionStocks\Tables;

use App\Models\MarketingMediaDivisionStockSetting;
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
                TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_limit')
                    ->label('Max Limit')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $setting = MarketingMediaDivisionStockSetting::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();

                        return $setting ? $setting->max_limit : 'N/A';
                    }),
                TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->getStateUsing(function ($record) {
                        $setting = MarketingMediaDivisionStockSetting::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();

                        if (! $setting) {
                            return 'No setting';
                        }

                        if ($record->current_stock > $setting->max_limit) {
                            return 'Over limit';
                        } elseif ($record->current_stock == $setting->max_limit) {
                            return 'At limit';
                        } elseif ($record->current_stock < $setting->max_limit) {
                            return 'Within limit';
                        } else {
                            return 'Empty';
                        }
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Over limit' => 'danger',
                        'At limit' => 'warning',
                        'Within limit' => 'success',
                        'Empty' => 'danger',
                        'No setting' => 'gray',
                        default => 'gray',
                    }),
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
