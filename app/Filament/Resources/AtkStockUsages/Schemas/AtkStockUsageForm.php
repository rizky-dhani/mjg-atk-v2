<?php

namespace App\Filament\Resources\AtkStockUsages\Schemas;

use App\Models\User;
use App\Models\AtkItem;
use App\Models\AtkCategory;
use App\Models\AtkBudgeting;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\AtkDivisionStock;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Size;

class AtkStockUsageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rejection Details')
                ->visible(function ($get, $record) {
                    // Show this section only when there's a rejection
                    if (!$record) {
                        return false;
                    }

                    // Check if there are any rejection records in ApprovalHistory
                    $hasRejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\Models\AtkStockUsage')->where('approvable_id', $record->id)->where('action', 'rejected')->exists();

                    return $hasRejection;
                })
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->readOnly()
                            ->dehydrated(false) // Don't include this in form data
                            ->formatStateUsing(function ($record) {
                                if (!$record) {
                                    return null;
                                }

                                // Get the most recent rejection reason from ApprovalHistory
                                $rejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\Models\AtkStockUsage')->where('approvable_id', $record->id)->where('action', 'rejected')->latest('performed_at')->first();

                                return $rejection ? $rejection->rejection_reason : null;
                            }),
                        TextInput::make('rejector_name')
                            ->label('Rejected By')
                            ->readOnly()
                            ->dehydrated(false) // Don't include this in form data
                            ->formatStateUsing(function ($record) {
                                if (!$record) {
                                    return null;
                                }

                                // Get the most recent rejection's user from ApprovalHistory
                                $rejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\Models\AtkStockUsage')->where('approvable_id', $record->id)->where('action', 'rejected')->latest('performed_at')->first();

                                return $rejection ? $rejection->user->name : null;
                            }),
                    ]),
                ]),
            Section::make('Stock Usage Items')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('atkStockUsageItems')
                        ->relationship()
                        ->cloneable()
                        ->live()
                        ->columns(5)
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
                                        'moving_average_cost' => null,
                                        'total_cost' => null,
                                    ];

                                    // Insert at correct position
                                    $keys = array_keys($state);
                                    $currentIndex = array_search($currentKey, $keys);

                                    $newState = array_slice($state, 0, $currentIndex + 1, true) + [$newKey => $newItem] + array_slice($state, $currentIndex + 1, null, true);

                                    $component->state($newState);
                                }),
                        ])
                        ->schema([
                            Select::make('category_id')
                                ->label('Category')
                                ->options(AtkCategory::all()->pluck('name', 'id'))
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
                                    if (!$categoryId) {
                                        return AtkItem::all()->pluck('name', 'id');
                                    }

                                    return AtkItem::where('category_id', $categoryId)->pluck('name', 'id');
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
                                            $set('category_id', $item->category_id);
                                            // Also set the category in the form to keep it in sync
                                            // $set('item_category_id', $item->category_id);
                                        }

                                        // Set the moving_average_cost based on the selected item
                                        $divisionId = auth()->user()->division_id ?? null;
                                        if ($divisionId) {
                                            $stock = AtkDivisionStock::where('division_id', $divisionId)->where('item_id', $state)->first();

                                            if ($stock) {
                                                $set('moving_average_cost', $stock->moving_average_cost);

                                                // Update total cost based on new moving_average_cost and existing quantity
                                                $quantity = (int) ($get('quantity') ?? 0);
                                                $totalCost = $quantity * $stock->moving_average_cost;
                                                $set('total_cost', $totalCost);
                                            } else {
                                                $set('moving_average_cost', 0);
                                                // Update total cost to 0 since moving_average_cost is 0
                                                $set('total_cost', 0);
                                            }

                                            // Trigger potential_cost recalculation
                                            $items = $get('../../atkStockUsageItems') ?? [];
                                            $set('../../atkStockUsageItems', $items);
                                        }
                                    }
                                }),
                            TextInput::make('quantity')
                                ->label('Quantity Used')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->helperText(function (callable $get) {
                                    $itemId = $get('item_id');
                                    if (!$itemId) {
                                        return '';
                                    }

                                    $stock = AtkDivisionStock::where('division_id', auth()->user()->division_id ?? null)
                                        ->where('item_id', $itemId)
                                        ->first();

                                    $currentStock = $stock ? $stock->current_stock : 0;

                                    return "Current Stock: {$currentStock}";
                                })
                                ->live()
                                ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                    $itemId = $get('item_id');
                                    if (!$itemId || !$state) {
                                        return;
                                    }

                                    $stock = AtkDivisionStock::where('division_id', auth()->user()->division_id ?? null)
                                        ->where('item_id', $itemId)
                                        ->first();

                                    $currentStock = $stock ? $stock->current_stock : 0;

                                    if ($state < 0) {
                                        // Reset to 0 if negative
                                        $set('quantity', 0);

                                        // Show notification to user
                                        Notification::make()->title('Quantity cannot be negative')->body('Quantity used cannot be less than zero')->warning()->send();
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

                                    // Recalculate total cost based on new quantity
                                    $movingAverageCost = (int) ($get('moving_average_cost') ?? 0);
                                    $newTotal = (int) $state * $movingAverageCost;
                                    $set('total_cost', $newTotal);

                                    // Trigger potential_cost recalculation by updating a parent field
                                    $items = $get('../../atkStockUsageItems') ?? [];
                                    $set('../../atkStockUsageItems', $items);
                                })
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail, $livewire) {
                                            // Extract the repeater index from the attribute name
                                            // e.g., "data.items.0.quantity" -> index 0
                                            preg_match('/atkStockUsageItems\\.(\d+)\\.quantity/', $attribute, $matches);
                                            $index = $matches[1] ?? null;

                                            if ($index === null) {
                                                return;
                                            }

                                            // Get the item_id for this repeater item
                                            $itemId = data_get($livewire, "data.atkStockUsageItems.{$index}.item_id");

                                            if (!$itemId || !$value) {
                                                return;
                                            }

                                            if ($value < 0) {
                                                $fail('Quantity used cannot be negative.');

                                                return;
                                            }

                                            $stock = AtkDivisionStock::where('division_id', auth()->user()->division_id ?? null)
                                                ->where('item_id', $itemId)
                                                ->first();

                                            $currentStock = $stock ? $stock->current_stock : 0;

                                            if ($value > $currentStock) {
                                                $fail("Quantity used ({$value}) exceeds current stock ({$currentStock}) for this item.");
                                            }
                                        };
                                    },
                                ]),

                            TextInput::make('moving_average_cost')
                                ->label('Average Cost')
                                ->readOnly()
                                ->live() // Update in real-time when dependencies change
                                ->prefix('Rp')
                                ->formatStateUsing(function ($state) {
                                    // Display the stored value from the model with Rp prefix
                                    return $state ?? 0;
                                }),
                            TextInput::make('total_cost')
                                ->label('Total Cost')
                                ->dehydrated(false) // Don't include in form data
                                ->live() // Update in real-time when dependencies change
                                ->prefix('Rp')
                                ->formatStateUsing(function (callable $get) {
                                    $quantity = (int) ($get('quantity') ?? 0);
                                    $movingAverageCost = (int) ($get('moving_average_cost') ?? 0);
                                    $total = $quantity * $movingAverageCost;

                                    return $total;
                                }),
                            \Filament\Forms\Components\Hidden::make('category_id'),
                        ])
                        ->minItems(1)
                        ->addActionLabel('Add Item')
                        ->reorderableWithButtons()
                        ->collapsible(),
                ]),
            // Budget Information SectionSection::make('Budget Information')
            Section::make('Budget Information')
                ->columnSpanFull()
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('current_budget')
                            ->label('Current Budget')
                            ->dehydrated(false)
                            ->prefix('Rp')
                            ->formatStateUsing(function () {
                                $divisionId = auth()->user()->division_id ?? null;
                                $currentYear = now()->year;

                                if (!$divisionId) {
                                    return 'N/A';
                                }

                                $budget = AtkBudgeting::where('division_id', $divisionId)->where('fiscal_year', $currentYear)->first();

                                return $budget ? $budget->budget_amount : 0;
                            }),
                        TextInput::make('potential_cost')
                            ->label('Potential Cost')
                            ->dehydrated(false) // Don't include in form data
                            ->readOnly()
                            ->prefix('Rp')
                            ->default(0)
                            ->extraInputAttributes(['class' => 'bg-blue-50']),

                        TextInput::make('potential_remaining_budget')
                            ->label('Potential Remaining Budget')
                            ->dehydrated(false) // Don't include in form data
                            ->readOnly()
                            ->prefix('Rp')
                            ->default(0)
                            ->extraInputAttributes(['class' => 'bg-green-50']),
                    ]),
                    Actions::make([
                        Action::make('calculate')
                            ->label('Calculate Budget')
                            ->button()
                            ->size(Size::Large)
                            ->action(function (callable $set, callable $get) {
                                // Calculate potential cost from all repeater items
                                $items = $get('atkStockUsageItems') ?? [];
                                $potentialCost = 0;

                                foreach ($items as $item) {
                                    if (isset($item['quantity']) && isset($item['moving_average_cost'])) {
                                        $potentialCost += (int)$item['quantity'] * (int)$item['moving_average_cost'];
                                    }
                                }

                                // Update potential_cost field
                                $set('potential_cost', $potentialCost);

                                // Calculate potential remaining budget
                                $divisionId = auth()->user()->division_id ?? null;
                                $currentYear = now()->year;

                                if ($divisionId) {
                                    $budget = AtkBudgeting::where('division_id', $divisionId)
                                        ->where('fiscal_year', $currentYear)
                                        ->first();

                                    if ($budget) {
                                        $usedBudget = $budget->used_amount;
                                        $potentialRemaining = $budget->budget_amount - $usedBudget - $potentialCost;
                                        
                                        // Update potential_remaining_budget field
                                        $set('potential_remaining_budget', $potentialRemaining);
                                    }
                                }
                            })
                            ->color('primary'),
                    ])
                    ->fullWidth(),
                ])
                ->collapsible(),
        ]);
    }
}
