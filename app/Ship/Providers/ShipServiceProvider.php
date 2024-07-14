<?php

namespace App\Ship\Providers;

use App\Containers\Authentication\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ShipServiceProvider extends ServiceProvider
{
    private array $serviceProviders = [
        RouteServiceProvider::class,
        MigrationServiceProvider::class,
        AuthServiceProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->serviceProviders ?? [] as $provider) {
            if (class_exists($provider)) {
                App::register($provider);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
