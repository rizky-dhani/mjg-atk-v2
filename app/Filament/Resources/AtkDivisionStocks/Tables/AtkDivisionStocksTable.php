<?php

namespace App\Filament\Resources\AtkDivisionStocks\Tables;

use App\Filament\Actions\ImportStockAction;
use App\Models\AtkDivisionStockSetting;
use App\Services\FloatingStockService;
use App\Services\StockTransactionService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
                    ->money('IDR', locale: 'id'),
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
            ->recordActions([
                ViewAction::make(), 
                EditAction::make(),
                Action::make('move_to_floating')
                    ->label('Move to Floating Stock')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn () => auth()->user()->isGA())
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to Move')
                            ->numeric()
                            ->required()
                            ->maxValue(fn ($record) => $record->current_stock),
                    ])
                    ->action(function ($record, array $data, FloatingStockService $floatingService, StockTransactionService $divisionService) {
                        DB::transaction(function () use ($record, $data, $floatingService, $divisionService) {
                            $quantity = (int) $data['quantity'];
                            $unitCost = $record->moving_average_cost;

                            // 1. Reduce from Division Stock
                            $newDivisionStock = $record->current_stock - $quantity;
                            $record->update(['current_stock' => $newDivisionStock]);

                            // 2. Record Division Transaction
                            $divisionService->recordTransactionOnly(
                                $record->division_id,
                                $record->item_id,
                                'transfer',
                                -$quantity,
                                $unitCost,
                                $record
                            );

                            // 3. Add to Floating Stock
                            $floatingService->recordTransaction(
                                $record->item_id,
                                'transfer',
                                $quantity,
                                $unitCost,
                                $record
                            );
                        });
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}