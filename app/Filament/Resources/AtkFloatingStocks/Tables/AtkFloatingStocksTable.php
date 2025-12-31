<?php

namespace App\Filament\Resources\AtkFloatingStocks\Tables;

use App\Services\FloatingStockService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AtkFloatingStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('moving_average_cost')
                    ->label('MAC')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('total_value')
                    ->label('Total Value')
                    ->money('IDR', locale: 'id')
                    ->state(fn ($record) => $record->getTotalStockValue())
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('adjust')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Adjustment Quantity')
                            ->numeric()
                            ->required()
                            ->helperText('Use positive for addition, negative for reduction.'),
                        TextInput::make('unit_cost')
                            ->label('Unit Cost (for additions)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(fn ($record) => $record->moving_average_cost),
                    ])
                    ->action(function ($record, array $data, FloatingStockService $service) {
                        $service->recordTransaction(
                            $record->item_id,
                            'adjustment',
                            (int) $data['quantity'],
                            (int) $data['unit_cost']
                        );
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
