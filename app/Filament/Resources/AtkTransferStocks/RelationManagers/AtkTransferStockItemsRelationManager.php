<?php

namespace App\Filament\Resources\AtkTransferStocks\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\AtkItem;
use Filament\Tables\Table;
use App\Models\AtkCategory;
use App\Models\UserDivision;
use Filament\Schemas\Schema;
use App\Models\AtkDivisionStock;
use Filament\Actions\EditAction;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use App\Models\AtkTransferStockItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;

class AtkTransferStockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'transferStockItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('item_category_id')
                    ->label('Category')
                    ->options(AtkCategory::pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                        // Reset item selection when category changes
                        $set('item_id', null);
                    }),
                Forms\Components\Select::make('item_id')
                    ->label('Item')
                    ->options(function (callable $get) {
                        $categoryId = $get('item_category_id');
                        if (! $categoryId) {
                            return AtkItem::pluck('name', 'id');
                        }

                        return AtkItem::where('category_id', $categoryId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                        // Automatically set the category_id when item is selected
                        if ($state) {
                            $item = AtkItem::find($state);
                            if ($item) {
                                $set('item_category_id', $item->category_id);
                            }
                        }
                    }),
                Forms\Components\Select::make('source_division_id')
                    ->label('Divisi Sumber')
                    ->options(function (callable $get) {
                        $itemId = $get('item_id');
                        $quantity = $get('quantity') ?: 0;
                        
                        if (! $itemId) {
                            return UserDivision::pluck('name', 'id');
                        }

                        // Get divisions that have the item with sufficient stock
                        $sufficientDivisions = AtkDivisionStock::where('item_id', $itemId)
                            ->where('current_stock', '>=', $quantity)
                            ->pluck('current_stock', 'division_id')
                            ->toArray();

                        // Get division names with stock counts
                        $divisionIds = array_keys($sufficientDivisions);
                        $divisions = UserDivision::whereIn('id', $divisionIds)->get();

                        // Create options with stock counts
                        $options = [];
                        foreach ($divisions as $division) {
                            $stockCount = $sufficientDivisions[$division->id];
                            $options[$division->id] = $division->name . " ({$stockCount} available)";
                        }

                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(function (): bool {
                        $user = auth()->user();
                        return $user && $user->roles->pluck('id')->contains(3) && $user->division_id == 5;
                    })
                    ->live(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('itemCategory.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Nama Item'),
                Tables\Columns\TextColumn::make('sourceDivision.name')
                    ->label('Divisi Sumber'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}