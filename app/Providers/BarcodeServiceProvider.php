<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BarcodeService;

class BarcodeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(BarcodeService::class, function ($app) {
            return new BarcodeService();
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
