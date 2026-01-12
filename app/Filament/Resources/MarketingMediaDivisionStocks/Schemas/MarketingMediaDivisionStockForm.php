<?php

namespace App\Filament\Resources\MarketingMediaDivisionStocks\Schemas;

use App\Models\MarketingMediaCategory;
use App\Models\MarketingMediaItem;
use App\Models\UserDivision;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MarketingMediaDivisionStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('division_id')
                    ->label('Divisi')
                    ->options(UserDivision::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
                Select::make('item_id')
                    ->label('Item Marketing Media')
                    ->options(MarketingMediaItem::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
                Select::make('category_id')
                    ->label('Kategori')
                    ->options(MarketingMediaCategory::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
                TextInput::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('max_stock_limit')
                    ->label('Batas Maksimal Stok')
                    ->numeric()
                    ->nullable(),
            ]);
    }
}
