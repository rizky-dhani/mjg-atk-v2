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
                                    }),
                                TextInput::make('quantity')
                                    ->label('Quantity to Transfer')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText(function (callable $get, $state, $component) {
                                        $itemId = $get('item_id');
                                        // Get the source division from the main form context using getContainer()
                                        $sourceDivisionId = $component->getContainer()->getParentComponent()->getState()['source_division_id'] ?? null;

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
                                    ->afterStateUpdated(function (callable $get, callable $set, $state, $component) {
                                        $itemId = $get('item_id');
                                        // Get the source division from the main form context
                                        $sourceDivisionId = $component->getContainer()->getParentComponent()->getState()['source_division_id'] ?? null;

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

                                                // Get the item_id and use the main record's source_division_id
                                                $itemId = data_get($livewire, "data.transferStockItems.{$index}.item_id");
                                                $sourceDivisionId = data_get($livewire, "data.source_division_id"); // Get from main record

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
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
                Section::make('Informasi Transfer Stok')
                    ->description('Data permintaan transfer stok')
                    ->schema([
                        Select::make('source_division_id')
                            ->label('Divisi Sumber')
                            ->columnSpanFull()
                            ->options(function (callable $get) {
                                $userDivisionId = auth()->user()?->division_id;

                                // Get all items and their quantities from the repeater
                                $transferItems = $get('transferStockItems') ?? [];

                                if (empty($transferItems)) {
                                    // If no items yet, return all divisions except user's division
                                    $query = UserDivision::query();
                                    if ($userDivisionId) {
                                        $query->where('id', '!=', $userDivisionId);
                                    }
                                    return $query->pluck('name', 'id');
                                }

                                // Get all divisions that have sufficient stock for ALL items
                                $validDivisionIds = collect();

                                foreach ($transferItems as $index => $item) {
                                    $itemId = $item['item_id'] ?? null;
                                    $quantity = $item['quantity'] ?? 0;

                                    if ($itemId && $quantity > 0) {
                                        $divisionsWithSufficientStock = AtkDivisionStock::where('item_id', $itemId)
                                            ->where('current_stock', '>=', $quantity)
                                            ->pluck('division_id');

                                        if ($validDivisionIds->isEmpty()) {
                                            $validDivisionIds = collect($divisionsWithSufficientStock);
                                        } else {
                                            // Intersect with previous valid divisions (AND logic - divisions that have all items)
                                            $validDivisionIds = $validDivisionIds->intersect($divisionsWithSufficientStock);
                                        }
                                    }
                                }

                                // If no items have been specified yet, return all divisions
                                if ($validDivisionIds->isEmpty()) {
                                    // If no divisions have all items, return empty options
                                    // This will effectively block the form submission until user adjusts quantities or items
                                    $validDivisionIds = collect();
                                }

                                // Filter out user's own division
                                if ($userDivisionId) {
                                    $validDivisionIds = $validDivisionIds->filter(function ($id) use ($userDivisionId) {
                                        return $id != $userDivisionId;
                                    });
                                }

                                // Get the transfer items again to build detailed options
                                $transferItems = $get('transferStockItems') ?? [];

                                // Build detailed options with stock information
                                $detailedOptions = [];
                                $validDivisions = UserDivision::whereIn('id', $validDivisionIds)->get();

                                foreach ($validDivisions as $division) {
                                    // Get stock information for each requested item in this division
                                    $itemDetails = [];
                                    $itemNumber = 1; // Use a counter for item numbering instead of relying on array keys
                                    foreach ($transferItems as $index => $item) {
                                        $itemId = $item['item_id'] ?? null;
                                        if ($itemId) {
                                            $divisionStock = AtkDivisionStock::where('division_id', $division->id)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            $stock = $divisionStock ? $divisionStock->current_stock : 0;

                                            $atkItem = AtkItem::find($itemId);
                                            $unitOfMeasure = $atkItem ? ($atkItem->unit_of_measure ?? 'pcs') : 'pcs';

                                            // Format: Item #(number): (division's current_stock) (item's unit_of_measure)
                                            $itemDetails[] = "Item #{$itemNumber}: {$stock} {$unitOfMeasure}";
                                            $itemNumber++;
                                        }
                                    }

                                    // Combine division name with item details
                                    $detailedName = $division->name . ' [' . implode(' | ', $itemDetails) . ']';
                                    $detailedOptions[$division->id] = $detailedName;
                                }

                                return $detailedOptions;
                            })
                            ->searchable()
                            ->preload()
                            ->live() // Make it reactive
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
