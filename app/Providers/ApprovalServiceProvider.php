<?php

namespace App\Providers;

use App\Services\ApprovalService;
use App\Services\ApprovalValidationService;
use App\Services\ApprovalProcessingService;
use App\Services\ApprovalHistoryService;
use App\Services\StockUpdateService;
use Illuminate\Support\ServiceProvider;

class ApprovalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the individual services
        $this->app->singleton(ApprovalValidationService::class, function ($app) {
            return new ApprovalValidationService();
        });

        $this->app->singleton(ApprovalHistoryService::class, function ($app) {
            return new ApprovalHistoryService();
        });

        $this->app->singleton(StockUpdateService::class, function ($app) {
            return new StockUpdateService();
        });

        $this->app->singleton(ApprovalProcessingService::class, function ($app) {
            return new ApprovalProcessingService(
                $app->make(ApprovalValidationService::class),
                $app->make(ApprovalHistoryService::class),
                $app->make(StockUpdateService::class)
            );
        });

        $this->app->singleton(ApprovalService::class, function ($app) {
            return new ApprovalService(
                $app->make(ApprovalValidationService::class),
                $app->make(ApprovalProcessingService::class),
                $app->make(ApprovalHistoryService::class),
                $app->make(StockUpdateService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}