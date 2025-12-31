<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AtkItems\AtkItemResource;
use App\Filament\Resources\AtkStockRequests\AtkStockRequestResource;
use App\Filament\Resources\AtkStockUsages\AtkStockUsageResource;
use App\Filament\Resources\AtkTransferStocks\AtkTransferStockResource;
use App\Filament\Resources\MarketingMediaItems\MarketingMediaItemResource;
use App\Filament\Resources\MarketingMediaStockRequests\MarketingMediaStockRequestResource;
use App\Filament\Resources\MarketingMediaStockUsages\MarketingMediaStockUsageResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->login()
            ->spa()
            ->databaseTransactions()
            ->brandLogo(fn() => asset('assets/images/LOGO-MEDQUEST-HD.png'))
            ->brandLogoHeight('2em')
            ->favicon(fn() => asset('assets/images/Medquest-Favicon.png'))
            ->maxContentWidth(Width::Full)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->navigationItems([
                // Alat Tulis Kantor
                NavigationItem::make('Permintaan ATK')
                    ->icon(fn () => Heroicon::ArrowDownTray)
                    ->url(fn () => AtkStockRequestResource::getUrl('index'))
                    ->group('Alat Tulis Kantor')
                    ->isActiveWhen(fn () => request()->url() === AtkStockRequestResource::getUrl('index')),
                NavigationItem::make('Pengeluaran ATK')
                    ->icon(fn () => Heroicon::ArrowUpTray)
                    ->url(fn () => AtkStockUsageResource::getUrl('index'))
                    ->group('Alat Tulis Kantor')
                    ->isActiveWhen(fn () => request()->url() === AtkStockUsageResource::getUrl('index')),
                NavigationItem::make('Transfer Stok ATK')
                    ->icon(fn () => Heroicon::ArrowsRightLeft)
                    ->url(fn () => AtkTransferStockResource::getUrl('index'))
                    ->group('Alat Tulis Kantor')
                    ->isActiveWhen(fn () => request()->url() === AtkTransferStockResource::getUrl('index')),

                // Marketing Media
                NavigationItem::make('Permintaan Marketing Media')
                    ->icon(fn () => Heroicon::ArrowDownTray)
                    ->url(fn () => MarketingMediaStockRequestResource::getUrl('index'))
                    ->group('Marketing Media')
                    ->isActiveWhen(fn () => request()->url() === MarketingMediaStockRequestResource::getUrl('index'))
                    ->visible(fn () => auth()->user()->hasRole('Admin') && auth()->user()->division && stripos(auth()->user()->division->name, 'Marketing') !== false || auth()->user()->hasRole('Super Admin')),
                NavigationItem::make('Pengeluaran Marketing Media')
                    ->icon(fn () => Heroicon::ArrowUpTray)
                    ->url(fn () => MarketingMediaStockUsageResource::getUrl('index'))
                    ->group('Marketing Media')
                    ->isActiveWhen(fn () => request()->url() === MarketingMediaStockUsageResource::getUrl('index'))
                    ->visible(fn () => auth()->user()->hasRole('Admin') && auth()->user()->division && stripos(auth()->user()->division->name, 'Marketing') !== false || auth()->user()->hasRole('Super Admin')),

                // Approval Permintaan
                NavigationItem::make('Permintaan ATK')
                    ->icon(fn () => Heroicon::ArrowDownTray)
                    ->url(fn () => AtkStockRequestResource::getUrl('approval'))
                    ->group('Approval Permintaan')
                    ->isActiveWhen(fn () => request()->url() === AtkStockRequestResource::getUrl('approval'))
                    ->visible(fn () => $this->canUserSeeApprovalNav()),
                NavigationItem::make('Pengeluaran ATK')
                    ->icon(fn () => Heroicon::ArrowUpTray)
                    ->url(fn () => AtkStockUsageResource::getUrl('approval'))
                    ->group('Approval Permintaan')
                    ->isActiveWhen(fn () => request()->url() === AtkStockUsageResource::getUrl('approval'))
                    ->visible(fn () => $this->canUserSeeApprovalNav()),
                NavigationItem::make('Transfer Stok ATK')
                    ->icon(fn () => Heroicon::ArrowsRightLeft)
                    ->url(fn () => AtkTransferStockResource::getUrl('approval'))
                    ->group('Approval Permintaan')
                    ->isActiveWhen(fn () => request()->url() === AtkTransferStockResource::getUrl('approval'))
                    ->visible(fn () => $this->canUserSeeApprovalNav()),
                NavigationItem::make('Permintaan Marketing Media')
                    ->icon(fn () => Heroicon::ArrowDownTray)
                    ->url(fn () => MarketingMediaStockRequestResource::getUrl('approval'))
                    ->group('Approval Permintaan')
                    ->isActiveWhen(fn () => request()->url() === MarketingMediaStockRequestResource::getUrl('approval'))
                    ->visible(fn () => $this->canUserSeeApprovalNav()),
                NavigationItem::make('Pengeluaran Marketing Media')
                    ->icon(fn () => Heroicon::ArrowUpTray)
                    ->url(fn () => MarketingMediaStockUsageResource::getUrl('approval'))
                    ->group('Approval Permintaan')
                    ->isActiveWhen(fn () => request()->url() === MarketingMediaStockUsageResource::getUrl('approval'))
                    ->visible(fn () => $this->canUserSeeApprovalNav()),

                // Inventory Stock Management
                NavigationItem::make('Item Inventaris - ATK')
                    ->icon(fn () => Heroicon::ArchiveBox)
                    ->url(fn () => AtkItemResource::getUrl('index'))
                    ->group('Settings')
                    ->isActiveWhen(fn () => request()->url() === AtkItemResource::getUrl('index'))
                    ->visible(fn () => auth()->user()->hasRole('Admin') && auth()->user()->division->initial === 'GA'),
                NavigationItem::make('Item Inventaris - Marketing Media')
                    ->icon(fn () => Heroicon::ArchiveBox)
                    ->url(fn () => MarketingMediaItemResource::getUrl('index'))
                    ->group('Settings')
                    ->isActiveWhen(fn () => request()->url() === MarketingMediaItemResource::getUrl('index'))
                    ->visible(fn () => auth()->user()->hasRole('Admin') && auth()->user()->division->initial === 'GA'),

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\AtkStockRequestStatus::class,
                \App\Filament\Widgets\AtkStockUsageStatus::class,
                \App\Filament\Widgets\AtkTransferStockStatus::class,
                \App\Filament\Widgets\Budgeting::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Check if the current user can see the approval navigation items
     * by checking if they have any matching approval flow steps
     */
    private function canUserSeeApprovalNav(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Check if user is Super Admin, return true immediately
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Find approval flow steps that match the user's role and division
        $matchingSteps = \App\Models\ApprovalFlowStep::whereHas('role', function ($query) use ($user) {
            $query->whereIn('id', $user->roles->pluck('id'));
        })
            ->where(function ($query) use ($user) {
                // Step is available for the user's own division OR for specific division
                $query
                    ->whereNull('division_id')
                    ->orWhere('division_id', $user->division_id);
            })
            ->get();

        // If there are matching steps, the user can see the approval navigation
        return ! $matchingSteps->isEmpty();
    }
}
