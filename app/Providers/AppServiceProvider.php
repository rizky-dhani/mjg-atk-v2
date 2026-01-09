<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Models\AtkBudgeting;
use App\Policies\RolePolicy;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Facades\Gate;
use App\Observers\AtkBudgetingObserver;
use Illuminate\Support\ServiceProvider;
use Filament\Notifications\Livewire\Notifications;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::Start);
        
        // Register model observers
        AtkBudgeting::observe(AtkBudgetingObserver::class);
        
        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true: null;
        });
        Gate::policies([
            Role::class => RolePolicy::class,
            Permission::class => PermissionPolicy::class,
        ]);
    }
}
