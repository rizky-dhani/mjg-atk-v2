<?php

namespace App\Filament\Resources\AtkFloatingStockTransactionHistories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkFloatingStockTransactionHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Transaksi')
                ->schema([
                    TextEntry::make('created_at')->label('Tanggal')->dateTime(),
                    TextEntry::make('item.name')->label('Barang'),
                    TextEntry::make('type')
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
                    TextEntry::make('quantity')->label('Jumlah'),
                    TextEntry::make('unit_cost')->label('Biaya Satuan')->money('idr'),
                    TextEntry::make('total_cost')->label('Total Biaya')->money('idr'),
                    TextEntry::make('mac_snapshot')->label('MAC Snapshot')->money('idr'),
                    TextEntry::make('balance_snapshot')->label('Saldo Snapshot'),
                    TextEntry::make('trx_src_type')->label('Tipe Sumber'),
                    TextEntry::make('trx_src_id')->label('ID Sumber'),
                ])->columns(2),
        ]);
    }
}
