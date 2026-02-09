<?php

namespace App\Filament\Resources\AtkStockRequests\Schemas;

use App\Models\AtkCategory;
use App\Models\AtkDivisionStock;
use App\Models\AtkDivisionStockSetting;
use App\Models\AtkItem;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AtkStockRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('division_id')
                                    ->label('Divisi')
                                    ->options(function () {
                                        if (auth()->user()->isSuperAdmin()) {
                                            return \App\Models\UserDivision::all()->pluck('name', 'id');
                                        }

                                        return auth()->user()->divisions->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->default(fn () => auth()->user()->divisions->first()?->id)
                                    ->hidden(fn () => ! auth()->user()->isSuperAdmin() && auth()->user()->divisions()->count() <= 1)
                                    ->dehydrated(),
                                TextInput::make('request_number')
                                    ->label('Nomor Permintaan')
                                    ->placeholder('Auto-generated')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
                Section::make('Rejection Details')
                    ->visible(function ($get, $record) {
                        // Show this section only when there's a rejection
                        if (! $record) {
                            return false;
                        }

                        // Check if there are any rejection records in ApprovalHistory
                        $hasRejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\Models\AtkStockRequest')
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
                                        $rejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\Models\AtkStockRequest')
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
                                        $rejection = \App\Models\ApprovalHistory::where('approvable_type', 'App\Models\AtkStockRequest')
                                            ->where('approvable_id', $record->id)
                                            ->where('action', 'rejected')
                                            ->latest('performed_at')
                                            ->first();

                                        return $rejection ? $rejection->user->name : null;
                                    }),
                            ]),
                    ]),
                Section::make('Stock Request Items')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('atkStockRequestItems')
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
                                                $set('category_id', $item->category_id);
                                            }
                                        }

                                        // Update current_mac when item_id changes
                                        if ($state) {
                                            $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                                ->where('item_id', $state)
                                                ->first();

                                            if ($stock) {
                                                $set('current_mac', 'Rp '.number_format($stock->moving_average_cost, 0, ',', '.'));
                                            } else {
                                                $set('current_mac', 'Rp 0');
                                            }
                                        } else {
                                            // If item_id is cleared, reset current_mac
                                            $set('current_mac', '');
                                        }

                                        // Update item_price when item_id changes
                                        if ($state) {
                                            $item = AtkItem::find($state);
                                            // Get the active price with the latest effective_date
                                            $priceModel = $item ? $item->activePrice()->first() : null;
                                            $price = $priceModel ? $priceModel->unit_price : 0;
                                            $set('item_price', 'Rp '.number_format($price, 0, ',', '.'));

                                            // Update new_mac_estimate when item_id changes
                                            $quantity = $get('quantity') ?? 0;
                                            if ($quantity > 0) {
                                                // Get current stock data
                                                $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                                    ->where('item_id', $state)
                                                    ->first();

                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                $currentMac = $stock ? $stock->moving_average_cost : 0;

                                                // Calculate new MAC using the formula:
                                                // New MAC = ((Old Stock × Old MAC) + (Incoming Stock × Incoming Unit Cost)) / (Old Stock + Incoming Stock)
                                                $totalValue = ($currentStock * $currentMac) + ($quantity * $price);
                                                $totalQuantity = $currentStock + $quantity;

                                                if ($totalQuantity == 0) {
                                                    $newMac = 0;
                                                } else {
                                                    $newMac = $totalValue / $totalQuantity;
                                                }
                                                $set('new_mac_estimate', 'Rp '.number_format((int) round($newMac), 0, ',', '.'));
                                            } else {
                                                // If quantity is 0, just show current MAC
                                                $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                                    ->where('item_id', $state)
                                                    ->first();

                                                if ($stock) {
                                                    $set('new_mac_estimate', 'Rp '.number_format($stock->moving_average_cost, 0, ',', '.'));
                                                } else {
                                                    $set('new_mac_estimate', 'Rp 0');
                                                }
                                            }
                                        } else {
                                            // If item_id is cleared, clear dependent fields
                                            $set('item_price', '');
                                            $set('new_mac_estimate', '');
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->label('Quantity Requested')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->suffix(fn (callable $get) => AtkItem::find($get('item_id'))?->unit_of_measure)
                                    ->helperText(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (! $itemId) {
                                            return '';
                                        }

                                        $setting = AtkDivisionStockSetting::where('division_id', $get('../../division_id'))
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                            ->where('item_id', $itemId)
                                            ->first();

                                        $currentStock = $stock ? $stock->current_stock : 0;
                                        $maxLimit = $setting ? $setting->max_limit : 'No limit';
                                        $availableSpace = $setting ? ($maxLimit - $currentStock) : 'Unlimited';

                                        return new \Illuminate\Support\HtmlString("
                                            <div class='flex flex-wrap gap-x-4 gap-y-1 text-xs'>
                                                <div class='bg-gray-100 px-2 py-0.5 rounded'><span class='text-gray-500'>Current:</span> <span class='font-medium'>{$currentStock}</span></div>
                                                <div class='bg-gray-100 px-2 py-0.5 rounded'><span class='text-gray-500'>Max:</span> <span class='font-medium'>{$maxLimit}</span></div>
                                                <div class='bg-green-50 px-2 py-0.5 rounded border border-green-100'><span class='text-green-600'>Available:</span> <span class='font-bold text-green-700'>{$availableSpace}</span></div>
                                            </div>
                                        ");
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $itemId = $get('item_id');

                                        // First run the original validation logic
                                        if ($itemId && $state) {
                                            $setting = AtkDivisionStockSetting::where('division_id', $get('../../division_id'))
                                                ->where('item_id', $itemId)
                                                ->first();

                                            if ($setting) {
                                                $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                $maxLimit = $setting->max_limit;
                                                $availableSpace = $maxLimit - $currentStock;

                                                if ($state > $availableSpace) {
                                                    // Reset to available space
                                                    $set('quantity', $availableSpace);

                                                    // Show notification to user
                                                    Notification::make()
                                                        ->title('Quantity exceeds maximum limit')
                                                        ->body("Quantity requested exceeds maximum limit, maximum quantity available: {$availableSpace}")
                                                        ->warning()
                                                        ->send();
                                                }
                                            }
                                        }

                                        // Update new_mac_estimate when quantity changes
                                        if ($itemId && $state) {
                                            $item = AtkItem::find($itemId);
                                            $priceModel = $item ? $item->activePrice()->first() : null;
                                            $itemPrice = $priceModel ? $priceModel->unit_price : 0;

                                            // Get current stock data
                                            $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                                ->where('item_id', $itemId)
                                                ->first();

                                            $currentStock = $stock ? $stock->current_stock : 0;
                                            $currentMac = $stock ? $stock->moving_average_cost : 0;

                                            // Calculate new MAC using the formula:
                                            // New MAC = ((Old Stock × Old MAC) + (Incoming Stock × Incoming Unit Cost)) / (Old Stock + Incoming Stock)
                                            $totalValue = ($currentStock * $currentMac) + ($state * $itemPrice);
                                            $totalQuantity = $currentStock + $state;

                                            if ($totalQuantity == 0) {
                                                $newMac = 0;
                                            } else {
                                                $newMac = $totalValue / $totalQuantity;
                                            }
                                            $set('new_mac_estimate', 'Rp '.number_format((int) round($newMac), 0, ',', '.'));
                                        } elseif (! $state) {
                                            // If quantity is 0, get current MAC (if item exists)
                                            if ($itemId) {
                                                $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                if ($stock) {
                                                    $set('new_mac_estimate', 'Rp '.number_format($stock->moving_average_cost, 0, ',', '.'));
                                                } else {
                                                    $set('new_mac_estimate', 'Rp 0');
                                                }
                                            } else {
                                                $set('new_mac_estimate', ''); // If no item either, clear field
                                            }
                                        }
                                    })
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail, $livewire) {
                                                // Extract the repeater index from the attribute name
                                                // e.g., "data.items.0.quantity" -> index 0
                                                preg_match('/atkStockRequestItems\.(\d+)\.quantity/', $attribute, $matches);
                                                $index = $matches[1] ?? null;

                                                if ($index === null) {
                                                    return;
                                                }

                                                // Get the item_id for this repeater item
                                                $itemId = data_get($livewire, "data.atkStockRequestItems.{$index}.item_id");

                                                if (! $itemId || ! $value) {
                                                    return;
                                                }

                                                $setting = AtkDivisionStockSetting::where('division_id', $get('../../division_id'))
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                if (! $setting) {
                                                    return;
                                                }

                                                $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                                    ->where('item_id', $itemId)
                                                    ->first();

                                                $currentStock = $stock ? $stock->current_stock : 0;
                                                $maxLimit = $setting->max_limit;
                                                $availableSpace = $maxLimit - $currentStock;

                                                if ($value > $availableSpace) {
                                                    $fail("Quantity requested ({$value}) exceeds maximum available quantity ({$availableSpace}) for this item.");
                                                }
                                            };
                                        },
                                    ]),

                                TextInput::make('current_mac')
                                    ->label('Current MAC')
                                    ->readOnly()
                                    ->dehydrated(false) // Don't include in form data
                                    ->live() // Make it live-update when dependencies change
                                    ->formatStateUsing(function (callable $get) {
                                        $itemId = $get('item_id');
                                        if (! $itemId) {
                                            return '';
                                        }

                                        $stock = AtkDivisionStock::where('division_id', $get('../../division_id'))
                                            ->where('item_id', $itemId)
                                            ->first();

                                        if (! $stock) {
                                            return 'Rp 0';
                                        }

                                        $mac = $stock->moving_average_cost ?? 0;

                                        return 'Rp '.number_format($mac, 0, ',', '.');
                                        // return $mac;
                                    })
                                    ->extraInputAttributes(['class' => 'bg-gray-50']),

                                TextInput::make('item_price')
                                    ->label('Item Price')
                                    ->readOnly()
                                    ->dehydrated(false) // Don't include in form data
                                    ->default('') // Set default to empty string
                                    ->extraInputAttributes(['class' => 'bg-blue-50']),

                                TextInput::make('new_mac_estimate')
                                    ->label('New MAC Estimate')
                                    ->readOnly()
                                    ->dehydrated(false) // Don't include in form data
                                    ->default('') // Set default to empty string
                                    ->extraInputAttributes(['class' => 'bg-green-50']),
                                \Filament\Forms\Components\Hidden::make('category_id'),
                            ])
                            ->columns(6)
                            ->minItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
                \Filament\Forms\Components\Hidden::make('status')
                    ->default(\App\Enums\AtkStockRequestStatus::Draft),
            ]);
    }
}
