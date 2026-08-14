<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AtkRequestFromFloatingStocks\Schemas\AtkRequestFromFloatingStockForm;
use App\Filament\Resources\AtkStockRequests\Schemas\AtkStockRequestForm;
use App\Filament\Resources\AtkStockUsages\Schemas\AtkStockUsageForm;
use App\Models\AtkRequestFromFloatingStock;
use App\Models\AtkStockRequest;
use App\Models\AtkStockUsage;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class QuickCreate extends Widget
{
    protected string $view = 'filament.widgets.quick-create';

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('create atk-stock-request') ||
            $user->can('create atk-stock-usage') ||
            $user->can('create atk-request-from-floating-stock')
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getActions(): array
    {
        $user = auth()->user();
        $divisions = $user->divisions;

        $actions = [];

        if ($user->can('create atk-stock-request')) {
            $actions[] = Action::make('create_permintaan')
                ->label('Permintaan ATK')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->modalHeading('Buat Permintaan ATK')
                ->modalSubmitActionLabel('Buat')
                ->form([
                    Select::make('division_id')
                        ->label('Divisi')
                        ->options($divisions->pluck('name', 'id'))
                        ->required(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($user) {
                    $request = AtkStockRequest::create([
                        'division_id' => $data['division_id'],
                        'requester_id' => $user->id,
                        'notes' => $data['notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Draft permintaan ATK berhasil dibuat')
                        ->success()
                        ->send();
                });
        }

        if ($user->can('create atk-stock-usage')) {
            $actions[] = Action::make('create_pengeluaran')
                ->label('Pengeluaran ATK')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalHeading('Buat Pengeluaran ATK')
                ->modalSubmitActionLabel('Buat')
                ->form([
                    Select::make('division_id')
                        ->label('Divisi')
                        ->options($divisions->pluck('name', 'id'))
                        ->required(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($user) {
                    $usage = AtkStockUsage::create([
                        'division_id' => $data['division_id'],
                        'requester_id' => $user->id,
                        'notes' => $data['notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Draft pengeluaran ATK berhasil dibuat')
                        ->success()
                        ->send();
                });
        }

        if ($user->can('create atk-request-from-floating-stock')) {
            $actions[] = Action::make('create_stok_umum')
                ->label('Minta Stok Umum')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('success')
                ->modalHeading('Minta Stok Umum')
                ->modalSubmitActionLabel('Buat')
                ->form([
                    Select::make('division_id')
                        ->label('Divisi')
                        ->options($divisions->pluck('name', 'id'))
                        ->required(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($user) {
                    $request = AtkRequestFromFloatingStock::create([
                        'division_id' => $data['division_id'],
                        'requester_id' => $user->id,
                        'notes' => $data['notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Draft permintaan stok umum berhasil dibuat')
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }
}
