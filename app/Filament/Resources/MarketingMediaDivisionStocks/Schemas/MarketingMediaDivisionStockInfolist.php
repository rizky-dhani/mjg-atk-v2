<?php

namespace App\Filament\Resources\MarketingMediaDivisionStocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketingMediaDivisionStockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // 1. Marketing Media Item Detail section
            Section::make('Marketing Media Item Detail')
                ->schema([
                    TextEntry::make('item.name')->label('Item Name'),
                    TextEntry::make('division.name')->label('Division Name'),
                    TextEntry::make('category.name')->label('Category Name'),
                ])
                ->columns(3)
                ->columnSpanFull(),
            // 2. Latest Stock Update section
            Section::make('Latest Stock Update')
                ->schema([
                    TextEntry::make('latest_stock_request_quantity')
                        ->label('Latest Added Quantity')
                        ->numeric()
                        ->getStateUsing(function ($record) {
                            $latestRequest = $record->marketingMediaStockRequests()
                                ->with(['marketingMediaStockRequestItems' => function ($query) use ($record) {
                                    $query->where('item_id', $record->item_id);
                                }])
                                ->latest()
                                ->first();

                            if ($latestRequest && $latestRequest->marketingMediaStockRequestItems) {
                                foreach ($latestRequest->marketingMediaStockRequestItems as $item) {
                                    if ($item->item_id == $record->item_id) {
                                        return $item->quantity;
                                    }
                                }
                            }

                            return 0;
                        }),
                    TextEntry::make('latest_stock_usage_quantity')
                        ->label('Latest Reduced Quantity')
                        ->numeric()
                        ->getStateUsing(function ($record) {
                            $latestUsage = $record->marketingMediaStockUsages()
                                ->with(['marketingMediaStockUsageItems' => function ($query) use ($record) {
                                    $query->where('item_id', $record->item_id);
                                }])
                                ->latest()
                                ->first();

                            if ($latestUsage && $latestUsage->marketingMediaStockUsageItems) {
                                foreach ($latestUsage->marketingMediaStockUsageItems as $item) {
                                    if ($item->item_id == $record->item_id) {
                                        return $item->quantity;
                                    }
                                }
                            }

                            return 0;
                        }),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
