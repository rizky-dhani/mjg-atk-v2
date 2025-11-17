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
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('transferStock.sourceDivision.name')
                    ->label('Divisi Sumber'),
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