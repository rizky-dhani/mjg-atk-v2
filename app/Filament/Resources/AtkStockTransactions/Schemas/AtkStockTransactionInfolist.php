<?php

namespace App\Filament\Resources\AtkStockTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkStockTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Transaction ID'),
                        TextEntry::make('division.name')
                            ->label('Division'),
                        TextEntry::make('item.name')
                            ->label('Item'),
                        TextEntry::make('type')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'request' => 'success',
                                'usage' => 'danger',
                                'adjustment' => 'warning',
                                'transfer' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('quantity')
                            ->numeric(),
                        TextEntry::make('unit_cost')
                            ->money('IDR'),
                        TextEntry::make('total_cost')
                            ->money('IDR'),
                        TextEntry::make('mac_snapshot')
                            ->label('Moving Average Cost (Snapshot)')
                            ->money('IDR'),
                        TextEntry::make('balance_snapshot')
                            ->label('Balance (Snapshot)'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
