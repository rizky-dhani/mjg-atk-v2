<?php

namespace App\Providers;

use App\Listeners\LogSentEmail;
use App\Models\AtkBudgeting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Observers\AtkBudgetingObserver;
use App\Policies\RolePolicy;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
            return $user->isSuperAdmin() ? true : null;
        });
        Gate::policies([
            Role::class => RolePolicy::class,
            Permission::class => PermissionPolicy::class,
        ]);

        Event::listen(
            MessageSent::class,
            LogSentEmail::class,
        );
    }
}
