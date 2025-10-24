<?php

namespace App\Filament\Resources\MarketingMediaStockUsages\Schemas;

use App\Models\MarketingMediaCategory;
use App\Models\MarketingMediaDivisionStock;
use App\Models\MarketingMediaItem;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketingMediaStockUsageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rejection Details')
                    ->visible(function ($get, $record) {
                        // Show this section only when there's a rejection
                        if (! $record) {
                            return false;
                        }

                        // Check if there are any rejection records in ApprovalHistory
                        $hasRejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\\Models\\MarketingMediaStockUsage')
                            ->where('approvable_id', $record->id)
                            ->where('action', 'rejected')
                            ->exists();

                        return $hasRejection;
                    })
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->readOnly()
                                    ->dehydrated(false) // Don't include this in form data
                                    ->formatStateUsing(function ($record) {
                                        if (! $record) {
                                            return null;
                                        }

                                        // Get the most recent rejection reason from ApprovalHistory
                                        $rejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\\Models\\MarketingMediaStockUsage')
                                            ->where('approvable_id', $record->id)
                                            ->where('action', 'rejected')
                                            ->latest('performed_at')
                                            ->first();

                                        return $rejection ? $rejection->rejection_reason : null;
                                    }),
                                TextInput::make('rejector_name')
                                    ->label('Rejected By')
                                    ->readOnly()
                                    ->dehydrated(false) // Don't include this in form data
                                    ->formatStateUsing(function ($record) {
                                        if (! $record) {
                                            return null;
                                        }

                                        // Get the most recent rejection's user from ApprovalHistory
                                        $rejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\\Models\\MarketingMediaStockUsage')
                                            ->where('approvable_id', $record->id)
                                            ->where('action', 'rejected')
                                            ->latest('performed_at')
                                            ->first();

                                        return $rejection ? $rejection->user->name : null;
                                    }),
                            ]),
                    ]),
                Section::make('Stock Usage Items')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('marketingMediaStockUsageItems')
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
                                            'item_category_id' => null,
                                            'item_id' => null,
                                            'category_id' => null,
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
                                Select::make('category_id')
                                    ->label('Category')
                                    ->options(MarketingMediaCategory::all()->pluck('name', 'id'))
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
                                        $categoryId = $get('category_id');
                                        if (! $categoryId) {
                                            return MarketingMediaItem::all()->pluck('name', 'id');
                                        }

                                        return MarketingMediaItem::where('category_id', $categoryId)
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        // Automatically set the category_id when item is selected
                                        if ($state) {
                                            $item = MarketingMediaItem::find($state);
                                            if ($item) {
                                                $set('category_id', $item->category_id);
                                                // Also set the category in the form to keep it in sync
                                                // $set('item_category_id', $item->category_id);
                                            }
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->label('Quantity Used')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (! $itemId) {
                                            return '';
                                        }

                                        $stock = MarketingMediaDivisionStock::where('division_id', auth()->user()->division_id ?? null)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $currentStock = $stock ? $stock->current_stock : 0;

                                        return "Current Stock: {$currentStock}";
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $itemId = $get('item_id');
                                        if (! $itemId || ! $state) {
                                            return;
                                        }

                                        $stock = MarketingMediaDivisionStock::where('division_id', auth()->user()->division_id ?? null)
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $currentStock = $stock ? $stock->current_stock : 0;

                                        if ($state < 0) {
                                            // Reset to 0 if negative
                                            $set('quantity', 0);

                                            // Show notification to user
                                            Notification::make()
                                                ->title('Quantity cannot be negative')
                                                ->body('Quantity used cannot be less than zero')
                                                ->warning()
                                                ->send();
                                        } elseif ($state > $currentStock) {
                                            // Reset to current stock
                                            $set('quantity', $currentStock);

                                            // Show notification to user
                                            Notification::make()
                                                ->title('Quantity exceeds current stock')
                                                ->body("Quantity used exceeds current stock: {$currentStock}")
                                                ->warning()
                                                ->send();
                                        }
                                    })
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail, $livewire) {
                                                // Extract the repeater index from the attribute name
                                                // e.g., "data.items.0.quantity" -> index 0
                                                preg_match('/marketingMediaStockUsageItems\\.(\d+)\\.quantity/', $attribute, $matches);
                                                $index = $matches[1] ?? null;

                                                if ($index === null) {
                                                    return;
                                                }

                                                // Get the item_id for this repeater item
                                                $itemId = data_get($livewire, "data.marketingMediaStockUsageItems.{$index}.item_id");

                                                if (! $itemId || ! $value) {
                                                    return;
                                                }

                                                if ($value < 0) {
                                                    $fail('Quantity used cannot be negative.');

                                                    return;
                                                }

                                                $stock = MarketingMediaDivisionStock::where('division_id', auth()->user()->division_id ?? null)
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                $currentStock = $stock ? $stock->current_stock : 0;

                                                if ($value > $currentStock) {
                                                    $fail("Quantity used ({$value}) exceeds current stock ({$currentStock}) for this item.");
                                                }
                                            };
                                        },
                                    ]),

                                Textarea::make('notes')
                                    ->maxLength(1000)
                                    ->rows(1)
                                    ->autosize(),
                                \Filament\Forms\Components\Hidden::make('category_id'),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
            ]);
    }
}
