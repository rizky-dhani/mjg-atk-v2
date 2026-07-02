<?php

namespace App\Filament\Resources\AtkDivisionStocks\Pages;

use App\Filament\Resources\AtkDivisionStocks\AtkDivisionStockResource;
use App\Models\AtkDivisionStock;
use App\Models\UserDivision;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAtkDivisionStocks extends ListRecords
{
    protected static string $resource = AtkDivisionStockResource::class;

    public function getTabs(): array
    {
        $user = auth()->user();
        $divisions = ($user->isGA() || $user->isSuperAdmin())
            ? UserDivision::all()
            : $user->divisions;

        $tabs = [
            'all' => Tab::make('Semua Divisi'),
        ];

        foreach ($divisions as $division) {
            $tabs["div_{$division->id}"] = Tab::make($division->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('division_id', $division->id));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            AtkDivisionStock::getImportAction()
                ->visible(fn () => auth()->user()->hasRole('Admin') && auth()->user()->isGA() || auth()->user()->hasRole('Super Admin')),
            CreateAction::make()
                ->successNotificationTitle('ATK Division Stock created'),
        ];
    }
}
