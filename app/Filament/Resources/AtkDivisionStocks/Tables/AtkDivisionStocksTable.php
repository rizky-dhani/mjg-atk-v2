<?php

namespace App\Filament\Resources\AtkDivisionStocks\Tables;

use App\Models\AtkDivisionStockSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AtkDivisionStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query->where('division_id', auth()->user()->division_id)->orderBy('category_id')->orderBy('created_at'))
            ->columns([
                TextColumn::make('item.name')->numeric()->sortable(),
                TextColumn::make('category.name')->numeric()->sortable(),
                TextColumn::make('current_stock')->numeric()->sortable(),
                TextColumn::make('moving_average_cost')
                    ->label('Average Cost')
                    ->numeric()
                    ->sortable()
                    ->money('IDR'),
                TextColumn::make('max_limit')
                    ->label('Max Stock Limit')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $setting = AtkDivisionStockSetting::where('division_id', $record->division_id)
                            ->where('item_id', $record->item_id)
                            ->first();

                        return $setting ? $setting->max_limit : 'N/A';
                    }),
                TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->getStateUsing(function ($record) {
                        $setting = AtkDivisionStockSetting::where('division_id', $record->division_id)
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
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
