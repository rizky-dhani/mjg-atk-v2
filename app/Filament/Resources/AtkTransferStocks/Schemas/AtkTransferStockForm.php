<?php

namespace App\Filament\Resources\AtkTransferStocks\Schemas;

use App\Models\AtkDivisionStock;
use App\Models\AtkDivisionStockSetting;
use App\Models\AtkItem;
use App\Models\UserDivision;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkTransferStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Section::make('Informasi Transfer Stok')
                    ->description('Data permintaan transfer stok')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->visible(function (): bool {
                                $user = auth()->user();

                                return $user && $user->roles->pluck('id')->contains(3) && $user->division_id == 5;
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Transfer Items')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('transferStockItems')
                            ->relationship()
                            ->cloneable()
                            ->extraItemActions([
                                Action::make('add_new_after')
                                    ->icon('heroicon-m-plus')
                                    ->color('primary')
                                    ->action(function (array $arguments, Repeater $component) {
                                        $state = $component->getState();
                                        $currentKey = $arguments['item'];

                                        $newKey = uniqid('item_');
                                        // Pre-populate with empty values for proper binding
                                        $newItem = [
                                            'item_id' => null,
                                            'source_division_id' => null,
                                            'quantity' => null,
                                            'notes' => null,
                                        ];

                                        // Insert at correct position
                                        $keys = array_keys($state);
                                        $currentIndex = array_search($currentKey, $keys);

                                        $newState = array_slice($state, 0, $currentIndex + 1, true) +
                                                [$newKey => $newItem] +
                                                array_slice($state, $currentIndex + 1, null, true);

                                        $component->state($newState);
                                    }),
                            ])
                            ->schema([
                                Select::make('item_category_id')
                                    ->label('Category')
                                    ->options(\App\Models\AtkCategory::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->reactive()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        // Reset item selection when category changes
                                        $set('item_id', null);
                                    }),
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (callable $get) {
                                        $categoryId = $get('item_category_id');
                                        if (! $categoryId) {
                                            return AtkItem::all()->pluck('name', 'id');
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
                                    })
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (! $itemId) {
                                            return '';
                                        }

                                        // Get current stock in requesting division (based on user's division)
                                        $requestingDivisionId = auth()->user()->division_id ?? null;
                                        if (! $requestingDivisionId) {
                                            return '';
                                        }

                                        $stock = AtkDivisionStock::where('division_id', $requestingDivisionId)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $currentStock = $stock ? $stock->current_stock : 0;

                                        // Get max limit for this item in requesting division
                                        $setting = AtkDivisionStockSetting::where('division_id', $requestingDivisionId)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $maxLimit = $setting ? $setting->max_limit : 'unlimited';

                                        return "Current: {$currentStock} | Max limit: {$maxLimit}";
                                    }),
                                TextInput::make('quantity')
                                    ->label('Quantity to Transfer')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        $sourceDivisionId = $get('source_division_id');

                                        if (! $itemId || ! $sourceDivisionId) {
                                            return '';
                                        }

                                        $stock = AtkDivisionStock::where('division_id', $sourceDivisionId)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $availableStock = $stock ? $stock->current_stock : 0;

                                        return "Available in source: {$availableStock}";
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $itemId = $get('item_id');
                                        $sourceDivisionId = $get('source_division_id');

                                        if (! $itemId || ! $sourceDivisionId || ! $state) {
                                            return;
                                        }

                                        $stock = AtkDivisionStock::where('division_id', $sourceDivisionId)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $availableStock = $stock ? $stock->current_stock : 0;

                                        if ($state > $availableStock) {
                                            // Set to available stock
                                            $set('quantity', $availableStock);

                                            // Show notification to user
                                            Notification::make()
                                                ->title('Quantity exceeds available stock')
                                                ->body("Quantity requested exceeds available stock, maximum quantity available: {$availableStock}")
                                                ->warning()
                                                ->send();
                                        }
                                    })
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail, $livewire) {
                                                // Extract the repeater index from the attribute name
                                                preg_match('/transferStockItems\.(\d+)\.quantity/', $attribute, $matches);
                                                $index = $matches[1] ?? null;

                                                if ($index === null) {
                                                    return;
                                                }

                                                // Get the item_id and source_division_id for this repeater item
                                                $itemId = data_get($livewire, "data.transferStockItems.{$index}.item_id");
                                                $sourceDivisionId = data_get($livewire, "data.transferStockItems.{$index}.source_division_id");

                                                if (! $itemId || ! $sourceDivisionId || ! $value) {
                                                    return;
                                                }

                                                $stock = AtkDivisionStock::where('division_id', $sourceDivisionId)
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                $availableStock = $stock ? $stock->current_stock : 0;

                                                if ($value > $availableStock) {
                                                    $fail("Quantity requested ({$value}) exceeds available stock ({$availableStock}) for this item.");
                                                }
                                            };
                                        },
                                    ]),
                                Select::make('source_division_id')
                                    ->label('Divisi Sumber')
                                    ->options(function (callable $get) {
                                        $itemId = $get('item_id');
                                        $quantity = $get('quantity');

                                        if (! $itemId) {
                                            return UserDivision::all()->pluck('name', 'id');
                                        }

                                        // Get divisions that have the item with sufficient stock
                                        $sufficientDivisions = AtkDivisionStock::where('item_id', $itemId)
                                            ->where('current_stock', '>=', $quantity ?: 0) // Use 0 if quantity is null
                                            ->pluck('current_stock', 'division_id')
                                            ->toArray();

                                        // Get division names with stock counts
                                        $divisionIds = array_keys($sufficientDivisions);
                                        $divisions = UserDivision::whereIn('id', $divisionIds)->get();

                                        // Create options with stock counts
                                        $options = [];
                                        foreach ($divisions as $division) {
                                            $stockCount = $sufficientDivisions[$division->id];
                                            $options[$division->id] = $division->name." ({$stockCount} available)";
                                        }

                                        return $options;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(3)
                                    ->visible(function (): bool {
                                        $user = auth()->user();

                                        return $user && $user->roles->pluck('id')->contains(3) && $user->division_id == 5;
                                    })
                                    ->helperText(function (callable $get) {
                                        $sourceDivisionId = $get('source_division_id');
                                        $itemId = $get('item_id');

                                        if (! $sourceDivisionId || ! $itemId) {
                                            return '';
                                        }

                                        $stock = AtkDivisionStock::where('division_id', $sourceDivisionId)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $availableStock = $stock ? $stock->current_stock : 0;

                                        return "Available in source: {$availableStock}";
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $itemId = $get('item_id');
                                        $quantity = $get('quantity');

                                        if (! $itemId || ! $state) {
                                            return;
                                        }

                                        // Get source division stock
                                        $stock = AtkDivisionStock::where('division_id', $state)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $availableStock = $stock ? $stock->current_stock : 0;

                                        // If the current quantity exceeds available stock, adjust it
                                        if ($quantity && $quantity > $availableStock) {
                                            $set('quantity', $availableStock);

                                            // Show notification to user
                                            Notification::make()
                                                ->title('Quantity exceeds available stock')
                                                ->body("Quantity requested exceeds available stock, maximum quantity available: {$availableStock}")
                                                ->warning()
                                                ->send();
                                        }
                                    }),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                        Textarea::make('notes')
                            ->maxLength(1000)
                            ->rows(1)
                            ->autosize(),
                    ]),
            ]);
    }
}
