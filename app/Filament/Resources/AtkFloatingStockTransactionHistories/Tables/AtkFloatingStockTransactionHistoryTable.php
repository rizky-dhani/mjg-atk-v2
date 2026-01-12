<?php

namespace App\Filament\Resources\AtkFloatingStockTransactionHistories\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AtkFloatingStockTransactionHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('item.name')
                    ->label('Barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'adjustment' => 'warning',
                        'transfer' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'adjustment' => 'Penyesuaian',
                        'transfer' => 'Transfer',
                        default => $state,
                    }),
                TextColumn::make('sourceDivision.name')
                    ->label('Divisi Asal/Tujuan')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('Biaya Satuan')
                    ->money('idr')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Total Biaya')
                    ->money('idr')
                    ->sortable(),
                TextColumn::make('balance_snapshot')
                    ->label('Saldo Akhir')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
