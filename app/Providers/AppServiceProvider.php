<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FonnteService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
{
    $this->app->singleton(FonnteService::class, function ($app) {
        return new FonnteService();
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
